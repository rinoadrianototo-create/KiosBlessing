<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan {{ ucfirst($reportType) }} — {{ $storeName }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h2 { margin: 0; text-transform: uppercase; font-size: 18px; }
        .header p { margin: 3px 0 0; color: #666; font-size: 11px; }
        .report-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-title h3 { margin: 0; font-size: 15px; }
        .report-title span { font-size: 11px; color: #666; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-size: 11px;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .no-print {
            text-align: right;
            margin-bottom: 15px;
        }
        .btn-print {
            padding: 6px 14px;
            background: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">Cetak / Simpan ke PDF</button>
</div>

<div class="header">
    <h2>{{ $storeName }}</h2>
    <p>Laporan Resmi Operasional & Finansial Kios</p>
</div>

<div class="report-title">
    <h3>
        @if($reportType === 'sales') LAPORAN PENJUALAN KASIR
        @elseif($reportType === 'profit_loss') LAPORAN LABA RUGI OPERASIONAL
        @else LAPORAN MUTASI STOK BARANG
        @endif
    </h3>
    <span>Periode: {{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}</span>
</div>

@if($reportType === 'sales')
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Invoice</th>
                <th>Waktu</th>
                <th>Kasir</th>
                <th>Metode</th>
                <th class="text-end">HPP (Modal)</th>
                <th class="text-end">Grand Total</th>
                <th class="text-end">Laba Kotor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $idx => $o)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $o->invoice_number }}</td>
                    <td>{{ $o->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $o->user ? $o->user->name : '-' }}</td>
                    <td>{{ strtoupper($o->payment_method) }}</td>
                    <td class="text-end">Rp {{ number_format($o->total_cost, 0, ',', '.') }}</td>
                    <td class="text-end fw-bold">Rp {{ number_format($o->grand_total, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($o->grand_total - $o->total_cost, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold" style="background-color: #f9f9f9;">
                <td colspan="5" class="text-end">TOTAL KESELURUHAN:</td>
                <td class="text-end">Rp {{ number_format($totalCogs, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($totalGrossProfit, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

@elseif($reportType === 'profit_loss')
    <table>
        <tbody>
            <tr style="background: #eef2f7;">
                <th colspan="2">A. PENDAPATAN OPERASIONAL</th>
            </tr>
            <tr>
                <td>Total Penjualan Kasir (POS)</td>
                <td class="text-end fw-bold">Rp {{ number_format($posSalesTotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Harga Pokok Penjualan (HPP Modal Barang Terjual)</td>
                <td class="text-end">- Rp {{ number_format($posCogsTotal, 0, ',', '.') }}</td>
            </tr>
            <tr style="background: #e8f5e9;">
                <th class="fw-bold">LABA KOTOR (GROSS PROFIT)</th>
                <td class="text-end fw-bold">Rp {{ number_format($grossProfit, 0, ',', '.') }}</td>
            </tr>

            <tr style="background: #eef2f7;">
                <th colspan="2">B. BIAYA OPERASIONAL</th>
            </tr>
            <tr>
                <td>Pengeluaran Operasional (Beban Usaha)</td>
                <td class="text-end">- Rp {{ number_format($operatingExpense, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Pemasukan Lain-lain</td>
                <td class="text-end">+ Rp {{ number_format($otherIncome, 0, ',', '.') }}</td>
            </tr>
            <tr style="background: #e3f2fd; font-size: 13px;">
                <th class="fw-bold">LABA BERSIH (NET PROFIT)</th>
                <td class="text-end fw-bold">Rp {{ number_format($netProfit, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

@elseif($reportType === 'stock')
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Waktu</th>
                <th>Kode</th>
                <th>Nama Produk</th>
                <th>Tipe</th>
                <th class="text-center">Qty</th>
                <th class="text-center">Stok Awal</th>
                <th class="text-center">Stok Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mutations as $idx => $m)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $m->product ? $m->product->sku : '-' }}</td>
                    <td>{{ $m->product ? $m->product->name : '-' }}</td>
                    <td>{{ strtoupper($m->type) }}</td>
                    <td class="text-center fw-bold">{{ $m->quantity }}</td>
                    <td class="text-center">{{ $m->stock_before }}</td>
                    <td class="text-center fw-bold">{{ $m->stock_after }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<div style="margin-top: 40px; display: flex; justify-content: space-between;">
    <div style="text-align: center; width: 200px;">
        <p>Mengetahui,</p>
        <br><br><br>
        <p class="fw-bold">( Pemilik Kios )</p>
    </div>
    <div style="text-align: center; width: 200px;">
        <p>Dicetak Pada: {{ date('d/m/Y H:i') }}</p>
        <br><br><br>
        <p class="fw-bold">( {{ auth()->user()->name }} )</p>
    </div>
</div>

</body>
</html>
