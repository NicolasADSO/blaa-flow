<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Binding extends Model
{
    use HasFactory;

    protected $fillable = [
        'control_process_id',  // referencia al proceso general
        'binding_type',        // tipo de empaste (rústica, tapa dura, cosido, etc.)
        'materials',           // materiales usados
        'cover_photo',         // foto del libro empastado
        'images',              // (opcional) varias fotos en formato JSON
        'notes',               // observaciones
        'status',              // Pendiente / En Proceso / Finalizado
        'user_id',             // quién lo realizó
        'completed_at',        // fecha de finalización
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'images'       => 'array', // si existe la columna en la DB
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
        static::creating(function ($binding) {
            if (! $binding->user_id) {
                $binding->user_id = Auth::id();
            }

            if (! $binding->status) {
                $binding->status = 'Pendiente';
            }
        });
    }

    /* 🔹 Accessor para que acta_final.blade pueda usar $binding->images */
    public function getImagesAttribute($value)
    {
        // Si ya existe columna "images" en DB → usarla
        if ($this->attributes['images'] ?? false) {
            return json_decode($this->attributes['images'], true);
        }

        // Si solo hay cover_photo, devolverlo como arreglo
        if ($this->cover_photo) {
            return [$this->cover_photo];
        }

        return [];
    }
}
