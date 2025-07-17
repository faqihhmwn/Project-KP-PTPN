<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapBpjsIuran extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun',
        'bulan_id',
        'kategori_iuran_id',
        'total_iuran_bpjs',
        'cakupan_semua_unit',
        'cakupan_semua_bulan',
        'cakupan_semua_kategori',
    ];

    // Relasi
    public function bulan()
    {
        return $this->belongsTo(Bulan::class);
    }

    public function kategoriIuran()
    {
        return $this->belongsTo(KategoriIuran::class);
    }
}
