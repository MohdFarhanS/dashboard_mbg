# Dashboard MBG — Monitoring Gizi & Biaya Produksi

Sistem monitoring berbasis web untuk program **Makan Bergizi Gratis (MBG)** yang dikelola oleh Satuan Pelayanan Pemenuhan Gizi (SPPG). Dashboard ini membantu pengelola memantau kandungan gizi menu harian dan efisiensi biaya produksi secara real-time.

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [Struktur Peran Pengguna](#struktur-peran-pengguna)
- [Panduan Penggunaan](#panduan-penggunaan)
- [Struktur Database](#struktur-database)
- [API Internal](#api-internal)

---

## Fitur Utama

### Manajemen Menu Harian
- Input menu harian beserta bahan pangan mengacu pada database TKPI
- Pilih **kelompok sasaran** (12 kelompok: TK/PAUD, SD Kelas 1–3, SD 4–6, SMP, SMA, Balita, Ibu Hamil/Menyusui)
- Status menu: **Draft** (dapat diedit) dan **Final** (terkunci & masuk laporan)
- Satu unit SPPG bisa menyimpan lebih dari satu menu per hari (satu menu per kelompok sasaran)

### Data Bahan Pangan (TKPI)
- Database lengkap **Tabel Komposisi Pangan Indonesia** (845+ bahan)
- Informasi proksimat, mineral, dan vitamin per 100g BDD
- Pencarian dan filter berdasarkan kategori & jenis
- Import data massal via file CSV

### Simulasi Menu
- Rakit kombinasi bahan pangan sebelum menyimpan
- Pilih kelompok sasaran untuk target AKG yang akurat per kelompok
- Kalkulasi estimasi gizi dan biaya secara real-time (AJAX)
- Perbandingan pemenuhan gizi vs. AKG Makan Siang per kelompok
- Simpan langsung sebagai Menu Harian (draft)

### Monitoring Gizi
- Pemenuhan gizi harian dibandingkan dengan **Angka Kecukupan Gizi (AKG)** makan siang
- Grafik tren energi harian dalam satu bulan
- Status gizi: **Kurang** (<70%), **Cukup** (70–130%), **Lebih** (>130%)
- Dashboard ringkasan rata-rata bulanan

### Monitoring Biaya Produksi
- Kalkulasi cost per porsi berdasarkan harga bahan yang diinput
- Perbandingan cost aktual vs. anggaran per porsi
- Grafik tren biaya vs. anggaran bulanan
- Manajemen harga bahan per unit SPPG

### Budget Alert
- Notifikasi otomatis menu yang melebihi anggaran (Over Budget)
- Peringatan menu mendekati batas anggaran (≥85%)
- Badge notifikasi di navbar dengan jumlah peringatan aktif

### Laporan
- Laporan gizi bulanan (perbandingan AKG)
- Laporan biaya produksi bulanan
- Export ke **Excel** dan **PDF**
- Fitur cetak langsung dari browser

### Administrasi (Admin)
- Kelola pengguna (tambah, edit, reset password, hapus)
- Kelola anggaran per porsi per unit SPPG dengan periode berlaku
- Import data TKPI via CSV dengan mode skip/update
- Filter data lintas semua unit SPPG

---

## Tech Stack

| Komponen | Teknologi |
|---|---|
| Backend | Laravel 12 (PHP) |
| Frontend | Bootstrap 5.3, Vanilla JS |
| Ikon | Font Awesome 6.5 |
| Chart | Chart.js 4.4 |
| Font | Plus Jakarta Sans |
| Database | MySQL |
| PDF Export | DomPDF (via Laravel) |
| Excel Export | FastExcel (rap2hpoutre) |
| Autocomplete | Custom AJAX + Fetch API |

---

## Persyaratan Sistem

- **PHP** >= 8.2
- **Composer** >= 2.x
- **Node.js** >= 18.x & NPM
- **MySQL** >= 8.0
- **PHP Extensions:** BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

---

## Instalasi

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

Edit file `.env`:

```env
DB_CONNECTION=mysql
DB_DATABASE=dashboard_mbg
DB_USERNAME=root
DB_PASSWORD=your_password
UNIT_SPPG="SPPG Utama"
```

### 5. Migrasi & Seed Database

```bash
php artisan migrate
php artisan db:seed
```

Seeder akan membuat:
- `superadmin@mbg.id` / `password123` → superadmin
- `ketua@mbg.id` / `password123` → ketua_sppg
- `gizi@mbg.id` / `password123` → ahli_gizi
- `akuntan@mbg.id` / `password123` → akuntan
- **845+ data bahan pangan TKPI**

### 6. Build Frontend & Jalankan Server

```bash
npm install
npm run build
php artisan serve
```

Akses aplikasi di `http://localhost:8000`.

---

## Konfigurasi

### Mengubah Nilai AKG

Edit `app/Constants/AKG.php`. Nilai AKG Makan Siang saat ini (32,5% dari AKG harian Anak 7–12 tahun):

```php
const MAKAN_SIANG = [
    'energi'      => 578,  // kkal
    'protein'     => 14,   // g
    'lemak'       => 19,   // g
    'karbohidrat' => 88,   // g
    'serat'       => 8,    // g
    'kalsium'     => 350,  // mg
    'besi'        => 4,    // mg
    'vit_c'       => 16,   // mg
];
```

Untuk target per kelompok sasaran (TK, SD, SMP, SMA, Balita, Ibu Hamil/Menyusui), edit konstanta `AKG::KELOMPOK`. Simulasi menu menggunakan `AKG::targetSajian($kelompok_sasaran)` yang menghitung 32,5% dari nilai harian kelompok tersebut.

---

## Struktur Peran Pengguna

Sistem menggunakan 4 role:

| Fitur | Superadmin | Ketua SPPG | Ahli Gizi | Akuntan |
|---|:---:|:---:|:---:|:---:|
| Kelola Pengguna | ✅ | ❌ | ❌ | ❌ |
| Dashboard | ❌ | ✅ | ✅ | ✅ |
| Lihat Menu Harian | ❌ | ✅ | ✅ | ❌ |
| Buat/Edit/Hapus Menu | ❌ | ❌ | ✅ | ❌ |
| Simulasi Menu | ❌ | ❌ | ✅ | ❌ |
| Monitor Gizi | ❌ | ✅ | ✅ | ❌ |
| Monitor Biaya | ❌ | ✅ | ❌ | ✅ |
| Budget Alert | ❌ | ✅ | ❌ | ✅ |
| Kelola Harga Bahan | ❌ | ✅ | ❌ | ✅ |
| Kelola Anggaran Porsi | ❌ | ✅ | ❌ | ❌ |
| Laporan & Export | ❌ | ✅ | ✅ | ✅ |
| Data Bahan Pangan (TKPI) | ❌ | ✅ (kelola) | ✅ (lihat) | ✅ (lihat) |
| Import TKPI | ❌ | ✅ | ❌ | ❌ |

---

## Panduan Penggunaan

### Login

1. Buka aplikasi di browser (`http://localhost:8000`)
2. Masukkan **email** dan **password**
3. Klik **Login**

Akun default setelah seeding:
- Superadmin: `superadmin@mbg.id` / `password123`
- Ketua SPPG: `ketua@mbg.id` / `password123`
- Ahli Gizi: `gizi@mbg.id` / `password123`
- Akuntan: `akuntan@mbg.id` / `password123`

> Superadmin langsung diarahkan ke halaman **Manajemen Pengguna**, bukan Dashboard.

---

### Dashboard (Halaman Utama)

Setelah login, Anda langsung masuk ke **Dashboard** yang menampilkan:

- **Ringkasan hari ini**: jumlah menu, total kalori, biaya produksi, dan status anggaran
- **Progress pemenuhan gizi** dibandingkan AKG (Energi, Protein, Lemak, Karbohidrat)
- **Grafik distribusi biaya** per kategori bahan pangan
- **Budget Alert** — daftar menu yang melebihi atau mendekati anggaran
- **Tabel menu hari ini** dengan status gizi dan anggaran

> Admin melihat data semua unit SPPG. Pengelola hanya melihat unit miliknya.

---

### Membuat Menu Harian (Pengelola)

Alur utama pembuatan menu harian:

**Langkah 1 — Buka Simulasi Menu**

Klik menu **Simulasi** di sidebar, atau dari halaman **Menu Harian** klik tombol **+ Buat Menu Baru**.

**Langkah 2 — Isi Data Menu**

- **Tanggal**: pilih tanggal menu (tidak bisa duplikat per kelompok sasaran per hari)
- **Kelompok Sasaran**: pilih kelompok penerima (TK/PAUD, SD 1–3, SD 4–6, SMP, SMA, Balita, Ibu Hamil, Ibu Menyusui) — menentukan target AKG yang digunakan
- **Jumlah Porsi**: isi total porsi yang akan dibuat
- **Nama Menu** (opsional): misalnya "Makan Siang Senin"

**Langkah 3 — Tambah Bahan Pangan**

- Klik **+ Tambah Bahan**
- Ketik nama bahan di kolom pencarian (autocomplete dari database TKPI)
- Pilih bahan yang sesuai dari dropdown
- Isi **Gram per Porsi** (berat per satu porsi)
- Isi **Jumlah Porsi**
- Ulangi untuk setiap bahan pangan

**Langkah 4 — Hitung Estimasi**

Klik tombol **Hitung Estimasi**. Panel kanan akan menampilkan:

- **Progress bar gizi** untuk 8 nutrisi: Energi, Protein, Lemak, Karbohidrat, Serat, Kalsium, Besi, Vitamin C
  - Hijau: cukup (70–130% AKG)
  - Merah: kurang (<70% AKG)
  - Kuning: berlebih (>130% AKG)
- **Estimasi biaya**: total biaya, biaya per porsi, anggaran, selisih, dan Food Cost Ratio
- **Status anggaran**: Aman / Mendekati Batas / Over Budget
- **Tabel detail** per bahan: gram, kalori, efisiensi BDD, biaya

**Langkah 5 — Simpan Menu**

Tambahkan catatan (opsional), lalu klik **Simpan ke Menu Harian**. Menu tersimpan sebagai **Draft**.

**Langkah 6 — Finalisasi Menu**

Di halaman **Menu Harian**, buka detail menu Draft dan klik **Finalisasi**. Status berubah menjadi **Final** — menu terkunci dan masuk ke laporan serta monitoring.

> Menu dengan status **Draft** tidak dihitung dalam laporan dan grafik monitoring.

---

### Monitoring Gizi

Buka menu **Monitoring Gizi** di sidebar.

- **Filter** berdasarkan bulan (dan unit SPPG untuk Admin)
- Lihat **status gizi hari ini** dengan progress bar per nutrisi
- Lihat **rata-rata bulanan** dan perbandingan vs AKG dalam grafik batang
- Pantau **grafik tren energi harian** sepanjang bulan
- Tabel **daftar menu final** bulan ini dengan status gizi masing-masing

Kode warna status gizi:
| Status | Kondisi | Warna |
|---|---|---|
| Cukup | 70% – 130% AKG | Hijau |
| Kurang | < 70% AKG | Merah |
| Lebih | > 130% AKG | Kuning |

---

### Monitoring Biaya Produksi

Buka menu **Monitoring Biaya** di sidebar.

- **Filter** berdasarkan bulan
- Lihat **4 kartu statistik**: jumlah hari, total biaya, rata-rata biaya/porsi, jumlah over/aman
- Pantau **grafik tren biaya vs anggaran** per hari
- Tabel **rekap biaya** per menu dengan kolom selisih dan status

> Biaya dihitung otomatis dari harga bahan yang diinput oleh Admin. Jika harga belum diinput, biaya tidak bisa dihitung.

---

### Budget Alert

Buka menu **Budget Alert** di sidebar untuk melihat semua menu yang melampaui atau mendekati batas anggaran.

- **Filter** berdasarkan bulan, unit, dan tingkat keparahan
- **3 kartu statistik**: Over Budget, Mendekati Batas (≥85%), Aman
- Setiap kartu alert menampilkan: nama menu, tanggal, biaya/porsi, anggaran, selisih, dan progress bar serapan anggaran
- Tombol **Lihat Detail Menu** dan **Lihat Rincian Biaya** untuk investigasi lebih lanjut

Badge merah di navbar menunjukkan jumlah alert aktif.

---

### Laporan

Buka menu **Laporan** di sidebar.

**Melihat Laporan:**
1. Pilih **bulan** dan **tipe laporan** (Gizi / Biaya)
2. Tabel laporan akan menampilkan data semua menu final di bulan tersebut

**Export Laporan:**
- Klik **Export Excel** untuk mengunduh file `.xlsx`
- Klik **Export PDF** untuk mengunduh file `.pdf`
- Klik **Cetak** untuk mencetak langsung dari browser

---

### Kelola Harga Bahan (Ketua SPPG / Akuntan)

Buka **Monitoring Biaya** → klik tombol **Kelola Harga Bahan**.

1. Klik **+ Tambah Harga**
2. Pilih **bahan pangan** dari dropdown
3. Isi **harga per 100g** (dalam Rupiah)
4. Isi **tanggal mulai berlaku**
5. Tambahkan keterangan (opsional)
6. Klik **Simpan**

Harga dapat diubah dari waktu ke waktu — sistem selalu menggunakan harga yang aktif pada tanggal menu. Saat menu di-**finalisasi**, harga tiap bahan dikunci sebagai snapshot sehingga kalkulasi biaya historis tidak berubah jika tarif harga diperbarui.

---

### Kelola Anggaran Porsi (Ketua SPPG)

Buka menu **Anggaran** di sidebar.

1. Klik **+ Tambah Anggaran**
2. Isi **nominal anggaran per porsi** (Rp)
3. Isi **tanggal mulai berlaku**
4. Tambahkan keterangan (opsional)
5. Klik **Simpan**

Anggaran per porsi digunakan sebagai acuan di seluruh fitur monitoring biaya dan budget alert.

---

### Data Bahan Pangan / TKPI (Ketua SPPG)

Buka menu **Bahan Pangan** di sidebar.

**Mencari Bahan:**
- Gunakan kolom **Cari** untuk mencari berdasarkan nama atau kode TKPI
- Filter berdasarkan **Kategori** (Serealia, Daging, Sayuran, dll.)
- Filter berdasarkan **Jenis** (Tunggal / Olahan)

**Menambah Bahan (Admin):**
1. Klik **+ Tambah Bahan Pangan**
2. Isi semua kolom: nama, kode, kategori, nilai gizi per 100g, dan nilai BDD
3. Klik **Simpan**

**Import Massal via CSV (Admin):**
1. Buka menu **Import TKPI** di sidebar
2. Upload file CSV dengan kolom minimal: `nama_bahan, energi, protein, lemak, karbohidrat`
3. Review preview 10 baris pertama
4. Pilih mode: **Skip** (abaikan data duplikat) atau **Update** (perbarui data yang ada)
5. Klik **Konfirmasi Import**

Format CSV minimal:
```
nama_bahan,energi,protein,lemak,karbohidrat
Nasi Putih,175,3.2,0.3,39.8
Ayam Goreng,320,18.5,22.1,0
```

---

### Kelola Pengguna (Superadmin)

Buka menu **Pengguna** di sidebar.

**Menambah Pengguna:**
1. Klik **+ Tambah Pengguna**
2. Isi nama, email, password, pilih role (**Ketua SPPG** / **Ahli Gizi** / **Akuntan**)
3. Isi **Unit SPPG** untuk role operasional
4. Klik **Simpan**

**Reset Password:**
1. Klik ikon kunci di baris pengguna
2. Isi password baru (minimal 8 karakter)
3. Konfirmasi password
4. Klik **Simpan**

**Menghapus Pengguna:**
Klik ikon hapus di baris pengguna. Akun sendiri tidak bisa dihapus.

---

### Alur Kerja Lengkap (Ringkasan)

```
[Superadmin]
  └─ Setup awal:
       1. Tambah pengguna Ketua SPPG, Ahli Gizi, Akuntan

[Ketua SPPG]
  └─ Setup operasional:
       1. Set anggaran per porsi per kelompok

[Ahli Gizi] — Harian
  └─ Simulasi Menu → Pilih kelompok sasaran → Tambah bahan → Hitung estimasi → Simpan (Draft)
  └─ Menu Harian → Review → Finalisasi (Final)

[Ketua SPPG / Ahli Gizi / Akuntan] — Monitoring
  └─ Dashboard → cek ringkasan hari ini
  └─ Monitoring Gizi (Ketua/Ahli Gizi) → pantau pemenuhan AKG per kelompok sasaran
  └─ Monitoring Biaya (Ketua/Akuntan) → pantau cost vs anggaran
  └─ Budget Alert (Ketua/Akuntan) → investigasi menu bermasalah
  └─ Laporan → export Excel/PDF bulanan
```

---

## Struktur Database

```
users                   — Pengguna sistem (4 role)
bahan_pangans           — Data TKPI (845+ bahan pangan)
menu_harians            — Menu harian per unit SPPG; unique (tanggal, kelompok_sasaran)
menu_detail_bahans      — Detail bahan dalam satu menu; menyimpan snapshot harga saat finalisasi
harga_bahans            — Harga bahan per 100g (time-based)
anggaran_porsis         — Anggaran per porsi per kelompok (time-based)
```

**Relasi Utama:**

```
users          ──< menu_harians         (user membuat menu)
menu_harians   ──< menu_detail_bahans   (menu punya banyak bahan)
bahan_pangans  ──< menu_detail_bahans   (bahan dipakai di banyak menu)
bahan_pangans  ──< harga_bahans         (bahan punya riwayat harga)
```

---

## API Internal

Semua endpoint membutuhkan autentikasi (session/cookie).

### Search Bahan Pangan
```
GET /api/bahan-pangan/search?q={keyword}&limit={n}
```

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

### Tren Gizi Bulanan
```
GET /gizi/api/trend?bulan=2026-04&unit=SPPG-01
```

### Estimasi Biaya
```
POST /biaya/api/estimasi
```

---

## Struktur Direktori Penting

```
app/
├── Constants/AKG.php                  — Nilai AKG makan siang
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
│   ├── BahanPangan.php
│   ├── MenuHarian.php
│   ├── MenuDetailBahan.php
│   ├── HargaBahan.php
│   └── AnggaranPorsi.php
resources/views/
├── layouts/app.blade.php              — Layout utama
├── partials/sidebar.blade.php
├── dashboard/
├── menu-harian/
├── simulasi/
├── gizi/
├── biaya/
├── anggaran/
├── laporan/
├── budget-alert/
├── bahan-pangan/
├── import-tkpi/
└── users/
database/seeders/data/
└── tkpi_seeder.json                   — Data 845+ bahan pangan
```

---

*Dashboard MBG — Sistem Monitoring Gizi & Biaya Produksi untuk Program Makan Bergizi Gratis (MBG)*
