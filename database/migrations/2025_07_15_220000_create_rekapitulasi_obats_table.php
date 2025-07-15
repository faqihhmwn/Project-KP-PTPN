<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rekapitulasi_obats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('obat_id');
            $table->date('tanggal');
            $table->integer('stok_awal')->default(0);
            $table->integer('jumlah_keluar')->default(0);
            $table->integer('sisa_stok')->default(0);
            $table->integer('total_biaya')->default(0);
            $table->integer('bulan');
            $table->integer('tahun');
            $table->timestamps();
            $table->unique(['obat_id', 'tanggal'], 'rekapitulasi_obats_obat_id_tanggal_unique');
            $table->foreign('obat_id')->references('id')->on('obats')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekapitulasi_obats');
    }
};
