<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSekolahTable extends Migration
{
    public function up()
    {
        Schema::create('sekolah', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('logo')->nullable(); // path logo sekolah
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sekolah');
    }
}
