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
 // 2025_08_09_0125368_create_laporan_table.php

public function up(): void
{
    Schema::create('laporan', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tim_id')->constrained('tim')->onDelete('cascade');
        $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
        $table->foreignId('jenis_kegiatan_id')->constrained('jenis_kegiatan')->onDelete('cascade');
        $table->text('deskripsi_kegiatan');
        $table->string('bukti_foto')->nullable();
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        $table->text('feedback')->nullable();
        
        // [TAMBAHKAN BARIS INI]
        $table->foreignId('approver_id')->nullable()->constrained('user')->onDelete('set null');

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
        Schema::dropIfExists("laporan");
    }
}
