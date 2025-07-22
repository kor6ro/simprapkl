<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskBreakdown extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = "task_breakdown";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ["file", "created_at", "updated_at"];
}
