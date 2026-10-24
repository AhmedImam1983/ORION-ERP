<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZatcaInvoiceSubmission extends Model
{
    public const ENVIRONMENT_TEST = 'test';
    public const ENVIRONMENT_PRODUCTION = 'production';
    public const ENVIRONMENT_UNKNOWN = 'unknown';

    public const SENDING_MODE_SIMULATION = 'simulation';
    public const SENDING_MODE_COMPLIANCE = 'compliance';
    public const SENDING_MODE_PRODUCTION = 'production';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'invoice_id',
        'environment',
        'status',
        'attempts',
        'status_code',
        'uuid',
        'invoice_hash',
        'error',
        'response',
        'last_attempt_at',
        'sent_at',
        'sending_mode',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'status_code' => 'integer',
            'last_attempt_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
