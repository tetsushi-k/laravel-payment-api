# U1 auth-api — Code Generation Plan

## Unit Context

| 項目 | 内容 |
|---|---|
| **Unit** | U1 auth-api |
| **Stories** | US-001, US-002, US-003（API 部分） |
| **依存** | なし |
| **想定コミット** | #1 `feat(api): add Sanctum SPA auth JSON endpoints` |

## 実装方針

- 既存 `AuthController`（Blade SSR）は U2 完了まで維持
- 新規 `AuthApiController` で JSON API を追加
- Sanctum SPA 認証（セッション + CSRF）を `statefulApi()` で有効化
- Feature テストは U5（commit #7）に委譲。U1 完了条件は curl 検証

## Steps

- [x] Step 1: `bootstrap/app.php` に `statefulApi()` を追加
- [x] Step 2: `.env.example` に `SANCTUM_STATEFUL_DOMAINS` を追記
- [x] Step 3: `AuthApiController` を新規作成（login / logout / user）
- [x] Step 4: `routes/api.php` に認証ルートを登録
- [x] Step 5: curl で login / user / logout / 401 を手動検証
- [x] Step 6: `aidlc-state.md` / `audit.md` を更新

## Story Traceability

| Story | 実装 |
|---|---|
| US-001 | `POST /api/login` — attempt + session regenerate + `{ user }` |
| US-002 | `POST /api/logout` — session invalidate + 204 |
| US-003 | `GET /api/user` — `auth:sanctum`、未認証 401 |

## 変更ファイル

| 操作 | パス |
|---|---|
| 改修 | `src/bootstrap/app.php` |
| 改修 | `src/routes/api.php` |
| 改修 | `src/.env.example` |
| 新規 | `src/app/Http/Controllers/AuthApiController.php` |
