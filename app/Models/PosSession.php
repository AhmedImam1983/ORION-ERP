<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSession extends Model
{
    protected $fillable = [
        'session_number', 'user_id', 'branch_id', 'warehouse_id',
        'treasury_id', 'bank_account_id', 'register_name',
        'opening_cash', 'closing_cash', 'total_sales', 'total_cash',
        'total_card', 'total_wallet', 'total_refunds', 'transactions_count',
        'status', 'opened_at', 'closed_at', 'notes',
    ];

    protected $casts = [
        'opening_cash'  => 'decimal:2',
        'closing_cash'  => 'decimal:2',
        'total_sales'   => 'decimal:2',
        'total_cash'    => 'decimal:2',
        'total_card'    => 'decimal:2',
        'total_wallet'  => 'decimal:2',
        'total_refunds' => 'decimal:2',
        'opened_at'     => 'datetime',
        'closed_at'     => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope());

        static::creating(function ($session) {
            if (empty($session->branch_id) && auth()->check()) {
                $session->branch_id = auth()->user()->branch_id;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function treasury(): BelongsTo
    {
        return $this->belongsTo(Treasury::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PosTransaction::class);
    }
}
