<?php

use App\Http\Controllers\ProcessExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome'); // 👈 esto muestra resources/views/welcome.blade.php
});

Route::prefix('process/export')->name('process.export.')->group(function () {
    Route::get('/{process}/excel', [ProcessExportController::class, 'excel'])->name('excel');
    Route::get('/{process}/pdf', [ProcessExportController::class, 'pdf'])->name('pdf');
    Route::get('/{process}/acta', [ProcessExportController::class, 'acta'])->name('acta'); // ✅ corregido
});
