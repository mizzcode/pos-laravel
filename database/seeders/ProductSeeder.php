<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $supplier = User::where('email', 'supplier@pos.com')->first();

        // Produk Toko (supplier_id = null, tidak perlu approval)
        $tokoProducts = [
            [
                'supplier_id' => null,
                'category_id' => 1, // Keripik & Kerupuk
                'kode_produk' => 'KRP001',
                'nama_produk' => 'Keripik Singkong Balado',
                'harga_beli' => 8000,
                'harga_jual' => 12000,
                'stok' => 50,
                'deskripsi' => 'Keripik singkong rasa balado pedas gurih',
                'notif_admin_seen' => true,
                'is_approved' => null
            ],
            [
                'supplier_id' => null,
                'category_id' => 2, // Kue Kering
                'kode_produk' => 'KUE001',
                'nama_produk' => 'Nastar Premium',
                'harga_beli' => 25000,
                'harga_jual' => 35000,
                'stok' => 30,
                'deskripsi' => 'Kue nastar dengan selai nanas asli',
                'notif_admin_seen' => true,
                'is_approved' => null
            ],
            [
                'supplier_id' => null,
                'category_id' => 4, // Permen & Lolipop
                'kode_produk' => 'PRM001',
                'nama_produk' => 'Permen Jahe Asli',
                'harga_beli' => 3000,
                'harga_jual' => 5000,
                'stok' => 100,
                'deskripsi' => 'Permen jahe untuk menghangatkan tenggorokan',
                'notif_admin_seen' => true,
                'is_approved' => null
            ],
            [
                'supplier_id' => null,
                'category_id' => 7, // Kacang-kacangan
                'kode_produk' => 'KCG001',
                'nama_produk' => 'Kacang Mete Madu',
                'harga_beli' => 15000,
                'harga_jual' => 22000,
                'stok' => 25,
                'deskripsi' => 'Kacang mete panggang dengan perasa madu',
                'notif_admin_seen' => true,
                'is_approved' => null
            ],
            [
                'supplier_id' => null,
                'category_id' => 8, // Biskuit & Roti Kecil
                'kode_produk' => 'BSK001',
                'nama_produk' => 'Biskuit Marie Regal',
                'harga_beli' => 6000,
                'harga_jual' => 9000,
                'stok' => 60,
                'deskripsi' => 'Biskuit marie rasa original',
                'notif_admin_seen' => true,
                'is_approved' => null
            ],
            [
                'supplier_id' => null,
                'category_id' => 1, // Keripik & Kerupuk
                'kode_produk' => 'KRP002',
                'nama_produk' => 'Makaroni',
                'harga_beli' => 5000,
                'harga_jual' => 8000,
                'stok' => 45,
                'deskripsi' => 'Makaroni renyah dengan berbagai varian rasa',
                'notif_admin_seen' => true,
                'is_approved' => null
            ]
        ];

        foreach ($tokoProducts as $product) {
            Product::create($product);
        }

        // Produk Supplier (perlu approval)
        if ($supplier) {
            $supplierProducts = [
                [
                    'supplier_id' => $supplier->id,
                    'category_id' => 3, // Cemilan Tradisional
                    'kode_produk' => 'SUP001',
                    'nama_produk' => 'Dodol Betawi Original',
                    'harga_beli' => 12000,
                    'harga_jual' => 18000,
                    'stok' => 40,
                    'deskripsi' => 'Dodol betawi asli dengan rasa khas Jakarta',
                    'notif_admin_seen' => false,
                    'is_approved' => 1 // Sudah diapprove
                ],
                [
                    'supplier_id' => $supplier->id,
                    'category_id' => 5, // Snack Asin & Gurih
                    'kode_produk' => 'SUP002',
                    'nama_produk' => 'Makaroni Spiral Pedas',
                    'harga_beli' => 7000,
                    'harga_jual' => 11000,
                    'stok' => 35,
                    'deskripsi' => 'Makaroni spiral dengan bumbu pedas gurih',
                    'notif_admin_seen' => false,
                    'is_approved' => 0 // Belum diapprove
                ],
                [
                    'supplier_id' => $supplier->id,
                    'category_id' => 6, // Snack Manis
                    'kode_produk' => 'SUP003',
                    'nama_produk' => 'Permen Kapas Warna-warni',
                    'harga_beli' => 4000,
                    'harga_jual' => 7000,
                    'stok' => 50,
                    'deskripsi' => 'Permen kapas dengan berbagai rasa dan warna',
                    'notif_admin_seen' => true,
                    'is_approved' => 1 // Sudah diapprove
                ],
                [
                    'supplier_id' => $supplier->id,
                    'category_id' => 9, // Cemilan Pedas
                    'kode_produk' => 'SUP004',
                    'nama_produk' => 'Ceker Pedas Mercon',
                    'harga_beli' => 10000,
                    'harga_jual' => 15000,
                    'stok' => 20,
                    'deskripsi' => 'Ceker ayam dengan sambal pedas level mercon',
                    'notif_admin_seen' => false,
                    'is_approved' => 0 // Belum diapprove
                ]
            ];

            foreach ($supplierProducts as $product) {
                Product::create($product);
            }
        }
    }
}
