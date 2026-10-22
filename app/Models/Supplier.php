<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'phone2',
        'email',
        'address',
        'tax_number',
        'opening_balance',
        'balance_type',
        'bank_name',
        'bank_account',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'opening_balance' => 'decimal:2',
    ];
public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(\App\Models\Invoice::class);
}
}