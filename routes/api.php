<?php

use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\SyncController;
use App\Http\Controllers\Api\V1\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::post('/products/{product}/enrich', [ProductController::class, 'enrich']);
    Route::post('/sync/merchant', [SyncController::class, 'dispatchRun']);
    Route::get('/sync/{syncRun}', [SyncController::class, 'show']);
});

Route::post('/webhooks/merchant', [WebhookController::class, 'merchant'])
    ->middleware('webhook.signature');
