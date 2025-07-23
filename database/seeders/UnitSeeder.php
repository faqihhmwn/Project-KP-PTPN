<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run()
    {
        $units = [
            'KANDIR_REG_7', 'PALMCO_REG_7', 'KANDIR_BCN', 'SGN', 'BEGE', 'BEKI', 'BUMA',
            'KEDA', 'PATU', 'REPA', 'TUBU', 'WABE', 'WALI', 'KSSL', 'BAJA', 'BEKA',
            'BERI', 'BETA', 'BETU', 'CIMA', 'MULA', 'SULI', 'SUNI', 'TASA', 'TEBE',
            'DBKL', 'KETA', 'PALA', 'PAWI', 'SENA', 'TAPI'
        ];

        foreach ($units as $unit) {
            DB::table('units')->updateOrInsert(
                ['nama' => $unit], // kolom unik
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
