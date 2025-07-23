<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskBreakDownTable extends Migration
{
    public function up()
    {
        Schema::create("task_break_down", function (Blueprint $table) {
            $table->id();
            $table->string("nama")->nullable();
            $table->string("file_upload")->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists("task_break_down");
    }
}
