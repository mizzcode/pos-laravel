<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class NotificationController extends Controller
{
  // Tampilkan semua notifikasi produk supplier
  public function index()
  {
    // Produk supplier yang belum dilihat admin (notif_admin_seen = 0)
    $notifications = Product::with(['supplier', 'category'])
      ->where('supplier_id', '!=', null)
      ->where('notif_admin_seen', 0)
      ->orderByDesc('created_at')
      ->get();

    return view('admin.notifications.index', compact('notifications'));
  }

  // Redirect ke detail produk tanpa mengubah status notifikasi
  public function markAsRead($product_id)
  {
    $product = Product::where('supplier_id', '!=', null)
      ->where('id', $product_id)
      ->firstOrFail();

    // JANGAN update notif_admin_seen di sini
    // Biarkan status tetap "Menunggu Review" sampai admin approve/reject

    return redirect()->route('products.show', $product_id);
  }

  // Hitung jumlah notifikasi yang belum dibaca
  public function getUnreadCount()
  {
    $count = Product::where('supplier_id', '!=', null)
      ->where('notif_admin_seen', 0)
      ->count();

    return response()->json(['count' => $count]);
  }
}
