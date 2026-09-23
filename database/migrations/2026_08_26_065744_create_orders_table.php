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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // Primary Key auto-increment nomor struk pesanan unik (Order #1, #2, dst)

            $table->string('customer_name'); // CONTOH: 'Budi Santoso'

            // CONTOH: Menggunakan string agar bisa menerima nomor meja angka '05' maupun format huruf 'Meja VIP-A'.
            $table->string('table_number'); 

            // CONTOH: Akumulasi seluruh subtotal item makanan, misal Rp 55000.
            $table->integer('total_price'); 

            // CONTOH: Nilai default adalah 'Pending' saat baru dipesan. Nanti admin di dashboard bisa mengubah statusnya menjadi 'Selesai' atau 'Batal'.
            $table->string('status')->default('Pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
