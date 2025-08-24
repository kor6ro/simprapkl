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
        Schema::create('tim', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ketua_id');
            
            // <-- PERUBAHAN UTAMA DI SINI
            $table->foreignId('divisi_id')->constrained('divisi')->onDelete('cascade');
            
            $table->date('tanggal');
            $table->timestamps();
            
            $table->foreign('ketua_id')->references('id')->on('user')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tim');
    }
};