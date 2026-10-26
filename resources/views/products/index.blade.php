@extends('layouts.app')

@section('title', 'Daftar Barang')
@section('page-title', 'Katalog Barang & Inventaris')

@section('content')
<div class="card card-custom p-4 mb-4">
    <div class="row g-3 align-items-center justify-content-between">
        <div class="col-md-6 col-lg-7">
            <form action="{{ route('products.index') }}" method="GET" class="d-flex flex-wrap gap-2">
                @if(request('filter'))
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                @endif
                <div class="input-group" style="max-width: 320px;">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Cari nama, SKU, barcode..." value="{{ request('search') }}">
                </div>

                <select name="category_id" class="form-select bg-light" style="max-width: 180px;" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-light"><i class="bi bi-funnel"></i> Filter</button>
                @if(request()->hasAny(['search', 'category_id', 'filter']))
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </form>
        </div>

        <div class="col-md-6 col-lg-5 text-md-end d-flex gap-2 justify-content-md-end">
            <a href="{{ route('stocks.create') }}" class="btn btn-outline-success">
                <i class="bi bi-box-arrow-in-down me-1"></i> + Stok Masuk
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Tambah Barang
            </a>
        </div>
    </div>
</div>

<!-- Tabs Status Barang -->
<ul class="nav nav-pills mb-3 gap-2">
    <li class="nav-item">
        <a class="nav-link {{ !request('filter') ? 'active' : 'bg-white text-secondary' }} border" href="{{ route('products.index', array_merge(request()->except('filter'), [])) }}">
            Semua Barang
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request('filter') === 'low_stock' ? 'active bg-danger text-white' : 'bg-white text-danger' }} border" href="{{ route('products.index', array_merge(request()->except('filter'), ['filter' => 'low_stock'])) }}">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> Stok Menipis ({{ $lowStockCount }})
        </a>
    </li>
</ul>

<!-- Tabel Barang -->
<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode / SKU</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th class="text-end">Harga Modal</th>
                    <th class="text-end">Harga Jual</th>
                    <th class="text-center">Stok</th>
                    <th>Satuan</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $prod)
                    <tr>
                        <td>
                            <span class="badge bg-light text-dark border font-monospace">{{ $prod->sku }}</span>
                            @if($prod->barcode)
                                <div class="text-muted" style="font-size: 0.72rem;"><i class="bi bi-upc-scan"></i> {{ $prod->barcode }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $prod->name }}</div>
                            @if($prod->brand)
                                <small class="text-muted">{{ $prod->brand }}</small>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary-subtle text-secondary">{{ $prod->category ? $prod->category->name : '-' }}</span></td>
                        <td class="text-end text-muted small">Rp {{ number_format($prod->buy_price, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold text-dark">Rp {{ number_format($prod->sell_price, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($prod->stock <= $prod->min_stock)
                                <span class="badge bg-danger" title="Batas minimum: {{ $prod->min_stock }} {{ $prod->unit }}">
                                    <i class="bi bi-exclamation-circle me-1"></i> {{ $prod->stock }}
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success fw-bold">{{ $prod->stock }}</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $prod->unit }}</td>
                        <td class="text-end">
                            <a href="{{ route('stocks.create') }}?product_id={{ $prod->id }}" class="btn btn-sm btn-outline-success me-1" title="Restock">
                                <i class="bi bi-arrow-up-right"></i>
                            </a>
                            <a href="{{ route('products.edit', $prod->id) }}" class="btn btn-sm btn-outline-secondary me-1" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('products.destroy', $prod->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-box-seam fs-2 d-block mb-2"></i>
                            Tidak ada barang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>
@endsection
