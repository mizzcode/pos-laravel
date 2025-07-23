@extends('layouts.supplier')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Info Status Panel -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <a href="{{ route('supplier.products.index', ['status' => 'belum_tawarkan']) }}"
                            class="text-decoration-none">
                            <div
                                class="card text-center border-secondary {{ request('status') === 'belum_tawarkan' ? 'bg-light' : '' }}">
                                <div class="card-body">
                                    <span class="badge bg-secondary">Belum Ditawarkan</span>
                                    <h4 class="mt-2 mb-1 text-secondary">{{ $statusCounts['belum_tawarkan'] }}</h4>
                                    <p class="small mt-1 mb-0 text-muted">Produk belum ditawarkan ke toko</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('supplier.products.index', ['status' => 'menunggu']) }}"
                            class="text-decoration-none">
                            <div
                                class="card text-center border-warning {{ request('status') === 'menunggu' ? 'bg-light' : '' }}">
                                <div class="card-body">
                                    <span class="badge bg-warning">Menunggu Review</span>
                                    <h4 class="mt-2 mb-1 text-warning">{{ $statusCounts['menunggu'] }}</h4>
                                    <p class="small mt-1 mb-0 text-muted">Produk sedang ditinjau toko</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('supplier.products.index', ['status' => 'diterima']) }}"
                            class="text-decoration-none">
                            <div
                                class="card text-center border-success {{ request('status') === 'diterima' ? 'bg-light' : '' }}">
                                <div class="card-body">
                                    <span class="badge bg-success">Diterima Toko</span>
                                    <h4 class="mt-2 mb-1 text-success">{{ $statusCounts['diterima'] }}</h4>
                                    <p class="small mt-1 mb-0 text-muted">Produk sudah masuk katalog toko</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('supplier.products.index', ['status' => 'ditolak']) }}"
                            class="text-decoration-none">
                            <div
                                class="card text-center border-danger {{ request('status') === 'ditolak' ? 'bg-light' : '' }}">
                                <div class="card-body">
                                    <span class="badge bg-danger">Ditolak Toko</span>
                                    <h4 class="mt-2 mb-1 text-danger">{{ $statusCounts['ditolak'] }}</h4>
                                    <p class="small mt-1 mb-0 text-muted">Produk ditolak oleh toko</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Produk Saya</h5>
                            <small class="text-muted">
                                @if (request('status'))
                                    @switch(request('status'))
                                        @case('belum_tawarkan')
                                            Menampilkan: {{ $products->total() }} produk belum ditawarkan
                                        @break

                                        @case('menunggu')
                                            Menampilkan: {{ $products->total() }} produk menunggu review
                                        @break

                                        @case('diterima')
                                            Menampilkan: {{ $products->total() }} produk diterima
                                        @break

                                        @case('ditolak')
                                            Menampilkan: {{ $products->total() }} produk ditolak
                                        @break
                                    @endswitch
                                    | <a href="{{ route('supplier.products.index') }}" class="text-primary">Lihat Semua</a>
                                @else
                                    Total: {{ $products->total() }} produk
                                @endif
                            </small>
                        </div>
                        <a href="{{ route('supplier.products.create') }}" class="btn btn-success">
                            <i class="bx bx-plus"></i> Tambah Produk
                        </a>
                    </div>
                    <div class="card-body">
                        {{-- Filter & Search --}}
                        <form method="GET" class="row g-2 align-items-end mb-3">
                            <div class="col-md-3">
                                <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                                    placeholder="Cari nama produk...">
                            </div>
                            <div class="col-md-3">
                                <select name="category_id" class="form-select">
                                    <option value="">Semua Kategori</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="belum_tawarkan"
                                        {{ request('status') === 'belum_tawarkan' ? 'selected' : '' }}>Belum Ditawarkan
                                    </option>
                                    <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>
                                        Menunggu Review</option>
                                    <option value="diterima" {{ request('status') === 'diterima' ? 'selected' : '' }}>
                                        Diterima Toko</option>
                                    <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak
                                        Toko</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary w-100"><i class="bx bx-search"></i> Filter</button>
                            </div>
                            @if (request()->has('q') || request()->has('category_id') || request()->has('status'))
                                <div class="col-md-1">
                                    <a href="{{ route('supplier.products.index') }}"
                                        class="btn btn-outline-secondary w-100">Reset</a>
                                </div>
                            @endif
                        </form>

                        <div class="table-responsive text-nowrap">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Produk</th>
                                        <th>Kategori</th>
                                        <th>Deskripsi</th>
                                        <th>Harga Jual</th>
                                        <th>Stok</th>
                                        <th>Status Toko</th>
                                        <th>Aksi</th>
                                        <th>Tawarkan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($products as $i => $p)
                                        <tr>
                                            <td>{{ $products->firstItem() + $i }}</td>
                                            <td>{{ $p->nama_produk }}</td>
                                            <td>{{ $p->category->nama_kategori ?? '-' }}</td>
                                            <td>
                                                @if ($p->deskripsi)
                                                    <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                                        title="{{ $p->deskripsi }}">
                                                        {{ $p->deskripsi }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>Rp{{ number_format($p->harga_jual, 0, ',', '.') }}</td>
                                            <td>{{ $p->stok }}</td>
                                            <td>
                                                @if ($p->is_approved)
                                                    <span class="badge bg-success">
                                                        <i class="bx bx-check"></i> Diterima Toko
                                                    </span>
                                                @elseif($p->is_rejected)
                                                    <span class="badge bg-danger">
                                                        <i class="bx bx-x"></i> Ditolak Toko
                                                    </span>
                                                @elseif($p->notif_admin_seen == 0)
                                                    <span class="badge bg-warning">
                                                        <i class="bx bx-time"></i> Menunggu Review
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="bx bx-sleep"></i> Belum Ditawarkan
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('supplier.products.edit', $p->id) }}"
                                                    class="btn btn-primary btn-sm mb-1">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                <form action="{{ route('supplier.products.destroy', $p->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-danger btn-sm"><i
                                                            class="bx bx-trash"></i></button>
                                                </form>
                                            </td>
                                            <td>
                                                @if ($p->is_approved)
                                                    <span class="badge bg-success">Sudah di Katalog Toko</span>
                                                @elseif($p->is_rejected)
                                                    <form action="{{ route('supplier.products.offerToStore', $p->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <button class="btn btn-outline-warning btn-sm fw-bold"
                                                            onclick="return confirm('Ajukan ulang produk yang ditolak ini?')">
                                                            <i class="bx bx-refresh fw-bold"></i> Ajukan Ulang
                                                        </button>
                                                    </form>
                                                    @if ($p->rejection_reason)
                                                        <small
                                                            class="text-danger d-block mt-1">{{ $p->rejection_reason }}</small>
                                                    @endif
                                                @elseif($p->notif_admin_seen == 0)
                                                    <button class="btn btn-outline-info btn-sm" disabled>
                                                        <i class="bx bx-loader bx-spin"></i> Sedang Ditinjau
                                                    </button>
                                                @else
                                                    <form action="{{ route('supplier.products.offerToStore', $p->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <button class="btn btn-warning btn-sm"
                                                            onclick="return confirm('Tawarkan produk ini ke admin toko?')">
                                                            <i class="bx bx-send"></i> Tawarkan ke Toko
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">Belum ada produk.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-3">
                            {{ $products->withQueryString()->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
