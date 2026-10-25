<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZatcaSubmissionReconciliation extends Model
{
    protected $fillable = [
        'submission_id',
        'outcome',
        'previous_status',
        'new_status',
        'previous_environment',
        'new_environment',
        'target_environment',
        'previous_status_code',
        'previous_response',
        'evidence_type',
        'evidence_reference',
        'notes',
        'reconciled_by',
        'reconciled_at',
    ];

    protected function casts(): array
    {
        return [
            'previous_status_code' => 'integer',
            'reconciled_at' => 'datetime',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(ZatcaInvoiceSubmission::class, 'submission_id');
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }
}
