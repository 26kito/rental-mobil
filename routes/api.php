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

Route::prefix('v1')->group(function() {
  Route::prefix('user')->group(function() {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/profile/{name}', [UserController::class, 'profile']);
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::put('/edit/{user:email}', [UserController::class, 'update']);
    Route::delete('/delete/{user:email}', [UserController::class, 'destroy']);
  });

  Route::prefix('car')->group(function() {
    Route::get('/', [CarController::class, 'index']);
    Route::post('/create', [CarController::class, 'store'])->middleware('auth:api');
    Route::get('/{car_id}', [CarController::class, 'show']);
    Route::put('/{car_id}', [CarController::class, 'update'])->middleware('auth:api');
    Route::delete('/{car_id}', [CarController::class, 'destroy'])->middleware('auth:api');
  });

  Route::prefix('rent')->group(function() {
    Route::post('/{car_id}', [RentController::class, 'store'])->middleware('auth:api');
  });
});
