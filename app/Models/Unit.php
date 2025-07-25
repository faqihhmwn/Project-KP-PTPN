<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = ['nama'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function rekapBiayaKesehatans()
    {
        return $this->hasMany(RekapBiayaKesehatan::class);
    }
}
