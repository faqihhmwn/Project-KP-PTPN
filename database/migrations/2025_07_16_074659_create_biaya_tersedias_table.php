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
        Schema::create('biaya_tersedias', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            // PENTING: Tambahkan ->nullable() di sini dan ubah onDelete
            $table->foreignId('kategori_biaya_id')->nullable()->constrained('kategori_biayas')->onDelete('set null');
            $table->decimal('total_tersedia', 15, 2); // Nama kolom Anda
            $table->timestamps();

            // PENTING: Tambahkan unique constraint untuk mencegah duplikasi
            $table->unique(['tahun', 'kategori_biaya_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biaya_tersedias');
    }
};