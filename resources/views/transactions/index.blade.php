@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('page-title', 'Riwayat Transaksi Penjualan')

@section('content')
<div class="card card-custom p-4 mb-4">
    <form action="{{ route('transactions.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Cari nomor invoice..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-text bg-light">Dari</span>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
        </div>

        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-text bg-light">Sampai</span>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
        </div>

        <div class="col-md-2">
            <select name="payment_method" class="form-select bg-light" onchange="this.form.submit()">
                <option value="">Semua Metode</option>
                <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Tunai</option>
                <option value="qris" {{ request('payment_method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                <option value="transfer" {{ request('payment_method') === 'transfer' ? 'selected' : '' }}>Transfer</option>
            </select>
        </div>

        <div class="col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter"></i></button>
            @if(request()->hasAny(['search', 'start_date', 'end_date', 'payment_method']))
                <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
            @endif
        </div>
    </form>
</div>

<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Invoice</th>
                    <th>Waktu</th>
                    <th>Kasir</th>
                    <th>Pelanggan</th>
                    <th>Metode</th>
                    <th class="text-end">Diskon</th>
                    <th class="text-end">Grand Total</th>
                    @if(auth()->user()->isPemilik())
                        <th class="text-end">Laba Kotor</th>
                    @endif
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                    <tr>
                        <td>
                            <a href="{{ route('transactions.show', $o->id) }}" class="fw-bold text-decoration-none">
                                {{ $o->invoice_number }}
                            </a>
                            @if($o->requires_approval)
                                <span class="badge bg-warning-subtle text-warning border ms-1" title="Diskon disetujui pemilik"><i class="bi bi-shield-check"></i> Disetujui</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $o->created_at->format('d/m/Y H:i') }}</td>
                        <td class="small">{{ $o->user ? $o->user->name : '-' }}</td>
                        <td class="small">{{ $o->customer ? $o->customer->name : 'Pelanggan Umum' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ strtoupper($o->payment_method) }}</span>
                        </td>
                        <td class="text-end text-muted small">
                            {{ $o->discount_amount > 0 ? 'Rp ' . number_format($o->discount_amount, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-end fw-bold text-dark">
                            Rp {{ number_format($o->grand_total, 0, ',', '.') }}
                        </td>
                        @if(auth()->user()->isPemilik())
                            <td class="text-end fw-semibold text-success small">
                                Rp {{ number_format($o->grand_total - $o->total_cost, 0, ',', '.') }}
                            </td>
                        @endif
                        <td class="text-center">
                            <a href="{{ route('transactions.show', $o->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.78rem;">
                                Detail
                            </a>
                            <a href="{{ route('pos.receipt', $o->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.78rem;" title="Cetak Struk">
                                <i class="bi bi-printer"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isPemilik() ? 9 : 8 }}" class="text-center py-5 text-muted">
                            <i class="bi bi-receipt fs-2 d-block mb-2"></i>
                            Belum ada riwayat transaksi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
@endsection
