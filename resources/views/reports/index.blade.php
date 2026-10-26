@extends('layouts.app')

@section('title', 'Laporan & Ekspor')
@section('page-title', 'Laporan Operasional & Ekspor')

@section('content')
<div class="card card-custom p-4 mb-4">
    <form action="{{ route('reports.index') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-secondary">Jenis Laporan</label>
            <select name="type" class="form-select bg-light">
                <option value="sales" {{ $reportType === 'sales' ? 'selected' : '' }}>Laporan Penjualan Kasir</option>
                <option value="profit_loss" {{ $reportType === 'profit_loss' ? 'selected' : '' }}>Laporan Laba Rugi Usaha</option>
                <option value="stock" {{ $reportType === 'stock' ? 'selected' : '' }}>Laporan Mutasi Stok</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label small fw-semibold text-secondary">Dari Tanggal</label>
            <input type="date" name="start_date" class="form-control bg-light" value="{{ $startDate }}">
        </div>

        <div class="col-md-3">
            <label class="form-label small fw-semibold text-secondary">Sampai Tanggal</label>
            <input type="date" name="end_date" class="form-control bg-light" value="{{ $endDate }}">
        </div>

        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">
                <i class="bi bi-search me-1"></i> Tampilkan
            </button>
            <a href="{{ route('reports.export', ['type' => $reportType, 'start_date' => $startDate, 'end_date' => $endDate, 'format' => 'excel']) }}" class="btn btn-success" title="Ekspor Excel">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
            <a href="{{ route('reports.export', ['type' => $reportType, 'start_date' => $startDate, 'end_date' => $endDate, 'format' => 'pdf']) }}" target="_blank" class="btn btn-danger" title="Cetak / Unduh PDF">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
    </form>
</div>

<!-- Tampilan Hasil Laporan Sesuai Jenis -->
<div class="card card-custom p-4">
    @if($reportType === 'sales')
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-dark mb-0">Laporan Penjualan ({{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }})</h6>
            <div class="text-end">
                <span class="text-muted small">Total Omzet:</span>
                <span class="fw-bold fs-6 text-primary ms-1">Rp {{ number_format($totalSales, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice</th>
                        <th>Waktu</th>
                        <th>Kasir</th>
                        <th>Pelanggan</th>
                        <th>Metode</th>
                        <th class="text-end">Diskon</th>
                        <th class="text-end">Modal (HPP)</th>
                        <th class="text-end">Grand Total</th>
                        <th class="text-end">Laba Kotor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                        <tr>
                            <td><a href="{{ route('transactions.show', $o->id) }}" class="fw-semibold text-decoration-none">{{ $o->invoice_number }}</a></td>
                            <td class="small">{{ $o->created_at->format('d/m/Y H:i') }}</td>
                            <td class="small">{{ $o->user ? $o->user->name : '-' }}</td>
                            <td class="small">{{ $o->customer ? $o->customer->name : 'Umum' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ strtoupper($o->payment_method) }}</span></td>
                            <td class="text-end text-muted small">Rp {{ number_format($o->discount_amount, 0, ',', '.') }}</td>
                            <td class="text-end text-muted small">Rp {{ number_format($o->total_cost, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-dark">Rp {{ number_format($o->grand_total, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold text-success">Rp {{ number_format($o->grand_total - $o->total_cost, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">Tidak ada transaksi penjualan pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(!$orders->isEmpty())
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="6" class="text-end">TOTAL KESELURUHAN:</td>
                        <td class="text-end text-muted">Rp {{ number_format($totalCogs, 0, ',', '.') }}</td>
                        <td class="text-end text-primary">Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
                        <td class="text-end text-success">Rp {{ number_format($totalGrossProfit, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

    @elseif($reportType === 'profit_loss')
        <div class="mb-4">
            <h6 class="fw-bold text-dark mb-1">Laporan Laba Rugi Komprehensif</h6>
            <small class="text-muted">Periode: {{ date('d F Y', strtotime($startDate)) }} s/d {{ date('d F Y', strtotime($endDate)) }}</small>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <tbody>
                            <tr class="table-light">
                                <th colspan="2" class="fw-bold text-primary">A. PENDAPATAN OPERASIONAL</th>
                            </tr>
                            <tr>
                                <td class="ps-4">Total Penjualan Kasir (POS)</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($posSalesTotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Harga Pokok Penjualan (HPP Modal Barang Terjual)</td>
                                <td class="text-end text-danger fw-semibold">- Rp {{ number_format($posCogsTotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="bg-success-subtle">
                                <th class="ps-4 text-success fw-bold">LABA KOTOR (GROSS PROFIT)</th>
                                <td class="text-end text-success fw-bold">Rp {{ number_format($grossProfit, 0, ',', '.') }}</td>
                            </tr>

                            <tr class="table-light">
                                <th colspan="2" class="fw-bold text-primary">B. BIAYA & BEBAN OPERASIONAL</th>
                            </tr>
                            <tr>
                                <td class="ps-4">Pengeluaran Operasional (Listrik, Sewa, Gaji, dll)</td>
                                <td class="text-end text-danger fw-semibold">- Rp {{ number_format($operatingExpense, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4">Pemasukan Operasional Lain-lain</td>
                                <td class="text-end text-success fw-semibold">+ Rp {{ number_format($otherIncome, 0, ',', '.') }}</td>
                            </tr>

                            <tr class="bg-primary-subtle fs-6">
                                <th class="ps-4 text-primary fw-bold">LABA BERSIH (NET PROFIT)</th>
                                <td class="text-end text-primary fw-bold">Rp {{ number_format($netProfit, 0, ',', '.') }}</td>
                            </tr>

                            <tr class="table-light">
                                <th colspan="2" class="fw-bold text-secondary">C. INFORMASI ARUS KAS LAINNYA</th>
                            </tr>
                            <tr>
                                <td class="ps-4 text-muted">Penyertaan Modal Kios</td>
                                <td class="text-end text-muted">Rp {{ number_format($capital, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4 text-muted">Pembelian Stok Barang (Inventaris Baru)</td>
                                <td class="text-end text-muted">Rp {{ number_format($stockPurchase, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @elseif($reportType === 'stock')
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-dark mb-0">Laporan Mutasi Stok ({{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }})</h6>
            <span class="badge bg-secondary">{{ $mutations->count() }} Mutasi</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Waktu</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Tipe</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-center">Stok Awal</th>
                        <th class="text-center">Stok Akhir</th>
                        <th>No. Referensi</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutations as $m)
                        <tr>
                            <td class="small">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                            <td class="font-monospace small">{{ $m->product ? $m->product->sku : '-' }}</td>
                            <td class="fw-semibold text-dark">{{ $m->product ? $m->product->name : '-' }}</td>
                            <td>
                                @if($m->type === 'in')
                                    <span class="badge bg-success-subtle text-success">Masuk</span>
                                @elseif($m->type === 'out')
                                    <span class="badge bg-danger-subtle text-danger">Keluar</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary">Penjualan</span>
                                @endif
                            </td>
                            <td class="text-center fw-bold">{{ $m->quantity }}</td>
                            <td class="text-center text-muted small">{{ $m->stock_before }}</td>
                            <td class="text-center fw-semibold text-dark">{{ $m->stock_after }}</td>
                            <td class="small font-monospace text-muted">{{ $m->reference_number ?: '-' }}</td>
                            <td class="small text-muted">{{ $m->notes ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">Tidak ada mutasi stok pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
