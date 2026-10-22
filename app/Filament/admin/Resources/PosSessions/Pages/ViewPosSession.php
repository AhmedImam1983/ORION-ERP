<?php

namespace App\Filament\Admin\Resources\PosSessions\Pages;

use App\Filament\Admin\Pages\PosPage;
use App\Filament\Admin\Resources\PosSessions\PosSessionResource;
use App\Models\Invoice;
use App\Models\PosTransaction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewPosSession extends ViewRecord
{
    protected static string $resource = PosSessionResource::class;

    protected string $view = 'filament.admin.resources.pos-sessions.pages.view-pos-session';

    public function getTitle(): string
    {
        return 'جلسة كاشير ' . $this->record->session_number;
    }

    public function getSessionSummary(): array
    {
        $sales = (float) ($this->record->total_sales ?? 0);
        $refunds = (float) ($this->record->total_refunds ?? 0);
        $cash = (float) ($this->record->total_cash ?? 0);
        $card = (float) ($this->record->total_card ?? 0);
        $wallet = (float) ($this->record->total_wallet ?? 0);
        $opening = (float) ($this->record->opening_cash ?? 0);
        $closing = (float) ($this->record->closing_cash ?? 0);

        return [
            'sales' => $sales,
            'refunds' => $refunds,
            'net_sales' => $sales - $refunds,
            'transactions' => (int) ($this->record->transactions_count ?? 0),
            'opening_cash' => $opening,
            'closing_cash' => $closing,
            'cash' => $cash,
            'card' => $card,
            'wallet' => $wallet,
            'expected_cash' => $opening + $cash,
        ];
    }

    public function getSessionTransactions(): array
    {
        $transactions = PosTransaction::query()
            ->where('pos_session_id', $this->record->id)
            ->orderByDesc('created_at')
            ->get();

        $invoiceNumbers = Invoice::query()
            ->whereIn('id', $transactions->pluck('invoice_id')->filter()->values())
            ->pluck('invoice_number', 'id');

        return $transactions->map(function ($transaction) use ($invoiceNumbers): array {
            /*
             * Presentation compatibility:
             * pos_transactions now stores refunds as `refund` (the schema-safe value),
             * while the existing session Blade view historically checks for `return`.
             * Normalize only the view payload; do NOT mutate the database record.
             */
            $rawType = (string) ($transaction->type ?? 'sale');
            $displayType = in_array($rawType, ['refund', 'return'], true) ? 'return' : 'sale';

            return [
                'number' => $transaction->transaction_number,
                'invoice' => $invoiceNumbers[$transaction->invoice_id] ?? '-',
                'type' => $displayType,
                'type_raw' => $rawType,
                'type_label' => $displayType === 'return' ? 'مرتجع' : 'بيع',
                'payment_method' => $transaction->payment_method,
                'amount' => (float) ($transaction->amount ?? 0),
                'cash_amount' => (float) ($transaction->cash_amount ?? 0),
                'change_amount' => (float) ($transaction->change_amount ?? 0),
                'created_at' => $transaction->created_at?->format('d/m/Y H:i') ?? '-',
            ];
        })->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pos')
                ->label('فتح POS')
                ->icon('heroicon-o-computer-desktop')
                ->color('primary')
                ->url(fn () => PosPage::getUrl()),
        ];
    }
}
