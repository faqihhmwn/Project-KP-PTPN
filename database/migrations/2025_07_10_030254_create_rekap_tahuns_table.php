<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekap_tahuns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->year('tahun');
            $table->decimal('gol_3_4')->nullable();
            $table->decimal('gol_1_2')->nullable();
            $table->decimal('kampanye')->nullable();
            $table->decimal('honor')->nullable();
            $table->decimal('pens_3_4')->nullable();
            $table->decimal('pens_1_2')->nullable();
            $table->decimal('direksi')->nullable();
            $table->decimal('dekom')->nullable();
            $table->decimal('pengacara')->nullable();
            $table->decimal('transport')->nullable();
            $table->decimal('hiperkes')->nullable();
            $table->decimal('total')->nullable();
            $table->timestamps();
        });
    }
    
    public function down(): void {
        Schema::dropIfExists('rekap_tahuns');
    }
};
