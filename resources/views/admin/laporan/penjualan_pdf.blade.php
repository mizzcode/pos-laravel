<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .info {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN PENJUALAN</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($tglAwal)->format('d/m/Y') }} -
            {{ \Carbon\Carbon::parse($tglAkhir)->format('d/m/Y') }}</p>
    </div>

    <div class="info">
        <p><strong>Tanggal Cetak:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
        <p><strong>Total Transaksi:</strong> {{ $orders->count() }} transaksi</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="10%">No</th>
                <th width="15%">Tanggal</th>
                <th width="20%">Customer</th>
                <th width="15%">ID Order</th>
                <th width="15%">Status</th>
                <th width="25%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $index => $order)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($order->tanggal_order)->format('d/m/Y H:i') }}</td>
                    <td>{{ $order->user->name ?? 'Guest' }}</td>
                    <td>{{ $order->midtrans_order_id ?? $order->id }}</td>
                    <td class="text-center">{{ ucfirst($order->status_order) }}</td>
                    <td class="text-right">Rp{{ number_format($order->total_order, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data penjualan pada periode ini</td>
                </tr>
            @endforelse

            @if ($orders->count() > 0)
                <tr class="total-row">
                    <td colspan="5" class="text-right"><strong>TOTAL PENJUALAN:</strong></td>
                    <td class="text-right"><strong>Rp{{ number_format($total, 0, ',', '.') }}</strong></td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada {{ now()->format('d/m/Y H:i:s') }} | Sistem POS Laravel</p>
    </div>
</body>

</html>
