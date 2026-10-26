<?php

namespace App\Http\Controllers;

use App\Models\Finance;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();

        // Metrik Hari Ini
        $todayOrders = Order::whereDate('created_at', $today)->get();
        $todaySales = (float) $todayOrders->sum('grand_total');
        $todayTransactionsCount = $todayOrders->count();
        $todayCogs = (float) $todayOrders->sum('total_cost');
        $todayGrossProfit = $todaySales - $todayCogs;

        // Metrik Bulan Ini
        $monthOrders = Order::whereDate('created_at', '>=', $startOfMonth)->get();
        $monthSales = (float) $monthOrders->sum('grand_total');
        $monthCogs = (float) $monthOrders->sum('total_cost');
        $monthGrossProfit = $monthSales - $monthCogs;

        // Beban Operasional Bulan Ini
        $monthExpenses = (float) Finance::where('type', 'expense')
            ->whereDate('date', '>=', $startOfMonth)
            ->sum('amount');

        $monthNetProfit = $monthGrossProfit - $monthExpenses;

        // Stok Menipis
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->with('category')
            ->take(5)
            ->get();
        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        // Barang Terlaris (Top 5)
        $topProducts = OrderItem::select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // Transaksi Terbaru
        $recentOrders = Order::with('user', 'customer')->latest()->take(5)->get();

        // Data Grafik Penjualan 7 Hari Terakhir
        $chartLabels = [];
        $chartSalesData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->toDateString();
            $chartLabels[] = $date->translatedFormat('d M');
            $chartSalesData[] = (float) Order::whereDate('created_at', $dateStr)->sum('grand_total');
        }

        return view('dashboard.index', compact(
            'todaySales',
            'todayTransactionsCount',
            'todayGrossProfit',
            'monthSales',
            'monthGrossProfit',
            'monthExpenses',
            'monthNetProfit',
            'lowStockProducts',
            'lowStockCount',
            'topProducts',
            'recentOrders',
            'chartLabels',
            'chartSalesData'
        ));
    }
}
