<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name'];

    public function rekapBiayas()
    {
        return $this->hasMany(RekapBiaya::class);
    }

    public function rekapJumlahs()
    {
        return $this->hasMany(RekapJumlah::class);
    }
}