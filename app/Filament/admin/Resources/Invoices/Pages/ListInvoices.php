<?php

namespace App\Filament\Admin\Resources\Invoices\Pages;

use App\Filament\Admin\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected string $view = 'filament.admin.resources.invoices.pages.list-invoices';

    public function getSubheading(): ?string
    {
        return 'مركز موحد لفواتير البيع والشراء والمرتجعات والتحصيل ومتابعة الأرصدة المفتوحة';
    }

    public function getInvoiceStats(): array
    {
        $base = Invoice::query();

        return [
            'count' => (clone $base)->count(),
            'value' => (float) (clone $base)->where('status', '!=', 'cancelled')->sum('total'),
            'outstanding' => (float) (clone $base)
                ->where('status', '!=', 'cancelled')
                ->where('remaining_amount', '>', 0)
                ->sum('remaining_amount'),
            'paid' => (clone $base)
                ->where('status', '!=', 'cancelled')
                ->where(function ($q) {
                    $q->where('status', 'paid')
                      ->orWhere('remaining_amount', '<=', 0);
                })
                ->count(),
            'credit_open' => (clone $base)
                ->whereIn('payment_type', ['credit', 'installment'])
                ->where('status', '!=', 'cancelled')
                ->where('remaining_amount', '>', 0)
                ->count(),
            'overdue' => (clone $base)
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', today())
                ->where('remaining_amount', '>', 0)
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->count(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('فاتورة جديدة')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
