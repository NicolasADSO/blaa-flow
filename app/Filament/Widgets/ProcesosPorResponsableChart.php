<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ControlProcess;
use Illuminate\Support\Facades\Cache;

class ProcesosPorResponsableChart extends ChartWidget
{
    /** Carga diferida para no bloquear el dashboard */
    protected static bool $isLazy = true;

    /** No usamos sondeo; refresca al invalidar la caché */
    protected static ?string $pollingInterval = null;

    public static ?string $heading = '👤 Procesos por Responsable';

    /** 🔒 Solo Admin */
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    public function getColumnSpan(): int|string|array
    {
        return [
            'default' => 1,
            'sm'      => 2,
            'lg'      => 2,
        ];
    }

    protected function getData(): array
    {
        // Cacheamos 5 min y lo invalidamos en el modelo (ver snippet abajo)
        $rows = Cache::remember('chart:cp_by_responsible:v1', 300, function () {
            return ControlProcess::query()
                ->leftJoin('users', 'users.id', '=', 'control_processes.responsible_id')
                ->selectRaw("COALESCE(users.name, 'Sin responsable') as name, COUNT(*) as total")
                ->groupBy('name')
                ->orderByDesc('total')
                ->get();
        });

        // Limitamos a los 8 primeros y agregamos "Otros" para legibilidad
        $topN   = 8;
        $top    = $rows->take($topN);
        $others = $rows->slice($topN);

        $labels = $top->pluck('name')->all();
        $data   = $top->pluck('total')->map(fn ($v) => (int) $v)->all();

        if ($others->isNotEmpty()) {
            $labels[] = 'Otros';
            $data[]   = (int) $others->sum('total');
        }

        return [
            'datasets' => [[
                'label'           => 'Procesos',
                'data'            => $data,
                // (Dejamos que Chart.js use su paleta por defecto; si quieres,
                // puedes fijar colores aquí con un array de hexadecimales.)
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
