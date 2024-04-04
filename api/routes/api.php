<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\ServiceController;
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


// Ruta para Authenticacion
Route::prefix('auth/users')->namespace('Api')->group(function () {
    // Ruta para el registro de usuarios
    Route::post('register', [AuthController::class, 'register']);
    // Ruta para el inicio de sesión de usuarios
    Route::post('login', [AuthController::class, 'login']);
    // Ruta para cerrar sesión de usuarios
    Route::post('logout', [AuthController::class, 'logout']);
});


// Rutas para solicitar servicios
Route::prefix('services')->namespace('Api')->group(function () {
    // Ruta para solicitar servicio por un usuario
    Route::post('request-service', [ServiceController::class, 'requestService']);
    // Ruta para cancelar servicio por un usuario
    Route::put('reject-service/{serviceId}', [ServiceController::class, 'rejectService']);
});


// Rutas para los drivers
Route::prefix('drivers')->namespace('Api')->group(function () {
    // Ruta para solicitar servicio por un usuario
    Route::put('update-location-driver', [DriverController::class, 'updateLocationDriver']);
    // Ruta para aceptar servicio
    Route::put('accept-service', [DriverController::class, 'acceptService']);
    // Ruta para aceptar servicio
    Route::put('end-service/{serviceId}', [DriverController::class, 'endService']);
});
