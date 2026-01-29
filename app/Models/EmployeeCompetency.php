<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCompetency extends Model
{
    protected $fillable = [
        'employee_id',
        'competency_id',
        'current_proficiency',
        'target_proficiency',
        'priority',
        'status',
    ];

    protected static function booted()
    {
        // Removed auto-calculation of gap_score as column is removed
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function competency()
    {
        return $this->belongsTo(Competency::class);
    }
}
