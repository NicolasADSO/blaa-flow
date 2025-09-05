<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phases', function (Blueprint $table) {
            // Tipo de input que requiere esta fase: formulario, archivo o solo check
            $table->enum('input_type', ['form', 'file', 'check'])
                  ->default('check')
                  ->after('order'); // lo ubicamos después de "order"
        });
    }

    public function down(): void
    {
        Schema::table('phases', function (Blueprint $table) {
            $table->dropColumn('input_type');
        });
    }
};
