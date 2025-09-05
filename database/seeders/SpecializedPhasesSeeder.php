<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ControlProcess;
use App\Models\Restoration;
use App\Models\Binding;
use App\Models\Digitalization;
use App\Models\QualityControl;
use App\Models\Delivery;
use App\Models\User;
use Carbon\Carbon;

class SpecializedPhasesSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar un proceso de prueba (o crear uno si no existe)
        $process = ControlProcess::firstOrCreate(
            ['internal_code' => 'PROC-TEST-001'],
            [
                'book_title'   => 'Libro de prueba',
                'provider'     => 'Proveedor X',
                'order_number' => 'ORD-001',
                'act_number'   => 'ACT-001',
                'responsible_id' => \App\Models\User::role('Recepcionista')->first()?->id,
                'subtotal'     => 1000,
                'iva'          => 190,
                'total'        => 1190,
                'status'       => 'En Proceso',
                'phase_id'     => 1, // Fase Recepción
            ]
        );

        // Usuario admin como fallback
        $admin = User::where('email', 'admin@example.com')->first();

        // Restauración
        Restoration::firstOrCreate(
            ['control_process_id' => $process->id],
            [
                'damage_type'   => 'Humedad',
                'technique_used'=> 'Secado y limpieza',
                'materials'     => 'Papel japonés, adhesivo',
                'notes'         => 'Se repararon 5 páginas con manchas',
                'status'        => 'Finalizado',
                'user_id'       => $admin?->id,
                'completed_at'  => Carbon::now()->subDays(5),
            ]
        );

        // Encuadernación
        Binding::firstOrCreate(
            ['control_process_id' => $process->id],
            [
                'binding_type'  => 'Reempaste en tapa dura',
                'materials'     => 'Cartón, pegamento, tela',
                'notes'         => 'Cubierta reemplazada',
                'status'        => 'Finalizado',
                'user_id'       => $admin?->id,
                'completed_at'  => Carbon::now()->subDays(3),
            ]
        );

        // Digitalización
        Digitalization::firstOrCreate(
            ['control_process_id' => $process->id],
            [
                'file_path'     => 'digitalizados/libro-prueba.pdf',
                'format'        => 'PDF',
                'resolution'    => 300,
                'pages_count'   => 150,
                'notes'         => 'Archivo digital completo',
                'status'        => 'Finalizado',
                'user_id'       => $admin?->id,
                'completed_at'  => Carbon::now()->subDays(2),
            ]
        );

        // Control de calidad
        QualityControl::firstOrCreate(
            ['control_process_id' => $process->id],
            [
                'checklist'     => ['Portada intacta', 'Todas las páginas completas', 'Archivo legible'],
                'approved'      => true,
                'notes'         => 'Aprobado sin observaciones',
                'status'        => 'Finalizado',
                'user_id'       => $admin?->id,
                'completed_at'  => Carbon::now()->subDay(),
            ]
        );

        // Entrega final
        Delivery::firstOrCreate(
            ['control_process_id' => $process->id],
            [
                'delivered_to'  => 'Biblioteca Central',
                'delivery_date' => Carbon::today(),
                'notes'         => 'Libro entregado y archivado',
                'status'        => 'Finalizado',
                'user_id'       => $admin?->id,
                'completed_at'  => Carbon::now(),
            ]
        );
    }
}
