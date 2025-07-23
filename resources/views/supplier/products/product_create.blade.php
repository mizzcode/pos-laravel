@extends('layouts.supplier')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card mt-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tambah Produk Baru</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('supplier.products.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Nama Produk</label>
                                <input type="text" name="nama_produk" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->nama_kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Harga Jual</label>
                                <input type="number" name="harga_jual" class="form-control" required min="0">
                                <small class="text-muted">Harga yang Anda tawarkan ke toko</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Stok</label>
                                <input type="number" name="stok" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Foto Produk (opsional)</label>
                                <input type="file" name="foto[]" class="form-control" accept="image/*" multiple>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary"><i class="bx bx-save"></i> Simpan</button>
                            </div>
                        </form>
                        <a href="{{ route('supplier.products.index') }}" class="btn btn-link mt-3">
                            <i class="bx bx-arrow-back"></i> Kembali ke Daftar Produk
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
