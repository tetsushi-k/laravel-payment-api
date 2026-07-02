# AGENTS.md

## Cursor Cloud specific instructions

Laravel 13 + React SPA (Stripe payments) app. Everything runs through Docker Compose:
`app` (php:8.4-fpm), `web` (nginx, exposes `http://localhost` on port 80), `db` (mysql:8.0).
Standard commands live in the `Makefile` (`make help`) and `README.md` (§⑤/⑥) — use those; only the non-obvious caveats are captured below.

### Starting services
- Docker is already installed in the VM image. On a fresh boot the daemon may not be running — start it (idempotent) before any `docker`/`make` command: `sudo service docker start`.
- The daemon is configured for this VM with `storage-driver: fuse-overlayfs` and `features.containerd-snapshotter: false` in `/etc/docker/daemon.json` (both required for Docker 29 here); iptables is set to `iptables-legacy`. Do not remove these.
- Start the stack: `make up` (= `docker compose up -d --build`). First-time full bootstrap: `make setup` (containers + composer install + key:generate + migrate + seed, then host-side `npm run build`).
- `make setup`/`make build` run the frontend build on the **host** (Node is pre-installed); all backend/artisan/composer commands run **inside the `app` container** (the host has no PHP/Composer).

### Layout gotchas
- The Laravel app lives in `src/`, bind-mounted to `/var/www` in the container. `src/vendor/` and `src/node_modules/` are on the host.
- Env file is `src/.env` (copied from `src/.env.example` by `make setup`). After editing `src/composer.json`, run `docker compose exec app composer install`.

### Login / session gotcha (not a bug)
- Seeded login: `test@example.com` / `password123`.
- `POST /api/login` returns HTTP 500 `"Session store not set on request."` for requests whose `Origin`/`Referer` do NOT match `SANCTUM_STATEFUL_DOMAINS` (`localhost`). Sanctum's stateful middleware (`statefulApi()`) only starts a session for SPA-origin requests. Test login through the browser, or with curl: `GET /sanctum/csrf-cookie` first, then send `Origin`, `Referer`, and `X-XSRF-TOKEN` headers with a shared cookie jar.

### Tests & lint
- Tests: `make test` (= `docker compose exec app php artisan test`). Uses in-memory SQLite and mocks Stripe, so **no external secrets are required**.
- Lint: `docker compose exec app ./vendor/bin/pint --test` (the repo currently has a few pre-existing style findings unrelated to setup).

### Stripe (optional for full payment flow)
- Login, orders list, and all UI work without Stripe keys. Creating a real PaymentIntent and receiving webhooks require valid Stripe test keys in `src/.env` (`STRIPE_SECRET_KEY`, `STRIPE_PUBLIC_KEY`, `STRIPE_WEBHOOK_SECRET`) plus the Stripe CLI (`stripe listen --forward-to localhost/api/webhook/stripe`).
