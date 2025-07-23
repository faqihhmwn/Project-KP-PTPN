<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'airta',
            'email' => 'airta@gmail.com',
            'password' => Hash::make('air123'), // gunakan Hash::make atau bcrypt()
        ]);
    }
}
