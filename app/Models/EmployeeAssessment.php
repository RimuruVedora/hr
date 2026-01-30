<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAssessment extends Model
{
    protected $fillable = [
        'employee_id',
        'training_id',
        'assessment_id',
        'score',
        'total_items',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
