<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();

            // Relación con proceso general
            $table->foreignId('control_process_id')
                ->constrained('control_processes')
                ->cascadeOnDelete();

            // Relación con usuario
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Campos de entrega final
            $table->string('delivered_to')->nullable();
            $table->date('delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['Pendiente', 'En Proceso', 'Finalizado'])
                  ->default('Pendiente');

            // Fecha de finalización
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
