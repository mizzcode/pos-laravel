@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-primary text-white d-flex align-items-center">
                <i class="bx bx-cart me-2"></i>
                <span class="fw-semibold">Keranjang Belanja</span>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @elseif(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @php $totalQty = 0; @endphp

                @if(count($keranjang) > 0)
                <div class="table-responsive mb-4">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $total = 0; @endphp
                            @foreach($keranjang as $item)
                            @php 
                                $subtotal = $item['harga'] * $item['qty']; 
                                $total += $subtotal; 
                                $totalQty += $item['qty'];
                            @endphp
                            <tr>
                                <td>{{ $item['nama'] }}</td>
                                <td class="text-end">Rp{{ number_format($item['harga'],0,',','.') }}</td>
                                <td class="text-center">
                                    <form action="{{ route('home.keranjang.update', $item['id']) }}" method="POST" class="keranjang-form" style="gap: 5px;">
                                        @csrf
                                        <input type="number" name="qty" value="{{ $item['qty'] }}" min="1" max="999"
                                            style="width: 60px; text-align:center;" class="form-control form-control-sm qty-input" required
                                            data-id="{{ $item['id'] }}">
                                    </form>
                                </td>
                                <td class="text-end">Rp{{ number_format($subtotal,0,',','.') }}</td>
                                <td class="text-center">
                                    <form action="{{ route('home.keranjang.hapus', $item['id']) }}" method="POST" class="d-inline">
                                        @csrf @method('POST')
                                        <button class="btn btn-outline-danger btn-sm">
                                            <i class="bx bx-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <td colspan="3" class="text-end">Total</td>
                                <td colspan="2" class="text-end text-primary">Rp{{ number_format($total,0,',','.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <form action="{{ route('home.checkout') }}" method="POST" class="mt-3" id="form-checkout">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Alamat Pengiriman <small class="text-muted">(isi jika domisili Tegal, kosongkan jika bukan)</small></label>
                        <textarea name="alamat" id="alamat" class="form-control" rows="2"
                            placeholder="Kosongkan jika bukan domisili Tegal"></textarea>
                        <div class="small text-danger mt-1 d-none" id="alamat-warning">
                            Jika Anda mengisi alamat, wajib mencantumkan kata <b>"Tegal"</b>.
                        </div>
                    </div>
                    @if($role == 'mitra')
                    <div class="alert alert-info py-2" id="mitra-warning" style="font-size: 15px;">
                        Minimal total pembelian untuk Mitra: <b>10 pcs</b> (semua produk)
                    </div>
                    @endif
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" id="pay-button" class="btn btn-success" disabled>
                            <i class="bx bx-credit-card"></i> Checkout & Bayar
                        </button>
                        <a href="{{ route('home.katalog') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back"></i> Kembali Belanja
                        </a>
                    </div>
                </form>
                @else
                <div class="alert alert-warning text-center">
                    <i class="bx bx-cart-alt fs-3"></i><br>
                    Keranjang Anda kosong. Yuk, belanja dulu!
                </div>
                <div class="text-center">
                    <a href="{{ route('home.katalog') }}" class="btn btn-primary">
                        <i class="bx bx-store"></i> Ke Katalog Produk
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-submit form saat qty diubah
    document.querySelectorAll('.qty-input').forEach(function(input) {
        input.addEventListener('change', function() {
            if(this.value < 1) this.value = 1;
            this.form.submit();
        });
    });

    // Cek validasi checkout tanpa reload
    const alamat = document.getElementById('alamat');
    const btn = document.getElementById('pay-button');
    const warning = document.getElementById('alamat-warning');
    const isMitra = @json($role == 'mitra');
    function cekForm() {
        let val = alamat.value.trim().toLowerCase();
        let alamatOk = false;

        if (val.length === 0) {
            alamatOk = true;
            warning.classList.add('d-none');
        } else if (val.includes('tegal')) {
            alamatOk = true;
            warning.classList.add('d-none');
        } else {
            alamatOk = false;
            warning.classList.remove('d-none');
        }

        // Mitra qty sudah pasti sinkron (karena auto-submit)
        let mitraOk = true;
        @if($role == 'mitra')
        mitraOk = {{ $totalQty }} >= 10;
        document.getElementById('mitra-warning').classList.toggle('alert-danger', !mitraOk);
        document.getElementById('mitra-warning').classList.toggle('alert-info', mitraOk);
        @endif

        btn.disabled = !(alamatOk && mitraOk);
    }
    alamat.addEventListener('input', cekForm);
    cekForm();
});
</script>
@endpush
