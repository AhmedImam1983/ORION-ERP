<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id', 'date', 'check_in', 'check_out',
        'working_hours', 'overtime_hours', 'late_minutes', 'status', 'notes',
    ];

    protected $casts = [
        'date'          => 'date',
        'working_hours' => 'decimal:2',
        'overtime_hours'=> 'decimal:2',
        'late_minutes'  => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}