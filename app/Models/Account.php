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
    ];

    protected $hidden = [
        'password',
    ];
}
