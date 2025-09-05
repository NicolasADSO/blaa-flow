<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bindings', function (Blueprint $table) {
            $table->id();
            
            // Relación con control_processes
            $table->foreignId('control_process_id')
                ->constrained('control_processes')
                ->onDelete('cascade');

            // Relación con usuarios
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Campos propios de empaste
            $table->string('binding_type')->nullable();     // tipo de empaste
            $table->string('materials')->nullable();        // materiales usados
            $table->string('cover_photo')->nullable();      // foto portada
            $table->text('notes')->nullable();              // observaciones
            $table->enum('status', ['Pendiente', 'En Proceso', 'Finalizado'])
                ->default('Pendiente');
            
            // Fechas
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bindings');
    }
};
