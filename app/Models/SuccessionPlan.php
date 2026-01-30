<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuccessionPlan extends Model
{
    protected $fillable = [
        'employee_id',
        'target_role_id',
        'department_id',
        'status',
        'readiness',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function targetRole()
    {
        return $this->belongsTo(JobRole::class, 'target_role_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
