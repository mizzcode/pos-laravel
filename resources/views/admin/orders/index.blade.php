@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="mb-0">Transaksi Penjualan</h5>
                    <a href="{{ route('orders.create') }}" class="btn btn-success">
                        <i class="bx bx-plus"></i> Transaksi Baru (Kasir)
                    </a>
                </div>
                <div class="card-body">
                    <form class="row g-2 mb-4" method="GET">
                        {{-- Tambahkan filter form di sini jika ada --}}
                    </form>

                    <div class="table-responsive text-nowrap">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ID Order</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Metode Bayar</th>
                                    <th>Alamat</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td>{{ ($orders->currentPage() - 1) * $orders->perPage() + $loop->iteration }}</td>
                                        <td>{{ $order->midtrans_order_id ?? '-' }}</td>
                                        <td>{{ $order->user ? $order->user->name : '-' }}</td>
                                        <td>Rp{{ number_format($order->total_order ?? 0, 0, ',', '.') }}</td>
                                        <td>
                                            @php $status = strtolower($order->status_order ?? $order->status); @endphp
                                            @if ($status == 'selesai' || $status == 'lunas')
                                                <span
                                                    class="badge bg-label-success">{{ ucfirst($order->status_order ?? $order->status) }}</span>
                                            @elseif($status == 'pending')
                                                <span
                                                    class="badge bg-label-warning">{{ ucfirst($order->status_order ?? $order->status) }}</span>
                                            @else
                                                <span
                                                    class="badge bg-label-secondary">{{ ucfirst($order->status_order ?? $order->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($order->payment && $order->payment->metode_bayar)
                                                <span>{{ ucfirst($order->payment->metode_bayar) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $order->alamat_kirim ?? '-' }}</td>
                                        <td>{{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '-' }}</td>
                                        <td>
                                            <a href="{{ route('orders.show', $order->midtrans_order_id) }}"
                                                class="btn btn-info btn-sm">
                                                <i class="bx bx-detail"></i> Detail
                                            </a>
                                            @if (strtolower($order->status_order ?? $order->status) === 'pending')
                                                <a href="{{ route('home.myorders.lanjutkan_pembayaran', $order->id) }}"
                                                    class="btn btn-warning btn-sm mt-1">
                                                    <i class="bx bx-refresh"></i> Bayar Ulang
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">Belum ada transaksi penjualan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $orders->links('pagination::bootstrap-5') }} {{-- Ganti sesuai CSS framework --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script copy ke clipboard (optional, hapus jika tidak dipakai) --}}
    @push('scripts')
        <script>
            document.querySelectorAll('.btn-copy-metode').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    navigator.clipboard.writeText(this.dataset.metode);
                });
            });
            document.querySelectorAll('.btn-copy-alamat').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    navigator.clipboard.writeText(this.dataset.alamat);
                });
            });
        </script>
    @endpush
@endsection
