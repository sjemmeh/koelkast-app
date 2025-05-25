<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersoonController;
use App\Http\Controllers\KioskController; 
use App\Http\Controllers\DrankController;
use App\Http\Controllers\TransactieController;

// Welkom pagina (niet in gebruik)
Route::get('/', function () {
    return view('welcome');
});


// Kiosk Route

// Hoofdpagina
Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk.index');
Route::post('/kiosk/process-barcode', [KioskController::class, 'processBarcode'])->name('kiosk.processBarcode');
Route::post('/kiosk/finalize-transaction', [KioskController::class, 'finalizeTransaction'])->name('kiosk.finalizeTransaction');



// Beheer sectie
Route::prefix('beheer')->name('beheer.')->group(function () {
    // Personen Beheer
    Route::get('/personen', [PersoonController::class, 'indexBeheer'])->name('personen.index');
    Route::post('/personen', [PersoonController::class, 'storeBeheer'])->name('personen.store');
    Route::delete('/personen/{persoon}', [PersoonController::class, 'destroyBeheer'])->name('personen.destroy');
    Route::patch('/personen/{persoon}/toggle-actief', [PersoonController::class, 'toggleActief'])->name('personen.toggleActief');

    // Dranken Beheer
    Route::get('/dranken', [DrankController::class, 'indexBeheer'])->name('dranken.index');
    Route::post('/dranken', [DrankController::class, 'storeBeheer'])->name('dranken.store');
    Route::delete('/dranken/{drank}', [DrankController::class, 'destroyBeheer'])->name('dranken.destroy');

    //Transacties Beheer
     Route::get('/transacties', [TransactieController::class, 'indexBeheer'])->name('transacties.index');
});