<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ControlProcess;
use App\Models\ControlProcessPhaseLog;
use App\Models\Phase;
use App\Models\User;

class ControlProcessPhaseLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuarios de prueba
        $admin = User::where('email', 'admin@example.com')->first();
        $recepcionista = User::where('email', 'recepcion@example.com')->first();
        $restaurador = User::where('email', 'restaurador@example.com')->first();

        // Buscar fases
        $recepcion = Phase::where('name', 'Recepción')->first();
        $revision  = Phase::where('name', 'Revisión')->first();
        $digitalizacion = Phase::where('name', 'Digitalización')->first();

        // Buscar procesos ya creados
        $process1 = ControlProcess::where('internal_code', 'LIB-001')->first();
        $process2 = ControlProcess::where('internal_code', 'LIB-002')->first();

        if ($process1 && $recepcion && $revision) {
            ControlProcessPhaseLog::firstOrCreate(
                [
                    'control_process_id' => $process1->id,
                    'phase_id'           => $recepcion->id,
                ],
                [
                    'user_id'      => $recepcionista?->id ?? $admin?->id,
                    'status'       => 'Finalizado',
                    'observations' => 'Libro recibido en buen estado.',
                    'created_at'   => now()->subDays(3),
                    'updated_at'   => now()->subDays(2),
                ]
            );

            ControlProcessPhaseLog::firstOrCreate(
                [
                    'control_process_id' => $process1->id,
                    'phase_id'           => $revision->id,
                ],
                [
                    'user_id'      => $restaurador?->id ?? $admin?->id,
                    'status'       => 'En Proceso',
                    'observations' => 'En restauración inicial.',
                    'created_at'   => now()->subDays(2),
                    'updated_at'   => now(),
                ]
            );
        }

        if ($process2 && $recepcion && $digitalizacion) {
            ControlProcessPhaseLog::firstOrCreate(
                [
                    'control_process_id' => $process2->id,
                    'phase_id'           => $recepcion->id,
                ],
                [
                    'user_id'      => $recepcionista?->id ?? $admin?->id,
                    'status'       => 'Finalizado',
                    'observations' => 'Ingresado y validado.',
                    'created_at'   => now()->subDays(5),
                    'updated_at'   => now()->subDays(4),
                ]
            );

            ControlProcessPhaseLog::firstOrCreate(
                [
                    'control_process_id' => $process2->id,
                    'phase_id'           => $digitalizacion->id,
                ],
                [
                    'user_id'      => $admin?->id,
                    'status'       => 'Pendiente',
                    'observations' => 'Esperando escaneo.',
                    'created_at'   => now()->subDays(4),
                    'updated_at'   => now(),
                ]
            );
        }
    }
}
