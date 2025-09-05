<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Digitalization extends Model
{
    use HasFactory;

    protected $fillable = [
        'control_process_id',   // referencia al proceso general
        'file_path',            // archivo escaneado
        'format',               // formato: PDF, JPG, TIFF
        'resolution',           // resolución en DPI
        'pages_count',          // cantidad de páginas escaneadas
        'notes',                // observaciones adicionales
        'status',               // Pendiente / En Proceso / Finalizado
        'user_id',              // quién realizó la digitalización
        'completed_at',         // fecha de finalización
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /* 🔹 Relación con el proceso general */
    public function controlProcess()
    {
        return $this->belongsTo(ControlProcess::class);
    }

    /* 🔹 Relación con el usuario */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* 🔹 Boot para asignar usuario y estado inicial */
    protected static function booted()
    {
        static::creating(function ($digitalization) {
            if (! $digitalization->user_id) {
                $digitalization->user_id = Auth::id();
            }

            if (! $digitalization->status) {
                $digitalization->status = 'Pendiente';
            }
        });
    }
}
