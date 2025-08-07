<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('siswa_divisi_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('user')->onDelete('cascade');
            $table->enum('divisi', ['teknisi', 'sales']);
            $table->date('tanggal');
            $table->timestamps();

            $table->unique(['siswa_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa_divisi_harian');
    }
};
