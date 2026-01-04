<?php

use App\Http\Controllers\BrewController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\TeaController;
use App\Http\Controllers\TeapotController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Health routes
Route::get('/health', [HealthController::class, 'health']);
Route::get('/health/live', [HealthController::class, 'live']);
Route::get('/health/ready', [HealthController::class, 'ready']);

// TIF 418 endpoint
Route::get('/brew', [HealthController::class, 'brew']);

// Teapot routes
Route::get('/teapots', [TeapotController::class, 'index']);
Route::post('/teapots', [TeapotController::class, 'store']);
Route::get('/teapots/{id}', [TeapotController::class, 'show']);
Route::put('/teapots/{id}', [TeapotController::class, 'update']);
Route::patch('/teapots/{id}', [TeapotController::class, 'patch']);
Route::delete('/teapots/{id}', [TeapotController::class, 'destroy']);
Route::get('/teapots/{teapotId}/brews', [BrewController::class, 'indexByTeapot']);

// Tea routes
Route::get('/teas', [TeaController::class, 'index']);
Route::post('/teas', [TeaController::class, 'store']);
Route::get('/teas/{id}', [TeaController::class, 'show']);
Route::put('/teas/{id}', [TeaController::class, 'update']);
Route::patch('/teas/{id}', [TeaController::class, 'patch']);
Route::delete('/teas/{id}', [TeaController::class, 'destroy']);

// Brew routes
Route::get('/brews', [BrewController::class, 'index']);
Route::post('/brews', [BrewController::class, 'store']);
Route::get('/brews/{id}', [BrewController::class, 'show']);
Route::patch('/brews/{id}', [BrewController::class, 'patch']);
Route::delete('/brews/{id}', [BrewController::class, 'destroy']);
Route::get('/brews/{brewId}/steeps', [BrewController::class, 'indexSteeps']);
Route::post('/brews/{brewId}/steeps', [BrewController::class, 'storeSteep']);
