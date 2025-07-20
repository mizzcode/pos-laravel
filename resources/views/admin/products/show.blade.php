@extends('layouts.admin')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7 col-md-9">
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <i class="bx bx-package me-2"></i> Detail Produk
                </div>
                @if($product->is_verified)
                    <span class="badge bg-success">Sudah Disetujui</span>
                @else
                    <span class="badge bg-warning text-dark">Belum Disetujui</span>
                @endif
            </div>
            <div class="card-body">
                <h4 class="mb-2 fw-bold">{{ $product->nama_produk }}</h4>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Kategori</div>
                    <div class="col-7">{{ $product->category->nama_kategori ?? '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Supplier</div>
                    <div class="col-7">{{ $product->supplier->name ?? '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Harga Jual</div>
                    <div class="col-7 fw-semibold text-success">Rp{{ number_format($product->harga_jual,0,',','.') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Stok</div>
                    <div class="col-7">{{ $product->stok }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 text-muted">Deskripsi</div>
                    <div class="col-7">{{ $product->deskripsi ?? '-' }}</div>
                </div>
                @if($product->images && count($product->images))
                <div class="mb-2">
                    <div class="text-muted mb-1">Foto Produk:</div>
                    <div>
                        @foreach($product->images as $img)
                            <img src="{{ asset('storage/'.$img->file_path) }}" width="90" class="rounded border m-1" style="object-fit:cover;max-height:90px;">
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            <div class="card-footer text-end bg-light">
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali ke Daftar Produk
                </a>
                @if(!$product->is_verified)
                <form action="{{ route('products.update', $product->id) }}" method="POST" class="d-inline">
                    @csrf @method('PUT')
                    <input type="hidden" name="is_verified" value="1">
                    <button class="btn btn-success"><i class="bx bx-check"></i> Setujui Produk</button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
