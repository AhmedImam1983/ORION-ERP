<?php

namespace App\Models;

use App\Services\WarehouseInventoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockCount extends Model
{
    protected $fillable = [
        'count_number', 'type', 'status',
        'warehouse_id', 'branch_id', 'category_id',
        'count_date', 'notes',
        'created_by', 'approved_by', 'approved_at',
        'journal_entry_id',
    ];

    protected $casts = [
        'count_date'  => 'date',
        'approved_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }
    public function items(): HasMany { return $this->hasMany(StockCountItem::class); }

    public static function generateNumber(): string
    {
        $last = static::latest()->first();
        $num  = $last ? (intval(substr($last->count_number, -6)) + 1) : 1;
        return 'CNT-' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Load the products that actually belong to the selected warehouse stock ledger.
     */
    public function loadProducts(): void
    {
        if (!$this->warehouse_id) {
            throw new RuntimeException('حدد المخزن قبل تحميل أصناف الجرد.');
        }

        $stocks = WarehouseStock::query()
            ->with('product')
            ->where('warehouse_id', $this->warehouse_id)
            ->whereHas('product', function ($q) {
                $q->where('is_active', true);

                if ($this->category_id) {
                    $q->where('category_id', $this->category_id);
                }
            })
            ->orderBy('product_id')
            ->get();

        foreach ($stocks as $stock) {
            $product = $stock->product;

            if (!$product) {
                continue;
            }

            $this->items()->firstOrCreate(
                ['product_id' => $product->id],
                [
                    'system_quantity'  => round((float) $stock->quantity, 3),
                    'actual_quantity'  => null,
                    'difference'       => 0,
                    'cost_price'       => $product->cost_price,
                    'difference_value' => 0,
                ]
            );
        }

        $this->update(['status' => 'in_progress']);
    }

    /**
     * Approve the count against the selected warehouse only.
     * A stale count is rejected if stock changed after the count was loaded.
     */
    public function approve(): void
    {
        DB::transaction(function () {
            $count = static::query()
                ->whereKey($this->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($count->status !== 'in_progress') {
                return;
            }

            if (!$count->warehouse_id) {
                throw new RuntimeException('لا يمكن اعتماد جرد بدون مخزن محدد.');
            }

            $items = $count->items()
                ->with('product')
                ->orderBy('product_id')
                ->get();

            $totalDiff = 0.0;
            $inventory = app(WarehouseInventoryService::class);

            foreach ($items as $item) {
                if ($item->actual_quantity === null) {
                    continue;
                }

                $product = Product::query()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $stock = WarehouseStock::query()
                    ->where('product_id', $product->id)
                    ->where('warehouse_id', $count->warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$stock) {
                    throw new RuntimeException(
                        'الصنف ' . $product->name . ' لم يعد مرتبطاً بالمخزن المحدد.'
                    );
                }

                $current = round((float) $stock->quantity, 3);
                $snapshot = round((float) $item->system_quantity, 3);

                if (abs($current - $snapshot) > 0.0009) {
                    throw new RuntimeException(
                        'تعذر اعتماد الجرد لأن رصيد الصنف ' . $product->name .
                        ' تغير بعد تحميل الجرد. الرصيد وقت التحميل ' .
                        number_format($snapshot, 3) . ' والحالي ' . number_format($current, 3) .
                        '. أعد تحميل/إنشاء الجرد قبل الاعتماد.'
                    );
                }

                $actual = round((float) $item->actual_quantity, 3);

                if ($actual < 0) {
                    throw new RuntimeException('الكمية الفعلية لا يمكن أن تكون سالبة.');
                }

                $diff = round($actual - $current, 3);
                $diffValue = round($diff * (float) $item->cost_price, 2);

                $item->update([
                    'difference'       => $diff,
                    'difference_value' => $diffValue,
                ]);

                if (abs($diff) <= 0.0009) {
                    continue;
                }

                $stock->update(['quantity' => $actual]);
                $inventory->syncProductTotal($product);

                StockMovement::create([
                    'product_id'     => $product->id,
                    'warehouse_id'   => $count->warehouse_id,
                    'branch_id'      => $count->branch_id,
                    'invoice_id'     => null,
                    'type'           => $diff > 0 ? 'adjustment_in' : 'adjustment_out',
                    'quantity'       => abs($diff),
                    'unit_price'     => round((float) $item->cost_price, 2),
                    'total'          => abs($diffValue),
                    'balance_before' => $current,
                    'balance_after'  => $actual,
                    'reference'      => $count->count_number,
                    'notes'          => 'فرق جرد مخزني',
                    'created_by'     => auth()->id() ?? $count->created_by ?? 1,
                ]);

                $totalDiff += $diffValue;
            }

            if (abs($totalDiff) > 0.009) {
                $count->createJournalEntry(round($totalDiff, 2));
            }

            $count->update([
                'status'      => 'completed',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        $this->refresh();
    }

    private function createJournalEntry(float $totalDiff): void
    {
        // Controlled mapping: inventory and inventory-count variance.
        $inventoryAccount  = Account::where('code', '1301')->where('is_active', true)->first();
        $adjustmentAccount = Account::where('code', '1303')->where('is_active', true)->first();

        if (!$inventoryAccount || !$adjustmentAccount) {
            throw new RuntimeException(
                'تعذر ترحيل فرق الجرد: الحساب 1301 (المخزون) أو 1303 (فروقات جرد المخزون) غير موجود/غير نشط.'
            );
        }

        $entry = JournalEntry::create([
            'entry_number' => JournalEntry::generateNumber(),
            'entry_date'   => $this->count_date ?? today(),
            'reference'    => $this->count_number,
            'description'  => 'قيد جرد مخزني — ' . $this->count_number,
            'status'       => 'posted',
            'created_by'   => auth()->id() ?? $this->created_by ?? 1,
            'total_debit'  => abs($totalDiff),
            'total_credit' => abs($totalDiff),
        ]);

        if ($totalDiff > 0) {
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $inventoryAccount->id,
                'debit'            => $totalDiff,
                'credit'           => 0,
                'description'      => 'زيادة مخزون — ' . $this->count_number,
            ]);
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $adjustmentAccount->id,
                'debit'            => 0,
                'credit'           => $totalDiff,
                'description'      => 'فروقات جرد — ' . $this->count_number,
            ]);
        } else {
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $adjustmentAccount->id,
                'debit'            => abs($totalDiff),
                'credit'           => 0,
                'description'      => 'فروقات جرد — ' . $this->count_number,
            ]);
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $inventoryAccount->id,
                'debit'            => 0,
                'credit'           => abs($totalDiff),
                'description'      => 'نقص مخزون — ' . $this->count_number,
            ]);
        }

        $this->update(['journal_entry_id' => $entry->id]);
    }
}
