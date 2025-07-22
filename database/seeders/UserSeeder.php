<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => 1,
        ]);

        // Customer User
        $customer = User::create([
            'name' => 'John Customer',
            'email' => 'customer@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'is_active' => 1,
        ]);

        // Create customer profile
        $customer->customer()->create([
            'nama_customer' => 'John Customer',
            'alamat' => 'Jl. Customer No. 123, Jakarta',
            'no_hp' => '081234567890',
        ]);

        // Supplier User
        $supplier = User::create([
            'name' => 'PT Supplier Utama',
            'email' => 'supplier@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'supplier',
            'is_active' => 1,
        ]);

        // Create supplier profile
        $supplier->supplier()->create([
            'nama_supplier' => 'PT Supplier Utama',
            'perusahaan' => 'PT Supplier Utama Indonesia',
            'no_hp' => '021-1234567',
            'alamat' => 'Jl. Industri No. 456, Bekasi',
        ]);

        // Mitra User
        $mitra = User::create([
            'name' => 'Toko Mitra Jaya',
            'email' => 'bromitra@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'mitra',
            'is_active' => 1,
        ]);

        // Create mitra profile
        $mitra->mitra()->create([
            'nama_mitra' => 'Toko Mitra Jaya',
            'nama_usaha' => 'Mitra Jaya Store',
            'no_hp' => '021-7654321',
            'alamat' => 'Jl. Perdagangan No. 789, Tangerang',
        ]);

        // Additional test users
        $supplier2 = User::create([
            'name' => 'CV Supplier Dua',
            'email' => 'supplier2@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'supplier',
            'is_active' => 0, // Belum diverifikasi
        ]);

        $supplier2->supplier()->create([
            'nama_supplier' => 'CV Supplier Dua',
            'perusahaan' => 'CV Supplier Dua Sejahtera',
            'no_hp' => '021-9876543',
            'alamat' => 'Jl. Supplier No. 234, Bogor',
        ]);

        $customer2 = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'is_active' => 1,
        ]);

        $customer2->customer()->create([
            'nama_customer' => 'Jane Doe',
            'alamat' => 'Jl. Customer Dua No. 567, Depok',
            'no_hp' => '089876543210',
        ]);
    }
}