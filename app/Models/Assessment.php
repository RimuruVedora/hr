<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'type',
        'time_limit',
        'passing_score',
        'status',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function competencies()
    {
        return $this->belongsToMany(Competency::class, 'assessment_competency');
    }

    public function questions()
    {
        return $this->hasMany(AssessmentQuestion::class);
    }
}
