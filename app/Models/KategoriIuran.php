<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriIuran extends Model
{
    use HasFactory;

    public function rekapBpjsIurans()
    {
        return $this->hasMany(RekapBpjsIuran::class);
    }

}
