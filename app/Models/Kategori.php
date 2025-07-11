<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategori';

    protected $fillable = ['nama'];

    public function subkategori()
    {
        return $this->hasMany(SubKategori::class);
    }

    public function laporan()
    {
        return $this->hasMany(LaporanBulanan::class);
    }
}
