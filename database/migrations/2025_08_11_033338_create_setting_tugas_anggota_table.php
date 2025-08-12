<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setting_tugas_anggota', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('setting_tugas_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('setting_tugas_id')->references('id')->on('setting_tugas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');
            
            $table->unique(['setting_tugas_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_tugas_anggota');
    }
};