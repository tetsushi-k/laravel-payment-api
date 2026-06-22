# Commit Strategy — React SPA 化

ポートフォリオとして「面接で設計意図を説明できる」コミット履歴を目指す方針案。
**コミットはユーザーが明示的に依頼したときのみ作成する**（作業中は WIP でも可）。

## 基本方針

1. **1 コミット = 1 つの説明可能な変更** — 「なぜこの順番か」を口頭で説明できる粒度
2. **常に動く状態を維持** — 各コミット後も `docker compose up` + 主要フローが破綻しない
3. **docs と code を分離** — AI-DLC ドキュメント（`aidlc-docs/`）とアプリコードは別コミット
4. **feat / refactor / test / docs** の接頭辞で意図を明示（Conventional Commits 風）

## 推奨コミット順序（6〜8 コミット）

| # | コミット（案） | 内容 | 完了時の状態 |
|---|---|---|---|
| 0 | `docs: add AI-DLC inception artifacts` | Workspace Detection / RE / Requirements / User Stories 等 | ドキュメントのみ |
| 1 | `feat(api): add Sanctum SPA auth JSON endpoints` | ログイン/ログアウト/user API、CSRF 設定、既存 Web 認証は残しても可 | API 単体で Postman/curl 検証可 |
| 2 | `feat(spa): scaffold React + TypeScript + Vite + Router` | 依存追加、`app.blade.php` エントリ、空のルート、Tailwind 設定 | SPA シェルが表示される |
| 3 | `feat(spa): add login page and auth flow` | Login 画面、API クライアント、認証ガード、401 → `/login` | ログイン〜リダイレクトまで動作 |
| 4 | `feat(api): add payment and orders JSON endpoints` | payment/intent、orders 一覧、stripe config API | 認証後 API が JSON で動作 |
| 5 | `feat(spa): add payment and order list pages` | 決済フォーム（Stripe.js）、完了画面、注文一覧（手動再取得） | 主要ユーザーフロー完結 |
| 6 | `refactor: remove legacy Blade views and web routes` | 不要 Blade 削除、web.php を SPA フォールバックに整理 | Blade 依存が解消 |
| 7 | `test: add API feature tests for auth, payment, webhook` | Feature テスト追加 | `php artisan test` がパス |
| 8 | `docs: update README for React SPA architecture` | README の技術・構成・起動手順を SPA 版に更新 | README と実装が整合 |

## やらないこと

- **巨大な 1 コミット**（「SPA 化した」だけの diff）— レビュー・説明が困難
- **コミットごとに壊れた状態**（例: 認証 API だけあってフロントが真っ白のまま放置）
- **無関係なリファクタの混入**（Order モデル整備など Q8 でスコープ外としたもの）
- **秘密情報のコミット**（`.env`、Stripe キー）

## ブランチ戦略（案）

```
main
 └── feat/react-spa   ← 作業ブランチ（SPA 化すべてここで）
      ├── (commit 1..N)
      └── PR → main（完了後）
```

- 作業は `feat/react-spa` で進め、完了後に PR 作成
- 中間コミットは feature ブランチ上に積み上げ、main は常に安定

## AI-DLC ドキュメントのコミット

| タイミング | 内容 |
|---|---|
| INCEPTION 完了時 | `aidlc-docs/` の inception 成果物を 1 コミット |
| CONSTRUCTION 各 Unit 完了時 | 該当 unit の設計ドキュメント（任意・まとめてでも可） |
| 最終 | README 更新コミットでコードと揃える |

## 確定事項（2026-06-07）

ストーリー計画 Question 4 の回答: **A（推奨案どおり、6〜8 コミット）**

各コミットは `stories.md` の Epic / Story と対応:
- commit #1 → US-001〜003（認証 API）
- commit #2〜3 → US-011, US-001〜003（SPA シェル + ログイン）
- commit #4〜5 → US-004〜009（決済・注文 API + UI）
- commit #6 → US-011（Blade 削除）
- commit #7 → US-010 等（Feature テスト）
- commit #8 → README 更新
