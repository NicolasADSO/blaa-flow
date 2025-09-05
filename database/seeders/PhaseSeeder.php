<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Phase;

class PhaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $phases = [
            [
                'name' => 'Recepción',
                'description' => 'Ingreso del libro o copia a la biblioteca.',
                'order' => 1,
                'input_type' => 'form', // Formulario de ingreso
            ],
            [
                'name' => 'Restauración',
                'description' => 'Reparación de libros dañados.',
                'order' => 2,
                'input_type' => 'form', // Formulario de registro
            ],
            [
                'name' => 'Empaste',
                'description' => 'Proceso físico de encuadernación o reempaste.',
                'order' => 3,
                'input_type' => 'file', // Subida de foto del libro empastado
            ],
            [
                'name' => 'Digitalización',
                'description' => 'Escaneo y conversión del libro a formato digital.',
                'order' => 4,
                'input_type' => 'file', // Subida de archivo escaneado
            ],
            [
                'name' => 'Control de Calidad',
                'description' => 'Revisión final y control de calidad.',
                'order' => 5,
                'input_type' => 'check', // Solo marcar aprobado
            ],
            [
                'name' => 'Disponibilización',
                'description' => 'El libro queda disponible para la biblioteca.',
                'order' => 6,
                'input_type' => 'check', // Solo marcar finalizado
            ],
            [
                'name' => 'Catalogación',
                'description' => 'Asignación de metadatos y clasificación.',
                'order' => 7,
                'input_type' => 'form', // Formulario con metadatos
            ],
        ];

        foreach ($phases as $phase) {
            Phase::updateOrCreate(
                ['name' => $phase['name']], // evita duplicados
                $phase
            );
        }
    }
}
