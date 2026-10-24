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
    public const STATUS_UNCERTAIN = 'uncertain';

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

    /**
     * Normalize boolean-like values returned by the ZATCA COM integration.
     * COM/.NET may expose booleans as native bools, strings, or -1/0 values.
     * Unknown values remain null so callers can treat the outcome as uncertain.
     */
    public static function normalizeApiSuccessFlag(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            if ($value == 1.0 || $value == -1.0) {
                return true;
            }

            if ($value == 0.0) {
                return false;
            }

            return null;
        }

        if (!is_string($value)) {
            return null;
        }

        return match (strtolower(trim($value))) {
            'true', '1', '-1', 'yes', 'on' => true,
            'false', '0', 'no', 'off' => false,
            default => null,
        };
    }

    /**
     * True when the response cannot establish whether ZATCA accepted the invoice.
     * Only a successful 202 response is treated as a confirmed submission. Known
     * client-side 4xx rejection responses are definitive except timeout/conflict/rate-limit codes.
     */
    public static function isAmbiguousApiResponse(?int $statusCode, bool $isSuccess): bool
    {
        if ($isSuccess && $statusCode === 202) {
            return false;
        }

        if ($isSuccess) {
            return true;
        }

        if ($statusCode === null || $statusCode === 0) {
            return true;
        }

        if (in_array($statusCode, [408, 409, 425, 429], true) || $statusCode >= 500) {
            return true;
        }

        return !($statusCode >= 400 && $statusCode < 500);
    }

    /**
     * Detect failed records created by older code where the request may already
     * have reached ZATCA, so a retry must be blocked pending reconciliation.
     */
    public function hasAmbiguousOutcome(): bool
    {
        if ($this->status === self::STATUS_UNCERTAIN) {
            return true;
        }

        if ($this->status !== self::STATUS_FAILED) {
            return false;
        }

        $statusCode = $this->status_code === null
            ? null
            : (int) $this->status_code;

        // A known API status code takes precedence over text from a legacy error.
        // For example, 401 is a definitive rejection even if the error mentions
        // the reporting step. Timeouts, rate limits, conflicts and server errors
        // remain ambiguous and require reconciliation.
        if ($statusCode !== null) {
            return self::isAmbiguousApiResponse($statusCode, false);
        }

        // A stored response with no status code is still evidence that the old
        // implementation received a response; don't infer ambiguity from its step text.
        if ($this->response !== null) {
            return false;
        }

        $error = (string) ($this->error ?? '');
        foreach ([
            '[9b-compliance-invoice-api]',
            '[9d-call-reporting-api]',
            '[10-read-response]',
        ] as $apiStep) {
            if (str_contains($error, $apiStep)) {
                return true;
            }
        }

        return false;
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
