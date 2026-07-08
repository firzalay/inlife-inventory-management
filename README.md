# 📦 InLife Inventory Management System

Aplikasi manajemen inventaris barang dan transaksi peminjaman barang berbasis web yang dibangun menggunakan **Laravel 13**, **TailwindCSS**, dan **AlpineJS**. Proyek ini dirancang agar dapat dijalankan dengan mudah menggunakan **Docker** baik untuk lingkungan pengembangan (development) maupun penyebaran ke server (production/VPS).

---

## ✨ Fitur Utama

1. **Manajemen Barang (Master Data)**
   - Pencatatan barang dengan kode unik, nama, kategori, dan lokasi fisik.
   - **Pemisahan Stok Adaptif**: Jumlah stok dibagi secara spesifik berdasarkan kondisi barang (`Stok Baik`, `Stok Rusak`, dan `Stok Perlu Perbaikan`).
   - Kalkulasi otomatis total stok dan stok tersedia yang dapat dipinjam.

2. **Sistem Peminjaman & Pengembalian Barang**
   - Pencatatan nama peminjam, tanggal pinjam, dan tenggat waktu pengembalian (*due date*).
   - Pengurangan stok tersedia secara otomatis saat barang dipinjam.
   - **Pencatatan Kondisi Saat Kembali**: Mengharuskan pencatatan kondisi setiap barang saat dikembalikan (Baik/Rusak/Perlu Perbaikan) untuk memperbarui status inventaris secara akurat.
   - Deteksi otomatis status keterlambatan (*overdue*).

3. **Notifikasi & Alerts**
   - Pengiriman notifikasi sistem bagi Admin jika ada pendaftaran user baru yang membutuhkan persetujuan.
   - Panel notifikasi dinamis dengan penanda bel interaktif.

4. **Manajemen Pengguna & Otorisasi**
   - Multi-role: `Admin`, `Manager`, dan `Staff` (didukung oleh *Spatie Laravel Permission*).
   - Halaman khusus pendaftaran pengguna dengan alur persetujuan (*approval process*) oleh Admin sebelum akun dapat digunakan.

5. **Aksesibilitas & UI Modern**
   - Toggle **Dark Mode & Light Mode** hybrid berbasis preferensi lokal dengan pencegahan FOUC (*Flash of Unstyled Content*).
   - Ikon yang disempurnakan menggunakan **Font Awesome 6**.

6. **RESTful API**
   - Endpoint API lengkap untuk integrasi pihak ketiga, terdokumentasi di [API_DOCUMENTATION.md](API_DOCUMENTATION.md).

---

## 🛠️ Stack Teknologi

* **Backend**: PHP 8.4 & Laravel 13
* **Frontend**: TailwindCSS, Alpine.js, Font Awesome 6
* **Database**: MySQL 9.1
* **Web Server**: Nginx

---

## 🐳 Dockerization

Proyek ini sepenuhnya mendukung kontainerisasi menggunakan Docker untuk mempermudah setup tanpa perlu menginstal PHP/Composer/MySQL secara manual di komputer host.

### Konfigurasi Docker:
* [**Dockerfile**](Dockerfile): Konfigurasi image PHP 8.4-FPM dengan ekstensi lengkap (`gd`, `zip`, `pdo_mysql`, `intl`, `exif`, dll).
* [**docker-compose.yml**](docker-compose.yml): Konfigurasi utama multi-kontainer (App, Nginx, MySQL) untuk server production/VPS.
* [**docker-compose.override.yml**](docker-compose.override.yml): Konfigurasi tambahan khusus lokal (development) untuk mengaktifkan hot reload Vite, volume sharing kode sumber, dan port DB `3307` (agar tidak bentrok dengan MySQL lokal).
* [**.dockerignore**](.dockerignore): Mengabaikan folder lokal berukuran besar seperti `node_modules` dan `vendor` agar proses build cepat.

---

## 🚀 Cara Menjalankan Aplikasi

### A. Menggunakan Docker (Lokal/Development)
1. Salin template environment Docker:
   ```bash
   cp .env.docker .env
   ```
2. Pastikan Docker Desktop Anda aktif.
3. Jalankan Docker Compose:
   ```bash
   docker compose up --build
   ```
4. Jalankan migrasi dan isi data awal (seeder) melalui container:
   ```bash
   docker compose exec app php artisan migrate:fresh --seed
   ```
5. Buka **[http://localhost](http://localhost)** di browser Anda.

### B. Tanpa Docker (Lokal)
1. Salin template `.env` dan sesuaikan kredensial MySQL lokal Anda (Laragon/XAMPP).
2. Jalankan instalasi dependensi:
   ```bash
   composer install
   ```
3. Generate key & jalankan migrasi:
   ```bash
   php artisan key:generate
   ```
4. Jalankan server lokal:
   ```bash
   composer run dev
   ```
5. Buka **[http://localhost:8000](http://localhost:8000)** di browser Anda.

---

## ☁️ Deployment ke VPS Ubuntu (Hostinger)

Aplikasi ini sangat mudah dideploy ke VPS Ubuntu menggunakan Docker Compose. Panduan langkah-demi-langkah dari nol hingga setup HTTPS gratis dengan Certbot Let's Encrypt dapat Anda baca langsung pada file dokumentasi terpisah di proyek ini.

---

## 📄 Lisensi

Aplikasi ini dirilis di bawah lisensi [MIT License](LICENSE).
