# Component Inventory

## Application Packages
- `laravel-payment-api/src` (Laravel 13 モノリス) - 認証・決済・Webhook・注文照会を提供する単一アプリケーション。

## Infrastructure Packages
- `docker/nginx` (Docker) - Nginx リバースプロキシ設定（`default.conf`）。
- `docker/php` (Docker) - PHP-FPM / Laravel 実行用 Dockerfile。
- `docker-compose.yml` (Docker Compose) - app / web / db の 3 サービス定義。
- IaC（Terraform / CloudFormation / CDK）: なし。

## Shared Packages
- なし（単一アプリ内で完結。共有ライブラリ・クライアント分割なし）。

## Test Packages
- `src/tests/Feature` (Feature) - `ExampleTest`（雛形のみ、業務テストなし）。
- `src/tests/Unit` (Unit) - `ExampleTest`（雛形のみ）。

## Logical Components（アプリ内）
- Controllers: `Auth` / `Payment` / `Order` / `StripeWebhook` / ベース `Controller`。
- Services: `PaymentService`。
- Models: `User` / `Order`。
- Providers: `AppServiceProvider`。
- Views: `auth/login`, `payment/index`, `payment/success`, `orders/index`, `welcome`。

## Total Count
- **Total Packages**: 1 アプリケーション + 3 インフラ定義
- **Application**: 1
- **Infrastructure**: 3（nginx / php / compose）
- **Shared**: 0
- **Test**: 2（Feature / Unit、いずれも雛形）
