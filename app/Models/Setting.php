<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type', 'label'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::where('key', $key)->first();

        if (!$row) return $default;

        return match ($row->type) {
            'boolean' => (bool) $row->value,
            'integer' => (int)  $row->value,
            'json'    => json_decode($row->value, true),
            default   => $row->value,
        };
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string) $value]
        );
    }

    public static function group(string $group): array
    {
        return static::where('group', $group)->pluck('value', 'key')->toArray();
    }

    public static function clearGroupCache(string $group): void
    {
        // no-op
    }
}