<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restorations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('control_process_id')
                ->constrained('control_processes')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('damage_type')->nullable();
            $table->string('technique_used')->nullable();
            $table->string('materials')->nullable();
            $table->string('before_photo')->nullable();
            $table->string('after_photo')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['Pendiente', 'En Proceso', 'Finalizado'])
                  ->default('Pendiente');

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restorations');
    }
};
