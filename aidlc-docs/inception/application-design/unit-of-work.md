# Units of Work — React SPA 化

**定義**: 論理的な開発単位（モノリス内モジュール）。独立デプロイはしない。

## U1: auth-api

- **責務**: Sanctum SPA 認証 API の提供
- **Stories**: US-001, US-002, US-003
- **主な変更**:
  - `POST /api/login`, `POST /api/logout`, `GET /api/user`
  - Sanctum stateful domains / CSRF 設定
  - `bootstrap/app.php` または middleware 調整
- **完了条件**: curl/Feature test でログイン・401・ログアウトが動作
- **想定コミット**: #1 `feat(api): add Sanctum SPA auth JSON endpoints`

## U2: spa-foundation

- **責務**: React SPA の土台とログイン UI
- **Stories**: US-011, US-001, US-002, US-003
- **主な変更**:
  - React + TypeScript + Vite + React Router + Tailwind
  - `app.blade.php`, `main.tsx`, `App.tsx`, `ApiClient`, `useAuth`, `LoginPage`, `AuthGuard`
  - `web.php` SPA フォールバック（初期）
- **依存**: U1
- **完了条件**: ログイン → 保護ルート遷移、未認証は `/login`
- **想定コミット**: #2 scaffold, #3 login flow

## U3: payment-orders-api

- **責務**: 決済・注文・Stripe 設定の JSON API
- **Stories**: US-004, US-005, US-007, US-008, US-009
- **主な変更**:
  - `POST /api/payment/intent`, `GET /api/orders`, `GET /api/config/stripe`
  - Controller の JSON 化（PaymentService / Webhook は触らない）
- **依存**: U1
- **完了条件**: 認証後 API が JSON で期待レスポンスを返す
- **想定コミット**: #4 `feat(api): add payment and orders JSON endpoints`

## U4: spa-payment-orders

- **責務**: 決済・完了・注文一覧の React 画面
- **Stories**: US-004, US-005, US-006, US-007, US-008, US-009
- **主な変更**:
  - `PaymentPage`（Stripe.js）, `PaymentSuccessPage`, `OrderListPage`, `Layout`
- **依存**: U2, U3
- **完了条件**: ログイン → 決済 → 完了 → 注文一覧（手動再取得）が手動 E2E で通る
- **想定コミット**: #5 `feat(spa): add payment and order list pages`

## U5: cleanup-tests-docs

- **責務**: レガシー削除、テスト、README
- **Stories**: US-010, US-011
- **主な変更**:
  - Blade ビュー削除（`app.blade.php` 以外）
  - 旧 web ルート整理
  - Feature tests（auth, payment intent, webhook）
  - README SPA 版更新
- **依存**: U1〜U4
- **完了条件**: `php artisan test` パス、README 整合、Blade 依存解消
- **想定コミット**: #6 refactor, #7 test, #8 docs
