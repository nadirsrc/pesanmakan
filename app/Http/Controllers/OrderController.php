<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Menampilkan katalog makanan untuk pelanggan (Halaman Utama).
     */
    public function index()
    {
        $foods = Food::all();

        return view('customer.index', compact('foods'));
    }

    /**
     * Memproses checkout pesanan pelanggan dan menyimpannya ke 2 tabel (orders & order_details).
     */
    public function store(Request $request)
    {
        // 1. Validasi Keamanan Input
        // CONTOH: Kalau pelanggan nekat checkout tapi nama pemesan kosong atau nomor meja diisi 0, form otomatis menolak.
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'table_number'  => 'required|integer|min:1',
            'items'         => 'required|array',
            'items.*'       => 'nullable|integer|min:0',
        ]);

        // 2. Filter hanya menu yang kuantitasnya > 0
        // CONTOH: Dari 10 menu yang ada di daftar, kalau pelanggan cuma pesan 2 Nasi Goreng dan 1 Es Teh, menu lain yang diisi 0 otomatis dibuang.
        $orderedItems = array_filter($request->items, fn ($qty) => $qty > 0);

        if (empty($orderedItems)) {
            return back()->with('error', 'Pilih minimal satu menu makanan!');
        }

        // 3. Memulai Sesi Database Transaction
        // CONTOH: Menjaga konsistensi data 2 tabel.
        // Jika penyimpanan tabel 'orders' sukses, TAPI tiba-tiba server mati saat menyimpan 'order_details',
        // maka rollBack() akan membatalkan (meng-UNDO) simpanan tabel orders. Jadi tidak ada pesanan 'hantu' yang rincian makanannya kosong.
        DB::beginTransaction();
        try {
            // A. Simpan Header Pesanan ke tabel 'orders'
            $order = Order::create([
                'customer_name' => $request->customer_name,
                'table_number'  => $request->table_number,
                'total_price'   => 0, // Nilai awal 0, dihitung dari akumulasi loop di bawah
                'status'        => 'pending',
            ]);

            $totalPrice = 0;

            // B. Looping setiap menu yang dipesan untuk disimpan ke tabel 'order_details'
            foreach ($orderedItems as $foodId => $quantity) {
                $food = Food::findOrFail($foodId);     // Ambil data harga master dari tabel foods
                $subtotal = $food->price * $quantity;  // CONTOH: Nasi Goreng (Rp 25.000) x 2 porsi = Rp 50.000
                $totalPrice += $subtotal;              // Akumulasikan ke total harga

                // Simpan baris detail pesanan
                OrderDetail::create([
                    'order_id' => $order->id,          // Foreign Key menghubungkan ke pesanan meja ini
                    'food_id'  => $food->id,           // Foreign Key menghubungkan ke master makanan
                    'quantity' => $quantity,           // 2 porsi
                    'subtotal' => $subtotal,           // Rp 50.000
                ]);
            }

            // C. Update total_price di tabel header 'orders' setelah semua item dihitung
            $order->update(['total_price' => $totalPrice]);

            // D. Jika seluruh baris berhasil tanpa ada satupun yang gagal, simpan permanen ke MySQL
            DB::commit();

            return redirect()->route('customer.index')->with('success', 'Pesanan berhasil dibuat! Nomor Meja: '.$order->table_number);
        } catch (\Exception $e) {
            // E. Jika ada error, batalkan semua perubahan (UNDO) agar database tetap bersih
            DB::rollBack();

            return back()->with('error', 'Gagal memproses pesanan: '.$e->getMessage());
        }
    }

    /**
     * Menampilkan rekap seluruh pesanan di Dashboard Admin.
     */
    public function adminDashboard()
    {
        // Eager Loading (with):
        // CONTOH: Mengambil data order sekaligus detail dan nama makanannya dalam 1 query SQL efisien.
        // Mencegah N+1 Problem (query database bolak-balik ratusan kali yang membuat website lelet).
        $orders = Order::with('orderDetails.food')->latest()->get();

        return view('dashboard', compact('orders'));
    }

    /**
     * Update status pesanan oleh Admin.
     * CONTOH: Mengubah status pesanan meja 5 dari 'Pending' menjadi 'completed' (Selesai/Lunas).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return back()->with('success', 'Status pesanan #'.$order->id.' berhasil diperbarui!');
    }
}
