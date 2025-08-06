<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresensiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('presensi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');


            $table->date('tanggal_presensi');
            $table->string('bukti_foto')->nullable();
            $table->enum('sesi', ['pagi', 'sore']);
            $table->time('jam_presensi')->nullable();
            $table->text('keterangan')->nullable();


            $table->timestamps();

            $table->unique(['user_id', 'tanggal_presensi', 'sesi']); // biar gak bisa presensi dua kali sesi sama
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("presensi");
    }
}
