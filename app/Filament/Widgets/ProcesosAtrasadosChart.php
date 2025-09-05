<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\ControlProcess;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ProcesosAtrasadosChart extends ChartWidget
{
    protected static bool $isLazy = true;

    public static ?string $heading = '⏰ Procesos Atrasados por Proveedor';

    /** Solo Admin */
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    /** Span responsivo */
    public function getColumnSpan(): int|string|array
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
        $hoy = Carbon::today();

        // Usa delivery_date como fecha compromiso (cámbiala a end_date si así lo manejas)
        $cacheKey = 'chart:overdue_by_provider:v1';

        $resultados = Cache::remember($cacheKey, 60, function () use ($hoy) {
            return ControlProcess::query()
                ->selectRaw('COALESCE(provider, "—") as prov, COUNT(*) as total')
                ->whereNotNull('delivery_date')                // tiene fecha compromiso
                ->whereDate('delivery_date', '<', $hoy)        // ya venció
                ->where('status', '!=', 'Finalizado')          // aún no finalizado
                ->groupBy('prov')
                ->orderByDesc('total')
                ->limit(10)                                    // top 10
                ->pluck('total', 'prov');                      // [proveedor => total]
        });

        $labels = $resultados->keys()->toArray();
        $data   = $resultados->values()->toArray();

        return [
            'datasets' => [[
                'label'           => 'Atrasados',
                'data'            => $data,
                'backgroundColor' => '#ef4444',
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
