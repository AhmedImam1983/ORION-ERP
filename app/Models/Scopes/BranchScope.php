<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // الأدمن والسوبر أدمن بيشوفوا كل حاجة
        if (!auth()->check()) return;

        $user = auth()->user();

        // السوبر أدمن والأدمن بيشوفوا كل الفروع
        if ($user->hasAnyRole(['super_admin', 'admin'])) return;

        // باقي المستخدمين بيشوفوا فرعهم بس
        if ($user->branch_id) {
            $builder->where($model->getTable() . '.branch_id', $user->branch_id);
        }
    }
}