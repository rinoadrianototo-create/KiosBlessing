@extends('layouts.app')

@section('title', 'Tambah Barang')
@section('page-title', 'Tambah Barang Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-box-seam text-primary me-2"></i>Informasi Data Barang</h6>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Kode Barang / SKU <span class="text-danger">*</span></label>
                        <input type="text" name="sku" class="form-control" value="{{ old('sku', 'PRD-' . rand(100, 999)) }}" required>
                        <small class="text-muted">Kode unik identitas produk di kios</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Barcode (Opsional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-upc-scan"></i></span>
                            <input type="text" name="barcode" class="form-control" value="{{ old('barcode') }}" placeholder="Scan atau ketik nomor barcode...">
                        </div>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label small fw-semibold text-secondary">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Beras Premium Ramos 5 Kg" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-secondary">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Pilih Kategori...</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Merek / Brand</label>
                        <input type="text" name="brand" class="form-control" value="{{ old('brand') }}" placeholder="Contoh: Sania, Indofood, Unilever...">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary">Satuan <span class="text-danger">*</span></label>
                        <input type="text" name="unit" class="form-control" value="{{ old('unit', 'pcs') }}" placeholder="pcs, botol, kg, dus..." required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary">Batas Stok Minimum <span class="text-danger">*</span></label>
                        <input type="number" name="min_stock" class="form-control" value="{{ old('min_stock', 5) }}" min="0" required>
                        <small class="text-muted">Peringatan saat stok menipis</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Harga Modal (Beli) Rp <span class="text-danger">*</span></label>
                        <input type="number" name="buy_price" class="form-control" value="{{ old('buy_price', 0) }}" min="0" step="100" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Harga Jual Rp <span class="text-danger">*</span></label>
                        <input type="number" name="sell_price" class="form-control" value="{{ old('sell_price', 0) }}" min="0" step="100" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Stok Awal <span class="text-danger">*</span></label>
                        <input type="number" name="stock" class="form-control" value="{{ old('stock', 0) }}" min="0" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Foto Barang (Opsional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-secondary">Deskripsi Tambahan</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Keterangan spesifikasi barang...">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('products.index') }}" class="btn btn-light px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Barang</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
