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
        Schema::create('foods', function (Blueprint $table) {
            $table->id(); // Primary Key auto-increment nomor identitas unik setiap makanan

            $table->string('name'); // Nama menu makanan (tipe varchar)
            
            // Tipe Enum: Membatasi pilihan hanya boleh salah satu dari array
            // CONTOH: Kalau ada yang nginput kategori 'Baju' atau 'Sepatu', MySQL otomatis menolak karena bukan Makanan, Minuman, atau Cemilan.
            $table->enum('category', ['Makanan', 'Minuman', 'Cemilan']);

            // CONTOH: Kalau diisi 25000, tersimpan sebagai angka bulat 25000 untuk mempermudah perhitungan matematika (subtotal).
            $table->integer('price'); 

            $table->text('description'); // Deskripsi bahan / rasa makanan (tipe text untuk teks panjang)

            // Kolom upload foto makanan
            // CONTOH: Diberi ->nullable() agar jika admin ingin menyimpan menu baru tetapi belum punya fotonya, data tetap berhasil disimpan tanpa error (nilainya jadi NULL).
            $table->string('image')->nullable();

            // CONTOH: Otomatis mengisi created_at saat menu dibuat, dan updated_at saat harga/nama menu diedit.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
