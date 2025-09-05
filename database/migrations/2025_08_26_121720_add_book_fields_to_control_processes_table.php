<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('control_processes', function (Blueprint $table) {
            $table->string('book_title')->default('SIN_TITULO')->after('id');
            $table->string('internal_code')->unique()->default('PENDIENTE')->after('book_title');
            $table->date('reception_date')->default(now())->after('internal_code');
        });
    }


    public function down(): void
    {
        Schema::table('control_processes', function (Blueprint $table) {
            $table->dropColumn(['book_title', 'internal_code', 'reception_date']);
        });
    }
};
