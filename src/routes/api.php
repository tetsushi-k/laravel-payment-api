<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthApiController;
use App\Http\Controllers\ConfigApiController;
use App\Http\Controllers\OrderApiController;
use App\Http\Controllers\PaymentApiController;
use App\Http\Controllers\StripeWebhookController;

Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/logout', [AuthApiController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthApiController::class, 'user'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payment/intent', [PaymentApiController::class, 'createIntent']);
    Route::get('/orders', [OrderApiController::class, 'index']);
    Route::get('/config/stripe', [ConfigApiController::class, 'stripe']);
});

// -----------------------------------------------------------
// Stripe Webhook エンドポイント
//
// 【セキュリティ上の注意点】
// 1. このルートは認証ミドルウェアを外している。
//    Stripe のサーバーから直接 POST されるため、
//    auth:sanctum などの認証は不要・かつ通過できない。
//    代わりに StripeWebhookController 内で署名検証を行う。
//
// 2. CSRF 保護について:
//    api.php に記載したルートは Laravel 13 では自動的に
//    CSRF 検証の対象外になる（api ミドルウェアグループに属するため）。
//    追加の CSRF 除外設定は不要。
// -----------------------------------------------------------
Route::post('/webhook/stripe', [StripeWebhookController::class, 'handle']);
