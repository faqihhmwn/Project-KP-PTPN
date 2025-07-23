<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapDanaKapitasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun',
        'bulan_id',
        'kategori_kapitasi_id',
        'total_biaya_kapitasi',
        'cakupan_semua_bulan',
        'cakupan_semua_kategori',
    ];

    // Relasi
    public function bulan()
    {
        return $this->belongsTo(Bulan::class);
    }

    public function kategoriKapitasi()
    {
        return $this->belongsTo(KategoriKapitasi::class);
    }
}
