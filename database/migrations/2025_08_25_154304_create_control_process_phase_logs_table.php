<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('control_process_phase_logs', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('control_process_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('phase_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Campos principales
            $table->string('action')->nullable(); // 🔹 Nueva columna para registrar la acción
            $table->enum('status', ['Pendiente', 'En Proceso', 'Finalizado'])
                ->default('Pendiente');
            $table->text('observations')->nullable();

            // Timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_process_phase_logs');
    }
};
