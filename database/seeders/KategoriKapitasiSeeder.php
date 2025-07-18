<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KategoriKapitasiSeeder extends Seeder
{

public function run()
{
    DB::table('kategori_kapitasis')->insert([
        ['nama' => 'REIMBURSE, DLL'],
        ['nama' => 'COVID 19'],
        ['nama' => 'OPTIK'],
        ['nama' => 'DOKTER GIGI'],
        ['nama' => 'PBF'],
        ['nama' => 'APOTEK'],
        ['nama' => 'LAB'],
        ['nama' => 'PMK'],
        ['nama' => 'RUMAH SAKIT']
    ]);
}
}

