<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KategoriBiayaSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriBiayas = [
            'gol_3_4',
            'gol_1_2',
            'kampanye',
            'honor_ila_os',
            'pens_3_4',
            'pens_1_2',
            'direksi',
            'dekom',
            'pengacara',
            'transport',
            'hiperkes',
            'pensiunan',
            'honorer',
        ];

        foreach ($kategoriBiayas as $nama) {
            DB::table('kategori_biayas')->updateOrInsert(
                ['nama' => $nama],
                ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            );
        }
    }
}
