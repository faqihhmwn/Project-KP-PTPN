<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SisaSaldoKapitasi extends Model
{
    protected $table = 'sisa_saldo_kapitasis';

    protected $fillable = [
        'tahun',
        'bulan_id',
        'dana_masuk_id',
        'rekap_dana_kapitasi_id',
        'saldo_awal_tahun',
        'sisa_saldo',
    ];

        public function bulan()
    {
        return $this->belongsTo(Bulan::class);
    }

        public function danaMasuk()
    {
        return $this->belongsTo(DanaMasuk::class);
    }

        public function rekapDanaKapitasi()
    {
        return $this->belongsTo(RekapDanaKapitasi::class);
    }

}
