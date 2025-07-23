<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;

class SupplierController extends Controller
{
    // Lihat semua supplier
    public function index()
    {
        $suppliers = User::where('role', 'supplier')->get();
        return view('admin.suppliers.index', compact('suppliers'));
    }

    // Verifikasi supplier baru (is_active = 0)
    public function verify($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = 1;
        $user->save();
        return back()->with('success', 'Supplier diaktifkan!');
    }

    // Lihat produk milik supplier
    public function products($supplier_id)
    {
        $supplier = User::findOrFail($supplier_id);
        $products = Product::where('supplier_id', $supplier_id)->get();
        return view('admin.suppliers.products', compact('supplier', 'products'));
    }

    // Approve produk supplier agar masuk ke produk toko
    public function approveProduct($product_id)
    {
        $supplierProduct = \App\Models\Product::where('supplier_id', '!=', null)
            ->where('id', $product_id)
            ->firstOrFail();

        // Clone produk ke produk toko (admin)
        $newProduct = $supplierProduct->replicate();
        $newProduct->supplier_id = null; // Jadi produk toko
        $newProduct->is_approved = 1;
        $newProduct->notif_admin_seen = 1;

        // Harga jual supplier menjadi harga beli admin
        $newProduct->harga_beli = $supplierProduct->harga_jual;

        // Generate unique kode_produk for the new admin product
        $baseCode = 'ADM-' . $supplierProduct->category_id . '-';
        $counter = 1;

        do {
            $kode_produk = $baseCode . str_pad($counter, 3, '0', STR_PAD_LEFT);
            $exists = \App\Models\Product::where('kode_produk', $kode_produk)->exists();
            $counter++;
        } while ($exists);

        $newProduct->kode_produk = $kode_produk;
        $newProduct->save();

        // Clone foto juga jika ada
        foreach ($supplierProduct->images as $img) {
            $newProduct->images()->create(['file_path' => $img->file_path]);
        }

        // Update status produk supplier menjadi approved
        $supplierProduct->is_approved = 1;
        $supplierProduct->notif_admin_seen = 1;
        $supplierProduct->save();

        return back()->with('success', 'Produk berhasil disetujui dan masuk ke katalog toko!');
    }

    // Pesan produk dari supplier (hanya untuk restock produk yang sudah approved)
    public function orderProduct(Request $request, $supplier_id, $product_id)
    {
        $request->validate([
            'qty' => 'required|integer|min:1'
        ]);

        // Ambil produk supplier yang sudah approved
        $supplierProduct = \App\Models\Product::where('supplier_id', $supplier_id)
            ->where('id', $product_id)
            ->where('is_approved', 1)
            ->firstOrFail();

        $qty = $request->qty;

        // Jika stok tidak cukup
        if ($qty > $supplierProduct->stok) {
            return back()->with('error', 'Stok tidak cukup.');
        }

        // Cari produk toko (internal/admin) dengan nama & kategori yang sama
        $adminProduct = \App\Models\Product::whereNull('supplier_id')
            ->where('nama_produk', $supplierProduct->nama_produk)
            ->where('category_id', $supplierProduct->category_id)
            ->first();

        if (!$adminProduct) {
            return back()->with('error', 'Produk belum ada di toko. Silakan approve dulu produk ini.');
        }

        // Tambah stok admin dan update harga beli dari harga jual supplier
        $adminProduct->stok += $qty;
        $adminProduct->harga_beli = $supplierProduct->harga_jual;
        $adminProduct->save();

        // Kurangi stok di supplier
        $supplierProduct->stok -= $qty;
        $supplierProduct->save();

        return back()->with('success', 'Stok berhasil masuk ke toko/admin!');
    }

    // Tampilkan detail supplier (user + relasi supplier)
    public function show($id)
    {
        $supplier = User::with('supplier')->findOrFail($id);
        return view('admin.suppliers.show', compact('supplier'));
    }

    // Nonaktifkan supplier (ubah is_active = 0)
    public function nonaktif($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = 0;
        $user->save();
        return back()->with('success', 'Supplier berhasil dinonaktifkan!');
    }
}
