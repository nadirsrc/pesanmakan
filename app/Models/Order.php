<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Proteksi Mass Assignment: hanya 'id' yang dijaga, kolom customer_name, table_number, dll bebas disimpan
    protected $guarded = ['id'];

    // Relasi One-to-Many:
    // CONTOH: 1 Struk Pesanan Meja 3 (Order #1) bisa berisi BANYAK item makanan sekaligus (misal: 2 Nasi Goreng, 1 Es Teh, 1 Kentang).
    // Cara panggil di kode: $order->orderDetails
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }
}
