<p align="center">
  <h1 align="center">💰 KASLO</h1>
  <p align="center">
    <strong>Personal & Shared Family Expense Tracker</strong><br>
    Aplikasi pelacak keuangan dan pengeluaran modern berbasis Laravel 12 & Filament v3 dengan dukungan multi-user (ruang keuangan bersama suami-istri).
  </p>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Filament-3.x-F59E0B?style=for-the-badge&logo=filament&logoColor=white" alt="Filament v3">
  <img src="https://img.shields.io/badge/PHP-%3E%3D8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License MIT">
</p>

---

## 📖 Tentang Kaslo

**Kaslo** (*Kas Flow*) adalah aplikasi pelacak keuangan dan anggaran yang dirancang untuk memudahkan pencatatan arus kas pribadi, keluarga, maupun usaha kecil. 

Dengan antarmuka yang bersih, cepat, dan mobile-responsive menggunakan **Filament v3**, Kaslo memungkinkan suami dan istri login menggunakan akun masing-masing namun terhubung ke ruang keuangan bersama yang sama secara real-time.

---

## ✨ Fitur Utama

- 👨‍👩‍👧 **Multi-User & Ruang Keuangan Bersama (Tenancy)**
  - Suami dan istri memiliki akun login terpisah namun mengelola dompet dan anggaran yang sama secara transparan.
  - Setiap transaksi mencatat siapa yang melakukan input (misal: *Budi Santoso* atau *Siti Rahma*).
  - Menu **Anggota Keluarga** untuk mengundang pasangan hanya dengan memasukkan alamat email.

- 💳 **Manajemen Dompet & Rekening (Multi-Wallet)**
  - Mendukung jenis akun: **Uang Tunai (Cash)**, **Rekening Bank** (BCA, Mandiri, BRI, dll.), **E-Wallet** (GoPay, OVO, Dana, ShopeePay), dan **Investasi**.
  - Pilihan warna indikator untuk identifikasi visual cepat.
  - **Sinkronisasi Saldo Otomatis**: Saldo dompet otomatis bertambah saat pemasukan, berkurang saat pengeluaran, dan berpindah saat transfer antar dompet via `TransactionObserver`.

- 📝 **Pencatatan Transaksi Cerdas**
  - Jenis transaksi: 🔴 **Pengeluaran**, 🟢 **Pemasukan**, dan 🔄 **Transfer Saldo Antar Dompet**.
  - **Format Rupiah Otomatis**: Input nominal dilengkapi pemisah titik ribuan secara real-time (`900.000`).
  - Upload foto struk / bukti transaksi belanja.
  - Filter tabel fleksibel: tanggal, dompet, kategori, jenis transaksi, dan pencatat.

- 🎯 **Target Anggaran Bulanan (Budgeting)**
  - Penetapan limit anggaran bulanan per kategori pengeluaran (format periode `YYYY-MM`).
  - Realisasi terpakai dihitung otomatis secara real-time.
  - Indikator status cerdas: **Aman (<80%)**, **Waspada (80-100%)**, dan **Over Budget (>100%)**.

- 📊 **Dashboard Finansial & Visualisasi Interaktif**
  - Kartu statistik: Total Saldo Bersama, Pemasukan Bulan Ini, Pengeluaran Bulan Ini, dan Arus Kas Bersih (*Net Cash Flow*).
  - *Doughnut Chart*: Alokasi pengeluaran per kategori pada bulan berjalan.
  - *Bar Chart*: Tren perbandingan pemasukan vs pengeluaran 6 bulan terakhir.
  - Widget 5 transaksi terkini.

- 📱 **Mobile Responsive**
  - Tata letak responsif untuk smartphone dan tablet dengan menu sidebar yang dapat dibuka-tutup dengan mulus.

---

## 🛠️ Teknologi yang Digunakan (Tech Stack)

| Komponen | Teknologi |
|---|---|
| **Framework Backend** | [Laravel 12](https://laravel.com/) |
| **Admin & UI Panel** | [Filament v3](https://filamentphp.com/) (TALL Stack) |
| **Frontend Framework** | [Livewire 3](https://livewire.laravel.com/) & [Alpine.js](https://alpinejs.dev/) |
| **Styling & CSS** | [Tailwind CSS](https://tailwindcss.com/) |
| **Database** | MySQL / MariaDB (kompatibel juga dengan SQLite) |
| **Bahasa Pemrograman** | PHP 8.2 / PHP 8.3 |

---

## 📋 Kebutuhan Sistem (System Requirements)

Pastikan server atau komputer lokal Anda telah memenuhi spesifikasi berikut:

- **PHP**: Versi `>= 8.2` (Disarankan PHP 8.3)
- **Ekstensi PHP Wajib**:
  - `pdo_mysql`
  - `mbstring`
  - `openssl`
  - `fileinfo`
  - `intl`
  - `zip`
  - `gd` atau `imagick`
  - `bcmath`
  - `curl`
- **Database**: MySQL `>= 8.0` atau MariaDB `>= 10.4`
- **Dependency Manager**: [Composer](https://getcomposer.org/) versi `>= 2.2`

---

## 🚀 Panduan Instalasi Lokal (Quick Start)

Ikuti langkah-langkah berikut untuk menjalankan Kaslo di komputer lokal:

### 1. Clone Repository
```bash
git clone https://github.com/username-anda/kaslo.git
cd kaslo
```

### 2. Install Dependensi Composer
```bash
composer install
```

### 3. Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Buka file `.env` dan sesuaikan konfigurasi database MySQL Anda:
```env
APP_NAME=Kaslo
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kaslo
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Jalankan Migrasi Database & Seeder
Buat database `kaslo` di MySQL Anda terlebih dahulu, kemudian jalankan:
```bash
php artisan migrate --seed
```

### 6. Hubungkan Storage Link (untuk Upload Bukti Struk)
```bash
php artisan storage:link
```

### 7. Jalankan Server Lokal
```bash
php artisan serve
```
Akses aplikasi melalui browser di: **[http://localhost:8000](http://localhost:8000)** (otomatis diarahkan ke halaman login Kaslo).

---

## 🔑 Akun Demo Bawaan

Setelah menjalankan perintah `--seed`, Anda dapat langsung login menggunakan akun demo berikut:

| Peran | Alamat Email | Password | Ruang Keuangan |
|---|---|---|---|
| **Suami** (Pemilik / Pengelola) | `suami@kaslo.test` | `password` | Keluarga Budi & Siti |
| **Istri** (Anggota Bersama) | `istri@kaslo.test` | `password` | Keluarga Budi & Siti |

---

## 🧪 Menjalankan Pengujian Otomatis (Testing)

Kaslo dilengkapi dengan Automated Feature & Unit Test untuk memverifikasi logika kalkulasi saldo, multi-tenancy, dan budgeting:

```bash
php artisan test
```

Hasil pengujian yang diharapkan:
```text
PASS  Tests\Unit\ExampleTest
PASS  Tests\Feature\ExampleTest
PASS  Tests\Feature\KasloExpenseTrackerTest
  ✓ root redirects to admin
  ✓ household and multi user access
  ✓ expense decreases wallet balance
  ✓ income increases wallet balance
  ✓ transfer moves balance between wallets
  ✓ budget creation

Tests: 8 passed (14 assertions)
```

---

## 📄 Lisensi

Proyek Kaslo ini dirilis di bawah lisensi terbuka [MIT License](LICENSE).
