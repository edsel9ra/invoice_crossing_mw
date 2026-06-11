# Invoice Crossing — Agent Guide

## Stack
Laravel 13 + Inertia.js (Vue 3) + Tailwind CSS 4 + Vite. PHP 8.4, MySQL 8.4, Node 22, pnpm.

## Quick start

```bash
docker compose up -d
```

- App: `http://localhost:8080`
- phpMyAdmin: `http://localhost:8081`
- ICG (external SQL Server) connection via `icg_sqlsrv` DB connection.

## Commands

All commands run inside the `app` container (or locally in `src/`).

| Purpose | Command |
|---|---|
| Run full test suite | `composer test` (runs `config:clear` then `php artisan test`) |
| List artisan commands | `php artisan list` |
| Run migrations | `php artisan migrate` |
| Run seeders | `php artisan db:seed --class=DatabaseSeeder` |
| Dev server stack | `composer dev` (server + queue + logs + Vite via concurrently) |

For single tests: `php artisan test tests/Unit/InvoiceCrossingServiceTest --filter=test_cross_creates_tickets_and_updates_client`.

## Project structure

- `app/Http/Controllers/` — 6 API controllers
- `app/Services/` — Core domain logic (InvoiceCrossingService orchestrates crossing)
- `app/Models/` — 7 Eloquent models
- `routes/api.php` — All API endpoints (prefixed `/api`)
- `routes/web.php` — Inertia page routes
- `database/migrations/` — 9 migrations
- `database/seeders/` — 3 seeders (BranchSeeder, ItemSeeder, DatabaseSeeder)
- `tests/Unit/` — Unit tests (InvoiceCrossingServiceTest uses Mockery)
- `tests/Feature/` — Feature tests (InvoiceCrossingTest tests full HTTP flow)

## Key architecture

- **ICG External DB**: `IcgInvoiceRepository` queries a remote SQL Server (`icg_sqlsrv` connection in `config/database.php`) for invoice line items. Requires `ICG_*` env vars.
- **Invoice crossing flow**: `POST /api/clients/{client}/cross` → validates series → fetches invoice items from ICG → matches against active Items → creates `InvoiceCrossing` + `InvoiceCrossingDetail` + `RaffleTicket` records — all inside a DB transaction with 3 retry attempts.
- **Branch resolution**: `InvoiceBranchResolver` maps a series number to a branch via `invoice_series_branches` table.
- **CamelCase API responses**: Models use `toApiArray()` to convert snake_case DB columns to camelCase JSON.
- **Client dedup**: `ClientController@store` uses `firstOrNew` on `doc_num` — creates new or updates existing.

## Testing quirks

- Tests use `RefreshDatabase` trait and **sqlite in-memory**.
- `IcgInvoiceRepository` must be mocked in tests (it hits external DB).
- Mockery is used directly (not wrappers). Call `Mockery::close()` in `tearDown()`.
- No factories exist for domain models (only `UserFactory`). Seed directly in tests.
- Feature tests hit full HTTP stack via `$this->postJson()` etc.

## Conventions

- `phpunit.xml` sets `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:` for testing.
- Vite HMR on port 5173, configured for Docker polling (`CHOKIDAR_USEPOLLING`, `WATCHPACK_POLLING`).
- Item code is uppercased/trimmed on save (`ItemController@store`).
- No TypeScript — plain Vue 3 with Composition API.
- `pint` for PHP CS fixer (no explicit config).
