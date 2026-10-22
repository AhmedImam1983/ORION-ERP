<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    protected string $view = 'filament.admin.pages.auth.login';

    public function getHeading(): string | Htmlable | null
    {
        return 'مرحباً بعودتك 👋';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return 'سجل دخولك للوصول إلى حسابك';
    }
}