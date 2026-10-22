<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'code', 'name', 'name_en', 'address', 'phone',
        'manager', 'warehouse_id', 'is_active', 'is_main', 'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_main'   => 'boolean',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function posSessions(): HasMany
    {
        return $this->hasMany(PosSession::class);
    }

    public static function getMain(): ?self
    {
        return static::where('is_main', true)->first()
            ?? static::where('is_active', true)->first();
    }
}