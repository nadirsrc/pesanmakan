<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // akun mimin
        User::create([
            'name' => 'Admin Toko',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        // jalankan seeder mkanan
        $this->call([
            FoodSeeder::class,
        ]);
    }
}
