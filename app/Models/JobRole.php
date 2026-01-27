<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobRole extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'weighting'];

    public function competencies()
    {
        return $this->belongsToMany(Competency::class, 'job_role_competency');
    }
}
