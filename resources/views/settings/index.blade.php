@extends('layouts.app')

@section('title', 'Pengaturan Kios')
@section('page-title', 'Pengaturan Toko & Pencadangan')

@section('content')
<div class="row">
    <!-- Form Pengaturan -->
    <div class="col-lg-7 mb-4">
        <div class="card card-custom p-4">
            <h6 class="fw-bold text-dark mb-4 pb-2 border-bottom">
                <i class="bi bi-shop text-primary me-2"></i>Informasi & Aturan Kios
            </h6>

            <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold text-secondary">Nama Kios</label>
                        <input type="text" name="store_name" class="form-control" value="{{ $settings['store_name'] }}" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-semibold text-secondary">Alamat Kios</label>
                        <textarea name="store_address" class="form-control" rows="2" required>{{ $settings['store_address'] }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="store_phone" class="form-control" value="{{ $settings['store_phone'] }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Batas Diskon Maksimal Kasir (%)</label>
                        <div class="input-group">
                            <input type="number" name="max_cashier_discount_percent" class="form-control" value="{{ $settings['max_cashier_discount_percent'] }}" min="0" max="100" step="1" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Diskon di atas persentase ini memerlukan verifikasi pemilik.</small>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-semibold text-secondary">Pesan Kaki Struk (Receipt Footer)</label>
                        <input type="text" name="receipt_footer" class="form-control" value="{{ $settings['receipt_footer'] }}" placeholder="Terima kasih telah berbelanja...">
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Pencadangan Database -->
    <div class="col-lg-5">
        <div class="card card-custom p-4 mb-4">
            <h6 class="fw-bold text-dark mb-3">
                <i class="bi bi-database-down text-success me-2"></i>Pencadangan Database
            </h6>
            <p class="small text-muted">
                Amankan seluruh data transaksi, riwayat stok, dan keuangan kios secara berkala. File cadangan dapat diunduh langsung ke komputer Anda.
            </p>

            <div class="p-3 bg-light rounded-3 border mb-3">
                <div class="small fw-semibold text-dark"><i class="bi bi-shield-check text-success me-1"></i> Status Database:</div>
                <div class="small text-muted">Tipe: SQLite Local Database</div>
                <div class="small text-muted">Lokasi: <code>database/database.sqlite</code></div>
            </div>

            <a href="{{ route('settings.backup') }}" class="btn btn-outline-success w-100 py-2 fw-semibold">
                <i class="bi bi-cloud-arrow-down-fill me-1"></i> Unduh Cadangan Database (.sqlite)
            </a>
        </div>

        <div class="card card-custom p-4">
            <h6 class="fw-bold text-dark mb-2">
                <i class="bi bi-info-circle text-info me-2"></i>Tentang Aplikasi
            </h6>
            <div class="small text-muted">
                <div><strong>Aplikasi:</strong> Kios Blessing POS & Management</div>
                <div><strong>Versi:</strong> 1.0 (Sesuai PRD)</div>
                <div><strong>Framework:</strong> Laravel & Bootstrap 5</div>
            </div>
        </div>
    </div>
</div>
@endsection
