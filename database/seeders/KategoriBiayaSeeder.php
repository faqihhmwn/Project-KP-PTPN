<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KategoriBiayaSeeder extends Seeder
{

public function run()
{
    // Nonaktifkan foreign key checks sementara
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    // Hapus semua data
    DB::table('kategori_biayas')->delete();

    // Aktifkan lagi foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    // Insert ulang data baru
    DB::table('kategori_biayas')->insert([
        ['nama' => 'Gol. III-IV'],
        ['nama' => 'Gol. I-II'],
        ['nama' => 'Kampanye'],
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

