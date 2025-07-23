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
        Schema::create('dana_masuks', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->foreignId('bulan_id')->nullable()->constrained('bulans')->onDelete('set null');
            $table->bigInteger('total_dana_masuk'); // Nama kolom Anda
            $table->timestamps();

            // PENTING: Tambahkan unique constraint untuk mencegah duplikasi
            $table->unique(['tahun', 'bulan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dana_masuks');
    }
};