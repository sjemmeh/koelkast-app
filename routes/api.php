<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategorieController; 
use App\Http\Controllers\Api\DrankController;
use App\Http\Controllers\Api\PersoonController;   


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Categorieën API Endpoints
Route::get('/categorieen', [CategorieController::class, 'index']);
Route::post('/categorieen', [CategorieController::class, 'store']);
Route::delete('/categorieen/{categorie}', [CategorieController::class, 'destroy']);

// Dranken API Endpoints (voor bewerken)
Route::get('/dranken/{drank}', [DrankController::class, 'show']);      // Haal één drankje op
Route::put('/dranken/{drank}', [DrankController::class, 'update']);    // Werk een drankje bij


// API voor transacties
Route::get('/personen/{persoon}/transacties', [PersoonController::class, 'getTransactiesVoorPeriode']);
