<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subkategori extends Model
{
    use HasFactory;

    protected $table = 'subkategori';

    protected $fillable = ['nama', 'kategori_id'];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function laporan()
    {
        return $this->hasMany(LaporanBulanan::class);
    }
}
