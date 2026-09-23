# 🚀 PANDUAN RINGKAS & CEPAT: MODUL 1 - 4 (JWP MUK LARAVEL)
**Aplikasi Pemesanan Makanan Restoran (100% Bebas Error)**

---

## 📌 DAFTAR ISI
1. [MODUL 1: Setup Proyek, Database (Migration & Seeder), dan Model](#-modul-1-setup-database--model)
2. [MODUL 2: Autentikasi Admin & CRUD Master Makanan](#-modul-2-autentikasi-admin--crud-makanan)
3. [MODUL 3: Sisi Customer (Katalog Menu & Transaksi Checkout)](#-modul-3-sisi-customer-katalog--checkout)
4. [MODUL 4: Sisi Admin (Rekap Pesanan Masuk & Update Status)](#-modul-4-sisi-admin-rekap-pesanan--update-status)
5. [⚡ Rangkuman Jebakan Error & Tips Anti-Gagal](#-rangkuman-jebakan-error--tips-anti-gagal)

---

# 📦 MODUL 1: Setup, Database & Model

### Langkah 1.1: Setup .env & Buat Database
1. Buka file `.env`, pastikan konfigurasi koneksi database:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pesanmakan
   DB_USERNAME=root
   DB_PASSWORD=
   ```
2. Buat database `pesanmakan` di phpMyAdmin / MySQL.

---

### Langkah 1.2: Generate Model, Migration, dan Controller Sekaligus
Jalankan 3 perintah praktis ini di terminal:
```bash
php artisan make:model Food -mcr
php artisan make:model Order -mcr
php artisan make:model OrderDetail -m
```

---

### Langkah 1.3: Isi File Migration

#### 1. `database/migrations/xxxx_create_food_table.php`
> ⚠️ **PERHATIAN KHUSUS (Jebakan Bawaan Laravel):**  
> Saat menjalankan `php artisan make:model Food -mcr`, Laravel otomatis membuat nama tabel `'food'` (tanpa huruf *s*).  
> **Wajib ubah teks `'food'` menjadi `'foods'` (tambah huruf *s*)** di `Schema::create('foods', ...)` dan `Schema::dropIfExists('foods')` agar sinkron dengan relasi *foreign key* di tabel `order_details`!

```php
public function up(): void
{
    Schema::create('foods', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->enum('category', ['Makanan', 'Minuman', 'Cemilan']);
        $table->integer('price');
        $table->text('description');
        $table->string('image')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('foods');
}
```

#### 2. `database/migrations/xxxx_create_orders_table.php`
```php
public function up(): void
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->string('customer_name');
        $table->string('table_number'); // Nomor meja
        $table->integer('total_price');
        $table->enum('status', ['Pending', 'Diproses', 'Selesai'])->default('Pending');
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('orders');
}
```

#### 3. `database/migrations/xxxx_create_order_details_table.php`
```php
public function up(): void
{
    Schema::create('order_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
        $table->foreignId('food_id')->constrained('foods')->onDelete('cascade');
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

---

### Langkah 1.4: Buat Seeder Data Awal (Minimal 5 Menu)
1. Buat seeder di terminal:
   ```bash
   php artisan make:seeder FoodSeeder
   ```
2. Isi file `database/seeders/FoodSeeder.php`:
   ```php
   <?php

   namespace Database\Seeders;

   use Illuminate\Database\Seeder;
   use Illuminate\Support\Facades\DB;

   class FoodSeeder extends Seeder
   {
       public function run(): void
       {
           DB::table('foods')->insert([
               [
                   'name' => 'Nasi Goreng Spesial',
                   'category' => 'Makanan',
                   'price' => 25000,
                   'description' => 'Nasi goreng dengan telur dan ayam.',
                   'image' => null,
                   'created_at' => now(),
                   'updated_at' => now(),
               ],
               [
                   'name' => 'Mie Goreng Seafood',
                   'category' => 'Makanan',
                   'price' => 28000,
                   'description' => 'Mie goreng pedas dengan udang.',
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
                   'description' => 'Jus alpukat susu cokelat.',
                   'image' => null,
                   'created_at' => now(),
                   'updated_at' => now(),
               ],
               [
                   'name' => 'Kentang Goreng',
                   'category' => 'Cemilan',
                   'price' => 12000,
                   'description' => 'Kentang goreng renyah saus keju.',
                   'image' => null,
                   'created_at' => now(),
                   'updated_at' => now(),
               ],
           ]);
       }
   }
   ```
3. Daftarkan di `database/seeders/DatabaseSeeder.php`:
   ```php
   <?php

   namespace Database\Seeders;

   use App\Models\User;
   use Illuminate\Database\Seeder;
   use Illuminate\Support\Facades\Hash;

   class DatabaseSeeder extends Seeder
   {
       public function run(): void
       {
           // 1. Akun Admin Toko Default
           User::create([
               'name' => 'Admin Toko',
               'email' => 'admin@gmail.com',
               'password' => Hash::make('password123'),
           ]);

           // 2. Jalankan Seeder Menu Makanan
           $this->call([
               FoodSeeder::class,
           ]);
       }
   }
   ```
4. Jalankan migrasi dan seeder:
   ```bash
   php artisan migrate:fresh --seed
   ```

---

### Langkah 1.5: Setup Model & Relasi Eloquent

#### 1. `app/Models/Food.php`
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

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'food_id');
    }
}
```

#### 2. `app/Models/Order.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }
}
```

#### 3. `app/Models/OrderDetail.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $table = 'order_details';
    protected $guarded = ['id']; // ✅ Menggunakan guarded agar kolom 'subtotal' otomatis diizinkan masuk database

    public function food()
    {
        return $this->belongsTo(Food::class, 'food_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
```

---

# 🔐 MODUL 2: Autentikasi Admin & CRUD Makanan

### Langkah 2.1: Pasang Laravel Breeze
Jalankan di terminal secara berurutan:
```bash
composer require laravel/breeze --dev
php artisan breeze:install blade --no-dark
php artisan storage:link
npm install
npm run build
```

---

### Langkah 2.2: Isi `app/Http/Controllers/FoodController.php`
```php
<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FoodController extends Controller
{
    public function index()
    {
        $foods = Food::latest()->paginate(10);
        return view('admin.foods.index', compact('foods'));
    }

    public function create()
    {
        return view('admin.foods.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|in:Makanan,Minuman,Cemilan',
            'price'       => 'required|numeric|min:0',
            'description' => 'required|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('foods', 'public');
        }

        Food::create([
            'name'        => $request->name,
            'category'    => $request->category,
            'price'       => $request->price,
            'description' => $request->description,
            'image'       => $imagePath,
        ]);

        return redirect()->route('foods.index')->with('success', 'Data makanan berhasil ditambahkan!');
    }

    public function edit(Food $food)
    {
        return view('admin.foods.edit', compact('food'));
    }

    public function update(Request $request, Food $food)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|in:Makanan,Minuman,Cemilan',
            'price'       => 'required|numeric|min:0',
            'description' => 'required|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $imagePath = $food->image;
        if ($request->hasFile('image')) {
            if ($food->image && Storage::disk('public')->exists($food->image)) {
                Storage::disk('public')->delete($food->image);
            }
            $imagePath = $request->file('image')->store('foods', 'public');
        }

        $food->update([
            'name'        => $request->name,
            'category'    => $request->category,
            'price'       => $request->price,
            'description' => $request->description,
            'image'       => $imagePath,
        ]);

        return redirect()->route('foods.index')->with('success', 'Data makanan berhasil diperbarui!');
    }

    public function destroy(Food $food)
    {
        if ($food->image && Storage::disk('public')->exists($food->image)) {
            Storage::disk('public')->delete($food->image);
        }
        $food->delete();

        return redirect()->route('foods.index')->with('success', 'Data makanan berhasil dihapus!');
    }
}
```

---

### Langkah 2.3: Buat 3 View Admin di `resources/views/admin/foods/`

#### 1. `resources/views/admin/foods/index.blade.php`
```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Master Data Makanan</h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <a href="{{ route('foods.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mb-4 inline-block font-semibold">+ Tambah Makanan</a>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <table class="w-full bg-white border mt-4 shadow-sm rounded-lg overflow-hidden">
            <thead>
                <tr class="bg-gray-100 border-b text-gray-600 text-sm">
                    <th class="p-3">Gambar</th>
                    <th class="p-3">Nama</th>
                    <th class="p-3">Kategori</th>
                    <th class="p-3">Harga</th>
                    <th class="p-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($foods as $food)
                <tr class="border-b text-center text-sm">
                    <td class="p-3">
                        @if($food->image)
                            <img src="{{ asset('storage/' . $food->image) }}" class="w-16 h-16 object-cover mx-auto rounded">
                        @else
                            <span class="text-gray-400 text-xs">No Image</span>
                        @endif
                    </td>
                    <td class="p-3 font-semibold">{{ $food->name }}</td>
                    <td class="p-3">{{ $food->category }}</td>
                    <td class="p-3 font-bold text-green-600">Rp {{ number_format($food->price) }}</td>
                    <td class="p-3">
                        <a href="{{ route('foods.edit', $food->id) }}" class="text-blue-600 hover:underline mr-3 font-semibold">Edit</a>
                        <form action="{{ route('foods.destroy', $food->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Yakin hapus data ini?')" class="text-red-600 hover:underline font-semibold">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $foods->links() }}</div>
    </div>
</x-app-layout>
```

#### 2. `resources/views/admin/foods/create.blade.php`
```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Makanan</h2>
    </x-slot>

    <div class="py-12 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <form action="{{ route('foods.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-sm border">
            @csrf
            <div class="mb-4">
                <label class="block font-medium mb-1">Nama Makanan</label>
                <input type="text" name="name" class="w-full border rounded p-2" required>
            </div>
            <div class="mb-4">
                <label class="block font-medium mb-1">Kategori</label>
                <select name="category" class="w-full border rounded p-2" required>
                    <option value="Makanan">Makanan</option>
                    <option value="Minuman">Minuman</option>
                    <option value="Cemilan">Cemilan</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block font-medium mb-1">Harga (Rp)</label>
                <input type="number" name="price" class="w-full border rounded p-2" required>
            </div>
            <div class="mb-4">
                <label class="block font-medium mb-1">Deskripsi</label>
                <textarea name="description" class="w-full border rounded p-2" rows="3" required></textarea>
            </div>
            <div class="mb-4">
                <label class="block font-medium mb-1">Gambar Makanan</label>
                <input type="file" name="image" class="w-full border rounded p-2">
            </div>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded font-semibold">Simpan Data</button>
        </form>
    </div>
</x-app-layout>
```

#### 3. `resources/views/admin/foods/edit.blade.php`
```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Makanan</h2>
    </x-slot>

    <div class="py-12 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <form action="{{ route('foods.update', $food->id) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-sm border">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block font-medium mb-1">Nama Makanan</label>
                <input type="text" name="name" value="{{ $food->name }}" class="w-full border rounded p-2" required>
            </div>
            <div class="mb-4">
                <label class="block font-medium mb-1">Kategori</label>
                <select name="category" class="w-full border rounded p-2" required>
                    <option value="Makanan" {{ $food->category == 'Makanan' ? 'selected' : '' }}>Makanan</option>
                    <option value="Minuman" {{ $food->category == 'Minuman' ? 'selected' : '' }}>Minuman</option>
                    <option value="Cemilan" {{ $food->category == 'Cemilan' ? 'selected' : '' }}>Cemilan</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block font-medium mb-1">Harga (Rp)</label>
                <input type="number" name="price" value="{{ $food->price }}" class="w-full border rounded p-2" required>
            </div>
            <div class="mb-4">
                <label class="block font-medium mb-1">Deskripsi</label>
                <textarea name="description" class="w-full border rounded p-2" rows="3" required>{{ $food->description }}</textarea>
            </div>
            <div class="mb-4">
                <label class="block font-medium mb-1">Gambar Baru (Opsional)</label>
                <input type="file" name="image" class="w-full border rounded p-2">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded font-semibold">Update Data</button>
        </form>
    </div>
</x-app-layout>
```

---

# 🛒 MODUL 3: Sisi Customer (Katalog & Checkout)

### Langkah 3.1: Isi `app/Http/Controllers/OrderController.php`
```php
<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // Menampilkan katalog makanan untuk pelanggan (Halaman Utama)
    public function index()
    {
        $foods = Food::all();
        return view('customer.index', compact('foods'));
    }

    // Memproses checkout pesanan pelanggan
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'table_number'  => 'required|integer|min:1',
            'items'         => 'required|array',
            'items.*'       => 'nullable|integer|min:0',
        ]);

        $orderedItems = array_filter($request->items, fn ($qty) => $qty > 0);

        if (empty($orderedItems)) {
            return back()->with('error', 'Pilih minimal satu menu makanan!');
        }

        DB::beginTransaction();
        try {
            // 1. Simpan Header Order
            $order = Order::create([
                'customer_name' => $request->customer_name,
                'table_number'  => $request->table_number,
                'total_price'   => 0,
                'status'        => 'pending',
            ]);

            $totalPrice = 0;

            // 2. Simpan Detail Order (Menggunakan kolom 'subtotal' yang sesuai tabel database)
            foreach ($orderedItems as $foodId => $quantity) {
                $food = Food::findOrFail($foodId);
                $subtotal = $food->price * $quantity;
                $totalPrice += $subtotal;

                OrderDetail::create([
                    'order_id' => $order->id,
                    'food_id'  => $food->id,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal, // ✅ Simpan ke kolom subtotal
                ]);
            }

            // 3. Update Total Harga di Header
            $order->update(['total_price' => $totalPrice]);

            DB::commit();

            return redirect()->route('customer.index')->with('success', 'Pesanan berhasil dibuat! Nomor Meja: ' . $order->table_number);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }

    // Menampilkan rekap pesanan di Dashboard Admin
    public function adminDashboard()
    {
        $orders = Order::with('orderDetails.food')->latest()->get();
        return view('dashboard', compact('orders'));
    }

    // Update status pesanan oleh Admin
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return back()->with('success', 'Status pesanan #' . $order->id . ' berhasil diperbarui!');
    }
}
```

---

### Langkah 3.2: Buat View `resources/views/customer/index.blade.php`
```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Restoran</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 font-sans">
    <div class="max-w-5xl mx-auto">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Menu Restoran</h1>
            <p class="text-gray-500 text-sm mt-1">Pilih menu makanan dan masukkan nomor meja Anda</p>
        </div>

        <!-- NOTIFIKASI SUCCESS / ERROR / VALIDASI -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 text-center font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6 text-center font-semibold">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6 font-semibold">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- FILTER KATEGORI (JAVASCRIPT) -->
        <div class="flex flex-wrap justify-center gap-3 mb-8">
            <button type="button" onclick="filterCategory('all', this)" class="btn-category px-5 py-2 rounded-full font-semibold text-sm transition bg-blue-600 text-white shadow-md">Semua Menu</button>
            <button type="button" onclick="filterCategory('Makanan', this)" class="btn-category px-5 py-2 rounded-full font-semibold text-sm transition bg-white text-gray-600 hover:bg-gray-200 border">Makanan</button>
            <button type="button" onclick="filterCategory('Minuman', this)" class="btn-category px-5 py-2 rounded-full font-semibold text-sm transition bg-white text-gray-600 hover:bg-gray-200 border">Minuman</button>
            <button type="button" onclick="filterCategory('Cemilan', this)" class="btn-category px-5 py-2 rounded-full font-semibold text-sm transition bg-white text-gray-600 hover:bg-gray-200 border">Cemilan</button>
        </div>

        <form id="orderForm" action="{{ route('customer.checkout') }}" method="POST">
            @csrf

            <!-- 1. Informasi Pemesan -->
            <div class="bg-white p-6 rounded-xl shadow-sm border mb-6">
                <h2 class="text-lg font-bold text-gray-700 mb-4 pb-2 border-b">1. Informasi Pemesan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Nama Lengkap</label>
                        <input type="text" id="customer_name" name="customer_name" required placeholder="Masukkan nama pemesan" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Nomor Meja</label>
                        <input type="number" id="table_number" name="table_number" required placeholder="Contoh: 05" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
            </div>

            <!-- 2. Pilih Menu -->
            <h2 class="text-lg font-bold text-gray-700 mb-4">2. Pilih Menu</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($foods as $food)
                    <div class="food-card bg-white rounded-xl shadow-sm border overflow-hidden flex flex-col justify-between" data-category="{{ $food->category ?? 'Makanan' }}">
                        <div>
                            @if($food->image)
                                <img src="{{ asset('storage/' . $food->image) }}" alt="{{ $food->name }}" class="w-full h-40 object-cover">
                            @else
                                <div class="bg-gray-200 h-40 flex items-center justify-center text-gray-400 font-medium">Tanpa Gambar</div>
                            @endif

                            <div class="p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs bg-blue-100 text-blue-700 font-semibold px-2.5 py-0.5 rounded">{{ $food->category ?? 'Makanan' }}</span>
                                    <span class="font-bold text-green-600">Rp {{ number_format($food->price) }}</span>
                                </div>
                                <h3 class="font-bold text-gray-800 text-lg item-name">{{ $food->name }}</h3>
                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $food->description }}</p>
                            </div>
                        </div>

                        <div class="p-4 bg-gray-50 border-t">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Jumlah Porsi</label>
                            <input type="number" name="items[{{ $food->id }}]" min="0" value="0" data-name="{{ $food->name }}" data-price="{{ $food->price }}" class="item-qty w-full border rounded-lg px-3 py-1.5 text-center font-bold text-gray-700 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="emptyCategoryMessage" class="hidden bg-white p-8 rounded-xl border text-center text-gray-500 font-medium mt-4">
                Menu untuk kategori ini belum tersedia.
            </div>

            <div class="mt-8 text-right">
                <button type="button" onclick="showConfirmationModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3 rounded-xl shadow-md transition">
                    Pesan Sekarang
                </button>
            </div>

            <!-- MODAL KONFIRMASI PESANAN (BERADA DI DALAM FORM) -->
            <div id="confirmModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
                <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl transform transition-all">
                    <h3 class="text-xl font-bold text-gray-800 border-b pb-3 mb-4">Konfirmasi Pesanan</h3>
                    
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <div class="flex justify-between"><span class="font-semibold">Nama:</span> <span id="modalName" class="text-gray-900 font-bold"></span></div>
                        <div class="flex justify-between"><span class="font-semibold">No. Meja:</span> <span id="modalTable" class="text-gray-900 font-bold"></span></div>
                    </div>

                    <div class="border-t border-b py-3 mb-4 max-h-48 overflow-y-auto">
                        <p class="font-semibold text-xs text-gray-400 uppercase mb-2">Rincian Item</p>
                        <ul id="modalItemList" class="space-y-2 text-sm"></ul>
                    </div>

                    <div class="flex justify-between items-center text-lg font-bold text-gray-800 mb-6">
                        <span>Total Pembayaran:</span>
                        <span id="modalTotalPrice" class="text-green-600 text-xl">Rp 0</span>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="closeConfirmationModal()" class="w-1/2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2.5 rounded-xl transition">
                            Batal
                        </button>
                        <!-- ✅ Tombol submit HTML5 native (Anti-gagal) -->
                        <button type="submit" class="w-1/2 bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 rounded-xl shadow transition">
                            Ya, Kirim Pesanan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- SCRIPT JAVASCRIPT -->
    <script>
        function filterCategory(category, element) {
            document.querySelectorAll('.btn-category').forEach(btn => {
                btn.className = "btn-category px-5 py-2 rounded-full font-semibold text-sm transition bg-white text-gray-600 hover:bg-gray-200 border";
            });
            element.className = "btn-category px-5 py-2 rounded-full font-semibold text-sm transition bg-blue-600 text-white shadow-md";

            const cards = document.querySelectorAll('.food-card');
            let visibleCount = 0;
            cards.forEach(card => {
                const cardCategory = card.getAttribute('data-category');
                if (category === 'all' || cardCategory === category) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            const emptyMsg = document.getElementById('emptyCategoryMessage');
            if (visibleCount === 0) {
                emptyMsg.classList.remove('hidden');
            } else {
                emptyMsg.classList.add('hidden');
            }
        }

        function showConfirmationModal() {
            const name = document.getElementById('customer_name').value.trim();
            const table = document.getElementById('table_number').value.trim();

            if (!name || !table) {
                alert('Silakan isi Nama Lengkap dan Nomor Meja terlebih dahulu!');
                return;
            }

            const items = document.querySelectorAll('.item-qty');
            let itemListHtml = '';
            let grandTotal = 0;
            let hasOrder = false;

            items.forEach(input => {
                const qty = parseInt(input.value) || 0;
                if (qty > 0) {
                    hasOrder = true;
                    const name = input.getAttribute('data-name');
                    const price = parseFloat(input.getAttribute('data-price'));
                    const subtotal = qty * price;
                    grandTotal += subtotal;

                    itemListHtml += `
                        <li class="flex justify-between items-center">
                            <div>
                                <span class="font-bold text-gray-800">${name}</span>
                                <span class="text-xs text-gray-500 block">x${qty} @ Rp ${price.toLocaleString('id-ID')}</span>
                            </div>
                            <span class="font-semibold text-gray-700">Rp ${subtotal.toLocaleString('id-ID')}</span>
                        </li>
                    `;
                }
            });

            if (!hasOrder) {
                alert('Pilih minimal 1 menu makanan/minuman dengan jumlah lebih dari 0!');
                return;
            }

            document.getElementById('modalName').textContent = name;
            document.getElementById('modalTable').textContent = table;
            document.getElementById('modalItemList').innerHTML = itemListHtml;
            document.getElementById('modalTotalPrice').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');

            const modal = document.getElementById('confirmModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeConfirmationModal() {
            const modal = document.getElementById('confirmModal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }
    </script>
</body>
</html>
```

---

# 📊 MODUL 4: Sisi Admin (Rekap Pesanan & Update Status)

### Langkah 4.1: Update Tampilan `resources/views/dashboard.blade.php`
```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Pesanan Masuk') }}
            </h2>
            <!-- Group Navigasi Tombol Admin -->
            <div class="flex items-center gap-3">
                <a href="{{ route('foods.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 shadow-sm transition">
                    Kelola Menu
                </a>
                <a href="{{ route('customer.index') }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 shadow-sm transition">
                    Lihat Menu Customer
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                            <tr>
                                <th class="p-4 border-b"># ID</th>
                                <th class="p-4 border-b">Pelanggan</th>
                                <th class="p-4 border-b">No. Meja</th>
                                <th class="p-4 border-b">Rincian Pesanan</th>
                                <th class="p-4 border-b">Total Harga</th>
                                <th class="p-4 border-b">Status</th>
                                <th class="p-4 border-b text-center">Aksi Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-sm">
                            @forelse($orders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="p-4 font-bold text-gray-700">#{{ $order->id }}</td>
                                    <td class="p-4 font-medium">{{ $order->customer_name }}</td>
                                    <td class="p-4">
                                        <span class="bg-blue-100 text-blue-800 font-bold px-2.5 py-1 rounded-full text-xs">
                                            Meja {{ $order->table_number }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <ul class="list-disc list-inside space-y-1 text-gray-600">
                                            @foreach($order->orderDetails as $detail)
                                                <li>
                                                    <strong>{{ $detail->food->name ?? 'Menu' }}</strong> 
                                                    x{{ $detail->quantity }} 
                                                    <span class="text-xs text-gray-400">(Rp {{ number_format($detail->subtotal) }})</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="p-4 font-bold text-green-600">
                                        Rp {{ number_format($order->total_price) }}
                                    </td>
                                    <td class="p-4">
                                        @if(strtolower($order->status) == 'pending')
                                            <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2.5 py-1 rounded">PENDING</span>
                                        @elseif(strtolower($order->status) == 'completed' || strtolower($order->status) == 'selesai')
                                            <span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-1 rounded">SELESAI</span>
                                        @else
                                            <span class="bg-red-100 text-red-800 text-xs font-bold px-2.5 py-1 rounded">{{ strtoupper($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-center">
                                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" onchange="this.form.submit()" class="text-xs border border-gray-300 rounded p-1.5 bg-white shadow-sm font-semibold">
                                                <option value="pending" {{ strtolower($order->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="completed" {{ strtolower($order->status) == 'completed' || strtolower($order->status) == 'selesai' ? 'selected' : '' }}>Selesai / Lunas</option>
                                                <option value="cancelled" {{ strtolower($order->status) == 'cancelled' || strtolower($order->status) == 'batal' ? 'selected' : '' }}>Batalkan</option>
                                            </select>  
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-6 text-center text-gray-500">Belum ada pesanan masuk.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
```

---

### Langkah 4.2: Update File `routes/web.php` (Final Lengkap Modul 1 - 4)
```php
<?php

use App\Http\Controllers\FoodController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// 1. Halaman Utama (Katalog Menu Pelanggan)
Route::get('/', [OrderController::class, 'index'])->name('customer.index');
Route::post('/checkout', [OrderController::class, 'store'])->name('customer.checkout');

// 2. Arahkan Dashboard Utama Breeze ke Admin Dashboard Rekap Pesanan
Route::get('/dashboard', [OrderController::class, 'adminDashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// 3. Rute Grup Admin (Wajib Login)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Alias route untuk dashboard admin & update status pesanan
    Route::get('/admin/dashboard', [OrderController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::patch('/admin/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.updateStatus');
    
    // CRUD Makanan
    Route::resource('/admin/foods', FoodController::class);
});

require __DIR__.'/auth.php';
```

---

# ⚡ RANGKUMAN JEBAKAN ERROR & TIPS ANTI-GAGAL

1. **Jebakan Nama Tabel `food` vs `foods`:**
   * Di file migrasi makanan, selalu pastikan tabelnya bernama **`foods`** (`Schema::create('foods', ...)`).
   * Di file migrasi `order_details` tulis `->constrained('foods')`.
   * Di model `Food.php` beri `protected $table = 'foods';`.
2. **Jebakan `subtotal` vs `price` di `order_details`:**
   * Kolom tabel database bernama `subtotal`.
   * Di `OrderController.php` selalu simpan `'subtotal' => $subtotal`.
   * Di Model `OrderDetail.php` gunakan `protected $guarded = ['id'];`.
3. **Jebakan Gambar Tidak Muncul:**
   * Selalu jalankan `php artisan storage:link`.
4. **Jebakan Tombol CSS Putih / Blank:**
   * Jalankan `npm run dev` di tab terminal kedua.
5. **Perintah Cepat Reset & Seed Database Kapan Saja:**
   ```bash
   php artisan migrate:fresh --seed
   ```
