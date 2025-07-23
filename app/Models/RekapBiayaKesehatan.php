<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapBiayaKesehatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun',
        'bulan_id',
        'kategori_biaya_id',
        'total_biaya_kesehatan',
        'cakupan_semua_unit',
        'cakupan_semua_bulan',
        'cakupan_semua_kategori',
    ];

    // Relasi
    public function bulan()
    {
        return $this->belongsTo(Bulan::class);
    }

    public function kategoriBiaya()
    {
        return $this->belongsTo(KategoriBiaya::class);
    }
}
