<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // control_processes
        Schema::table('control_processes', function (Blueprint $table) {
            // Filtros/listados más comunes
            $table->index(['status', 'phase_id', 'created_at'], 'cp_status_phase_created_idx');
            $table->index(['phase_id', 'created_at'], 'cp_phase_created_idx');
            $table->index('responsible_id', 'cp_responsible_idx');
            $table->index('book_title', 'cp_book_title_idx');
            $table->index('provider', 'cp_provider_idx');
        });

        // control_process_phase_logs (logs que usas para currentResponsibleUser y trazabilidad)
        Schema::table('control_process_phase_logs', function (Blueprint $table) {
            // Búsqueda del "último" por proceso+fase => created_at DESC
            $table->index(['control_process_id', 'phase_id', 'created_at'], 'cppl_proc_phase_created_idx');
            $table->index('user_id', 'cppl_user_idx');
        });

        // control_process_phase_plans (plan de fases por proceso)
        Schema::table('control_process_phase_plans', function (Blueprint $table) {
            // Orden por sort dentro del proceso y consultas por fase
            $table->index(['control_process_id', 'sort'], 'cppp_proc_sort_idx');
            $table->index('phase_id', 'cppp_phase_idx');
            // Evita duplicados de fase dentro del mismo proceso (opcional pero recomendado)
            $table->unique(['control_process_id', 'phase_id'], 'cppp_unique_proc_phase');
        });

        // phases (las consultas por nombre/orden son frecuentes)
        Schema::table('phases', function (Blueprint $table) {
            $table->index('order', 'phases_order_idx');
            $table->index('name', 'phases_name_idx'); // si ya es UNIQUE, puedes borrar esta línea
        });

        // Fases especializadas (tablas hijas): restorations, bindings, digitalizations, quality_controls, deliveries
        // Todas comparten columnas similares: control_process_id, user_id, status
        $children = [
            'restorations',
            'bindings',
            'digitalizations',
            'quality_controls',
            'deliveries',
            'catalogings', // tu nueva fase
        ];

        foreach ($children as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->index('control_process_id', $tableName . '_proc_idx');
                    $table->index('user_id', $tableName . '_user_idx');
                    $table->index('status', $tableName . '_status_idx');
                });
            }
        }
    }

    public function down(): void
    {
        // control_processes
        Schema::table('control_processes', function (Blueprint $table) {
            $table->dropIndex('cp_status_phase_created_idx');
            $table->dropIndex('cp_phase_created_idx');
            $table->dropIndex('cp_responsible_idx');
            $table->dropIndex('cp_book_title_idx');
            $table->dropIndex('cp_provider_idx');
        });

        // control_process_phase_logs
        Schema::table('control_process_phase_logs', function (Blueprint $table) {
            $table->dropIndex('cppl_proc_phase_created_idx');
            $table->dropIndex('cppl_user_idx');
        });

        // control_process_phase_plans
        Schema::table('control_process_phase_plans', function (Blueprint $table) {
            $table->dropIndex('cppp_proc_sort_idx');
            $table->dropIndex('cppp_phase_idx');
            $table->dropUnique('cppp_unique_proc_phase');
        });

        // phases
        Schema::table('phases', function (Blueprint $table) {
            $table->dropIndex('phases_order_idx');
            $table->dropIndex('phases_name_idx');
        });

        // hijas
        $children = [
            'restorations',
            'bindings',
            'digitalizations',
            'quality_controls',
            'deliveries',
            'catalogings',
        ];

        foreach ($children as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropIndex($tableName . '_proc_idx');
                    $table->dropIndex($tableName . '_user_idx');
                    $table->dropIndex($tableName . '_status_idx');
                });
            }
        }
    }
};
