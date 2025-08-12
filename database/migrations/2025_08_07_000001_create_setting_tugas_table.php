<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_tugas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ketua_id');
            $table->enum('divisi', ['teknisi', 'sales']);
            $table->text('deskripsi')->nullable(); // buat nullable jika mau keep
            $table->date('tanggal');
            $table->timestamps();
            
            $table->foreign('ketua_id')->references('id')->on('user')->onDelete('cascade');
            $table->unique(['ketua_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_tugas');
    }
};