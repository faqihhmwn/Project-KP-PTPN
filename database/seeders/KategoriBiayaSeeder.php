<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KategoriBiayaSeeder extends Seeder
{

public function run()
{
    DB::table('kategori_biayas')->insert([
        ['nama' => 'Gol. III-IV'],
        ['nama' => 'Gol. I-II'],
        ['nama' => 'Kampanye'],
        ['nama' => 'Honor, ILA, OS'],
        ['nama' => 'Pens. III-IV'],
        ['nama' => 'Pens. I-II'],
        ['nama' => 'Direksi'],
        ['nama' => 'Dekom'],
        ['nama' => 'Pengacara'],
        ['nama' => 'Transport'],
        ['nama' => 'Jml. Biaya Hiperkes'],
    ]);
}
}

