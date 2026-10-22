<?php

namespace App\Filament\Admin\Resources\JournalEntries\Pages;

use App\Filament\Admin\Resources\JournalEntries\JournalEntryResource;
use App\Models\JournalEntry;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJournalEntries extends ListRecords
{
    protected static string $resource = JournalEntryResource::class;
    protected string $view = 'filament.admin.resources.journal-entries.pages.list-journal-entries';

    public function getJournalStats(): array
    {
        return [
            'total' => JournalEntry::count(),
            'today' => JournalEntry::whereDate('entry_date', today())->count(),
            'posted' => JournalEntry::where('status', 'posted')->count(),
            'unbalanced' => JournalEntry::whereRaw(
                'ABS(COALESCE(total_debit, 0) - COALESCE(total_credit, 0)) >= 0.01'
            )->count(),
            'today_debit' => (float) JournalEntry::whereDate('entry_date', today())->sum('total_debit'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('قيد يدوي جديد'),
        ];
    }
}
