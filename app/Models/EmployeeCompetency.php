<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCompetency extends Model
{
    protected $fillable = [
        'user_id',
        'competency_id',
        'current_proficiency',
        'target_proficiency',
        'gap_score',
        'priority',
        'status',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'user_id', 'User_ID');
    }

    public function competency()
    {
        return $this->belongsTo(Competency::class);
    }
}
