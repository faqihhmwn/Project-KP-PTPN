<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run()
    {
        $units = ['KANDIR', 'WABE', 'RESA', 'KEDA', 'BEGE', 'WALI', 'BEKI', 'PATU', 'PUMA', 'TUBU', 'CIMA', 'BETA', 'BEKA', 'BETU', 'TASA', 'TEBE', 'MULA', 'BERI', 'BAJA', 'PALA', 'PEWA', 'SUNI', 'D.SUM', 'SULI', 'TAPI', 'KETA', 'PAWI', 'KBKL'];
        foreach ($units as $nama) {
            Unit::create(['nama' => $nama]);
        }
    }
}

