<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 決済関連のビジネスロジックを集約するサービスクラス
 *
 * Controller はリクエストの受け取りとレスポンスの返却に専念し、
 * DB操作・状態遷移などのビジネスロジックはこのクラスに集約する。
 */
class PaymentService
{
    /**
     * 決済成功時の処理
     *
     * 冪等性の担保：
     * - status = 'pending' の注文のみを対象にすることで二重処理を防ぐ
     * - lockForUpdate() でレースコンディションを防ぐ
     */
    public function handleSucceeded(object $paymentIntent): void
    {
        Log::info('決済成功', ['payment_intent_id' => $paymentIntent->id]);

        DB::transaction(function () use ($paymentIntent) {
            $order = Order::where('stripe_payment_intent_id', $paymentIntent->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$order) {
                Log::info('処理済みまたは対象注文なし', [
                    'payment_intent_id' => $paymentIntent->id,
                ]);
                return;
            }

            $order->update(['status' => 'succeeded']);
        });
    }

    /**
     * 決済失敗時の処理
     *
     * カード拒否などで決済が失敗したときに発火する。
     */
    public function handleFailed(object $paymentIntent): void
    {
        Log::warning('決済失敗', ['payment_intent_id' => $paymentIntent->id]);

        DB::transaction(function () use ($paymentIntent) {
            $order = Order::where('stripe_payment_intent_id', $paymentIntent->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$order) {
                Log::info('処理済みまたは対象注文なし', [
                    'payment_intent_id' => $paymentIntent->id,
                ]);
                return;
            }

            $order->update(['status' => 'failed']);
        });
    }

    /**
     * 返金時の処理
     *
     * 管理者が Stripe Dashboard から返金を行ったときに発火する。
     */
    public function handleRefunded(object $charge): void
    {
        Log::info('返金処理', ['payment_intent_id' => $charge->payment_intent]);

        DB::transaction(function () use ($charge) {
            $order = Order::where('stripe_payment_intent_id', $charge->payment_intent)
                ->where('status', 'succeeded')
                ->lockForUpdate()
                ->first();

            if (!$order) {
                Log::info('処理済みまたは対象注文なし', [
                    'payment_intent_id' => $charge->payment_intent,
                ]);
                return;
            }

            $order->update(['status' => 'refunded']);
        });
    }
}
