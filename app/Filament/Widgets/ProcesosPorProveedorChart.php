<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ControlProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProcesosPorProveedorChart extends ChartWidget
{
    protected static bool $isLazy = true;

    public static ?string $heading = '📦 Procesos por Proveedor';

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

    /** 🔄 Auto-actualiza cada 10s */
    protected function getPollingInterval(): ?string
    {
        return '10s';
    }

    protected function getData(): array
    {
        // 🔹 Consulta única con agregaciones por estado + caché
        $rows = Cache::remember('chart:cp_by_provider:v1', 30, function () {
            return ControlProcess::query()
                ->select([
                    'provider',
                    DB::raw('COUNT(*) as total'),
                    DB::raw("SUM(CASE WHEN status = 'Pendiente'   THEN 1 ELSE 0 END) as pendientes"),
                    DB::raw("SUM(CASE WHEN status = 'En Proceso'  THEN 1 ELSE 0 END) as en_proceso"),
                    DB::raw("SUM(CASE WHEN status = 'Finalizado'  THEN 1 ELSE 0 END) as finalizados"),
                ])
                ->groupBy('provider')
                ->orderByDesc('total')
                ->limit(8) // Top 8 proveedores
                ->get();
        });

        $labels       = $rows->pluck('provider')->toArray();
        $pendientes   = $rows->pluck('pendientes')->map(fn ($v) => (int) $v)->toArray();
        $enProceso    = $rows->pluck('en_proceso')->map(fn ($v) => (int) $v)->toArray();
        $finalizados  = $rows->pluck('finalizados')->map(fn ($v) => (int) $v)->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Pendiente',
                    'data'  => $pendientes,
                    'backgroundColor' => '#facc15', // Amarillo
                ],
                [
                    'label' => 'En Proceso',
                    'data'  => $enProceso,
                    'backgroundColor' => '#3b82f6', // Azul
                ],
                [
                    'label' => 'Finalizado',
                    'data'  => $finalizados,
                    'backgroundColor' => '#22c55e', // Verde
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
