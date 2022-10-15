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

Route::group(["prefix" => "v1", "middleware" => "throttle:api"], function () {
  Route::prefix("user")->group(function () {
    // Register user
    Route::post("/register", [UserController::class, "register"]);
    // Login user
    Route::post("/login", [UserController::class, "login"])->middleware("throttle:login");
    // Profil user
    Route::get("/profile", [UserController::class, "profile"])->middleware("auth:api");
    // Update user
    Route::put("/edit/{user_id}", [UserController::class, "updateUser"])->middleware("auth:api");
    // Logout user
    Route::post("/logout", [UserController::class, "logout"])->middleware("auth:api");
  });

  Route::prefix("car")->group(function () {
    // Lihat semua mobil
    Route::get("/", [CarController::class, "index"]);
    // Aksi owner
    Route::post("/create", [CarController::class, "store"])->middleware("auth:api", "is_car_owner");
    Route::get("/owner", [CarController::class, "carOwner"])->middleware("auth:api", "is_car_owner");
    // Lihat mobil berdasarkan id mobil
    Route::get("/{car_id}", [CarController::class, "show"]);
    // Cari mobil berdasarkan nama mobil
    Route::get("/search/{keyword}", [CarController::class, "search"]);
    // Aksi owner
    Route::put("/edit/{car_id}", [CarController::class, "updateCar"])->middleware("auth:api", "is_car_owner");
    Route::delete("/delete/{car_id}", [CarController::class, "destroy"])->middleware("auth:api", "is_car_owner");
  });

  Route::group(["prefix" => "rent", "middleware" => "auth:api"], function () {
    // Aksi owner
    Route::get("/rent-list", [RentController::class, "owner"])->middleware("is_car_owner");
    Route::post("/approval-status/customer/{customer_id}", [RentController::class, "approval"])->middleware("is_car_owner");
    // Cust mau sewa mobil
    Route::post("/car/{car_id}", [RentController::class, "rentCar"])->middleware("is_customer");
    Route::get("/my-rent", [RentController::class, "history"])->middleware("is_customer");
  });
});
