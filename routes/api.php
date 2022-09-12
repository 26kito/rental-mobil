<?php

use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\RentController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

Route::prefix('v1')->group(function () {
  Route::prefix('user')->group(function () {
    // Btw ini perlu g yah ?
    Route::get('/', [UserController::class, 'index']);

    // Biar g n+1
    Route::get('/profile/{name}', [UserController::class, 'profile']);

    // routes user convensional pada umum nya 
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);

    // Mungkin butuh klo ada admin nya
    // Route::get('/{id}', [UserController::class, 'show']);

    // Kalo pake id nanti gampang ke tebak, bisa aja gua iseng gitu kan awowkwk
    Route::put('/edit/{user:email}', [UserController::class, 'update']);
    Route::delete('/delete/{user:email}', [UserController::class, 'destroy']);
  });

  Route::prefix('car')->group(function () {
    Route::get('/', [CarController::class, 'index']);
    Route::post('/create', [CarController::class, 'store'])->middleware('auth:api');
    Route::get('/{id}', [CarController::class, 'show']);
    Route::put('/{id}', [CarController::class, 'update'])->middleware('auth:api');
    Route::delete('/{id}', [CarController::class, 'destroy'])->middleware('auth:api');
  });

  Route::prefix('rent')->group(function () {
    Route::post('/{car_id}', [RentController::class, 'store'])->middleware('auth:api');
  });
});
