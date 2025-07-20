<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;

class ProductController extends Controller
{
    public function index()
    {
        // Tampilkan SEMUA produk (internal & supplier)
        $products = \App\Models\Product::with('category', 'supplier')
            ->orderByDesc('created_at')
            ->paginate(20);

        // Notifikasi produk supplier baru (opsional)
        $notif_products = \App\Models\Product::whereNotNull('supplier_id')
            ->where('notif_admin_seen', 0)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('admin.products.index', compact('products', 'notif_products'));
    }


    public function show($id)
    {
        $product = Product::with(['category', 'supplier', 'images'])->findOrFail($id);

        // Update notifikasi jika produk supplier dan belum dilihat admin
        if ($product->supplier_id && !$product->notif_admin_seen) {
            $product->notif_admin_seen = 1;
            $product->save();
        }

        return view('admin.products.show', compact('product'));
    }

    public function create()
    {
        $categories = Category::all();
        $suppliers = \App\Models\User::where('role', 'supplier')->get();
        return view('admin.products.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'category_id' => 'required',
            'kode_produk' => 'required',
            'nama_produk' => 'required',
            'harga_beli'  => 'nullable|numeric',
            'harga_jual'  => 'required|numeric',
            'stok'        => 'required|numeric',
            'deskripsi'   => 'nullable'
        ]);
        $data = $request->only('category_id', 'kode_produk', 'nama_produk', 'harga_beli', 'harga_jual', 'stok', 'deskripsi');
        $data['supplier_id'] = ($user->role == 'supplier') ? $user->id : $request->supplier_id;
        $product = Product::create($data);

        // Upload multi foto produk
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $product->images()->create([
                    'file_path' => $img->store('product_images', 'public')
                ]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Produk ditambah!');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        $suppliers = \App\Models\User::where('role', 'supplier')->get();
        return view('admin.products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $user = auth()->user();
        $request->validate([
            'category_id' => 'required',
            'kode_produk' => 'required',
            'nama_produk' => 'required',
            'harga_beli'  => 'nullable|numeric',
            'harga_jual'  => 'required|numeric',
            'stok'        => 'required|numeric',
            'deskripsi'   => 'nullable'
        ]);

        if ($user->role === 'admin' || ($user->role === 'supplier' && $product->supplier_id == $user->id)) {
            $product->update($request->only('category_id', 'kode_produk', 'nama_produk', 'harga_beli', 'harga_jual', 'stok', 'deskripsi'));

            // Upload foto baru jika ada
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $img) {
                    $product->images()->create([
                        'file_path' => $img->store('product_images', 'public')
                    ]);
                }
            }
            return redirect()->route('products.index')->with('success', 'Produk diupdate!');
        }
        abort(403, 'Akses ditolak!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $user = auth()->user();
        if ($user->role === 'admin' || ($user->role === 'supplier' && $product->supplier_id == $user->id)) {
            $product->delete();
            return back()->with('success', 'Produk dihapus!');
        }
        abort(403, 'Akses ditolak!');
    }

    public function receiveFromSupplier(Request $request)
    {
        // Validasi input: ID produk supplier dan jumlah
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty'        => 'required|numeric|min:1',
        ]);

        // Produk supplier
        $supplierProduct = \App\Models\Product::where('id', $request->product_id)
            ->whereNotNull('supplier_id')
            ->firstOrFail();

        // Cari produk utama di toko (berdasarkan nama/kategori/kode yang sama)
        $mainProduct = \App\Models\Product::where('supplier_id', null)
            ->where('nama_produk', $supplierProduct->nama_produk)
            ->where('category_id', $supplierProduct->category_id)
            // bisa tambahkan where('kode_produk', ...) jika pakai kode unik
            ->first();

        if ($mainProduct) {
            // Jika produk sudah ada di toko, tambahkan stok
            $mainProduct->stok += $request->qty;
            $mainProduct->save();
        } else {
            // Jika belum ada, buat produk baru (copy dari produk supplier, tanpa supplier_id)
            $newProduct = $supplierProduct->replicate();
            $newProduct->supplier_id = null;
            $newProduct->stok = $request->qty;
            $newProduct->save();

            // Jika ada foto, copy juga (jika pakai relasi images)
            foreach ($supplierProduct->images as $img) {
                $newProduct->images()->create(['file_path' => $img->file_path]);
            }
        }

        // Update notifikasi produk supplier agar hilang (opsional)
        $supplierProduct->notif_admin_seen = 1;
        $supplierProduct->save();

        return back()->with('success', 'Stok berhasil ditambahkan ke produk toko!');
    }
}
