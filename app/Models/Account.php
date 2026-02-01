<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Account extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $table = 'accounts';
    protected $primaryKey = 'Login_ID';
    public $timestamps = false;

    protected $fillable = [
        'User_ID',
        'Name',
        'Email',
        'Password',
        'Account_Type',
        'auth_code',
        'department_id',
        'job_role_id',
        'path_img',
        'position', // Added position
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
