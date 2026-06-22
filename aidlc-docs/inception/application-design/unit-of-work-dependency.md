# Unit Dependencies — React SPA 化

## 実行順序（必須）

```
U1 auth-api
  ↓
  ├── U2 spa-foundation
  └── U3 payment-orders-api
         ↓
      U4 spa-payment-orders  （U2 + U3 完了後）
         ↓
      U5 cleanup-tests-docs
```

## Dependency Matrix

| Unit | Depends On | Blocks |
|---|---|---|
| U1 | — | U2, U3 |
| U2 | U1 | U4 |
| U3 | U1 | U4 |
| U4 | U2, U3 | U5 |
| U5 | U1, U2, U3, U4 | — |

## 並行化
- **U2 と U3** は U1 完了後に並行可能（フロント土台と API を別セッションで進められる）
- **推奨**: 勉強用・初回は **直列（U1→U2→U3→U4→U5）** で常に動く状態を確認しやすい

## 共有リソース
- `routes/api.php` — U1 と U3 で追記（コンフリクト注意、順序守れば問題なし）
- `package.json` / `vite.config.js` — U2 で作成、U4 で依存追加（@stripe/stripe-js 等）
- `PaymentService` / Webhook — 全 Unit で変更しない

## Rollback
- 各 Unit は `feat/react-spa` ブランチ上の個別コミットで revert 可能
