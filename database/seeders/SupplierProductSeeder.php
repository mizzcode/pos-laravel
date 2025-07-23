<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;

class SupplierProductSeeder extends Seeder
{
    public function run(): void
    {
        // Cari supplier (user dengan role 'supplier')
        $suppliers = User::where('role', 'supplier')->get();
        $categories = Category::all();

        if ($suppliers->count() === 0 || $categories->count() === 0) {
            $this->command->info('No suppliers or categories found. Please run UserSeeder and CategorySeeder first.');
            return;
        }

        $products = [
            [
                'nama_produk' => 'Smartphone Samsung Galaxy A54',
                'harga_jual' => 4500000,
                'stok' => 15,
                'deskripsi' => 'Smartphone dengan kamera 50MP dan layar Super AMOLED',
                'category_id' => $categories->where('nama_kategori', 'Elektronik')->first()?->id ?? $categories->first()->id,
            ],
            [
                'nama_produk' => 'Sepatu Nike Air Max',
                'harga_jual' => 1200000,
                'stok' => 25,
                'deskripsi' => 'Sepatu olahraga dengan teknologi Air Max cushioning',
                'category_id' => $categories->where('nama_kategori', 'Fashion')->first()?->id ?? $categories->first()->id,
            ],
            [
                'nama_produk' => 'Skincare Set The Ordinary',
                'harga_jual' => 350000,
                'stok' => 30,
                'deskripsi' => 'Set perawatan kulit dengan Niacinamide dan Hyaluronic Acid',
                'category_id' => $categories->where('nama_kategori', 'Kecantikan')->first()?->id ?? $categories->first()->id,
            ],
            [
                'nama_produk' => 'Beras Premium 5kg',
                'harga_jual' => 75000,
                'stok' => 50,
                'deskripsi' => 'Beras putih premium kualitas terbaik untuk keluarga',
                'category_id' => $categories->where('nama_kategori', 'Makanan')->first()?->id ?? $categories->first()->id,
            ],
        ];

        foreach ($suppliers as $supplier) {
            foreach ($products as $index => $productData) {
                // Generate unique kode_produk
                $baseCode = 'SUP-' . $supplier->id . '-' . $productData['category_id'] . '-';
                $counter = 1;

                do {
                    $kode_produk = $baseCode . str_pad($counter, 3, '0', STR_PAD_LEFT);
                    $exists = Product::where('kode_produk', $kode_produk)->exists();
                    $counter++;
                } while ($exists);

                Product::create([
                    'supplier_id' => $supplier->id,
                    'category_id' => $productData['category_id'],
                    'kode_produk' => $kode_produk,
                    'nama_produk' => $productData['nama_produk'],
                    'harga_jual' => $productData['harga_jual'],
                    'stok' => $productData['stok'],
                    'deskripsi' => $productData['deskripsi'],
                    'notif_admin_seen' => $index < 2 ? 0 : 1, // 2 produk pertama jadi notifikasi
                    'is_approved' => 0,
                ]);
            }
        }

        $this->command->info('Supplier products seeded successfully!');
    }
}
