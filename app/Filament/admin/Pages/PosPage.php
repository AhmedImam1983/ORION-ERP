<?php

namespace App\Filament\Admin\Pages;

use App\Models\Branch;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PosSession;
use App\Models\PosSessionItem;
use App\Models\PosTransaction;
use App\Models\Product;
use App\Models\ReceiptVoucher;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Treasury;
use App\Models\BankAccount;
use App\Services\InvoicePostingService;
use App\Services\ReceiptPostingService;
use App\Services\PaymentPostingService;
use App\Services\WarehouseInventoryService;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class PosPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?string $navigationLabel = 'الكاشير';
    protected static ?string $title = 'الكاشير · POS';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.admin.pages.pos-page';

    public static function getNavigationGroup(): ?string
    {
        return 'المبيعات · Sales';
    }

    public ?PosSession $currentSession = null;
    public bool $showOpenSession = false;
    public float $openingCash = 0;
    public ?int $selectedBranchId = null;
    public ?int $selectedWarehouseId = null;
    public ?int $selectedTreasuryId = null;
    public ?int $selectedBankAccountId = null;
    public array $cartItems = [];
    public ?int $selectedCustomerId = null;
    public string $paymentMethod = 'cash';
    public float $cashReceived = 0;
    public string $productSearch = '';
    public array $productResults = [];
    public bool $showSuccess = false;
    public ?array $lastTransaction = null;

    // Return Modal
    public bool $showReturnModal = false;
    public string $returnInvoiceNumber = '';
    public array $returnSearchResults = [];
    public ?Invoice $returnInvoice = null;
    public array $returnItems = [];
    public string $returnReason = 'defective';
    public string $returnError = '';
    public string $returnPaymentMethodLabel = '';
    public string $returnSourceLabel = '';
    public string $returnReceiptNumber = '';

    public function mount(): void
    {
        $this->currentSession = PosSession::where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        if (!$this->currentSession) {
            $this->showOpenSession = true;
            $user = auth()->user();
            $this->selectedBranchId = $user->branch_id ?? Branch::getMain()?->id;
            $this->applyBranchDefaults();
            return;
        }

        // Restore ALL operational choices after refresh/reconnect.
        $this->selectedBranchId      = $this->currentSession->branch_id;
        $this->selectedWarehouseId   = $this->currentSession->warehouse_id;
        $this->selectedTreasuryId    = $this->currentSession->treasury_id;
        $this->selectedBankAccountId = $this->currentSession->bank_account_id;

        $this->loadCartFromDB();
    }

    public function updatedSelectedBranchId($value): void
    {
        $this->selectedBranchId = $value ? (int) $value : null;
        $this->applyBranchDefaults();
    }

    private function applyBranchDefaults(): void
    {
        $branchId = $this->selectedBranchId;

        // Warehouses are not branch-scoped in the current schema.
        // Prefer the warehouse explicitly linked to the branch; otherwise use
        // the active default warehouse without querying a non-existent branch_id.
        $this->selectedWarehouseId = $branchId
            ? (Branch::find($branchId)?->warehouse_id
                ?? Warehouse::where('is_active', true)
                    ->orderByDesc('is_default')
                    ->value('id'))
            : Warehouse::where('is_active', true)
                ->orderByDesc('is_default')
                ->value('id');

        $this->selectedTreasuryId = Treasury::where('is_active', true)
            ->when($branchId, fn ($q) => $q->where(function ($qq) use ($branchId) {
                $qq->where('branch_id', $branchId)->orWhereNull('branch_id');
            }))
            ->orderByDesc('is_default')
            ->value('id');

        $this->selectedBankAccountId = BankAccount::where('is_active', true)
            ->when($branchId, fn ($q) => $q->where(function ($qq) use ($branchId) {
                $qq->where('branch_id', $branchId)->orWhereNull('branch_id');
            }))
            ->orderByDesc('is_default')
            ->value('id');
    }

    private function loadCartFromDB(): void
    {
        if (!$this->currentSession) return;
        $this->cartItems = PosSessionItem::where('pos_session_id', $this->currentSession->id)
            ->get()
            ->map(function ($item) {
                $product = Product::find($item->product_id);

                return [
                'id'           => $item->id,
                'product_id'   => $item->product_id,
                'product_name' => $item->product_name,
                'product_code' => $item->product_code,
                'image'        => $product?->image,
                'unit'         => $item->unit,
                'quantity'     => (float) $item->quantity,
                'unit_price'   => (float) $item->unit_price,
                'cost_price'   => (float) $item->cost_price,
                'discount_percent' => (float) ($item->discount_percent ?? 0),
                'discount_amount'  => (float) ($item->discount_amount ?? 0),
                'stock'        => $product && $this->currentSession?->warehouse_id
                    ? app(WarehouseInventoryService::class)->balance($product, (int) $this->currentSession->warehouse_id)
                    : (float) $item->stock,
                'total'        => (float) $item->total,
                // ── ZATCA VAT Snapshot ──
                'vat_type'                  => $item->vat_type ?? 'S',
                'tax_percent'               => (float) ($item->tax_percent ?? 15),
                'vat_exemption_reason_code' => $item->vat_exemption_reason_code ?? null,
                'vat_exemption_reason'      => $item->vat_exemption_reason ?? null,
                ];
            })->toArray();
    }

    public function openSession(): void
    {
        if (!$this->selectedBranchId) {
            Notification::make()->title('اختر الفرع أولاً.')->danger()->send();
            return;
        }

        if (!$this->selectedWarehouseId) {
            Notification::make()->title('اختر المخزن قبل فتح جلسة الكاشير.')->danger()->send();
            return;
        }

        if (!$this->selectedTreasuryId) {
            Notification::make()->title('اختر خزينة الكاش قبل فتح الجلسة.')->danger()->send();
            return;
        }

        $warehouse = Warehouse::whereKey($this->selectedWarehouseId)
            ->where('is_active', true)
            ->first();

        $treasury = Treasury::whereKey($this->selectedTreasuryId)
            ->where('is_active', true)
            ->first();

        if (!$warehouse || !$treasury) {
            Notification::make()
                ->title('المخزن أو الخزينة المختارة غير صالحة/غير نشطة.')
                ->danger()
                ->send();
            return;
        }

        if ($this->selectedBankAccountId) {
            $bankOk = BankAccount::whereKey($this->selectedBankAccountId)
                ->where('is_active', true)
                ->exists();

            if (!$bankOk) {
                Notification::make()->title('الحساب البنكي المختار غير نشط.')->danger()->send();
                return;
            }
        }

        $count = PosSession::count() + 1;

        $this->currentSession = PosSession::create([
            'session_number'  => 'POS-' . str_pad($count, 4, '0', STR_PAD_LEFT),
            'user_id'         => auth()->id(),
            'branch_id'       => $this->selectedBranchId,
            'warehouse_id'    => $this->selectedWarehouseId,
            'treasury_id'     => $this->selectedTreasuryId,
            'bank_account_id' => $this->selectedBankAccountId,
            'register_name'   => 'كاشير ' . (Branch::find($this->selectedBranchId)?->name ?? '1'),
            'opening_cash'    => $this->openingCash,
            'status'          => 'open',
            'opened_at'       => now(),
        ]);

        $this->showOpenSession = false;
        $this->cartItems = [];

        Notification::make()->title('تم فتح جلسة الكاشير')->success()->send();
    }

    public function cancelOpenSession(): void
    {
        $this->showOpenSession = false;
        $this->redirect('/admin/dashboard');
    }

    public function searchProduct(): void
    {
        if (strlen($this->productSearch) < 1) { $this->productResults = []; return; }
        $term = trim($this->productSearch);

        $query = Product::query()
            ->with([
                'brand',
                'warehouseStocks.warehouse',
                'warehouseStocks.locations',
                'vehicles',
            ])
            ->where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('name_en', 'like', '%' . $term . '%')
                    ->orWhere('code', 'like', '%' . $term . '%')
                    ->orWhere('barcode', 'like', '%' . $term . '%')
                    ->orWhere('part_number', 'like', '%' . $term . '%')
                    ->orWhere('part_number_normalized', 'like', '%' . $term . '%')
                    ->orWhereHas('brand', fn ($brand) =>
                        $brand->where('name', 'like', '%' . $term . '%')
                    );
            });
        if ($this->selectedWarehouseId) {
            $warehouseId = (int) $this->selectedWarehouseId;
            $query->whereHas('warehouseStocks', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        $warehouseId = (int) ($this->selectedWarehouseId ?? $this->currentSession?->warehouse_id ?? 0);
        $this->productResults = $query->limit(12)->get()->map(function (Product $product) use ($warehouseId) {
            $row = $product->toArray();
            $row['quantity'] = $warehouseId > 0
                ? app(WarehouseInventoryService::class)->balance($product, $warehouseId)
                : 0;
            $row['brand_name'] = $product->brand?->name;
            $row['part_number'] = $product->part_number;
            $row['location_codes'] = $product->warehouseStocks
                ->flatMap(fn ($stock) => $stock->locations)
                ->pluck('location_code')
                ->filter()
                ->unique()
                ->values()
                ->implode('، ');
            $row['vehicle_summary'] = $product->vehicles
                ->take(2)
                ->map(function ($vehicle) {
                    $years = ($vehicle->year_from || $vehicle->year_to)
                        ? ' ' . ($vehicle->year_from ?? '؟') . '-' . ($vehicle->year_to ?? '؟')
                        : '';

                    return trim($vehicle->make . ' ' . ($vehicle->model ?? '') . $years);
                })
                ->implode(' • ');
            return $row;
        })->toArray();
    }

    public function addToCart(int $productId): void
    {
        if (!$this->currentSession) {
            Notification::make()->title('افتح جلسة الكاشير أولاً.')->danger()->send();
            return;
        }

        $product = Product::find($productId);
        if (!$product) return;

        $warehouseId = (int) $this->currentSession->warehouse_id;
        $warehouseBalance = app(WarehouseInventoryService::class)->balance($product, $warehouseId);

        if ($warehouseBalance <= 0) {
            Notification::make()
                ->title('الصنف غير متاح في مخزن جلسة الكاشير: ' . $product->name)
                ->danger()
                ->send();
            return;
        }

        $existingKey = null;
        foreach ($this->cartItems as $i => $item) {
            if ((int) $item['product_id'] === $productId) {
                $existingKey = $i;
                break;
            }
        }

        if ($existingKey !== null) {
            $newQty = (float) $this->cartItems[$existingKey]['quantity'] + 1;

            if ($newQty > $warehouseBalance) {
                Notification::make()
                    ->title('الكمية المطلوبة أكبر من الرصيد المتاح.')
                    ->danger()
                    ->send();
                return;
            }

            $line = $this->lineAmounts(
                $newQty,
                (float) $this->cartItems[$existingKey]['unit_price'],
                (float) ($this->cartItems[$existingKey]['discount_percent'] ?? 0),
                (string) ($this->cartItems[$existingKey]['vat_type'] ?? 'S'),
                (float) ($this->cartItems[$existingKey]['tax_percent'] ?? 15)
            );

            $this->cartItems[$existingKey]['quantity'] = $newQty;
            $this->cartItems[$existingKey]['discount_amount'] = $line['discount_amount'];
            $this->cartItems[$existingKey]['total'] = $line['total'];

            if (isset($this->cartItems[$existingKey]['id'])) {
                PosSessionItem::where('id', $this->cartItems[$existingKey]['id'])->update([
                    'quantity' => $newQty,
                    'discount_amount' => $line['discount_amount'],
                    'total'    => $line['total'],
                ]);
            }
        } else {
            $vatType = $product->vat_type ?? 'S';
            $taxPercent = (float) ($product->vat_percent ?? 15);
            $unitPrice = (float) $product->sale_price;
            $line = $this->lineAmounts(1, $unitPrice, 0, $vatType, $taxPercent);

            $newItem = [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'product_code' => $product->code,
                'image'        => $product->image,
                'unit'         => $product->unit,
                'quantity'     => 1,
                'unit_price'   => $unitPrice,
                'cost_price'   => (float) $product->cost_price,
                'discount_percent' => 0,
                'discount_amount'  => 0,
                'stock'        => $warehouseBalance,
                'total'        => $line['total'],
                'vat_type'                  => $vatType,
                'tax_percent'               => $taxPercent,
                'vat_exemption_reason_code' => $product->vat_exemption_reason_code,
                'vat_exemption_reason'      => $product->vat_exemption_reason,
            ];

            $dbPayload = $newItem;
            unset($dbPayload['image']);

            $dbItem = PosSessionItem::create([
                'pos_session_id' => $this->currentSession->id,
                ...$dbPayload,
            ]);

            $dbItem->discount_percent = 0;
            $dbItem->discount_amount = 0;
            $dbItem->save();

            $newItem['id'] = $dbItem->id;
            $this->cartItems[] = $newItem;
        }

        $this->productSearch = '';
        $this->productResults = [];
    }

    public function updateQuantity(int $index, float $qty): void
    {
        if ($qty <= 0) {
            $this->removeFromCart($index);
            return;
        }

        if (!isset($this->cartItems[$index])) return;

        $product = Product::find($this->cartItems[$index]['product_id']);
        $warehouseId = (int) ($this->currentSession?->warehouse_id ?? 0);
        $warehouseBalance = $product && $warehouseId > 0
            ? app(WarehouseInventoryService::class)->balance($product, $warehouseId)
            : 0;

        if (!$product || $qty > $warehouseBalance) {
            Notification::make()
                ->title('الكمية المطلوبة أكبر من رصيد مخزن جلسة الكاشير.')
                ->danger()
                ->send();
            return;
        }

        $line = $this->lineAmounts(
            $qty,
            (float) $this->cartItems[$index]['unit_price'],
            (float) ($this->cartItems[$index]['discount_percent'] ?? 0),
            (string) ($this->cartItems[$index]['vat_type'] ?? 'S'),
            (float) ($this->cartItems[$index]['tax_percent'] ?? 15)
        );

        $this->cartItems[$index]['quantity'] = $qty;
        $this->cartItems[$index]['discount_amount'] = $line['discount_amount'];
        $this->cartItems[$index]['total'] = $line['total'];

        if (isset($this->cartItems[$index]['id'])) {
            PosSessionItem::where('id', $this->cartItems[$index]['id'])->update([
                'quantity' => $qty,
                'discount_amount' => $line['discount_amount'],
                'total'    => $line['total'],
            ]);
        }
    }

    public function updatePrice(int $index, float $price): void
    {
        if ($price < 0 || !isset($this->cartItems[$index])) return;

        $line = $this->lineAmounts(
            (float) $this->cartItems[$index]['quantity'],
            $price,
            (float) ($this->cartItems[$index]['discount_percent'] ?? 0),
            (string) ($this->cartItems[$index]['vat_type'] ?? 'S'),
            (float) ($this->cartItems[$index]['tax_percent'] ?? 15)
        );

        $this->cartItems[$index]['unit_price'] = $price;
        $this->cartItems[$index]['discount_amount'] = $line['discount_amount'];
        $this->cartItems[$index]['total'] = $line['total'];

        if (isset($this->cartItems[$index]['id'])) {
            PosSessionItem::where('id', $this->cartItems[$index]['id'])->update([
                'unit_price' => $price,
                'discount_amount' => $line['discount_amount'],
                'total'      => $line['total'],
            ]);
        }
    }

    public function removeFromCart(int $index): void
    {
        if (isset($this->cartItems[$index]['id'])) PosSessionItem::destroy($this->cartItems[$index]['id']);
        unset($this->cartItems[$index]);
        $this->cartItems = array_values($this->cartItems);
    }

    private function lineAmounts(
        float $quantity,
        float $unitPrice,
        float $discountPercent,
        string $vatType,
        float $taxPercent
    ): array {
        $subtotal = round($quantity * $unitPrice, 2);
        $discountPercent = min(max($discountPercent, 0), 100);
        $discountAmount = round($subtotal * ($discountPercent / 100), 2);
        $afterDiscount = round(max(0, $subtotal - $discountAmount), 2);

        $effectiveRate = $vatType === 'S'
            ? max($taxPercent, 0)
            : 0;

        $tax = round($afterDiscount * ($effectiveRate / 100), 2);
        $total = round($afterDiscount + $tax, 2);

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax' => $tax,
            'total' => $total,
        ];
    }

    public function setDiscount(int $index, float $percent): void
    {
        if (!isset($this->cartItems[$index])) return;

        $percent = min(max($percent, 0), 100);
        $item = $this->cartItems[$index];
        $line = $this->lineAmounts(
            (float) $item['quantity'],
            (float) $item['unit_price'],
            $percent,
            (string) ($item['vat_type'] ?? 'S'),
            (float) ($item['tax_percent'] ?? 15)
        );

        $this->cartItems[$index]['discount_percent'] = $percent;
        $this->cartItems[$index]['discount_amount'] = $line['discount_amount'];
        $this->cartItems[$index]['total'] = $line['total'];

        if (isset($item['id'])) {
            PosSessionItem::whereKey($item['id'])->update([
                'discount_percent' => $percent,
                'discount_amount'  => $line['discount_amount'],
                'total'            => $line['total'],
            ]);
        }
    }

    public function getCartSubtotal(): float
    {
        $sum = 0;
        foreach ($this->cartItems as $item) {
            $sum += $this->lineAmounts(
                (float) $item['quantity'],
                (float) $item['unit_price'],
                (float) ($item['discount_percent'] ?? 0),
                (string) ($item['vat_type'] ?? 'S'),
                (float) ($item['tax_percent'] ?? 15)
            )['subtotal'];
        }
        return round($sum, 2);
    }

    public function getCartDiscount(): float
    {
        $sum = 0;
        foreach ($this->cartItems as $item) {
            $sum += $this->lineAmounts(
                (float) $item['quantity'],
                (float) $item['unit_price'],
                (float) ($item['discount_percent'] ?? 0),
                (string) ($item['vat_type'] ?? 'S'),
                (float) ($item['tax_percent'] ?? 15)
            )['discount_amount'];
        }
        return round($sum, 2);
    }

    public function getCartTax(): float
    {
        $sum = 0;
        foreach ($this->cartItems as $item) {
            $sum += $this->lineAmounts(
                (float) $item['quantity'],
                (float) $item['unit_price'],
                (float) ($item['discount_percent'] ?? 0),
                (string) ($item['vat_type'] ?? 'S'),
                (float) ($item['tax_percent'] ?? 15)
            )['tax'];
        }
        return round($sum, 2);
    }

    public function getCartTotal(): float
    {
        return round($this->getCartSubtotal() - $this->getCartDiscount() + $this->getCartTax(), 2);
    }

    /**
     * Generate a unique invoice number.
     *
     * Invoice::generateNumber() can return an already-used number when
     * invoices were deleted/imported or when more than one request reaches
     * the POS at nearly the same time. The UNIQUE index in the database is
     * the final authority, so we explicitly check and advance the number.
     */
    private function generateUniqueInvoiceNumber(string $type): string
    {
        $candidate = Invoice::generateNumber($type);

        if (!Invoice::where('invoice_number', $candidate)->exists()) {
            return $candidate;
        }

        if (preg_match('/^(.*?)(\d+)$/', $candidate, $matches)) {
            $prefix = $matches[1];
            $number = (int) $matches[2];
            $width  = strlen($matches[2]);

            do {
                $number++;
                $candidate = $prefix . str_pad((string) $number, $width, '0', STR_PAD_LEFT);
            } while (Invoice::where('invoice_number', $candidate)->exists());

            return $candidate;
        }

        // Fallback for an unexpected format returned by generateNumber().
        do {
            $candidate .= '-1';
        } while (Invoice::where('invoice_number', $candidate)->exists());

        return $candidate;
    }

    /**
     * Generate a unique POS transaction number.
     */
    private function generateUniqueTransactionNumber(): string
    {
        $lastNumber = PosTransaction::where('transaction_number', 'like', 'TRX-%')
            ->get(['transaction_number'])
            ->map(function ($transaction) {
                if (preg_match('/TRX-(\d+)$/', $transaction->transaction_number, $m)) {
                    return (int) $m[1];
                }
                return 0;
            })
            ->max() ?? 0;

        do {
            $lastNumber++;
            $candidate = 'TRX-' . str_pad((string) $lastNumber, 6, '0', STR_PAD_LEFT);
        } while (PosTransaction::where('transaction_number', $candidate)->exists());

        return $candidate;
    }

    public function processPayment(): void
    {
        if (empty($this->cartItems)) {
            Notification::make()->title('السلة فارغة!')->warning()->send();
            return;
        }

        if (!$this->currentSession) {
            Notification::make()->title('لا توجد جلسة كاشير مفتوحة.')->danger()->send();
            return;
        }

        $displayTotal = $this->getCartTotal();

        if ($this->paymentMethod === 'cash') {
            if ($this->cashReceived <= 0) {
                $this->cashReceived = $displayTotal;
            }

            if ($this->cashReceived + 0.009 < $displayTotal) {
                Notification::make()
                    ->title('المبلغ المستلم أقل من إجمالي الفاتورة')
                    ->body('المتبقي المطلوب: ' . number_format($displayTotal - $this->cashReceived, 2) . ' ر.س')
                    ->danger()
                    ->send();
                return;
            }
        }

        try {
            $result = DB::transaction(function () {
                $session = PosSession::query()
                    ->whereKey($this->currentSession->id)
                    ->where('status', 'open')
                    ->lockForUpdate()
                    ->first();

                if (!$session) {
                    throw new RuntimeException('جلسة الكاشير مغلقة أو غير موجودة.');
                }

                if (!$session->warehouse_id) {
                    throw new RuntimeException('جلسة الكاشير غير مرتبطة بمخزن.');
                }

                if (!$session->treasury_id) {
                    throw new RuntimeException('جلسة الكاشير غير مرتبطة بخزينة كاش.');
                }

                if (
                    in_array($this->paymentMethod, ['card', 'wallet'], true)
                    && !$session->bank_account_id
                ) {
                    throw new RuntimeException('الدفع الإلكتروني يحتاج حساباً بنكياً مرتبطاً بجلسة الكاشير.');
                }

                $normalizedItems = [];
                $subtotal = 0;
                $discountAmount = 0;
                $taxAmount = 0;
                $total = 0;

                foreach ($this->cartItems as $cartItem) {
                    $product = Product::query()
                        ->whereKey((int) $cartItem['product_id'])
                        ->firstOrFail();

                    $quantity = round((float) $cartItem['quantity'], 3);
                    $unitPrice = round((float) $cartItem['unit_price'], 2);
                    $vatType = (string) ($cartItem['vat_type'] ?? $product->vat_type ?? 'S');
                    $taxPercent = (float) ($cartItem['tax_percent'] ?? $product->vat_percent ?? 15);
                    $discountPercent = min(max((float) ($cartItem['discount_percent'] ?? 0), 0), 100);

                    if ($quantity <= 0) {
                        throw new RuntimeException('يوجد صنف بكمية غير صالحة في السلة.');
                    }

                    $line = $this->lineAmounts($quantity, $unitPrice, $discountPercent, $vatType, $taxPercent);

                    $subtotal += $line['subtotal'];
                    $discountAmount += $line['discount_amount'];
                    $taxAmount += $line['tax'];
                    $total += $line['total'];

                    $normalizedItems[] = [
                        'product'      => $product,
                        'quantity'     => $quantity,
                        'unit_price'   => $unitPrice,
                        'discount_percent' => $discountPercent,
                        'discount_amount'  => $line['discount_amount'],
                        'cost_price'   => round((float) $product->cost_price, 2),
                        'vat_type'     => $vatType,
                        'tax_percent'  => $vatType === 'S' ? max($taxPercent, 0) : 0,
                        'tax_amount'   => $line['tax'],
                        'total'        => $line['total'],
                        'vat_exemption_reason_code' => $cartItem['vat_exemption_reason_code'] ?? $product->vat_exemption_reason_code,
                        'vat_exemption_reason'      => $cartItem['vat_exemption_reason'] ?? $product->vat_exemption_reason,
                    ];
                }

                $subtotal = round($subtotal, 2);
                $taxAmount = round($taxAmount, 2);
                $total = round($total, 2);

                if ($total <= 0) {
                    throw new RuntimeException('إجمالي الفاتورة غير صالح.');
                }

                if (
                    $this->paymentMethod === 'cash'
                    && $this->cashReceived + 0.009 < $total
                ) {
                    throw new RuntimeException('المبلغ المستلم أقل من إجمالي الفاتورة بعد إعادة التحقق.');
                }

                /*
                 * IMPORTANT:
                 * Invoice starts confirmed + unpaid.
                 * InvoicePostingService posts the commercial obligation first:
                 * Dr AR / Cr Sales+VAT + COGS + Stock.
                 * ReceiptPostingService then settles AR and makes the real
                 * treasury/bank movement in the SAME outer transaction.
                 */
                $invoice = Invoice::create([
                    'invoice_number'   => $this->generateUniqueInvoiceNumber('sale'),
                    'type'             => 'sale',
                    'payment_type'     => 'cash', // means immediate sale; tender is stored on POS/Receipt
                    'customer_id'      => $this->selectedCustomerId ?: null,
                    'branch_id'        => $session->branch_id,
                    'warehouse_id'     => $session->warehouse_id,
                    'invoice_date'     => today(),
                    'subtotal'         => $subtotal,
                    'discount_amount'  => $discountAmount,
                    'discount_percent' => 0,
                    'tax_amount'       => $taxAmount,
                    'tax_percent'      => 0, // line-level VAT can be mixed
                    'total'            => $total,
                    'paid_amount'      => 0,
                    'remaining_amount' => $total,
                    'status'           => 'confirmed',
                    'notes'            => 'POS ' . $session->session_number . ' — ' . strtoupper($this->paymentMethod),
                    'created_by'       => auth()->id(),
                ]);

                $receiptItems = [];

                foreach ($normalizedItems as $row) {
                    $product = $row['product'];

                    InvoiceItem::create([
                        'invoice_id'        => $invoice->id,
                        'product_id'        => $product->id,
                        'product_name'      => $product->name,
                        'product_code'      => $product->code,
                        'unit'              => $product->unit,
                        'quantity'          => $row['quantity'],
                        'unit_price'        => $row['unit_price'],
                        'cost_price'        => $row['cost_price'],
                        'discount_percent'  => $row['discount_percent'],
                        'discount_amount'   => $row['discount_amount'],
                        'tax_percent'       => $row['tax_percent'],
                        'tax_amount'        => $row['tax_amount'],
                        'total'             => $row['total'],
                        'vat_type'          => $row['vat_type'],
                        'vat_exemption_reason_code' => $row['vat_exemption_reason_code'],
                        'vat_exemption_reason'      => $row['vat_exemption_reason'],
                    ]);

                    $receiptItems[] = [
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'product_code' => $product->code,
                        'unit'         => $product->unit,
                        'quantity'     => $row['quantity'],
                        'unit_price'   => $row['unit_price'],
                        'total'        => $row['total'],
                    ];
                }

                $invoice->refresh()->load('items', 'customer');

                // Step 9 service: GL commercial posting + COGS + StockMovement atomically.
                app(InvoicePostingService::class)->post($invoice);

                $isCash = $this->paymentMethod === 'cash';

                $receipt = app(ReceiptPostingService::class)->post([
                    'invoice_id'       => $invoice->id,
                    'customer_id'      => $invoice->customer_id,
                    'allow_anonymous'  => !$invoice->customer_id,
                    'amount'           => $total,
                    'receipt_date'     => today(),
                    'destination_type' => $isCash ? 'treasury' : 'bank',
                    'treasury_id'      => $isCash ? $session->treasury_id : null,
                    'bank_account_id'  => $isCash ? null : $session->bank_account_id,
                    'payment_method'   => $this->paymentMethod,
                    'reference'        => $session->session_number,
                    'description'      => 'تحصيل نقطة بيع — ' . $invoice->invoice_number,
                ]);

                $change = $isCash
                    ? round(max(0, $this->cashReceived - $total), 2)
                    : 0;

                $posTransaction = PosTransaction::create([
                    'pos_session_id'     => $session->id,
                    'invoice_id'         => $invoice->id,
                    'transaction_number' => $this->generateUniqueTransactionNumber(),
                    'payment_method'     => $this->paymentMethod,
                    'amount'             => $total,
                    'cash_amount'        => $isCash ? round((float) $this->cashReceived, 2) : 0,
                    'card_amount'        => $this->paymentMethod === 'card' ? $total : 0,
                    'wallet_amount'      => $this->paymentMethod === 'wallet' ? $total : 0,
                    'change_amount'      => $change,
                    'type'               => 'sale',
                    'notes'              => 'Receipt ' . $receipt->voucher_number,
                ]);

                $sessionUpdates = [
                    'total_sales'        => round((float) $session->total_sales + $total, 2),
                    'transactions_count' => (int) $session->transactions_count + 1,
                ];

                if ($this->paymentMethod === 'cash') {
                    $sessionUpdates['total_cash'] = round((float) $session->total_cash + $total, 2);
                } elseif ($this->paymentMethod === 'card') {
                    $sessionUpdates['total_card'] = round((float) $session->total_card + $total, 2);
                } elseif ($this->paymentMethod === 'wallet') {
                    $sessionUpdates['total_wallet'] = round((float) $session->total_wallet + $total, 2);
                }

                $session->update($sessionUpdates);

                PosSessionItem::where('pos_session_id', $session->id)->delete();

                return [
                    'invoice_id'   => $invoice->id,
                    'number'       => $invoice->invoice_number,
                    'total'        => $total,
                    'cash'         => $isCash ? $this->cashReceived : $total,
                    'change'       => $change,
                    'items'        => $receiptItems,
                    'customer'     => $invoice->customer?->name ?? 'عميل نقدي',
                    'date'         => now()->format('d/m/Y H:i'),
                    'cashier'      => auth()->user()->name,
                    'branch'       => $session->branch?->name ?? '',
                    'payment_type' => $this->paymentMethod,
                    'receipt'      => $receipt->voucher_number,
                    'pos_tx'       => $posTransaction->transaction_number,
                ];
            });

            $this->currentSession->refresh();
            $this->lastTransaction = $result;
            $this->cartItems = [];
            $this->showSuccess = true;
            $this->selectedCustomerId = null;
            $this->cashReceived = 0;
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('فشل إتمام البيع — لم يتم ترحيل أي جزء من العملية')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function closeSuccess(): void
    {
        $this->showSuccess = false;
        $this->lastTransaction = null;
    }

    public function closeSession(): void
    {
        PosSessionItem::where('pos_session_id', $this->currentSession->id)->delete();
        $this->currentSession->update([
            'status'       => 'closed',
            'closing_cash' => $this->currentSession->opening_cash + $this->currentSession->total_cash,
            'closed_at'    => now(),
        ]);
        $this->currentSession = null;
        $this->showOpenSession = true;
        $this->cartItems = [];
        Notification::make()->title('تم إغلاق الجلسة بنجاح')->success()->send();
    }

    // ==================== المرتجع ====================

    public function openReturnModal(): void
    {
        if (!$this->currentSession || $this->currentSession->status !== 'open') {
            Notification::make()
                ->title('افتح جلسة كاشير قبل تسجيل المرتجع.')
                ->danger()
                ->send();
            return;
        }

        $this->resetReturnState();
        $this->showReturnModal = true;
    }

    private function resetReturnState(): void
    {
        $this->returnInvoiceNumber = '';
        $this->returnSearchResults = [];
        $this->returnInvoice = null;
        $this->returnItems = [];
        $this->returnReason = 'defective';
        $this->returnError = '';
        $this->returnPaymentMethodLabel = '';
        $this->returnSourceLabel = '';
        $this->returnReceiptNumber = '';
    }

    public function closeReturnModal(): void
    {
        $this->showReturnModal = false;
        $this->resetReturnState();
    }

    public function searchReturnInvoice(): void
    {
        $this->updatedReturnInvoiceNumber($this->returnInvoiceNumber);
    }

    public function clearReturnInvoiceSelection(): void
    {
        $this->returnInvoice = null;
        $this->returnItems = [];
        $this->returnError = '';
        $this->returnPaymentMethodLabel = '';
        $this->returnSourceLabel = '';
        $this->returnReceiptNumber = '';
    }

    public function updatedReturnInvoiceNumber(string $value): void
    {
        $this->returnError = '';
        $this->returnInvoice = null;
        $this->returnItems = [];
        $this->returnSearchResults = [];

        $value = trim($value);

        if (strlen($value) < 2 || !$this->currentSession) {
            return;
        }

        $session = $this->currentSession;

        /*
         * Only show fully-settled POS sales in the same branch/warehouse.
         * Legacy sales without a POS transaction + posted receipt are excluded.
         */
        $this->returnSearchResults = Invoice::withoutGlobalScopes()
            ->with('customer')
            ->where('type', 'sale')
            ->where('status', 'paid')
            ->where('branch_id', $session->branch_id)
            ->where('warehouse_id', $session->warehouse_id)
            ->whereIn(
                'id',
                PosTransaction::query()
                    ->select('invoice_id')
                    ->where('type', 'sale')
                    ->whereNotNull('invoice_id')
            )
            ->whereIn(
                'id',
                ReceiptVoucher::query()
                    ->select('invoice_id')
                    ->where('status', 'posted')
            )
            ->where(function ($q) use ($value) {
                $q->where('invoice_number', 'like', '%' . $value . '%')
                    ->orWhereHas(
                        'customer',
                        fn ($q2) => $q2->where('name', 'like', '%' . $value . '%')
                    );
            })
            ->latest('id')
            ->limit(8)
            ->get()
            ->toArray();
    }

    public function selectReturnInvoice(int $invoiceId): void
    {
        $this->returnError = '';

        try {
            if (!$this->currentSession) {
                throw new RuntimeException('لا توجد جلسة كاشير مفتوحة.');
            }

            $invoice = Invoice::withoutGlobalScopes()
                ->with(['items', 'customer'])
                ->findOrFail($invoiceId);

            [$saleTransaction, $receipt] = $this->resolveOriginalPosSettlement(
                $invoice,
                $this->currentSession,
                false
            );

            $returned = $this->returnedItemSummary($invoice->id);

            $this->returnInvoice = $invoice;
            $this->returnSearchResults = [];
            $this->returnInvoiceNumber = $invoice->invoice_number;
            $this->returnPaymentMethodLabel = $this->paymentMethodLabel(
                (string) $saleTransaction->payment_method
            );
            $this->returnSourceLabel = $receipt->destination_label;
            $this->returnReceiptNumber = $receipt->voucher_number;

            $this->returnItems = $invoice->items->map(function ($item) use ($returned) {
                $summary = $returned[(int) $item->id] ?? null;

                $originalQty = round((float) $item->quantity, 3);
                $returnedQty = round((float) ($summary->returned_qty ?? 0), 3);
                $remainingQty = round(max($originalQty - $returnedQty, 0), 3);

                $originalTotal = round((float) $item->total, 2);
                $returnedTotal = round((float) ($summary->returned_total ?? 0), 2);

                return [
                    'invoice_item_id'           => $item->id,
                    'product_id'                => $item->product_id,
                    'product_name'              => $item->product_name,
                    'product_code'              => $item->product_code,
                    'unit'                      => $item->unit,
                    'unit_price'                => (float) $item->unit_price,
                    'cost_price'                => (float) $item->cost_price,
                    'original_qty'              => $originalQty,
                    'returned_qty'              => $returnedQty,
                    'remaining_qty'             => $remainingQty,
                    'return_qty'                => 0,
                    'original_total'            => $originalTotal,
                    'remaining_total'           => round(max($originalTotal - $returnedTotal, 0), 2),
                    'discount_percent'          => (float) ($item->discount_percent ?? 0),
                    'discount_amount'           => (float) ($item->discount_amount ?? 0),
                    'vat_type'                  => $item->vat_type ?? 'S',
                    'tax_percent'               => (float) ($item->tax_percent ?? 0),
                    'tax_amount'                => (float) ($item->tax_amount ?? 0),
                    'vat_exemption_reason_code' => $item->vat_exemption_reason_code,
                    'vat_exemption_reason'      => $item->vat_exemption_reason,
                ];
            })->toArray();

            if (collect($this->returnItems)->sum('remaining_qty') <= 0.0009) {
                $this->returnError = 'تم إرجاع كل كميات هذه الفاتورة بالفعل.';
            }
        } catch (\Throwable $e) {
            report($e);
            $this->returnInvoice = null;
            $this->returnItems = [];
            $this->returnError = $e->getMessage();
        }
    }

    public function getReturnTotal(): float
    {
        $total = 0;

        foreach ($this->returnItems as $item) {
            $qty = round((float) ($item['return_qty'] ?? 0), 3);
            $originalQty = round((float) ($item['original_qty'] ?? 0), 3);
            $remainingQty = round((float) ($item['remaining_qty'] ?? 0), 3);

            if ($qty <= 0 || $originalQty <= 0 || $remainingQty <= 0) {
                continue;
            }

            $qty = min($qty, $remainingQty);

            if (abs($qty - $remainingQty) <= 0.0009) {
                $total += round((float) ($item['remaining_total'] ?? 0), 2);
            } else {
                $total += round(
                    ((float) ($item['original_total'] ?? 0)) * ($qty / $originalQty),
                    2
                );
            }
        }

        return round($total, 2);
    }

    public function processReturn(): void
    {
        if (!$this->returnInvoice || !$this->currentSession) {
            $this->returnError = 'اختر فاتورة بيع صالحة أولاً.';
            return;
        }

        $requested = collect($this->returnItems)
            ->filter(fn ($row) => (float) ($row['return_qty'] ?? 0) > 0)
            ->mapWithKeys(fn ($row) => [
                (int) $row['invoice_item_id'] => round((float) $row['return_qty'], 3),
            ]);

        if ($requested->isEmpty()) {
            $this->returnError = 'حدد كمية واحدة على الأقل للمرتجع.';
            return;
        }

        try {
            $result = DB::transaction(function () use ($requested) {
                $session = PosSession::query()
                    ->whereKey($this->currentSession->id)
                    ->where('status', 'open')
                    ->lockForUpdate()
                    ->first();

                if (!$session) {
                    throw new RuntimeException('جلسة الكاشير مغلقة أو غير موجودة.');
                }

                /*
                 * Keep lock order compatible with Receipt reversal:
                 * ReceiptVoucher -> Invoice.
                 * This reduces deadlock risk if a receipt reversal and a POS
                 * return are attempted at the same moment.
                 */
                $prelockedReceipts = ReceiptVoucher::query()
                    ->where('invoice_id', $this->returnInvoice->id)
                    ->where('status', 'posted')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($prelockedReceipts->count() !== 1) {
                    throw new RuntimeException(
                        'الفاتورة لا تحتوي على سند قبض POS واحد مرحّل. تم إيقاف الرد الآلي للمبلغ.'
                    );
                }

                $original = Invoice::withoutGlobalScopes()
                    ->whereKey($this->returnInvoice->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $original->load(['items', 'customer']);

                [$saleTransaction, $receipt] = $this->resolveOriginalPosSettlement(
                    $original,
                    $session,
                    true
                );

                $returned = $this->returnedItemSummary($original->id);
                $rows = [];

                $subtotal = 0;
                $discountAmount = 0;
                $taxAmount = 0;
                $total = 0;

                foreach ($original->items as $item) {
                    $qty = round((float) ($requested[(int) $item->id] ?? 0), 3);

                    if ($qty <= 0) {
                        continue;
                    }

                    $originalQty = round((float) $item->quantity, 3);

                    if ($originalQty <= 0) {
                        throw new RuntimeException('كمية أصلية غير صالحة في الفاتورة.');
                    }

                    $summary = $returned[(int) $item->id] ?? null;

                    $alreadyQty = round((float) ($summary->returned_qty ?? 0), 3);
                    $alreadyTax = round((float) ($summary->returned_tax ?? 0), 2);
                    $alreadyDiscount = round((float) ($summary->returned_discount ?? 0), 2);
                    $alreadyTotal = round((float) ($summary->returned_total ?? 0), 2);

                    $remainingQty = round(max($originalQty - $alreadyQty, 0), 3);

                    if ($remainingQty <= 0.0009) {
                        throw new RuntimeException(
                            'تم إرجاع كامل كمية الصنف بالفعل: ' . $item->product_name
                        );
                    }

                    if ($qty - $remainingQty > 0.0009) {
                        throw new RuntimeException(
                            'كمية المرتجع أكبر من المتاح للصنف ' .
                            $item->product_name .
                            '. المتاح للمرتجع: ' .
                            number_format($remainingQty, 3)
                        );
                    }

                    $originalTax = round((float) ($item->tax_amount ?? 0), 2);
                    $originalDiscount = round((float) ($item->discount_amount ?? 0), 2);
                    $originalTotal = round((float) $item->total, 2);

                    $isFinalRemainder = abs($qty - $remainingQty) <= 0.0009;

                    if ($isFinalRemainder) {
                        // Use residual values on the last return to eliminate rounding drift.
                        $lineTax = round(max($originalTax - $alreadyTax, 0), 2);
                        $lineDiscount = round(max($originalDiscount - $alreadyDiscount, 0), 2);
                        $lineTotal = round(max($originalTotal - $alreadyTotal, 0), 2);
                    } else {
                        $ratio = $qty / $originalQty;
                        $lineTax = round($originalTax * $ratio, 2);
                        $lineDiscount = round($originalDiscount * $ratio, 2);
                        $lineTotal = round($originalTotal * $ratio, 2);
                    }

                    if ($lineTotal <= 0) {
                        throw new RuntimeException(
                            'قيمة مرتجع غير صالحة للصنف: ' . $item->product_name
                        );
                    }

                    /*
                     * subtotal - discount + VAT = total
                     * Using the original financial snapshot avoids recalculating
                     * historical VAT/discount rules from the current Product row.
                     */
                    $lineSubtotal = round($lineTotal - $lineTax + $lineDiscount, 2);

                    $subtotal += $lineSubtotal;
                    $discountAmount += $lineDiscount;
                    $taxAmount += $lineTax;
                    $total += $lineTotal;

                    $rows[] = [
                        'original_item' => $item,
                        'quantity'      => $qty,
                        'subtotal'      => $lineSubtotal,
                        'discount'      => $lineDiscount,
                        'tax'           => $lineTax,
                        'total'         => $lineTotal,
                    ];
                }

                if (empty($rows)) {
                    throw new RuntimeException('لا توجد كميات صالحة للمرتجع.');
                }

                $subtotal = round($subtotal, 2);
                $discountAmount = round($discountAmount, 2);
                $taxAmount = round($taxAmount, 2);
                $total = round($total, 2);

                if ($total <= 0) {
                    throw new RuntimeException('إجمالي المرتجع غير صالح.');
                }

                if ((string) $saleTransaction->payment_method === 'cash') {
                    $expectedDrawerCash = round(
                        (float) $session->opening_cash + (float) $session->total_cash,
                        2
                    );

                    if ($expectedDrawerCash + 0.009 < $total) {
                        throw new RuntimeException(
                            'النقد المتوقع في جلسة الكاشير غير كافٍ لرد هذا المرتجع. ' .
                            'المتاح: ' . number_format($expectedDrawerCash, 2) . ' ر.س.'
                        );
                    }
                }

                $returnInvoice = Invoice::create([
                    'invoice_number'      => $this->generateUniqueInvoiceNumber('sale_return'),
                    'type'                => 'sale_return',
                    'payment_type'        => 'cash', // immediate refund semantics
                    'customer_id'         => $original->customer_id,
                    'branch_id'           => $original->branch_id,
                    'warehouse_id'        => $original->warehouse_id,
                    'original_invoice_id' => $original->id,
                    'invoice_date'        => today(),
                    'subtotal'            => $subtotal,
                    'discount_amount'     => $discountAmount,
                    'discount_percent'    => 0,
                    'tax_amount'          => $taxAmount,
                    'tax_percent'         => 0,
                    'total'               => $total,
                    'paid_amount'         => 0,
                    'remaining_amount'    => $total,
                    'status'              => 'confirmed',
                    'notes'               => 'POS Return for ' . $original->invoice_number .
                        ' — ' . $this->returnReasonLabel($this->returnReason),
                    'created_by'          => auth()->id(),
                ]);

                foreach ($rows as $row) {
                    $item = $row['original_item'];

                    InvoiceItem::create([
                        'invoice_id'               => $returnInvoice->id,
                        'original_invoice_item_id' => $item->id,
                        'product_id'               => $item->product_id,
                        'product_name'             => $item->product_name,
                        'product_code'             => $item->product_code,
                        'unit'                     => $item->unit,
                        'quantity'                 => $row['quantity'],
                        'unit_price'               => $item->unit_price,
                        'cost_price'               => $item->cost_price,
                        'discount_percent'         => $item->discount_percent ?? 0,
                        'discount_amount'          => $row['discount'],
                        'tax_percent'              => $item->tax_percent ?? 0,
                        'tax_amount'               => $row['tax'],
                        'total'                    => $row['total'],
                        'vat_type'                 => $item->vat_type ?? 'S',
                        'vat_exemption_reason_code'=> $item->vat_exemption_reason_code,
                        'vat_exemption_reason'     => $item->vat_exemption_reason,
                        'notes'                    => 'مرتجع من ' . $original->invoice_number,
                    ]);
                }

                $returnInvoice->refresh()->load(['items', 'customer']);

                /*
                 * Commercial return:
                 * Dr Sales + Dr VAT / Cr AR
                 * Dr Inventory / Cr COGS
                 * plus stock IN, all through the Step 9 service.
                 */
                app(InvoicePostingService::class)->post($returnInvoice);

                /*
                 * Real refund settlement:
                 * Dr AR / Cr original Treasury or Bank.
                 * PaymentPostingService updates the return invoice to paid and
                 * records the operational cash/bank withdrawal atomically.
                 */
                $payment = app(PaymentPostingService::class)->post([
                    'purpose'         => 'customer_refund',
                    'invoice_id'      => $returnInvoice->id,
                    'amount'          => $total,
                    'payment_date'    => today(),
                    'source_type'     => $receipt->destination_type,
                    'treasury_id'     => $receipt->destination_type === 'treasury'
                        ? $receipt->treasury_id
                        : null,
                    'bank_account_id' => $receipt->destination_type === 'bank'
                        ? $receipt->bank_account_id
                        : null,
                    'payment_method'  => $saleTransaction->payment_method,
                    'beneficiary_name'=> $original->customer?->name ?? 'عميل نقدي',
                    'reference'       => $session->session_number,
                    'description'     => 'رد قيمة مرتجع نقطة بيع — ' .
                        $returnInvoice->invoice_number .
                        ' عن ' . $original->invoice_number,
                ]);

                $method = (string) $saleTransaction->payment_method;

                $posTransaction = PosTransaction::create([
                    'pos_session_id'     => $session->id,
                    'invoice_id'         => $returnInvoice->id,
                    'transaction_number' => $this->generateUniqueTransactionNumber(),
                    'payment_method'     => $method,
                    'amount'             => $total,
                    'cash_amount'        => $method === 'cash' ? $total : 0,
                    'card_amount'        => $method === 'card' ? $total : 0,
                    'wallet_amount'      => $method === 'wallet' ? $total : 0,
                    'change_amount'      => 0,
                    'type'               => 'refund',
                    'notes'              => 'Refund ' . $payment->voucher_number .
                        ' | Original ' . $original->invoice_number,
                ]);

                $sessionUpdates = [
                    'total_refunds'      => round((float) $session->total_refunds + $total, 2),
                    'transactions_count' => (int) $session->transactions_count + 1,
                ];

                if ($method === 'cash') {
                    $sessionUpdates['total_cash'] = round((float) $session->total_cash - $total, 2);
                } elseif ($method === 'card') {
                    $sessionUpdates['total_card'] = round((float) $session->total_card - $total, 2);
                } elseif ($method === 'wallet') {
                    $sessionUpdates['total_wallet'] = round((float) $session->total_wallet - $total, 2);
                }

                /*
                 * Keep total_sales as GROSS sales.
                 * Net sales = total_sales - total_refunds.
                 * Tender totals are NET financial movement for the session.
                 */
                $session->update($sessionUpdates);

                return [
                    'return_invoice'   => $returnInvoice->invoice_number,
                    'original_invoice' => $original->invoice_number,
                    'payment_voucher'  => $payment->voucher_number,
                    'pos_transaction'  => $posTransaction->transaction_number,
                    'amount'           => $total,
                    'method'           => $this->paymentMethodLabel($method),
                ];
            });

            $this->currentSession->refresh();
            $this->closeReturnModal();

            Notification::make()
                ->title('تم تسجيل مرتجع POS ورد المبلغ بنجاح')
                ->body(
                    $result['return_invoice'] .
                    ' — ' . number_format($result['amount'], 2) . ' ر.س' .
                    ' — ' . $result['method'] .
                    ' — ' . $result['payment_voucher']
                )
                ->success()
                ->send();
        } catch (\Throwable $e) {
            report($e);

            $this->returnError = $e->getMessage();

            Notification::make()
                ->title('فشل المرتجع — لم يتم ترحيل أي جزء من العملية')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Return the original POS sale transaction and its ONE posted receipt.
     * Automated refund is rejected whenever the original settlement is
     * ambiguous, reversed, legacy, or points to another session endpoint.
     */
    private function resolveOriginalPosSettlement(
        Invoice $invoice,
        PosSession $session,
        bool $lockForUpdate
    ): array {
        if ($invoice->type !== 'sale') {
            throw new RuntimeException('يمكن إرجاع فاتورة بيع فقط من شاشة POS.');
        }

        if ($invoice->status !== 'paid') {
            throw new RuntimeException('مرتجع POS يتطلب فاتورة بيع مسددة بالكامل.');
        }

        if ((bool) ($invoice->getAttribute('zatca_sent') ?? false)) {
            throw new RuntimeException(
                'الفاتورة مرسلة إلى زاتكا. يلزم مسار Credit Note متوافق مع زاتكا قبل المرتجع.'
            );
        }

        if ((int) $invoice->branch_id !== (int) $session->branch_id) {
            throw new RuntimeException('الفاتورة تخص فرعًا مختلفًا عن جلسة الكاشير الحالية.');
        }

        if ((int) $invoice->warehouse_id !== (int) $session->warehouse_id) {
            throw new RuntimeException(
                'الفاتورة تخص مخزنًا مختلفًا. تم إيقاف المرتجع لمنع حركة مخزون بين مخازن غير مؤكدة.'
            );
        }

        $txQuery = PosTransaction::query()
            ->where('invoice_id', $invoice->id)
            ->where('type', 'sale')
            ->orderBy('id');

        if ($lockForUpdate) {
            $txQuery->lockForUpdate();
        }

        $saleTransactions = $txQuery->get();

        if ($saleTransactions->count() !== 1) {
            throw new RuntimeException(
                'لا يمكن تحديد عملية POS الأصلية بشكل وحيد. لن يتم تخمين وسيلة الدفع.'
            );
        }

        $saleTransaction = $saleTransactions->first();
        $method = (string) $saleTransaction->payment_method;

        if (!in_array($method, ['cash', 'card', 'wallet'], true)) {
            throw new RuntimeException('وسيلة الدفع الأصلية غير مدعومة في مرتجع POS الحالي.');
        }

        $receiptQuery = ReceiptVoucher::query()
            ->where('invoice_id', $invoice->id)
            ->where('status', 'posted')
            ->orderBy('id');

        if ($lockForUpdate) {
            $receiptQuery->lockForUpdate();
        }

        $receipts = $receiptQuery->get();

        if ($receipts->count() !== 1) {
            throw new RuntimeException(
                'الفاتورة لا تحتوي على سند قبض POS واحد مرحّل. تم إيقاف الرد الآلي للمبلغ.'
            );
        }

        $receipt = $receipts->first();

        if (abs((float) $receipt->amount - (float) $invoice->total) > 0.009) {
            throw new RuntimeException(
                'قيمة سند القبض الأصلي لا تطابق إجمالي فاتورة POS. يلزم مراجعة يدوية.'
            );
        }

        if ((string) $receipt->payment_method !== $method) {
            throw new RuntimeException(
                'وسيلة الدفع في سند القبض لا تطابق عملية POS الأصلية.'
            );
        }

        if ($method === 'cash') {
            if ($receipt->destination_type !== 'treasury' || !$receipt->treasury_id) {
                throw new RuntimeException('بيع الكاش الأصلي غير مرتبط بخزينة صالحة للرد.');
            }

            if ((int) $receipt->treasury_id !== (int) $session->treasury_id) {
                throw new RuntimeException(
                    'خزينة جلسة الكاشير الحالية تختلف عن خزينة البيع الأصلية.'
                );
            }
        } else {
            if ($receipt->destination_type !== 'bank' || !$receipt->bank_account_id) {
                throw new RuntimeException('الدفع الإلكتروني الأصلي غير مرتبط بحساب بنكي صالح للرد.');
            }

            if ((int) $receipt->bank_account_id !== (int) $session->bank_account_id) {
                throw new RuntimeException(
                    'الحساب البنكي لجلسة الكاشير الحالية يختلف عن حساب البيع الأصلي.'
                );
            }
        }

        return [$saleTransaction, $receipt];
    }

    /**
     * Aggregate active linked return items by original invoice item.
     * Cancelled/reversed invoice returns are excluded by status.
     */
    private function returnedItemSummary(int $originalInvoiceId): array
    {
        return DB::table('invoice_items as ri')
            ->join('invoices as r', 'r.id', '=', 'ri.invoice_id')
            ->where('r.original_invoice_id', $originalInvoiceId)
            ->where('r.type', 'sale_return')
            ->whereIn('r.status', ['confirmed', 'partial', 'paid'])
            ->whereNull('r.deleted_at')
            ->whereNotNull('ri.original_invoice_item_id')
            ->groupBy('ri.original_invoice_item_id')
            ->selectRaw(
                'ri.original_invoice_item_id,' .
                ' SUM(ri.quantity) as returned_qty,' .
                ' SUM(ri.tax_amount) as returned_tax,' .
                ' SUM(ri.discount_amount) as returned_discount,' .
                ' SUM(ri.total) as returned_total'
            )
            ->get()
            ->keyBy(fn ($row) => (int) $row->original_invoice_item_id)
            ->all();
    }

    private function paymentMethodLabel(string $method): string
    {
        return match ($method) {
            'cash'   => 'نقدي',
            'card'   => 'بطاقة',
            'wallet' => 'محفظة',
            default  => $method,
        };
    }

    private function returnReasonLabel(string $reason): string
    {
        return match ($reason) {
            'defective'       => 'صنف معيب',
            'wrong'           => 'صنف خاطئ',
            'customer_change' => 'تغيير رأي العميل',
            'other'           => 'أخرى',
            default           => $reason,
        };
    }
}
