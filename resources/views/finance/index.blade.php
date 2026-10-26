@extends('layouts.app')

@section('title', 'Manajemen Keuangan')
@section('page-title', 'Pencatatan Keuangan & Arus Kas')

@section('content')
<!-- Filter Periode -->
<div class="card card-custom p-3 mb-4">
    <form action="{{ route('finance.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-light">Dari</span>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-light">Sampai</span>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="type" class="form-select bg-light" onchange="this.form.submit()">
                <option value="">Semua Kategori Kas</option>
                <option value="capital" {{ $typeFilter === 'capital' ? 'selected' : '' }}>Modal Awal</option>
                <option value="income" {{ $typeFilter === 'income' ? 'selected' : '' }}>Pemasukan Lain</option>
                <option value="expense" {{ $typeFilter === 'expense' ? 'selected' : '' }}>Beban Operasional</option>
                <option value="stock_purchase" {{ $typeFilter === 'stock_purchase' ? 'selected' : '' }}>Pembelian Stok Barang</option>
            </select>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button>
        </div>
    </form>
</div>

<!-- Kartu Ringkasan Laba Rugi & Arus Kas -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-custom p-3 border-start border-4 border-primary">
            <span class="text-muted small">Penjualan Kasir (POS)</span>
            <h5 class="fw-bold text-dark mt-1 mb-0">Rp {{ number_format($posSalesTotal, 0, ',', '.') }}</h5>
            <small class="text-muted">HPP: Rp {{ number_format($posCogsTotal, 0, ',', '.') }}</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-custom p-3 border-start border-4 border-success">
            <span class="text-muted small">Laba Kotor</span>
            <h5 class="fw-bold text-success mt-1 mb-0">Rp {{ number_format($grossProfit, 0, ',', '.') }}</h5>
            <small class="text-muted">Penjualan - HPP Terjual</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-custom p-3 border-start border-4 border-danger">
            <span class="text-muted small">Beban Operasional</span>
            <h5 class="fw-bold text-danger mt-1 mb-0">Rp {{ number_format($totalOperatingExpense, 0, ',', '.') }}</h5>
            <small class="text-muted">Listrik, sewa, gaji, utilitas</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-custom p-3 border-start border-4 border-info">
            <span class="text-muted small">Laba Bersih</span>
            <h5 class="fw-bold text-info mt-1 mb-0">Rp {{ number_format($netProfit, 0, ',', '.') }}</h5>
            <small class="text-muted">Laba kotor - Operasional</small>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Form Tambah Catatan Keuangan -->
    <div class="col-lg-4">
        <div class="card card-custom p-4">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-cash-coin text-primary me-2"></i>Catat Arus Kas</h6>
            <form action="{{ route('finance.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Jenis Transaksi</label>
                    <select name="type" class="form-select" required>
                        <option value="expense">Pengeluaran Operasional (Beban)</option>
                        <option value="stock_purchase">Pembelian Stok Barang (Belanja Stok)</option>
                        <option value="income">Pemasukan Lain-lain</option>
                        <option value="capital">Modal Awal / Tambahan Modal</option>
                    </select>
                    <small class="text-muted" style="font-size: 0.72rem;">*Pembelian stok tidak langsung memotong laba kotor, melainkan dihitung via HPP saat barang terjual.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Kategori Transaksi</label>
                    <input type="text" name="category" class="form-control" placeholder="Contoh: Listrik, Plastik, Gaji, Sewa..." required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Nominal (Rp)</label>
                    <input type="number" name="amount" class="form-control" min="1" step="100" placeholder="0" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Keterangan / Catatan</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-save me-1"></i> Simpan Catatan
                </button>
            </form>
        </div>
    </div>

    <!-- Tabel Catatan Keuangan -->
    <div class="col-lg-8">
        <div class="card card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-journal-text text-primary me-2"></i>Riwayat Kas & Keuangan</h6>
                <span class="badge bg-secondary-subtle text-secondary">{{ $finances->total() }} Catatan</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>Jenis</th>
                            <th class="text-end">Nominal</th>
                            <th>Keterangan</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($finances as $f)
                            <tr>
                                <td class="small">{{ $f->date->format('d/m/Y') }}</td>
                                <td class="fw-semibold text-dark">{{ $f->category }}</td>
                                <td>
                                    @if($f->type === 'capital')
                                        <span class="badge bg-secondary">Modal</span>
                                    @elseif($f->type === 'income')
                                        <span class="badge bg-success-subtle text-success">Pemasukan</span>
                                    @elseif($f->type === 'expense')
                                        <span class="badge bg-danger-subtle text-danger">Operasional</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">Belanja Stok</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold {{ in_array($f->type, ['income', 'capital']) ? 'text-success' : 'text-danger' }}">
                                    {{ in_array($f->type, ['income', 'capital']) ? '+' : '-' }}Rp {{ number_format($f->amount, 0, ',', '.') }}
                                </td>
                                <td class="small text-muted">{{ $f->description ?: '-' }}</td>
                                <td class="text-end">
                                    <form action="{{ route('finance.destroy', $f->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus catatan keuangan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    Belum ada catatan keuangan manual pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $finances->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
