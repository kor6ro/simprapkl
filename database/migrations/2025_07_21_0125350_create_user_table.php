<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    /**
     * Jalankan migrasi.
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
            $table->string('photo_profile')->nullable(); // Menambahkan kolom foto profil
            $table->boolean("validasi")->default(0);
            $table->string("alamat");
            $table->foreignId("sekolah_id")->nullable()->constrained("sekolah")->onDelete('set null');
            $table->foreignId("program_keahlian_id")
                ->nullable()
                ->constrained("program_keahlian")
                ->onDelete('set null'); // Menambahkan relasi ke program keahlian
            $table->foreignId("group_id")->references("id")->on("group");
            $table->string("id_pkl")->nullable();
            $table->foreignId('penilai_id')
                ->nullable()
                ->constrained('user')
                ->onDelete('set null');
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Balikkan migrasi.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("user");
    }
}