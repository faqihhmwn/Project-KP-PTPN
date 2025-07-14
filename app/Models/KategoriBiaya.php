<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriBiaya extends Model
{
    use HasFactory;

    public function rekapBiayaKesehatans()
    {
        return $this->hasMany(RekapBiayaKesehatan::class);
    }

}
