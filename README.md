# laravel-payment-api

Laravel × Stripe Webhook による決済連携 API のポートフォリオ実装です。

---

## ① 概要

Stripe の決済フローと Webhook 連携を実装したバックエンド API です。
決済イベント（成功・失敗・返金）を受け取り、データベースの注文ステータスをリアルタイムで更新します。

**主な機能**
- `POST /api/webhook/stripe` - Stripe Webhook 受信エンドポイント
- 署名検証（Webhook Signature）による不正リクエストの検出・拒否
- `orders` テーブルへの決済ステータスの記録・更新
- イベント種別（成功 / 失敗 / 返金）ごとの処理振り分け

---

## ② 使用技術

| カテゴリ | 技術 |
|---|---|
| バックエンド | Laravel 13.x（PHP 8.4） |
| 決済 | Stripe API（stripe/stripe-php v20.x） |
| データベース | MySQL 8.0 |
| Web サーバー | Nginx 1.25 |
| コンテナ | Docker / Docker Compose |
| 認証（API） | Laravel Sanctum |

---

## ③ 設計上の工夫

### セキュリティ
- **Webhook 署名検証の必須化**
  `Stripe\Webhook::constructEvent()` で署名を検証し、第三者による偽リクエストを防止。
  署名検証をスキップするコードは一切存在しない。

- **生のリクエストボディ取得**
  Laravel の Request オブジェクトではなく `php://input` から直接取得することで、
  署名検証に必要なバイト列を保持。

- **機密情報の管理**
  API キー・Webhook シークレットはすべて `.env` で管理し、コードへの直接記述を禁止。

### データ設計
- **金額を整数で保持**
  Stripe の仕様に合わせ `amount` カラムを `integer` 型にすることで、浮動小数点誤差を排除。

- **冪等性の確保**
  `stripe_payment_intent_id` に `unique` 制約を付与し、同一イベントの二重処理を防止。

- **拡張性**
  `user_id` を `nullable` にすることで、将来のゲスト購入対応が可能。
  `status` を `string` 型にすることで、新しい Stripe ステータスにも柔軟に対応。

### アーキテクチャ
- イベント処理を private メソッドに分離し、コントローラーの可読性・保守性を確保。
- Docker でコンテナを専用ネットワーク（`laravel_network`）で隔離し、外部からの直接アクセスを防止。

---

## ④ ローカル起動方法

### 前提条件
- Docker Desktop がインストール済みであること
- Stripe アカウントを持っていること（テストモード）

### 手順

**1. リポジトリのクローン**
```bash
git clone <リポジトリURL>
cd laravel-payment-api
```

**2. 環境変数ファイルの作成**
```bash
cp src/.env.example src/.env
```

`.env` を編集して Stripe のキーを設定します。

```env
STRIPE_SECRET_KEY=sk_test_xxxxxxxxxx   # Stripe Dashboard から取得
STRIPE_PUBLIC_KEY=pk_test_xxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxx # Webhook 設定後に取得
```

**3. Docker コンテナの起動**
```bash
docker compose up -d --build
```

**4. マイグレーションの実行**
```bash
docker compose exec app php artisan migrate
```

**5. 動作確認**

ブラウザで `http://localhost` にアクセスし、Laravel のウェルカムページが表示されれば起動成功です。

### Stripe Webhook のローカルテスト

[Stripe CLI](https://stripe.com/docs/stripe-cli) を使用してローカルで Webhook を受信できます。

```bash
# Stripe CLI でイベントを転送
stripe listen --forward-to localhost/api/webhook/stripe

# 別ターミナルでテストイベントを送信
stripe trigger payment_intent.succeeded
```

### コンテナの停止

```bash
docker compose down
```

---

## ディレクトリ構成

```
laravel-payment-api/
├── docker/
│   ├── nginx/
│   │   └── default.conf     # Nginx 設定
│   └── php/
│       └── Dockerfile        # PHP 8.4-fpm 環境
├── src/                      # Laravel アプリケーション
│   ├── app/Http/Controllers/
│   │   └── StripeWebhookController.php
│   ├── database/migrations/
│   │   └── xxxx_create_orders_table.php
│   └── routes/
│       └── api.php
├── docker-compose.yml
└── README.md
```
