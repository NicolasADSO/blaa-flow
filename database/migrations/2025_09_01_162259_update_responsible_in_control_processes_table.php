<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('control_processes', function (Blueprint $table) {
            // 🔹 Eliminar columna antigua (si existe)
            if (Schema::hasColumn('control_processes', 'responsible')) {
                $table->dropColumn('responsible');
            }

            // 🔹 Agregar nueva relación con users
            $table->foreignId('responsible_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('control_processes', function (Blueprint $table) {
            // Revertir a columna texto
            if (Schema::hasColumn('control_processes', 'responsible_id')) {
                $table->dropForeign(['responsible_id']);
                $table->dropColumn('responsible_id');
            }

            $table->string('responsible')->nullable();
        });
    }
};
