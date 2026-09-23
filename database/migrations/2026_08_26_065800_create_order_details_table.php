<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id(); // Primary Key auto-increment nomor baris rincian

            // Foreign Key ke tabel orders
            // CONTOH: Mengikat kolom ini ke id di tabel orders.
            // Kalau ada input order_id = 99 padahal pesanan #99 tidak pernah ada, MySQL otomatis menolak (Data Integrity).
            // onDelete('cascade') CONTOH: Kalau data pesanan Meja 5 (Order #1) dihapus admin dari tabel orders,
            // maka seluruh rincian makanannya di tabel order_details ini ikut terhapus otomatis, tidak berserakan jadi sampah database.
            $table->foreignId('order_id')
                ->constrained('orders')
                ->onDelete('cascade');

            // Foreign Key ke tabel foods
            // CONTOH: Mengikat ke id di tabel foods.
            // onDelete('cascade') CONTOH: Kalau menu 'Es Teh' dihapus dari daftar makanan,
            // baris detail riwayat yang mereferensikan menu tersebut ikut dibersihkan.
            $table->foreignId('food_id')
                ->constrained('foods')
                ->onDelete('cascade');

            $table->integer('quantity'); // CONTOH: Jumlah porsi yang dipesan, misal 2 porsi

            // CONTOH: Hasil perkalian (harga satuan Rp 25.000 x 2 porsi = Rp 50.000).
            $table->integer('subtotal'); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
