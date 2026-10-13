# Laravel Catalog Gateway

A Laravel 11 API that sits between a shop catalog and a merchant / marketplace channel.

It accepts product upserts, queues bounded catalog pushes, verifies signed webhooks, and optionally enriches copy through an OpenAI-compatible provider with a strict JSON schema.

Built by **SC EMPIRE GLOBAL SOLUTIONS SRL** (EMPIREX Tech). This is an original demonstration application. It is not a client ERP, not EMPIRE Synapse, and not a live Merchant Center account. The shape of the work — incremental feed pushes, circuit breaking, HMAC webhooks, structured AI output — comes from catalog-sync and automation work reimplemented as a clean API.

## Problem

Shops push tens of thousands of SKUs to Google Merchant Center, comparison engines, or marketplaces. Doing that inside a request is how catalogs deadlock. Doing AI enrichment without a schema is how garbage lands on product pages.

The gateway keeps HTTP fast: validate, persist, dispatch. Workers do the slow part.

## Architecture

```
Shop / admin ── Sanctum ── ProductController
                              │
                              ├─ ProductNormalizer (SKU, GTIN, integer money)
                              ├─ EnrichProductJob ── CatalogEnricher ── AiProvider
                              └─ PushCatalogBatch ── CircuitBreaker ── MerchantChannel

Marketplace ── HMAC webhook ── WebhookController
```

| Piece | Role |
| --- | --- |
| `ProductNormalizer` | Store-agnostic catalog record. |
| `PushCatalogBatch` | Unique queued job, batch limit, sync-run audit row. |
| `CircuitBreaker` | Cache-backed pause when the channel fails. |
| `CatalogEnricher` | Rejects AI payloads that miss title/bullets/terms. |
| `VerifyWebhookSignature` | `sha256=` HMAC of the raw body. |

## Requirements

- PHP 8.3+
- Composer
- SQLite (default) or MySQL

## Installation

```bash
git clone https://github.com/empirextech/laravel-catalog-gateway.git
cd laravel-catalog-gateway
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

The seeder prints a Sanctum token for `operator@example.com`.

## API

All `/api/v1/*` routes require `Authorization: Bearer <token>`.

```http
POST /api/v1/products
{
  "sku": "PIPE-15",
  "title": "Copper pipe 15mm",
  "price": 19.90,
  "stock": 4,
  "gtin": "5901234123457"
}
```

```http
POST /api/v1/products/{id}/enrich
POST /api/v1/sync/merchant
GET  /api/v1/sync/{id}
```

Webhook (no Sanctum; signature instead):

```http
POST /api/webhooks/merchant
X-Signature: sha256=<hmac>
```

```bash
BODY='{"event":"offer.updated","sku":"PIPE-15"}'
SIG=$(php -r "echo 'sha256='.hash_hmac('sha256', file_get_contents('php://stdin'), getenv('WEBHOOK_SECRET'));" <<< "$BODY")
curl -s -X POST http://localhost:8000/api/webhooks/merchant \
  -H "Content-Type: application/json" \
  -H "X-Signature: $SIG" \
  -d "$BODY"
```

CLI:

```bash
php artisan catalog:sync --limit=100
```

## AI

Default `AI_FAKE=true` uses `FakeProvider` so tests and local demos never call a network.

To use an OpenAI-compatible endpoint:

```
AI_FAKE=false
AI_ENDPOINT=https://api.openai.com/v1/chat/completions
AI_KEY=sk-...
AI_MODEL=gpt-4o-mini
```

The HTTP client times out at 15 seconds, retries twice, and never logs the key. The enricher still rejects malformed JSON.

## Tests

```bash
php artisan test
```

Queue connection is `sync` in PHPUnit so jobs run in-process.

## Security

- Sanctum tokens for human/API operators.
- Webhooks fail closed without a valid HMAC.
- `.env` is gitignored. `.env.example` has no secrets.
- Integer money (`price_minor`) avoids float drift in feeds.

## License

MIT. EMPIREX Tech / SC EMPIRE GLOBAL SOLUTIONS SRL.
