<?php

namespace App\Filament\Resources\ControlProcessResource\Pages;

use App\Filament\Resources\ControlProcessResource;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\ControlProcessResource\RelationManagers\PhaseLogsRelationManager;
use Filament\Actions;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ControlProcessesExport;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewControlProcess extends ViewRecord
{
    protected static string $resource = ControlProcessResource::class;

    /**
     * Relación para mostrar en pestañas
     */
    public function getRelationManagers(): array
    {
        return [
            PhaseLogsRelationManager::class,
        ];
    }

    /**
     * Forzar que los relation managers se muestren en pestañas
     */
    protected function hasCombinedRelationManagerTabsWithForm(): bool
    {
        return false;
    }

    /**
     * Botones de acciones extra en la vista de detalle
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),

            // 📤 Exportar a Excel
            Actions\Action::make('exportExcel')
                ->label('Exportar Excel')
                ->icon('lucide-file-down')
                ->action(fn () =>
                    Excel::download(new ControlProcessesExport([$this->record]), 'control_process_'.$this->record->id.'.xlsx')
                ),

            // 📄 Exportar a PDF
            Actions\Action::make('exportPdf')
                ->label('Exportar PDF')
                ->icon('lucide-file-text')
                ->action(function () {
                    $pdf = Pdf::loadView('exports.control_process_pdf', [
                        'process' => $this->record,
                    ]);
                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'control_process_'.$this->record->id.'.pdf'
                    );
                }),

            // 📑 Acta Final (solo si está finalizado)
            Actions\Action::make('exportActa')
                ->label('Generar Acta Final')
                ->icon('lucide-clipboard-check')
                ->visible(fn () => $this->record->status === 'Finalizado')
                ->action(function () {
                    $pdf = Pdf::loadView('exports.control_process_acta', [
                        'process' => $this->record,
                    ]);
                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'acta_final_'.$this->record->id.'.pdf'
                    );
                }),
        ];
    }
}
