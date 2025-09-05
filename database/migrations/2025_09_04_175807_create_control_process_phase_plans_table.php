<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('control_process_phase_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('control_process_id')
                ->constrained('control_processes')
                ->cascadeOnDelete();

            $table->foreignId('phase_id')
                ->constrained('phases')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('order')->index();

            $table->timestamps();

            $table->unique(['control_process_id', 'phase_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_process_phase_plans');
    }
};
