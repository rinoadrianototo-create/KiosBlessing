@extends('layouts.app')

@section('title', 'Dashboard Pemilik')
@section('page-title', 'Dashboard Ringkasan Usaha')

@section('content')
<div class="row g-3 mb-4">
    <!-- Kartu Penjualan Hari Ini -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold">Penjualan Hari Ini</span>
                    <h4 class="fw-bold text-dark mt-1 mb-0">Rp {{ number_format($todaySales, 0, ',', '.') }}</h4>
                    <small class="text-muted">{{ $todayTransactionsCount }} transaksi berhasil</small>
                </div>
                <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                    <i class="bi bi-cash-stack fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu Laba Kotor Hari Ini -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold">Laba Kotor Hari Ini</span>
                    <h4 class="fw-bold text-success mt-1 mb-0">Rp {{ number_format($todayGrossProfit, 0, ',', '.') }}</h4>
                    <small class="text-muted">Total Penjualan - Modal (HPP)</small>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-circle">
                    <i class="bi bi-graph-up-arrow fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu Laba Bersih Bulan Ini -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-4 border-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold">Laba Bersih Bulan Ini</span>
                    <h4 class="fw-bold text-info mt-1 mb-0">Rp {{ number_format($monthNetProfit, 0, ',', '.') }}</h4>
                    <small class="text-muted">Laba kotor dikurangi biaya</small>
                </div>
                <div class="bg-info-subtle text-info p-3 rounded-circle">
                    <i class="bi bi-piggy-bank fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu Peringatan Stok Menipis -->
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-4 {{ $lowStockCount > 0 ? 'border-danger' : 'border-secondary' }}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-semibold">Stok Menipis</span>
                    <h4 class="fw-bold {{ $lowStockCount > 0 ? 'text-danger' : 'text-dark' }} mt-1 mb-0">{{ $lowStockCount }} Barang</h4>
                    <small class="text-muted"><a href="{{ route('products.index', ['filter' => 'low_stock']) }}" class="text-decoration-none">Lihat & Restock &rarr;</a></small>
                </div>
                <div class="{{ $lowStockCount > 0 ? 'bg-danger-subtle text-danger' : 'bg-light text-muted' }} p-3 rounded-circle">
                    <i class="bi bi-exclamation-triangle fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Grafik Penjualan 7 Hari Terakhir -->
    <div class="col-lg-8">
        <div class="card card-custom p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bar-chart-line text-primary me-2"></i>Tren Penjualan 7 Hari Terakhir</h6>
                <span class="badge bg-light text-secondary">Harian</span>
            </div>
            <div style="height: 280px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Barang Terlaris -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 h-100">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-trophy text-warning me-2"></i>5 Barang Terlaris</h6>
            @if($topProducts->isEmpty())
                <div class="text-center text-muted py-5 small">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                    Belum ada transaksi penjualan tercatat.
                </div>
            @else
                <div class="list-group list-group-flush">
                    @foreach($topProducts as $idx => $prod)
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-secondary-subtle text-secondary me-2">{{ $idx + 1 }}</span>
                                <span class="fw-medium text-dark">{{ $prod->product_name }}</span>
                            </div>
                            <span class="badge bg-primary rounded-pill">{{ $prod->total_qty }} terjual</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Peringatan Stok Menipis -->
    <div class="col-lg-6">
        <div class="card card-custom p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bell text-danger me-2"></i>Peringatan Stok Menipis</h6>
                <a href="{{ route('stocks.create') }}" class="btn btn-sm btn-outline-danger">+ Tambah Stok</a>
            </div>

            @if($lowStockProducts->isEmpty())
                <div class="text-center text-muted py-4 small">
                    <i class="bi bi-check-circle text-success fs-3 d-block mb-1"></i>
                    Semua stok barang dalam kondisi aman.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Barang</th>
                                <th>Sisa Stok</th>
                                <th>Batas Min</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockProducts as $low)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $low->name }}</div>
                                        <small class="text-muted">{{ $low->sku }}</small>
                                    </td>
                                    <td><span class="badge bg-danger">{{ $low->stock }} {{ $low->unit }}</span></td>
                                    <td class="text-muted small">{{ $low->min_stock }} {{ $low->unit }}</td>
                                    <td>
                                        <a href="{{ route('stocks.create') }}?product_id={{ $low->id }}" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">Restock</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Transaksi Terbaru -->
    <div class="col-lg-6">
        <div class="card card-custom p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Transaksi Penjualan Terbaru</h6>
                <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-link text-decoration-none">Lihat Semua &rarr;</a>
            </div>

            @if($recentOrders->isEmpty())
                <div class="text-center text-muted py-4 small">
                    <i class="bi bi-receipt text-muted fs-3 d-block mb-1"></i>
                    Belum ada transaksi.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice</th>
                                <th>Kasir</th>
                                <th>Total</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('transactions.show', $order->id) }}" class="fw-semibold text-decoration-none">{{ $order->invoice_number }}</a>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ strtoupper($order->payment_method) }}</div>
                                    </td>
                                    <td class="small">{{ $order->user ? $order->user->name : '-' }}</td>
                                    <td class="fw-bold text-dark small">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                                    <td class="text-muted small">{{ $order->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Penjualan (Rp)',
                data: {!! json_encode($chartSalesData) !!},
                backgroundColor: 'rgba(13, 110, 253, 0.85)',
                borderColor: '#0d6efd',
                borderWidth: 1,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
