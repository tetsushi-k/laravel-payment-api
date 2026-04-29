<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;

Route::get('/', function () {
    return redirect('/payment');
});

// -----------------------------------------------------------
// 決済フォーム関連ルート
// -----------------------------------------------------------
Route::get('/payment', [PaymentController::class, 'index']);
Route::post('/payment/intent', [PaymentController::class, 'createIntent']);
Route::get('/payment/success', [PaymentController::class, 'success']);

// -----------------------------------------------------------
// 注文一覧ルート
// Webhook で更新された決済ステータスを確認する画面
// -----------------------------------------------------------
Route::get('/orders', [OrderController::class, 'index']);
