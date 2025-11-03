<?php

use App\Http\Controllers\Api\PropertyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Property API routes
Route::prefix('properties')->name('api.properties.')->group(function () {
    Route::get('/', [PropertyController::class, 'index'])->name('index');
    Route::post('/', [PropertyController::class, 'store'])->name('store');
    Route::get('/search', [PropertyController::class, 'search'])->name('search');
    Route::get('/stats', [PropertyController::class, 'stats'])->name('stats');
    Route::get('/{property}', [PropertyController::class, 'show'])->name('show');
    Route::put('/{property}', [PropertyController::class, 'update'])->name('update');
    Route::patch('/{property}', [PropertyController::class, 'update'])->name('update.partial');
    Route::delete('/{property}', [PropertyController::class, 'destroy'])->name('destroy');
});
