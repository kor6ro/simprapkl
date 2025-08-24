<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // ..._create_penilaian_tables.php
    public function up(): void
    {
        Schema::create('penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('user');
            $table->foreignId('penilai_id')->constrained('user');
            $table->date('tanggal_penilaian');
            $table->text('komentar_saran')->nullable();
            $table->decimal('nilai_rata_rata', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('detail_penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_id')->constrained('penilaian')->onDelete('cascade');
            $table->string('variabel');
            $table->integer('nilai');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_penilaian');
        Schema::dropIfExists('penilaian');
    }
};
