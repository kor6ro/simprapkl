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
        Schema::create('presensi_setting', function (Blueprint $table) {
            $table->id();
            // PERBAIKAN: Jadikan kolom waktu nullable agar bisa dikosongkan
            $table->time('pagi_mulai')->nullable();
            $table->time('pagi_selesai')->nullable();
            $table->time('sore_mulai')->nullable();
            $table->time('sore_selesai')->nullable();
            // Kolom toleransi tetap wajib ada
            $table->unsignedInteger('toleransi_telat')->nullable()->comment('Toleransi keterlambatan dalam menit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_setting');
    }
};