@extends('layouts.app')

@section('title', 'Detail Transaksi ' . $order->invoice_number)
@section('page-title', 'Detail Transaksi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <div>
                    <h5 class="fw-bold text-dark mb-0">Faktur #{{ $order->invoice_number }}</h5>
                    <small class="text-muted">{{ $order->created_at->format('d F Y, H:i:s') }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('pos.receipt', $order->id) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-printer me-1"></i> Cetak Struk
                    </a>
                    <a href="{{ route('transactions.index') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>

            <!-- Info Bar Transaksi -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <span class="text-muted small">Kasir Bertugas:</span>
                    <div class="fw-semibold text-dark">{{ $order->user ? $order->user->name : '-' }}</div>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Pelanggan:</span>
                    <div class="fw-semibold text-dark">{{ $order->customer ? $order->customer->name : 'Pelanggan Umum' }}</div>
                    @if($order->customer && $order->customer->phone)
                        <small class="text-muted">{{ $order->customer->phone }}</small>
                    @endif
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Metode Pembayaran:</span>
                    <div><span class="badge bg-primary text-uppercase">{{ $order->payment_method }}</span></div>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small">Status Otorisasi:</span>
                    <div>
                        @if($order->requires_approval)
                            <span class="badge bg-success-subtle text-success">Disetujui Pemilik</span>
                        @else
                            <span class="badge bg-light text-secondary border">Standar</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tabel Item -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td class="font-monospace small">{{ $item->product_sku }}</td>
                                <td class="fw-semibold text-dark">{{ $item->product_name }}</td>
                                <td class="text-end">Rp {{ number_format($item->sell_price, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">Subtotal:</th>
                            <td class="text-end fw-bold">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if($order->discount_amount > 0)
                        <tr>
                            <th colspan="5" class="text-end text-danger">Diskon ({{ $order->discount_percent }}%):</th>
                            <td class="text-end text-danger fw-bold">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr class="table-light">
                            <th colspan="5" class="text-end fs-6">GRAND TOTAL:</th>
                            <td class="text-end fw-bold fs-6 text-primary">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-end">Uang Dibayarkan:</th>
                            <td class="text-end">Rp {{ number_format($order->cash_paid, 0, ',', '.') }}</td>
                        </tr>
                        @if($order->payment_method === 'cash')
                        <tr>
                            <th colspan="5" class="text-end">Kembalian:</th>
                            <td class="text-end text-success fw-bold">Rp {{ number_format($order->cash_change, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    </tfoot>
                </table>
            </div>

            @if(auth()->user()->isPemilik())
                <div class="p-3 bg-light rounded-3 border">
                    <span class="small text-muted fw-semibold d-block mb-1">Informasi Profitabilitas (Khusus Pemilik):</span>
                    <div class="d-flex justify-content-between small">
                        <span>Total Modal (COGS): <strong>Rp {{ number_format($order->total_cost, 0, ',', '.') }}</strong></span>
                        <span class="text-success">Laba Kotor Transaksi: <strong>Rp {{ number_format($order->grand_total - $order->total_cost, 0, ',', '.') }}</strong></span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
