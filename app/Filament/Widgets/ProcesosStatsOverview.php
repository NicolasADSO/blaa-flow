<?php

namespace App\Filament\Widgets;

use App\Models\ControlProcess;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class ProcesosStatsOverview extends Widget
{
    protected static string $view = 'filament.widgets.procesos-stats-overview';

    protected ?string $heading = '📈 Resumen de Procesos';

    // 🔸 Carga diferida (no consulta hasta que el widget es visible)
    protected static bool $isLazy = true;

    // 🔸 Sin auto-refresh (o pon '120s' si quieres refrescar cada 2 min)
    protected static ?string $pollingInterval = null;

    /** Solo visible para Admin */
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    /** Ocupar toda la fila en pantallas grandes */
    public function getColumnSpan(): int|string|array
    {
        return [
            'default' => 1,
            'sm'      => 2,
            'lg'      => 4,
        ];
    }

    /** Distribución interna de 4 columnas para las cards */
    protected function getColumns(): int
    {
        return 4;
    }

    /** Datos para el Blade (cacheados) */
    protected function getViewData(): array
    {
        // Cachea 60s; ajusta el TTL si lo necesitas
        $data = Cache::remember('widget:proc_stats:v1', 60, function () {
            // 1 sola consulta agrupada por status
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
            'stats' => [
                [
                    'label'       => 'Total de Procesos',
                    'value'       => $data['total'],
                    'description' => 'Procesos registrados',
                    'icon'        => 'lucide-layers',
                    'color'       => 'primary',
                ],
                [
                    'label'       => 'Pendientes',
                    'value'       => $data['pend'],
                    'description' => 'Procesos sin iniciar',
                    'icon'        => 'lucide-clock',
                    'color'       => 'warning',
                ],
                [
                    'label'       => 'En Proceso',
                    'value'       => $data['proc'],
                    'description' => 'Procesos en curso',
                    'icon'        => 'lucide-refresh-ccw',
                    'color'       => 'info',
                ],
                [
                    'label'       => 'Finalizados',
                    'value'       => $data['fin'],
                    'description' => 'Procesos completados',
                    'icon'        => 'lucide-check-circle',
                    'color'       => 'success',
                ],
            ],
        ];
    }
}
