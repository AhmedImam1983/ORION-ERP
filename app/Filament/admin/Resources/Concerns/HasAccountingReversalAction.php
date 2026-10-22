<?php

namespace App\Filament\Admin\Resources\Concerns;

use App\Services\AccountingReversalService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

trait HasAccountingReversalAction
{
    public static function getRecordActions(): array
    {
        return [
            Action::make('reverse')
                ->label('عكس / إلغاء')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('عكس المستند المرحّل')
                ->modalDescription(
                    'لن يتم حذف المستند أو القيد الأصلي. سيتم إنشاء قيد عكسي وحركة تشغيلية عكسية مع الاحتفاظ بسجل كامل.'
                )
                ->modalSubmitActionLabel('تأكيد العكس')
                ->form([
                    Textarea::make('reason')
                        ->label('سبب الإلغاء / العكس')
                        ->required()
                        ->rows(3)
                        ->maxLength(1000),
                ])
                ->visible(fn ($record) => ($record->status ?? null) === 'posted')
                ->action(function ($record, array $data) {
                    try {
                        $reversal = app(AccountingReversalService::class)
                            ->reverse($record, $data['reason']);

                        Notification::make()
                            ->title('تم إنشاء القيد العكسي بنجاح')
                            ->body(
                                $reversal->reversal_number .
                                ' — ' .
                                ($reversal->reversalJournalEntry?->entry_number ?? '')
                            )
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('تعذر عكس المستند')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
