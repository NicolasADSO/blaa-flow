<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlProcessPhaseLog extends Model
{
    use HasFactory;

    protected $table = 'control_process_phase_logs';

    protected $fillable = [
        'control_process_id',
        'phase_id',
        'user_id',
        'action',        // 🔹 Nuevo campo agregado
        'status',
        'observations',
    ];

    /* ================= RELACIONES ================= */

    public function controlProcess()
    {
        return $this->belongsTo(ControlProcess::class);
    }

    public function phase()
    {
        return $this->belongsTo(Phase::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
