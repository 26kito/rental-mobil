<?php

use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\RentController;
use App\Http\Controllers\Api\UserController;
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
    Route::get('/profile/{user_id}', [UserController::class, 'profile'])->middleware('auth:api');
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::put('/edit/{user_id}', [UserController::class, 'updateUser'])->middleware('auth:api');
    Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:api');
  });

  Route::prefix('car')->group(function() {
    Route::get('/', [CarController::class, 'index']);
    Route::post('/create', [CarController::class, 'store'])->middleware('auth:api');
    Route::get('/{car_id}', [CarController::class, 'show']);
    Route::get('/user/{user_id}', [CarController::class, 'carOwner'])->middleware('auth:api');
    Route::put('edit/{car_id}', [CarController::class, 'updateCar'])->middleware('auth:api');
    Route::delete('delete/{car_id}', [CarController::class, 'destroy'])->middleware('auth:api');
  });

  Route::prefix('rent')->group(function() {
    Route::post('/{car_id}', [RentController::class, 'rentCar'])->middleware('auth:api');
  });
});
