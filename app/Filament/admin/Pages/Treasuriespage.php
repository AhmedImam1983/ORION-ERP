<?php

namespace App\Filament\Admin\Pages;

use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class TreasuriesPage extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'accountant']);
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'الخزائن';
    protected static ?string $title = 'إدارة الخزائن';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.admin.pages.treasuries-page';

    public bool $showTransfer = false;
    public ?int $fromTreasuryId = null;
    public ?int $toTreasuryId = null;
    public float $transferAmount = 0;
    public string $transferDesc = '';

    public ?int $selectedTreasuryId = null;

    public bool $showCreateTreasury = false;
    public string $newTreasuryName = '';
    public string $newTreasuryType = 'cash';
    public float $newTreasuryBalance = 0;

    public string $treasurySearch = '';
    public string $treasuryFilter = 'all';

    public static function getNavigationGroup(): ?string
    {
        return 'الحسابات · Accounting';
    }

    public function mount(): void
    {
        $this->selectedTreasuryId = Treasury::where('is_active', true)
            ->orderBy('type')
            ->value('id');
    }

    public function getTreasuries()
    {
        $query = Treasury::with('branch')
            ->where('is_active', true);

        $search = trim($this->treasurySearch);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('branch', fn ($branch) => $branch->where('name', 'like', '%' . $search . '%'));
            });
        }

        if (in_array($this->treasuryFilter, ['today', 'no_today'], true)) {
            $todayTreasuryIds = TreasuryTransaction::whereDate('created_at', today())
                ->pluck('treasury_id')
                ->filter()
                ->unique()
                ->values();

            if ($this->treasuryFilter === 'today') {
                $query->whereIn('id', $todayTreasuryIds);
            } else {
                $query->whereNotIn('id', $todayTreasuryIds);
            }
        }

        if ($this->treasuryFilter === 'zero') {
            $query->where('current_balance', '<=', 0);
        }

        return $query->orderBy('type')->orderBy('name')->get();
    }

    public function getSelectedTreasury(): ?Treasury
    {
        if (!$this->selectedTreasuryId) {
            return null;
        }

        return Treasury::with('branch')->find($this->selectedTreasuryId);
    }

    public function getTotalBalance(): float
    {
        return (float) Treasury::where('is_active', true)->sum('current_balance');
    }

    public function getTodaySummary(): array
    {
        $visibleTreasuryIds = Treasury::query()->pluck('id');
        $transactions = TreasuryTransaction::whereIn('treasury_id', $visibleTreasuryIds)
            ->whereDate('created_at', today())->get();

        $incoming = (float) $transactions
            ->filter(fn ($tx) => in_array($tx->type, ['in', 'transfer_in'], true))
            ->sum('amount');

        $outgoing = (float) $transactions
            ->reject(fn ($tx) => in_array($tx->type, ['in', 'transfer_in'], true))
            ->sum('amount');

        return [
            'incoming' => $incoming,
            'outgoing' => $outgoing,
            'net' => $incoming - $outgoing,
            'count' => $transactions->count(),
        ];
    }

    public function getTodayStatsByTreasury(): array
    {
        $visibleTreasuryIds = Treasury::query()->pluck('id');
        $rows = TreasuryTransaction::whereIn('treasury_id', $visibleTreasuryIds)
            ->whereDate('created_at', today())->get();
        $stats = [];

        foreach ($rows as $tx) {
            $id = (int) $tx->treasury_id;

            if (!isset($stats[$id])) {
                $stats[$id] = ['in' => 0.0, 'out' => 0.0, 'count' => 0];
            }

            if (in_array($tx->type, ['in', 'transfer_in'], true)) {
                $stats[$id]['in'] += (float) $tx->amount;
            } else {
                $stats[$id]['out'] += (float) $tx->amount;
            }

            $stats[$id]['count']++;
        }

        return $stats;
    }

    public function getTransactions()
    {
        if (!$this->selectedTreasuryId) {
            return collect();
        }

        // selectedTreasuryId is a public Livewire property; re-check visibility server-side.
        if (!Treasury::query()->whereKey($this->selectedTreasuryId)->exists()) {
            return collect();
        }

        return TreasuryTransaction::with('createdBy', 'fromTreasury', 'toTreasury')
            ->where('treasury_id', $this->selectedTreasuryId)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
    }

    public function selectTreasury(int $id): void
    {
        if (!Treasury::query()->whereKey($id)->exists()) {
            Notification::make()->title('الخزينة غير متاحة لحسابك.')->danger()->send();
            return;
        }

        $this->selectedTreasuryId = $id;
    }

    public function openCreateTreasury(): void
    {
        $this->newTreasuryName = '';
        $this->newTreasuryType = 'cash';
        $this->newTreasuryBalance = 0;
        $this->showCreateTreasury = true;
    }

    public function createTreasury(): void
    {
        $name = trim($this->newTreasuryName);

        if ($name === '') {
            Notification::make()->title('أدخل اسم الخزينة')->warning()->send();
            return;
        }

        if ($this->newTreasuryBalance < 0) {
            Notification::make()->title('الرصيد الافتتاحي غير صحيح')->warning()->send();
            return;
        }

        try {
            $user = auth()->user();
            $branchId = $user?->branch_id ?? \App\Models\Branch::getMain()?->id;

            $openingBalance = round((float) $this->newTreasuryBalance, 2);

            $treasury = Treasury::create([
                'name'            => $name,
                'type'            => $this->newTreasuryType ?: 'cash',
                'branch_id'       => $branchId,
                'opening_balance' => $openingBalance,
                'current_balance' => $openingBalance,
                'is_active'       => true,
            ]);

            $this->showCreateTreasury = false;
            $this->selectedTreasuryId = $treasury->id;
            $this->newTreasuryName = '';
            $this->newTreasuryType = 'cash';
            $this->newTreasuryBalance = 0;

            Notification::make()
                ->title('تم إنشاء الخزينة بنجاح')
                ->body($treasury->name)
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('تعذر إنشاء الخزينة')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function openTransfer(?int $fromId = null): void
    {
        $this->fromTreasuryId = $fromId;
        $this->toTreasuryId = null;
        $this->transferAmount = 0;
        $this->transferDesc = '';
        $this->showTransfer = true;
    }

    public function doTransfer(): void
    {
        if (!$this->fromTreasuryId || !$this->toTreasuryId) {
            Notification::make()->title('اختر الخزينتين')->warning()->send();
            return;
        }

        if ($this->fromTreasuryId === $this->toTreasuryId) {
            Notification::make()->title('لا يمكن التحويل لنفس الخزينة')->warning()->send();
            return;
        }

        if ($this->transferAmount <= 0) {
            Notification::make()->title('أدخل المبلغ')->warning()->send();
            return;
        }

        try {
            Treasury::transfer(
                $this->fromTreasuryId,
                $this->toTreasuryId,
                $this->transferAmount,
                $this->transferDesc
            );

            $this->showTransfer = false;
            $this->selectedTreasuryId = $this->toTreasuryId;

            Notification::make()
                ->title('تم التحويل بنجاح')
                ->body(number_format($this->transferAmount, 2) . ' ر.س')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}