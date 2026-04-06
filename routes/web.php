<?php

use App\Http\Controllers\MesaController;
use App\Http\Controllers\ReservaController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    
    Route::resource('mesas', MesaController::class);
    Route::get('reservas/disponibilidad', [ReservaController::class, 'disponibilidad'])->name('reservas.disponibilidad');
    Route::resource('reservas', ReservaController::class);
});

require __DIR__.'/settings.php';
