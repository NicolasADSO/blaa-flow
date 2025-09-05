<?php

namespace App\Filament\Widgets;

use App\Models\ControlProcess;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class ProcessStats extends BaseWidget
{
    /** Carga diferida para no bloquear el dashboard */
    protected static bool $isLazy = true;

    /** Sin sondeo automático; si quieres, usa '120s' */
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // Cacheamos 60s; se invalida en eventos del modelo (abajo)
        $data = Cache::remember('widget:process_stats:v1', 60, function () {
            $map = ControlProcess::query()
                ->selectRaw('status, COUNT(*) as cnt')
                ->groupBy('status')
                ->pluck('cnt', 'status');

            $pend = (int) ($map['Pendiente'] ?? 0);
            $proc = (int) ($map['En Proceso'] ?? 0);
            $fin  = (int) ($map['Finalizado'] ?? 0);

            return [
                'total' => $pend + $proc + $fin,
                'pend'  => $pend,
                'proc'  => $proc,
                'fin'   => $fin,
            ];
        });

        return [
            Stat::make('Total de Procesos', number_format($data['total']))
                ->description('Registrados')
                ->icon('lucide-layers')
                ->color('primary'),

            Stat::make('Finalizados', number_format($data['fin']))
                ->description('Completados')
                ->icon('lucide-check-circle')
                ->color('success'),

            Stat::make('En Proceso', number_format($data['proc']))
                ->description('En curso')
                ->icon('lucide-refresh-ccw')
                ->color('info'),

            Stat::make('Pendientes', number_format($data['pend']))
                ->description('Sin iniciar')
                ->icon('lucide-clock')
                ->color('warning'),
        ];
    }

    public static function canView(): bool
    {
        // Tu rol es 'Admin'
        return auth()->check() && auth()->user()->hasRole('Admin');
    }
}
