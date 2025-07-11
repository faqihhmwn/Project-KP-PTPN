<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\UnitSeeder; 
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Jalankan seeder unit
        $this->call(UnitSeeder::class);

        // Buat user contoh
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
