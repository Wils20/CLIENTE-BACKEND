<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;

// Ruta Principal (El Dashboard con la imagen de fondo)
Route::get('/', [ClienteController::class, 'index'])->name('inicio');

// Ruta del Partido en Vivo (El marcador grande)
Route::get('/partido/{id}', [ClienteController::class, 'show'])->name('partido.show');
