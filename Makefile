# =============================================================
# Makefile - Laravel Payment API
# Usage: make <target>
# =============================================================

.PHONY: help setup up down restart bash migrate seed fresh build test logs ps

help:
	@echo ""
	@echo "Laravel Payment API - Available commands"
	@echo "=================================================="
	@echo "  make setup    First-time setup (install, key:generate, migrate, seed, frontend build)"
	@echo "  make up       Start containers"
	@echo "  make down     Stop containers"
	@echo "  make restart  Restart containers"
	@echo "  make bash     Open shell in app container"
	@echo "  make migrate  Run migrations"
	@echo "  make seed     Seed test data"
	@echo "  make fresh    Drop all tables and re-migrate with seed data"
	@echo "  make build    Build frontend assets (npm install & build)"
	@echo "  make test     Run PHPUnit tests"
	@echo "  make logs     Tail container logs"
	@echo "  make ps       Show container status"
	@echo ""

# First-time setup: up -> .env -> composer install -> key:generate -> migrate -> seed -> frontend build
# Note: フロントビルドはホスト側で実行するため Node.js が必要。
setup: up
	@echo "=== copy .env ==="
	docker compose exec app cp -n .env.example .env || true
	@echo "=== composer install ==="
	docker compose exec app composer install
	@echo "=== generate application key ==="
	docker compose exec app php artisan key:generate
	@echo "=== run migrations ==="
	docker compose exec app php artisan migrate --force
	@echo "=== seed test data ==="
	docker compose exec app php artisan db:seed --force
	@echo "=== build frontend ==="
	cd src && npm install && npm run build
	@echo ""
	@echo "=== setup complete ==="
	@echo "Open http://localhost and log in with test@example.com / password123"

up:
	docker compose up -d --build
	@echo "Containers started."

down:
	docker compose down

restart: down up

bash:
	docker compose exec app bash

migrate:
	docker compose exec app php artisan migrate

seed:
	docker compose exec app php artisan db:seed

# 全テーブルを削除して再作成し、テストデータを投入する（既存データは消える）
fresh:
	docker compose exec app php artisan migrate:fresh --seed

build:
	cd src && npm install && npm run build

test:
	docker compose exec app php artisan test

logs:
	docker compose logs -f

ps:
	docker compose ps
