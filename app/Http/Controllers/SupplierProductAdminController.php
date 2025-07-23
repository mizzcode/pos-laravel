<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class SupplierProductAdminController extends Controller
{
  // Tampilkan semua produk supplier (untuk admin)
  public function index(Request $request)
  {
    $query = Product::with(['supplier', 'category'])
      ->whereNotNull('supplier_id'); // Hanya produk supplier

    // Filter berdasarkan status approval
    if ($request->filled('status')) {
      if ($request->status === 'approved') {
        $query->where('is_approved', 1);
      } elseif ($request->status === 'pending') {
        // Hanya produk yang sudah ditawarkan (notif_admin_seen = 0) dan belum di-approve/reject
        $query->where('notif_admin_seen', 0)->where('is_approved', 0)->where('is_rejected', 0);
      } elseif ($request->status === 'rejected') {
        $query->where('is_rejected', 1);
      } elseif ($request->status === 'belum_tawarkan') {
        // Produk yang belum ditawarkan ke admin
        $query->where('notif_admin_seen', 1)->where('is_approved', 0)->where('is_rejected', 0);
      }
    }

    // Filter berdasarkan supplier
    if ($request->filled('supplier_id')) {
      $query->where('supplier_id', $request->supplier_id);
    }

    // Search
    if ($request->filled('q')) {
      $query->where('nama_produk', 'like', '%' . $request->q . '%');
    }

    $products = $query->orderByDesc('created_at')->paginate(10);

    // Data untuk filter
    $suppliers = \App\Models\User::where('role', 'supplier')->get();
    $categories = Category::all();

    return view('admin.supplier-products.index', compact('products', 'suppliers', 'categories'));
  }

  // Approve produk supplier
  public function approve($product_id)
  {
    $supplierProduct = Product::where('supplier_id', '!=', null)
      ->where('id', $product_id)
      ->firstOrFail();

    // Langsung approve produk supplier tanpa cloning
    // Produk tetap mempertahankan supplier_id asli
    $supplierProduct->is_approved = 1;
    $supplierProduct->notif_admin_seen = 1;
    $supplierProduct->is_rejected = 0;
    $supplierProduct->rejection_reason = null;
    $supplierProduct->save();

    return back()->with('success', 'Produk supplier berhasil disetujui dan akan tampil di katalog toko!');
  }

  // Reject produk supplier
  public function reject($product_id)
  {
    $supplierProduct = Product::where('supplier_id', '!=', null)
      ->where('id', $product_id)
      ->firstOrFail();

    // Set status menjadi ditolak
    $supplierProduct->is_rejected = 1;
    $supplierProduct->is_approved = 0;
    $supplierProduct->notif_admin_seen = 1;
    $supplierProduct->rejection_reason = 'Produk ditolak oleh admin'; // Bisa dikustomisasi nanti
    $supplierProduct->save();

    return back()->with('success', 'Produk berhasil ditolak.');
  }
}
