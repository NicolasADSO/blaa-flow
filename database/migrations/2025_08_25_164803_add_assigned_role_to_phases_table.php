<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phases', function (Blueprint $table) {
            // 🔹 usuario asignado a la fase (opcional)
            $table->foreignId('assigned_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // 🔹 en caso de que después quieras manejar roles
            // $table->string('assigned_role')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('phases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_user_id');
            // $table->dropColumn('assigned_role');
        });
    }
};
