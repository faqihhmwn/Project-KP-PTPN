<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanKependudukan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'laporan_bulanan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unit_id',
        'subkategori_id',
        'bulan',
        'tahun',
        'laki_laki',
        'perempuan',
        'total',
        'is_approved',
    ];

    /**
     * Get the unit that owns the report.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the subcategory for the report.
     */
    public function subkategori()
    {
        return $this->belongsTo(Subkategori::class);
    }
}