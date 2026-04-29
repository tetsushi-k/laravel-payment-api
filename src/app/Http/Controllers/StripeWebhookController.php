<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * Stripe Webhook 受信コントローラー
 *
 * 【設計上の工夫】
 * Stripe から送信される Webhook イベントを受け取り、
 * 署名検証 → イベント種別の判定 → DB更新 という流れで処理する。
 * 各イベント処理は private メソッドに分離し、可読性・拡張性を確保している。
 */
class StripeWebhookController extends Controller
{
    /**
     * Stripe Webhook を受信するエントリーポイント
     *
     * POST /webhook/stripe
     *
     * 【セキュリティ上の注意点】
     * 1. 必ず署名検証（Webhook Signature）を行うこと。
     *    検証なしでは、第三者が偽のリクエストを送り付けて
     *    決済完了を偽装できてしまう（深刻なセキュリティホール）。
     *
     * 2. 署名検証には「生のリクエストボディ（Raw Body）」が必要。
     *    LaravelのRequestオブジェクトを経由すると変換されてしまうため、
     *    php://input から直接取得する。
     *
     * 3. Webhook Secret は .env に保存し、コードに直接書かないこと。
     *    本番環境では必ず whsec_ から始まる本番用シークレットを使用すること。
     *
     * 4. レスポンスは処理の成否に関係なく 200 を返すのが Stripe の推奨。
     *    200 以外を返すと Stripe がリトライし、二重処理の原因になる。
     *    ただし検証失敗時は 400 を返して不正リクエストを弾く。
     */
    public function handle(Request $request): Response
    {
        // -----------------------------------------------------------
        // Step 1: 生のリクエストボディを取得
        // Laravelのリクエストオブジェクトではなく、php://input から直接取得。
        // Stripeの署名検証はバイト単位で行われるため、加工されていない
        // 生データが必須。
        // -----------------------------------------------------------
        $payload = file_get_contents('php://input');

        // -----------------------------------------------------------
        // Step 2: Stripe-Signature ヘッダーを取得
        // Stripe はリクエストヘッダーに署名を含めて送信してくる。
        // このヘッダーが存在しない場合は不正なリクエストとして拒否する。
        // -----------------------------------------------------------
        $sigHeader = $request->header('Stripe-Signature');

        if (empty($sigHeader)) {
            Log::warning('Stripe Webhook: Stripe-Signature ヘッダーが存在しません');
            return response('Signature header missing', 400);
        }

        // -----------------------------------------------------------
        // Step 3: Webhook シークレットを .env から取得
        // セキュリティ注意: シークレットはコードにハードコードしないこと。
        // -----------------------------------------------------------
        $webhookSecret = config('services.stripe.webhook_secret');

        if (empty($webhookSecret)) {
            Log::error('Stripe Webhook: STRIPE_WEBHOOK_SECRET が設定されていません');
            return response('Webhook secret not configured', 500);
        }

        // -----------------------------------------------------------
        // Step 4: 署名検証
        // Stripe\Webhook::constructEvent() を使って署名を検証する。
        // 検証に失敗した場合は SignatureVerificationException が発生する。
        // この検証をスキップするのは絶対に禁止。
        // -----------------------------------------------------------
        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            // 署名検証失敗 = 不正なリクエストとして 400 を返す
            Log::warning('Stripe Webhook: 署名検証に失敗しました', [
                'error' => $e->getMessage(),
            ]);
            return response('Invalid signature', 400);
        } catch (\Exception $e) {
            Log::error('Stripe Webhook: イベントの解析に失敗しました', [
                'error' => $e->getMessage(),
            ]);
            return response('Webhook error', 400);
        }

        // -----------------------------------------------------------
        // Step 5: イベント種別に応じた処理の振り分け
        // Stripe は様々なイベントを送信してくる。
        // 処理したいイベントのみ対応し、それ以外は無視して 200 を返す。
        // （Stripe のベストプラクティス）
        // -----------------------------------------------------------
        Log::info('Stripe Webhook: イベント受信', ['type' => $event->type]);

        match ($event->type) {
            'payment_intent.succeeded'              => $this->handlePaymentIntentSucceeded($event->data->object),
            'payment_intent.payment_failed'         => $this->handlePaymentIntentFailed($event->data->object),
            'charge.refunded'                       => $this->handleChargeRefunded($event->data->object),
            default                                 => Log::info('Stripe Webhook: 未処理のイベント', ['type' => $event->type]),
        };

        // Stripe の推奨: 処理完了後は 200 を返す
        return response('Webhook received', 200);
    }

    /**
     * 決済成功イベントの処理
     *
     * payment_intent.succeeded
     * Stripe での決済が成功したときに発火する。
     * ordersテーブルの status を 'succeeded' に更新する。
     */
    private function handlePaymentIntentSucceeded(object $paymentIntent): void
    {
        Log::info('Stripe Webhook: 決済成功', ['payment_intent_id' => $paymentIntent->id]);

        Order::where('stripe_payment_intent_id', $paymentIntent->id)
            ->update(['status' => 'succeeded']);
    }

    /**
     * 決済失敗イベントの処理
     *
     * payment_intent.payment_failed
     * 決済が失敗したときに発火する（カード拒否など）。
     * ordersテーブルの status を 'failed' に更新する。
     */
    private function handlePaymentIntentFailed(object $paymentIntent): void
    {
        Log::warning('Stripe Webhook: 決済失敗', ['payment_intent_id' => $paymentIntent->id]);

        Order::where('stripe_payment_intent_id', $paymentIntent->id)
            ->update(['status' => 'failed']);
    }

    /**
     * 返金イベントの処理
     *
     * charge.refunded
     * 管理者が Stripe Dashboard から返金を行ったときに発火する。
     * ordersテーブルの status を 'refunded' に更新する。
     */
    private function handleChargeRefunded(object $charge): void
    {
        Log::info('Stripe Webhook: 返金処理', ['payment_intent_id' => $charge->payment_intent]);

        Order::where('stripe_payment_intent_id', $charge->payment_intent)
            ->update(['status' => 'refunded']);
    }
}
