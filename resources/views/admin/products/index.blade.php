@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Kelola Produk</h5>
                        <p class="text-muted mb-0 small">Produk toko dan supplier yang sudah disetujui</p>
                    </div>
                    <a href="{{ route('products.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Tambah Produk Toko
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Asal Produk</th>
                                    <th>Stok</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $i => $p)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            <div>{{ $p->nama_produk }}</div>
                                            <small class="text-muted">{{ $p->kode_produk }}</small>
                                        </td>
                                        <td>{{ $p->category->nama_kategori ?? '-' }}</td>
                                        <td>
                                            @if ($p->supplier_id)
                                                <div>
                                                    <strong>{{ $p->supplier->name }}</strong>
                                                    <br><small class="text-muted">Supplier</small>
                                                </div>
                                            @else
                                                <div>
                                                    <strong>Internal</strong>
                                                    <br><small class="text-muted">Produk Toko</small>
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $p->stok }}</td>
                                        <td>
                                            <a href="{{ route('products.show', $p->id) }}" class="btn btn-info btn-sm">
                                                <i class="bx bx-show"></i> Detail
                                            </a>
                                            <a href="{{ route('products.edit', $p->id) }}" class="btn btn-warning btn-sm">
                                                <i class="bx bx-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('products.destroy', $p->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Yakin hapus produk ini?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-danger btn-sm">
                                                    <i class="bx bx-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($products->isEmpty())
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Belum ada produk.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($products->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted small">
                                Menampilkan {{ $products->firstItem() ?? 0 }} sampai {{ $products->lastItem() ?? 0 }}
                                dari {{ $products->total() }} produk
                            </div>
                            <div>
                                {{ $products->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
