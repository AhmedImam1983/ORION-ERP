<?php

namespace App\Filament\Admin\Pages;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\PosSession;
use App\Models\PosTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Treasury;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class Dashboard extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_dashboard') ?? false;
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'لوحة التحكم';
    protected static ?string $title = 'لوحة التحكم';
    protected static ?int $navigationSort = -1;
    protected string $view = 'filament.admin.pages.dashboard';

    public bool $isCashier = false;
    public bool $isBranchUser = false;

    // Shared / sales
    public float $totalSalesToday = 0;
    public float $collectedToday = 0;
    public int $totalInvoices = 0;
    public $recentInvoices;

    // Executive comparisons
    public float $salesYesterday = 0;
    public float $collectedYesterday = 0;
    public float $salesChangePct = 0;
    public float $collectionChangePct = 0;
    public float $previousPeriodExpenses = 0;
    public float $previousPeriodNetProfit = 0;
    public float $expensesChangePct = 0;
    public float $netChangePct = 0;

    // Admin
    public int $totalProducts = 0;
    public int $totalEmployees = 0;
    public int $totalCustomers = 0;
    public int $accountsCount = 0;
    public float $totalDebts = 0;
    public int $unpaidCount = 0;
    public int $pendingOrders = 0;
    public int $lowStockCount = 0;

    public float $treasuryBalance = 0;
    public float $bankBalance = 0;
    public float $cashPosition = 0;
    public float $inventoryValue = 0;
    public float $supplierPayables = 0;
    public int $supplierPayablesCount = 0;

    public float $monthRevenue = 0;
    public float $monthExpenses = 0;
    public float $monthNetProfit = 0;
    public float $monthNetMargin = 0;

    public array $chartRows = [];
    public array $previousChartRows = [];
    public array $adminAlerts = [];

    // Cashier
    public ?array $cashierSession = null;
    public array $cashierRecentTransactions = [];
    public float $cashierExpectedCash = 0;
    public float $cashierSessionSales = 0;
    public float $cashierCash = 0;
    public float $cashierCard = 0;
    public float $cashierWallet = 0;
    public float $cashierRefunds = 0;
    public int $cashierTransactionsCount = 0;

    // Backup
    public string $lastBackup = 'لا توجد نسخ بعد';
    public bool $showRestore = false;
    public array $backupFiles = [];

    public function mount(): void
    {
        $user = auth()->user();
        // Accounting is centralized: accountants with an assigned branch still
        // need the company-wide financial dashboard. Backup actions remain
        // separately restricted to admin/super_admin in both Blade and methods.
        $hasCompanyWideView = (bool) $user?->hasAnyRole(['super_admin', 'admin', 'accountant']);
        $this->isCashier = !$hasCompanyWideView && (bool) $user?->hasRole('cashier');
        $this->isBranchUser = !$hasCompanyWideView && !$this->isCashier;

        $saleStatuses = ['confirmed', 'paid', 'partial'];
        $today = today();

        $salesQuery = $this->invoiceQuery()
            ->where('type', 'sale')
            ->whereIn('status', $saleStatuses)
            ->whereDate('invoice_date', $today);

        if ($this->isCashier) {
            $salesQuery->where('created_by', $user->id);
        }

        $this->totalSalesToday = (float) (clone $salesQuery)->sum('total');
        $this->collectedToday = (float) (clone $salesQuery)->sum('paid_amount');

        if ($this->isBranchUser) {
            // Branch-bound roles get summary numbers only. Do not hydrate a
            // company-wide recent-invoice list into their Livewire component.
            $this->recentInvoices = collect();
        } else {
            $recentQuery = $this->invoiceQuery()->with('customer')
                ->where('type', 'sale')
                ->whereIn('status', $saleStatuses);

            if ($this->isCashier) {
                $recentQuery
                    ->where('created_by', $user->id)
                    ->whereDate('invoice_date', $today);
            }

            $this->recentInvoices = $recentQuery
                ->latest('created_at')
                ->limit($this->isCashier ? 8 : 6)
                ->get();
        }

        if ($this->isCashier) {
            $this->loadCashierDashboard($user->id);
        } elseif ($this->isBranchUser) {
            $this->loadBranchDashboard($saleStatuses);
        } else {
            $this->loadAdminDashboard($saleStatuses);
            $this->lastBackup = $this->getLastBackup();
        }
    }

    /**
     * Invoice query for dashboard calculations. The accountant role has a
     * company-wide finance remit, so dashboard totals include all branches.
     * Other branch-bound users retain the standard BranchScope.
     */
    private function invoiceQuery()
    {
        $query = Invoice::query();

        if (auth()->user()?->hasRole('accountant')) {
            $query->withoutGlobalScope(\App\Models\Scopes\BranchScope::class);
        }

        return $query;
    }

    private function loadBranchDashboard(array $saleStatuses): void
    {
        // Invoice's BranchScope limits these counts and sums to the current user's
        // assigned branch. The journal-entry table is not queried here because it
        // has no branch_id in the current schema.
        $query = Invoice::query()
            ->where('type', 'sale')
            ->whereIn('status', $saleStatuses);

        $this->totalInvoices = (int) (clone $query)->count();
        $this->totalDebts = (float) (clone $query)
            ->where('remaining_amount', '>', 0)
            ->sum('remaining_amount');
        $this->unpaidCount = (int) (clone $query)
            ->where('remaining_amount', '>', 0)
            ->count();
    }

    private function loadCashierDashboard(int $userId): void
    {
        $session = PosSession::with('branch')
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        if (!$session) {
            $this->cashierSession = null;
            $this->cashierRecentTransactions = [];
            return;
        }

        $this->cashierSessionSales = (float) $session->total_sales;
        $this->cashierCash = (float) $session->total_cash;
        $this->cashierCard = (float) $session->total_card;
        $this->cashierWallet = (float) $session->total_wallet;
        $this->cashierRefunds = (float) ($session->total_refunds ?? 0);
        $this->cashierTransactionsCount = (int) $session->transactions_count;

        $this->cashierExpectedCash =
            (float) $session->opening_cash
            + (float) $session->total_cash;

        $this->cashierSession = [
            'id' => $session->id,
            'number' => $session->session_number,
            'register_name' => $session->register_name,
            'branch' => $session->branch?->name ?? '-',
            'opening_cash' => (float) $session->opening_cash,
            'opened_at' => $session->opened_at?->format('H:i'),
            'opened_at_full' => $session->opened_at?->format('d/m/Y H:i'),
            'status' => $session->status,
        ];

        $this->cashierRecentTransactions = PosTransaction::query()
            ->where('pos_session_id', $session->id)
            ->latest('created_at')
            ->limit(6)
            ->get()
            ->map(fn ($tx) => [
                'number' => $tx->transaction_number,
                'method' => $tx->payment_method,
                'type' => $tx->type,
                'amount' => (float) $tx->amount,
                'time' => $tx->created_at?->format('H:i'),
            ])
            ->toArray();
    }

    private function loadAdminDashboard(array $saleStatuses): void
    {
        $this->totalProducts = Product::count();
        $this->totalEmployees = Employee::where('status', 'active')->count();
        $this->totalCustomers = Customer::count();
        $this->accountsCount = Account::count();

        $this->totalDebts = (float) $this->invoiceQuery()->where('type', 'sale')
            ->where('status', '!=', 'cancelled')
            ->sum('remaining_amount');

        $this->totalInvoices = $this->invoiceQuery()->where('type', 'sale')->count();

        $this->unpaidCount = $this->invoiceQuery()->where('type', 'sale')
            ->where('remaining_amount', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->count();

        $this->pendingOrders = PurchaseOrder::whereIn('status', ['pending', 'approved'])->count();

        $this->lowStockCount = Product::where('item_type', 'stock')
            ->where('quantity', '<=', 5)
            ->where('is_active', true)
            ->count();

        // Cash position
        $this->treasuryBalance = (float) Treasury::where('is_active', true)
            ->sum('current_balance');

        $this->bankBalance = $this->safeBankBalance();
        $this->cashPosition = $this->treasuryBalance + $this->bankBalance;

        // Inventory value at cost. The fallback keeps the dashboard safe if a legacy schema differs.
        $this->inventoryValue = $this->safeInventoryValue();

        // Supplier outstanding invoices, when purchase invoices use the same Invoice model.
        [$this->supplierPayables, $this->supplierPayablesCount] = $this->safeSupplierPayables();

        // Current month P&L from posted journal entries.
        $currentFrom = now()->copy()->startOfMonth();
        $currentTo = now();

        $pnl = $this->calculatePnl(
            $currentFrom->format('Y-m-d'),
            $currentTo->format('Y-m-d')
        );

        $this->monthRevenue = $pnl['revenue'];
        $this->monthExpenses = $pnl['expenses'];
        $this->monthNetProfit = $pnl['net'];
        $this->monthNetMargin = abs($this->monthRevenue) >= 0.01
            ? round(($this->monthNetProfit / abs($this->monthRevenue)) * 100, 1)
            : 0;

        // Like-for-like previous month-to-date comparison.
        $previousFrom = now()->copy()->subMonthNoOverflow()->startOfMonth();
        $previousEndOfMonth = $previousFrom->copy()->endOfMonth();
        $previousDay = min(now()->day, $previousEndOfMonth->day);
        $previousTo = $previousFrom->copy()->day($previousDay)->endOfDay();

        $previousPnl = $this->calculatePnl(
            $previousFrom->format('Y-m-d'),
            $previousTo->format('Y-m-d')
        );

        $this->previousPeriodExpenses = (float) $previousPnl['expenses'];
        $this->previousPeriodNetProfit = (float) $previousPnl['net'];
        $this->expensesChangePct = $this->percentChange($this->monthExpenses, $this->previousPeriodExpenses);
        $this->netChangePct = $this->percentChange($this->monthNetProfit, $this->previousPeriodNetProfit);

        // Today vs yesterday.
        $yesterday = now()->copy()->subDay()->toDateString();

        $this->salesYesterday = (float) $this->invoiceQuery()
            ->where('type', 'sale')
            ->whereIn('status', $saleStatuses)
            ->whereDate('invoice_date', $yesterday)
            ->sum('total');

        $this->collectedYesterday = (float) $this->invoiceQuery()
            ->where('type', 'sale')
            ->whereIn('status', $saleStatuses)
            ->whereDate('invoice_date', $yesterday)
            ->sum('paid_amount');

        $this->salesChangePct = $this->percentChange($this->totalSalesToday, $this->salesYesterday);
        $this->collectionChangePct = $this->percentChange($this->collectedToday, $this->collectedYesterday);

        // Last 7 days and the seven days immediately before them.
        $this->chartRows = collect(range(6, 0))->map(function ($days) use ($saleStatuses) {
            $date = now()->subDays($days);

            return [
                'label' => $date->locale('ar')->isoFormat('ddd'),
                'value' => (float) $this->invoiceQuery()->where('type', 'sale')
                    ->whereIn('status', $saleStatuses)
                    ->whereDate('invoice_date', $date->toDateString())
                    ->sum('total'),
            ];
        })->toArray();

        $this->previousChartRows = collect(range(13, 7))->map(function ($days) use ($saleStatuses) {
            $date = now()->subDays($days);

            return [
                'label' => $date->locale('ar')->isoFormat('ddd'),
                'value' => (float) $this->invoiceQuery()->where('type', 'sale')
                    ->whereIn('status', $saleStatuses)
                    ->whereDate('invoice_date', $date->toDateString())
                    ->sum('total'),
            ];
        })->toArray();

        $this->adminAlerts = [
            [
                'label' => 'أصناف مخزون منخفض',
                'value' => $this->lowStockCount,
                'severity' => $this->lowStockCount > 0 ? 'danger' : 'ok',
                'hint' => $this->lowStockCount > 0 ? 'تحتاج مراجعة وإعادة طلب' : 'مستويات المخزون مستقرة',
                'url' => '/admin/products',
                'icon' => 'box',
            ],
            [
                'label' => 'فواتير عملاء غير مسددة',
                'value' => $this->unpaidCount,
                'severity' => $this->unpaidCount > 0 ? 'warning' : 'ok',
                'hint' => number_format($this->totalDebts, 2) . ' ر.س مستحق',
                'url' => '/admin/invoices',
                'icon' => 'user',
            ],
            [
                'label' => 'طلبات شراء معلقة',
                'value' => $this->pendingOrders,
                'severity' => $this->pendingOrders > 0 ? 'info' : 'ok',
                'hint' => 'Pending / Approved',
                'url' => '/admin/purchase-orders',
                'icon' => 'cart',
            ],
            [
                'label' => 'فواتير موردين مستحقة',
                'value' => $this->supplierPayablesCount,
                'severity' => $this->supplierPayablesCount > 0 ? 'purple' : 'ok',
                'hint' => number_format($this->supplierPayables, 2) . ' ر.س مستحق',
                'url' => '/admin/invoices',
                'icon' => 'doc',
            ],
        ];
    }

    private function percentChange(float $current, float $previous): float
    {
        if (abs($previous) < 0.01) {
            return abs($current) < 0.01 ? 0 : 100;
        }

        return round((($current - $previous) / abs($previous)) * 100, 1);
    }

    private function safeBankBalance(): float
    {
        try {
            $model = \App\Models\BankAccount::class;

            if (!class_exists($model)) {
                return 0;
            }

            return (float) $model::query()
                ->where('is_active', true)
                ->sum('current_balance');
        } catch (\Throwable) {
            return 0;
        }
    }

    private function safeInventoryValue(): float
    {
        try {
            return (float) Product::query()
                ->selectRaw('COALESCE(SUM(COALESCE(quantity,0) * COALESCE(cost_price,0)),0) as inventory_value')
                ->value('inventory_value');
        } catch (\Throwable) {
            return 0;
        }
    }

    private function safeSupplierPayables(): array
    {
        try {
            $query = $this->invoiceQuery()
                ->where('type', 'purchase')
                ->where('status', '!=', 'cancelled')
                ->where('remaining_amount', '>', 0);

            return [
                (float) (clone $query)->sum('remaining_amount'),
                (int) (clone $query)->count(),
            ];
        } catch (\Throwable) {
            return [0.0, 0];
        }
    }

    private function calculatePnl(string $from, string $to): array
    {
        try {
            $rows = DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->join('accounts as a', 'a.id', '=', 'jel.account_id')
                ->where('je.status', 'posted')
                ->whereBetween('je.entry_date', [$from, $to])
                ->whereIn('a.type', ['revenue', 'expense'])
                ->groupBy('a.type')
                ->selectRaw(
                    'a.type, COALESCE(SUM(jel.debit),0) as debit, COALESCE(SUM(jel.credit),0) as credit'
                )
                ->get()
                ->keyBy('type');

            $revenue = (float) ($rows->get('revenue')->credit ?? 0)
                - (float) ($rows->get('revenue')->debit ?? 0);

            $expenses = (float) ($rows->get('expense')->debit ?? 0)
                - (float) ($rows->get('expense')->credit ?? 0);

            return [
                'revenue' => $revenue,
                'expenses' => $expenses,
                'net' => $revenue - $expenses,
            ];
        } catch (\Throwable) {
            return ['revenue' => 0, 'expenses' => 0, 'net' => 0];
        }
    }

    protected function getLastBackup(): string
    {
        $backupDir = storage_path('app/backups');
        $files = glob($backupDir . DIRECTORY_SEPARATOR . 'backup_*.sql');

        if (empty($files)) {
            return 'لا توجد نسخ بعد';
        }

        $latest = max(array_map('filemtime', $files));

        return \Carbon\Carbon::createFromTimestamp($latest)->diffForHumans();
    }

    private function authorizeBackupManagement(): void
    {
        abort_unless(
            auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'admin']),
            403
        );
    }

    public function makeBackup(): void
    {
        $this->authorizeBackupManagement();
        \Artisan::call('backup:database');
        $this->lastBackup = $this->getLastBackup();

        Notification::make()
            ->title('تم إنشاء النسخة الاحتياطية')
            ->success()
            ->send();
    }

    public function openRestore(): void
    {
        $this->authorizeBackupManagement();
        $backupDir = storage_path('app/backups');
        $files = glob($backupDir . DIRECTORY_SEPARATOR . 'backup_*.sql');
        rsort($files);

        $this->backupFiles = array_map(fn ($f) => [
            'name' => basename($f),
            'size' => round(filesize($f) / 1024, 2),
            'date' => \Carbon\Carbon::createFromTimestamp(filemtime($f))->format('d/m/Y H:i'),
        ], $files);

        $this->showRestore = true;
    }

    public function restoreBackup(string $filename): void
    {
        $this->authorizeBackupManagement();

        $filename = basename($filename);
        $backupDirectory = realpath(storage_path('app/backups'));

        if (!preg_match('/^backup_[A-Za-z0-9_.-]+\.sql$/D', $filename) || !$backupDirectory) {
            Notification::make()
                ->title('اسم ملف النسخة غير صالح')
                ->danger()
                ->send();
            return;
        }

        $filepath = realpath($backupDirectory . DIRECTORY_SEPARATOR . $filename);
        $backupPrefix = rtrim($backupDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (
            $filepath === false
            || !is_file($filepath)
            || !str_starts_with($filepath, $backupPrefix)
        ) {
            Notification::make()
                ->title('ملف النسخة غير موجود')
                ->danger()
                ->send();

            return;
        }

        $command = sprintf(
            'mysql --host=%s --user=%s --password=%s %s < %s',
            escapeshellarg(config('database.connections.mysql.host')),
            escapeshellarg(config('database.connections.mysql.username')),
            escapeshellarg(config('database.connections.mysql.password')),
            escapeshellarg(config('database.connections.mysql.database')),
            escapeshellarg($filepath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            Notification::make()
                ->title('تمت استعادة النسخة بنجاح')
                ->success()
                ->send();

            $this->showRestore = false;
        } else {
            Notification::make()
                ->title('فشلت عملية الاستعادة')
                ->danger()
                ->send();
        }
    }
}
