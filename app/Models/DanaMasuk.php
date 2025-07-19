<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanaMasuk extends Model
{
    protected $table = 'dana_masuks';

    protected $fillable = [
        'tahun',
        'bulan_id',
        'total_dana_masuk',
    ];

        public function bulan()
    {
        return $this->belongsTo(Bulan::class);
    }

}
