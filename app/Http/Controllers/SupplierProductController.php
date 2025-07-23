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

        // Filter search
        if ($request->filled('q')) {
            $query->where('nama_produk', 'like', '%' . $request->q . '%');
        }

        // Filter kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter status
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'belum_tawarkan':
                    $query->where('notif_admin_seen', 1)->where('is_approved', 0)->where('is_rejected', 0);
                    break;
                case 'menunggu':
                    $query->where('notif_admin_seen', 0)->where('is_approved', 0)->where('is_rejected', 0);
                    break;
                case 'diterima':
                    $query->where('is_approved', 1);
                    break;
                case 'ditolak':
                    $query->where('is_rejected', 1);
                    break;
            }
        }

        $products = $query->orderByDesc('created_at')->paginate(10);
        $categories = Category::all();

        // Hitung status count untuk panel
        $statusCounts = [
            'belum_tawarkan' => Product::where('supplier_id', auth()->id())
                ->where('notif_admin_seen', 1)
                ->where('is_approved', 0)
                ->where('is_rejected', 0)
                ->count(),
            'menunggu' => Product::where('supplier_id', auth()->id())
                ->where('notif_admin_seen', 0)
                ->where('is_approved', 0)
                ->where('is_rejected', 0)
                ->count(),
            'diterima' => Product::where('supplier_id', auth()->id())
                ->where('is_approved', 1)
                ->count(),
            'ditolak' => Product::where('supplier_id', auth()->id())
                ->where('is_rejected', 1)
                ->count(),
        ];

        return view('supplier.products.product_list', compact('products', 'categories', 'statusCounts'));
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
        $data['notif_admin_seen'] = 1; // default tidak langsung tawarkan ke admin
        $data['is_approved'] = 0;      // butuh approval admin  
        $data['is_rejected'] = 0;      // belum ditolak

        // Untuk supplier, harga_beli tidak diset (null)

        // Generate unique kode_produk untuk supplier
        $baseCode = 'SUP-' . $data['supplier_id'] . '-' . $data['category_id'] . '-';
        $counter = 1;

        do {
            $kode_produk = $baseCode . str_pad($counter, 3, '0', STR_PAD_LEFT);
            $exists = Product::where('kode_produk', $kode_produk)->exists();
            $counter++;
        } while ($exists);

        $data['kode_produk'] = $kode_produk;

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

        // Reset status jika sebelumnya ditolak
        if ($product->is_rejected) {
            $product->is_rejected = 0;
            $product->rejection_reason = null;
        }

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

        $updateData = $request->only('nama_produk', 'harga_jual', 'stok', 'category_id', 'deskripsi');

        // Untuk supplier, harga_beli tidak diubah (tetap null jika supplier product)

        $product->update($updateData);

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
