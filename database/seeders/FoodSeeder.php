<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('foods')->insert([
            [
                'name' => 'nasi goreng',
                'category' => 'Makanan',
                'price' => 15000,
                'description' => 'nasi goreng biasa.',
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'mie goreng seafood',
                'category' => 'Makanan',
                'price' => 28000,
                'description' => 'mie goreng dengan cumi dan udang.',
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'es teh manis',
                'category' => 'Minuman',
                'price' => 5000,
                'description' => 'es teh manis biasa.',
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'jus alpukat',
                'category' => 'Minuman',
                'price' => 15000,
                'description' => 'jus alpukat biasa.',
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'kentang goreng',
                'category' => 'Makanan',
                'price' => 12000,
                'description' => 'kentang goreng biasa buat nyemil.',
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
