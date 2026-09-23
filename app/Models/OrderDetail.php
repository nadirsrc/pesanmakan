<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    // Menentukan nama tabel di database secara eksplisit ke tabel order_details
    protected $table = 'order_details';

    // Proteksi Mass Assignment
    protected $guarded = ['id'];

    // Relasi Invers / BelongsTo:
    // CONTOH: Setiap 1 baris rincian di struk ini HANYA MERUJUK ke 1 menu makanan tertentu (misal baris ini milik 'Es Teh').
    // Cara panggil di kode Blade untuk menampilkan nama makanannya: $detail->food->name
    public function food()
    {
        return $this->belongsTo(Food::class, 'food_id');
    }

    // Relasi Invers / BelongsTo:
    // CONTOH: Setiap 1 baris rincian ini HANYA TERIKAT ke 1 struk pesanan tertentu (misal terikat ke pesanan Meja 5).
    // Cara panggil di kode: $detail->order->customer_name
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
