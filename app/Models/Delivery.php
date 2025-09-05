<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'control_process_id',   // proceso asociado
        'delivered_to',         // persona o área que recibe
        'delivery_date',        // fecha de entrega
        'notes',                // observaciones
        'status',               // Pendiente / En Proceso / Finalizado
        'user_id',              // usuario que hizo la entrega
        'completed_at',         // fecha de finalización
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'completed_at'  => 'datetime',
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
        static::creating(function ($delivery) {
            if (! $delivery->user_id) {
                $delivery->user_id = Auth::id();
            }

            if (! $delivery->status) {
                $delivery->status = 'Pendiente';
            }
        });
    }
}
