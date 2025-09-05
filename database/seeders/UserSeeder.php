<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 📌 Lista de roles que usa tu sistema
        $roles = [
            'Administrador',
            'Recepcionista',
            'Técnico Restaurador',
            'Restaurador',
            'Encuadernador',
            'Digitalizador',
            'Control de Calidad',
            'Entrega',
        ];

        // Crear roles si no existen
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Crear permisos básicos (opcional, puedes ampliarlo después)
        $permissions = [
            'ver procesos',
            'crear procesos',
            'editar procesos',
            'eliminar procesos',
            'gestionar usuarios',
            'gestionar fases',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // 📌 Crear usuario Administrador si no existe
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador General',
                'password' => Hash::make('password'), // cámbialo luego
            ]
        );

        $admin->assignRole('Administrador');

        // 📌 Crear usuario Recepcionista de ejemplo
        $recep = User::firstOrCreate(
            ['email' => 'recepcionista@example.com'],
            [
                'name' => 'Recepcionista Principal',
                'password' => Hash::make('password'),
            ]
        );

        $recep->assignRole('Recepcionista');
    }
}
