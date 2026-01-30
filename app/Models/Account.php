<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Account extends Authenticatable
{
    use Notifiable;

    protected $table = 'accounts';
    protected $primaryKey = 'Login_ID';
    public $timestamps = false;

    protected $fillable = [
        'email',
        'password',
        'Account_Type',
        'auth_code',
        'department_id',
        'job_role_id',
    ];

    protected $hidden = [
        'password',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function jobRole()
    {
        return $this->belongsTo(JobRole::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'account_id', 'Login_ID');
    }
}
