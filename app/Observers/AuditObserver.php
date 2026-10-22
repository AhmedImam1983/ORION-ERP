<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    // موديلات تشغيلية عالية الضوضاء فقط لا نحتاجها في سجل المراجعة الأساسي.
    // IMPORTANT: StockMovement لم يعد مستبعداً لأنه جزء أساسي من الـ audit trail المالي/المخزني.
    private array $excluded = [
        \App\Models\AuditLog::class,
        \App\Models\PosSessionItem::class,
        \App\Models\Attendance::class,
    ];

    // تجاهل timestamps والحقول السرية فقط.
    // POS totals لم تعد مستبعدة لأنها مهمة لمراجعة الجلسة ومطابقة Cash/Card/Wallet/Refunds.
    private array $ignoredFields = [
        'updated_at', 'created_at', 'deleted_at',
        'remember_token', 'password', 'password_confirmation',
    ];

    public function created(Model $model): void
    {
        if ($this->shouldSkip($model)) return;

        AuditLog::log('created', $model, [], $this->getValues($model));
    }

    public function updated(Model $model): void
    {
        if ($this->shouldSkip($model)) return;

        $dirty    = $model->getDirty();
        $oldVals  = [];
        $newVals  = [];

        foreach ($dirty as $field => $newVal) {
            if (in_array($field, $this->ignoredFields)) continue;
            $oldVals[$field] = $model->getOriginal($field);
            $newVals[$field] = $newVal;
        }

        if (empty($newVals)) return;

        AuditLog::log('updated', $model, $oldVals, $newVals);
    }

    public function deleted(Model $model): void
    {
        if ($this->shouldSkip($model)) return;
        AuditLog::log('deleted', $model, $this->getValues($model));
    }

    public function restored(Model $model): void
    {
        if ($this->shouldSkip($model)) return;
        AuditLog::log('restored', $model);
    }

    private function shouldSkip(Model $model): bool
    {
        return in_array(get_class($model), $this->excluded);
    }

    private function getValues(Model $model): array
    {
        return collect($model->getAttributes())
            ->except($this->ignoredFields)
            ->except(['password', 'remember_token'])
            ->toArray();
    }
}