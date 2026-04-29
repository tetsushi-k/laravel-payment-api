<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ordersテーブルを作成する
     *
     * 【カラム設計の意図】
     *
     * id:
     *   主キー。LaravelのデフォルトのunsignedBigIntegerを使用。
     *
     * user_id:
     *   どのユーザーの注文かを紐付けるための外部キー。
     *   nullable にしているのは、将来的にゲスト購入（未ログイン購入）に
     *   対応できるよう拡張性を持たせるため。
     *
     * amount:
     *   決済金額（円単位の整数で保持）。
     *   Stripeは金額を最小通貨単位（日本円なら1円単位の整数）で扱うため、
     *   小数点を使わず integer で保存するのが正しい設計。
     *
     * status:
     *   決済の状態を表す文字列カラム。
     *   想定する値: 'pending'（処理中）/ 'succeeded'（成功）/ 'failed'（失敗）/ 'refunded'（返金済）
     *   enumではなくstringを使うことで、Stripeの新しいステータスにも柔軟に対応できる。
     *   デフォルトは 'pending'（注文作成直後は未決済状態）。
     *
     * stripe_payment_intent_id:
     *   StripeのPaymentIntentのIDを保存するカラム。
     *   このIDを使ってStripe側と照合・追跡が可能になる。
     *   nullable にしているのは、PaymentIntentの作成前にレコードを
     *   作成するフローに対応するため。
     *   unique制約を付けることで二重登録を防ぐ（セキュリティ上重要）。
     *
     * created_at / updated_at:
     *   LaravelのEloquentが自動管理するタイムスタンプ。
     *   注文の作成日時・更新日時を記録するために必須。
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            // 主キー（自動インクリメント）
            $table->id();

            // 注文したユーザーのID（未ログイン購入を考慮してnullable）
            $table->unsignedBigInteger('user_id')->nullable();

            // 決済金額（円単位の整数 / Stripeの仕様に合わせてintegerで保持）
            $table->integer('amount');

            // 決済ステータス（pending / succeeded / failed / refunded）
            $table->string('status')->default('pending');

            // StripeのPaymentIntent ID（照合・追跡用 / unique制約で二重登録を防止）
            $table->string('stripe_payment_intent_id')->nullable()->unique();

            // 作成日時・更新日時（Eloquentが自動管理）
            $table->timestamps();

            // user_idにインデックスを付与してユーザーごとの注文検索を高速化
            $table->index('user_id');
        });
    }

    /**
     * マイグレーションをロールバックする
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
