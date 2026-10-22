<?php

namespace App\Filament\Traits;

trait HasResourcePermissions
{
    public static function canAccess(): bool
    {
        $permission = 'view_' . static::getPermissionPrefix();
        return auth()->user()?->hasRole('super_admin') 
            || auth()->user()?->can($permission);
    }

    public static function canCreate(): bool
    {
        $permission = 'create_' . static::getPermissionPrefix();
        return auth()->user()?->hasRole('super_admin')
            || auth()->user()?->can($permission);
    }

    public static function canEdit($record): bool
    {
        $permission = 'edit_' . static::getPermissionPrefix();
        return auth()->user()?->hasRole('super_admin')
            || auth()->user()?->can($permission);
    }

    public static function canDelete($record): bool
    {
        $permission = 'delete_' . static::getPermissionPrefix();
        return auth()->user()?->hasRole('super_admin')
            || auth()->user()?->can($permission);
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    protected static function getPermissionPrefix(): string
    {
        // بيرجع اسم الـ Resource بالـ snake_case
        // مثلاً: ProductResource → products
        $class = class_basename(static::class);
        $name  = str_replace('Resource', '', $class);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name)) . 's';
    }
}