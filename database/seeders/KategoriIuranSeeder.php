<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KategoriIuranSeeder extends Seeder
{

public function run()
{
    DB::table('kategori_iurans')->insert([
        ['nama' => 'Karyawan III-IV'],
        ['nama' => 'Karyawan I-II'],
        ['nama' => 'Direksi'],
        ['nama' => 'Pensiunan'],
        ['nama' => 'Pengacara'],
        ['nama' => 'Honorer']
    ]);
}
}

