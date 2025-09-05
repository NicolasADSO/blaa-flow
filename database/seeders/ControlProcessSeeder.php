<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ControlProcess;
use App\Models\Phase;
use App\Models\ControlProcessPhaseLog;
use App\Models\User;
use Carbon\Carbon;

class ControlProcessSeeder extends Seeder
{
    public function run(): void
    {
        $recepcion = Phase::where('name', 'Recepción')->first();

        if (! $recepcion) {
            $this->command->warn('⚠️ No se encontró la fase Recepción. Ejecuta primero el PhaseSeeder.');
            return;
        }

        // Usuarios de ejemplo (ajusta según tus roles creados)
        $admin         = User::role('Admin')->first();
        $recepcionista = User::role('Recepcionista')->first();
        $restaurador   = User::role('Restaurador')->first();

        $processes = [
            // 📘 Proceso atrasado (delivery_date ya venció)
            [
                'book_title'     => 'El Quijote - Ejemplar Antiguo',
                'internal_code'  => 'LIB-001',
                'provider'       => 'Proveedor A',
                'order_number'   => 'ORD-001',
                'act_number'     => 'ACT-001',
                'subtotal'       => 100000,
                'iva'            => 19000,
                'total'          => 119000,
                'responsible_id' => $recepcionista?->id ?? $admin?->id,
                'status'         => 'En Proceso',
                'phase_id'       => $recepcion->id,
                'start_date'     => Carbon::now()->subDays(10),
                'end_date'       => null,
                'delivery_date'  => Carbon::now()->subDays(2), // vencido → atrasado
            ],

            // 📗 Proceso finalizado con duración
            [
                'book_title'     => 'Historia de Bogotá',
                'internal_code'  => 'LIB-002',
                'provider'       => 'Proveedor B',
                'order_number'   => 'ORD-002',
                'act_number'     => 'ACT-002',
                'subtotal'       => 200000,
                'iva'            => 38000,
                'total'          => 238000,
                'responsible_id' => $restaurador?->id ?? $admin?->id,
                'status'         => 'Finalizado',
                'phase_id'       => $recepcion->id,
                'start_date'     => Carbon::now()->subDays(15),
                'end_date'       => Carbon::now()->subDays(5), // duración de 10 días
                'delivery_date'  => Carbon::now()->addDays(3),
            ],

            // 📙 Proceso pendiente (para que aparezca en gráficos básicos)
            [
                'book_title'     => 'Enciclopedia del Arte',
                'internal_code'  => 'LIB-003',
                'provider'       => 'El Dorado',
                'order_number'   => 'ORD-003',
                'act_number'     => 'ACT-003',
                'subtotal'       => 500000,
                'iva'            => 95000,
                'total'          => 595000,
                'responsible_id' => $admin?->id,
                'status'         => 'Pendiente',
                'phase_id'       => $recepcion->id,
                'start_date'     => Carbon::now(),
                'end_date'       => null,
                'delivery_date'  => Carbon::now()->addDays(7),
            ],
        ];

        foreach ($processes as $processData) {
            $process = ControlProcess::firstOrCreate(
                ['internal_code' => $processData['internal_code']],
                $processData
            );

            // Crear log inicial
            ControlProcessPhaseLog::firstOrCreate(
                [
                    'control_process_id' => $process->id,
                    'phase_id'           => $recepcion->id,
                ],
                [
                    'user_id'      => $admin?->id ?? 1,
                    'status'       => $process->status,
                    'action'       => 'Creación', // 👈 acción corta
                    'observations' => 'Proceso creado en fase inicial', // 👈 detalle
                ]
            );
        }
    }
}
