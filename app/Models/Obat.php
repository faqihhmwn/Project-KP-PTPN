<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Obat extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($obat) {
            // Delete related rekapitulasi records first
            $obat->rekapitulasiObats()->delete();
            // Delete related transaksi records
            $obat->transaksiObats()->delete();
        });
    }

    // Pastikan ini mencerminkan kolom-kolom AKTUAL di tabel 'obats' Anda sekarang
    protected $fillable = [
        'nama_obat',
        'jenis_obat',
        'harga_satuan',
        'satuan',
        'keterangan',
        'unit_id',
        'stok_awal', // <-- PASTIKAN INI ADA DI SINI UNTUK MASS ASSIGNMENT
        'stok_terakhir', // Ini adalah kolom ACTUAL untuk stok saat ini
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
    ];

    // Relationships
    public function transaksiObats()
    {
        return $this->hasMany(TransaksiObat::class);
    }

    // Ubah nama relasi menjadi plural (rekapitulasiObats) agar konsisten dengan hasMany
    public function rekapitulasiObats() // <-- PERUBAHAN NAMA RELASI DI SINI
    {
        return $this->hasMany(RekapitulasiObat::class)
                     ->where('unit_id', Auth::user()->unit_id);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nama_obat', 'like', "%{$search}%")
                ->orWhere('jenis_obat', 'like', "%{$search}%");
        });
    }

    /**
     * Metode ini untuk mendapatkan stok awal bulan dari rekapitulasi_obats.
     * Ini AKAN digambar di tampilan Rekapitulasi Obat Bulanan pada kolom 'Stok Awal'.
     *
     * @param int|null $bulan
     * @param int|null $tahun
     * @return int
     */
    public function getStokAwalBulan($bulan = null, $tahun = null)
    {
        $bulan = $bulan ?? Carbon::now()->month;
        $tahun = $tahun ?? Carbon::now()->year;

        $firstDayOfTargetMonth = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $lastDayOfPreviousMonth = $firstDayOfTargetMonth->copy()->subDay()->endOfDay();

        // 1. Coba cari entri rekapitulasi untuk hari pertama bulan ini
        $firstRekapEntryToday = $this->rekapitulasiObats() // <-- GUNAKAN RELASI YANG SUDAH DIPERBAIKI
            ->whereDate('tanggal', $firstDayOfTargetMonth->toDateString())
            ->first();

        if ($firstRekapEntryToday) {
            // Jika ada entri untuk hari pertama bulan ini, gunakan stok_awal dari entri tersebut
            return $firstRekapEntryToday->stok_awal;
        }

        // 2. Jika tidak ada entri untuk hari pertama bulan ini, cari entri rekapitulasi terakhir di bulan sebelumnya
        $lastRekapEntryPreviousMonth = $this->rekapitulasiObats() // <-- GUNAKAN RELASI YANG SUDAH DIPERBAIKI
            ->whereDate('tanggal', '<=', $lastDayOfPreviousMonth->toDateString())
            ->orderBy('tanggal', 'desc')
            ->first();

        if ($lastRekapEntryPreviousMonth) {
            // Jika ada entri di bulan sebelumnya, stok awal bulan ini adalah sisa stok akhir bulan sebelumnya
            return $lastRekapEntryPreviousMonth->sisa_stok;
        }

        // --- PERBAIKAN LOGIKA FALLBACK UNTUK BULAN SEBELUM OBAT DIBUAT ---
        // 3. Jika tidak ada rekapitulasi sama sekali di bulan ini maupun bulan sebelumnya,
        // periksa tanggal pembuatan obat (created_at).
        
        // Pastikan created_at ada dan merupakan objek Carbon
        if (!$this->created_at instanceof Carbon) {
            // Jika created_at belum berupa Carbon, coba parse. Jika gagal, kembalikan 0.
            try {
                $this->created_at = Carbon::parse($this->created_at);
            } catch (\Exception $e) {
                return 0;
            }
        }

        // Konversi created_at ke objek Carbon untuk perbandingan bulan/tahun
        $obatCreatedAtMonth = $this->created_at->startOfMonth();
        $targetMonth = Carbon::create($tahun, $bulan, 1)->startOfMonth();

        // Jika bulan yang diminta JAUH SEBELUM tanggal pembuatan obat, maka stok awal adalah 0
        if ($targetMonth->lt($obatCreatedAtMonth)) {
            return 0;
        }

        // Jika bulan yang diminta SAMA DENGAN atau SETELAH tanggal pembuatan obat,
        // dan belum ada rekapitulasi, gunakan stok_terakhir sebagai stok awal
        // (ini untuk bulan pertama obat itu aktif, atau jika belum ada transaksi)
        $this->refresh(); // Pastikan stok_terakhir adalah yang terbaru dari database
        return $this->stok_terakhir ?? 0;
        // --- AKHIR PERBAIKAN LOGIKA FALLBACK ---
    }

    // Metode ini untuk mendapatkan semua entri rekapitulasi harian untuk bulan dan tahun tertentu.
    public function getRekapitulasiBulanan($bulan = null, $tahun = null)
    {
        if (is_null($bulan) || is_null($tahun)) {
            $now = Carbon::now();
            $bulan = $now->month;
            $tahun = $now->year;
        }

        return $this->rekapitulasiObats() // <-- GUNAKAN RELASI YANG SUDAH DIPERBAIKI
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'asc')
            ->get();
    }

    // Metode ini untuk mendapatkan informasi penggunaan total (jumlah keluar dan biaya) untuk bulan tertentu.
    public function getInfoPenggunaan($bulan = null, $tahun = null)
    {
        $rekapitulasi = $this->getRekapitulasiBulanan($bulan, $tahun); // Panggil metode di atas

        $jumlahKeluar = $rekapitulasi->sum('jumlah_keluar'); // Menggunakan kolom fisik 'jumlah_keluar' di rekapitulasi_obats
        $totalBiaya = $jumlahKeluar * $this->harga_satuan;

        $lastUpdate = $rekapitulasi->sortByDesc('updated_at')->first();

        return [
            'jumlah' => $jumlahKeluar,
            'biaya' => $totalBiaya,
            'last_update' => $lastUpdate ? $lastUpdate->updated_at : null
        ];
    }

    // Accessor untuk format harga
    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format($this->harga_satuan, 0, ',', '.');
    }

    // Accessor untuk nama obat dalam uppercase (untuk tampilan website)
    public function getNamaObatDisplayAttribute()
    {
        return strtoupper($this->nama_obat);
    }

    // Accessor untuk jenis obat dalam uppercase (untuk tampilan website)
    public function getJenisObatDisplayAttribute()
    {
        return strtoupper($this->jenis_obat);
    }
}
