<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Installment;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Notifications\OverdueInstallmentNotification;
use Illuminate\Console\Command;

class SendDailyAlerts extends Command
{
    protected $signature   = 'alerts:send';
    protected $description = 'إرسال تنبيهات المخزون الناقص والأقساط المتأخرة';

    public function handle(): void
    {
        $admins = User::role(['super_admin', 'admin'])->get();

        if ($admins->isEmpty()) {
            $this->warn('لا يوجد مدراء لإرسال التنبيهات إليهم');
            return;
        }

        // ===== تنبيهات المخزون الناقص =====
        $lowStock = Product::where('is_active', true)
            ->where('min_quantity', '>', 0)
            ->whereRaw('COALESCE(quantity, 0) <= min_quantity')
            ->get();

        $stockCount = 0;
        foreach ($lowStock as $product) {
            foreach ($admins as $admin) {
                // تجنب إرسال نفس الإشعار مرتين في نفس اليوم
                $alreadySent = $admin->notifications()
                    ->where('type', LowStockNotification::class)
                    ->where('data->product_id', $product->id)
                    ->whereDate('created_at', today())
                    ->exists();

                if (!$alreadySent) {
                    $admin->notify(new LowStockNotification($product));
                    $stockCount++;
                }
            }
        }

        // ===== تنبيهات الأقساط المتأخرة =====
        $overdueInstallments = Installment::with(['invoice.customer'])
            ->where('status', 'pending')
            ->where('due_date', '<', today())
            ->get();

        $installmentCount = 0;
        foreach ($overdueInstallments as $installment) {
            // تحديث حالة القسط لـ overdue
            $installment->update(['status' => 'overdue']);

            foreach ($admins as $admin) {
                $alreadySent = $admin->notifications()
                    ->where('type', OverdueInstallmentNotification::class)
                    ->where('data->installment_id', $installment->id)
                    ->whereDate('created_at', today())
                    ->exists();

                if (!$alreadySent) {
                    $admin->notify(new OverdueInstallmentNotification($installment));
                    $installmentCount++;
                }
            }
        }

        $this->info("✅ تم إرسال {$stockCount} تنبيه مخزون و {$installmentCount} تنبيه أقساط");
    }
}