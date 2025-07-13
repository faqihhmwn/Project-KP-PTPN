<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekap_bpjs_iurans', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->foreignId('bulan_id')->nullable()->constrained('bulans')->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('cascade');
            $table->foreignId('kategori_biaya_id')->nullable()->constrained('kategori_biayas')->onDelete('cascade');
            $table->decimal('total_iuran_bpjs')->nullable();
            // Tiga boolean untuk menandakan cakupan
            $table->boolean('cakupan_semua_unit')->default(false);
            $table->boolean('cakupan_semua_bulan')->default(false);
            $table->boolean('cakupan_semua_kategori')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_bpjs_iurans');
    }
};
