<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digitalizations', function (Blueprint $table) {
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

            // Campos propios de digitalización
            $table->string('file_path')->nullable();                 // archivo escaneado
            $table->enum('format', ['PDF', 'JPG', 'TIFF'])->nullable();
            $table->integer('resolution')->nullable();               // dpi
            $table->integer('pages_count')->nullable();              // cantidad de páginas
            $table->text('notes')->nullable();                       // observaciones
            $table->enum('status', ['Pendiente', 'En Proceso', 'Finalizado'])
                  ->default('Pendiente');

            // Fecha de finalización
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digitalizations');
    }
};
