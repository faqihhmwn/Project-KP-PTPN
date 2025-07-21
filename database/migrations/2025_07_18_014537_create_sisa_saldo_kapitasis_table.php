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
        Schema::create('sisa_saldo_kapitasis', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun');
            $table->foreignId('bulan_id')->nullable()->constrained('bulans')->onDelete('set null');
            $table->bigInteger('saldo_awal_tahun')->nullable(); // hanya untuk bulan_id null
            $table->bigInteger('sisa_saldo')->nullable();        // hasil akhir dari perhitungan

            $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sisa_saldo_kapitasis');
    }
};
