@extends('layouts.app')

@section('title', 'Catat Mutasi Stok')
@section('page-title', 'Pencatatan Stok Masuk / Keluar')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-box-arrow-in-down text-primary me-2"></i>Formulir Mutasi Stok</h6>
                <a href="{{ route('stocks.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="{{ route('stocks.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold text-secondary">Pilih Barang <span class="text-danger">*</span></label>
                        <select name="product_id" id="product_id" class="form-select select2" required onchange="updateStockInfo()">
                            <option value="">-- Pilih Barang --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" data-stock="{{ $p->stock }}" data-unit="{{ $p->unit }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} (Sisa: {{ $p->stock }} {{ $p->unit }}) - [{{ $p->sku }}]
                                </option>
                            @endforeach
                        </select>
                        <div id="stockNotice" class="form-text text-primary mt-1 fw-semibold"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Jenis Mutasi <span class="text-danger">*</span></label>
                        <select name="type" id="mutationType" class="form-select" required onchange="togglePurchaseExpenseOption()">
                            <option value="in" {{ old('type') === 'in' ? 'selected' : '' }}>Barang Masuk (Restock / Pembelian)</option>
                            <option value="out" {{ old('type') === 'out' ? 'selected' : '' }}>Barang Keluar (Penyesuaian / Rusak)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Jumlah (Quantity) <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" value="{{ old('quantity', 1) }}" min="1" required>
                    </div>

                    <div class="col-md-12" id="purchaseExpenseDiv">
                        <div class="form-check p-3 bg-light rounded-3 border">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="record_purchase_expense" value="1" id="recordPurchaseExpense" checked>
                            <label class="form-check-label small fw-semibold text-dark" for="recordPurchaseExpense">
                                Catat otomatis ke pengeluaran keuangan sebagai <u>Pembelian Stok Barang</u>
                            </label>
                            <div class="text-muted small ps-4">Nilai beli akan dikalikan harga modal barang secara otomatis.</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">No. Referensi / No. Surat / Invoice (Opsional)</label>
                        <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}" placeholder="Contoh: INV-SUPPLIER-123">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Catatan / Alasan</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Contoh: Restock mingguan, barang rusak kadaluwarsa...">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('stocks.index') }}" class="btn btn-light px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Mutasi Stok</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateStockInfo() {
        const select = document.getElementById('product_id');
        const selected = select.options[select.selectedIndex];
        const notice = document.getElementById('stockNotice');
        if (selected && selected.dataset.stock) {
            notice.innerText = `Stok saat ini: ${selected.dataset.stock} ${selected.dataset.unit}`;
        } else {
            notice.innerText = '';
        }
    }

    function togglePurchaseExpenseOption() {
        const type = document.getElementById('mutationType').value;
        const div = document.getElementById('purchaseExpenseDiv');
        if (type === 'out') {
            div.style.display = 'none';
            document.getElementById('recordPurchaseExpense').checked = false;
        } else {
            div.style.display = 'block';
            document.getElementById('recordPurchaseExpense').checked = true;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateStockInfo();
        togglePurchaseExpenseOption();
    });
</script>
@endpush
