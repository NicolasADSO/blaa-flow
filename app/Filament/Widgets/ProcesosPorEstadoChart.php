<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ControlProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProcesosPorEstadoChart extends ChartWidget
{
    /** Render perezoso (mejor TTFB) */
    protected static bool $isLazy = true;

    /** Título */
    public function getHeading(): ?string
    {
        return '📊 Procesos por Estado';
    }

    /** Visible solo para Admin */
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    /** Grid span */
    public function getColumnSpan(): int | string | array
    {
        return [
            'default' => 1,
            'sm'      => 2,
            'lg'      => 2,
        ];
    }

    /** 🔄 Auto-actualiza cada 10s */
    protected function getPollingInterval(): ?string
    {
        return '10s';
    }

    /** Datos del gráfico */
    protected function getData(): array
    {
        // 🚀 Consulta + caché 30s
        $counts = Cache::remember('chart:cp_by_status:v1', 30, function () {
            return ControlProcess::query()
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status'); // ['Pendiente' => n, 'En Proceso' => m, ...]
        });

        // Asegurar orden consistente
        $labels = ['Pendiente', 'En Proceso', 'Finalizado'];
        $data   = array_map(fn ($s) => (int) ($counts[$s] ?? 0), $labels);

        return [
            'datasets' => [[
                'label'           => 'Procesos',
                'data'            => $data,
                'backgroundColor' => ['#facc15', '#3b82f6', '#22c55e'], // Pendi / En proceso / Finalizado
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
