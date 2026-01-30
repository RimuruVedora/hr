<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Employee extends Model
{
    protected $fillable = [
        'employee_id',
        'account_id',
        'first_name',
        'last_name',
        'email',
        'department',
        'job_role_id',
        'status',
        'date_hired',
    ];

    /**
     * Get the employee's full name.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['first_name'] . ' ' . $attributes['last_name'],
        );
    }

    public function jobRole()
    {
        return $this->belongsTo(JobRole::class);
    }

    public function competencies()
    {
        return $this->hasMany(EmployeeCompetency::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'Login_ID');
    }

    public function trainingParticipants()
    {
        return $this->hasMany(TrainingParticipant::class);
    }

    public function employeeAssessments()
    {
        return $this->hasMany(EmployeeAssessment::class);
    }
}
