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
        // Nama tabel disesuaikan menjadi singular: 'task_breakdown'
        Schema::create('task_breakdown', function (Blueprint $table) {
            $table->id();
            
            // [BARU] Kolom untuk tipe tugas (file atau teks)
            $table->enum('tipe', ['file', 'teks'])->default('file');
            
            // [DIUBAH] Kolom untuk path file, sekarang bisa kosong (nullable)
            $table->string('task_breakdown')->nullable(); 
            
            // [BARU] Kolom untuk menyimpan deskripsi teks (bisa null jika tipenya file)
            $table->text('deskripsi_tugas')->nullable();
            
            $table->date('applicable_date'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_breakdown');
    }
};