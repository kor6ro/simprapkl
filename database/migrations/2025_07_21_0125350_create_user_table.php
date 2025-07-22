<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::create("user", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("username");
            $table->string("email");
            $table->string("password");
            $table->boolean("validasi");
            $table->string("role")->default("siswa");
            $table->string("alamat");
            $table->foreignId("sekolah_id")->references("id")->on("sekolah");
            $table->foreignId("group_id")->references("id")->on("group");
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
        Schema::dropIfExists("user");
    }
}
