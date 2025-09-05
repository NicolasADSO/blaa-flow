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
        Schema::create('control_processes', function (Blueprint $table) {
            $table->id();
            
            // 🔹 Campos principales basados en el Excel / flujo
            $table->string('provider')->nullable();             // Proveedor
            $table->string('order_number')->nullable()->index(); // N° pedido
            $table->string('act_number')->nullable()->index();   // N° acta

            // 🔹 Valores
            $table->decimal('subtotal', 12, 2)->nullable(); // Valor del material
            $table->decimal('iva', 12, 2)->nullable();      // IVA
            $table->decimal('total', 12, 2)->nullable();    // Valor total

            // 🔹 Fechas del flujo
            $table->date('delivery_date')->nullable();   // Fecha de entrega
            $table->date('invoice_date')->nullable();    // Fecha factura
            $table->date('payment_date')->nullable();    // Fecha de pago

            // 🔹 Campos de control de proceso
            $table->string('responsible')->nullable();   // Responsable del proceso
            $table->dateTime('start_date')->nullable();  // Fecha inicio proceso
            $table->dateTime('end_date')->nullable();    // Fecha fin proceso
            $table->integer('real_duration')->nullable(); // Duración real (días)

            // 🔹 Relación con fases
            $table->foreignId('phase_id')
                ->nullable()
                ->constrained('phases')
                ->nullOnDelete(); // Si se borra la fase, el campo se pone en NULL

            // 🔹 Estado / notas
            $table->enum('status', ['Pendiente', 'En Proceso', 'Pagado', 'Finalizado'])->default('Pendiente');
            $table->text('observations')->nullable();    // Comentarios u observaciones

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_processes');
    }
};
