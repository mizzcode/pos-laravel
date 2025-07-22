<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class SupplierProductController extends Controller
{
    // List produk supplier (dengan filter, search, paginasi)
    public function index(Request $request)
    {
        $query = Product::where('supplier_id', auth()->id())->with('category', 'images');
        if ($request->filled('q')) {
            $query->where('nama_produk', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        $products = $query->orderByDesc('created_at')->paginate(10);
        $categories = Category::all();

        return view('supplier.products.product_list', compact('products', 'categories'));
    }

    // Form tambah produk supplier
    public function create()
    {
        $categories = Category::all();
        return view('supplier.products.product_create', compact('categories'));
    }

    // Simpan produk supplier (tidak langsung tampil di admin)
    public function store(Request $request)
    {
        $request->validate([
            'nama_produk'  => 'required|string|max:100',
            'harga_jual'   => 'required|numeric|min:0',
            'stok'         => 'required|integer|min:0',
            'category_id'  => 'required|exists:categories,id',
            'deskripsi'    => 'nullable|string|max:255',
            'foto.*'       => 'nullable|image|max:2048',
        ]);
        $data = $request->only('nama_produk', 'harga_jual', 'stok', 'category_id', 'deskripsi');
        $data['supplier_id'] = auth()->id();
        $data['notif_admin_seen'] = 1; // default tidak notif admin
        $data['is_approved'] = 0;      // butuh approval admin

        $product = Product::create($data);

        // Upload multi foto
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $img) {
                $path = $img->store('produk', 'public');
                $product->images()->create(['file_path' => $path]);
            }
        }

        return redirect()->route('supplier.products.index')->with('success', 'Produk berhasil ditambahkan! Silakan tawarkan ke toko jika ingin tampil di admin.');
    }

    // Tombol "Tawarkan ke Toko" untuk notif admin
    public function offerToStore(Request $request, $id)
    {
        $product = Product::where('supplier_id', auth()->id())->findOrFail($id);
        $product->notif_admin_seen = 0; // Tampil di notif admin
        $product->save();

        return redirect()->route('supplier.products.index')->with('success', 'Produk berhasil ditawarkan ke toko/admin!');
    }

    // Edit & Update produk (hanya milik supplier)
    public function edit($id)
    {
        $product = Product::where('supplier_id', auth()->id())->with('images')->findOrFail($id);
        $categories = Category::all();
        return view('supplier.products.product_edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::where('supplier_id', auth()->id())->findOrFail($id);
        $request->validate([
            'nama_produk'  => 'required|string|max:100',
            'harga_jual'   => 'required|numeric|min:0',
            'stok'         => 'required|integer|min:0',
            'category_id'  => 'required|exists:categories,id',
            'deskripsi'    => 'nullable|string|max:255',
            'foto.*'       => 'nullable|image|max:2048',
        ]);
        $product->update($request->only('nama_produk', 'harga_jual', 'stok', 'category_id', 'deskripsi'));

        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $img) {
                $path = $img->store('produk', 'public');
                $product->images()->create(['file_path' => $path]);
            }
        }

        return redirect()->route('supplier.products.index')->with('success', 'Produk berhasil diupdate!');
    }

    public function destroy($id)
    {
        $product = Product::where('supplier_id', auth()->id())->with('images')->findOrFail($id);
        foreach ($product->images as $img) {
            Storage::disk('public')->delete($img->file_path);
            $img->delete();
        }
        $product->delete();
        return back()->with('success', 'Produk berhasil dihapus.');
    }

    public function produkToko(Request $request)
    {
        // Hanya menampilkan produk toko (internal/admin) = supplier_id NULL
        // Hapus kondisi is_approved karena produk toko tidak perlu approval
        $query = \App\Models\Product::with('category')
            ->whereNull('supplier_id'); // Hanya produk toko/admin

        if ($request->filled('q')) {
            $query->where('nama_produk', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->orderByDesc('created_at')->paginate(10);
        $categories = \App\Models\Category::all();

        return view('supplier.products.product_toko', compact('products', 'categories'));
    }
}
