<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    CategoryController,
    ProductController,
    ProductImageController,
    SupplierController,
    MitraController,
    CustomerController,
    LaporanController,
    PurchaseController,
    OrderController,
    PaymentController,
    MidtransController,
    SupplierDashboardController,
    SupplierProductController,
    SupplierProductAdminController,
    HomeController,
    ProfileController,
    AdminDashboardController,
    NotificationController
};
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

// =====================
// KATALOG & KERANJANG (Umum)
// =====================
Route::get('/', [HomeController::class, 'katalog'])->name('home.katalog');
Route::get('/keranjang', [HomeController::class, 'keranjang'])->name('home.keranjang');
Route::post('/keranjang/tambah/{id}', [HomeController::class, 'tambahKeranjang'])->name('home.keranjang.tambah');
Route::post('/keranjang/hapus/{id}', [HomeController::class, 'hapusKeranjang'])->name('home.keranjang.hapus');
Route::post('/keranjang/update/{id}', [HomeController::class, 'updateKeranjang'])->name('home.keranjang.update');
Route::middleware(['auth'])->group(function () {
    Route::post('/checkout', [HomeController::class, 'checkout'])->name('home.checkout');
    // ...route lain yg butuh login
});
Route::get('/checkout/success', fn() => view('home.checkout_success'))->name('home.checkout.success');
Route::get('/checkout/pending', fn() => view('home.checkout_pending'))->name('home.checkout.pending');
Route::get('/checkout/failed', fn() => view('home.checkout_failed'))->name('home.checkout.failed');

// =====================
// AUTH (register, login, logout)
// =====================
Route::get('register', fn() => view('auth.register'))->name('register');
Route::post('register', [AuthController::class, 'register']);
Route::get('login', fn() => view('auth.login'))->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// =====================
// USER AREA (auth required: profile, orders, edit profile)
// =====================
Route::middleware('auth')->group(function () {
    Route::get('/profile', fn() => view('home.profile'))->name('home.profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/orders', [HomeController::class, 'pesananSaya'])->name('home.orders');
    Route::prefix('myorders')->name('home.myorders.')->group(function () {
        Route::get('/', [HomeController::class, 'pesananSaya'])->name('index');
        Route::get('/{id}', [HomeController::class, 'pesananDetail'])->name('detail');
        Route::post('/{id}/lanjutkan-pembayaran', [HomeController::class, 'lanjutkanPembayaran'])->name('lanjutkan_pembayaran');
    });
});

// =====================
// DASHBOARD (dinamis redirect berdasarkan role)
// =====================
Route::get('/dashboard', function () {
    if (!auth()->check()) return redirect()->route('login');
    $role = auth()->user()->role;
    return match ($role) {
        'admin'    => redirect()->route('dashboard.admin'),
        'supplier' => redirect()->route('supplier.dashboard'),
        'mitra'    => redirect()->route('home.katalog'),
        'customer' => redirect()->route('home.katalog'),
        default    => abort(403, 'Role tidak dikenal'),
    };
});
Route::get('/dashboard/admin', [AdminDashboardController::class, 'index'])->name('dashboard.admin');

// =====================
// VERIFIKASI PENGGUNA BARU (admin)
// =====================
Route::get('users/verifikasi', [AuthController::class, 'verifikasi'])->name('users.verifikasi');
Route::post('users/{id}/approve', [AuthController::class, 'approve'])->name('users.approve');

// =====================
// ADMIN ROUTES (dengan middleware auth)
// =====================
Route::middleware(['auth'])->group(function () {
    // =====================
    // KATEGORI PRODUK (admin)
    // =====================
    Route::resource('categories', CategoryController::class)->except(['show']);

    // =====================
    // PRODUK (admin)
    // =====================
    // Route untuk gambar produk harus sebelum resource products
    Route::post('products/{productId}/images', [ProductImageController::class, 'store'])->name('products.images.store');
    Route::delete('product-images/{id}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
    Route::post('product-images/{id}/set-default', [ProductImageController::class, 'setDefault'])->name('products.images.setDefault');

    Route::resource('products', ProductController::class);
    Route::post('products/receive-from-supplier', [ProductController::class, 'receiveFromSupplier'])->name('products.receiveFromSupplier');

    // =====================
    // SUPPLIER PRODUCTS MANAGEMENT (admin-only)
    // =====================
    Route::prefix('admin/supplier-products')->name('admin.supplier-products.')->group(function () {
        Route::get('/', [SupplierProductAdminController::class, 'index'])->name('index');
        Route::post('approve/{product_id}', [SupplierProductAdminController::class, 'approve'])->name('approve');
        Route::post('reject/{product_id}', [SupplierProductAdminController::class, 'reject'])->name('reject');
    });

    // =====================
    // SUPPLIER (admin-only)
    // =====================
    // SUPPLIER (admin-only)
    // =====================
    Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('suppliers/{id}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::post('suppliers/{id}/verify', [SupplierController::class, 'verify'])->name('suppliers.verify');
    Route::post('suppliers/{id}/nonaktif', [SupplierController::class, 'nonaktif'])->name('suppliers.nonaktif');
    Route::get('suppliers/{id}/products', [SupplierController::class, 'products'])->name('suppliers.products');
    Route::post('suppliers/approve-product/{product_id}', [SupplierController::class, 'approveProduct'])->name('suppliers.approve-product');
    // Route untuk menambah stok produk dari supplier ke toko (ADMIN beli dari supplier)
    Route::post('suppliers/{supplier_id}/order-product/{product_id}', [SupplierController::class, 'orderProduct'])->name('suppliers.orderProduct');

    // =====================
    // NOTIFICATIONS (admin-only)
    // =====================
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/read/{product_id}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('api/notifications/count', [NotificationController::class, 'getUnreadCount'])->name('notifications.count');

    // =====================
    // MITRA (admin-only)
    // =====================
    Route::resource('mitras', MitraController::class)->only(['index', 'edit', 'update', 'show']);
    Route::post('mitras/{id}/disable', [MitraController::class, 'disable'])->name('mitras.disable');

    // =====================
    // CUSTOMER (admin-only)
    // =====================
    Route::resource('customers', CustomerController::class)->only(['index', 'show']);
    Route::post('customers/{id}/disable', [CustomerController::class, 'disable'])->name('customers.disable');

    // =====================
    // PEMBELIAN KE SUPPLIER (admin)
    // =====================
    Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);

    // =====================
    // PENJUALAN / ORDER (admin/kasir)
    // =====================
    Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('orders/{midtrans_order_id}/pay', [OrderController::class, 'pay'])->name('orders.pay');
    Route::post('orders/{midtrans_order_id}/kirim', [OrderController::class, 'kirim'])->name('orders.kirim');
    Route::patch('orders/{midtrans_order_id}/ubah-status', [OrderController::class, 'ubahStatus'])->name('orders.ubahStatus');

    // =====================
    // PEMBAYARAN (admin)
    // =====================
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments/{id}/set-lunas', [PaymentController::class, 'setLunas'])->name('payments.setLunas');
    Route::post('payments/create', [PaymentController::class, 'createTransaction'])->name('payments.create');
    Route::post('payments/callback', [PaymentController::class, 'handleCallback'])
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('payments.callback');

    // =====================
    // LAPORAN (admin)
    // =====================
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('penjualan', [LaporanController::class, 'penjualan'])->name('penjualan');
        Route::get('produk-terlaris', [LaporanController::class, 'produkTerlaris'])->name('produk_terlaris');
        Route::get('pembelian', [LaporanController::class, 'pembelian'])->name('pembelian');

        // PDF exports
        Route::get('penjualan/pdf', [LaporanController::class, 'penjualanPdf'])->name('penjualan_pdf');
        Route::get('pembelian/pdf', [LaporanController::class, 'pembelianPdf'])->name('pembelian_pdf');
        Route::get('produk-terlaris/pdf', [LaporanController::class, 'produkTerlarisPdf'])->name('produk_terlaris_pdf');
    });
}); // End auth middleware group

// =====================
// SUPPLIER AREA (role supplier saja)
// =====================
Route::middleware(['auth', 'role:supplier'])->prefix('supplier')->name('supplier.')->group(function () {
    Route::get('/dashboard', [SupplierDashboardController::class, 'index'])->name('dashboard');
    Route::get('products', [SupplierProductController::class, 'index'])->name('products.index');
    Route::get('products/create', [SupplierProductController::class, 'create'])->name('products.create');
    Route::post('products', [SupplierProductController::class, 'store'])->name('products.store');
    Route::get('products-toko', [SupplierProductController::class, 'produkToko'])->name('products.toko');
    Route::get('products-toko/{id}/tawarkan', [SupplierProductController::class, 'tawarkanAlternatifForm'])->name('products.tawarkan');
    Route::post('products-toko/{id}/tawarkan', [SupplierProductController::class, 'tawarkanAlternatif'])->name('products.tawarkan.store');
    Route::get('products/{product}/edit', [SupplierProductController::class, 'edit'])->name('products.edit');
    Route::put('products/{product}', [SupplierProductController::class, 'update'])->name('products.update');
    Route::delete('products/{product}', [SupplierProductController::class, 'destroy'])->name('products.destroy');
    Route::post('products/{id}/offer-to-store', [SupplierProductController::class, 'offerToStore'])->name('products.offerToStore');
});

// =====================
// MIDTRANS CALLBACK/NOTIFICATION
// =====================
Route::post('/midtrans/notification', [MidtransController::class, 'notificationHandler'])
    ->name('midtrans.notification')
    ->withoutMiddleware([VerifyCsrfToken::class]);

// =====================
// FALLBACK - 404 custom
// =====================
Route::fallback(fn() => abort(404, 'Halaman tidak ditemukan.'));
