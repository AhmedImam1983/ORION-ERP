<?php

namespace App\Notifications;

use App\Models\Installment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class OverdueInstallmentNotification extends Notification
{
    use Queueable;

    public function __construct(public Installment $installment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $invoice  = $this->installment->invoice;
        $customer = $invoice?->customer;

        return FilamentNotification::make()
            ->danger()
            ->title('💰 قسط متأخر')
            ->body("فاتورة {$invoice?->invoice_number} — {$customer?->name} — " . number_format($this->installment->amount, 2) . " ر.س")
            ->getDatabaseMessage();
    }
}