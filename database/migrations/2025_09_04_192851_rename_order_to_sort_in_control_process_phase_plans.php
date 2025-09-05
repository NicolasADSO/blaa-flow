<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // MySQL: renombrar y tipar en una sola sentencia
        DB::statement('ALTER TABLE control_process_phase_plans CHANGE `order` `sort` SMALLINT UNSIGNED NOT NULL');
        // (opcional) índice
        DB::statement('CREATE INDEX control_process_phase_plans_sort_index ON control_process_phase_plans (`sort`)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE control_process_phase_plans CHANGE `sort` `order` SMALLINT UNSIGNED NOT NULL');
        DB::statement('DROP INDEX control_process_phase_plans_sort_index ON control_process_phase_plans');
    }
};
