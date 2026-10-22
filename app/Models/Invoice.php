<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number', 'type', 'payment_type',
        'customer_id', 'supplier_id',
        'invoice_date', 'due_date',
        'subtotal', 'discount_amount', 'discount_percent',
        'tax_amount', 'tax_percent',
        'total', 'paid_amount', 'remaining_amount',
        'status', 'warehouse', 'notes', 'created_by',
        'branch_id', 'warehouse_id', 'original_invoice_id',
        'eta_uuid', 'eta_long_id', 'eta_hash_key',
        'eta_status', 'eta_submitted_at', 'eta_error',
    ];

    protected $casts = [
        'invoice_date'     => 'date',
        'due_date'         => 'date',
        'subtotal'         => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'tax_percent'      => 'decimal:2',
        'total'            => 'decimal:2',
        'paid_amount'      => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope());

        // تعيين branch_id تلقائياً عند الإنشاء
        static::creating(function ($invoice) {
            if (empty($invoice->branch_id) && auth()->check()) {
                $invoice->branch_id = auth()->user()->branch_id;
            }
            if (empty($invoice->created_by) && auth()->check()) {
                $invoice->created_by = auth()->id();
            }
        });
    }

    public function originalInvoice(): BelongsTo
    {
        return $this->belongsTo(self::class, 'original_invoice_id');
    }

    public function returnInvoices(): HasMany
    {
        return $this->hasMany(self::class, 'original_invoice_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }

    public function calculateTotals(): void
    {
        $this->subtotal         = $this->items->sum('total');
        $this->discount_amount  = $this->subtotal * ($this->discount_percent / 100);
        $afterDiscount          = $this->subtotal - $this->discount_amount;
        $this->tax_amount       = $afterDiscount * ($this->tax_percent / 100);
        $this->total            = $afterDiscount + $this->tax_amount;
        $this->remaining_amount = $this->total - $this->paid_amount;
    }

    public function updatePaidAmount(): void
    {
        $totalPaid = $this->installments()->sum('paid_amount');
        $this->update([
            'paid_amount'      => $totalPaid,
            'remaining_amount' => max(0, $this->total - $totalPaid),
            'status'           => $totalPaid >= $this->total ? 'paid' : 'partial',
        ]);
    }

    public static function generateNumber(string $type = 'sale'): string
    {
        $prefix = match($type) {
            'sale'            => 'INV',
            'purchase'        => 'PUR',
            'sale_return'     => 'SRT',
            'purchase_return' => 'PRT',
            default           => 'INV',
        };
        $last   = static::withoutGlobalScopes()->where('type', $type)->latest()->first();
        $number = $last ? (intval(substr($last->invoice_number, -6)) + 1) : 1;
        return $prefix . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}