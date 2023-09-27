<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
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

Route::group(['prefix' => 'auth'], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::POST('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('jwt.verify');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('jwt.verify');
    Route::get('userProfile', [AuthController::class, 'userProfile'])->middleware('jwt.verify');
});

Route::group(['prefix' => 'user', 'middleware' => 'jwt.verify'], function () {
    Route::get('/', [UserController::class, 'getAll']);
    Route::get('/{username}', [UserController::class, 'getByUsername']);
    Route::put('/{username}',[UserController::class, 'update']);
    Route::delete('/{username}',[UserController::class, 'delete']);
});