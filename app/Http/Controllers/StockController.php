<?php

namespace App\Http\Controllers;

use App\Models\Finance;
use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMutation::with(['product', 'user'])->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $mutations = $query->paginate(20)->withQueryString();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('stocks.index', compact('mutations', 'products'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('stocks.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'reference_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:255',
            'record_purchase_expense' => 'nullable|boolean', // jika barang masuk ingin langsung dicatat sebagai pembelian stok di keuangan
        ]);

        return DB::transaction(function () use ($request) {
            $product = Product::lockForUpdate()->findOrFail($request->product_id);
            $stockBefore = $product->stock;
            $quantity = (int) $request->quantity;

            if ($request->type === 'out') {
                if ($quantity > $stockBefore) {
                    return back()->withInput()->with('error', "Stok tidak mencukupi! Stok saat ini hanya {$stockBefore} {$product->unit}, tidak boleh membuat stok negatif.");
                }
                $stockAfter = $stockBefore - $quantity;
            } else {
                $stockAfter = $stockBefore + $quantity;
            }

            // Update stok barang
            $product->update(['stock' => $stockAfter]);

            // Catat mutasi
            $mutation = StockMutation::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => $request->type,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_number' => $request->reference_number ?: ($request->type === 'in' ? 'RESTOCK-' . date('YmdHis') : 'ADJ-' . date('YmdHis')),
                'notes' => $request->notes ?: ($request->type === 'in' ? 'Barang masuk / Pembelian stok' : 'Penyesuaian stok keluar'),
            ]);

            // Jika opsi catat ke keuangan dicentang untuk barang masuk
            if ($request->type === 'in' && $request->boolean('record_purchase_expense')) {
                $totalPurchaseCost = $quantity * $product->buy_price;
                Finance::create([
                    'type' => 'stock_purchase',
                    'category' => 'Pembelian Stok Barang',
                    'amount' => $totalPurchaseCost,
                    'date' => now()->toDateString(),
                    'description' => "Pembelian stok {$product->name} sebanyak {$quantity} {$product->unit}",
                    'user_id' => auth()->id(),
                    'reference_number' => $mutation->reference_number,
                ]);
            }

            $typeLabel = $request->type === 'in' ? 'masuk' : 'keluar';
            return redirect()->route('stocks.index')->with('success', "Mutasi stok {$typeLabel} sebanyak {$quantity} {$product->unit} berhasil disimpan.");
        });
    }
}
