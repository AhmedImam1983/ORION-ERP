<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchScope implements Scope
{
    public function __construct(
        private bool $includeUnassigned = false,
        private ?bool $runningInConsole = null
    ) {
    }

    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->check()) {
            $isConsole = $this->runningInConsole
                ?? app()->runningInConsole();

            if ($isConsole) {
                return;
            }

            $builder->whereRaw('1 = 0');

            return;
        }

        $user = auth()->user();

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return;
        }

        if (empty($user->branch_id)) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $branchColumn = $model->qualifyColumn('branch_id');

        if ($this->includeUnassigned) {
            $builder->where(
                function (Builder $query) use ($branchColumn, $user): void {
                    $query->where($branchColumn, $user->branch_id)
                        ->orWhereNull($branchColumn);
                }
            );

            return;
        }

        $builder->where($branchColumn, $user->branch_id);
    }
}