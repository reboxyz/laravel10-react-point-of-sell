<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
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

/* Note! This is for sanctum which we will not be using
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {

    Route::post('login',  [AuthController::class, 'login']);
    Route::post('logout',  [AuthController::class, 'logout']);
    Route::post('refresh',  [AuthController::class, 'refresh']);
    Route::get('me',  [AuthController::class, 'me']);

});

Route::group(['middleware' => 'auth'], function($routes) {
    // Customer management
    Route::group(['prefix' => 'v1/customers'], function($routes) {
        Route::get('/list', [CustomerController::class, 'index']);
        Route::get('/getList', [CustomerController::class, 'getList']);  // For dropdown selection
        Route::post('/', [CustomerController::class, 'store']);
        Route::get('/{id}', [CustomerController::class, 'show']);
        Route::put('/{id}', [CustomerController::class, 'update']);
        Route::delete('/{id}', [CustomerController::class, 'destroy']);
    });

    // Product management
    Route::group(['prefix' => 'v1/products'], function($routes) {
        Route::get('/list', [ProductController::class, 'index']);
        Route::get('/getList', [ProductController::class, 'getList']);  // For dropdown selection
        Route::get('/categories', [ProductController::class, 'getCategories']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/{id}', [ProductController::class, 'show']);
        // Note! We changed from 'put' to 'post' because of the 'image' upload
        Route::post('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
    });

    // Order management
    Route::group(['prefix' => 'v1/orders'], function($routes) {
        Route::get('/list', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/{id}', [OrderController::class, 'show']);
    });
    
});