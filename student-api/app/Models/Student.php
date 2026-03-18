<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    // These are the columns that can be filled via API requests
    protected $fillable = [
        'name',
        'email',
        'course',
        'age',
    ];
}