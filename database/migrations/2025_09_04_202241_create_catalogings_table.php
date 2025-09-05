<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('catalogings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('control_process_id')
                ->constrained('control_processes')
                ->cascadeOnDelete();

            $table->foreignId('user_id')                 // responsable catalogador
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('status')->default('Pendiente');  // Pendiente | En Proceso | Finalizado
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogings');
    }
};
