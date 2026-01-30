<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $fillable = [
        'title',
        'course_id',
        'start_date',
        'end_date',
        'capacity',
        'duration',
        'org_scope',
        'proficiency',
        'description',
        'status',
        'training_type',
        'assessment_id',
        'location',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function participants()
    {
        return $this->hasMany(TrainingParticipant::class);
    }
}
