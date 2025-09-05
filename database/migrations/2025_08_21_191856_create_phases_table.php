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
        Schema::create('phases', function (Blueprint $table) {
            $table->id();
            
            // 🔹 Nombre de la fase (ejemplo: Digitalización, Empaste, Restauración, etc.)
            $table->string('name'); 
            
            // 🔹 Descripción opcional de la fase
            $table->text('description')->nullable();

            // 🔹 Orden de ejecución (para saber cuál fase sigue después)
            $table->unsignedInteger('order')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phases');
    }
};
