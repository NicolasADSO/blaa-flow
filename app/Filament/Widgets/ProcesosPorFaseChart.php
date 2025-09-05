<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Phase;
use App\Models\ControlProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProcesosPorFaseChart extends ChartWidget
{
    protected static bool $isLazy = true;

    public static ?string $heading = '📌 Procesos por Fase';

    /** Solo visible para Admin */
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    public function getColumnSpan(): int | string | array
    {
        return [
            'default' => 1,
            'sm'      => 2,
            'lg'      => 'full',
        ];
    }

    /** 🔄 Auto-actualiza cada 10 segundos */
    protected function getPollingInterval(): ?string
    {
        return '10s';
    }

    protected function getData(): array
    {
        // 🚀 Conteos por fase cacheados 30s
        $counts = Cache::remember('chart:cp_by_phase:v1', 30, function () {
            return ControlProcess::query()
                ->select('phase_id', DB::raw('COUNT(*) as total'))
                ->groupBy('phase_id')
                ->pluck('total', 'phase_id'); // [phase_id => total]
        });

        // Fases ordenadas por su "order"
        $phases = Phase::query()
            ->orderBy('order')
            ->pluck('name', 'id');           // [id => name]

        // Construcción de labels/datos respetando el orden de fases
        $labels = [];
        $data   = [];
        foreach ($phases as $id => $name) {
            $labels[] = $name;
            $data[]   = (int) ($counts[$id] ?? 0);
        }

        return [
            'datasets' => [[
                'label'           => 'Procesos',
                'data'            => $data,
                'backgroundColor' => '#3b82f6',
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
