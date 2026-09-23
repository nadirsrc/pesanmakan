<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;

    // Menentukan nama tabel di MySQL
    protected $table = 'foods';

    // Proteksi Mass Assignment:
    // CONTOH: Hanya kolom 'id' yang dikunci agar tidak bisa disusupi input hacker (misal hacker menyisipkan 'id' => 999).
    // Sementara kolom 'name', 'price', 'category', dll bebas disimpan massal lewat Food::create($request->all()).
    protected $guarded = ['id'];

    // Relasi One-to-Many:
    // CONTOH: 1 Menu Makanan (misal 'Nasi Goreng') bisa tercatat di BANYAK struk pesanan yang berbeda.
    // Cara panggil di kode: $food->orderDetails
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'food_id');
    }
}
