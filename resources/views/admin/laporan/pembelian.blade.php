@extends('layouts.admin')
@section('content')
    <div class="row mb-4">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-0"><i class="bx bx-cart-alt"></i> Laporan Pembelian ke Supplier</h5>
                </div>
                <div class="card-body pt-3">
                    <form method="GET" class="row g-2 align-items-end mb-4">
                        <div class="col-md-5">
                            <label for="tgl_awal" class="form-label mb-0">Tanggal Awal</label>
                            <input type="date" name="tgl_awal" id="tgl_awal" class="form-control"
                                value="{{ $tglAwal }}">
                        </div>
                        <div class="col-md-5">
                            <label for="tgl_akhir" class="form-label mb-0">Tanggal Akhir</label>
                            <input type="date" name="tgl_akhir" id="tgl_akhir" class="form-control"
                                value="{{ $tglAkhir }}">
                        </div>
                        <div class="col-md-2 d-flex mt-3 mt-md-0">
                            <button class="btn btn-primary w-100">
                                <i class="bx bx-search"></i> Filter
                            </button>
                        </div>
                    </form>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Supplier</th>
                                    <th>No. PO</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $p)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y H:i') }}</td>
                                        <td>{{ $p->supplier->nama_supplier ?? '-' }}</td>
                                        <td>{{ $p->id }}</td>
                                        <td>Rp{{ number_format($p->total_beli, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada data pembelian selesai.
                                        </td>
                                    </tr>
                                @endforelse
                                <tr class="table-active fw-bold">
                                    <td colspan="3" class="text-end">Total</td>
                                    <td class="text-success">Rp{{ number_format($total, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
