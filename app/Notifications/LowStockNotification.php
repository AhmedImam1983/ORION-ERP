<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(public Product $product) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->warning()
            ->title('⚠️ مخزون ناقص')
            ->body("الصنف {$this->product->name} — الكمية: {$this->product->quantity} / الحد: {$this->product->min_quantity}")
            ->getDatabaseMessage();
    }
}