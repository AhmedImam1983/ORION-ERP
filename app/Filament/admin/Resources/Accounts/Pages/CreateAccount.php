<?php

namespace App\Filament\Admin\Resources\Accounts\Pages;

use App\Filament\Admin\Resources\Accounts\AccountResource;
use App\Models\Account;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateAccount extends CreateRecord
{
    protected static string $resource = AccountResource::class;
    protected string $view = 'filament.admin.resources.accounts.pages.create-account';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->normalizeHierarchy($data);
    }

    private function normalizeHierarchy(array $data): array
    {
        $parentId = $data['parent_id'] ?? null;

        if (!$parentId) {
            $data['parent_id'] = null;
            $data['level'] = 'main';

            return $data;
        }

        $parent = Account::find($parentId);

        if (!$parent) {
            throw ValidationException::withMessages([
                'data.parent_id' => 'الحساب الأب غير موجود.',
            ]);
        }

        if ($parent->level === 'detail') {
            throw ValidationException::withMessages([
                'data.parent_id' => 'لا يمكن إضافة حساب فرعي تحت حساب تفصيلي.',
            ]);
        }

        $data['type'] = $parent->type;
        $data['level'] = $parent->level === 'main' ? 'sub' : 'detail';

        return $data;
    }
}
