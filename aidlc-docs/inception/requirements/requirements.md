# Requirements — React SPA 化

## Intent Analysis

| 項目 | 内容 |
|---|---|
| **User Request** | `laravel-payment-api` を React SPA 化する |
| **Request Type** | Migration / Enhancement（プレゼンテーション層の刷新） |
| **Scope Estimate** | Multiple Components（フロントエンド全面刷新 + バックエンド API 化 + 認証方式変更） |
| **Complexity Estimate** | Moderate（既存決済中核は維持、画面4枚 + API 整備） |
| **Requirements Depth** | Standard |

## 回答サマリ（確定方針）

ユーザー回答と、未回答・「おすすめで」項目の AI 推奨を統合した確定方針。

| # | 論点 | 確定 |
|---|---|---|
| Q1 | SPA 全体構成 | **A** 真の SPA + JSON API（同一リポジトリ、Laravel がビルド成果物を配信） |
| Q2 | 認証方式 | **A** Laravel Sanctum SPA 認証（クッキー + CSRF）※ユーザー「おすすめ」→ Q1 と整合する標準選択 |
| Q3 | React 化範囲 | **A** 全画面（ログイン / 決済 / 完了 / 注文一覧） |
| Q4 | 言語 | **A** TypeScript |
| Q5 | ルーティング | **A** React Router |
| Q6 | スタイリング | **A** Tailwind CSS ※ユーザー「おすすめ」→ 既存依存を活用 |
| Q7 | 注文ステータス更新 | **A** 手動更新（再取得ボタン）。スコープを広げない |
| Q8 | バックエンド中核 | **A** `PaymentService` / Webhook / マイグレーションは原則維持 |
| Q9 | テスト | **A** バックエンド API の Feature テストを整備 ※ユーザー「おすすめ」→ 決済・Webhook の信頼性が最重要 |
| 拡張 | Security Baseline | **B (No)** ※下記「拡張機能」参照 |
| 拡張 | Property-Based Testing | **C (No)** ※下記「拡張機能」参照 |

## 拡張機能（英語質問の日本語説明）

末尾2問は AI-DLC の「追加ルールを適用するか」の確認です。

### Security Baseline（セキュリティ基準の強制適用）
- **A) Yes**: セキュリティ関連の追加ルールをすべて必須チェックとして適用（本番向け）
- **B) No**: 追加ルールは適用しない（プロトタイプ・ポートフォリオ向け）
- **確定: B (No)** — 既に Webhook 署名検証・冪等性・CSRF 等を実装済み。今回の主目的は SPA 化であり、追加のブロッキングルールはスコープ外とする。

### Property-Based Testing（プロパティベーステストの強制適用）
- **A) Yes**: すべての PBT ルールを必須適用（ビジネスロジックが複雑な場合向け）
- **B) Partial**: 純粋関数・シリアライズのみ PBT 適用
- **C) No**: PBT ルールは適用しない（CRUD / UI 中心向け）
- **確定: C (No)** — 純粋関数が少なく、Feature テストで十分。過剰なテスト基盤は追加しない。

## Functional Requirements

### FR-1: SPA フロントエンド構築
- React + TypeScript + Vite で SPA を構築する。
- React Router で以下の画面をクライアントルーティングする:
  - `/login` — ログイン
  - `/payment` — 決済フォーム
  - `/payment/success` — 決済完了
  - `/orders` — 注文一覧
- Blade ビュー（`auth/login`, `payment/*`, `orders/index`）は React コンポーネントに置き換える。
- ルート `/` は認証状態に応じて `/payment` または `/login` へリダイレクトする。

### FR-2: JSON API 化（Laravel バックエンド）
- 既存の Web ルートを JSON API に再編する（`routes/api.php` 中心）。
- 必要な API エンドポイント（想定）:
  - `POST /api/login` — 認証
  - `POST /api/logout` — ログアウト
  - `GET /api/user` — 認証済みユーザー取得
  - `POST /api/payment/intent` — PaymentIntent 作成（`clientSecret` 返却）
  - `GET /api/orders` — 注文一覧（ページネーション）
  - `GET /api/config/stripe` — Stripe 公開キー取得（フロント初期化用）
- `POST /api/webhook/stripe` は現状維持（署名検証・イベント処理は変更しない）。

### FR-3: 認証（Sanctum SPA）
- Laravel Sanctum の SPA 認証（クッキー + CSRF）を採用する。
- フロントは API 呼び出し前に CSRF クッキーを取得し、リクエストに CSRF トークンを付与する。
- 未認証時は API が 401 を返し、フロントは `/login` へリダイレクトする。
- ログイン成功時のセッション再生成（固定化攻撃対策）は維持する。

### FR-4: 決済フロー（Stripe.js）
- 決済画面で Stripe.js（Card Element）を使用する。
- フローは現状と同じ:
  1. 金額入力 → `POST /api/payment/intent` で `clientSecret` 取得
  2. `stripe.confirmCardPayment(clientSecret)` で決済確定
  3. 成功時 `/payment/success` へ遷移
- カード情報はブラウザ → Stripe 直接送信（自社サーバーを経由しない）。

### FR-5: 注文一覧
- ログインユーザー本人の注文のみ表示する（現状踏襲）。
- ページネーション対応（20 件/頁）。
- ステータス表示: `pending` / `succeeded` / `failed` / `refunded` を日本語ラベルで表示。
- Webhook 反映後は **手動の再取得ボタン** で最新ステータスを取得する（ポーリング・WebSocket は導入しない）。

### FR-6: バックエンド中核の維持
- 以下は原則変更しない:
  - `PaymentService`（冪等性・状態遷移・`lockForUpdate()`）
  - `StripeWebhookController`（署名検証・イベント振り分け）
  - `orders` / `users` マイグレーション
  - Stripe 設定（`config/services.php` + `.env`）

### FR-7: テスト整備
- バックエンド API の Feature テストを追加する:
  - 認証（ログイン成功/失敗、未認証アクセス拒否）
  - PaymentIntent 作成（金額バリデーション、`orders` の pending 作成）
  - Webhook（署名検証失敗時 400、正常イベント時のステータス更新）
- フロントエンド（React コンポーネント）のテストは今回スコープ外。

## Non-Functional Requirements

### NFR-1: ポートフォリオ品質
- README・実装・ディレクトリ構成の整合性を維持する。
- `make setup`（または同等手順）直後に主要フローが再現できる状態を保つ。
- 余計なヘルパースクリプト・防御コード・自明なコメントは追加しない。

### NFR-2: セキュリティ
- Webhook 署名検証は必須（現状維持）。
- API キー・Webhook シークレットは `.env` 管理（コード直書き禁止）。
- Sanctum SPA 認証の CSRF 保護を正しく設定する。
- CORS は同一オリジン構成のため最小限（別オリジン分離は行わない）。

### NFR-3: パフォーマンス
- 画面遷移はクライアントサイドで即時応答（SPA）。
- 注文一覧はページネーションで大量データを避ける。

### NFR-4: 保守性
- フロントはコンポーネント単位で分割（Login / Payment / PaymentSuccess / OrderList / Layout）。
- API クライアントは共通モジュールに集約（CSRF 取得・エラーハンドリング）。

### NFR-5: 互換性
- Docker Compose 構成（app / web / db）は維持する。
- 既存の Stripe テストカード・Webhook CLI 手順は引き続き有効であること。

## Out of Scope（今回やらないこと）

- Inertia.js 導入
- フロントの完全分離（別リポジトリ / CORS ヘッドレス構成）
- Bearer トークン認証
- 注文ステータスのポーリング / WebSocket リアルタイム更新
- `Order` モデルのリレーション/キャスト整備（Q8=A のため）
- React コンポーネントのユニットテスト
- Property-Based Testing 導入
- AI-DLC Security Baseline 拡張ルールの追加適用

## Acceptance Criteria

1. `docker compose up` 後、ブラウザで `http://localhost` にアクセスすると React SPA が表示される。
2. `test@example.com` / `password123` でログインでき、決済フォーム・注文一覧に遷移できる。
3. テストカード `4242 4242 4242 4242` で決済成功し、Webhook 受信後に注文一覧の再取得で `succeeded` が確認できる。
4. 未ログインで保護画面にアクセスすると `/login` へリダイレクトされる。
5. Blade ビューは SPA 用の単一エントリ（`app.blade.php` 等）以外は不要になる。
6. 追加した Feature テストが `php artisan test` でパスする。

## Traceability

| 要件 ID | ユーザー回答 | 根拠 |
|---|---|---|
| FR-1〜2 | Q1=A, Q3=A, Q4=A, Q5=A | SPA + JSON API、全画面 React 化 |
| FR-3 | Q2=おすすめ→A | Sanctum SPA が Q1 と最も整合 |
| FR-4 | 現状踏襲 | 決済中核維持（Q8=A） |
| FR-5 | Q7=A | 手動再取得 |
| FR-6 | Q8=A | バックエンド中核維持 |
| FR-7 | Q9=おすすめ→A | 決済 API の信頼性優先 |
| NFR-2 拡張 | Security=No | 既存セキュリティ実装済み |
| FR-7 拡張 | PBT=No | Feature テストで十分 |
