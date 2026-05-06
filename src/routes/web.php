<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect('/payment');
});

// -----------------------------------------------------------
// 認証ルート
// -----------------------------------------------------------
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// -----------------------------------------------------------
// 認証必須ルート（/login にリダイレクト）
// 決済・注文一覧はログイン済みユーザー専用
// ゲスト決済を許可しないことで、注文と user_id の紐付けを保証する
// -----------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::get('/payment', [PaymentController::class, 'index']);
    Route::post('/payment/intent', [PaymentController::class, 'createIntent']);
    Route::get('/payment/success', [PaymentController::class, 'success']);

    Route::get('/orders', [OrderController::class, 'index']);
});
