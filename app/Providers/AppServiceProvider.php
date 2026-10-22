<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Audit Observer
        // Core business + accounting coverage only.
        // Keep this list explicit so the audit trail remains intentional and reviewable.
        $models = [
            \App\Models\Invoice::class,
            \App\Models\Product::class,
            \App\Models\Customer::class,
            \App\Models\Supplier::class,
            \App\Models\JournalEntry::class,
            \App\Models\Employee::class,
            \App\Models\PosSession::class,

            // Core accounting documents
            \App\Models\ReceiptVoucher::class,
            \App\Models\PaymentVoucher::class,
            \App\Models\FinancialTransfer::class,
            \App\Models\AccountingReversal::class,
            \App\Models\Expense::class,

            // Cash / bank master balances + operational ledgers
            // Auditing the parent models is important because any direct balance
            // mutation must be visible in the audit trail, not only the child transaction.
            \App\Models\Treasury::class,
            \App\Models\BankAccount::class,
            \App\Models\TreasuryTransaction::class,
            \App\Models\BankTransaction::class,

            // Inventory + POS operational trail
            \App\Models\WarehouseStock::class,
            \App\Models\StockMovement::class,
            \App\Models\PosTransaction::class,
        ];

        foreach ($models as $model) {
            if (class_exists($model)) {
                $model::observe(\App\Observers\AuditObserver::class);
            }
        }

        \App\Models\Invoice::observe(\App\Observers\InvoiceObserver::class);

        FilamentView::registerRenderHook(
            'panels::head.end',
            fn(): string => '
            <style>
                @import url("https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap");

                * { font-family: Cairo, sans-serif !important; }

                /* ===== RTL Sidebar — نحركه لليمين ===== */
                .fi-sidebar {
                    right: 0 !important;
                    left: auto !important;
                    border-left: 1px solid rgb(255 255 255 / 0.1) !important;
                    border-right: none !important;
                }

                /* ===== Typography ===== */
                .fi-logo {
                    font-family: Cairo, sans-serif !important;
                    font-weight: 800 !important;
                    font-size: 1.25rem !important;
                }

                .fi-sidebar-group-label {
                    font-size: 0.7rem !important;
                    letter-spacing: 0.05em !important;
                    opacity: 0.6 !important;
                }

                .fi-sidebar-item-label {
                    font-size: 0.875rem !important;
                    font-weight: 500 !important;
                }

                .fi-header-heading {
                    font-family: Cairo, sans-serif !important;
                    font-weight: 800 !important;
                    font-size: 1.5rem !important;
                }

                /* ===== Tables ===== */
                .fi-ta-header-cell-label {
                    font-weight: 700 !important;
                    font-size: 0.8rem !important;
                }

                /* ===== Buttons ===== */
                .fi-btn {
                    font-family: Cairo, sans-serif !important;
                    font-weight: 600 !important;
                }

                /* ===== Forms ===== */
                .fi-fo-field-wrp-label label {
                    font-weight: 600 !important;
                    font-size: 0.875rem !important;
                }

                .fi-section-header-heading {
                    font-weight: 700 !important;
                }

                .fi-sidebar-item-badge {
                    font-family: Cairo, sans-serif !important;
                    font-size: 0.7rem !important;
                }

                /* ===== KPI Cards ===== */
                .fi-wi-stats-overview-stat { border-radius: 12px !important; }
                .fi-wi-stats-overview-stat-label {
                    font-size: 0.8rem !important;
                    font-weight: 600 !important;
                    opacity: 0.7 !important;
                }
                .fi-wi-stats-overview-stat-value {
                    font-size: 1.6rem !important;
                    font-weight: 800 !important;
                }
            </style>
            <script>
                document.documentElement.setAttribute("dir", "rtl");
                document.documentElement.setAttribute("lang", "ar");
            </script>',
        );
    }
}