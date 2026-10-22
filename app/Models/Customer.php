<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;



class Customer extends Model
{
    use SoftDeletes;
public function invoices(): HasMany
{
    return $this->hasMany(Invoice::class);
}
    protected $fillable = [
        'code',
        'name',
        'phone',
        'phone2',
        'email',
        'address',
        'tax_number',
        'national_id',
        'credit_limit',
        'opening_balance',
        'balance_type',
        'type',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'credit_limit'    => 'decimal:2',
        'opening_balance' => 'decimal:2',
    ];
}