<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapitulasiObat extends Model
{
    protected $table = 'rekapitulasi_obats';
    protected $fillable = [
        'obat_id',
        'tanggal',
        'stok_awal',
        'jumlah_keluar',
        'sisa_stok',
        'total_biaya',
        'bulan',
        'tahun'
    ];
    public $timestamps = true;

    protected $dates = [
        'tanggal',
        'created_at',
        'updated_at'
    ];

    public function obat()
    {
        return $this->belongsTo(Obat::class, 'obat_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
