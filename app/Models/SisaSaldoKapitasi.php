<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SisaSaldoKapitasi extends Model
{
    protected $table = 'sisa_saldo_kapitasis';

    protected $fillable = [
        'tahun',
        'bulan_id',
        'saldo_awal_tahun',
        'sisa_saldo',
    ];

        public function bulan()
    {
        return $this->belongsTo(Bulan::class);
    }
}
