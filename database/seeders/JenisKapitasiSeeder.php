<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class JenisKapitasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisKapitasis = [
            'reimburse_dll',
            'covid19',
            'optik',
            'doktergigi',
            'PBF',
            'apotek',
            'lab',
            'PMK',
            'rumahsakit',
        ];

        foreach ($jenisKapitasis as $nama) {
            DB::table('jenis_kapitasis')->updateOrInsert(
                ['nama' => $nama],
                ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            );
        }
    }
}
