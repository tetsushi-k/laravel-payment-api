# Technology Stack

## Programming Languages
- PHP - 8.4（composer は `^8.3`）- バックエンド全般。
- JavaScript - ES Modules - 決済画面のインライン Stripe.js / Vite エントリ。
- Blade - Laravel テンプレート - 現行フロントエンド（SSR）。

## Frameworks
- Laravel - ^13.0 - アプリケーションフレームワーク。
- Laravel Sanctum - ^4.0 - API トークン認証（現状サンプルのみ）。
- Tailwind CSS - ^4.0 - スタイリング（Vite プラグイン経由、Blade では一部インライン CSS）。

## Infrastructure
- Docker / Docker Compose - app / web / db のコンテナオーケストレーション。
- Nginx - 1.25 - Web サーバー / リバースプロキシ。
- MySQL - 8.0 - リレーショナルデータベース。

## Build Tools
- Composer - PHP 依存管理・スクリプト（setup/dev/test）。
- Vite - ^8.0 - フロントエンドビルド。
- laravel-vite-plugin - ^3.0 - Laravel と Vite の統合。
- concurrently - ^9.0 - 開発時の複数プロセス同時起動。

## Testing Tools
- PHPUnit - ^12.5 - テストランナー（現状は雛形テストのみ）。
- Mockery - ^1.6 - モック。
- fakerphp/faker - ^1.23 - テストデータ生成。
- nunomaduro/collision - ^8.6 - CLI エラー表示。

## External Services
- Stripe（stripe/stripe-php ^20.1）- 決済 / Webhook。
- Stripe.js（CDN）- ブラウザでのカード決済確定。

## Dev Tooling
- Laravel Pint - ^1.27 - コードスタイル整形。
- Laravel Pail - ^1.2 - ログ閲覧。
- Laravel Tinker - ^3.0 - REPL。
