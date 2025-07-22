<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = "sekolah";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ["nama", "created_at", "updated_at"];

    public function user()
    {
        return $this->hasMany(User::class, "sekolah_id");
    }
}
