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

Always use `User::ROLE_*` constants or helper methods (`isKetuaSppg()`, `isAhliGizi()`, `isAkuntan()`, `isSuperAdmin()`, `isOperational()`) — never hardcode role strings. Also available: `hasRole($role)`, `hasAnyRole(array $roles)`, `User::roleLabel()`, `User::allRoles()`.

**Seeder accounts** (password: `password123`):
- `superadmin@mbg.id` → superadmin
- `ketua@mbg.id` → ketua_sppg
- `gizi@mbg.id` → ahli_gizi
- `akuntan@mbg.id` → akuntan

**Route protection** via `RoleMiddleware` (`app/Http/Middleware/RoleMiddleware.php`), aliased as `role` in `bootstrap/app.php`. Superadmin is redirected to `users.index` after login (not dashboard). Operasional roles (`ketua_sppg`, `ahli_gizi`, `akuntan`) access their respective routes as grouped in `routes/web.php`.

Each operasional user belongs to a `unit_sppg` (string). All data is intended to be scoped by `unit_sppg`, but currently the system assumes a single SPPG unit (no active unit filtering in most queries). Superadmin does not have a unit.

### Core Domain Models

- **`BahanPangan`** — TKPI food ingredient database (845+ items). Key fields: `kode` (unique, max 10), `kode_lama`, `nama_bahan`, `kategori`, `sub_kategori`, `sumber`, `is_active`. Nutritional fields per 100g BDD: `bdd` (Berat Dapat Dimakan %), `air`, `energi`, `protein`, `lemak`, `karbohidrat`, `serat`, `abu`, `kalsium`, `fosfor`, `besi`, `natrium`, `kalium`, `tembaga`, `seng`, `retinol`, `b_karoten`, `kar_total`, `thiamin`, `riboflavin`, `niasin`, `vit_c` (all decimal, nullable). Scopes: `cari($keyword)`, `Kategori($kategori)`.

- **`MenuHarian`** — Daily menu per SPPG unit. Fields: `tanggal`, `user_id`, `nama_menu`, `status` (enum: `draft`|`final`), `kelompok` (enum: `balita_sd3`|`sd4_ibu_menyusui`, default `sd4_ibu_menyusui`), `kelompok_sasaran` (string, default `SD_4_6` — one of 12 AKG kelompok keys), `jumlah_porsi`, `catatan`, `anggaran_per_porsi`, `catatan_anggaran`. Unique constraint: `(tanggal, kelompok_sasaran)` — one menu per day per kelompok_sasaran. Status `draft` = editable; `final` = locked (via `PATCH /menu-harian/{id}/finalize`). Accessor: `getLabelKelompokAttribute()`. Scope: `scopeTanggal()`. Methods: `akgTarget(?mealType)`, `evaluasiGizi(?mealType)`.

- **`MenuDetailBahan`** — Line items of a menu: links `MenuHarian` → `BahanPangan` with `jumlah_gram` (decimal), `jumlah_porsi` (int, default 1), and `harga_per_100g` (decimal, nullable — price snapshot locked at finalization).

- **`HargaBahan`** — Time-based pricing per ingredient. Fields: `bahan_pangan_id`, `harga_per_100g` (decimal), `berlaku_mulai` (date), `berlaku_sampai` (date, nullable), `keterangan`. `HargaBahan::hargaAktif(int $bahanId, ?string $tanggal = null)` returns float — finds active price where `berlaku_mulai <= date AND (berlaku_sampai IS NULL OR berlaku_sampai >= date)`, ordered by `berlaku_mulai DESC`; defaults to 0.0 if no price found.

- **`AnggaranPorsi`** — Time-based budget per portion per kelompok. Fields: `kelompok` (enum nullable: `balita_sd3`|`sd4_ibu_menyusui`), `anggaran_per_porsi`, `berlaku_mulai`, `berlaku_sampai` (nullable), `keterangan`, `created_by` (FK to User). Constants: `KELOMPOK_LABELS = ['balita_sd3' => 'Balita s/d Kelas 3 SD', 'sd4_ibu_menyusui' => 'Kelas 4 SD s/d Ibu Menyusui']`. `AnggaranPorsi::aktif(?string $tanggal = null, ?string $kelompok = null)` returns float; defaults to 15000 if no match.

- **`ImportLog`** — Records CSV import history. Fields: `user_id`, `filename`, `inserted`, `updated`, `skipped`, `mode`, `created_at` (no `updated_at`).

- **`MenuSesi`** — Legacy/unused model (not wired to any routes). Fields: `menu_harian_id`, `sesi`, `nama_menu`. Has `totalGizi()` method (without `jumlah_porsi` multiplication).

### Nutrition & Cost Calculation

Both calculations live on `MenuHarian` model methods:

- `totalGizi()` — Sums nutritional values across all `detailBahans`, applying BDD factor, then **divides by `jumlah_porsi`** to return per-person values:
  ```
  batch_value = (gram × bdd/100) / 100 × jumlah_porsi × nutrient
  nutrient_per_orang = batch_value / jumlah_porsi
  ```
- `totalBiaya()` — Calculates cost. For **final** menus uses the `harga_per_100g` snapshot on `MenuDetailBahan`; for **draft** menus queries `HargaBahan::hargaAktif()` live. Returns cost per portion, budget comparison, and per-item breakdown:
  ```
  cost_per_item = (gram / 100) × harga_per_100g × jumlah_porsi
  cost_per_porsi = total_cost / jumlah_porsi
  ```
- `statusAnggaran()` — Returns `'over'` (>100%), `'warning'` (≥85%), `'aman'`, or `'belum_ada_data'`.
- `persenAnggaran()` — Returns the percentage of budget used.
- `anggaranAktif()` — Returns active `AnggaranPorsi` value for the menu's `kelompok`.
- `akgTarget(string $mealType = 'siang')` — Returns per-kelompok AKG target using `kelompok_sasaran` field.
- `evaluasiGizi(string $mealType = 'siang')` — Returns per-nutrient evaluation with `pct`, `aktual`, `target`, `status` (kurang/cukup/lebih) for energi, protein, lemak, karbohidrat.

**Finalization** (`MenuHarianController::finalize`) snapshots `harga_per_100g` for each `MenuDetailBahan` and locks `anggaran_per_porsi` at that moment, so historical cost calculations remain stable if prices change later.

AKG (nutritional targets) are constants in `app/Constants/AKG.php`:
```php
AKG::HARIAN       // Full-day targets (energi=1850 kcal, etc.)
AKG::MAKAN_SIANG  // Lunch targets (~32.5% of daily, energi=578 kcal, etc.)
AKG::LABEL        // Associative array: label, satuan, icon per nutrient
AKG::PCT_PAGI     // 0.225 — morning meal proportion
AKG::PCT_SIANG    // 0.325 — lunch meal proportion
AKG::KELOMPOK     // 12 kelompok keys with per-kelompok daily targets:
                  // TK_PAUD, SD_1_3, SD_4_6, SMP, SMA,
                  // BALITA_1_3, BALITA_4_6,
                  // HAMIL_T1/T2/T3, MENYUSUI_6BLN_1/2
AKG::targetSajian(string $key, string $mealType)  // Per-meal target for a kelompok
AKG::cascadeOptions()                              // Grouped dropdown options
AKG::toAnggaranKelompok(string $key)               // Maps kelompok_sasaran → anggaran kelompok
                                                   // ('balita_sd3' or 'sd4_ibu_menyusui')
```

### Dashboard

`DashboardController::index` serves a single unified dashboard for all operasional roles. It aggregates final menus by month, computes: `totalGizi`, `totalBiaya`, `budgetTotal`, `distribusiBiaya` (top 6 categories), `alerts` (over/warning, capped at 5 shown), trend data, rata-rata gizi, and `persenAkg` against `AKG::MAKAN_SIANG`.

The view (`resources/views/dashboard/index.blade.php`) conditionally renders sections based on `$role`. `GiziController::dashboard` now just redirects to `dashboard` — there is no separate gizi dashboard view.

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
| `/bahan-pangan` (CRUD + toggle status) | ketua_sppg only |
| `/menu-harian` (view) | ketua_sppg, ahli_gizi |
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
| `GET /api/bahan-pangan/search?q=&limit=` | Autocomplete for ingredient search (returns harga_per_100g too) |
| `POST /simulasi/kalkulasi` | Real-time nutrition + cost estimation per kelompok (no DB write) |
| `POST /biaya/api/estimasi` | Cost estimation for biaya dashboard |
| `GET /gizi/api/trend` | Nutrition trend data for charts |

### Simulasi

`SimulasiController` supports both **creating** new menus and **editing** existing ones (via `GET /simulasi/{menuHarian}/edit`). `kalkulasi` accepts `kelompok` (AKG kelompok_sasaran key, e.g. `SD_4_6`) and returns `akg_target`, `persen_akg`, and `anggaran_per_kelompok` breakdown for both anggaran kelompok values. `simpan` creates or updates a `MenuHarian` with its `MenuDetailBahan` items, saving `kelompok_sasaran`.

### Import TKPI

`ImportTkpiController` handles CSV import of TKPI data with 42 column aliases for flexible column mapping. Supports comma and semicolon delimiters, BOM stripping, `skip` or `update` duplicate mode (case-insensitive match on `nama_bahan`), and auto-generates a 10-char `kode` if missing. Preview step stores temp file in session. Import results are logged to `ImportLog`.

### Exports

- **Excel**: `rap2hpoutre/fast-excel` via `LaporanController::exportExcel`
- **PDF**: `barryvdh/laravel-dompdf` via `LaporanController::exportPdf` (renders `laporan.pdf-biaya` or `laporan.pdf-gizi`)

### Notable Dependencies

- `spatie/laravel-permission` — installed but not actively used in application code (roles managed via `users.role` column instead)

### Environment

Key `.env` variables beyond standard Laravel:
- `UNIT_SPPG` — Default unit name used in some contexts
- `DB_CONNECTION` — defaults to `sqlite` in example; production uses MySQL
