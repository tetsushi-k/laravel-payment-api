# U3 payment-orders-api — Code Generation Plan



## Unit Context



| 項目 | 内容 |

|---|---|

| **Unit** | U3 payment-orders-api |

| **Stories** | US-004, US-005, US-007, US-008, US-009（API 部分） |

| **依存** | U1 auth-api |

| **想定コミット** | #4 `feat(api): 決済・注文 JSON API を追加` |



## 実装方針



- 既存 `PaymentController` / `OrderController`（Blade SSR）は U5 まで維持

- 新規 `PaymentApiController` / `OrderApiController` / `ConfigApiController` で JSON API を追加

- `PaymentService` / `StripeWebhookController` は触らない

- Feature テストは U5 に委譲。U3 完了条件は curl 検証



## Steps



- [x] Step 1: `PaymentApiController` — `POST /api/payment/intent`

- [x] Step 2: `OrderApiController` — `GET /api/orders`（`{ data, meta }` 形式）

- [x] Step 3: `ConfigApiController` — `GET /api/config/stripe`

- [x] Step 4: `routes/api.php` に `auth:sanctum` 付きルートを登録

- [x] Step 5: curl で intent / orders / stripe config / 401 を手動検証

- [x] Step 6: `aidlc-state.md` / `audit.md` 更新



## Story Traceability



| Story | 実装 |

|---|---|

| US-004 | `GET /api/config/stripe` — Stripe 公開キー取得 |

| US-005 | `POST /api/payment/intent` — PaymentIntent 作成 + orders pending 登録 |

| US-007 | `GET /api/orders` — 本人の注文のみ返却 |

| US-008 | `GET /api/orders` — 再取得可能な JSON 一覧 |

| US-009 | `GET /api/orders?page=` — 20 件/頁のページネーション meta |



## 変更ファイル



| 操作 | パス |

|---|---|

| 新規 | `src/app/Http/Controllers/PaymentApiController.php` |

| 新規 | `src/app/Http/Controllers/OrderApiController.php` |

| 新規 | `src/app/Http/Controllers/ConfigApiController.php` |

| 改修 | `src/routes/api.php` |
| 改修 | `src/bootstrap/app.php`（API 未認証時 401 返却） |

