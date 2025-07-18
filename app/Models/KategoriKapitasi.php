<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriKapitasi extends Model
{
    use HasFactory;

    public function rekapDanaKapitasis()
    {
        return $this->hasMany(RekapDanaKapitasi::class);
    }

}
