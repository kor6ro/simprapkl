<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tim_anggota', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tim_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('tim_id')->references('id')->on('tim')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');

            $table->unique(['tim_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tim_anggota');
    }
};
