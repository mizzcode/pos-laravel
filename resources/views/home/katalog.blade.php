@extends('layouts.app')

@section('content')
    {{-- HERO SECTION --}}
    <div class="row align-items-center my-5">
        <div class="col-lg-6 text-center text-lg-start mb-4 mb-lg-0">
            <h1 class="display-4 fw-bold mb-3">
                Temukan Produk Terbaik di <span class="text-primary">Shop</span>
            </h1>
            <p class="lead mb-4">
                Belanja online mudah, aman, dan banyak promo setiap hari! Mulai dari kebutuhan harian, elektronik, fashion,
                hingga produk eksklusif hanya di sini.
            </p>
            @auth
                <div class="alert alert-primary mb-4 d-inline-block" style="font-size:1.1rem;">
                    <i class="bx bx-user"></i>
                    Selamat datang, <strong>{{ auth()->user()->name }}</strong>!
                </div>
            @else
                <a href="{{ route('register') }}" class="btn btn-lg btn-primary px-4 me-2">
                    <i class="bx bx-user-plus"></i> Daftar Gratis
                </a>
            @endauth
        </div>
        <div class="col-lg-6 text-center">
            <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" class="img-fluid rounded"
                alt="POS Katalog" style="max-height: 340px;">
        </div>
    </div>

    {{-- FILTER KATEGORI --}}
    <div class="card shadow-sm border-0 p-3 mb-4">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <select name="kategori" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $kategori == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-none d-md-block">
                <button class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i> Filter</button>
            </div>
        </form>
    </div>

    {{-- ALERT --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- PRODUK GRID --}}
    <div class="mb-3 mt-4 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold"><i class="bx bx-package text-primary"></i> Katalog Produk</h5>
        <span class="text-muted small">{{ $products->total() }} produk ditemukan</span>
    </div>
    <div id="produk" class="row g-4 mb-5">
        @forelse ($products as $p)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card h-100 shadow-sm border-0 produk-card position-relative" style="transition:transform .15s;">
                    {{-- LABEL STOK HABIS/TERLARIS --}}
                    @if ($p->stok < 1)
                        <span class="badge bg-danger position-absolute top-0 end-0 m-2 shadow">Stok Habis</span>
                    @elseif($p->terlaris ?? false)
                        <span class="badge bg-success position-absolute top-0 end-0 m-2 shadow">Terlaris</span>
                    @endif
                    {{-- GAMBAR PRODUK --}}
                    @if ($p->images && count($p->images) > 0)
                        @php
                            $firstImage = $p->images->first();
                            $defaultPlaceholder =
                                'https://placehold.co/500x500/95A5A6/FFFFFF?text=' . urlencode($p->nama_produk);

                            // Prioritas: cek storage lokal dulu, kalau tidak ada gunakan URL eksternal
                            if (str_starts_with($firstImage->file_path, 'http')) {
                                // Jika sudah URL eksternal, gunakan langsung
                                $imageUrl = $firstImage->file_path;
                            } else {
                                // Cek apakah file ada di storage, kalau tidak ada gunakan placeholder
                                $localPath = public_path('storage/' . $firstImage->file_path);
                                $imageUrl = file_exists($localPath)
                                    ? asset('storage/' . $firstImage->file_path)
                                    : $defaultPlaceholder;
                            }
                        @endphp
                        <img src="{{ $imageUrl }}" class="card-img-top" alt="{{ $p->nama_produk }}"
                            style="height:160px;object-fit:cover;" onerror="this.src='{{ $defaultPlaceholder }}'">
                    @else
                        <div class="d-flex align-items-center justify-content-center bg-light" style="height:160px;">
                            <i class="bx bx-image-alt text-secondary" style="font-size:2.2rem;"></i>
                        </div>
                    @endif
                    <div class="card-body d-flex flex-column pb-2">
                        <div class="mb-1 text-muted small">{{ $p->category->nama_kategori ?? '-' }}</div>
                        <h6 class="fw-bold mb-1">{{ $p->nama_produk }}</h6>
                        <div class="mb-1 text-primary fw-semibold" style="font-size:1.1rem;">
                            Rp{{ number_format($p->harga_jual, 0, ',', '.') }}
                        </div>
                        <div class="small mb-2 text-muted">Stok: {{ $p->stok }}</div>
                        <form action="{{ route('home.keranjang.tambah', $p->id) }}" method="POST" class="mt-auto">
                            @csrf
                            <button class="btn btn-primary w-100 btn-sm" {{ $p->stok < 1 ? 'disabled' : '' }}>
                                <i class="bx bx-cart"></i>
                                @if ($p->stok < 1)
                                    Tidak Tersedia
                                @else
                                    Beli
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    Produk tidak ditemukan.
                </div>
            </div>
        @endforelse
    </div>

    {{-- PAGINATION --}}
    @if ($products->hasPages())
        <div class="d-flex justify-content-center">
            {{ $products->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    @endif

    {{-- TIPS: tambahkan efek hover --}}
    @push('styles')
        <style>
            .produk-card:hover {
                transform: translateY(-7px) scale(1.03);
                box-shadow: 0 8px 24px rgba(105, 108, 255, 0.08);
            }
        </style>
    @endpush
@endsection
