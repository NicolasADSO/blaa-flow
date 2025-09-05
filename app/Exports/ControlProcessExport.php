<?php

namespace App\Exports;

use App\Models\ControlProcess;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ControlProcessExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected $process;

    public function __construct(ControlProcess $process)
    {
        $this->process = $process;
    }

    public function array(): array
    {
        return [[
            $this->process->order_number,
            $this->process->book_title,
            $this->process->provider,
            $this->process->status,
            $this->process->phase?->name ?? 'N/A',
            $this->process->responsible,
            optional($this->process->start_date)->format('d/m/Y H:i'),
            optional($this->process->end_date)->format('d/m/Y H:i'),
        ]];
    }

    public function headings(): array
    {
        return [
            'N° Pedido',
            'Título',
            'Proveedor',
            'Estado',
            'Fase actual',
            'Responsable',
            'Fecha inicio',
            'Fecha fin',
        ];
    }
}
