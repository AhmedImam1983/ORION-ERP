<?php

namespace App\Filament\Admin\Resources\Branches\Pages;

use App\Filament\Admin\Resources\Branches\BranchResource;
use App\Models\Branch;
use Filament\Resources\Pages\ListRecords;

class ListBranches extends ListRecords
{
    protected static string $resource = BranchResource::class;

    protected string $view = 'filament.admin.resources.branches.pages.list-branches';

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getBranchStats(): array
    {
        $branches = Branch::query()
            ->withCount('invoices')
            ->get();

        $mainBranch = $branches->firstWhere('is_main', true);

        return [
            'total' => $branches->count(),
            'active' => $branches->where('is_active', true)->count(),
            'invoices' => (int) $branches->sum('invoices_count'),
            'main_name' => $mainBranch?->name ?? 'غير محدد',
        ];
    }
}
