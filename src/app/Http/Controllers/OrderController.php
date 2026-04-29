<?php

namespace App\Http\Controllers;

use App\Models\Order;

class OrderController extends Controller
{
    /**
     * 注文一覧を表示する
     * GET /orders
     *
     * Webhook によって更新された決済ステータスを一覧で確認できる画面。
     * 新しい順に表示し、Webhook のリアルタイム更新を確認しやすくする。
     */
    public function index()
    {
        $orders = Order::latest()->paginate(20);

        return view('orders.index', compact('orders'));
    }
}
