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
     * ZATCA documents HTTP 200 as accepted without warnings and HTTP 202 as
     * accepted with warnings. These status codes mean the invoice was accepted.
     */
    public static function isAcceptedApiStatus(?int $statusCode): bool
    {
        return in_array($statusCode, [200, 202], true);
    }

    /**
     * True when a response does not establish whether ZATCA accepted the invoice.
     * Timeouts, conflicts, rate limits, server errors, and contradictory statuses
     * must not trigger an automatic retry.
     */
    public static function isAmbiguousApiResponse(?int $statusCode, bool $isSuccess): bool
    {
        if (self::isAcceptedApiStatus($statusCode)) {
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

        // A failed row with an accepted HTTP code contradicts the saved status.
        // Reconcile it before retrying, promoting it to SENT only when the stored
        // response itself contains the matching accepted status code.
        if ($this->hasAcceptedStoredResponse()) {
            return true;
        }

        if ($statusCode !== null) {
            if (self::isAcceptedApiStatus($statusCode)) {
                return true;
            }

            // For example, 401 is a definitive rejection even if the error text
            // mentions the reporting step. Transient/server errors stay ambiguous.
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

    /**
     * Detect a legacy row marked failed even though its persisted API status says
     * the invoice was accepted. Only matching stored/database status codes qualify.
     */
    public function hasAcceptedStoredResponse(): bool
    {
        if ($this->status !== self::STATUS_FAILED || $this->response === null) {
            return false;
        }

        $payload = json_decode((string) $this->response, true);
        if (!is_array($payload)
            || !isset($payload['status_code'])
            || !is_numeric($payload['status_code'])) {
            return false;
        }

        $responseStatusCode = (int) $payload['status_code'];
        $storedStatusCode = $this->status_code === null ? null : (int) $this->status_code;

        if ($storedStatusCode !== null && $storedStatusCode !== $responseStatusCode) {
            return false;
        }

        return self::isAcceptedApiStatus($responseStatusCode);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
