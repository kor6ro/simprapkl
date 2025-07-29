<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresensiGambarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('presensi_gambar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('presensi_id');
            $table->string('bukti');
            $table->timestamps();

            $table->foreign('presensi_id')->references('id')->on('presensi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("presensi_gambar");
    }
}
