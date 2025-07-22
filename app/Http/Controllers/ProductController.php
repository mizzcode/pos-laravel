<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
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
            'kode_produk' => 'required|unique:products,kode_produk',
            'nama_produk' => 'required',
            'harga_beli'  => 'nullable|numeric',
            'harga_jual'  => 'required|numeric',
            'stok'        => 'required|numeric',
            'deskripsi'   => 'nullable',
            'images.*'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->only('category_id', 'kode_produk', 'nama_produk', 'harga_beli', 'harga_jual', 'stok', 'deskripsi');
        $data['supplier_id'] = ($user->role == 'supplier') ? $user->id : $request->supplier_id;
        $product = Product::create($data);

        // Upload multi foto produk
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $img) {
                $product->images()->create([
                    'file_path' => $img->store('product_images', 'public'),
                    'is_default' => $index === 0, // Gambar pertama sebagai default
                    'urutan' => $index + 1
                ]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $product = Product::with(['images', 'category', 'supplier'])->findOrFail($id);
        $categories = Category::all();
        $suppliers = \App\Models\User::where('role', 'supplier')->get();
        return view('admin.products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        // Test apakah method ini dipanggil
        Log::info('=== UPDATE METHOD CALLED ===', [
            'method' => $request->method(),
            'url' => $request->url(),
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'user_role' => auth()->user()->role ?? 'not_logged_in'
        ]);

        Log::info('=== UPDATE PRODUCT START ===', [
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'all_request_data' => $request->all(),
            'has_files' => $request->hasFile('images'),
            'files_info' => $request->hasFile('images') ?
                collect($request->file('images'))->map(fn($file) => [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType()
                ])->toArray() : []
        ]);

        // Enhanced validation with clear error messages
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string', // Changed from required to nullable
            'harga_jual' => 'required|numeric|min:0',
            'harga_beli' => 'nullable|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ], [
            'nama_produk.required' => 'Nama produk wajib diisi',
            'deskripsi.string' => 'Deskripsi harus berupa teks',
            'harga_jual.required' => 'Harga jual wajib diisi',
            'harga_jual.numeric' => 'Harga jual harus berupa angka',
            'harga_jual.min' => 'Harga jual tidak boleh negatif',
            'harga_beli.numeric' => 'Harga beli harus berupa angka',
            'harga_beli.min' => 'Harga beli tidak boleh negatif',
            'stok.required' => 'Stok produk wajib diisi',
            'stok.integer' => 'Stok harus berupa angka bulat',
            'stok.min' => 'Stok tidak boleh negatif',
            'category_id.required' => 'Kategori wajib dipilih',
            'category_id.exists' => 'Kategori yang dipilih tidak valid',
            'images.*.image' => 'File harus berupa gambar',
            'images.*.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
            'images.*.max' => 'Ukuran gambar maksimal 2MB'
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed', ['errors' => $validator->errors()->toArray()]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Ada error pada form. Silakan periksa input Anda.');
        }

        Log::info('Validation passed, starting update');

        try {
            // Check user permission
            if (auth()->user()->role !== 'admin' && $product->user_id !== auth()->id()) {
                Log::warning('Unauthorized update attempt', [
                    'user_id' => auth()->id(),
                    'user_role' => auth()->user()->role,
                    'product_user_id' => $product->user_id
                ]);
                return redirect()->route('products.index')
                    ->with('error', 'Anda tidak memiliki izin untuk mengedit produk ini.');
            }

            Log::info('Permission check passed');

            // Update product data
            $updateData = [
                'nama_produk' => $request->nama_produk,
                'deskripsi' => $request->deskripsi,
                'harga_jual' => $request->harga_jual,
                'harga_beli' => $request->harga_beli,
                'stok' => $request->stok,
                'category_id' => $request->category_id,
            ];

            Log::info('Updating product with data', $updateData);
            $product->update($updateData);
            Log::info('Product data updated successfully');

            // Handle image uploads
            if ($request->hasFile('images')) {
                Log::info('Processing image uploads', ['count' => count($request->file('images'))]);

                // Get current image count for proper ordering
                $currentImageCount = $product->images()->count();
                Log::info('Current image count', ['count' => $currentImageCount]);

                foreach ($request->file('images') as $index => $image) {
                    Log::info("Processing image {$index}", [
                        'name' => $image->getClientOriginalName(),
                        'size' => $image->getSize(),
                        'mime' => $image->getMimeType()
                    ]);

                    try {
                        // Generate unique filename
                        $filename = time() . '_' . $index . '_' . $image->getClientOriginalName();
                        Log::info("Generated filename: {$filename}");

                        // Store the image
                        $path = $image->storeAs('product_images', $filename, 'public');
                        Log::info("Image stored at: {$path}");

                        // Create ProductImage record
                        // New images should NOT be default if there are existing images
                        $imageRecord = ProductImage::create([
                            'product_id' => $product->id,
                            'file_path' => $path,
                            'is_default' => $currentImageCount === 0 && $index === 0, // Only first image is default if no existing images
                            'urutan' => $currentImageCount + $index + 1 // Continue numbering from existing images
                        ]);

                        Log::info('ProductImage record created', [
                            'id' => $imageRecord->id,
                            'path' => $imageRecord->file_path,
                            'is_default' => $imageRecord->is_default,
                            'urutan' => $imageRecord->urutan
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Error processing image {$index}", [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);

                        return redirect()->back()
                            ->with('error', "Error uploading image: " . $e->getMessage())
                            ->withInput();
                    }
                }

                Log::info('All images processed successfully');
            } else {
                Log::info('No images to upload');
            }

            Log::info('=== UPDATE PRODUCT SUCCESS ===');

            return redirect()->route('products.index')
                ->with('success', 'Produk berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('=== UPDATE PRODUCT ERROR ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
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