<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class QualityControl extends Model
{
    use HasFactory;

    protected $fillable = [
        'control_process_id',   // referencia al proceso principal
        'checklist',            // lista de validaciones (JSON)
        'approved',             // aprobado / rechazado
        'notes',                // observaciones
        'status',               // Pendiente / En Proceso / Finalizado
        'user_id',              // usuario que hizo el control
        'completed_at',         // fecha de finalización
    ];

    protected $casts = [
        'checklist'    => 'array',
        'approved'     => 'boolean',
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
        static::creating(function ($qc) {
            if (! $qc->user_id) {
                $qc->user_id = Auth::id();
            }

            if (! $qc->status) {
                $qc->status = 'Pendiente';
            }
        });
    }
}
