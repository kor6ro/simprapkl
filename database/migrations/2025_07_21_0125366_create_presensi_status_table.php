<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Buat tabel presensi_status
        Schema::create('presensi_status', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('status', 50);
            $table->string('color', 20)->default('light');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // 3. Update tabel presensi
        Schema::table('presensi', function (Blueprint $table) {
            // Pastikan kolom status ada
            if (!Schema::hasColumn('presensi', 'status')) {
                $table->string('status', 50)->nullable()->after('keterangan');
            }

            // Tambah foreign key ke presensi_status (opsional)
            $table->unsignedBigInteger('presensi_status_id')->nullable()->after('status');
            $table->foreign('presensi_status_id')->references('id')->on('presensi_status')->onDelete('set null');

            // Pastikan kolom bukti_foto konsisten
            if (Schema::hasColumn('presensi', 'bukti') && !Schema::hasColumn('presensi', 'bukti_foto')) {
                $table->renameColumn('bukti', 'bukti_foto');
            }
        });

        // 4. Tambah kolom toleransi_telat di presensi_setting jika belum ada
        Schema::table('presensi_setting', function (Blueprint $table) {
            if (!Schema::hasColumn('presensi_setting', 'toleransi_telat')) {
                $table->integer('toleransi_telat')->default(10)->after('sore_selesai');
            }
        });
    }

    public function down()
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->dropForeign(['presensi_status_id']);
            $table->dropColumn('presensi_status_id');
        });

        Schema::dropIfExists('presensi_status');

        Schema::table('presensi_setting', function (Blueprint $table) {
            $table->dropColumn('toleransi_telat');
        });
    }
};
