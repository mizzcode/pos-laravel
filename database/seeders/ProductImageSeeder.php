<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;

class ProductImageSeeder extends Seeder
{
  public function run(): void
  {
    // Array gambar untuk setiap produk berdasarkan nama produk
    // Menggunakan URL eksternal karena belum ada gambar lokal
    $productImages = [
      // Produk Toko
      'Keripik Singkong Balado' => [
        'product_images/NgmDv2hJQuDGw1njHiZJFZ3PgCfPPBKXaYIKkRQB.jpg', // Gambar lokal
        'https://placehold.co/500x500/FF6B6B/FFFFFF?text=Keripik+Singkong'
      ],
      'Nastar Premium' => [
        'https://placehold.co/500x500/4ECDC4/FFFFFF?text=Nastar+Premium',
        'https://placehold.co/500x500/45B7D1/FFFFFF?text=Kue+Kering'
      ],
      'Permen Jahe Asli' => [
        'https://placehold.co/500x500/F7DC6F/000000?text=Permen+Jahe',
        'https://placehold.co/500x500/F39C12/FFFFFF?text=Jahe+Asli'
      ],
      'Kacang Mete Madu' => [
        'https://placehold.co/500x500/D7BDE2/000000?text=Kacang+Mete',
        'https://placehold.co/500x500/BB8FCE/FFFFFF?text=Mete+Madu'
      ],
      'Biskuit Marie Regal' => [
        'https://placehold.co/500x500/85C1E9/000000?text=Biskuit+Marie',
        'https://placehold.co/500x500/5DADE2/FFFFFF?text=Marie+Regal'
      ],
      'Makaroni' => [
        'product_images/oeBqYkGOXFdymHcLuOkpEzHzY2xIVUkbAiVdA2Za.jpg', // Gambar lokal
        'https://placehold.co/500x500/58D68D/FFFFFF?text=Makaroni'
      ],
      // Produk Supplier
      'Dodol Betawi Original' => [
        'https://placehold.co/500x500/A569BD/FFFFFF?text=Dodol+Betawi',
        'https://placehold.co/500x500/8E44AD/FFFFFF?text=Original'
      ],
      'Makaroni Spiral Pedas' => [
        'https://placehold.co/500x500/E74C3C/FFFFFF?text=Makaroni+Spiral',
        'https://placehold.co/500x500/C0392B/FFFFFF?text=Pedas'
      ],
      'Permen Kapas Warna-warni' => [
        'https://placehold.co/500x500/EC7063/FFFFFF?text=Permen+Kapas',
        'https://placehold.co/500x500/F1948A/000000?text=Warna+Warni'
      ],
      'Ceker Pedas Mercon' => [
        'https://placehold.co/500x500/E67E22/FFFFFF?text=Ceker+Pedas',
        'https://placehold.co/500x500/D35400/FFFFFF?text=Mercon'
      ]
    ];

    // Loop semua produk dan tambahkan gambar
    $products = Product::all();

    foreach ($products as $product) {
      if (isset($productImages[$product->nama_produk])) {
        $images = $productImages[$product->nama_produk];

        foreach ($images as $index => $imageUrl) {
          ProductImage::create([
            'product_id' => $product->id,
            'file_path' => $imageUrl, // Simpan URL eksternal langsung
            'is_default' => $index === 0, // Gambar pertama sebagai default
            'urutan' => $index + 1
          ]);
        }
      } else {
        // Jika tidak ada gambar spesifik, gunakan gambar placeholder
        ProductImage::create([
          'product_id' => $product->id,
          'file_path' => 'https://placehold.co/500x500/95A5A6/FFFFFF?text=No+Image',
          'is_default' => true,
          'urutan' => 1
        ]);
      }
    }
  }
}
