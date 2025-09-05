<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('catalogings', function (Blueprint $table) {
            // Bibliografía
            $table->string('title')->nullable()->after('status');
            $table->string('subtitle')->nullable()->after('title');
            $table->json('authors')->nullable()->after('subtitle');
            $table->string('publisher')->nullable()->after('authors');
            $table->string('place_of_publication')->nullable()->after('publisher');
            $table->string('edition')->nullable()->after('place_of_publication');
            $table->smallInteger('publication_year')->nullable()->after('edition');
            $table->string('isbn', 32)->nullable()->after('publication_year');
            $table->string('language', 8)->nullable()->after('isbn');

            // Clasificación
            $table->enum('classification_system', ['DDC','LCC','UDC','LOCAL'])->nullable()->after('language');
            $table->string('classification_code')->nullable()->after('classification_system'); // ej: 813.54
            $table->string('call_number')->nullable()->after('classification_code');          // signatura topográfica
            $table->string('shelf_location')->nullable()->after('call_number');               // ubicación en estantería

            // Materias / descriptores
            $table->json('subjects')->nullable()->after('shelf_location'); // encabezamientos
            $table->text('descriptors')->nullable()->after('subjects');

            // Descripción física
            $table->integer('pages')->nullable()->after('descriptors');
            $table->string('dimensions')->nullable()->after('pages'); // ej: "23 cm"
            $table->enum('material_type', ['Libro','Revista','Manuscrito','Mapa','Foto','Otro'])->nullable()->after('dimensions');

            // Control / adjuntos
            $table->string('barcode')->nullable()->after('material_type');
            $table->string('cover_image_path')->nullable()->after('barcode'); // ruta portada
            $table->enum('quality_status', ['Pendiente','Revisar','Aprobado'])->default('Pendiente')->after('cover_image_path');
            $table->timestamp('record_completed_at')->nullable()->after('quality_status');
        });
    }

    public function down(): void
    {
        Schema::table('catalogings', function (Blueprint $table) {
            $table->dropColumn([
                'title','subtitle','authors','publisher','place_of_publication','edition','publication_year','isbn','language',
                'classification_system','classification_code','call_number','shelf_location',
                'subjects','descriptors',
                'pages','dimensions','material_type',
                'barcode','cover_image_path','quality_status','record_completed_at',
            ]);
        });
    }
};
