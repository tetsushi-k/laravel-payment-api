# U5 cleanup-tests-docs — Code Generation Plan

## Unit Context

| 項目 | 内容 |
|---|---|
| **Unit** | U5 cleanup-tests-docs |
| **Stories** | US-010, US-011 |
| **依存** | U1〜U4 |
| **想定コミット** | #6 refactor, #7 test, #8 docs |

## Steps

- [x] Step 1: レガシー Blade ビュー削除（`app.blade.php` 以外）
- [x] Step 2: レガシー Controller 削除（Auth/Payment/Order Blade 版）
- [x] Step 3: Feature tests（auth, payment, orders, webhook, SPA shell）
- [x] Step 4: Seeder を README と整合（`password123`）
- [x] Step 5: README を SPA 版に更新
- [x] Step 6: `php artisan test` 確認
- [x] Step 7: `aidlc-state.md` / `audit.md` 更新

## 変更ファイル

| 操作 | パス |
|---|---|
| 削除 | `AuthController`, `PaymentController`, `OrderController`, Blade views |
| 新規 | `tests/Feature/AuthApiTest.php`, `PaymentApiTest.php`, `StripeWebhookTest.php`, `SpaShellTest.php` |
| 改修 | `database/seeders/DatabaseSeeder.php`, `README.md` |
