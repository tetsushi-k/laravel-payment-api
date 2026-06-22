# User Stories — React SPA 化

**分割方式**: User Journey-Based（Epic 単位でグループ化）  
**受け入れ基準形式**: Given / When / Then（標準）  
**要件トレース**: `aidlc-docs/inception/requirements/requirements.md`

---

## Epic E-1: 認証（Authentication Journey）

### US-001: ログイン

**As a** テストユーザー（P-1）  
**I want to** メールアドレスとパスワードでログインする  
**So that** 決済画面や注文一覧にアクセスできる

**受け入れ基準**
- **Given** 未ログインで `/login` にいる  
  **When** 正しい認証情報を送信する  
  **Then** 認証が成功し `/orders` または意図した保護画面へ遷移する

- **Given** 未ログインで `/login` にいる  
  **When** 誤った認証情報を送信する  
  **Then** エラーメッセージが表示され、ログイン画面に留まる

- **Given** ログイン処理が成功した  
  **When** セッションが確立される  
  **Then** セッション固定化対策（`session()->regenerate()`）がサーバー側で行われる

**要件**: FR-3, NFR-2  
**想定コミット**: #1（auth API）, #3（login SPA）

---

### US-002: ログアウト

**As a** テストユーザー（P-1）  
**I want to** ログアウトする  
**So that** セッションを終了し他人にアカウントを使われない

**受け入れ基準**
- **Given** ログイン済みで注文一覧等にいる  
  **When** ログアウトを実行する  
  **Then** セッションが破棄され `/login` へ遷移する

- **Given** ログアウト直後  
  **When** 保護された API や画面にアクセスする  
  **Then** 401 または `/login` へのリダイレクトとなる

**要件**: FR-3  
**想定コミット**: #1, #3

---

### US-003: 未認証アクセスの保護

**As a** テストユーザー（P-1）  
**I want to** 未ログイン時に保護画面へアクセスできない  
**So that** 決済や注文情報が他人に見えない

**受け入れ基準**
- **Given** 未ログイン  
  **When** `/payment` や `/orders` に直接アクセスする  
  **Then** `/login` へリダイレクトされる

- **Given** 未ログイン  
  **When** 保護 API（例: `POST /api/payment/intent`）を呼ぶ  
  **Then** 401 Unauthorized が返る

- **Given** ログイン済み  
  **When** 保護画面にアクセスする  
  **Then** 正常に画面が表示される

**要件**: FR-3, NFR-2  
**想定コミット**: #3

---

## Epic E-2: 決済（Payment Journey）

### US-004: 決済フォームの表示

**As a** テストユーザー（P-1）  
**I want to** 決済フォームで金額とカード情報を入力する  
**So that** テスト決済を行える

**受け入れ基準**
- **Given** ログイン済み  
  **When** `/payment` にアクセスする  
  **Then** 金額入力欄と Stripe Card Element が表示される

- **Given** 決済画面が読み込まれた  
  **When** Stripe 公開キーが必要になる  
  **Then** API から取得したキーで Stripe.js が初期化される

- **Given** 決済画面  
  **When** テスト用カード情報の案内を見る  
  **Then** `4242...` 等のテスト番号が確認できる（UI または README と整合）

**要件**: FR-1, FR-4, FR-2  
**想定コミット**: #4, #5

---

### US-005: カード決済の実行

**As a** テストユーザー（P-1）  
**I want to** 入力した金額でカード決済を完了する  
**So that** 注文が `pending` で作成され、Stripe で決済が確定する

**受け入れ基準**
- **Given** ログイン済みで決済フォームに金額（100〜1,000,000 円）を入力している  
  **When** 「支払う」を押す  
  **Then** `POST /api/payment/intent` が呼ばれ `clientSecret` が返る

- **Given** `clientSecret` を取得した  
  **When** Stripe.js で `confirmCardPayment` を実行する  
  **Then** カード情報は Stripe に直接送信され、自社サーバーを経由しない

- **Given** PaymentIntent 作成が成功した  
  **When** サーバーが DB に記録する  
  **Then** `orders` に `status=pending` と `stripe_payment_intent_id` が保存される

- **Given** テストカード `4242 4242 4242 4242` を使用  
  **When** 決済が Stripe 側で成功する  
  **Then** フロントは `/payment/success` へ遷移する

- **Given** 決済失敗カードを使用  
  **When** Stripe がエラーを返す  
  **Then** 画面上にエラーメッセージが表示され、再試行できる

**要件**: FR-4, FR-2, FR-6  
**想定コミット**: #4, #5  
**関連ペルソナ**: P-2（Stripe API）

---

### US-006: 決済完了画面

**As a** テストユーザー（P-1）  
**I want to** 決済成功後に完了画面を見る  
**So that** 決済が受理されたことを確認できる

**受け入れ基準**
- **Given** Stripe.js で決済が成功した  
  **When** フロントが遷移する  
  **Then** `/payment/success` に完了メッセージが表示される

- **Given** 完了画面  
  **When** 注文一覧への導線を探す  
  **Then** `/orders` へ遷移できるリンクまたはボタンがある

**要件**: FR-1  
**想定コミット**: #5

---

## Epic E-3: 注文確認（Order Journey）

### US-007: 注文一覧の表示

**As a** テストユーザー（P-1）  
**I want to** 自分の注文一覧を見る  
**So that** 決済履歴とステータスを確認できる

**受け入れ基準**
- **Given** ログイン済み  
  **When** `/orders` にアクセスする  
  **Then** 本人の注文のみが一覧表示される（他ユーザー・ゲスト注文は含まない）

- **Given** 注文が存在する  
  **When** 一覧を表示する  
  **Then** ID・金額・ステータス（日本語ラベル）・PaymentIntent ID・作成日時が見える

- **Given** 注文が 0 件  
  **When** 一覧を表示する  
  **Then** 空状態メッセージと決済フォームへの導線が表示される

**要件**: FR-5  
**想定コミット**: #4, #5

---

### US-008: 注文ステータスの手動更新

**As a** テストユーザー（P-1）  
**I want to** 注文一覧を手動で再取得する  
**So that** Webhook 反映後の最新ステータスを確認できる

**受け入れ基準**
- **Given** 決済直後で Webhook 未反映（`pending`）  
  **When** 再取得ボタンを押す  
  **Then** `GET /api/orders` が呼ばれ一覧が更新される

- **Given** Webhook で `succeeded` に更新済み  
  **When** 再取得する  
  **Then** ステータスが「成功」と表示される

- **Given** 注文一覧画面  
  **When** 自動ポーリングや WebSocket は使わない  
  **Then** 更新はユーザーの明示操作（再取得ボタン）のみで行われる

**要件**: FR-5, Q7=A  
**想定コミット**: #5

---

### US-009: 注文一覧のページネーション

**As a** テストユーザー（P-1）  
**I want to** 注文が多い場合にページを切り替える  
**So that** 一覧が読みやすく保たれる

**受け入れ基準**
- **Given** 21 件以上の注文がある  
  **When** 一覧を表示する  
  **Then** 20 件/頁でページネーションが機能する

- **Given** 2 頁目以降  
  **When** ページを切り替える  
  **Then** 正しい範囲の注文が表示される

**要件**: FR-5, NFR-3  
**想定コミット**: #5

---

## Epic E-4: システム連携（Webhook Journey）

### US-010: Webhook による注文ステータス更新

**As** Stripe（P-2）  
**I want to** 決済結果を Webhook でサーバーに通知する  
**So that** 注文ステータスが非同期かつ冪等に確定する

**受け入れ基準**
- **Given** 有効な `Stripe-Signature` 付きリクエスト  
  **When** `payment_intent.succeeded` が届く  
  **Then** 対象注文が `pending` から `succeeded` に更新される

- **Given** 同一イベントが再送される  
  **When** Webhook が再度処理される  
  **Then** 二重更新されず冪等に無視される

- **Given** 署名が無効または欠落  
  **When** Webhook が届く  
  **Then** 400 が返り、ステータスは変更されない

- **Given** `payment_intent.payment_failed` または `charge.refunded`  
  **When** Webhook が届く  
  **Then** それぞれ `failed` / `refunded` に遷移する（既存 `PaymentService` ロジック踏襲）

**要件**: FR-2（webhook 維持）, FR-6, NFR-2  
**想定コミット**: 変更なし（既存維持）+ #7（Feature テスト）

---

## Epic E-5: SPA 基盤

### US-011: ルートと SPA シェル

**As a** テストユーザー（P-1）  
**I want to** ブラウザで SPA としてアプリを利用する  
**So that** 画面遷移が速く、単一ページ体験になる

**受け入れ基準**
- **Given** `http://localhost` にアクセスする  
  **When** アプリが起動する  
  **Then** React SPA が表示され、Blade の個別画面は使われない（`app.blade.php` エントリのみ）

- **Given** 未ログインで `/` にアクセスする  
  **When** ルートが解決される  
  **Then** `/login` へリダイレクトされる

- **Given** ログイン済みで `/` にアクセスする  
  **When** ルートが解決される  
  **Then** `/payment` へリダイレクトされる

- **Given** 存在しないクライアントルート  
  **When** 直接アクセスする  
  **Then** 404 または適切なフォールバックが表示される

**要件**: FR-1, NFR-3, NFR-4  
**想定コミット**: #2, #6

---

## Story Summary

| Epic | Story ID | タイトル | Persona |
|---|---|---|---|
| E-1 認証 | US-001 | ログイン | P-1 |
| E-1 認証 | US-002 | ログアウト | P-1 |
| E-1 認証 | US-003 | 未認証アクセスの保護 | P-1 |
| E-2 決済 | US-004 | 決済フォームの表示 | P-1 |
| E-2 決済 | US-005 | カード決済の実行 | P-1, P-2 |
| E-2 決済 | US-006 | 決済完了画面 | P-1 |
| E-3 注文 | US-007 | 注文一覧の表示 | P-1 |
| E-3 注文 | US-008 | 手動再取得 | P-1 |
| E-3 注文 | US-009 | ページネーション | P-1 |
| E-4 Webhook | US-010 | Webhook ステータス更新 | P-2 |
| E-5 SPA | US-011 | ルートと SPA シェル | P-1 |

**合計**: 5 Epic / 11 User Stories

## INVEST チェック（要約）

| 原則 | 対応 |
|---|---|
| Independent | Epic 単位で独立実装可能（認証 → 決済 API → 決済 UI の順） |
| Negotiable | 受け入れ基準は実装詳細（コンポーネント名等）に依存しない |
| Valuable | 各ストーリーがユーザーまたは Stripe 連携の価値を持つ |
| Estimable | 画面・API 単位で見積もり可能 |
| Small | 1 ストーリー = 1 画面または 1 API 群 |
| Testable | Given/When/Then で Feature テスト・手動確認に落とせる |
