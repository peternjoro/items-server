<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\DataController;

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

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

Route::get('all-items', [DataController::class, 'allItems']);

Route::middleware('jwt.verify')->group(function() {
    Route::get('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::get('/dashboard', function() {
        return response()->json(['message' => 'Welcome to dashboard'], 200);
    });
    Route::controller(ItemsController::class)->group(function () {
        Route::get('items','index');
        Route::post('item','store');
        Route::get('item/{id}', 'show');
        Route::post('item/{id}', 'update');
        Route::delete('item/{id}', 'destroy');
    });
});
