<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresensiSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('presensi_setting', function (Blueprint $table) {
            $table->id();
            $table->time('pagi_mulai');
            $table->time('pagi_selesai');
            $table->time('sore_mulai');
            $table->time('sore_selesai');
            $table->boolean('is_active')->default(false); // hanya 1 setting aktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("presensi_setting");
    }
}
