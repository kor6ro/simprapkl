// database/migrations/xxxx_xx_xx_xxxxxx_create_kriteria_penilaian_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kriteria_penilaian', function (Blueprint $table) {
            $table->id();
            $table->string('nama_variabel')->comment('Label yang ditampilkan di form, cth: "Kerjasama dalam Tim"');
            $table->string('kode_variabel')->unique()->comment('Kode unik untuk name attribute di form, cth: "kerjasama"');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kriteria_penilaian');
    }
};
