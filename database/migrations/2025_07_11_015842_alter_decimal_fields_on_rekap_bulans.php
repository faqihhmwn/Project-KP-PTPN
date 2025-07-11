<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rekap_bulans', function (Blueprint $table) {
        $fields = [
            'gol_3_4', 'gol_1_2', 'kampanye', 'honor',
            'pens_3_4', 'pens_1_2', 'direksi', 'dekom',
            'pengacara', 'transport', 'hiperkes', 'total'
        ];

        foreach ($fields as $field) {
            $table->decimal($field, 15, 2)->change();
        }
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekap_bulans', function (Blueprint $table) {
        $fields = [
            'gol_3_4', 'gol_1_2', 'kampanye', 'honor',
            'pens_3_4', 'pens_1_2', 'direksi', 'dekom',
            'pengacara', 'transport', 'hiperkes', 'total'
        ];

        foreach ($fields as $field) {
            $table->decimal($field, 8, 2)->change(); // balik ke awal jika rollback
        }
    });
    }
};
