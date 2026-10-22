<?php

namespace App\Filament\Admin\Resources\JournalEntries\Pages;

use App\Filament\Admin\Resources\JournalEntries\JournalEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditJournalEntry extends EditRecord
{
    protected static string $resource = JournalEntryResource::class;
    protected string $view = 'filament.admin.resources.journal-entries.pages.edit-journal-entry';

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->getRecord()->status !== 'draft') {
            Notification::make()
                ->title('القيد المرحّل لا يُعدّل مباشرة')
                ->body('استخدم قيداً عكسياً أو راجع الصلاحيات والإجراء المحاسبي.')
                ->warning()
                ->send();

            $this->halt();
        }

        return $this->prepareAndValidateEntry($data);
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord()->fresh('lines');

        $record->calculateTotals();
        $record->saveQuietly();
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label('عرض القيد'),
            DeleteAction::make()
                ->label('حذف')
                ->visible(fn (): bool => $this->getRecord()->status === 'draft'),
        ];
    }

    private function prepareAndValidateEntry(array $data): array
    {
        $lines = $data['lines'] ?? [];

        if (count($lines) < 2) {
            throw ValidationException::withMessages([
                'data.lines' => 'القيد يجب أن يحتوي على سطرين على الأقل.',
            ]);
        }

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($lines as $index => $line) {
            $debit = max(0, (float) ($line['debit'] ?? 0));
            $credit = max(0, (float) ($line['credit'] ?? 0));

            if (($debit > 0 && $credit > 0) || ($debit <= 0 && $credit <= 0)) {
                throw ValidationException::withMessages([
                    'data.lines' => 'السطر رقم ' . ($index + 1) . ' يجب أن يحتوي مبلغاً في المدين أو الدائن فقط.',
                ]);
            }

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        $data['total_debit'] = round($totalDebit, 2);
        $data['total_credit'] = round($totalCredit, 2);

        if (($data['status'] ?? 'draft') === 'posted' && abs($totalDebit - $totalCredit) >= 0.01) {
            throw ValidationException::withMessages([
                'data.lines' => 'لا يمكن ترحيل القيد لأن إجمالي المدين لا يساوي إجمالي الدائن.',
            ]);
        }

        return $data;
    }
}
