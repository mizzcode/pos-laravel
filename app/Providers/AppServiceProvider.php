<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
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
