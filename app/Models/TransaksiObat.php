<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TransaksiObat extends Model
{
    use HasFactory;

    protected $fillable = [
        'obat_id',
        'tanggal',
        'jumlah_keluar',
        'jumlah_masuk',
        'total_biaya',
        'tipe_transaksi',
        'keterangan',
        'petugas'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_biaya' => 'decimal:2'
    ];

    // Relationship
    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }

    // Scope untuk filter berdasarkan bulan
    public function scopeByMonth($query, $month, $year = null)
    {
        $year = $year ?? Carbon::now()->year;
        return $query->whereMonth('tanggal', $month)
                    ->whereYear('tanggal', $year);
    }

    // Scope untuk filter berdasarkan range tanggal
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    // Scope untuk transaksi masuk
    public function scopeMasuk($query)
    {
        return $query->where('tipe_transaksi', 'masuk');
    }

    // Scope untuk transaksi keluar
    public function scopeKeluar($query)
    {
        return $query->where('tipe_transaksi', 'keluar');
    }

    // Method untuk auto calculate total biaya
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($transaksi) {
            if ($transaksi->obat) {
                if ($transaksi->tipe_transaksi === 'keluar') {
                    $transaksi->total_biaya = $transaksi->jumlah_keluar * $transaksi->obat->harga_satuan;
                } else {
                    $transaksi->total_biaya = $transaksi->jumlah_masuk * $transaksi->obat->harga_satuan;
                }
            }
        });

        static::saved(function ($transaksi) {
            // Update stok obat setelah transaksi disimpan
            $transaksi->obat->updateStok();
        });
    }
}
