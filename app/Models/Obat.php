<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class Obat extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_obat',
        'jenis_obat',
        'harga_satuan',
        'satuan',
        'stok_awal',
        'stok_sisa',
        'keterangan',
        'unit_id',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2'
    ];

    // Relationship
    public function transaksiObats()
    {
        return $this->hasMany(TransaksiObat::class);
    }

    // Scope untuk obat aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk search
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nama_obat', 'like', "%{$search}%")
                ->orWhere('jenis_obat', 'like', "%{$search}%");
        });
    }

    // Scope untuk mengambil rekapitulasi terbaru
    public function scopeWithLastRekapitulasi($query)
    {
        return $query->addSelect(['last_rekapitulasi_id' => RekapitulasiObat::select('id')
            ->whereColumn('obat_id', 'obats.id')
            ->latest('tahun')
            ->latest('bulan')
            ->latest('tanggal')
            ->limit(1)
        ])->with(['last_rekapitulasi' => function($q) {
            $q->select('id', 'obat_id', 'sisa_stok', 'tanggal', 'bulan', 'tahun');
        }]);
    }

    // Relationship dengan rekapitulasi obat
    public function rekapitulasiObat()
    {
    return $this->hasMany(RekapitulasiObat::class)
                ->where('unit_id', Auth::user()->unit_id);
}

    public function last_rekapitulasi()
    {
        return $this->belongsTo(RekapitulasiObat::class, 'last_rekapitulasi_id');
    }

    // Method untuk mendapatkan stok awal berdasarkan bulan dan tahun
    public function stokAwal($bulan = null, $tahun = null)
    {
        // Jika bulan dan tahun tidak diisi, gunakan bulan sekarang
        if (is_null($bulan) || is_null($tahun)) {
            $now = Carbon::now();
            $bulan = $now->month;
            $tahun = $now->year;
        }

        // Tanggal yang diminta
        $tanggalDiminta = Carbon::create($tahun, $bulan, 1);

        // Cari data rekapitulasi pertama yang pernah dibuat untuk obat ini
        $rekapPertama = $this->rekapitulasiObat()
            ->where('unit_id', Auth::user()->unit_id)
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->orderBy('tanggal')
            ->first();

        // Jika belum ada rekapitulasi sama sekali, kembalikan stok awal dari data obat
        if (!$rekapPertama) {
            return $this->attributes['stok_awal'];
        }

        // Tanggal rekapitulasi pertama
        $tanggalPertama = Carbon::create($rekapPertama->tahun, $rekapPertama->bulan, 1);

        // Jika bulan yang diminta adalah sebelum bulan pertama rekapitulasi
        // kembalikan 0 untuk mencegah stok muncul di bulan-bulan sebelumnya
        if ($tanggalDiminta < $tanggalPertama) {
            return 0;
        }

        // Jika bulan yang diminta adalah bulan pertama rekapitulasi
        // kembalikan stok awal dari data obat
        if ($tanggalDiminta->format('Y-m') === $tanggalPertama->format('Y-m')) {
            return $this->attributes['stok_awal'];
        }

        // Untuk bulan-bulan setelah rekapitulasi pertama
        // cari rekapitulasi terakhir dari bulan sebelumnya
        $rekapTerakhir = $this->rekapitulasiObat()
            ->where('unit_id', Auth::user()->unit_id)
            ->where(function ($query) use ($tanggalDiminta) {
                $query->where('tanggal', '<', $tanggalDiminta->format('Y-m-d'));
            })
            ->latest('tahun')
            ->latest('bulan')
            ->latest('tanggal')
            ->first();

        return $rekapTerakhir ? $rekapTerakhir->sisa_stok : 0;
    }


    // Method untuk mendapatkan sisa stok berdasarkan rekapitulasi terbaru
    public function getStokSisaAttribute()
    {
        // Ambil rekapitulasi paling baru berdasarkan tahun, bulan, dan tanggal
        $rekapTerbaru = $this->rekapitulasiObat()
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->orderBy('tanggal', 'desc')
            ->first();

        // Jika ada rekapitulasi, gunakan sisa stok dari rekapitulasi
        // Jika tidak ada, kembalikan nilai stok_sisa yang ada di database
        return $rekapTerbaru ? $rekapTerbaru->sisa_stok : $this->attributes['stok_sisa'] ?? $this->stok_awal;
    }

    // Method untuk set stok sisa
    public function setStokSisaAttribute($value)
    {
        $this->attributes['stok_sisa'] = $value;
    }


    // Method untuk update stok
    public function updateStok()
    {
        try {
            // Ambil rekapitulasi terbaru untuk mendapatkan sisa stok yang benar
            $rekapTerbaru = $this->rekapitulasiObat()
                ->orderBy('tahun', 'desc')
                ->orderBy('bulan', 'desc')
                ->orderBy('tanggal', 'desc')
                ->first();

            if ($rekapTerbaru) {
                $this->stok_sisa = $rekapTerbaru->sisa_stok;
            } else {
                // Jika tidak ada rekapitulasi, hitung dari transaksi
                $totalMasuk = $this->transaksiObats()
                    ->where('tipe_transaksi', 'masuk')
                    ->sum('jumlah_masuk') ?? 0;
                    
                $totalKeluar = $this->transaksiObats()
                    ->where('tipe_transaksi', 'keluar')
                    ->sum('jumlah_keluar') ?? 0;

                $this->stok_masuk = $totalMasuk;
                $this->stok_keluar = $totalKeluar;
                $this->stok_sisa = $this->stok_awal + $totalMasuk - $totalKeluar;
            }

            $this->save();
            return true;
        } catch (\Exception $e) {
            \Log::error('Error updating stock: ' . $e->getMessage());
            return false;
        }
    }
    // Method untuk mendapatkan rekapitulasi bulan tertentu
    public function getRekapitulasiBulan($bulan = null, $tahun = null)
    {
        if (is_null($bulan) || is_null($tahun)) {
            $now = Carbon::now();
            $bulan = $now->month;
            $tahun = $now->year;
        }

        return $this->rekapitulasiObat()
            ->where('unit_id', Auth::user()->unit_id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    // Method untuk mendapatkan informasi penggunaan untuk bulan tertentu
    public function getInfoPenggunaan($bulan = null, $tahun = null)
    {
        if (is_null($bulan) || is_null($tahun)) {
            $now = Carbon::now();
            $bulan = $now->month;
            $tahun = $now->year;
        }

        // Ambil semua rekapitulasi untuk bulan tersebut
        $rekapitulasi = $this->rekapitulasiObat()
            ->where('unit_id', Auth::user()->unit_id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get();

        // Hitung total penggunaan dan biaya
        $jumlahKeluar = $rekapitulasi->sum('jumlah_keluar');
        $totalBiaya = $jumlahKeluar * $this->harga_satuan;

        // Ambil update terakhir
        $lastUpdate = $rekapitulasi->sortByDesc('updated_at')->first();

        return [
            'jumlah' => $jumlahKeluar,
            'biaya' => $totalBiaya,
            'last_update' => $lastUpdate ? $lastUpdate->updated_at : null
        ];
    }

    // Method untuk mendapatkan penggunaan bulan ini
    public function getPenggunaanBulanIni($bulan = null, $tahun = null)
    {
        return $this->getInfoPenggunaan($bulan, $tahun);
    }

    // Method untuk mendapatkan penggunaan bulan lalu
    public function getPenggunaanBulanLalu($bulan = null, $tahun = null)
    {
        if (is_null($bulan) || is_null($tahun)) {
            $date = Carbon::now();
        } else {
            $date = Carbon::createFromDate($tahun, $bulan, 1);
        }
        
        $bulanLalu = $date->copy()->subMonth();
        
        return $this->getInfoPenggunaan($bulanLalu->month, $bulanLalu->year);
    }

    // Method untuk format harga
    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format($this->harga_satuan, 0, ',', '.');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }


    public function transaksiObatsByUnit()
    {
        return $this->hasMany(TransaksiObat::class)->whereHas('obat', function ($q) {
            $q->where('unit_id', Auth::user()->unit_id);
        });
    }
}