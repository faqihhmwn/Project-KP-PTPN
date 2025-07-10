<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class RekapTahun extends Model
{
    protected $fillable = [
        'tahun','unit_id',
        'gol_3_4','gol_1_2','kampanye','honor',
        'pens_3_4','pens_1_2','direksi','dekom','pengacara',
        'transport','hiperkes','total'
    ];

    public function unit()
{
    return $this->belongsTo(Unit::class);
}

}