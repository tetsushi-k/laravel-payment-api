# =============================================================
# PHP 8.4 Dockerfile
# Laravel 13.x の実行環境を構築する
# ベースイメージに php:8.4-fpm を使用（FastCGI Process Manager）
# Nginx と PHP-FPM を連携させる構成
# インストールされた Laravel 13.x は symfony/console 経由で PHP 8.4 以上が必要
# =============================================================

FROM php:8.4-fpm

# -----------------------------------------------------------
# システムパッケージのインストール
# - git: Composerが依存関係をgitから取得する際に必要
# - curl: HTTPリクエストのデバッグ用
# - libpng-dev / libjpeg-dev / libfreetype6-dev: GD拡張（画像処理）用
# - libonig-dev: mbstring拡張（マルチバイト文字列処理）用
# - libxml2-dev: xml / soap 拡張用
# - zip / unzip: Composerのパッケージ展開に必要
# -----------------------------------------------------------
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# -----------------------------------------------------------
# PHP拡張のインストール
# - pdo_mysql: LaravelからMySQLに接続するために必須
# - mbstring: 日本語などマルチバイト文字列の処理に必要
# - exif: 画像のメタ情報処理（オプション）
# - bcmath: 高精度な数値計算（金額計算で使用する可能性あり）
# - gd: 画像処理ライブラリ
# -----------------------------------------------------------
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd

# -----------------------------------------------------------
# Composerのインストール
# 公式イメージからバイナリをコピーする方法（推奨）
# -----------------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# -----------------------------------------------------------
# 作業ディレクトリの設定
# Laravelプロジェクトは /var/www に配置する
# -----------------------------------------------------------
WORKDIR /var/www

# -----------------------------------------------------------
# セキュリティ注意:
# 本番環境では composer install --no-dev を使用し、
# 開発用パッケージを含めないこと
# -----------------------------------------------------------
