<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'user_name', 'event',
        'auditable_type', 'auditable_id', 'auditable_label',
        'old_values', 'new_values',
        'ip_address', 'user_agent', 'url',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(
        string $event,
        ?Model $model = null,
        array $oldValues = [],
        array $newValues = []
    ): void {
        try {
            $user = auth()->user();
            static::create([
                'user_id'         => $user?->id,
                'user_name'       => $user?->name ?? 'System',
                'event'           => $event,
                'auditable_type'  => $model ? get_class($model) : null,
                'auditable_id'    => $model?->getKey(),
                'auditable_label' => $model ? static::getLabel($model) : null,
                'old_values'      => !empty($oldValues) ? $oldValues : null,
                'new_values'      => !empty($newValues) ? $newValues : null,
                'ip_address'      => request()->ip(),
                'user_agent'      => substr(request()->userAgent() ?? '', 0, 255),
                'url'             => substr(request()->fullUrl(), 0, 500),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AuditLog Error: ' . $e->getMessage());
        }
    }

    private static function getLabel(Model $model): string
    {
        return match(true) {
            isset($model->invoice_number) => $model->invoice_number,
            isset($model->name)           => $model->name,
            isset($model->code)           => $model->code,
            isset($model->session_number) => $model->session_number,
            isset($model->entry_number)   => $model->entry_number,
            default                       => class_basename($model) . ' #' . $model->getKey(),
        };
    }

    public function getEventLabelAttribute(): string
    {
        return match($this->event) {
            'created'  => '✅ إنشاء',
            'updated'  => '✏️ تعديل',
            'deleted'  => '🗑️ حذف',
            'restored' => '♻️ استعادة',
            'login'    => '🔐 دخول',
            'logout'   => '🚪 خروج',
            default    => $this->event,
        };
    }

    public function getModelLabelAttribute(): string
    {
        return match($this->auditable_type) {
            'App\Models\Invoice'      => '🧾 فاتورة',
            'App\Models\Product'      => '📦 صنف',
            'App\Models\Customer'     => '👤 عميل',
            'App\Models\Supplier'     => '🚛 مورد',
            'App\Models\JournalEntry' => '📒 قيد',
            'App\Models\Employee'     => '👥 موظف',
            'App\Models\PosSession'   => '🖥️ جلسة كاشير',
            default                   => class_basename($this->auditable_type ?? ''),
        };
    }
}