<?php

namespace App\Http\Controllers;

use App\Models\ControlProcess;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ControlProcessesExport;

class ProcessExportController extends Controller
{
    /** 📊 Exportar a Excel (solo este proceso) */
    public function excel(ControlProcess $process)
    {
        return Excel::download(
            new ControlProcessesExport($process), 
            "Proceso_{$process->order_number}.xlsx"
        );
    }

    /** 📄 Exportar a PDF */
    public function pdf(ControlProcess $process)
    {
        $pdf = Pdf::loadView('pdf.control_process', compact('process'));
        return $pdf->download("Proceso_{$process->order_number}.pdf");
    }

    /** 📝 Generar Acta Final */
    public function acta(ControlProcess $process)
    {
        // 🔹 Cargar todas las relaciones necesarias de manera segura
        $process->loadMissing([
            'phaseLogs.phase',
            'phaseLogs.user',
            'restorations.user',
            'bindings.user',
            'digitalizations.user',
            'qualityControls.user',
            'deliveries.user',
        ]);

        // 🔹 Renderizar la vista y generar el PDF
        $pdf = Pdf::loadView('pdf.acta_final', compact('process'));

        return $pdf->download("ActaFinal_{$process->order_number}.pdf");
    }
}
