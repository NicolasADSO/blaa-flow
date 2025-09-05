<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlProcessPhasePlan extends Model
{
    protected $table = 'control_process_phase_plans';

    protected $fillable = [
        'control_process_id',
        'phase_id',
        'sort',
    ];

    protected $casts = [
        'sort' => 'integer',
    ];

    protected static function booted(): void
    {
        // Asignar sort incremental si no viene
        static::creating(function (self $item) {
            if (is_null($item->sort)) {
                $max = static::where('control_process_id', $item->control_process_id)->max('sort');
                $item->sort = is_null($max) ? 1 : ($max + 1);
            }
        });

        // Evitar duplicados del par (control_process_id, phase_id) a nivel de modelo
        static::saving(function (self $item) {
            $exists = static::where('control_process_id', $item->control_process_id)
                ->where('phase_id', $item->phase_id)
                ->when($item->exists, fn ($q) => $q->where('id', '!=', $item->id))
                ->exists();

            if ($exists) {
                throw new \RuntimeException('Esta fase ya está en el plan para este proceso.');
            }
        });
    }

    /* ================= RELACIONES ================= */

    public function process()
    {
        return $this->belongsTo(ControlProcess::class, 'control_process_id');
    }

    public function phase()
    {
        return $this->belongsTo(Phase::class, 'phase_id');
    }
}
