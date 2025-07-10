<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run()
    {
        DB::table('units')->insert([
            ['name' => 'Kandir'],
            ['name' => 'Way Lima'],
            ['name' => 'Way Berulu'],
            ['name' => 'Kedaton'],
            ['name' => 'Bergen'],
            ['name' => 'Tulang Buyut'],
            ['name' => 'Musilandas'],
            ['name' => 'Tebenan'],
            ['name' => 'Beringin'],
            ['name' => 'Padang Pelawi'],
            ['name' => 'Ketahun'],
            ['name' => 'Senabing'],
        ]);
    }
}