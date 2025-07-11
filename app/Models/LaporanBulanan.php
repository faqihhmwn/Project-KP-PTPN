<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanBulanan extends Model
{
    use HasFactory;

    protected $table = 'laporan_bulanan';
    protected $fillable = [
        'kategori_id',
        'subkategori_id',
        'unit_id',
        'user_id',
        'bulan',
        'tahun',
        'jumlah',
        'input_manual_id',
    ];
    

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function subkategori()
    {
        return $this->belongsTo(SubKategori::class);
    }

    public function inputManual()
    {
        return $this->belongsTo(InputManual::class, 'input_manual_id');
    }
}
