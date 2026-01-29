<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'title',
        'level',
        'department_id',
        'category',
        'picture',
        'material_pdf',
        'duration',
        'description',
        'status',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class); // Assuming Department model exists or will be created
    }

    public function competencies()
    {
        return $this->belongsToMany(Competency::class, 'course_competency');
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function trainings()
    {
        return $this->hasMany(Training::class);
    }
}
