<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;

class ProductImageController extends Controller
{
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $user = auth()->user();

        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($user->role === 'admin' || ($user->role === 'supplier' && $product->supplier_id == $user->id)) {
            if ($request->hasFile('images')) {
                $currentImagesCount = $product->images()->count();
                foreach ($request->file('images') as $index => $img) {
                    $product->images()->create([
                        'file_path' => $img->store('product_images', 'public'),
                        'is_default' => $currentImagesCount === 0 && $index === 0,
                        'urutan' => $currentImagesCount + $index + 1
                    ]);
                }
            }
            return back()->with('success', 'Foto produk berhasil diupload!');
        }
        abort(403, 'Akses ditolak!');
    }

    public function destroy($id)
    {
        $img = ProductImage::findOrFail($id);
        $user = auth()->user();
        $product = $img->product;

        if ($user->role === 'admin' || ($user->role === 'supplier' && $product->supplier_id == $user->id)) {
            $wasDefault = $img->is_default;

            // Hapus file fisik jika bukan URL eksternal
            if (!str_starts_with($img->file_path, 'http')) {
                $filePath = storage_path('app/public/' . $img->file_path);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $img->delete();

            // Jika gambar yang dihapus adalah default, set gambar pertama yang tersisa sebagai default
            if ($wasDefault) {
                $firstRemainingImage = ProductImage::where('product_id', $product->id)
                    ->orderBy('urutan')
                    ->first();

                if ($firstRemainingImage) {
                    $firstRemainingImage->update(['is_default' => true]);
                }
            }

            return back()->with('success', 'Foto berhasil dihapus!');
        }
        abort(403, 'Akses ditolak!');
    }
}
