# Requirements Clarification Questions — React SPA 化

`laravel-payment-api` を React SPA 化するにあたり、設計方針を確定するための質問です。
各質問の `[Answer]:` の後に **記号（A/B/C…）** を記入してください。該当がなければ最後の「Other」を選び、内容を記述してください。
すべて回答し終えたら「done」とお知らせください。

---

## Question 1
SPA の全体構成（フロントとバックエンドの結び方）はどれにしますか?

A) 真の SPA + JSON API（Laravel は API のみを提供、React は同一リポジトリ内で Vite ビルドし Laravel が配信）。`payment-api` の名前と整合し、API 設計力をアピールできる
B) Inertia.js + React（ルーティング/認証を Laravel 側に寄せたまま React 化。実装は軽量だが「API」色は薄まる）
C) フロントを完全分離（別ディレクトリ/別配信。CORS 前提のヘッドレス構成）
X) Other (please describe after [Answer]: tag below)

[Answer]: A

---

## Question 2
SPA の認証方式はどれにしますか?（現状はセッション認証）

A) Laravel Sanctum の SPA 認証（クッキー + CSRF、同一ドメイン前提。セッションの利点を維持しつつ SPA 対応）
B) Sanctum のトークン認証（Bearer トークンを localStorage 等で保持。完全分離・モバイル拡張に向く）
C) 現状のセッション認証を踏襲し、ログインだけ API 化
X) Other (please describe after [Answer]: tag below)

[Answer]: X ポートフォリオとしてみたときのおすすめで。→ 確定: A（Sanctum SPA 認証）

---

## Question 3
React 化する画面の範囲はどこまでにしますか?

A) 全画面（ログイン / 決済フォーム / 決済完了 / 注文一覧）を React 化
B) 認証後の画面（決済フォーム / 決済完了 / 注文一覧）のみ React 化し、ログインは Blade のまま
C) 決済フォームのみ React 化（最小スコープ）
X) Other (please describe after [Answer]: tag below)

[Answer]: A

---

## Question 4
フロントエンドの言語は TypeScript / JavaScript どちらにしますか?

A) TypeScript（型安全。ポートフォリオとして実務水準を示せる）
B) JavaScript（最小構成）
X) Other (please describe after [Answer]: tag below)

[Answer]: A

---

## Question 5
クライアントサイドのルーティング（複数画面の遷移）はどうしますか?

A) React Router を導入し、SPA 内でクライアントルーティング
B) 画面が少ないため自前の状態管理で画面切替（ライブラリ非依存）
X) Other (please describe after [Answer]: tag below)

[Answer]: A

---

## Question 6
スタイリング方針はどうしますか?（現状は Blade へのインライン CSS、依存に Tailwind CSS v4 あり）

A) Tailwind CSS（既に依存があるため活用。クラスベースで一貫性を確保）
B) CSS Modules / 通常の CSS ファイル
C) 現状のデザインを踏襲しつつ React コンポーネント内に集約（CSS-in-JS など）
X) Other (please describe after [Answer]: tag below)

[Answer]: X おすすめで。→ 確定: A（Tailwind CSS）

---

## Question 7
注文一覧のステータス更新（現状は「再読み込みで反映」）について、SPA 化で挙動を変えますか?

A) 現状踏襲（手動更新ボタン / 再取得）。スコープを広げない
B) ポーリングで自動更新（数秒間隔で再フェッチ）
C) リアルタイム更新（WebSocket / Laravel Echo 等。スコープ大）
X) Other (please describe after [Answer]: tag below)

[Answer]: A


---

## Question 8
バックエンドの決済中核（`PaymentService` / `StripeWebhookController` / 冪等性・署名検証・マイグレーション）の扱いは?

A) 原則維持し、Web ルートの JSON API 化と認証方式の変更のみ行う（影響を最小化）
B) この機会に Order モデルのリレーション/キャスト整備や軽微なリファクタも含める
X) Other (please describe after [Answer]: tag below)

[Answer]: A

---

## Question 9
テストの整備方針はどうしますか?（現状は雛形テストのみ）

A) バックエンド API の Feature テスト（認証・決済 Intent・Webhook）を整備する
B) フロント（React コンポーネント）のテストも含める
C) 両方整備する
D) 今回はテストを追加しない（実装のみ）
X) Other (please describe after [Answer]: tag below)

[Answer]: おすすめで → 確定: A（バックエンド API Feature テスト）

---

## Question 10: Security Extensions（セキュリティ追加ルールを適用するか）
このプロジェクトにセキュリティ関連の追加ルールを必須チェックとして適用しますか?

A) Yes — セキュリティルールをすべて必須適用（本番向けアプリ向け）
B) No — セキュリティ追加ルールは適用しない（プロトタイプ・ポートフォリオ向け）
X) Other (please describe after [Answer]: tag below)

[Answer]: B（AI 推奨。既に Webhook 署名検証等を実装済みのため）

---

## Question 11: Property-Based Testing Extension（プロパティベーステストを適用するか）
プロパティベーステスト（PBT）の追加ルールを適用しますか?

A) Yes — PBT ルールをすべて必須適用（ビジネスロジックが複雑な場合向け）
B) Partial — 純粋関数・シリアライズのみ PBT 適用
C) No — PBT ルールは適用しない（CRUD / UI 中心向け）
X) Other (please describe after [Answer]: tag below)

[Answer]: C（AI 推奨。Feature テストで十分）
