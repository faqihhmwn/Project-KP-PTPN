<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Obat extends Model
{
    use HasFactory;

    // Pastikan ini mencerminkan kolom-kolom AKTUAL di tabel 'obats' Anda sekarang
    protected $fillable = [
        'nama_obat',
        'jenis_obat',
        'harga_satuan',
        'satuan',
        'keterangan',
        'unit_id',
        'stok_terakhir', // Ini adalah kolom ACTUAL untuk stok saat ini
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
    ];

    // Relationships (tetap sama)
    public function transaksiObats()
    {
        return $this->hasMany(TransaksiObat::class);
    }

    public function rekapitulasiObat()
    {
        return $this->hasMany(RekapitulasiObat::class)
                    ->where('unit_id', Auth::user()->unit_id);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // Scopes (tetap relevan, contoh)
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Menghitung stok awal obat untuk bulan tertentu
     *
     * @param int $bulan
     * @param int $tahun
     * @return int
     */
    public function getStokAwal($bulan, $tahun)
    {
        // Cek bulan sebelumnya
        $previousMonth = $bulan == 1 ? 12 : $bulan - 1;
        $previousYear = $bulan == 1 ? $tahun - 1 : $tahun;
        
        // Cari sisa stok dari bulan sebelumnya
        $previousMonthStock = $this->rekapitulasiObat()
            ->where('bulan', $previousMonth)
            ->where('tahun', $previousYear)
            ->orderBy('tanggal', 'desc')
            ->first();

        // Jika ada data bulan sebelumnya, gunakan sisa stoknya
        if ($previousMonthStock) {
            return $previousMonthStock->sisa_stok;
        }

        // Jika tidak ada data sebelumnya, gunakan stok terakhir dari obat
        return $this->stok_terakhir;
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nama_obat', 'like', "%{$search}%")
                ->orWhere('jenis_obat', 'like', "%{$search}%");
        });
    }

    // --- METHODS (Logika stok yang benar) ---

    // Metode ini untuk mendapatkan stok awal bulan dari rekapitulasi_obats.
    // Ini AKAN digambar di tampilan Rekapitulasi Obat Bulanan pada kolom 'Stok Awal'.
    public function getStokAwalBulan($bulan = null, $tahun = null)
    {
        $bulan = $bulan ?? Carbon::now()->month;
        $tahun = $tahun ?? Carbon::now()->year;

        $firstDayOfMonth = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $lastDayOfPreviousMonth = $firstDayOfMonth->copy()->subDay()->endOfDay();

        // Cari rekapitulasi terakhir di bulan sebelumnya
        $rekapAkhirBulanSebelumnya = $this->rekapitulasiObat()
            ->whereDate('tanggal', $lastDayOfPreviousMonth->toDateString())
            ->first();

        if ($rekapAkhirBulanSebelumnya) {
            // Jika ada data rekapitulasi di akhir bulan sebelumnya, itu adalah stok awal bulan ini
            return $rekapAkhirBulanSebelumnya->sisa_stok; // Menggunakan kolom 'sisa_stok' di rekapitulasi_obats
        }

        // Jika tidak ada data rekapitulasi di bulan sebelumnya,
        // Cek apakah ada rekapitulasi pertama di hari pertama bulan yang diminta.
        // Jika ada, 'stok_awal' di entri itu adalah stok awal hari pertama bulan itu.
        $rekapPertamaDiBulan = $this->rekapitulasiObat()
                                    ->whereDate('tanggal', $firstDayOfMonth->toDateString())
                                    ->first();
        if ($rekapPertamaDiBulan) {
            return $rekapPertamaDiBulan->stok_awal; // Menggunakan kolom 'stok_awal' di rekapitulasi_obats
        }

        // Jika belum ada rekapitulasi sama sekali untuk bulan ini atau bulan sebelumnya,
        // Ambil dari stok_terakhir di tabel obat itu sendiri.
        // Ini adalah skenario ketika obat baru ditambahkan atau belum ada pergerakan.
        return $this->stok_terakhir ?? 0;
    }

    // Metode ini untuk mendapatkan semua entri rekapitulasi harian untuk bulan dan tahun tertentu.
    public function getRekapitulasiBulanan($bulan = null, $tahun = null)
    {
        if (is_null($bulan) || is_null($tahun)) {
            $now = Carbon::now();
            $bulan = $now->month;
            $tahun = $now->year;
        }

        return $this->rekapitulasiObat()
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

    // Accessor untuk format harga (tetap relevan)
    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format($this->harga_satuan, 0, ',', '.');
    }
}