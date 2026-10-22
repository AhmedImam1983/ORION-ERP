<?php

namespace App\Filament\Admin\Resources\Accounts\Pages;

use App\Filament\Admin\Resources\Accounts\AccountResource;
use App\Models\Account;
use App\Models\JournalEntryLine;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditAccount extends EditRecord
{
    protected static string $resource = AccountResource::class;
    protected string $view = 'filament.admin.resources.accounts.pages.edit-account';

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->normalizeHierarchy($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label('عرض الحساب'),

            DeleteAction::make()
                ->label('حذف')
                ->visible(fn (): bool => $this->canSafelyDelete())
                ->before(function (): void {
                    if (!$this->canSafelyDelete()) {
                        Notification::make()
                            ->title('لا يمكن حذف الحساب')
                            ->body('الحساب يحتوي على حسابات فرعية أو حركات محاسبية.')
                            ->warning()
                            ->send();

                        $this->halt();
                    }
                }),
        ];
    }

    private function canSafelyDelete(): bool
    {
        $record = $this->getRecord();

        return !Account::where('parent_id', $record->id)->exists()
            && !JournalEntryLine::where('account_id', $record->id)->exists();
    }

    private function normalizeHierarchy(array $data): array
    {
        $record = $this->getRecord();
        $parentId = $data['parent_id'] ?? null;

        if ((int) $parentId === (int) $record->id) {
            throw ValidationException::withMessages([
                'data.parent_id' => 'لا يمكن جعل الحساب أباً لنفسه.',
            ]);
        }

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

        $cursor = $parent;
        while ($cursor) {
            if ((int) $cursor->id === (int) $record->id) {
                throw ValidationException::withMessages([
                    'data.parent_id' => 'لا يمكن نقل الحساب تحت أحد حساباته الفرعية.',
                ]);
            }

            $cursor = $cursor->parent_id ? Account::find($cursor->parent_id) : null;
        }

        $data['type'] = $parent->type;
        $data['level'] = $parent->level === 'main' ? 'sub' : 'detail';

        return $data;
    }
}
