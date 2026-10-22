<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installment extends Model
{
    protected $fillable = [
        'invoice_id', 'customer_id', 'installment_number',
        'amount', 'paid_amount', 'due_date', 'paid_date',
        'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date'    => 'date',
        'paid_date'   => 'date',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRemainingAttribute(): float
    {
        return max(0, $this->amount - $this->paid_amount);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'  => '⏳ قيد الانتظار',
            'paid'     => '✅ مدفوع',
            'overdue'  => '🔴 متأخر',
            'partial'  => '🟡 جزئي',
            default    => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'paid'    => '#4ade80',
            'overdue' => '#f87171',
            'partial' => '#fbbf24',
            default   => '#94a3b8',
        };
    }

    // تحديث الحالة تلقائياً
    public function updateStatus(): void
    {
        if ($this->paid_amount >= $this->amount) {
            $this->status = 'paid';
            $this->paid_date = $this->paid_date ?? today();
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        } elseif ($this->due_date < today()) {
            $this->status = 'overdue';
        } else {
            $this->status = 'pending';
        }
        $this->save();

        // تحديث الفاتورة
        $this->invoice->updatePaidAmount();
    }

    // جدول أقساط تلقائي
    public static function generateSchedule(Invoice $invoice, int $count, string $startDate, string $frequency = 'monthly'): void
    {
        $invoice->installments()->delete();

        $amount    = $invoice->total;
        $perInst   = round($amount / $count, 2);
        $remainder = $amount - ($perInst * ($count - 1));
        $date      = \Carbon\Carbon::parse($startDate);

        for ($i = 1; $i <= $count; $i++) {
            self::create([
                'invoice_id'          => $invoice->id,
                'customer_id'         => $invoice->customer_id,
                'installment_number'  => $i,
                'amount'              => $i === $count ? $remainder : $perInst,
                'paid_amount'         => 0,
                'due_date'            => $date->copy(),
                'status'              => 'pending',
                'created_by'          => auth()->id() ?? 1,
            ]);

            match($frequency) {
                'weekly'    => $date->addWeek(),
                'biweekly'  => $date->addWeeks(2),
                'monthly'   => $date->addMonth(),
                'quarterly' => $date->addMonths(3),
                default     => $date->addMonth(),
            };
        }

        $invoice->update(['payment_type' => 'installment']);
    }
}