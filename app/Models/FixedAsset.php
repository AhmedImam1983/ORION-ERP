<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Models\Concerns\EnforcesBranchAssignment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FixedAsset extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope());
    }

    use EnforcesBranchAssignment;

    protected $fillable = [
        'code', 'name', 'category', 'branch_id', 'purchase_date',
        'purchase_price', 'salvage_value', 'useful_life_years',
        'depreciation_method', 'depreciation_rate', 'accumulated_depreciation',
        'net_book_value', 'last_depreciation_date', 'status',
        'location', 'serial_number', 'supplier', 'notes', 'created_by',
    ];

    protected $casts = [
        'purchase_date'           => 'date',
        'last_depreciation_date'  => 'date',
        'purchase_price'          => 'decimal:2',
        'salvage_value'           => 'decimal:2',
        'accumulated_depreciation'=> 'decimal:2',
        'net_book_value'          => 'decimal:2',
        'depreciation_rate'       => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function depreciations(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class);
    }

    public function calculateMonthlyDepreciation(): float
    {
        if ($this->depreciation_method === 'straight_line') {
            $depreciableAmount = $this->purchase_price - $this->salvage_value;
            return round($depreciableAmount / ($this->useful_life_years * 12), 2);
        } else {
            // declining balance
            return round($this->net_book_value * ($this->depreciation_rate / 100) / 12, 2);
        }
    }

    public function processMonthlyDepreciation(): void
    {
        if ($this->status !== 'active') return;
        if ($this->net_book_value <= $this->salvage_value) return;

        $amount = $this->calculateMonthlyDepreciation();
        $amount = min($amount, $this->net_book_value - $this->salvage_value);

        $newAccumulated = $this->accumulated_depreciation + $amount;
        $newNetValue    = $this->purchase_price - $newAccumulated;

        $depreciation = AssetDepreciation::create([
            'fixed_asset_id'   => $this->id,
            'depreciation_date'=> now(),
            'year'             => now()->year,
            'month'            => now()->month,
            'amount'           => $amount,
            'accumulated'      => $newAccumulated,
            'net_book_value'   => $newNetValue,
        ]);

        $this->update([
            'accumulated_depreciation' => $newAccumulated,
            'net_book_value'           => $newNetValue,
            'last_depreciation_date'   => now(),
        ]);
    }
}