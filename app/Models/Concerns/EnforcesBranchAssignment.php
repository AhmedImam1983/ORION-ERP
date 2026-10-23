<?php

namespace App\Models\Concerns;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

trait EnforcesBranchAssignment
{
    protected static function bootEnforcesBranchAssignment(): void
    {
        static::creating(function (Model $model): void {
            if (! auth()->check()) {
                if (app()->runningInConsole()) {
                    return;
                }

                throw new AuthorizationException(
                    'يجب تسجيل الدخول قبل إنشاء سجل.'
                );
            }

            $user = auth()->user();

            if ($user->hasAnyRole(['super_admin', 'admin'])) {
                return;
            }

            $branchId = (int) ($user->branch_id ?? 0);

            if ($branchId <= 0) {
                throw new AuthorizationException(
                    'لا يمكن إنشاء سجل لمستخدم غير مرتبط بفرع.'
                );
            }

            $recordBranchId = $model->getAttribute('branch_id');

            if ($recordBranchId === null || $recordBranchId === '') {
                $model->setAttribute('branch_id', $branchId);

                return;
            }

            if ((int) $recordBranchId !== $branchId) {
                throw new AuthorizationException(
                    'لا يمكنك إنشاء سجل تابع لفرع آخر.'
                );
            }
        });

        static::updating(function (Model $model): void {
            if (! auth()->check()) {
                if (app()->runningInConsole()) {
                    return;
                }

                throw new AuthorizationException(
                    'يجب تسجيل الدخول قبل تعديل سجل.'
                );
            }

            $user = auth()->user();

            if ($user->hasAnyRole(['super_admin', 'admin'])) {
                return;
            }

            $branchId = (int) ($user->branch_id ?? 0);
            $originalBranchId = (int) $model->getRawOriginal('branch_id');
            $newBranchId = (int) $model->getAttribute('branch_id');

            if (
                $branchId <= 0
                || $originalBranchId !== $branchId
                || $newBranchId !== $branchId
            ) {
                throw new AuthorizationException(
                    'لا يمكنك تعديل سجل تابع لفرع آخر أو نقل السجل إلى فرع آخر.'
                );
            }
        });

        static::deleting(function (Model $model): void {
            if (! auth()->check()) {
                if (app()->runningInConsole()) {
                    return;
                }

                throw new AuthorizationException(
                    'يجب تسجيل الدخول قبل حذف سجل.'
                );
            }

            $user = auth()->user();

            if ($user->hasAnyRole(['super_admin', 'admin'])) {
                return;
            }

            $branchId = (int) ($user->branch_id ?? 0);
            $recordBranchId = (int) $model->getRawOriginal('branch_id');

            if ($branchId <= 0 || $recordBranchId !== $branchId) {
                throw new AuthorizationException(
                    'لا يمكنك حذف سجل تابع لفرع آخر أو غير مرتبط بفرعك.'
                );
            }
        });
    }
}