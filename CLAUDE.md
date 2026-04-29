# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Dashboard MBG** is a Laravel 12 web application for monitoring nutrition (gizi) and production costs for the Makan Bergizi Gratis (MBG) program, managed by units called SPPG (Satuan Pelayanan Pemenuhan Gizi).

## Commands

### Development

```bash
# Start all dev services (Laravel server + queue + logs + Vite) concurrently
composer dev

# Or manually:
php artisan serve
npm run dev
```

### Build & Setup

```bash
# Full first-time setup
composer setup

# Build frontend assets
npm run build

# Lint PHP (Laravel Pint)
./vendor/bin/pint
```

### Database

```bash
php artisan migrate
php artisan db:seed          # Seeds admin, pengelola accounts + 845 TKPI items
php artisan migrate:fresh --seed
```

### Testing

```bash
composer test                # Clears config cache then runs PHPUnit
php artisan test             # Run all tests
php artisan test --filter=TestName  # Run a specific test
```

Tests use SQLite in-memory DB (configured in `phpunit.xml`).

## Architecture

### Auth & Roles

Authentication uses `laravel/ui` with a custom `LoginController`. Roles are stored directly on the `users` table (`role` column: `admin` or `pengelola`) — **not** via Spatie permissions (the package is installed but role checks are done with `$user->role === 'pengelola'`).

Each `pengelola` belongs to a specific `unit_sppg` (string). All data is scoped by `unit_sppg` — admins see all units, pengelola only see their own.

### Core Domain Models

- **`BahanPangan`** — TKPI food ingredient database (845+ items). Has nutritional fields per 100g: `energi`, `protein`, `lemak`, `karbohidrat`, `serat`, `kalsium`, `besi`, `vit_c`. Also has `bdd` (Berat Dapat Dimakan %) for waste adjustment.
- **`MenuHarian`** — Daily menu per SPPG unit. Status: `draft` (editable) or `final` (locked). Contains `jumlah_porsi` (portion count) and is linked to a `unit_sppg`.
- **`MenuDetailBahan`** — Line items of a menu: links `MenuHarian` → `BahanPangan` with `jumlah_gram` and `jumlah_porsi`.
- **`HargaBahan`** — Time-based pricing per ingredient per unit (`berlaku_mulai`/`berlaku_sampai`). `HargaBahan::hargaAktif($bahanId, $unit, $tanggal)` returns the active price.
- **`AnggaranPorsi`** — Time-based budget per portion per unit. `AnggaranPorsi::aktif($unit, $tanggal)` returns the active budget (defaults to 15000 if none set).

### Nutrition & Cost Calculation

Both calculations live on `MenuHarian` model methods:
- `totalGizi()` — Sums nutritional values across all `detailBahans`, applying BDD factor: `gram × (bdd/100) / 100 × jumlah_porsi`.
- `totalBiaya()` — Calculates cost using active prices from `HargaBahan`, returns cost per portion, budget comparison, and per-item breakdown.
- `statusAnggaran()` — Returns `'over'` (>100%), `'warning'` (≥85%), `'aman'`, or `'belum_ada_data'`.

AKG (nutritional targets) are constants in `app/Constants/AKG.php`:
```php
AKG::MAKAN_SIANG['energi']  // 667 kcal, etc.
```

### Frontend

- **Blade templates** with Bootstrap 5.3, Font Awesome 6.5, Chart.js 4.4
- Layout: `resources/views/layouts/app.blade.php` wraps all authenticated pages
- Sidebar (`resources/views/partials/sidebar.blade.php`) and navbar with budget alert badge
- AJAX via Fetch API for autocomplete (`GET /api/bahan-pangan/search?q=`) and simulation calculation (`POST /simulasi/kalkulasi`)
- Vite bundles `resources/sass/app.scss` + `resources/js/app.js`

### Key Internal APIs (web routes, auth-protected)

| Route | Purpose |
|---|---|
| `GET /api/bahan-pangan/search?q=&limit=` | Autocomplete for ingredient search |
| `POST /simulasi/kalkulasi` | Real-time nutrition + cost estimation (no DB write) |
| `POST /biaya/api/estimasi` | Cost estimation for biaya dashboard |
| `GET /gizi/api/trend` | Nutrition trend data for charts |

### Exports

- **Excel**: `rap2hpoutre/fast-excel` via `LaporanController::exportExcel`
- **PDF**: `barryvdh/laravel-dompdf` via `LaporanController::exportPdf`

### Environment

Key `.env` variables beyond standard Laravel:
- `UNIT_SPPG` — Default unit name used in some contexts
- `DB_CONNECTION` — defaults to `sqlite` in example; production uses MySQL
