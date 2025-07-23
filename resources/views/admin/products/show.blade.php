@extends('layouts.admin')
@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-11 col-xl-10">

                <!-- Header Section -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">
                            <i class="bx bx-package text-primary me-2"></i>Detail Produk
                        </h4>
                        <p class="text-muted mb-0">Informasi lengkap produk</p>
                    </div>

                    <!-- Status Badge -->
                    @if ($product->supplier_id)
                        @if ($product->is_approved)
                            <span class="badge bg-success text-white px-3 py-2 fs-6">
                                <i class="bx bx-check-circle me-1"></i>Sudah Disetujui
                            </span>
                        @elseif($product->is_rejected)
                            <span class="badge bg-danger text-white px-3 py-2 fs-6">
                                <i class="bx bx-x-circle me-1"></i>Ditolak
                            </span>
                        @elseif($product->notif_admin_seen == 0)
                            <span class="badge bg-warning text-white px-3 py-2 fs-6">
                                <i class="bx bx-clock me-1"></i>Menunggu Review
                            </span>
                        @else
                            <span class="badge bg-secondary text-white px-3 py-2 fs-6">
                                <i class="bx bx-sleep me-1"></i>Belum Ditawarkan
                            </span>
                        @endif
                    @else
                        <span class="badge bg-info text-white px-3 py-2 fs-6">
                            <i class="bx bx-store me-1"></i>Produk Toko
                        </span>
                    @endif
                </div>

                <div class="row g-4">
                    <!-- Main Content -->
                    <div class="col-lg-8">
                        <!-- Product Info Card -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">
                                <!-- Product Name & Code -->
                                <div class="mb-4 pb-3 border-bottom">
                                    <h2 class="fw-bold text-dark mb-2">{{ $product->nama_produk }}</h2>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-light text-dark px-3 py-2">
                                            <i class="bx bx-barcode me-1"></i>{{ $product->kode_produk }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Product Details Grid -->
                                <div class="row g-4">
                                    <!-- Category & Supplier -->
                                    <div class="col-md-6">
                                        <div class="bg-light rounded-3 p-4 h-100">
                                            <h6 class="fw-semibold text-dark mb-3">
                                                <i class="bx bx-category-alt text-primary me-2"></i>Kategori & Supplier
                                            </h6>
                                            <div class="mb-3">
                                                <label class="form-label text-muted small mb-1">Kategori</label>
                                                <div class="fw-medium text-dark">
                                                    {{ $product->category->nama_kategori ?? '-' }}</div>
                                            </div>
                                            <div>
                                                <label class="form-label text-muted small mb-1">Supplier</label>
                                                <div>
                                                    @if ($product->supplier_id)
                                                        <span class="badge bg-primary-subtle text-primary px-3 py-2">
                                                            <i class="bx bx-user me-1"></i>{{ $product->supplier->name }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-info-subtle text-info px-3 py-2">
                                                            <i class="bx bx-store me-1"></i>Produk Toko
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Price & Stock -->
                                    <div class="col-md-6">
                                        <div class="bg-light rounded-3 p-4 h-100">
                                            <h6 class="fw-semibold text-dark mb-3">
                                                <i class="bx bx-money text-success me-2"></i>Harga & Stok
                                            </h6>

                                            <!-- Harga Beli -->
                                            <div class="mb-3">
                                                <label class="form-label text-muted small mb-1">
                                                    @if ($product->supplier_id)
                                                        Harga Beli (ke Supplier)
                                                    @else
                                                        Harga Beli
                                                    @endif
                                                </label>
                                                <div class="fw-semibold text-primary">
                                                    @if ($product->supplier_id)
                                                        Rp {{ number_format($product->harga_jual, 0, ',', '.') }}
                                                        <small class="text-muted d-block">Harga dari supplier</small>
                                                    @else
                                                        Rp {{ number_format($product->harga_beli ?? 0, 0, ',', '.') }}
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Harga Jual -->
                                            <div class="mb-3">
                                                <label class="form-label text-muted small mb-1">Harga Jual (Katalog
                                                    Toko)</label>
                                                <div class="h5 fw-bold text-success mb-0">
                                                    @if ($product->supplier_id)
                                                        @php
                                                            // Margin 20% untuk produk supplier
                                                            $hargaJualToko = $product->harga_jual * 1.2;
                                                        @endphp
                                                        Rp {{ number_format($hargaJualToko, 0, ',', '.') }}
                                                    @else
                                                        Rp {{ number_format($product->harga_jual, 0, ',', '.') }}
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                <label class="form-label text-muted small mb-1">Stok Tersedia</label>
                                                <div>
                                                    <span class="badge bg-secondary-subtle text-secondary px-3 py-2 fs-6">
                                                        <i class="bx bx-package me-1"></i>{{ $product->stok }} unit
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Description -->
                                @if ($product->deskripsi)
                                    <div class="mt-4 pt-3 border-top">
                                        <h6 class="fw-semibold text-dark mb-3">
                                            <i class="bx bx-text text-info me-2"></i>Deskripsi Produk
                                        </h6>
                                        <div class="bg-light rounded-3 p-3">
                                            <p class="text-dark mb-0 lh-lg">{{ $product->deskripsi }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Product Images -->
                        @if ($product->images && count($product->images))
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white border-bottom-0 py-3">
                                    <h6 class="fw-semibold text-dark mb-0">
                                        <i class="bx bx-image text-primary me-2"></i>Galeri Produk
                                    </h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-2">
                                        @foreach ($product->images as $img)
                                            <div class="col-6">
                                                <div class="position-relative">
                                                    <img src="{{ asset('storage/' . $img->file_path) }}"
                                                        class="img-fluid rounded-3 shadow-sm border"
                                                        style="object-fit:cover; height: 140px; width: 100%; cursor: pointer; transition: all 0.2s;"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#imageModal{{ $loop->iteration }}"
                                                        onmouseover="this.style.transform='scale(1.02)'"
                                                        onmouseout="this.style.transform='scale(1)'">
                                                </div>

                                                <!-- Modal for full size image -->
                                                <div class="modal fade" id="imageModal{{ $loop->iteration }}"
                                                    tabindex="-1">
                                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                                        <div class="modal-content border-0 shadow">
                                                            <div class="modal-header border-bottom-0">
                                                                <h5 class="modal-title fw-semibold">
                                                                    {{ $product->nama_produk }}</h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-center p-4">
                                                                <img src="{{ asset('storage/' . $img->file_path) }}"
                                                                    class="img-fluid rounded-3">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body text-center py-5">
                                    <i class="bx bx-image text-muted display-1"></i>
                                    <p class="text-muted mt-2 mb-0">Tidak ada foto produk</p>
                                </div>
                            </div>
                        @endif

                        <!-- Status Info Card -->
                        @if ($product->supplier_id && $product->is_rejected && $product->rejection_reason)
                            <div class="card border-danger border-opacity-25 shadow-sm mb-4">
                                <div class="card-header bg-danger-subtle border-danger border-opacity-25">
                                    <h6 class="text-danger fw-semibold mb-0">
                                        <i class="bx bx-x-circle me-2"></i>Alasan Penolakan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-dark mb-0">{{ $product->rejection_reason }}</p>
                                </div>
                            </div>
                        @elseif($product->supplier_id && $product->notif_admin_seen == 1 && !$product->is_approved && !$product->is_rejected)
                            <div class="card border-info border-opacity-25 shadow-sm mb-4">
                                <div class="card-header bg-info-subtle border-info border-opacity-25">
                                    <h6 class="text-info fw-semibold mb-0">
                                        <i class="bx bx-info-circle me-2"></i>Status Produk
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-dark mb-0">Produk ini belum ditawarkan oleh supplier ke toko.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                            <div>
                                @if ($product->supplier_id)
                                    <a href="{{ route('admin.supplier-products.index') }}"
                                        class="btn btn-outline-secondary px-4">
                                        <i class="bx bx-arrow-back me-2"></i>Kembali ke Produk Supplier
                                    </a>
                                @else
                                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary px-4">
                                        <i class="bx bx-arrow-back me-2"></i>Kembali ke Daftar Produk
                                    </a>
                                @endif
                            </div>

                            @if ($product->supplier_id && !$product->is_approved && !$product->is_rejected && $product->notif_admin_seen == 0)
                                <div class="d-flex gap-2">
                                    <form action="{{ route('admin.supplier-products.reject', $product->id) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger px-4"
                                            onclick="return confirm('Tolak produk ini?')">
                                            <i class="bx bx-x me-2"></i>Tolak Produk
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.supplier-products.approve', $product->id) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success px-4"
                                            onclick="return confirm('Setujui produk ini untuk masuk ke katalog toko?')">
                                            <i class="bx bx-check me-2"></i>Setujui Produk
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
