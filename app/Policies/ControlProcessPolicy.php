<?php

namespace App\Policies;

use App\Models\ControlProcess;
use App\Models\User;

class ControlProcessPolicy
{
    /**
     * Admin puede todo
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
    }

    /**
     * Ver listado
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver procesos');
    }

    /**
     * Ver proceso específico
     */
    public function view(User $user, ControlProcess $process): bool
    {
        // Si el proceso no tiene fase aún, solo Admin puede verlo
        if (!$process->phase) {
            return $user->hasRole('Admin');
        }

        // ✅ Si tiene permiso "ver procesos", puede verlo
        return $user->can('ver procesos');
    }

    /**
     * Crear proceso
     */
    public function create(User $user): bool
    {
        return $user->can('crear procesos');
    }

    /**
     * Editar proceso
     */
    public function update(User $user, ControlProcess $process): bool
    {
        return $user->can('editar procesos');
    }

    /**
     * Eliminar proceso
     */
    public function delete(User $user, ControlProcess $process): bool
    {
        return $user->can('eliminar procesos');
    }
}
