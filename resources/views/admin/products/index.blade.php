@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Kelola Produk</h5>
                <a href="{{ route('products.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus"></i> Tambah Produk
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
                                <th>Supplier</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $i => $p)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $p->nama_produk }}</td>
                                <td>{{ $p->category->nama_kategori ?? '-' }}</td>
                                <td>
                                    @if($p->supplier)
                                    {{ $p->supplier->name }}
                                    @else
                                    Internal
                                    @endif
                                </td>
                                <td>{{ $p->stok }}</td>
                                <td>
                                    <a href="{{ route('products.edit', $p->id) }}" class="btn btn-warning btn-sm">
                                        <i class="bx bx-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('products.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus produk ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm">
                                            <i class="bx bx-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            @if($products->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada produk.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection