<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class StockMovement extends Model
{
    protected static function booted(): void
    {
        static::creating(function (StockMovement $movement): void {
            if (!$movement->product_id) {
                return;
            }

            $product = Product::withTrashed()->find($movement->product_id);
            if ($product?->isService()) {
                throw new RuntimeException('لا يمكن إنشاء حركة مخزون لخدمة غير مخزنية.');
            }
        });

        static::addGlobalScope('branch_inventory_movements', function (Builder $builder): void {
            if (!auth()->check()) {
                return;
            }

            $user = auth()->user();
            if ($user->hasAnyRole(['super_admin', 'admin'])) {
                return;
            }

            $branchId = (int) ($user->branch_id ?? 0);
            if ($branchId <= 0) {
                $builder->whereRaw('1 = 0');
                return;
            }

            $branch = Branch::query()
                ->whereKey($branchId)
                ->where('is_active', true)
                ->first();

            if (!$branch) {
                $builder->whereRaw('1 = 0');
                return;
            }

            $warehouseId = $branch->warehouse_id;

            // Legacy fallback is limited to the main branch. Other branches need
            // an explicit warehouse mapping before branch-specific transfer rows show.
            if (!$warehouseId && $branch->is_main) {
                $warehouseId = Warehouse::query()
                    ->where('is_active', true)
                    ->orderByDesc('is_default')
                    ->orderBy('id')
                    ->value('id');
            }

            $builder->where(function (Builder $query) use ($branchId, $warehouseId): void {
                $query->where('branch_id', $branchId);

                if ($warehouseId) {
                    $query->orWhere(function (Builder $shared) use ($warehouseId): void {
                        $shared->whereNull('branch_id')
                            ->where('warehouse_id', $warehouseId);
                    });
                }
            });
        });
    }

    protected $fillable = [
        'product_id', 'warehouse_id', 'branch_id', 'invoice_id',
        'type', 'quantity', 'unit_price', 'total',
        'balance_before', 'balance_after', 'reference', 'notes', 'created_by',
    ];

    protected $casts = [
        'quantity'       => 'decimal:3',
        'unit_price'     => 'decimal:2',
        'total'          => 'decimal:2',
        'balance_before' => 'decimal:3',
        'balance_after'  => 'decimal:3',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'purchase'        => 'شراء',
            'sale'            => 'بيع',
            'sale_return'     => 'مرتجع بيع',
            'purchase_return' => 'مرتجع شراء',
            'transfer_in'     => 'تحويل وارد',
            'transfer_out'    => 'تحويل صادر',
            'adjustment_in'   => 'تسوية زيادة',
            'adjustment_out'  => 'تسوية نقص',
            'opening'         => 'رصيد افتتاحي',
            default           => $this->type,
        };
    }

    public function isIncoming(): bool
    {
        return in_array($this->type, ['purchase', 'sale_return', 'transfer_in', 'adjustment_in', 'opening']);
    }
}