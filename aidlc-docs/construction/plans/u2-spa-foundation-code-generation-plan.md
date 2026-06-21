# U2 spa-foundation — Code Generation Plan

## Unit Context

| 項目 | 内容 |
|---|---|
| **Unit** | U2 spa-foundation |
| **Stories** | US-011, US-001, US-002, US-003 |
| **依存** | U1 auth-api |
| **想定コミット** | #2 scaffold, #3 login flow（本 Unit で一括実装） |

## Steps

- [x] Step 1: React + TypeScript + React Router 依存追加、Vite/tsconfig 設定
- [x] Step 2: `app.blade.php` 作成、`web.php` SPA フォールバック
- [x] Step 3: `ApiClient`, `useAuth`, `AuthGuard`, `Layout` 実装
- [x] Step 4: `LoginPage`（Tailwind、Blade 相当 UI）、`/orders` プレースホルダ
- [x] Step 5: `npm run build` 確認（ローカル Node / Docker 環境で実行要）
- [x] Step 6: `aidlc-state.md` / `audit.md` 更新

## 変更ファイル

| 操作 | パス |
|---|---|
| 新規 | `resources/views/app.blade.php` |
| 新規 | `resources/js/main.tsx`, `App.tsx`, `api/client.ts`, … |
| 改修 | `package.json`, `vite.config.js`, `routes/web.php`, `resources/css/app.css` |
| 削除 | `resources/js/app.js`（Vite エントリを main.tsx に移行） |
