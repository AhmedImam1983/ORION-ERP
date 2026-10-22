<?php

namespace App\Filament\Admin\Resources\PosSessions\Pages;

use App\Filament\Admin\Pages\PosPage;
use App\Filament\Admin\Resources\PosSessions\PosSessionResource;
use App\Models\PosSession;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListPosSessions extends ListRecords
{
    protected static string $resource = PosSessionResource::class;

    protected string $view = 'filament.admin.resources.pos-sessions.pages.list-pos-sessions';

    public function getSubheading(): ?string
    {
        return 'متابعة الجلسات والمبيعات والمرتجعات من سجل تشغيلي واحد';
    }

    public function getSessionStats(): array
    {
        $today = PosSession::query()->whereDate('opened_at', today());

        return [
            'total' => PosSession::query()->count(),
            'open' => PosSession::query()->where('status', 'open')->count(),
            'today_sales' => (float) (clone $today)->sum('total_sales'),
            'today_refunds' => (float) (clone $today)->sum('total_refunds'),
            'today_transactions' => (int) (clone $today)->sum('transactions_count'),
            'today_sessions' => (int) (clone $today)->count(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_pos')
                ->label('فتح الكاشير POS')
                ->icon('heroicon-o-computer-desktop')
                ->color('primary')
                ->url(fn () => PosPage::getUrl()),
        ];
    }
}
