<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FoodController extends Controller
{
    /**
     * Menampilkan daftar master data makanan (admin).
     */
    public function index()
    {
        $foods = Food::latest()->paginate(10);

        return view('admin.foods.index', compact('foods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.foods.create');
    }

    /**
     * Menyimpan data makanan baru yang diinput admin ke database.
     */
     public function store(Request $request)
     {
         // 1. Validasi Keamanan Input
         // CONTOH: Kalau harga diisi huruf 'seratus' atau angka minus (-5000), Laravel otomatis nolak karena wajib 'numeric|min:0'.
         // CONTOH: Kalau admin upload file PDF atau file virus .exe, ditolak karena wajib 'image|mimes:jpeg,png,jpg'.
         // CONTOH: Ukuran file dibatasi maks 2048 KB (2MB) agar harddisk server tidak cepat penuh.
         $request->validate([
             'name'        => 'required|string|max:255',
             'category'    => 'required|in:Makanan,Minuman,Cemilan',
             'price'       => 'required|numeric|min:0',
             'description' => 'required|string',
             'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
         ]);

         // 2. Proses Upload Gambar jika ada
         // CONTOH: File foto disimpan ke folder 'storage/app/public/foods' dan diberi nama acak unik (misal: 'foods/xY87z.jpg') agar nama file tidak bentrok.
         $imagePath = null;
         if ($request->hasFile('image')) {
             $imagePath = $request->file('image')->store('foods', 'public');
         }

         // 3. Simpan data makanan ke database MySQL
         Food::create([
             'name'        => $request->name,
             'category'    => $request->category,
             'price'       => $request->price,
             'description' => $request->description,
             'image'       => $imagePath,
         ]);

         return redirect()->route('foods.index')->with('success', 'Data makanan berhasil ditambahkan!');
     }

    /**
     * Display the specified resource.
     */
    public function show(Food $food)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Food $food)
    {
        return view('admin.foods.edit', compact('food'));
    }

    /**
     * Memperbarui data makanan yang sudah ada.
     */
    public function update(Request $request, Food $food)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|in:Makanan,Minuman,Cemilan',
            'price'       => 'required|numeric|min:0',
            'description' => 'required|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $imagePath = $food->image; // Default: tetap memakai path foto yang lama

        // CONTOH: Kalau menu 'Es Teh' fotonya diganti dari foto_lama.jpg ke foto_baru.jpg,
        // maka foto_lama.jpg langsung dihapus dari harddisk server biar tidak jadi file sampah yang menumpuk.
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

    /**
     * Menghapus data makanan dari database beserta file fotonya.
     */
    public function destroy(Food $food)
    {
        // CONTOH: Jika menu makanan dihapus, file gambar fisiknya di storage juga ikut dihapus permanen.
        if ($food->image && Storage::disk('public')->exists($food->image)) {
            Storage::disk('public')->delete($food->image);
        }

        $food->delete();

        return redirect()->route('foods.index')->with('success', 'Data makanan berhasil dihapus!');
    }
}
