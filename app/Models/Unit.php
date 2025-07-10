<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name'];

    public function rekapBulans()
    {
        return $this->hasMany(RekapBulan::class);
    }

    public function rekapTahuns()
    {
        return $this->hasMany(RekapTahun::class);
    }
}