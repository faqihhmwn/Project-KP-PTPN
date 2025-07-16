<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiayaTersedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun',
        'kategori_biaya_id',
        'total_tersedia'
    ];

    // Relasi

    public function kategoriBiaya()
    {
        return $this->belongsTo(KategoriBiaya::class);
    }
}
