# Services — React SPA 化

## 既存サービス（維持）

### PaymentService
- **責務**: Webhook 起因の注文ステータス更新（succeeded / failed / refunded）
- **オーケストレーション**: `DB::transaction` + `lockForUpdate()` + 条件付き UPDATE
- **SPA 化での変更**: なし

## 新規/拡張パターン

### Sanctum SPA 認証フロー（横断関心）
```
ApiClient.ensureCsrfCookie()
  → AuthApiController.login()  [session regenerate]
  → 以降の API は session cookie で認証
  → auth:sanctum ミドルウェア
```

### 決済フロー（同期 + 非同期の分離）
```
PaymentPage
  → PaymentApiController.createIntent()  [pending order 作成]
  → Stripe.js confirmCardPayment()       [ブラウザ → Stripe 直接]
  → （非同期）StripeWebhookController
  → PaymentService.handleSucceeded()     [status 確定]
  → OrderListPage 手動再取得
```

### エラーハンドリング方針
- API: Laravel 標準 JSON エラー（422 バリデーション、401 未認証）
- SPA: 401 → `/login` リダイレクト、422 → フォームエラー表示、Stripe エラー → 画面内メッセージ

## サービス層を新設しない理由
- 認証・一覧取得は Controller + Eloquent で十分（薄い CRUD）
- 決済ロジックは既に `PaymentService` に集約済み
- 過剰な抽象化を避ける（ポートフォリオ品質基準）
