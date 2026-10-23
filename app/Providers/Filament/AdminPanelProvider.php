<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use App\Filament\Admin\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession as StartSession;
use Illuminate\Session\Middleware\StartSession as StartSessionMiddleware;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Admin\Pages\Auth\Login;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
           ->login(Login::class)
            ->colors([
                'primary' => [
                    50  => '#ede9fe',
                    100 => '#ddd6fe',
                    200 => '#c4b5fd',
                    300 => '#a78bfa',
                    400 => '#8b5cf6',
                    500 => '#7c3aed',
                    600 => '#6d28d9',
                    700 => '#5b21b6',
                    800 => '#4c1d95',
                    900 => '#2e1065',
                    950 => '#1e0a6e',
                ],
            ])
            ->font('Cairo')
            ->darkMode(true)
            ->maxContentWidth('full')
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->brandLogo(asset('images/orion-logo.png'))
            ->favicon(asset('images/orion-logo.png'))
            ->sidebarWidth('18rem')
            ->navigationGroups([
                NavigationGroup::make('المبيعات · Sales'),
                NavigationGroup::make('المشتريات · Purchases'),
                NavigationGroup::make('المخازن · Warehouse'),
                NavigationGroup::make('العملاء والموردين · CRM'),
                NavigationGroup::make('الحسابات · Accounting'),
                NavigationGroup::make('الموارد البشرية · HR'),
                NavigationGroup::make('الإعدادات · Settings')
                    ->collapsed(),
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->resources([
                \App\Filament\Admin\Resources\StockCounts\StockCountResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSessionMiddleware::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->renderHook(
                'panels::body.end',
                fn () => new \Illuminate\Support\HtmlString('
                    <script>
                        document.addEventListener("livewire:initialized", () => {
                            Livewire.on("open-barcode-tab", (event) => {
                                window.open(event.url, "_blank");
                            });
                        });
                    </script>
                ')
            )
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}