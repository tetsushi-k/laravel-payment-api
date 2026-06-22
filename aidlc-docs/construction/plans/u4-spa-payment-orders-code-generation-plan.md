# U4 spa-payment-orders — Code Generation Plan

## Unit Context

| 項目 | 内容 |
|---|---|
| **Unit** | U4 spa-payment-orders |
| **Stories** | US-004, US-005, US-006, US-007, US-008, US-009 |
| **依存** | U2 spa-foundation, U3 payment-orders-api |
| **想定コミット** | #5 `feat(spa): 決済・注文一覧画面を追加` |

## 実装方針

- U3 の JSON API を React 画面に接続
- Stripe.js は `@stripe/stripe-js` + `@stripe/react-stripe-js` を使用
- Blade 版 UI を Tailwind で踏襲（CardElement、テストカード案内、ステータス日本語ラベル）
- 注文一覧の更新は手動「再取得」のみ（ポーリングなし）
- Feature テストは U5 に委譲

## Steps

- [x] Step 1: `@stripe/stripe-js` / `@stripe/react-stripe-js` 追加
- [x] Step 2: `PaymentPage` — config API + intent API + confirmCardPayment
- [x] Step 3: `PaymentSuccessPage` — 完了画面と `/orders` 導線
- [x] Step 4: `OrderListPage` — 一覧・再取得・ページネーション
- [x] Step 5: `App.tsx` に `/payment/success` ルート追加
- [x] Step 6: `npm run build` 確認
- [x] Step 7: `aidlc-state.md` / `audit.md` 更新

## Story Traceability

| Story | 実装 |
|---|---|
| US-004 | `PaymentPage` — 金額入力 + Stripe Card Element + 公開キー API |
| US-005 | intent API → `confirmCardPayment` → success 遷移 |
| US-006 | `PaymentSuccessPage` |
| US-007 | `OrderListPage` — 本人注文の一覧表示 |
| US-008 | 「再取得」ボタンで `GET /api/orders` |
| US-009 | `meta` に基づくページネーション |

## 変更ファイル

| 操作 | パス |
|---|---|
| 改修 | `src/package.json`, `src/package-lock.json` |
| 新規 | `src/resources/js/types/order.ts`, `types/payment.ts` |
| 改修 | `src/resources/js/pages/PaymentPage.tsx`, `OrderListPage.tsx` |
| 新規 | `src/resources/js/pages/PaymentSuccessPage.tsx` |
| 改修 | `src/resources/js/App.tsx` |
