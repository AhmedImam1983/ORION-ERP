<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salary extends Model
{
    protected $fillable = [
        'employee_id', 'month', 'year', 'basic_salary', 'allowances',
        'overtime_hours', 'overtime_amount', 'bonus', 'deductions',
        'absence_deduction', 'late_deduction', 'insurance_deduction',
        'tax_deduction', 'net_salary', 'status', 'payment_date',
        'payment_method', 'notes', 'created_by',
    ];

    protected $casts = [
        'basic_salary'       => 'decimal:2',
        'allowances'         => 'decimal:2',
        'overtime_hours'     => 'decimal:2',
        'overtime_amount'    => 'decimal:2',
        'bonus'              => 'decimal:2',
        'deductions'         => 'decimal:2',
        'absence_deduction'  => 'decimal:2',
        'late_deduction'     => 'decimal:2',
        'insurance_deduction'=> 'decimal:2',
        'tax_deduction'      => 'decimal:2',
        'net_salary'         => 'decimal:2',
        'payment_date'       => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function calculateNet(): float
    {
        $gross = $this->basic_salary + $this->allowances + $this->overtime_amount + $this->bonus;
        $deductions = $this->deductions + $this->absence_deduction + $this->late_deduction
            + $this->insurance_deduction + $this->tax_deduction;
        return $gross - $deductions;
    }

    public function getMonthNameAttribute(): string
    {
        $months = [
            1=>'يناير', 2=>'فبراير', 3=>'مارس', 4=>'أبريل',
            5=>'مايو', 6=>'يونيو', 7=>'يوليو', 8=>'أغسطس',
            9=>'سبتمبر', 10=>'أكتوبر', 11=>'نوفمبر', 12=>'ديسمبر',
        ];
        return $months[$this->month] ?? '';
    }
}