<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskBreakDown extends Model
{
    use HasFactory;

    protected $table = "task_break_down";

    protected $fillable = ["nama", "file_upload", "created_at", "updated_at"];
}
