<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ControlProcess extends Model
{
    use HasFactory;

    protected $table = 'control_processes';

    protected $fillable = [
        'provider',
        'order_number',
        'act_number',
        'subtotal',
        'iva',
        'total',
        'delivery_date',
        'invoice_date',
        'payment_date',
        'responsible_id',   // Se sincroniza con quien envía a la fase
        'start_date',
        'end_date',
        'real_duration',
        'status',
        'observations',
        'phase_id',
        'book_title',
        'internal_code',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'invoice_date'  => 'date',
        'payment_date'  => 'date',
        'start_date'    => 'datetime',
        'end_date'      => 'datetime',
        'subtotal'      => 'decimal:2',
        'iva'           => 'decimal:2',
        'total'         => 'decimal:2',
    ];

    /**
     * Claves de caché usadas por widgets/gráficas.
     */
    protected static array $cacheKeys = [
        'widget:process_stats:v1',
        'chart:cp_by_responsible:v1',
        'chart:by_provider:v1',
        'chart:by_month:v1',
        'chart:by_phase:v1',
        'chart:by_status:v1',
        'chart:avg_duration_by_phase:v1',
        'chart:overdue_by_provider:v1',
    ];

    /* ================= RELACIONES ================= */

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function phase()
    {
        return $this->belongsTo(Phase::class);
    }

    public function phaseLogs()
    {
        return $this->hasMany(ControlProcessPhaseLog::class);
    }

    public function restorations()
    {
        return $this->hasMany(Restoration::class);
    }

    public function bindings()
    {
        return $this->hasMany(Binding::class);
    }

    public function digitalizations()
    {
        return $this->hasMany(Digitalization::class);
    }

    public function catalogings()
    {
        return $this->hasMany(Cataloging::class);
    }

    public function qualityControls()
    {
        return $this->hasMany(QualityControl::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    /** Plan (orden) de fases elegido para ESTE proceso (usa columna `sort`) */
    public function phasePlan()
    {
        return $this->hasMany(ControlProcessPhasePlan::class, 'control_process_id')
            ->orderBy('sort');
    }

    /** Fases planificadas (helper belongsToMany) */
    public function plannedPhases()
    {
        return $this->belongsToMany(Phase::class, 'control_process_phase_plans', 'control_process_id', 'phase_id')
            ->withPivot('sort')
            ->orderBy('pivot_sort');
    }

    /**
     * Usuario responsable “real” de la fase actual (último log de llegada).
     */
    public function currentResponsibleUser(): ?\App\Models\User
    {
        return $this->phaseLogs()
            ->where('phase_id', $this->phase_id)
            ->latest()
            ->first()
            ?->user ?? null;
    }

    /* ================= EVENTOS (boot) ================= */

    protected static function booted()
    {
        // Invalidar todos los caches relevantes cuando cambia el modelo
        $bust = fn () => static::bustAllDashboardsCache();

        static::saved($bust);
        static::deleted($bust);
        // ⚠️ Importante: NO usamos SoftDeletes, por eso NO registramos ::restored()

        static::creating(function (self $controlProcess) {
            // Fase inicial por defecto
            if (! $controlProcess->phase_id) {
                $firstPhase = Phase::orderBy('order')->first();
                if ($firstPhase) {
                    $controlProcess->phase_id = $firstPhase->id;
                }
            }

            // Fecha de inicio
            if (! $controlProcess->start_date) {
                $controlProcess->start_date = now();
            }

            // Estado inicial
            if (! $controlProcess->status) {
                $controlProcess->status = 'Pendiente';
            }

            // Responsable inicial: quien crea
            if (! $controlProcess->responsible_id) {
                $controlProcess->responsible_id = Auth::id();
            }
        });

        static::created(function (self $controlProcess) {
            // Log de llegada a la fase inicial
            $controlProcess->phaseLogs()->create([
                'phase_id' => $controlProcess->phase_id,
                'user_id'  => Auth::id(),
                'action'   => 'Proceso creado en fase inicial',
            ]);
        });
    }

    /** Borra todas las claves de caché de dashboard/widgets */
    public static function bustAllDashboardsCache(): void
    {
        foreach (static::$cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /* ================= LÓGICA ================= */

    /**
     * Determina la siguiente fase según plan (si existe) o el orden global.
     */
    protected function nextPhasePlannedOrGlobal(): ?Phase
    {
        if ($this->phasePlan()->exists()) {
            $currentItem = $this->phasePlan()->where('phase_id', $this->phase_id)->first();

            if ($currentItem) {
                $nextItem = $this->phasePlan()
                    ->where('sort', '>', $currentItem->sort)
                    ->orderBy('sort')
                    ->first();
            } else {
                // La fase actual no está en el plan → usar la primera del plan
                $nextItem = $this->phasePlan()->orderBy('sort')->first();
            }

            return $nextItem?->phase ?? null;
        }

        if (! $this->phase) {
            return null;
        }

        return Phase::where('order', '>', $this->phase->order)
            ->orderBy('order', 'asc')
            ->first();
    }

    /**
     * Avanza a la siguiente fase del flujo.
     */
    public function avanzarAFaseSiguiente(?int $senderId = null): bool
    {
        $senderId = $senderId ?? Auth::id();
        $nextPhase = $this->nextPhasePlannedOrGlobal();

        if ($nextPhase) {
            $this->update([
                'phase_id'       => $nextPhase->id,
                'status'         => 'En Proceso',
                'responsible_id' => $senderId,
            ]);

            $this->phaseLogs()->create([
                'phase_id' => $nextPhase->id,
                'user_id'  => $senderId,
                'action'   => 'Asignado / Avanzó a la fase ' . $nextPhase->name,
            ]);

            static::bustAllDashboardsCache();

            return true;
        }

        // No hay más fases → finalizado
        $this->update([
            'status'         => 'Finalizado',
            'responsible_id' => $senderId,
        ]);

        $this->phaseLogs()->create([
            'phase_id' => $this->phase_id,
            'user_id'  => $senderId,
            'action'   => 'Proceso finalizado',
        ]);

        static::bustAllDashboardsCache();

        return false;
    }

    /** ¿El proceso está finalizado? */
    public function isFinalizado(): bool
    {
        return $this->status === 'Finalizado';
    }
}
