@extends('layouts.supplier')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card mt-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit Produk</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('supplier.products.update', $product->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nama Produk</label>
                            <input type="text" name="nama_produk" class="form-control" required value="{{ old('nama_produk', $product->nama_produk) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->nama_kategori }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga Jual</label>
                            <input type="number" name="harga_jual" class="form-control" required value="{{ old('harga_jual', $product->harga_jual) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stok" class="form-control" required value="{{ old('stok', $product->stok) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="2">{{ old('deskripsi', $product->deskripsi) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Produk (upload baru untuk menambah, max 2MB per file)</label>
                            <input type="file" name="foto[]" class="form-control" accept="image/*" multiple>
                        </div>
                        @if($product->images && count($product->images))
                        <div class="mb-3">
                            <label class="form-label">Foto Saat Ini:</label><br>
                            @foreach($product->images as $img)
                            <div class="d-inline-block text-center me-2 mb-2">
                                <img src="{{ asset('storage/'.$img->file_path) }}" width="65" class="rounded shadow mb-1">
                                <form action="{{ route('products.images.destroy', $img->id) }}" method="POST" onsubmit="return confirm('Hapus foto ini?')" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger d-block mx-auto mt-1" title="Hapus Foto">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary"><i class="bx bx-save"></i> Simpan Perubahan</button>
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