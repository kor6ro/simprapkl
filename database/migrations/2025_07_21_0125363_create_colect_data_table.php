<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColectDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::create("colect_data", function (Blueprint $table) {
            $table->id();
            $table->date("tanggal");
            $table->string("nama_cus");
            $table->string("no_telp");
            $table->text("alamat_cus");
            $table->string("provider_sekarang");
            $table->text("kelebihan");
            $table->text("kekurangan");
            $table->string("serlok");
            $table->string("gambar_foto");
            $table->foreignId("user_id")->references("id")->on("user");
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
        Schema::dropIfExists("colect_data");
    }
}
