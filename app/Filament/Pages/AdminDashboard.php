<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\ProcesosStatsOverview;
use App\Filament\Widgets\ProcesosPorEstadoChart;
use App\Filament\Widgets\ProcesosPorFaseChart;
use App\Filament\Widgets\ProcesosPorMesChart;
use App\Filament\Widgets\ProcesosPorProveedorChart;
use App\Filament\Widgets\ProcesosPorResponsableChart;
use App\Filament\Widgets\ProcesosPorDuracionChart;
use App\Filament\Widgets\ProcesosAtrasadosChart;

class AdminDashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'lucide-home';
    protected static ?string $navigationLabel = 'Dashboard Admin';
    protected static ?string $title = '📊 Panel de Estadísticas';

    /** 🔹 Solo visible para Admin */
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    /** 🔹 Orden y disposición de los widgets */
    public function getWidgets(): array
    {
        return [
            // 🟦 Fila 1 (stats → fila completa)
            ProcesosStatsOverview::class,

            ProcesosPorResponsableChart::class,

            // 🟦 Fila 2 (estado y fase → 2 columnas cada uno)
            ProcesosPorEstadoChart::class,
            ProcesosPorFaseChart::class,

            // 🟦 Fila 3 (línea de tiempo → fila completa)
            ProcesosPorMesChart::class,

            // 🟦 Fila 4 (comparativas → 2 columnas cada uno)
          
            

            // 🟦 Fila 5 (indicadores especiales → 2 columnas cada uno)
            ProcesosPorDuracionChart::class,
            ProcesosAtrasadosChart::class,

            ProcesosPorProveedorChart::class,
        ];
    }

    /** 🔹 Columnas globales */
    public function getColumns(): int
    {
        return 4; // ✅ grid de 4 columnas → más flexible y ordenado
    }
}