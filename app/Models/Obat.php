<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'keterangan'
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
        return $query->where(function($q) use ($search) {
            $q->where('nama_obat', 'like', "%{$search}%")
              ->orWhere('jenis_obat', 'like', "%{$search}%");
        });
    }

    // Relationship dengan rekapitulasi obat
    public function rekapitulasiObat()
    {
        return $this->hasMany(RekapitulasiObat::class);
    }

    // Method untuk mendapatkan stok awal berdasarkan bulan dan tahun
    public function getStokAwalAttribute()
    {
        $now = Carbon::now();
        
        // Ambil rekapitulasi terakhir dari bulan sebelumnya
        $rekapBulanSebelumnya = $this->rekapitulasiObat()
            ->whereMonth('tanggal', $now->copy()->subMonth()->month)
            ->whereYear('tanggal', $now->copy()->subMonth()->year)
            ->latest('tanggal')
            ->first();

        // Jika ada data bulan sebelumnya, gunakan sisa stoknya
        // Jika tidak, gunakan stok awal dari database
        return $rekapBulanSebelumnya ? $rekapBulanSebelumnya->sisa_stok : $this->attributes['stok_awal'];
    }

    // Method untuk mendapatkan sisa stok berdasarkan rekapitulasi terbaru
    public function getStokSisaAttribute()
    {
        // Ambil rekapitulasi paling baru
        $rekapTerbaru = $this->rekapitulasiObat()
            ->latest('tanggal')
            ->first();

        // Jika ada rekapitulasi, gunakan sisa stok dari rekapitulasi
        // Jika tidak ada, gunakan stok awal
        return $rekapTerbaru ? $rekapTerbaru->sisa_stok : $this->getStokAwalAttribute();
    }

    // Method untuk update stok
    public function updateStok()
    {
        $totalMasuk = $this->transaksiObats()->where('tipe_transaksi', 'masuk')->sum('jumlah_masuk');
        $totalKeluar = $this->transaksiObats()->where('tipe_transaksi', 'keluar')->sum('jumlah_keluar');
        
        $this->stok_masuk = $totalMasuk;
        $this->stok_keluar = $totalKeluar;
        $this->stok_sisa = $this->stok_awal + $totalMasuk - $totalKeluar;
        $this->save();
    }

    // Method untuk mendapatkan transaksi bulan ini
    public function getTransaksiBulanIni()
    {
        return $this->transaksiObats()
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->get();
    }

    // Method untuk mendapatkan transaksi bulan lalu
    public function getTransaksiBulanLalu()
    {
        return $this->transaksiObats()
            ->whereMonth('tanggal', Carbon::now()->subMonth()->month)
            ->whereYear('tanggal', Carbon::now()->subMonth()->year)
            ->get();
    }

    // Method untuk check apakah obat akan expired
    public function isExpiringSoon($days = 30)
    {
        if (!$this->expired_date) {
            return false;
        }
        
        return $this->expired_date->diffInDays(Carbon::now()) <= $days;
    }

    // Method untuk format harga
    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format($this->harga_satuan, 0, ',', '.');
    }
}
