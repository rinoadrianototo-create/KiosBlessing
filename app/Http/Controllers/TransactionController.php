<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'customer', 'items'])->latest();

        // Kasir hanya melihat transaksi mereka jika bukan pemilik/admin
        if (auth()->user()->isKasir()) {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('invoice_number', 'like', "%{$search}%");
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('transactions.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if (auth()->user()->isKasir() && $order->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki hak untuk melihat transaksi ini.');
        }

        $order->load(['items', 'user', 'customer', 'approver']);

        return view('transactions.show', compact('order'));
    }
}
