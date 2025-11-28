<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 2025_08_07_000001_create_tim_table.php

public function up(): void
{
    // 1. Skema untuk tabel 'tim'
   Schema::create('tim', function (Blueprint $table) {
        $table->id();
        $table->foreignId('divisi_id')->constrained('divisi')->onDelete('cascade');
        $table->date('tanggal');
        $table->enum('status_approval', ['belum_selesai', 'tugas_selesai'])->default('belum_selesai');
        // Kolom approver_id dan feedback sudah dihapus dari sini
        $table->timestamps();
    });

    // 2. Skema untuk tabel pivot 'tim_ketua'
    Schema::create('tim_ketua', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tim_id')->constrained('tim')->onDelete('cascade');
        $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
        $table->unique(['tim_id', 'user_id']);
        $table->timestamps();
    });
}

    public function down(): void
    {
        // Urutan drop dibalik dari pembuatan
        Schema::dropIfExists('tim_ketua');
        Schema::dropIfExists('tim');
    }
};