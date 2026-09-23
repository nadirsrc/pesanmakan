# MODUL 2: AUTENTIKASI ADMIN & CRUD MASTER MAKANAN

### 1. Instal Laravel Breeze via Composer:
```bash
composer require laravel/breeze --dev
```
*(Catatan: wajib pakai dua strip `--dev`, bukan satu strip `-dev`)*

---

### 2. Jalankan Perintah Instalasi Breeze:
```bash
php artisan breeze:install
```
- pilih: `blade`
- dark mode?: `NO`
- framework do you prefer?: `0` (PHPUnit)

**Langkah Wajib Setelah Instalasi Selesai:**  
Jalankan 4 perintah ini secara berurutan di terminal:
```bash
php artisan migrate
php artisan storage:link
npm install
npm run build
```

---

### 3. Isi File `app/Http/Controllers/FoodController.php`
Buka file `FoodController.php` dan ganti kodenya menjadi seperti di bawah ini. Kode ini mengelola logika CRUD Master Makanan (Sisi Admin) beserta fitur upload gambar:

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

### 4. Membuat Tampilan Admin View CRUD Makanan
Buat folder baru di `resources/views/admin/foods/`, lalu buat 3 file Blade berikut:

**1. `resources/views/admin/foods/index.blade.php` (Daftar Makanan):**
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
                            <button type="submit" onclick="return confirm('Hapus data ini?')" class="text-red-600 hover:underline font-semibold">Hapus</button>
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

**2. `resources/views/admin/foods/create.blade.php` (Form Tambah Makanan):**
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

**3. `resources/views/admin/foods/edit.blade.php` (Edit Makanan):**
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

### 5. Update `database/seeders/DatabaseSeeder.php`
Buka file `DatabaseSeeder.php`, pastikan pendaftaran akun admin default sudah dibuat:
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
Lalu jalankan di terminal:
```bash
php artisan migrate:fresh --seed
```

---

### 6. PENTING: Perbarui `routes/web.php` (Daftarkan Route CRUD)
Buka file **`routes/web.php`**, tambahkan baris `use App\Http\Controllers\FoodController;` di atas dan daftarkan `Route::resource('/admin/foods', FoodController::class);` di dalam grup `Route::middleware('auth')`:

```php
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FoodController; // <-- TAMBAHKAN INI

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // <-- TAMBAHKAN ROUTE RESOURCE CRUD MAKANAN INI
    Route::resource('/admin/foods', FoodController::class);
});

require __DIR__.'/auth.php';
```

---

### 7. PENTING: Menambahkan Menu Navigasi di Admin View (`navigation.blade.php`)
Agar setelah admin login tidak perlu mengetikkan URL `/admin/foods` secara manual di browser, tambahkan link navigasi di navbar Breeze:

Buka file **`resources/views/layouts/navigation.blade.php`**, cari bagian `<!-- Navigation Links -->`, lalu tambahkan menu `Kelola Makanan`:

```blade
<!-- Navigation Links -->
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
        {{ __('Dashboard') }}
    </x-nav-link>

    <!-- TAMBAHKAN ROUTE LINK MENU KELOLA MAKANAN INI -->
    <x-nav-link :href="route('foods.index')" :active="request()->routeIs('foods.*')">
        {{ __('Kelola Makanan') }}
    </x-nav-link>
</div>
```

---

### 8. Hubungkan Storage & Uji Coba CRUD Makanan
Pastikan symbolic link storage sudah dibuat:
```bash
php artisan storage:link
```

Sekarang jalankan `php artisan serve` dan buka browser ke `http://127.0.0.1:8000/login`:
- **Email:** `admin@gmail.com`
- **Password:** `password123`

Setelah login ke Dashboard, klik tombol menu **"Kelola Makanan"** di bagian navbar atas untuk menguji fitur Tambah, Edit, Upload Gambar, dan Hapus makanan!
