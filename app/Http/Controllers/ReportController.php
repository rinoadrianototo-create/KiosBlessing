<?php

namespace App\Http\Controllers;

use App\Models\Finance;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\StockMutation;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        $reportType = $request->get('type', 'sales'); // 'sales', 'profit_loss', 'stock'

        $data = $this->getReportData($reportType, $startDate, $endDate);

        return view('reports.index', array_merge($data, [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportType' => $reportType,
        ]));
    }

    public function export(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        $reportType = $request->get('type', 'sales');
        $format = $request->get('format', 'excel'); // 'excel' or 'pdf'

        $data = $this->getReportData($reportType, $startDate, $endDate);
        $storeName = Setting::get('store_name', 'Kios Blessing');

        if ($format === 'pdf') {
            return view('reports.pdf', array_merge($data, [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'reportType' => $reportType,
                'storeName' => $storeName,
            ]));
        }

        // Export Excel (CSV dengan BOM UTF-8 agar karakter rapi di Excel)
        $filename = "Laporan_{$reportType}_{$startDate}_sd_{$endDate}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($reportType, $data) {
            $handle = fopen('php://output', 'w');
            // Menulis UTF-8 BOM untuk Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            if ($reportType === 'sales') {
                fputcsv($handle, ['No. Invoice', 'Tanggal & Waktu', 'Kasir', 'Pelanggan', 'Metode Bayar', 'Diskon', 'HPP (Modal)', 'Grand Total', 'Laba Kotor']);
                foreach ($data['orders'] as $order) {
                    fputcsv($handle, [
                        $order->invoice_number,
                        $order->created_at->format('Y-m-d H:i'),
                        $order->user ? $order->user->name : '-',
                        $order->customer ? $order->customer->name : 'Pelanggan Umum',
                        strtoupper($order->payment_method),
                        $order->discount_amount,
                        $order->total_cost,
                        $order->grand_total,
                        $order->grand_total - $order->total_cost,
                    ]);
                }
            } elseif ($reportType === 'profit_loss') {
                fputcsv($handle, ['Komponen Keuangan', 'Nominal (Rp)']);
                fputcsv($handle, ['Total Penjualan POS', $data['posSalesTotal']]);
                fputcsv($handle, ['Total HPP (Harga Pokok Penjualan)', $data['posCogsTotal']]);
                fputcsv($handle, ['LABA KOTOR', $data['grossProfit']]);
                fputcsv($handle, ['Pemasukan Operasional Lain', $data['otherIncome']]);
                fputcsv($handle, ['Beban / Biaya Operasional', $data['operatingExpense']]);
                fputcsv($handle, ['LABA BERSIH', $data['netProfit']]);
                fputcsv($handle, ['', '']);
                fputcsv($handle, ['Penyertaan Modal', $data['capital']]);
                fputcsv($handle, ['Belanja / Pembelian Stok Barang', $data['stockPurchase']]);
            } elseif ($reportType === 'stock') {
                fputcsv($handle, ['Tanggal', 'Kode Barang', 'Nama Barang', 'Tipe Mutasi', 'Jumlah', 'Stok Sebelum', 'Stok Sesudah', 'No. Referensi', 'Keterangan']);
                foreach ($data['mutations'] as $mutation) {
                    fputcsv($handle, [
                        $mutation->created_at->format('Y-m-d H:i'),
                        $mutation->product ? $mutation->product->sku : '-',
                        $mutation->product ? $mutation->product->name : '-',
                        strtoupper($mutation->type),
                        $mutation->quantity,
                        $mutation->stock_before,
                        $mutation->stock_after,
                        $mutation->reference_number,
                        $mutation->notes,
                    ]);
                }
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function getReportData($type, $startDate, $endDate)
    {
        if ($type === 'sales') {
            $orders = Order::with(['user', 'customer'])
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->latest()
                ->get();

            $totalSales = (float) $orders->sum('grand_total');
            $totalCogs = (float) $orders->sum('total_cost');
            $totalGrossProfit = $totalSales - $totalCogs;

            return compact('orders', 'totalSales', 'totalCogs', 'totalGrossProfit');
        }

        if ($type === 'profit_loss') {
            $orders = Order::whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->get();

            $posSalesTotal = (float) $orders->sum('grand_total');
            $posCogsTotal = (float) $orders->sum('total_cost');
            $grossProfit = $posSalesTotal - $posCogsTotal;

            $finances = Finance::whereDate('date', '>=', $startDate)
                ->whereDate('date', '<=', $endDate)
                ->get();

            $otherIncome = (float) $finances->where('type', 'income')->sum('amount');
            $operatingExpense = (float) $finances->where('type', 'expense')->sum('amount');
            $capital = (float) $finances->where('type', 'capital')->sum('amount');
            $stockPurchase = (float) $finances->where('type', 'stock_purchase')->sum('amount');

            $netProfit = $grossProfit + $otherIncome - $operatingExpense;

            return compact('posSalesTotal', 'posCogsTotal', 'grossProfit', 'otherIncome', 'operatingExpense', 'capital', 'stockPurchase', 'netProfit');
        }

        if ($type === 'stock') {
            $mutations = StockMutation::with(['product', 'user'])
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->latest()
                ->get();

            return compact('mutations');
        }

        return [];
    }
}
