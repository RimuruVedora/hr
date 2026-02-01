<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EssRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'request_category',
        'request_type',
        'details',
        'status',
        'attachment_path',
        'admin_remarks',
        'response_file_name',
        'response_file_mime'
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
