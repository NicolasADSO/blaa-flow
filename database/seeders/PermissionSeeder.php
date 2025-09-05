<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Definir todos los permisos
        $permissions = [
            'ver procesos',
            'crear procesos',
            'editar procesos',
            'eliminar procesos',
            'avanzar procesos',
        ];

        // Crear permisos si no existen
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Asignar permisos a roles
        Role::where('name', 'Admin')->first()?->syncPermissions(Permission::all());

        Role::where('name', 'Recepcionista')->first()?->syncPermissions([
            'ver procesos',
            'crear procesos',
            'avanzar procesos',
        ]);

        Role::where('name', 'Restaurador')->first()?->syncPermissions([
            'ver procesos',
            'editar procesos',
            'avanzar procesos',
        ]);

        Role::where('name', 'Encuadernador')->first()?->syncPermissions([
            'ver procesos',
            'editar procesos',
            'avanzar procesos',
        ]);

        Role::where('name', 'Digitalizador')->first()?->syncPermissions([
            'ver procesos',
            'editar procesos',
            'avanzar procesos',
        ]);

        Role::where('name', 'Calidad')->first()?->syncPermissions([
            'ver procesos',
            'editar procesos',
            'avanzar procesos',
        ]);
    }
}
