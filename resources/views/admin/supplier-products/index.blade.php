@extends('layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Admin /</span> Kelola Produk Supplier
        </h4>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bx bx-package me-2"></i>Daftar Produk Supplier
                </h5>
                <span class="badge bg-info">{{ $products->total() }} Produk</span>
            </div>

            <!-- Filter -->
            <div class="card-body border-bottom">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="q" class="form-control" placeholder="Cari produk..."
                            value="{{ request('q') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="belum_tawarkan" {{ request('status') === 'belum_tawarkan' ? 'selected' : '' }}>
                                Belum Ditawarkan</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu
                                Review</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Sudah
                                Disetujui</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="supplier_id" class="form-select">
                            <option value="">Semua Supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}"
                                    {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bx bx-search"></i> Cari
                        </button>
                        <a href="{{ route('admin.supplier-products.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>

            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Supplier</th>
                            <th>Kategori</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($products as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($product->images->where('is_default', 1)->first())
                                            <img src="{{ asset('storage/' . $product->images->where('is_default', 1)->first()->file_path) }}"
                                                class="rounded me-3" width="40" height="40"
                                                style="object-fit: cover;">
                                        @else
                                            <img src="https://placehold.co/40x40/e9ecef/6c757d?text=No+Image"
                                                class="rounded me-3" width="40" height="40">
                                        @endif
                                        <div>
                                            <strong>{{ $product->nama_produk }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $product->kode_produk }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $product->supplier->name ?? 'N/A' }}</td>
                                <td>{{ $product->category->nama_kategori ?? 'N/A' }}</td>
                                <td>Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</td>
                                <td>{{ $product->stok }}</td>
                                <td>
                                    @if ($product->is_approved)
                                        <span class="badge bg-success">Sudah Disetujui</span>
                                    @elseif ($product->is_rejected)
                                        <span class="badge bg-danger">Ditolak</span>
                                    @elseif ($product->notif_admin_seen == 0)
                                        <span class="badge bg-warning">Menunggu Review</span>
                                    @else
                                        <span class="badge bg-secondary">Belum Ditawarkan</span>
                                    @endif
                                </td>
                                <td>{{ $product->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('products.show', $product->id) }}">
                                                <i class="bx bx-show me-1"></i> Lihat Detail
                                            </a>
                                            @if (!$product->is_approved && !$product->is_rejected && $product->notif_admin_seen == 0)
                                                <form action="{{ route('admin.supplier-products.approve', $product->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success"
                                                        onclick="return confirm('Setujui produk ini untuk masuk ke katalog toko?')">
                                                        <i class="bx bx-check me-1"></i> Setujui
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.supplier-products.reject', $product->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-danger"
                                                        onclick="return confirm('Tolak produk ini?')">
                                                        <i class="bx bx-x me-1"></i> Tolak
                                                    </button>
                                                </form>
                                            @elseif ($product->is_rejected)
                                                <span class="dropdown-item text-muted">
                                                    <i class="bx bx-info-circle me-1"></i> Produk telah ditolak
                                                </span>
                                            @elseif ($product->notif_admin_seen == 1 && !$product->is_approved && !$product->is_rejected)
                                                <span class="dropdown-item text-muted">
                                                    <i class="bx bx-sleep me-1"></i> Belum ditawarkan supplier
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bx bx-package bx-lg text-muted mb-3 d-block"></i>
                                    <h6 class="text-muted">Belum ada produk supplier</h6>
                                    <p class="text-muted">Produk dari supplier akan muncul di sini setelah mereka
                                        menambahkan produk.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($products->hasPages())
                <div class="card-footer">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
