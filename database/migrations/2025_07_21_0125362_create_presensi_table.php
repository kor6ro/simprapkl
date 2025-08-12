<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresensiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('presensi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');

            $table->date('tanggal_presensi');
            $table->string('bukti_foto')->nullable();
            $table->enum('sesi', ['pagi', 'sore']);
            $table->time('jam_presensi')->nullable();
            $table->text('keterangan')->nullable();

            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->string('requested_status', 50)->nullable();
            $table->text('approval_notes')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            // Add foreign key for approved_by
            $table->foreign('approved_by')->references('id')->on('user')->onDelete('set null');

            $table->timestamps();

            // Unique constraint to prevent double attendance for same session
            $table->unique(['user_id', 'tanggal_presensi', 'sesi']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('presensi');
    }
}
