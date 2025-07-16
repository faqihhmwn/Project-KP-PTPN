<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiayaTersedia extends Model
{
    use HasFactory;

    protected $table = 'biaya_tersedia';

    protected $fillable = [
        'tahun',
        'kategori_biaya_id',
        'jumlah',
    ];

    public function kategoriBiaya()
    {
        return $this->belongsTo(KategoriBiaya::class);
    }
}
