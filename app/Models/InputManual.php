<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InputManual extends Model
{
    use HasFactory;

    protected $table = 'input_manual';

    protected $fillable = [
        'kategori_id',
        'unit_id',
        'user_id',
        'subkategori_id',
        'laporan_id',
        'nama',
        'status',
        'jenis_disabilitas',
        'keterangan',
        'bulan',
        'tahun',
    ];


    /**
     * Relasi ke unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Relasi ke user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke subkategori
     */
    public function subkategori()
    {
        return $this->belongsTo(SubKategori::class);
    }

    /**
     * Relasi ke laporan_bulanan
     */
    public function laporan()
    {
        return $this->belongsTo(LaporanBulanan::class, 'laporan_id');
    }
}
