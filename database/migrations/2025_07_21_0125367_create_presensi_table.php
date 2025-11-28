<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->foreignId('presensi_status_id')->nullable()->constrained('presensi_status')->onDelete('set null');
            $table->dateTime('presensi_at')->nullable()->comment('Menyimpan tanggal dan waktu presensi dalam satu kolom');
            
            $table->enum('sesi', ['pagi', 'sore']);
            $table->string('status', 50)->comment('Denormalisasi dari tabel status untuk mempermudah pembacaan');
            
            $table->string('bukti_foto')->nullable();
            $table->text('keterangan')->nullable();

            // Kolom untuk alur approval
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->string('requested_status', 50)->nullable();
            $table->text('approval_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('user')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->index('presensi_at'); // Tambahkan index pada kolom baru untuk performa query
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};