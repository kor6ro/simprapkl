<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramKeahlian extends Model
{

    protected $table = 'program_keahlian'; // Add this line
    protected $fillable = ['nama']; 
}
