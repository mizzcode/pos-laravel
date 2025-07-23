<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use App\Models\Product;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set pagination view untuk Tailwind CSS (gunakan Bootstrap 4 yang kompatibel dengan theme admin)
        Paginator::defaultView('pagination::bootstrap-4');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-4');

        // Notifikasi produk supplier yang belum dilihat admin
        View::composer('layouts.admin', function ($view) {
            $notif_products = Product::with(['supplier'])
                ->whereNotNull('supplier_id')
                ->where('notif_admin_seen', 0)
                ->orderByDesc('created_at')
                ->get();
            $view->with('notif_products', $notif_products);
        });
    }
}
