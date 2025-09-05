<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

// 👇 dashboard principal
use App\Filament\Pages\AdminDashboard;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('BlaaFlow') // texto alternativo del logo
            ->brandLogo(asset('images/logo-sin-fondo.png'))
            ->brandLogoHeight('4.5rem')
            ->favicon(asset('images/favicon-16x16.png'))

            // 🎨 Forzar tema claro
            ->darkMode(false)

            // 🎨 Colores corporativos y de estado
            ->colors([
                'primary'   => '#990f0c',   // Rojo corporativo
                'secondary' => '#C2BABA',   // Gris corporativo
                'success'   => '#28a745',   // Verde éxito
                'warning'   => '#ffc107',   // Amarillo advertencia
                'danger'    => '#990f0c',   // Rojo corporativo (también para errores)
                'info'      => '#17a2b8',   // Azul informativo
            ])

            ->viteTheme('resources/css/app.css') // Carga tu CSS compilado (app.css + gadier.css)

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                AdminDashboard::class, // solo dashboard custom
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class, // 👤 solo este fijo
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
