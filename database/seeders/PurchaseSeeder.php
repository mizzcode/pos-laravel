<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;

class PurchaseSeeder extends Seeder
{
  public function run(): void
  {
    // Ambil supplier users
    $suppliers = User::where('role', 'supplier')->get();

    if ($suppliers->count() === 0) {
      echo "No suppliers found. Please run UserSeeder first.\n";
      return;
    }

    // Ambil produk yang ada supplier_id (produk dari supplier)
    $supplierProducts = Product::whereNotNull('supplier_id')->get();

    if ($supplierProducts->count() === 0) {
      echo "No supplier products found. Creating some...\n";

      // Mapping produk berdasarkan kategori
      $productsByCategory = [
        1 => ['Keripik Pisang Premium', 'Kerupuk Udang Spesial', 'Keripik Tempe Gurih', 'Kerupuk Ikan Segar'],
        2 => ['Nastar Mentega', 'Kastengel Keju', 'Putri Salju Vanilla', 'Sagu Keju Premium'],
        3 => ['Dodol Durian Asli', 'Wajik Gula Merah', 'Klepon Tradisional', 'Getuk Lindri'],
        4 => ['Permen Mint Segar', 'Lollipop Buah-buahan', 'Permen Karet Bubble', 'Permen Jahe Hangat'],
        5 => ['Kacang Telur Asin', 'Keripik Tahu Pedas', 'Makaroni Goreng', 'Krupuk Bawang'],
        6 => ['Coklat Wafer Crispy', 'Permen Karamel Manis', 'Wafer Tango', 'Coklat Silverqueen'],
        7 => ['Kacang Mete Madu', 'Kacang Tanah Bawang', 'Kacang Almond Panggang', 'Kacang Atom Pedas'],
        8 => ['Biskuit Roma Kelapa', 'Roti Marie Susu', 'Crackers Asin', 'Wafer Tango Coklat'],
        9 => ['Keripik Singkong Balado', 'Makaroni Pedas Level 5', 'Seblak Kering', 'Ciki-ciki Pedas'],
        10 => ['Permen Yupi Gummy', 'Choki-choki Coklat', 'Pilus Garuda', 'Taro Net Jagung'],
        11 => ['Pudding Susu', 'Agar-agar Buah', 'Jelly Cincau', 'Es Krim Potong']
      ];

      // Buat produk supplier dengan nama yang sesuai kategori
      foreach ($suppliers as $supplier) {
        // Buat 3-5 produk per supplier
        $numProducts = rand(3, 5);
        for ($i = 1; $i <= $numProducts; $i++) {
          $categoryId = rand(1, 11);
          $categoryProducts = $productsByCategory[$categoryId];
          $productName = $categoryProducts[array_rand($categoryProducts)];

          Product::create([
            'supplier_id' => $supplier->id,
            'category_id' => $categoryId,
            'kode_produk' => 'SUP-' . $supplier->id . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
            'nama_produk' => $productName,
            'harga_beli' => rand(8000, 25000),
            'harga_jual' => rand(12000, 35000),
            'stok' => rand(20, 100),
            'deskripsi' => 'Produk berkualitas dari supplier ' . $supplier->name,
          ]);
        }
      }

      $supplierProducts = Product::whereNotNull('supplier_id')->get();
    }

    // Generate purchases untuk 3 bulan terakhir
    for ($i = 0; $i < 15; $i++) {
      $supplier = $suppliers->random();
      $tanggalBeli = Carbon::now()->subDays(rand(1, 90))->format('Y-m-d');

      $purchase = Purchase::create([
        'supplier_id' => $supplier->id,
        'tanggal_beli' => $tanggalBeli,
        'total_beli' => 0, // Will be calculated
        'status_bayar' => ['pending', 'paid', 'cancelled'][rand(0, 2)],
        'bukti_bayar' => rand(0, 1) ? 'bukti_bayar_' . $i . '.jpg' : null,
      ]);

      // Add purchase items
      $totalBeli = 0;
      $numItems = rand(1, 4); // 1-4 items per purchase

      for ($j = 0; $j < $numItems; $j++) {
        // Ambil produk yang benar-benar milik supplier ini
        $availableProducts = $supplierProducts->where('supplier_id', $supplier->id);

        if ($availableProducts->count() === 0) {
          // Jika tidak ada produk, skip item ini
          continue;
        }

        $product = $availableProducts->random();
        $qty = rand(5, 20);
        $hargaBeli = $product->harga_beli ?? rand(10000, 50000);
        $subtotal = $qty * $hargaBeli;
        $totalBeli += $subtotal;

        PurchaseItem::create([
          'purchase_id' => $purchase->id,
          'product_id' => $product->id,
          'qty' => $qty,
          'harga_satuan' => $hargaBeli,
          'subtotal' => $subtotal,
        ]);
      }

      // Update total_beli
      $purchase->update(['total_beli' => $totalBeli]);
    }

    echo "Created 15 dummy purchases with items.\n";
  }
}
