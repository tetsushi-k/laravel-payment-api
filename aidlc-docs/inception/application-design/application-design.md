# Application Design — React SPA 化（統合）

**Depth**: minimal  
**参照**: `components.md`, `component-methods.md`, `services.md`, `component-dependency.md`

## 設計方針

Laravel モノリス内で **API 層（JSON）** と **SPA 層（React）** を分離する。決済中核（`PaymentService` / Webhook）は維持し、変更はプレゼンテーションと API 契約に限定する。

## アーキテクチャ概要

```
Browser
  ├─ React SPA (Vite)     … 画面・ルーティング・Stripe.js
  └─ Cookie (Sanctum)     … セッション認証

Laravel
  ├─ routes/api.php       … JSON API（auth, payment, orders, config）
  ├─ routes/web.php       … SPA フォールバック + sanctum/csrf-cookie
  ├─ Controllers          … 薄い API 層
  ├─ PaymentService       … 既存（Webhook 処理）
  └─ StripeWebhook        … 既存（署名検証）
```

## コンポーネント一覧

| 層 | コンポーネント | 新規/改修/維持 |
|---|---|---|
| API | AuthApiController | 改修 |
| API | PaymentApiController | 改修 |
| API | OrderApiController | 改修 |
| API | ConfigApiController | 新規 |
| API | StripeWebhookController | 維持 |
| Service | PaymentService | 維持 |
| SPA | App/Router, AuthGuard, ApiClient | 新規 |
| SPA | Login, Payment, Success, OrderList, Layout | 新規 |
| Infra | app.blade.php, web.php fallback | 改修 |

## API 契約（サマリ）

| Endpoint | Auth | 用途 |
|---|---|---|
| `POST /api/login` | なし | ログイン |
| `POST /api/logout` | sanctum | ログアウト |
| `GET /api/user` | sanctum | ユーザー取得 |
| `POST /api/payment/intent` | sanctum | PaymentIntent |
| `GET /api/orders` | sanctum | 注文一覧 |
| `GET /api/config/stripe` | sanctum | 公開キー |
| `POST /api/webhook/stripe` | 署名 | Webhook（既存） |

## Sanctum SPA 設定要点
- `SANCTUM_STATEFUL_DOMAINS` に `localhost` を含める
- SPA 起動時に `/sanctum/csrf-cookie` を取得
- API リクエストに `X-XSRF-TOKEN` を付与
- `auth:sanctum` で保護ルートをガード

## ディレクトリ方針（SPA）

```
src/resources/js/
├── main.tsx
├── App.tsx
├── api/client.ts
├── hooks/useAuth.ts
├── components/Layout.tsx
└── pages/
    ├── LoginPage.tsx
    ├── PaymentPage.tsx
    ├── PaymentSuccessPage.tsx
    └── OrderListPage.tsx
```

## 意図的にやらないこと
- 新規 Service クラスの乱立
- Order モデルのリレーション/キャスト整備（要件スコープ外）
- Bearer トークン認証
- リアルタイム更新（WebSocket）
