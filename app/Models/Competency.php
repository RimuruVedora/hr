<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competency extends Model
{
    protected $table = 'competencies';
    protected $fillable = [
        'name',
        'category',
        'scope',
        'proficiency',
        'weight',
        'status',
        'desc',
    ];
}
