<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapitulasiObat extends Model
{
    use HasFactory;

    // Pastikan ini mencerminkan kolom-kolom AKTUAL di tabel 'rekapitulasi_obats' Anda sekarang
    protected $fillable = [
        'obat_id',
        'unit_id',
        'tanggal',
        'stok_awal',             // Kolom fisik di DB
        'jumlah_masuk_hari_ini', // Kolom baru yang ditambahkan di DB
        'jumlah_keluar',         // Kolom fisik di DB
        'sisa_stok',             // Kolom fisik di DB
        'total_biaya',
        'bulan',                 // Kolom fisik di DB
        'tahun',                 // Kolom fisik di DB
        // 'user_id',             // Hapus ini jika Anda sudah menghapus kolom user_id di DB
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}