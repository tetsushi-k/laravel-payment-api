<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeWebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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
