<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BulanSeeder extends Seeder
{
    public function run(): void
    {
        $namaBulan = [
            'Januari', 'Februari', 'Maret', 'April',
            'Mei', 'Juni', 'Juli', 'Agustus',
            'September', 'Oktober', 'November', 'Desember'
        ];

        foreach ($namaBulan as $bulan) {
            DB::table('bulans')->insert([
                'nama' => $bulan,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
