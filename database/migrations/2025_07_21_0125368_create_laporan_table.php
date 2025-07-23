<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLaporanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::create("laporan", function (Blueprint $table) {
            $table->id();
            $table->string("jenis_laporan");
            $table->string("hasil_capaian");
            $table->foreignId("user_id")->references("id")->on("user");
            $table
                ->foreignId("laporan_gambar_id")
                ->references("id")
                ->on("laporan_gambar");
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("laporan");
    }
}
