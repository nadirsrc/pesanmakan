# 🍽️ RestoApp - Aplikasi Pemesanan Makanan & Rekap Transaksi Restoran

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)

Aplikasi web pemesanan makanan restoran terintegrasi yang dibangun menggunakan **Laravel Framework**, **Tailwind CSS**, dan **Laravel Breeze**. Proyek ini dirancang sesuai standar **Uji Kompetensi Keahlian (UKK) / Sertifikasi Junior Web Programmer (JWP - LSP / BNSP)** dengan menerapkan konsep MVC (*Model-View-Controller*), *Database Transactions*, dan otentikasi admin terproteksi.

---

## 🌟 Fitur Utama Aplikasi

### 1. Sisi Pelanggan (Customer - Publik)
* **Katalog Menu Digital:** Menampilkan daftar menu makanan, minuman, dan cemilan secara rapi dengan gambar dan harga.
* **Filter Kategori Real-Time:** Filter menu (*Semua, Makanan, Minuman, Cemilan*) tanpa reload halaman.
* **Form Pemesanan Interaktif:** Input nama pelanggan, nomor meja, serta pemilihan jumlah porsi pesanan.
* **Modal Konfirmasi & Cek Total:** Pratinjau rincian pesanan dan total harga sebelum data dikirim ke database.
* **Zero-Reload Notification:** Pesan sukses/gagal langsung muncul setelah proses checkout.

### 2. Sisi Administrator (Admin - Terproteksi Login)
* **Autentikasi Aman:** Sistem login admin menggunakan enkripsi password `Hash::make()` dan proteksi route middleware `auth`.
* **CRUD Master Makanan Lengkap:**
  * Tambah menu baru dengan validasi data (`name`, `category`, `price`, `description`).
  * Upload foto makanan ke storage lokal (`storage/app/public/foods`).
  * Edit data menu beserta penggantian foto (foto lama otomatis terhapus dari server).
  * Hapus menu makanan sekaligus membersihkan file gambar fisiknya dari harddisk.
* **Dashboard Rekap Pesanan:**
  * Memantau daftar transaksi masuk secara *real-time*.
  * Rincian item pesanan, jumlah kuantitas, dan subtotal per item.
  * Status badge dinamis (*Pending, Selesai, Batal*).
  * Fitur ganti status pesanan instan via dropdown menggunakan method `PATCH`.

---

## 🗄️ Arsitektur Database (db_pemesanan_makanan)

Aplikasi ini menggunakan 3 tabel utama yang saling berelasi:

```
[foods] 1 ──────────< [order_details] >────────── 1 [orders]
(Master Menu)          (Detail Transaksi)            (Header Pesanan)
```

| Tabel | Kolom Utama | Keterangan |
| :--- | :--- | :--- |
| **`foods`** | `id`, `name`, `category` (Enum: Makanan, Minuman, Cemilan), `price`, `description`, `image` (nullable), `timestamps` | Menyimpan master katalog makanan |
| **`orders`** | `id`, `customer_name`, `table_number`, `total_price`, `status` (default: 'Pending'), `timestamps` | Menyimpan transaksi induk pesanan meja |
| **`order_details`** | `id`, `order_id` (FK), `food_id` (FK), `quantity`, `subtotal`, `timestamps` | Menyimpan rincian item, jumlah, dan subtotal harga |

> **Integritas Data:** Foreign Key pada tabel `order_details` dilengkapi dengan `->onDelete('cascade')` untuk mencegah adanya *orphaned records* (data yatim piatu).

---

## 🔐 Akun Login Default (Untuk Pengujian / Asesor)

Untuk masuk ke Dashboard Admin, gunakan kredensial bawaan hasil seeder:

* **URL Login:** `http://127.0.0.1:8000/login`
* **Email:** `admin@gmail.com`
* **Password:** `password123`

---

## 🚀 Panduan Instalasi & Menjalankan Proyek

Ikuti langkah-langkah di bawah ini untuk mengkloning dan menjalankan proyek ini di komputer lokal:

### 1. Clone Repository
```bash
git clone https://github.com/username-anda/nama-repo.git
cd nama-repo
```

### 2. Install Dependensi PHP & JavaScript
```bash
composer install
npm install
```

### 3. Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Buka file `.env`, lalu sesuaikan konfigurasi database MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pesanmakan
DB_USERNAME=root
DB_PASSWORD=
```
*(Pastikan Anda telah membuat database bernama `pesanmakan` di phpMyAdmin)*.

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Jalankan Migration & Seeder Data Awal
Perintah ini akan membuat semua struktur tabel dan mengisi 5 data menu makanan serta akun admin:
```bash
php artisan migrate:fresh --seed
```

### 6. Hubungkan Storage Foto (Symlink)
Wajib dijalankan agar gambar menu yang diunggah dapat ditampilkan oleh browser:
```bash
php artisan storage:link
```

### 7. Kompilasi Aset Frontend (Tailwind CSS)
```bash
npm run build
```
*(Atau jalankan `npm run dev` jika dalam mode pengembangan)*.

### 8. Jalankan Server Lokal
```bash
php artisan serve
```
Akses aplikasi melalui browser:
* **Halaman Pelanggan (Menu):** [http://127.0.0.1:8000](http://127.0.0.1:8000)
* **Halaman Login Admin:** [http://127.0.0.1:8000/login](http://127.0.0.1:8000/login)

---

## 🧪 Pengujian Fungsional (Automated Testing)

Proyek ini telah dilengkapi dengan suite pengujian otomatis fitur dan unit menggunakan PHPUnit:

```bash
php artisan test --compact
```
> **Hasil Pengujian:** 25 Tests Passed, 61 Assertions (Zero-Error Code).

---

## 📐 Konsep Penting & Best Practices yang Diterapkan

1. **Database Transaction (`DB::beginTransaction`, `DB::commit`, `DB::rollBack`):**  
   Menjamin proses penyimpanan data `orders` dan `order_details` bersifat atomik (*All-or-Nothing*). Jika terjadi kesalahan pada salah satu baris, seluruh transaksi dibatalkan sehingga database tidak korup.
2. **Eager Loading (`with('orderDetails.food')`):**  
   Mengambil data pesanan dan relasi makanannya dalam satu kali query SQL yang efisien untuk mengatasi masalah *N+1 Query Problem*.
3. **Mass Assignment Protection (`protected $guarded = ['id']`):**  
   Mengamankan model Eloquent dari eksploitasi pengisian data liar dengan hanya mengunci kolom `id`.
4. **File Storage Management:**  
   Menghapus file fisik dari harddisk server (`Storage::disk('public')->delete()`) saat foto makanan diperbarui atau dihapus.
5. **CSRF Protection & RESTful Method Spoofing:**  
   Pengamanan form dengan token `@csrf` dan penggunaan directive `@method('PUT')`, `@method('PATCH')`, dan `@method('DELETE')`.
