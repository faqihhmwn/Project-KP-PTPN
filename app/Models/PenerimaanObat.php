<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenerimaanObat extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model ini.
     *
     * @var string
     */
    protected $table = 'penerimaan_obats';

    /**
     * Kolom yang dapat diisi secara massal (mass assignable).
     *
     * @var array
     */
    protected $fillable = [
        'obat_id',
        'unit_id',
        'jumlah_masuk',
        'tanggal_masuk',
        'catatan',
    ];

    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
