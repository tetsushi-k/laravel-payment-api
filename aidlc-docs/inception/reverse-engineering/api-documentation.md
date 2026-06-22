# API Documentation

> 現状は SSR（Blade）構成のため、大半のエンドポイントは HTML を返す Web ルート。JSON を返すのは PaymentIntent 作成と Webhook のみ。SPA 化ではこれらを JSON API へ再編する。

## Web Routes（routes/web.php）

### ルートリダイレクト
- **Method**: GET
- **Path**: `/`
- **Purpose**: `/payment` へリダイレクト。
- **Auth**: なし
- **Response**: 302 Redirect

### ログイン画面
- **Method**: GET
- **Path**: `/login`（name: `login`）
- **Purpose**: ログインフォーム表示。ログイン済みなら `/orders` へ。
- **Response**: HTML（`auth.login`）

### ログイン処理
- **Method**: POST
- **Path**: `/login`
- **Purpose**: セッション認証。成功で `intended`（既定 `/orders`）へ。
- **Request**: `email`（required, email）, `password`（required）, `remember`（任意）
- **Response**: 302 Redirect（失敗時はエラー付きで `back()`）

### ログアウト
- **Method**: POST
- **Path**: `/logout`（name: `logout`）
- **Purpose**: セッション破棄・トークン再生成。
- **Response**: 302 Redirect → `/login`

### 決済フォーム
- **Method**: GET
- **Path**: `/payment`
- **Auth**: `auth` ミドルウェア
- **Purpose**: 決済フォーム表示（公開キーを埋め込み）。
- **Response**: HTML（`payment.index`）

### PaymentIntent 作成
- **Method**: POST
- **Path**: `/payment/intent`
- **Auth**: `auth` ミドルウェア
- **Purpose**: PaymentIntent 作成、`orders` を pending 作成、`client_secret` 返却。
- **Request**: `{ "amount": <int, 100-1000000> }`（JSON、`X-CSRF-TOKEN` 必須）
- **Response**: `200 { "clientSecret": "<string>" }`

### 決済完了画面
- **Method**: GET
- **Path**: `/payment/success`
- **Auth**: `auth` ミドルウェア
- **Response**: HTML（`payment.success`）

### 注文一覧
- **Method**: GET
- **Path**: `/orders`
- **Auth**: `auth` ミドルウェア
- **Purpose**: 本人の注文をページネーション表示（20 件/頁）。
- **Response**: HTML（`orders.index`）

## API Routes（routes/api.php、プレフィックス `/api`）

### 認証ユーザー取得（サンプル）
- **Method**: GET
- **Path**: `/api/user`
- **Auth**: `auth:sanctum`
- **Purpose**: 認証済みユーザー返却（Laravel 標準サンプル、業務未使用）。
- **Response**: JSON（User）

### Stripe Webhook 受信
- **Method**: POST
- **Path**: `/api/webhook/stripe`
- **Auth**: なし（署名検証で代替）
- **Purpose**: Stripe イベント受信・署名検証・状態反映。
- **Request**: 生ボディ（Stripe イベント JSON）+ `Stripe-Signature` ヘッダ
- **Response**:
  - `200 "Webhook received"`（正常受理。未処理イベントも 200）
  - `400`（署名ヘッダ欠落／署名検証失敗／解析失敗）
  - `500`（Webhook シークレット未設定）
- **処理対象イベント**: `payment_intent.succeeded`, `payment_intent.payment_failed`, `charge.refunded`

## Internal APIs

### PaymentService
- **`handleSucceeded(object $paymentIntent): void`** - pending の注文を `succeeded` に更新（トランザクション + 行ロック）。
- **`handleFailed(object $paymentIntent): void`** - pending の注文を `failed` に更新。
- **`handleRefunded(object $charge): void`** - succeeded の注文を `refunded` に更新（`charge.payment_intent` で照合）。

## Data Models

### Order
- **Fields**: `id`, `user_id`（nullable, FK）, `amount`（integer, 最小通貨単位）, `status`（pending/succeeded/failed/refunded）, `stripe_payment_intent_id`（nullable, unique）, `created_at`, `updated_at`。
- **Relationships**: `User` に従属（現状モデル上のリレーションメソッドは未定義、`user_id` で紐付け）。
- **Validation**: 作成時は `amount` を `integer|min:100|max:1000000`（Controller 側）。

### User
- **Fields**: `id`, `name`, `email`（unique）, `email_verified_at`, `password`（hashed）, `remember_token`, timestamps。
- **Relationships**: 複数 `Order` を持つ（業務上）。
- **Validation**: 認証時に `email`/`password` を検証。`password` は `hashed` キャスト。
