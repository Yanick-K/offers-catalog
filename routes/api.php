<?php

declare(strict_types=1);

use App\Http\Controllers\Api\OfferController as ApiOfferController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public endpoint to list published offers
Route::prefix('v1')->group(function () {
    Route::get('/offers', [ApiOfferController::class, 'index']);
});

Route::get('/offers', [ApiOfferController::class, 'index']);
