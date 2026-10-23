<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Models\Concerns\EnforcesBranchAssignment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope());
    }

    use EnforcesBranchAssignment;

    protected $fillable = [
        'order_number', 'supplier_id', 'warehouse_id', 'branch_id',
        'order_date', 'expected_date', 'status',
        'subtotal', 'discount_percent', 'discount_amount',
        'tax_percent', 'tax_amount', 'total', 'received_amount',
        'notes', 'created_by',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'expected_date' => 'date',
        'subtotal'      => 'decimal:2',
        'total'         => 'decimal:2',
        'received_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateNumber(): string
    {
        $last = static::withoutGlobalScopes()->latest()->first();
        $number = $last ? (intval(substr($last->order_number, -6)) + 1) : 1;
        return 'PO-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}