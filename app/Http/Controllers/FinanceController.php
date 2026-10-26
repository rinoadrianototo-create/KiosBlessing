<?php

namespace App\Http\Controllers;

use App\Models\Finance;
use App\Models\Order;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        $typeFilter = $request->get('type');

        // Transaksi Penjualan POS pada periode
        $ordersQuery = Order::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        $posSalesTotal = (float) $ordersQuery->sum('grand_total');
        $posCogsTotal = (float) $ordersQuery->sum('total_cost'); // HPP barang terjual
        $grossProfit = $posSalesTotal - $posCogsTotal;

        // Catatan Keuangan Manual
        $financesQuery = Finance::whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->with('user')
            ->latest('date');

        if ($typeFilter) {
            $financesQuery->where('type', $typeFilter);
        }

        $finances = $financesQuery->paginate(20)->withQueryString();

        // Agregasi Keuangan untuk periode ini
        $allFinances = Finance::whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->get();

        $totalCapital = (float) $allFinances->where('type', 'capital')->sum('amount');
        $totalOtherIncome = (float) $allFinances->where('type', 'income')->sum('amount');
        $totalOperatingExpense = (float) $allFinances->where('type', 'expense')->sum('amount');
        $totalStockPurchase = (float) $allFinances->where('type', 'stock_purchase')->sum('amount');

        // Laba Bersih = Laba Kotor + Pemasukan Lain - Beban Operasional
        $netProfit = $grossProfit + $totalOtherIncome - $totalOperatingExpense;

        // Arus Kas Bersih (Cash Flow) = (Pemasukan Kasir + Pemasukan Lain + Modal) - (Beban Operasional + Pembelian Stok)
        $totalCashIn = $posSalesTotal + $totalOtherIncome + $totalCapital;
        $totalCashOut = $totalOperatingExpense + $totalStockPurchase;
        $netCashFlow = $totalCashIn - $totalCashOut;

        return view('finance.index', compact(
            'finances',
            'startDate',
            'endDate',
            'typeFilter',
            'posSalesTotal',
            'posCogsTotal',
            'grossProfit',
            'totalCapital',
            'totalOtherIncome',
            'totalOperatingExpense',
            'totalStockPurchase',
            'netProfit',
            'netCashFlow'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:capital,income,expense,stock_purchase',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:50',
        ]);

        Finance::create([
            'type' => $request->type,
            'category' => $request->category,
            'amount' => $request->amount,
            'date' => $request->date,
            'description' => $request->description,
            'user_id' => auth()->id(),
            'reference_number' => $request->reference_number ?: strtoupper(substr($request->type, 0, 3)) . '-' . date('YmdHis'),
        ]);

        return redirect()->route('finance.index')->with('success', 'Catatan keuangan berhasil disimpan.');
    }

    public function destroy(Finance $finance)
    {
        $finance->delete();
        return redirect()->route('finance.index')->with('success', 'Catatan keuangan berhasil dihapus.');
    }
}
