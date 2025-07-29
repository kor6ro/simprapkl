<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresensiJenisTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presensi_jenis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->boolean('butuh_bukti')->default(false);
            $table->boolean('otomatis')->default(false); // untuk status otomatis (telat, bolos)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_jenis');
    }
};
