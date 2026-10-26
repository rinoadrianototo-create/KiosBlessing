@extends('layouts.app')

@section('title', 'Mutasi Stok')
@section('page-title', 'Riwayat Mutasi & Perubahan Stok')

@section('content')
<div class="card card-custom p-4 mb-4">
    <div class="row g-3 align-items-center justify-content-between">
        <div class="col-md-8">
            <form action="{{ route('stocks.index') }}" method="GET" class="d-flex flex-wrap gap-2">
                <select name="type" class="form-select bg-light" style="max-width: 170px;" onchange="this.form.submit()">
                    <option value="">Semua Tipe Mutasi</option>
                    <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Barang Masuk (Restock)</option>
                    <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Barang Keluar (Penyesuaian)</option>
                    <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>Penjualan Kasir (POS)</option>
                </select>

                <select name="product_id" class="form-select bg-light" style="max-width: 250px;" onchange="this.form.submit()">
                    <option value="">Semua Barang</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-light"><i class="bi bi-funnel"></i> Filter</button>
                @if(request()->hasAny(['type', 'product_id']))
                    <a href="{{ route('stocks.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </form>
        </div>

        <div class="col-md-4 text-md-end">
            <a href="{{ route('stocks.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Catat Stok Masuk / Keluar
            </a>
        </div>
    </div>
</div>

<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Waktu</th>
                    <th>No. Referensi</th>
                    <th>Barang</th>
                    <th>Tipe Mutasi</th>
                    <th class="text-center">Jumlah</th>
                    <th class="text-center">Stok Sebelum</th>
                    <th class="text-center">Stok Sesudah</th>
                    <th>Operator</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mutations as $m)
                    <tr>
                        <td class="small text-muted">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td><span class="badge bg-light text-dark font-monospace border">{{ $m->reference_number ?: '-' }}</span></td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $m->product ? $m->product->name : 'Barang Dihapus' }}</div>
                            <small class="text-muted">{{ $m->product ? $m->product->sku : '' }}</small>
                        </td>
                        <td>
                            @if($m->type === 'in')
                                <span class="badge bg-success-subtle text-success"><i class="bi bi-box-arrow-in-down me-1"></i>Masuk</span>
                            @elseif($m->type === 'out')
                                <span class="badge bg-danger-subtle text-danger"><i class="bi bi-box-arrow-up me-1"></i>Keluar</span>
                            @else
                                <span class="badge bg-primary-subtle text-primary"><i class="bi bi-cart-check me-1"></i>Penjualan</span>
                            @endif
                        </td>
                        <td class="text-center fw-bold {{ $m->type === 'in' ? 'text-success' : 'text-danger' }}">
                            {{ $m->type === 'in' ? '+' : '-' }}{{ $m->quantity }}
                        </td>
                        <td class="text-center text-muted small">{{ $m->stock_before }}</td>
                        <td class="text-center fw-semibold text-dark">{{ $m->stock_after }}</td>
                        <td class="small">{{ $m->user ? $m->user->name : '-' }}</td>
                        <td class="small text-muted">{{ $m->notes ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-arrow-left-right fs-2 d-block mb-2"></i>
                            Belum ada riwayat mutasi stok.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $mutations->links() }}
    </div>
</div>
@endsection
