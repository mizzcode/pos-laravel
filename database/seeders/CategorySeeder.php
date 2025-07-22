<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'nama_kategori' => 'Keripik & Kerupuk',
                'deskripsi' => 'Berbagai jenis keripik dan kerupuk tradisional'
            ],
            [
                'nama_kategori' => 'Kue Kering',
                'deskripsi' => 'Aneka kue kering dan cookies'
            ],
            [
                'nama_kategori' => 'Cemilan Tradisional',
                'deskripsi' => 'Jajanan dan cemilan khas tradisional Indonesia'
            ],
            [
                'nama_kategori' => 'Permen & Lolipop',
                'deskripsi' => 'Berbagai macam permen dan lolipop'
            ],
            [
                'nama_kategori' => 'Snack Asin & Gurih',
                'deskripsi' => 'Cemilan dengan rasa asin dan gurih'
            ],
            [
                'nama_kategori' => 'Snack Manis',
                'deskripsi' => 'Cemilan dengan rasa manis'
            ],
            [
                'nama_kategori' => 'Kacang-kacangan',
                'deskripsi' => 'Berbagai jenis kacang dan olahan kacang'
            ],
            [
                'nama_kategori' => 'Biskuit & Roti Kecil',
                'deskripsi' => 'Aneka biskuit dan roti kemasan kecil'
            ],
            [
                'nama_kategori' => 'Cemilan Pedas',
                'deskripsi' => 'Cemilan dengan cita rasa pedas'
            ],
            [
                'nama_kategori' => 'Jajanan Anak-anak',
                'deskripsi' => 'Cemilan khusus untuk anak-anak'
            ],
            [
                'nama_kategori' => 'Cemilan Basah',
                'deskripsi' => 'Cemilan basah seperti puding, jelly, dll'
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
