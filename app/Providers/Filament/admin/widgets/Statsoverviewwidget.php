<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('مبيعات اليوم', '٠ ر.س')
                ->description('لا توجد مبيعات بعد')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('المحصّل اليوم', '٠ ر.س')
                ->description('٠٪ من الهدف')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('أصناف ناقصة', '٠')
                ->description('المخزون سليم')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('warning'),

            Stat::make('حضور اليوم', '٠ / ٠')
                ->description('٠٪ نسبة الحضور')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}