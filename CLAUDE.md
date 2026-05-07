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
php artisan db:seed          # Seeds 4 role accounts + 845 TKPI items
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

Authentication uses `laravel/ui` with a custom `LoginController`. Roles are stored on the `users` table (`role` column) with 4 values defined as constants on `User`:

| Constant | Value | Akses |
|---|---|---|
| `User::ROLE_SUPERADMIN` | `superadmin` | Manajemen akun pengguna saja (`/users`) |
| `User::ROLE_KETUA_SPPG` | `ketua_sppg` | Akses operasional penuh |
| `User::ROLE_AHLI_GIZI` | `ahli_gizi` | Input menu harian, simulasi, monitoring gizi, laporan |
| `User::ROLE_AKUNTAN` | `akuntan` | Harga bahan, monitoring biaya, budget alert, laporan |

Always use `User::ROLE_*` constants or helper methods (`isKetuaSppg()`, `isAhliGizi()`, `isAkuntan()`, `isSuperAdmin()`, `isOperational()`) — never hardcode role strings.

**Seeder accounts** (password: `password123`):
- `superadmin@mbg.id` → superadmin
- `ketua@mbg.id` → ketua_sppg
- `gizi@mbg.id` → ahli_gizi
- `akuntan@mbg.id` → akuntan

**Route protection** via `RoleMiddleware` (`app/Http/Middleware/RoleMiddleware.php`), aliased as `role` in `bootstrap/app.php`. Superadmin is redirected to `users.index` after login. Operasional roles (`ketua_sppg`, `ahli_gizi`, `akuntan`) access their respective routes as grouped in `routes/web.php`.

Each operasional user belongs to a `unit_sppg` (string). All data is scoped by `unit_sppg`. Superadmin does not have a unit.

### Core Domain Models

- **`BahanPangan`** — TKPI food ingredient database (845+ items). Has nutritional fields per 100g: `energi`, `protein`, `lemak`, `karbohidrat`, `serat`, `kalsium`, `besi`, `vit_c`. Also has `bdd` (Berat Dapat Dimakan %) for waste adjustment.
- **`MenuHarian`** — Daily menu per SPPG unit. Status: `draft` (editable) or `final` (locked). Contains `jumlah_porsi` (portion count) and is linked to a `unit_sppg`.
- **`MenuDetailBahan`** — Line items of a menu: links `MenuHarian` → `BahanPangan` with `jumlah_gram` and `jumlah_porsi`.
- **`HargaBahan`** — Time-based pricing per ingredient (`berlaku_mulai`/`berlaku_sampai`). `HargaBahan::hargaAktif(int $bahanId, ?string $tanggal = null)` returns the active price (defaults to 0 if none set).
- **`AnggaranPorsi`** — Time-based budget per portion per kelompok. `AnggaranPorsi::aktif(?string $tanggal = null, ?string $kelompok = null)` returns the active budget (defaults to 15000 if none set).

### Nutrition & Cost Calculation

Both calculations live on `MenuHarian` model methods:
- `totalGizi()` — Sums nutritional values across all `detailBahans`, applying BDD factor: `gram × (bdd/100) / 100 × jumlah_porsi`.
- `totalBiaya()` — Calculates cost using active prices from `HargaBahan`, returns cost per portion, budget comparison, and per-item breakdown.
- `statusAnggaran()` — Returns `'over'` (>100%), `'warning'` (≥85%), `'aman'`, or `'belum_ada_data'`.

AKG (nutritional targets) are constants in `app/Constants/AKG.php`:
```php
AKG::MAKAN_SIANG['energi']  // 667 kcal, etc.
```

### Dashboard

`DashboardController::index` serves a single unified dashboard for all operasional roles. The view (`resources/views/dashboard/index.blade.php`) conditionally renders sections based on `$role` (`ahli_gizi`, `akuntan`, `ketua_sppg`). `GiziController::dashboard` now just redirects to `dashboard` — there is no separate gizi dashboard view.

### Frontend

- **Blade templates** with Bootstrap 5.3, Font Awesome 6.5, Chart.js 4.4
- Layout: `resources/views/layouts/app.blade.php` wraps all authenticated pages
- Sidebar (`resources/views/partials/sidebar.blade.php`) renders role-specific navigation using `Auth::user()->role`
- AJAX via Fetch API for autocomplete (`GET /api/bahan-pangan/search?q=`) and simulation calculation (`POST /simulasi/kalkulasi`)
- Vite bundles `resources/sass/app.scss` + `resources/js/app.js`

### Route Access Summary

| Route prefix | Roles |
|---|---|
| `/users` | superadmin only |
| `/dashboard` | ketua_sppg, ahli_gizi, akuntan |
| `/bahan-pangan` (view) | ketua_sppg, ahli_gizi, akuntan |
| `/bahan-pangan` (CRUD) | ketua_sppg only |
| `/menu-harian` (view) | ketua_sppg, ahli_gizi, akuntan |
| `/menu-harian` (CRUD + finalize) | ahli_gizi only |
| `/simulasi` | ahli_gizi only |
| `/gizi` | ketua_sppg, ahli_gizi |
| `/biaya` + `/biaya/harga` | ketua_sppg, akuntan |
| `/anggaran` | ketua_sppg only |
| `/import-tkpi` | ketua_sppg only |
| `/budget-alert` | ketua_sppg, akuntan |
| `/laporan` | ketua_sppg, ahli_gizi, akuntan |

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
