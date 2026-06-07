# Components — React SPA 化

## Backend（Laravel API 層）

### C-B1: AuthApiController（新規/改修）
- **Purpose**: SPA 向け認証 API
- **Responsibilities**: ログイン、ログアウト、認証ユーザー返却、セッション再生成
- **変更**: 既存 `AuthController` を JSON 化するか、API 専用コントローラーに分離

### C-B2: PaymentApiController（改修）
- **Purpose**: PaymentIntent 作成 API
- **Responsibilities**: 金額バリデーション、Stripe PaymentIntent 作成、`orders` pending 作成、`clientSecret` 返却
- **変更**: `PaymentController::createIntent` の JSON API 化。Blade 表示は削除

### C-B3: OrderApiController（改修）
- **Purpose**: 注文一覧 API
- **Responsibilities**: ログインユーザーの注文をページネーションで JSON 返却
- **変更**: `OrderController::index` の JSON 化

### C-B4: ConfigApiController（新規）
- **Purpose**: フロント初期化用の公開設定
- **Responsibilities**: Stripe 公開キーを JSON で返却（シークレットは返さない）

### C-B5: StripeWebhookController（維持）
- **Purpose**: Stripe Webhook 受信
- **Responsibilities**: 署名検証、イベント振り分け（変更なし）

### C-B6: PaymentService（維持）
- **Purpose**: 決済ビジネスロジック
- **Responsibilities**: 冪等な状態遷移（変更なし）

## Frontend（React SPA 層）

### C-F1: App / Router
- **Purpose**: SPA ルート定義とレイアウト
- **Responsibilities**: `/login`, `/payment`, `/payment/success`, `/orders`, `/` リダイレクト

### C-F2: AuthGuard
- **Purpose**: 認証状態に基づくルート保護
- **Responsibilities**: 未認証 → `/login`、API 401 ハンドリング

### C-F3: ApiClient
- **Purpose**: API 呼び出しの共通化
- **Responsibilities**: CSRF クッキー取得、JSON リクエスト、エラーハンドリング

### C-F4: LoginPage
- **Purpose**: ログイン UI
- **Responsibilities**: フォーム、エラー表示、`POST /api/login`

### C-F5: PaymentPage
- **Purpose**: 決済フォーム UI
- **Responsibilities**: 金額入力、Stripe Card Element、`confirmCardPayment`

### C-F6: PaymentSuccessPage
- **Purpose**: 決済完了 UI

### C-F7: OrderListPage
- **Purpose**: 注文一覧 UI
- **Responsibilities**: 一覧表示、手動再取得、ページネーション

### C-F8: Layout
- **Purpose**: 共通ヘッダー（ユーザー名、ログアウト、ナビ）

## Infrastructure（最小変更）

### C-I1: SpaEntry（app.blade.php）
- **Purpose**: Vite + React のマウントポイント、SPA フォールバック

### C-I2: web.php フォールバック
- **Purpose**: 非 API ルートを SPA にフォールバック（React Router が処理）
