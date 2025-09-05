<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\Phase;

class AddCatalogacionSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Rol "Catalogador"
        Role::firstOrCreate([
            'name' => 'Catalogador',
            'guard_name' => 'web',
        ]);

        // 2) Fase "Catalogación" (si no existe, se crea al final del orden)
        Phase::firstOrCreate(
            ['name' => 'Catalogación'],
            ['order' => (int) Phase::max('order') + 1]
        );
    }
}
