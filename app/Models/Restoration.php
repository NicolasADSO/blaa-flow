<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Restoration extends Model
{
    use HasFactory;

    protected $fillable = [
        'control_process_id',   // referencia al proceso general
        'damage_type',          // tipo de daño (ej: humedad, rotura, manchas)
        'technique_used',       // técnica de restauración usada
        'materials',            // materiales empleados
        'before_photo',         // foto antes de restaurar
        'after_photo',          // foto después de restaurar
        'notes',                // observaciones adicionales
        'status',               // Pendiente / En Proceso / Finalizado
        'user_id',              // quién realizó la acción
        'completed_at',         // cuándo finalizó
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /* 🔹 Relación con proceso general */
    public function controlProcess()
    {
        return $this->belongsTo(ControlProcess::class);
    }

    /* 🔹 Relación con usuario */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* 🔹 Boot para asignar usuario y estado inicial */
    protected static function booted()
    {
        static::creating(function ($restoration) {
            if (! $restoration->user_id) {
                $restoration->user_id = Auth::id();
            }

            if (! $restoration->status) {
                $restoration->status = 'Pendiente';
            }
        });
    }
}
