<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ControlProcess;
use App\Models\Phase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProcesosPorDuracionChart extends ChartWidget
{
    /** Render perezoso para mejor TTFB */
    protected static bool $isLazy = true;

    public static ?string $heading = '⏱️ Duración promedio por Fase (días)';

    /** Solo Admin */
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    /** Span responsivo */
    public function getColumnSpan(): int | string | array
    {
        return [
            'default' => 1,
            'sm'      => 2,
            'lg'      => 'full',
        ];
    }

    /** 🔄 Auto-actualiza cada 15s */
    protected function getPollingInterval(): ?string
    {
        return '15s';
    }

    protected function getData(): array
    {
        // Cacheamos la lista de fases (estable) 5 min
        $fases = Cache::remember('chart:phases:list:v1', 300, function () {
            return Phase::query()
                ->orderBy('order')
                ->get(['id', 'name']);
        });

        // Promedio de duración por fase (en días, con decimales) — cache 60s
        $promedios = Cache::remember('chart:avg_duration_by_phase:v1', 60, function () {
            return ControlProcess::query()
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->selectRaw('phase_id, AVG(TIMESTAMPDIFF(HOUR, start_date, end_date))/24 as avg_days')
                ->groupBy('phase_id')
                ->pluck('avg_days', 'phase_id'); // [phase_id => float]
        });

        $labels = [];
        $data   = [];
        $colors = [];

        $palette = [
            '#3b82f6', '#22c55e', '#f97316', '#a855f7',
            '#ef4444', '#14b8a6', '#facc15', '#ec4899',
        ];

        foreach ($fases as $i => $fase) {
            $labels[] = $fase->name;
            $valor    = (float) ($promedios[$fase->id] ?? 0);
            $data[]   = round($valor, 1);
            $colors[] = $palette[$i % count($palette)];
        }

        return [
            'datasets' => [[
                'label'           => 'Duración promedio (días)',
                'data'            => $data,
                'backgroundColor' => $colors,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
