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

    // Pesan produk dari supplier (buat purchase dan clone produk ke toko)
    public function orderProduct(Request $request, $supplier_id, $product_id)
    {
        $request->validate([
            'qty' => 'required|integer|min:1'
        ]);

        // Ambil produk supplier
        $supplierProduct = \App\Models\Product::where('supplier_id', $supplier_id)
            ->where('id', $product_id)
            ->firstOrFail();

        $qty = $request->qty;

        // Jika stok tidak cukup
        if ($qty > $supplierProduct->stok) {
            return back()->with('error', 'Stok tidak cukup.');
        }

        // Cari produk toko (internal/admin) dengan nama & kategori yang sama (atau kode_produk kalau ada)
        $adminProduct = \App\Models\Product::whereNull('supplier_id')
            ->where('nama_produk', $supplierProduct->nama_produk)
            ->where('category_id', $supplierProduct->category_id)
            ->first();

        if ($adminProduct) {
            // Tambah stok admin
            $adminProduct->stok += $qty;
            $adminProduct->save();
        } else {
            // Buat produk baru di admin (clone dari supplier, tapi supplier_id NULL)
            $newProduct = $supplierProduct->replicate();
            $newProduct->supplier_id = null;
            $newProduct->stok = $qty;
            $newProduct->save();

            // Clone foto juga jika ada
            foreach ($supplierProduct->images as $img) {
                $newProduct->images()->create(['file_path' => $img->file_path]);
            }
        }

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