<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanApproval extends Model
{
    use HasFactory;

    protected $table = 'laporan_approvals';

    protected $fillable = [
        'unit_id',
        'kategori_id',
        'bulan',
        'tahun',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }
}