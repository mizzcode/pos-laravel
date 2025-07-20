@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="card mx-auto" style="max-width:420px">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bx bx-credit-card" style="font-size: 3rem; color: #28a745;"></i>
                </div>
                <h4 class="mb-3">Pembayaran Order #{{ $order->id }}</h4>
                <p class="text-muted">Total: <strong>Rp{{ number_format($order->total_order, 0, ',', '.') }}</strong></p>
                <p class="small text-muted mb-4">Jendela pembayaran akan muncul otomatis. Jika tidak muncul, klik tombol di
                    bawah.</p>

                <button id="retry-pay" class="btn btn-success">
                    <i class="bx bx-credit-card"></i> Bayar Sekarang
                </button>

                <div class="mt-3">
                    <a href="{{ route('home.myorders.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bx bx-arrow-back"></i> Kembali ke Pesanan
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}">
    </script>
    <script>
        var orderId = @json($order->id);
        var snapToken = @json($snapToken);

        function showSnap() {
            if (!snapToken) {
                alert('Token pembayaran tidak tersedia');
                return;
            }

            if (!window.snap) {
                alert('Midtrans Snap belum dimuat. Silakan coba lagi.');
                return;
            }

            window.snap.pay(snapToken, {
                onSuccess: function(result) {
                    console.log('Payment Success:', result);
                    window.location.href = '/myorders/' + orderId;
                },
                onPending: function(result) {
                    console.log('Payment Pending:', result);
                    window.location.href = '/myorders/' + orderId;
                },
                onError: function(result) {
                    console.log('Payment Error:', result);
                    alert('Terjadi kesalahan dalam pembayaran');
                    window.location.href = '/myorders/' + orderId;
                },
                onClose: function() {
                    console.log('Payment closed by user');
                    window.location.href = '/myorders/' + orderId;
                }
            });
        }

        // Tunggu hingga halaman dan script sepenuhnya dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Cek apakah Snap sudah tersedia
            function checkSnapAvailability() {
                if (window.snap) {
                    showSnap();
                } else {
                    setTimeout(checkSnapAvailability, 500);
                }
            }

            // Mulai pengecekan setelah delay singkat
            setTimeout(checkSnapAvailability, 1000);

            // Event handler untuk tombol manual
            document.getElementById('retry-pay').onclick = function(e) {
                e.preventDefault();
                showSnap();
            };
        });
    </script>
@endpush
