# MODUL 4: SISI ADMIN (Rekap Pesanan Masuk & Update Status)

### 1. File `resources/views/dashboard.blade.php`
Untuk menampilkan tabel pesanan makanan yang sudah masuk dari pelanggan:

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

### 2. Perbarui `routes/web.php`
```php
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\OrderController;

// 1. Halaman utama (Katalog Menu Pelanggan)
Route::get('/', [OrderController::class, 'index'])->name('customer.index');
Route::post('/checkout', [OrderController::class, 'store'])->name('customer.checkout');

// 2. Arahkan dashboard utama Breeze langsung ke Admin Dashboard Rekap Pesanan
Route::get('/dashboard', [OrderController::class, 'adminDashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// 3. Grup Rute Admin (Wajib Login)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Alias route untuk admin dashboard & update status pesanan
    Route::get('/admin/dashboard', [OrderController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::patch('/admin/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.updateStatus');
    
    // CRUD Makanan
    Route::resource('/admin/foods', FoodController::class);
});

require __DIR__.'/auth.php';
```
