@extends('layouts.admin')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card mb-4">
            <h5 class="card-header">Edit Produk</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('products.update', $product->id) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" name="nama_produk" id="nama_produk" class="form-control @error('nama_produk') is-invalid @enderror"
                            value="{{ old('nama_produk', $product->nama_produk) }}" required>
                        @error('nama_produk')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Kategori</label>
                        <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (old('category_id', $product->category_id) == $cat->id) ? 'selected' : '' }}>
                                {{ $cat->nama_kategori }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="kode_produk" class="form-label">Kode Produk</label>
                        <input type="text" name="kode_produk" id="kode_produk" class="form-control @error('kode_produk') is-invalid @enderror"
                            value="{{ old('kode_produk', $product->kode_produk) }}" required>
                        @error('kode_produk')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="supplier_id" class="form-label">Supplier (Opsional)</label>
                        <select name="supplier_id" id="supplier_id" class="form-select">
                            <option value="">Internal (tanpa supplier)</option>
                            @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ (old('supplier_id', $product->supplier_id) == $sup->id) ? 'selected' : '' }}>
                                {{ $sup->nama_supplier ?? $sup->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="stok" class="form-label">Stok</label>
                        <input type="number" name="stok" id="stok" class="form-control @error('stok') is-invalid @enderror"
                            min="0" value="{{ old('stok', $product->stok) }}" required>
                        @error('stok')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="harga_beli" class="form-label">Harga Beli</label>
                        <input type="number" name="harga_beli" id="harga_beli" class="form-control @error('harga_beli') is-invalid @enderror"
                            min="0" value="{{ old('harga_beli', $product->harga_beli) }}">
                        @error('harga_beli')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="harga_jual" class="form-label">Harga Jual</label>
                        <input type="number" name="harga_jual" id="harga_jual" class="form-control @error('harga_jual') is-invalid @enderror"
                            min="0" value="{{ old('harga_jual', $product->harga_jual) }}" required>
                        @error('harga_jual')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="2">{{ old('deskripsi', $product->deskripsi) }}</textarea>
                        @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="images" class="form-label">Foto Produk (tambahkan jika ingin ganti/menambah)</label>
                        <input type="file" name="images[]" id="images" class="form-control @error('images') is-invalid @enderror" multiple>
                        @error('images')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        @if(isset($product->images) && count($product->images))
                        <div class="mt-2">
                            <small>Foto saat ini:</small><br>
                            @foreach($product->images as $img)
                            <img src="{{ asset('storage/'.$img->file_path) }}" width="60" class="me-2 mb-2 rounded border">
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="bx bx-arrow-back"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection