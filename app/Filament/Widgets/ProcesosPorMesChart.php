<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ControlProcess;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProcesosPorMesChart extends ChartWidget
{
    protected static bool $isLazy = true;

    public static ?string $heading = '📈 Procesos creados por mes (últimos 12 meses)';

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
        $now   = Carbon::now();
        $start = $now->copy()->subMonths(11)->startOfMonth();

        // 🚀 Consulta única con agrupación mensual + caché (30s)
        $rows = Cache::remember('chart:cp_by_month:v1', 30, function () use ($start, $now) {
            return ControlProcess::query()
                ->whereBetween('created_at', [$start, $now])
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, COUNT(*) as total')
                ->groupBy('ym')
                ->orderBy('ym')
                ->pluck('total', 'ym');
        });

        // Construimos los 12 meses consecutivos, rellenando con 0 si falta alguno
        $labels = [];
        $data   = [];

        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonths($i);
            $ym    = $month->format('Y-m');

            $labels[] = $month->translatedFormat('M Y'); // Ej: Ene 2025
            $data[]   = (int) ($rows[$ym] ?? 0);
        }

        return [
            'datasets' => [[
                'label' => 'Procesos creados',
                'data'  => $data,
                'borderColor'           => '#3b82f6',
                'backgroundColor'       => 'rgba(59,130,246,0.3)',
                'fill'                  => true,
                'tension'               => 0.35,
                'pointBackgroundColor'  => '#3b82f6',
                'pointRadius'           => 3,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
