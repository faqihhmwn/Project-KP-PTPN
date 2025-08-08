<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapitulasiValidasiGlobal extends Model
{
    protected $table = 'rekapitulasi_validasi_global';

    protected $fillable = [
        'bulan', 'tahun', 'validated_at', 'validated_by'
    ];

    public $timestamps = true;
}
