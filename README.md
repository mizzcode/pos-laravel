# 🛒 POS Laravel - Sistem Point of Sale

<p align="center">
<img src="https://img.shields.io/badge/Laravel-12.x-red?style=for-the-badge&logo=laravel" alt="Laravel Version">
<img src="https://img.shields.io/badge/PHP-8.3+-blue?style=for-the-badge&logo=php" alt="PHP Version">
<img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License">
</p>

## 📋 Tentang Proyek

Sistem Point of Sale (POS) berbasis Laravel yang comprehensive dengan multi-role management. Sistem ini dirancang untuk mengelola operasional toko dengan fitur lengkap untuk admin, supplier, mitra, dan customer.

## ✨ Fitur Utama

### 🔐 Multi-Role Authentication

-   **Admin**: Pengelolaan penuh sistem
-   **Supplier**: Manajemen produk dan penawaran
-   **Mitra**: Akses terbatas untuk partnership
-   **Customer**: Shopping dan pemesanan

### 📦 Manajemen Produk

-   CRUD produk dengan multi-image upload
-   Kategorisasi produk yang terorganisir
-   Sistem stok real-time
-   Import produk dari supplier ke toko
-   Placeholder image system dengan fallback

### 🤝 Sistem Supplier

-   Registrasi dan verifikasi supplier
-   Manajemen produk supplier
-   Sistem pemesanan dari supplier ke admin
-   Notifikasi produk baru dari supplier

### 🛍️ E-Commerce Features

-   Katalog produk publik
-   Sistem keranjang belanja
-   Checkout dan pembayaran
-   Integrasi Midtrans payment gateway

### 📊 Sistem Laporan

-   Laporan penjualan dengan filter tanggal
-   Laporan pembelian dari supplier
-   Laporan produk terlaris
-   Export PDF untuk semua laporan

### 💰 Manajemen Keuangan

-   Tracking pembayaran
-   Status pembayaran real-time
-   Integrasi payment gateway

## 🚀 Instalasi

### Prerequisites

-   PHP 8.3 atau lebih tinggi
-   Composer
-   MySQL/MariaDB
-   Node.js & NPM

### Langkah Instalasi

1. **Clone repository**

```bash
git clone https://github.com/mizzcode/pos-laravel.git
cd pos-laravel
```

2. **Install dependencies**

```bash
composer install
npm install
```

3. **Environment setup**

```bash
cp .env.example .env
php artisan key:generate
```

4. **Database setup**

```bash
# Edit .env file dengan konfigurasi database Anda
php artisan migrate
php artisan db:seed
```

5. **Storage setup**

```bash
php artisan storage:link
```

6. **Build assets**

```bash
npm run build
# atau untuk development
npm run dev
```

7. **Start server**

```bash
composer run dev
```

## 🔧 Konfigurasi

### Midtrans Payment Gateway

Edit file `.env` dan tambahkan konfigurasi Midtrans:

```env
MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_ENVIRONMENT=sandbox # atau production
```

### File Storage

Sistem menggunakan local storage dengan fallback ke placeholder images:

```env
FILESYSTEM_DISK=local
```

## 👥 Default Users

Setelah menjalankan seeder, Anda dapat login dengan:

### Admin

-   **Email**: admin@gmail.com
-   **Password**: password

### Supplier

-   **Email**: supplier@gmail.com
-   **Password**: password

### Customer

-   **Email**: customer@gmail.com
-   **Password**: password

### Mitra

-   **Email**: bromitra@gmail.com
-   **Password**: password

## 📚 Struktur Database

### Core Tables

-   `users` - Multi-role user management
-   `categories` - Kategori produk
-   `products` - Data produk dengan relasi supplier
-   `product_images` - Multiple images per product
-   `orders` - Transaksi penjualan
-   `order_items` - Detail item per order
-   `purchases` - Pembelian dari supplier
-   `purchase_items` - Detail pembelian
-   `payments` - Tracking pembayaran

### Relationships

-   User → Products (Supplier relationship)
-   Product → Category (Many to One)
-   Product → ProductImages (One to Many)
-   Order → OrderItems (One to Many)
-   Purchase → PurchaseItems (One to Many)

### Public Routes

-   `GET /` - Katalog produk
-   `GET /keranjang` - Keranjang belanja
-   `POST /login` - User authentication
-   `POST /register` - User registration

### Protected Routes (Auth Required)

-   `GET /dashboard` - Dynamic dashboard redirect
-   `/products/*` - Product management
-   `/categories/*` - Category management
-   `/suppliers/*` - Supplier management
-   `/orders/*` - Order management
-   `/laporan/*` - Reports with PDF export

-   `GET /supplier/dashboard` - Supplier dashboard
-   `/supplier/products/*` - Supplier product management

## 🔒 Security Features

-   **Route Protection**: Middleware auth pada semua route admin
-   **Role-based Access**: Setiap role memiliki akses terbatas
-   **CSRF Protection**: Semua form dilindungi CSRF token
-   **Input Validation**: Comprehensive request validation
-   **File Upload Security**: Image validation dan sanitization

## 📈 Performance

-   **Database Optimization**: Eager loading untuk relasi
-   **Image Optimization**: Automatic image resizing
-   **Caching**: Query result caching
-   **Asset Optimization**: Vite untuk bundling

## 🤝 Contributing

1. Fork repository
2. Buat feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 🐛 Known Issues

-   Image upload pada mobile memerlukan optimasi
-   Laporan untuk periode besar memerlukan pagination

## 📧 Support

Untuk pertanyaan atau dukungan, silakan hubungi:

-   Email: mizzc0d3@gmail.com
-   GitHub Issues: [Create Issue](https://github.com/mizzcode/pos-laravel/issues)

## 📄 License

Proyek ini dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT).

## 🙏 Acknowledgments

-   Laravel Framework untuk foundation yang solid
-   Midtrans untuk payment gateway integration
-   Bootstrap untuk UI components
-   Seluruh kontributor yang telah membantu pengembangan

---

<p align="center">
Dibuat dengan ❤️ menggunakan Laravel
</p>
