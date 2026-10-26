<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $order->invoice_number }} — {{ $storeName }}</title>
    <style>
        @page {
            size: 58mm auto; /* Default thermal printer 58mm, auto height */
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px 5px;
            width: 58mm;
            box-sizing: border-box;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .double-divider {
            border-top: 1px double #000;
            margin: 6px 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .receipt-header {
            margin-bottom: 8px;
        }
        .receipt-header h2 {
            font-size: 14px;
            margin: 0 0 2px 0;
            text-transform: uppercase;
        }
        .receipt-header p {
            margin: 0;
            font-size: 9px;
            line-height: 1.2;
        }
        .no-print {
            display: flex;
            gap: 5px;
            justify-content: center;
            margin-bottom: 15px;
            padding: 8px;
            background: #f0f0f0;
            border-radius: 4px;
        }
        .btn {
            font-family: sans-serif;
            font-size: 11px;
            padding: 5px 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            cursor: pointer;
            background: #fff;
        }
        .btn-primary {
            background: #0d6efd;
            color: #fff;
            border-color: #0d6efd;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn btn-primary" onclick="window.print()">Cetak Struk</button>
    <button class="btn" onclick="window.close()">Tutup</button>
</div>

<div class="receipt-header text-center">
    <h2>{{ $storeName }}</h2>
    <p>{{ $storeAddress }}</p>
    <p>Telp: {{ $storePhone }}</p>
</div>

<div class="divider"></div>

<div class="row">
    <span>No: {{ $order->invoice_number }}</span>
</div>
<div class="row">
    <span>Tgl: {{ $order->created_at->format('d/m/Y H:i') }}</span>
</div>
<div class="row">
    <span>Kasir: {{ $order->user ? $order->user->name : '-' }}</span>
</div>
@if($order->customer)
<div class="row">
    <span>Plg: {{ $order->customer->name }}</span>
</div>
@endif

<div class="divider"></div>

<!-- Item Belanja -->
@foreach($order->items as $item)
    <div class="fw-bold">{{ $item->product_name }}</div>
    <div class="row">
        <span>{{ $item->quantity }} x {{ number_format($item->sell_price, 0, ',', '.') }}</span>
        <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
    </div>
@endforeach

<div class="divider"></div>

<div class="row">
    <span>Subtotal:</span>
    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
</div>

@if($order->discount_amount > 0)
<div class="row">
    <span>Diskon ({{ $order->discount_percent }}%):</span>
    <span>-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
</div>
@endif

<div class="double-divider"></div>

<div class="row fw-bold" style="font-size: 12px;">
    <span>TOTAL:</span>
    <span>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
</div>

<div class="row">
    <span>Bayar ({{ strtoupper($order->payment_method) }}):</span>
    <span>Rp {{ number_format($order->cash_paid, 0, ',', '.') }}</span>
</div>

@if($order->payment_method === 'cash')
<div class="row">
    <span>Kembali:</span>
    <span>Rp {{ number_format($order->cash_change, 0, ',', '.') }}</span>
</div>
@endif

<div class="divider"></div>

<div class="text-center" style="margin-top: 8px;">
    <p style="font-size: 9px; margin: 0;">{{ $receiptFooter }}</p>
</div>

<script>
    // Auto trigger print saat halaman dibuka
    window.addEventListener('load', () => {
        // window.print();
    });
</script>
</body>
</html>
