<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles básicos + especializados
        $roles = [
            'Admin',
            'Recepcionista',
            'Restaurador',
            'Encuadernador',
            'Digitalizador',
            'Calidad',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Crear usuario admin y darle TODOS los roles
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Administrador', 'password' => Hash::make('password')]
        );
        $admin->syncRoles(Role::all()); // 👈 le asignamos todos los roles

        // Crear usuarios de prueba con rol específico
        $recepcionista = User::firstOrCreate(
            ['email' => 'recepcion@example.com'],
            ['name' => 'Recepcionista', 'password' => Hash::make('password')]
        );
        $recepcionista->assignRole('Recepcionista');

        $restaurador = User::firstOrCreate(
            ['email' => 'restaurador@example.com'],
            ['name' => 'Restaurador', 'password' => Hash::make('password')]
        );
        $restaurador->assignRole('Restaurador');

        $encuadernador = User::firstOrCreate(
            ['email' => 'encuadernador@example.com'],
            ['name' => 'Encuadernador', 'password' => Hash::make('password')]
        );
        $encuadernador->assignRole('Encuadernador');

        $digitalizador = User::firstOrCreate(
            ['email' => 'digitalizador@example.com'],
            ['name' => 'Digitalizador', 'password' => Hash::make('password')]
        );
        $digitalizador->assignRole('Digitalizador');

        $calidad = User::firstOrCreate(
            ['email' => 'calidad@example.com'],
            ['name' => 'Control de Calidad', 'password' => Hash::make('password')]
        );
        $calidad->assignRole('Calidad');
    }
}
