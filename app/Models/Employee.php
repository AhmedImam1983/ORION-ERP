<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'name_en', 'national_id', 'phone', 'email',
        'address', 'department', 'job_title', 'employment_type',
        'hire_date', 'basic_salary', 'allowances', 'working_hours',
        'fingerprint_id', 'status', 'image', 'notes',
    ];

    protected $casts = [
        'hire_date'    => 'date',
        'basic_salary' => 'decimal:2',
        'allowances'   => 'decimal:2',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}