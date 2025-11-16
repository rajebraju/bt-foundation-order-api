<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductImportController;
use App\Http\Controllers\Api\V1\OrderController;

Route::prefix('v1')->group(function () {
    Route::post('auth/register', [AuthController::class,'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:api');

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::get('products/search', [ProductController::class, 'search']);

    Route::middleware('auth:api')->group(function () {
        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{id}', [ProductController::class, 'update']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);

        Route::post('products/import', [ProductImportController::class, 'upload']);
        Route::get('products/import/{id}', [ProductImportController::class, 'status']);


        Route::get('orders', [OrderController::class, 'index']);
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
        Route::post('orders/{order}/confirm', [OrderController::class, 'confirm']);
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
        Route::get('orders/{order}/invoice', [OrderController::class, 'downloadInvoice']);
    });
});
