# BERDASARKAN KISI-KISI 
## Spesifikasi Teknis & Kebutuhan Aplikasi
### A. Struktur Database (db_pemesanan_makanan)
Anda diminta membuat 3 tabel utama menggunakan fitur Migration: 
- **foods (Master Makanan):** `id`, `name`, `category` (Enum: Makanan, Minuman, Cemilan), `price`, `description`, `image`, `timestamps`. 
- **orders (Transaksi Pesanan):** `id`, `customer_name`, `table_number`, `total_price`, `status` (Enum: Pending, Diproses, Selesai), `timestamps`. 
- **order_details (Detail Transaksi):** `id`, `order_id` (Foreign Key), `food_id` (Foreign Key), `quantity`, `subtotal`, `timestamps`. 

### Peran & Alur Kerja Aplikasi (Workflow)
**Sisi Customer (Publik):**
- Melihat daftar menu makanan berdasarkan kategori. 
- Memilih makanan, menginput jumlah item, mengisi nama & nomor meja, lalu melakukan checkout pesanan. 

**Sisi Admin (Terproteksi/Login):**
- Melihat rekap pesanan yang masuk. 
- Mengelola data makanan melalui modul CRUD lengkap (Tambah, Lihat, Update, Hapus).

---

## Instruksi Kerja & Komponen Kunci Penilaian

| Langkah / Unit | Instruksi Utama |
| :--- | :--- |
| **1. Instalasi** | Menyiapkan XAMPP/Laragon, PHP 8.x, Composer, Node.js, VS Code. Menginstal Laravel baru dan konfigurasi .env. |
| **2. Struktur Data** | Membuat Migration (3 tabel + Relasi FK) dan Seeder (minimal 5 data dummy makanan). |
| **3. Logika Bisnis** | Menerapkan MVC, memisahkan routes Admin & Customer, menghitung total harga, dan menangani transaksi multi-tabel (DB::transaction). |
| **4. UI/UX** | Membuat layout responsif, form input CRUD (termasuk upload gambar & dropdown), serta rekap data dalam bentuk tabel rapi. |
| **5. Library/Package** | Memakai Framework CSS (Bootstrap/Tailwind) & Package Laravel (Laravel Breeze/Fortify atau fitur Validator/File upload). |
| **6. Best Practices** | Mengikuti standar penulisan PSR (StudlyCaps, camelCase), memasang proteksi @csrf, dan enkripsi password. |
| **7. Debugging** | Pengujian fungsionalitas hingga bebas bug/error (Zero-Error Code). |
| **8. Dokumentasi** | Menambahkan komentar pada method Controller (PHPDoc/Inline) & membuat file README.md (petunjuk instalasi, akun admin) |

---

# PERSIAPAN DAN SET UP PROYEK
**Jalankan Web Server = Xampp**

**Buka cmd buat folder Laravel:**
```bash
composer create-project laravel/laravel nama-anda_JWP_MUK
```

**Buka folder Laravel “ code .”:**
```bash
code .
```

**Konfigurasi nama database pada file `.env` dengan nama database db_pemesanan_makanan (atau pesanmakan):**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pesanmakan
DB_USERNAME=root
DB_PASSWORD=
```

---

# Database & Model (Migration & Seeder)
**Buat model, migration, dan controller sekaligus lewat terminal:**
```bash
php artisan make:model Food -mcr
php artisan make:model Order -mcr
php artisan make:model OrderDetail -m
```

---

### MIGRATION TABLE foods
Buka file `database/migrations/xxxx_xx_xx_xxxxxx_create_food_table.php`  
*(PENTING: Pastikan nama tabel di Schema::create adalah `'foods'` dengan huruf s, agar relasi foreign key di tabel order_details tidak error)*  
Ganti sesuai dengan soal:

```php
public function up(): void
{
    Schema::create('foods', function (Blueprint $table) {
        $table->id(); // Primary Key
        $table->string('name');
        $table->enum('category', ['Makanan', 'Minuman', 'Cemilan']);
        $table->integer('price');
        $table->text('description');
        $table->string('image')->nullable(); // Boleh kosong/null
        $table->timestamps(); // created_at & updated_at
    });
}

public function down(): void
{
    Schema::dropIfExists('foods');
}
```

---

### Migration table orders
Buka file `database/migrations/xxxx_xx_xx_xxxxxx_create_orders_table.php`

```php
public function up(): void
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id(); // Primary Key
        $table->string('customer_name');
        $table->string('table_number'); // Bisa Integer/Varchar (disarankan string/varchar)
        $table->integer('total_price');
        $table->string('status')->default('Pending');
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('orders');
}
```

---

### Migration table order_details
**Migration Tabel order_details (Detail Transaksi + Foreign Key)**  
File ini berada di folder `database/migrations/xxxx_xx_xx_xxxxxx_create_order_details_table.php`

```php
public function up(): void
{
    Schema::create('order_details', function (Blueprint $table) {
        $table->id();

        // Foreign Key ke tabel orders
        $table->foreignId('order_id')
              ->constrained('orders')
              ->onDelete('cascade');

        // Foreign Key ke tabel foods
        $table->foreignId('food_id')
              ->constrained('foods')
              ->onDelete('cascade');

        $table->integer('quantity');
        $table->integer('subtotal');
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('order_details');
}
```

**Buka terminal, jalankan:**
```bash
php artisan migrate
```

---

### Seeder
Seeder digunakan untuk menginput minimal 5 data menu awal ke database secara otomatis.  
**Buat File Seeder via Terminal Jalankan perintah berikut di terminal VS Code:**
```bash
php artisan make:seeder FoodSeeder
```

**Buka file `database/seeders/FoodSeeder.php`:**
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // <-- TAMBAHKAN BARIS INI

class FoodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('foods')->insert([
            [
                'name' => 'Nasi Goreng Spesial',
                'category' => 'Makanan',
                'price' => 25000,
                'description' => 'Nasi goreng dengan telur, ayam suwir, dan kerupuk.',
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mie Goreng Seafood',
                'category' => 'Makanan',
                'price' => 28000,
                'description' => 'Mie goreng pedas dengan udang dan cumi.',
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Es Teh Manis',
                'category' => 'Minuman',
                'price' => 5000,
                'description' => 'Es teh melati segar.',
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jus Alpukat',
                'category' => 'Minuman',
                'price' => 15000,
                'description' => 'Jus alpukat murni dengan susu cokelat.',
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kentang Goreng',
                'category' => 'Cemilan',
                'price' => 12000,
                'description' => 'Kentang goreng renyah dengan saus keju.',
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
```

**Daftarkan Seeder di `database/seeders/DatabaseSeeder.php`:**  
Buka file `DatabaseSeeder.php`, lalu tambahkan pemanggilan `FoodSeeder` di dalam method `run()`:
```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat User Admin Default
        User::create([
            'name' => 'Admin Toko',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        // 2. Panggil Seeder Makanan
        $this->call([
            FoodSeeder::class,
        ]);
    }
}
```

**JALANKAN PERINTAH:**
```bash
php artisan migrate:fresh --seed
```

---

# MODEL 
### Setup Relasi Eloquent Model
Buka masing-masing file model di folder `app/Models/`

**1. `app/Models/Food.php`**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;

    protected $table = 'foods';
    protected $guarded = ['id'];
}
```

**2. `app/Models/Order.php`**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi: 1 Pesanan punya banyak detail item
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }
}
```

**3. `app/Models/OrderDetail.php`**
*(Catatan Penting: Pastikan nama class ditulis `OrderDetail` dengan huruf besar D, dan beri baris `protected $guarded = ['id'];` agar kolom subtotal pesanan otomatis tersimpan).*
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $table = 'order_details';
    protected $guarded = ['id'];

    // Relasi balik ke model Food
    public function food()
    {
        return $this->belongsTo(Food::class, 'food_id');
    }

    // Relasi balik ke model Order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
```
