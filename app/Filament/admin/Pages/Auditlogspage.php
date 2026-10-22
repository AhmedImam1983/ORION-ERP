<?php

namespace App\Filament\Admin\Pages;

use App\Models\AuditLog;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\WithPagination;

class AuditLogsPage extends Page
{
    use WithPagination;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'سجل العمليات';
    protected static ?string $title = 'سجل العمليات';
    protected static ?int $navigationSort = 99;
    protected string $view = 'filament.admin.pages.audit-logs';

    public string $search = '';
    public string $filterEvent = '';
    public string $filterModel = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public int $perPage = 25;
    public string $viewMode = 'table';
    public ?int $selectedLogId = null;

    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات · Settings';
    }

    protected function getFilteredQuery(): Builder
    {
        return AuditLog::query()
            ->with('user')
            ->when($this->search !== '', function (Builder $query): void {
                $term = '%' . trim($this->search) . '%';

                $query->where(function (Builder $q) use ($term): void {
                    $q->where('user_name', 'like', $term)
                        ->orWhere('auditable_label', 'like', $term)
                        ->orWhere('event', 'like', $term)
                        ->orWhere('auditable_type', 'like', $term)
                        ->orWhere('ip_address', 'like', $term);
                });
            })
            ->when($this->filterEvent !== '', fn (Builder $query) => $query->where('event', $this->filterEvent))
            ->when($this->filterModel !== '', fn (Builder $query) => $query->where('auditable_type', $this->filterModel))
            ->when($this->dateFrom !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $this->dateTo));
    }

    public function getLogs()
    {
        return $this->getFilteredQuery()
            ->orderByDesc('created_at')
            ->paginate($this->perPage);
    }

    public function getStats(): array
    {
        $query = $this->getFilteredQuery();

        $total = (clone $query)->count();
        $today = (clone $query)->whereDate('created_at', today())->count();
        $updates = (clone $query)->where('event', 'updated')->count();
        $deletes = (clone $query)->where('event', 'deleted')->count();

        $topUserRow = (clone $query)
            ->whereNotNull('user_name')
            ->where('user_name', '!=', '')
            ->selectRaw('user_name, COUNT(*) as aggregate')
            ->groupBy('user_name')
            ->orderByDesc('aggregate')
            ->first();

        return [
            'total' => $total,
            'today' => $today,
            'updates' => $updates,
            'deletes' => $deletes,
            'top_user' => $topUserRow?->user_name ?: '—',
            'top_user_count' => (int) ($topUserRow?->aggregate ?? 0),
        ];
    }

    public function getModelOptions(): array
    {
        return AuditLog::query()
            ->whereNotNull('auditable_type')
            ->where('auditable_type', '!=', '')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type')
            ->mapWithKeys(fn ($type) => [$type => $this->modelLabelFromType($type)])
            ->all();
    }

    public function getEventOptions(): array
    {
        return AuditLog::query()
            ->whereNotNull('event')
            ->where('event', '!=', '')
            ->distinct()
            ->orderBy('event')
            ->pluck('event')
            ->mapWithKeys(fn ($event) => [$event => $this->eventLabel($event)])
            ->all();
    }

    public function modelLabelFromType(?string $type): string
    {
        if (!$type) {
            return 'النظام';
        }

        $name = class_basename($type);

        return match ($name) {
            'Invoice' => 'الفواتير',
            'Product' => 'الأصناف',
            'Customer' => 'العملاء',
            'Supplier' => 'الموردون',
            'JournalEntry' => 'القيود اليومية',
            'Employee' => 'الموظفون',
            'PosSession', 'CashierSession' => 'جلسات الكاشير',
            'Warehouse' => 'المخازن',
            'Category' => 'المجموعات',
            'User' => 'المستخدمون',
            'Branch' => 'الفروع',
            'BankAccount' => 'الحسابات البنكية',
            'BankTransaction' => 'حركات البنك',
            'FixedAsset' => 'الأصول الثابتة',
            'DailyExpense' => 'المصروفات اليومية',
            'PurchaseOrder' => 'طلبات الشراء',
            'StockMovement' => 'حركة المخزون',
            'Treasury' => 'الخزائن',
            default => Str::headline($name),
        };
    }

    public function eventLabel(?string $event): string
    {
        return match ($event) {
            'created' => 'إنشاء',
            'updated' => 'تعديل',
            'deleted' => 'حذف',
            'restored' => 'استعادة',
            'login' => 'تسجيل دخول',
            'logout' => 'تسجيل خروج',
            'opened' => 'فتح',
            'closed' => 'إغلاق',
            default => $event ? Str::headline($event) : 'عملية',
        };
    }

    public function eventClass(?string $event): string
    {
        return match ($event) {
            'created' => 'created',
            'updated' => 'updated',
            'deleted' => 'deleted',
            'restored' => 'restored',
            'login', 'logout' => 'auth',
            'opened', 'closed' => 'session',
            default => 'default',
        };
    }

    public function eventIcon(?string $event): string
    {
        return match ($event) {
            'created' => 'plus',
            'updated' => 'edit',
            'deleted' => 'trash',
            'restored' => 'restore',
            'login' => 'login',
            'logout' => 'logout',
            'opened' => 'play',
            'closed' => 'stop',
            default => 'activity',
        };
    }

    public function fieldLabel(string $field): string
    {
        return match ($field) {
            'id' => 'المعرّف',
            'name' => 'الاسم',
            'name_en' => 'الاسم بالإنجليزية',
            'code' => 'الكود',
            'status' => 'الحالة',
            'user_id' => 'المستخدم',
            'branch_id' => 'الفرع',
            'warehouse_id' => 'المخزن',
            'category_id' => 'المجموعة',
            'customer_id' => 'العميل',
            'supplier_id' => 'المورد',
            'session_number' => 'رقم الجلسة',
            'register_name' => 'الكاشير',
            'opening_cash' => 'رصيد الفتح',
            'closing_cash' => 'رصيد الإغلاق',
            'opened_at' => 'وقت الفتح',
            'closed_at' => 'وقت الإغلاق',
            'total' => 'الإجمالي',
            'paid_amount' => 'المدفوع',
            'remaining_amount' => 'المتبقي',
            'quantity' => 'الكمية',
            'cost_price' => 'سعر التكلفة',
            'sale_price' => 'سعر البيع',
            'is_active' => 'نشط',
            'is_default' => 'افتراضي',
            'updated_at' => 'آخر تحديث',
            'created_at' => 'تاريخ الإنشاء',
            default => Str::headline($field),
        };
    }

    public function formatAuditValue(mixed $value, int $limit = 60): string
    {
        if ($value === null) {
            return '∅';
        }

        if (is_bool($value)) {
            return $value ? 'نعم' : 'لا';
        }

        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $text = trim((string) $value);

        if ($text === '') {
            return 'فارغ';
        }

        return Str::limit($text, $limit);
    }

    public function changedFields(AuditLog $log): array
    {
        $oldValues = is_array($log->old_values) ? $log->old_values : [];
        $newValues = is_array($log->new_values) ? $log->new_values : [];

        $fields = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
        $changes = [];

        foreach ($fields as $field) {
            $old = $oldValues[$field] ?? null;
            $new = $newValues[$field] ?? null;

            if ($old === $new) {
                continue;
            }

            $changes[] = [
                'field' => $field,
                'label' => $this->fieldLabel($field),
                'old' => $old,
                'new' => $new,
            ];
        }

        return $changes;
    }

    public function openLog(int $id): void
    {
        if (AuditLog::query()->whereKey($id)->exists()) {
            $this->selectedLogId = $id;
        }
    }

    public function closeLog(): void
    {
        $this->selectedLogId = null;
    }

    public function getSelectedLog(): ?AuditLog
    {
        if (!$this->selectedLogId) {
            return null;
        }

        return AuditLog::with('user')->find($this->selectedLogId);
    }

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['table', 'timeline'], true)) {
            $this->viewMode = $mode;
        }
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'filterEvent',
            'filterModel',
            'dateFrom',
            'dateTo',
        ]);

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterEvent(): void
    {
        $this->resetPage();
    }

    public function updatedFilterModel(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }
}