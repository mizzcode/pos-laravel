<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'supplier_id',
        'category_id',
        'kode_produk',
        'nama_produk',
        'harga_beli',
        'harga_jual',
        'stok',
        'deskripsi',
        'notif_admin_seen',
        'is_approved',
        'is_rejected',
        'rejection_reason'
    ];

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class, 'category_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    // Accessor untuk harga_beli: jika null return 0
    public function getHargaBeliAttribute($value)
    {
        return $value ?? 0;
    }
}
