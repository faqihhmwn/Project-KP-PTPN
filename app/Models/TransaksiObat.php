<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiObat extends Model
{
    use HasFactory;

    // Pastikan ini mencerminkan kolom-kolom AKTUAL di tabel 'transaksi_obats' Anda sekarang
    protected $fillable = [
        'obat_id',
        'tanggal_transaksi', // Ini adalah 'tanggal' setelah di-rename
        'jenis_transaksi',   // Contoh: 'masuk', 'keluar', 'penyesuaian'
        'jumlah',            // Ini adalah 'jumlah_keluar' setelah di-rename
        // 'referensi_transaksi', // Hapus ini jika Anda tidak menambahkannya
        // 'petugas',             // Hapus ini jika Anda tidak menambahkannya
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
    ];

    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }
}