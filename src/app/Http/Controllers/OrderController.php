<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * 注文一覧を表示する
     * GET /orders
     *
     * ログインユーザー自身の注文のみ表示する。
     * 他ユーザーやゲスト注文（user_id = null）は表示しない。
     */
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('orders.index', compact('orders'));
    }
}
