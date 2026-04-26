# 🍱 Dashboard MBG — Monitoring Gizi & Biaya Produksi

Sistem monitoring berbasis web untuk program **Makan Bergizi Gratis (MBG)** yang dikelola oleh Satuan Pelayanan Pemenuhan Gizi (SPPG). Dashboard ini membantu pengelola memantau kandungan gizi menu harian dan efisiensi biaya produksi secara real-time.

---

## 📋 Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [Struktur Peran Pengguna](#struktur-peran-pengguna)
- [Panduan Penggunaan](#panduan-penggunaan)
- [Struktur Database](#struktur-database)
- [API Internal](#api-internal)
- [Lisensi](#lisensi)

---

## ✨ Fitur Utama

### 🍽️ Manajemen Menu Harian
- Input menu harian beserta bahan pangan (mengacu pada TKPI)
- Status menu: **Draft** (bisa diedit) dan **Final** (terkunci)
- Estimasi gizi real-time saat merakit bahan menu
- Satu unit SPPG hanya bisa memiliki satu menu per hari

### 🥕 Data Bahan Pangan (TKPI)
- Database lengkap **Tabel Komposisi Pangan Indonesia** (845+ bahan)
- Informasi proksimat, mineral, dan vitamin per 100g BDD
- Fitur pencarian dan filter berdasarkan kategori & jenis
- Import data massal via file CSV

### 🔬 Simulasi Menu
- Rakit kombinasi bahan pangan secara bebas sebelum menyimpan
- Kalkulasi estimasi gizi dan biaya secara real-time (AJAX)
- Perbandingan pemenuhan gizi vs. AKG Makan Siang
- Simpan langsung sebagai Menu Harian (draft)

### 💊 Monitoring Gizi
- Pemenuhan gizi harian dibandingkan dengan **Angka Kecukupan Gizi (AKG)** makan siang
- Grafik tren energi harian dalam satu bulan
- Status gizi: **Kurang** (<70%), **Cukup** (70–130%), **Lebih** (>130%)
- Dashboard ringkasan rata-rata bulanan

### 💰 Monitoring Biaya Produksi
- Kalkulasi cost per porsi berdasarkan harga bahan yang diinput
- Perbandingan cost aktual vs. anggaran per porsi
- Grafik tren biaya vs. anggaran bulanan
- Manajemen harga bahan per unit SPPG

### 🔔 Budget Alert
- Notifikasi otomatis menu yang melebihi anggaran (🚨 Over Budget)
- Peringatan menu mendekati batas anggaran (⚠️ ≥85%)
- Badge notifikasi di navbar dengan jumlah peringatan aktif

### 📊 Laporan
- Laporan gizi bulanan (perbandingan AKG)
- Laporan biaya produksi bulanan
- Export ke **Excel** dan **PDF**
- Fitur cetak langsung dari browser

### ⚙️ Administrasi (Admin)
- Kelola pengguna (tambah, edit, reset password, hapus)
- Kelola anggaran per porsi per unit SPPG dengan periode berlaku
- Import data TKPI via CSV dengan mode skip/update
- Filter data lintas semua unit SPPG

---

## 🛠️ Tech Stack

| Komponen | Teknologi |
|---|---|
| Backend | Laravel 12 (PHP) |
| Frontend | Bootstrap 5.3, Vanilla JS |
| Ikon | Font Awesome 6.5 |
| Chart | Chart.js 4.4 |
| Font | Plus Jakarta Sans |
| Database | MySQL |
| PDF Export | DomPDF (via Laravel) |
| Excel Export | PhpSpreadsheet / Laravel Excel |
| Autocomplete | Custom AJAX + Fetch API |

---

## 💻 Persyaratan Sistem

- **PHP** >= 8.2
- **Composer** >= 2.x
- **Node.js** >= 18.x & NPM (untuk asset build)
- **MySQL** >= 8.0 (untuk development)
- **PHP Extensions:** BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

---

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/MohdFarhanS/dashboard_mbg.git
cd dashboard-mbg
```

### 2. Install Dependensi PHP

```bash
composer install
```

### 3. Salin File Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Konfigurasi Database

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_DATABASE=dashboard_mbg
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Migrasi & Seed Database

```bash
php artisan migrate
php artisan db:seed
```

Seeder akan membuat:
- **1 akun Admin**: `admin@mbg.id` / `password123`
- **1 akun Pengelola**: `pengelola@mbg.id` / `password123`
- **845+ data bahan pangan TKPI** (dari `database/seeders/data/tkpi_seeder.json`)

> ⚠️ Pastikan file `database/seeders/data/tkpi_seeder.json` sudah tersedia sebelum menjalankan seeder.

### 6. Jalankan Server Development

```bash
php artisan serve
```

---

## ⚙️ Konfigurasi

### Lokasi File Konfigurasi Penting

| File | Keterangan |
|---|---|
| `.env` | Konfigurasi environment (DB, mail, app key) |
| `app/Constants/AKG.php` | Nilai AKG makan siang sebagai acuan gizi |
| `config/permission.php` | Konfigurasi role & permission (Spatie) |

### Mengubah Nilai AKG

Edit file `app/Constants/AKG.php` untuk menyesuaikan nilai Angka Kecukupan Gizi makan siang:

```php
const MAKAN_SIANG = [
    'energi'      => 667,   // kkal
    'protein'     => 20,    // g
    'lemak'       => 22,    // g
    'karbohidrat' => 100,   // g
    'serat'       => 8,     // g
    'kalsium'     => 350,   // mg
    'besi'        => 4,     // mg
    'vit_c'       => 25,    // mg
];
```

---

## 👥 Struktur Peran Pengguna

| Fitur | Admin | Pengelola |
|---|:---:|:---:|
| Dashboard | ✅ | ✅ |
| Lihat Menu Harian | ✅ | ✅ (unit sendiri) |
| Input/Edit Menu | ✅ | ✅ |
| Lihat Data TKPI | ✅ | ✅ |
| Tambah/Edit Bahan Pangan | ✅ | ❌ |
| Simulasi Menu | ✅ | ✅ |
| Monitor Gizi | ✅ (semua unit) | ✅ (unit sendiri) |
| Monitor Biaya | ✅ (semua unit) | ✅ (unit sendiri) |
| Budget Alert | ✅ | ✅ |
| Kelola Harga Bahan | ✅ | ✅ (unit sendiri) |
| Kelola Anggaran | ✅ | ❌ |
| Laporan & Export | ✅ | ✅ |
| Import TKPI | ✅ | ❌ |
| Kelola Pengguna | ✅ | ❌ |

---

## 📖 Panduan Penggunaan

### Alur Kerja Harian (Pengelola)

```
1. Login → Dashboard
2. Menu Harian → Tambah Menu
3. Pilih bahan pangan dari TKPI (autocomplete)
4. Input gram/porsi untuk setiap bahan
5. Pantau estimasi gizi secara real-time
6. Simpan sebagai Draft
7. Review → Finalisasi menu
```

### Alur Simulasi Menu

```
1. Simulasi Menu → Rakit bahan (tanpa menyimpan)
2. Klik "Hitung Estimasi" → Lihat gizi & biaya
3. Evaluasi pemenuhan AKG dan status anggaran
4. Simpan ke Menu Harian jika sudah sesuai
```

### Import Data Bahan Pangan (Admin)

```
1. Admin → Import TKPI
2. Upload file CSV (delimiter koma/titik koma)
3. Preview 10 baris data
4. Pilih mode: Skip (abaikan duplikat) / Update (perbarui)
5. Konfirmasi import
```

**Format CSV minimal:**
```
nama_bahan,energi,protein,lemak,karbohidrat
Nasi Putih,175,3.2,0.3,39.8
Ayam Goreng,320,18.5,22.1,0
```

---

## 🗄️ Struktur Database

```
users                   → Pengguna sistem (admin & pengelola)
bahan_pangans           → Data TKPI (845+ bahan pangan)
menu_harians            → Menu harian per unit SPPG per tanggal
menu_detail_bahans      → Detail bahan dalam satu menu
harga_bahans            → Harga bahan per unit SPPG (time-based)
anggaran_porsis         → Anggaran per porsi per unit SPPG (time-based)
```

### Relasi Utama

```
users          ──< menu_harians         (user membuat menu)
menu_harians   ──< menu_detail_bahans   (menu punya banyak bahan)
bahan_pangans  ──< menu_detail_bahans   (bahan dipakai di banyak menu)
bahan_pangans  ──< harga_bahans         (bahan punya riwayat harga)
```

---

## 🔌 API Internal

### Search Bahan Pangan
```
GET /api/bahan-pangan/search?q={keyword}&limit={n}
```
Digunakan oleh autocomplete di form menu dan simulasi.

**Response:**
```json
[
  {
    "id": 1,
    "kode": "AR001",
    "nama_bahan": "Beras giling, putih",
    "kategori": "Serealia",
    "energi": 360,
    "protein": 6.8,
    "lemak": 0.5,
    "karbohidrat": 79.9,
    "bdd": 100
  }
]
```

### Kalkulasi Simulasi
```
POST /simulasi/kalkulasi
Content-Type: application/json

{
  "bahans": [{"id": 1, "gram": 150, "porsi": 100}],
  "jumlah_porsi": 100,
  "tanggal": "2026-04-26"
}
```

---

## 📁 Struktur Direktori Penting

```
app/
├── Constants/
│   └── AKG.php                  # Nilai AKG makan siang
├── Http/Controllers/
│   ├── DashboardController.php
│   ├── MenuHarianController.php
│   ├── BahanPanganController.php
│   ├── GiziController.php
│   ├── BiayaController.php
│   ├── SimulasiController.php
│   ├── AnggaranController.php
│   ├── LaporanController.php
│   ├── BudgetAlertController.php
│   ├── ImportTkpiController.php
│   └── UserController.php
├── Models/
│   ├── User.php
│   ├── BahanPangan.php
│   ├── MenuHarian.php
│   ├── MenuDetailBahan.php
│   ├── HargaBahan.php
│   └── AnggaranPorsi.php
resources/views/
├── layouts/app.blade.php        # Layout utama + sidebar
├── partials/
│   ├── sidebar.blade.php
│   └── navbar.blade.php
├── dashboard/
├── menu-harian/
├── bahan-pangan/
├── simulasi/
├── gizi/
├── biaya/
├── anggaran/
├── laporan/
├── budget-alert/
├── import-tkpi/
└── users/
database/
├── migrations/
└── seeders/
    └── data/
        └── tkpi_seeder.json     # Data 845+ bahan pangan TKPI
```

---

## 🤝 Kontribusi

1. Fork repository ini
2. Buat branch fitur: `git checkout -b fitur/nama-fitur`
3. Commit perubahan: `git commit -m 'Tambah fitur XYZ'`
4. Push ke branch: `git push origin fitur/nama-fitur`
5. Buat Pull Request

---

## 📄 Lisensi

Proyek ini dikembangkan untuk keperluan monitoring Program Makan Bergizi Gratis (MBG) oleh Satuan Pelayanan Pemenuhan Gizi (SPPG).

---

*Dashboard MBG © 2026 — SPPG Monitoring System*