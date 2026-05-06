<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Order;

/**
 * 決済フォームコントローラー
 *
 * 【処理の流れ】
 * 1. GET /payment  → フォーム画面を表示
 * 2. POST /payment → PaymentIntent を作成し、orders テーブルに pending で記録
 *                    client_secret をフロントに返す
 * 3. フロントの Stripe.js がカード情報を Stripe に送信して決済を完了させる
 * 4. Stripe が Webhook を送信 → StripeWebhookController が status を更新
 */
class PaymentController extends Controller
{
    /**
     * 決済フォーム画面を表示する
     * GET /payment
     */
    public function index()
    {
        return view('payment.index', [
            // Stripe の公開可能キーをフロントに渡す（Stripe.js の初期化に必要）
            'stripePublicKey' => config('services.stripe.public_key'),
        ]);
    }

    /**
     * PaymentIntent を作成して client_secret をフロントに返す
     * POST /payment/intent
     *
     * 【設計上の注意点】
     * - PaymentIntent の作成はサーバーサイドで行う（シークレットキーを使用するため）
     * - client_secret はフロントに返してよいが、シークレットキー自体は絶対に返さない
     * - orders テーブルに pending で記録しておき、Webhook で succeeded に更新する
     */
    public function createIntent(Request $request)
    {
        // 金額のバリデーション（最小100円、最大100万円）
        $request->validate([
            'amount' => 'required|integer|min:100|max:1000000',
        ]);

        // Stripe シークレットキーをセット
        Stripe::setApiKey(config('services.stripe.secret_key'));

        // PaymentIntent を作成（Stripe 側で決済の意図を登録）
        $paymentIntent = PaymentIntent::create([
            'amount'   => $request->amount,
            'currency' => 'jpy', // 日本円
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);

        // orders テーブルに pending 状態で記録
        // Webhook 受信後に status が succeeded に更新される
        Order::create([
            'user_id'                    => Auth::id(), // auth ミドルウェアを通すので必ず取得できる
            'amount'                     => $request->amount,
            'status'                     => 'pending',
            'stripe_payment_intent_id'   => $paymentIntent->id,
        ]);

        // client_secret をフロントに返す（Stripe.js の決済確定に必要）
        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
        ]);
    }

    /**
     * 決済完了画面を表示する
     * GET /payment/success
     */
    public function success()
    {
        return view('payment.success');
    }
}
