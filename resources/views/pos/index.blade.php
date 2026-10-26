@extends('layouts.app')

@section('title', 'Kasir POS')
@section('page-title', 'Kasir Penjualan (Point of Sale)')

@push('styles')
<style>
    .product-grid-card {
        cursor: pointer;
        transition: transform 0.15s, box-shadow 0.15s;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #fff;
    }
    .product-grid-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-color: #0d6efd;
    }
    .cart-wrapper {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        height: calc(100vh - 120px);
        position: sticky;
        top: 85px;
    }
    .cart-items-box {
        flex: 1;
        overflow-y: auto;
    }
    .quick-cash-btn {
        font-size: 0.8rem;
        padding: 0.3rem 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="row g-3">
    <!-- Kolom Kiri: Produk & Pencarian -->
    <div class="col-lg-7 col-xl-8">
        <!-- Search & Filter Bar -->
        <div class="card card-custom p-3 mb-3">
            <div class="row g-2">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-upc-scan"></i></span>
                        <input type="text" id="barcodeSearch" class="form-control" placeholder="Scan barcode atau cari nama produk..." autofocus>
                    </div>
                </div>
                <div class="col-md-5">
                    <select id="categoryFilter" class="form-select bg-light" onchange="filterProducts()">
                        <option value="">Semua Kategori</option>
                        @foreach($products->pluck('category.name')->unique()->filter() as $catName)
                            <option value="{{ $catName }}">{{ $catName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Grid Produk -->
        <div class="row g-2" id="productGrid">
            @forelse($products as $p)
                <div class="col-6 col-md-4 col-xl-3 product-item" data-category="{{ $p->category ? $p->category->name : '' }}" data-name="{{ strtolower($p->name) }}" data-barcode="{{ $p->barcode }}" data-sku="{{ strtolower($p->sku) }}">
                    <div class="product-grid-card p-3 h-100 d-flex flex-direction-column justify-content-between" onclick="addToCart({{ json_encode($p) }})">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="badge bg-light text-secondary border small">{{ $p->unit }}</span>
                                <span class="badge {{ $p->stock <= $p->min_stock ? 'bg-danger' : 'bg-success-subtle text-success' }} small">
                                    Stok: {{ $p->stock }}
                                </span>
                            </div>
                            <h6 class="fw-semibold text-dark mb-1 text-truncate" title="{{ $p->name }}">{{ $p->name }}</h6>
                            <small class="text-muted d-block font-monospace" style="font-size: 0.72rem;">{{ $p->sku }}</small>
                        </div>
                        <div class="mt-2 pt-2 border-top">
                            <span class="fw-bold text-primary fs-6">Rp {{ number_format($p->sell_price, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 text-muted">
                    <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                    Belum ada barang aktif dengan stok tersedia.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Kolom Kanan: Keranjang Belanja & Pembayaran -->
    <div class="col-lg-5 col-xl-4">
        <div class="cart-wrapper p-3">
            <!-- Header Keranjang -->
            <div class="d-flex justify-content-between align-items-center pb-2 border-bottom mb-2">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-cart3 text-primary me-2"></i>Keranjang Belanja</h6>
                <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0" onclick="clearCart()">
                    <i class="bi bi-trash"></i> Kosongkan
                </button>
            </div>

            <!-- Pelanggan Opsional -->
            <div class="mb-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                    <input type="text" id="customerName" class="form-control" placeholder="Nama Pelanggan (Opsional / Umum)">
                </div>
            </div>

            <!-- List Item Keranjang -->
            <div class="cart-items-box mb-2" id="cartItemsList">
                <div class="text-center text-muted py-5 small" id="emptyCartNotice">
                    <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
                    Keranjang masih kosong. Klik barang di sebelah kiri untuk menambahkan.
                </div>
            </div>

            <!-- Kalkulasi Biaya -->
            <div class="pt-2 border-top">
                <div class="d-flex justify-content-between text-muted small mb-1">
                    <span>Subtotal</span>
                    <span id="labelSubtotal">Rp 0</span>
                </div>

                <div class="d-flex justify-content-between align-items-center small mb-2">
                    <span class="text-muted">Diskon (%) <small class="text-danger" id="discountLimitNotice">(Maks {{ $maxDiscountPercent }}%)</small></span>
                    <div class="input-group input-group-sm" style="width: 110px;">
                        <input type="number" id="discountPercent" class="form-control text-end" value="0" min="0" max="100" step="1" oninput="calculateTotal()">
                        <span class="input-group-text">%</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center py-2 px-3 bg-primary-subtle rounded-3 mb-2">
                    <span class="fw-bold text-primary">TOTAL BAYAR</span>
                    <span class="fw-bold fs-5 text-primary" id="labelGrandTotal">Rp 0</span>
                </div>

                <!-- Metode Pembayaran -->
                <div class="mb-2">
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="payment_method" id="payCash" value="cash" checked onchange="togglePaymentInputs()">
                        <label class="btn btn-outline-primary btn-sm" for="payCash"><i class="bi bi-cash"></i> Tunai</label>

                        <input type="radio" class="btn-check" name="payment_method" id="payQRIS" value="qris" onchange="togglePaymentInputs()">
                        <label class="btn btn-outline-primary btn-sm" for="payQRIS"><i class="bi bi-qr-code"></i> QRIS</label>

                        <input type="radio" class="btn-check" name="payment_method" id="payTransfer" value="transfer" onchange="togglePaymentInputs()">
                        <label class="btn btn-outline-primary btn-sm" for="payTransfer"><i class="bi bi-bank"></i> Transfer</label>
                    </div>
                </div>

                <!-- Input Tunai & Kembalian -->
                <div id="cashInputBox">
                    <div class="mb-1">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Rp Bayar</span>
                            <input type="number" id="cashPaid" class="form-control text-end fw-bold" placeholder="0" oninput="calculateChange()">
                        </div>
                    </div>

                    <!-- Tombol Uang Cepat -->
                    <div class="d-flex gap-1 mb-2">
                        <button type="button" class="btn btn-light btn-sm quick-cash-btn flex-fill border" onclick="setExactCash()">Uang Pas</button>
                        <button type="button" class="btn btn-light btn-sm quick-cash-btn flex-fill border" onclick="addCash(20000)">+20rb</button>
                        <button type="button" class="btn btn-light btn-sm quick-cash-btn flex-fill border" onclick="addCash(50000)">+50rb</button>
                        <button type="button" class="btn btn-light btn-sm quick-cash-btn flex-fill border" onclick="addCash(100000)">+100rb</button>
                    </div>

                    <div class="d-flex justify-content-between align-items-center small p-2 bg-light rounded-2 mb-2">
                        <span class="fw-semibold text-secondary">Kembalian:</span>
                        <span class="fw-bold text-success fs-6" id="labelChange">Rp 0</span>
                    </div>
                </div>

                <!-- Tombol Proses Transaksi -->
                <button type="button" class="btn btn-success w-100 py-2 fw-bold shadow-sm" id="btnCheckout" onclick="processCheckout()">
                    <i class="bi bi-check2-circle me-1"></i> Bayar Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Otorisasi Pemilik untuk Diskon di atas batas -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-warning-subtle">
                <h6 class="modal-title fw-bold text-dark"><i class="bi bi-shield-lock me-1"></i> Otorisasi Pemilik</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-2" id="approvalNoticeText">Diskon melebihi batas standar kasir. Masukkan password pemilik kios untuk melanjutkan:</p>
                <div class="mb-3">
                    <input type="password" id="ownerPasswordInput" class="form-control" placeholder="Password Pemilik Kios">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning btn-sm fw-bold" onclick="submitWithApproval()">Setujui & Bayar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Sukses Checkout -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-3">
            <div class="my-3 text-success">
                <i class="bi bi-check-circle-fill display-4"></i>
            </div>
            <h5 class="fw-bold mb-1">Transaksi Berhasil!</h5>
            <p class="text-muted small mb-3" id="successInvoiceText"></p>
            <div class="d-grid gap-2">
                <a href="#" id="btnPrintReceipt" target="_blank" class="btn btn-primary fw-semibold">
                    <i class="bi bi-printer me-1"></i> Cetak Struk
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" onclick="resetPOS()">
                    Transaksi Baru
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let cart = [];
    const maxDiscountLimit = {{ $maxDiscountPercent }};
    const isOwner = {{ auth()->user()->isPemilik() ? 'true' : 'false' }};

    function addToCart(product) {
        const existing = cart.find(item => item.id === product.id);
        if (existing) {
            if (existing.quantity + 1 > product.stock) {
                alert(`Stok untuk ${product.name} tidak mencukupi (Tersedia: ${product.stock} ${product.unit}).`);
                return;
            }
            existing.quantity += 1;
        } else {
            if (product.stock < 1) {
                alert(`Stok untuk ${product.name} habis.`);
                return;
            }
            cart.push({
                id: product.id,
                name: product.name,
                price: parseFloat(product.sell_price),
                stock: product.stock,
                unit: product.unit,
                quantity: 1
            });
        }
        renderCart();
    }

    function updateQty(id, delta) {
        const item = cart.find(i => i.id === id);
        if (!item) return;

        const newQty = item.quantity + delta;
        if (newQty <= 0) {
            cart = cart.filter(i => i.id !== id);
        } else if (newQty > item.stock) {
            alert(`Stok untuk ${item.name} hanya tersedia ${item.stock} ${item.unit}.`);
            return;
        } else {
            item.quantity = newQty;
        }
        renderCart();
    }

    function removeItem(id) {
        cart = cart.filter(i => i.id !== id);
        renderCart();
    }

    function clearCart() {
        if (cart.length > 0 && confirm('Kosongkan keranjang belanja?')) {
            cart = [];
            renderCart();
        }
    }

    function renderCart() {
        const box = document.getElementById('cartItemsList');
        const emptyNotice = document.getElementById('emptyCartNotice');

        if (cart.length === 0) {
            box.innerHTML = `
                <div class="text-center text-muted py-5 small" id="emptyCartNotice">
                    <i class="bi bi-cart-x fs-1 d-block mb-2"></i>
                    Keranjang masih kosong. Klik barang di sebelah kiri untuk menambahkan.
                </div>
            `;
            calculateTotal();
            return;
        }

        let html = '';
        cart.forEach(item => {
            const sub = item.price * item.quantity;
            html += `
                <div class="p-2 border rounded-2 mb-2 bg-light">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="fw-semibold text-dark text-truncate" style="max-width: 170px;">${item.name}</div>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeItem(${item.id})">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="small text-muted">Rp ${item.price.toLocaleString('id-ID')}</span>
                        <div class="input-group input-group-sm" style="width: 90px;">
                            <button class="btn btn-white border px-1" type="button" onclick="updateQty(${item.id}, -1)">-</button>
                            <input type="text" class="form-control text-center bg-white px-0" value="${item.quantity}" readonly>
                            <button class="btn btn-white border px-1" type="button" onclick="updateQty(${item.id}, 1)">+</button>
                        </div>
                        <span class="fw-bold small text-dark">Rp ${sub.toLocaleString('id-ID')}</span>
                    </div>
                </div>
            `;
        });

        box.innerHTML = html;
        calculateTotal();
    }

    function calculateTotal() {
        let subtotal = 0;
        cart.forEach(i => subtotal += (i.price * i.quantity));

        const discountPercent = parseFloat(document.getElementById('discountPercent').value) || 0;
        const discountAmount = (subtotal * discountPercent) / 100;
        const grandTotal = Math.max(0, subtotal - discountAmount);

        document.getElementById('labelSubtotal').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        document.getElementById('labelGrandTotal').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');

        calculateChange();
    }

    function togglePaymentInputs() {
        const isCash = document.getElementById('payCash').checked;
        document.getElementById('cashInputBox').style.display = isCash ? 'block' : 'none';
        calculateChange();
    }

    function calculateChange() {
        let subtotal = 0;
        cart.forEach(i => subtotal += (i.price * i.quantity));
        const discountPercent = parseFloat(document.getElementById('discountPercent').value) || 0;
        const grandTotal = Math.max(0, subtotal - (subtotal * discountPercent) / 100);

        const cashPaid = parseFloat(document.getElementById('cashPaid').value) || 0;
        const change = Math.max(0, cashPaid - grandTotal);

        document.getElementById('labelChange').innerText = 'Rp ' + change.toLocaleString('id-ID');
    }

    function setExactCash() {
        let subtotal = 0;
        cart.forEach(i => subtotal += (i.price * i.quantity));
        const discountPercent = parseFloat(document.getElementById('discountPercent').value) || 0;
        const grandTotal = Math.max(0, subtotal - (subtotal * discountPercent) / 100);
        document.getElementById('cashPaid').value = grandTotal;
        calculateChange();
    }

    function addCash(amount) {
        const current = parseFloat(document.getElementById('cashPaid').value) || 0;
        document.getElementById('cashPaid').value = current + amount;
        calculateChange();
    }

    function filterProducts() {
        const cat = document.getElementById('categoryFilter').value;
        const search = document.getElementById('barcodeSearch').value.toLowerCase().trim();
        const items = document.querySelectorAll('.product-item');

        items.forEach(el => {
            const matchesCat = !cat || el.dataset.category === cat;
            const matchesSearch = !search || el.dataset.name.includes(search) || el.dataset.sku.includes(search) || (el.dataset.barcode && el.dataset.barcode.includes(search));
            el.style.display = (matchesCat && matchesSearch) ? 'block' : 'none';
        });
    }

    document.getElementById('barcodeSearch').addEventListener('input', filterProducts);

    function processCheckout(ownerPassword = null) {
        if (cart.length === 0) {
            alert('Keranjang belanja masih kosong!');
            return;
        }

        const discountPercent = parseFloat(document.getElementById('discountPercent').value) || 0;

        // Otorisasi diskon
        if (discountPercent > maxDiscountLimit && !isOwner && !ownerPassword) {
            document.getElementById('approvalNoticeText').innerText = `Diskon ${discountPercent}% melebihi batas kasir (${maxDiscountLimit}%). Masukkan password pemilik untuk menyetujui:`;
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
            modal.show();
            return;
        }

        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        const cashPaid = parseFloat(document.getElementById('cashPaid').value) || 0;

        let subtotal = 0;
        cart.forEach(i => subtotal += (i.price * i.quantity));
        const grandTotal = Math.max(0, subtotal - (subtotal * discountPercent) / 100);

        if (paymentMethod === 'cash' && cashPaid < grandTotal) {
            alert(`Uang tunai kurang! Total belanja: Rp ${grandTotal.toLocaleString('id-ID')}, uang dibayarkan: Rp ${cashPaid.toLocaleString('id-ID')}`);
            return;
        }

        const payload = {
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            items: cart.map(i => ({ id: i.id, quantity: i.quantity })),
            customer_name: document.getElementById('customerName').value,
            discount_percent: discountPercent,
            payment_method: paymentMethod,
            cash_paid: cashPaid,
            owner_approval_password: ownerPassword,
        };

        const btn = document.getElementById('btnCheckout');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

        fetch("{{ route('pos.checkout') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Bayar Sekarang';

            if (data.status === 'success') {
                // Tutup approval modal jika buka
                const approvalEl = document.getElementById('approvalModal');
                const approvalModal = bootstrap.Modal.getInstance(approvalEl);
                if (approvalModal) approvalModal.hide();

                // Buka modal sukses & tombol cetak
                document.getElementById('successInvoiceText').innerText = `No. Transaksi: ${data.invoice_number}`;
                document.getElementById('btnPrintReceipt').href = data.print_url;
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            } else {
                alert(data.message || 'Terjadi kesalahan saat memproses transaksi.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Bayar Sekarang';
            alert('Gagal menghubungi server.');
        });
    }

    function submitWithApproval() {
        const pwd = document.getElementById('ownerPasswordInput').value;
        if (!pwd) {
            alert('Silakan masukkan password pemilik!');
            return;
        }
        processCheckout(pwd);
    }

    function resetPOS() {
        cart = [];
        document.getElementById('customerName').value = '';
        document.getElementById('discountPercent').value = 0;
        document.getElementById('cashPaid').value = '';
        document.getElementById('ownerPasswordInput').value = '';
        renderCart();
    }
</script>
@endpush
