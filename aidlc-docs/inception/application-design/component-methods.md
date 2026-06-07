# Component Methods — React SPA 化

> 詳細ビジネスルールは既存 `PaymentService` / Webhook に委譲。新規は API 契約と UI 操作が中心。

## Backend API

### AuthApiController

| Method | HTTP | Path | Input | Output |
|---|---|---|---|---|
| login | POST | `/api/login` | `{ email, password }` | `200 { user }` / `422` / `401` |
| logout | POST | `/api/logout` | — | `204` |
| user | GET | `/api/user` | — | `200 { user }` / `401` |

### PaymentApiController

| Method | HTTP | Path | Input | Output |
|---|---|---|---|---|
| createIntent | POST | `/api/payment/intent` | `{ amount: int }` | `200 { clientSecret }` / `422` / `401` |

### OrderApiController

| Method | HTTP | Path | Input | Output |
|---|---|---|---|---|
| index | GET | `/api/orders` | `?page=` | `200 { data[], meta }` / `401` |

### ConfigApiController

| Method | HTTP | Path | Input | Output |
|---|---|---|---|---|
| stripe | GET | `/api/config/stripe` | — | `200 { publicKey }` |

### StripeWebhookController（既存）

| Method | HTTP | Path | Input | Output |
|---|---|---|---|---|
| handle | POST | `/api/webhook/stripe` | Raw body + Stripe-Signature | `200` / `400` / `500` |

## Frontend（React）

### ApiClient

| Method | Purpose |
|---|---|
| `ensureCsrfCookie()` | `GET /sanctum/csrf-cookie` |
| `post(path, body)` | JSON POST + CSRF ヘッダ |
| `get(path)` | JSON GET |
| `logout()` | `POST /api/logout` |

### AuthGuard / useAuth（hook）

| Method | Purpose |
|---|---|
| `login(email, password)` | ログイン API 呼び出し |
| `fetchUser()` | 現在ユーザー取得 |
| `isAuthenticated` | 認証状態 |

### PaymentPage

| Method | Purpose |
|---|---|
| `handleSubmit()` | intent 作成 → confirmCardPayment → success 遷移 |

### OrderListPage

| Method | Purpose |
|---|---|
| `fetchOrders(page?)` | 注文一覧取得 |
| `handleRefresh()` | 手動再取得 |
