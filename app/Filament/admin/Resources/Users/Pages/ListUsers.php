<?php

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected string $view = 'filament.admin.resources.users.pages.list-users';

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getUserStats(): array
    {
        $query = User::query();

        return [
            'total' => (clone $query)->count(),
            'managers' => (clone $query)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['super_admin', 'admin']))
                ->count(),
            'cashiers' => (clone $query)
                ->whereHas('roles', fn ($q) => $q->where('name', 'cashier'))
                ->count(),
            'assigned_branch' => (clone $query)->whereNotNull('branch_id')->count(),
        ];
    }
}
