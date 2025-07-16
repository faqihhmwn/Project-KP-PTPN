<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// database/migrations/xxxx_xx_xx_create_biaya_tersedia_table.php
    public function up()
    {
    Schema::create('biaya_tersedia', function (Blueprint $table) {
        $table->id();
        $table->integer('tahun');
        $table->unsignedBigInteger('kategori_biaya_id')->nullable(); // null = total semua kategori
        $table->bigInteger('jumlah')->default(0);
        $table->timestamps();

        $table->foreign('kategori_biaya_id')->references('id')->on('kategori_biayas')->onDelete('cascade');
        $table->unique(['kategori_biaya_id', 'tahun']); // mencegah duplikat input
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biaya_tersedia');
    }
};
