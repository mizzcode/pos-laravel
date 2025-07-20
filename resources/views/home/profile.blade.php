@extends('layouts.app')

@section('content')
@php
    $role = auth()->user()->role;
    $profileData = [
        'nama'    => auth()->user()->name,
        'no_hp'   => auth()->user()->no_hp ?? '',
        'alamat'  => auth()->user()->alamat ?? '',
    ];
    if ($role === 'customer') {
        $customer = \App\Models\Customer::where('user_id', auth()->id())->first();
        if ($customer) {
            $profileData['nama']    = $customer->nama_customer ?? auth()->user()->name;
            $profileData['no_hp']   = $customer->no_hp ?? '';
            $profileData['alamat']  = $customer->alamat ?? '';
        }
    } elseif ($role === 'mitra') {
        $mitra = \App\Models\Mitra::where('user_id', auth()->id())->first();
        if ($mitra) {
            $profileData['nama']    = $mitra->nama_mitra ?? auth()->user()->name;
            $profileData['no_hp']   = $mitra->no_hp ?? '';
            $profileData['alamat']  = $mitra->alamat ?? '';
        }
    } elseif ($role === 'supplier') {
        $supplier = \App\Models\Supplier::where('user_id', auth()->id())->first();
        if ($supplier) {
            $profileData['nama']    = $supplier->nama_supplier ?? auth()->user()->name;
            $profileData['no_hp']   = $supplier->no_hp ?? '';
            $profileData['alamat']  = $supplier->alamat ?? '';
        }
    }
@endphp

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow mb-4 border-0">
            <div class="card-header bg-primary d-flex align-items-center">
                <i class="bx bx-user-circle me-2" style="font-size: 1.5rem"></i>
                <span class="text-white fw-semibold">Profil Saya</span>
            </div>
            <div class="card-body pb-3">
                @if(session('success'))
                    <div class="alert alert-success text-center">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="text-center mb-4">
                    <img src="{{ asset('assets/img/avatars/1.png') }}" class="rounded-circle shadow-sm" width="90" height="90" alt="Avatar">
                    <div class="fw-bold mt-2" style="font-size: 1.1rem;">
                        {{ $profileData['nama'] }}
                    </div>
                </div>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label text-end">
                            @if ($role == 'customer')
                                Nama Customer
                            @elseif ($role == 'mitra')
                                Nama Mitra
                            @elseif ($role == 'supplier')
                                Nama Supplier
                            @else
                                Nama
                            @endif
                        </label>
                        <div class="col-sm-8">
                            <input type="text" name="nama" class="form-control"
                                   value="{{ old('nama', $profileData['nama']) }}" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label text-end">Email</label>
                        <div class="col-sm-8">
                            <input type="email" class="form-control" readonly value="{{ auth()->user()->email }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label text-end">Nomor HP</label>
                        <div class="col-sm-8">
                            <input type="text" name="no_hp" class="form-control"
                                   value="{{ old('no_hp', $profileData['no_hp']) }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-4 col-form-label text-end">Alamat</label>
                        <div class="col-sm-8">
                            <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', $profileData['alamat']) }}</textarea>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label class="col-sm-4 col-form-label text-end">Role</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control-plaintext" readonly value="{{ ucfirst($role) }}">
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bx bx-save"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('home.katalog') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
