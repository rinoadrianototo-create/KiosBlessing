<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMutation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->with('category')
            ->orderBy('name')
            ->get();

        $customers = Customer::orderBy('name')->get();
        $maxDiscountPercent = (float) Setting::get('max_cashier_discount_percent', '10');

        return view('pos.index', compact('products', 'customers', 'maxDiscountPercent'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $products = Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'sku', 'barcode', 'name', 'sell_price', 'stock', 'unit']);

        return response()->json($products);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:30',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'owner_approval_password' => 'nullable|string',
            'payment_method' => 'required|in:cash,transfer,qris',
            'cash_paid' => 'required_if:payment_method,cash|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $maxDiscountPercent = (float) Setting::get('max_cashier_discount_percent', '10');
        $discountPercent = (float) ($request->discount_percent ?? 0);
        $user = auth()->user();
        $approvedBy = null;

        // Otorisasi diskon jika melebihi batas kewenangan kasir
        if ($discountPercent > $maxDiscountPercent) {
            if ($user->isPemilik()) {
                $approvedBy = $user->id;
            } else {
                // Periksa password persetujuan pemilik
                $ownerPassword = $request->owner_approval_password;
                if (! $ownerPassword) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Diskon {$discountPercent}% melebihi batas kasir ({$maxDiscountPercent}%). Memerlukan otorisasi password Pemilik Kios.",
                        'needs_approval' => true,
                    ], 422);
                }

                $owner = User::where('role', 'pemilik')->first();
                if (! $owner || ! Hash::check($ownerPassword, $owner->password)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Password otorisasi pemilik salah! Diskon ditolak.',
                    ], 422);
                }

                $approvedBy = $owner->id;
            }
        }

        return DB::transaction(function () use ($request, $user, $discountPercent, $approvedBy) {
            // Pelanggan opsional
            $customerId = $request->customer_id;
            if (! $customerId && $request->filled('customer_name')) {
                $customer = Customer::create([
                    'name' => $request->customer_name,
                    'phone' => $request->customer_phone,
                ]);
                $customerId = $customer->id;
            }

            // Hitung subtotal dan periksa ketersediaan stok
            $subtotal = 0;
            $totalCost = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['id']);
                $qty = (int) $item['quantity'];

                if ($qty > $product->stock) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Stok untuk {$product->name} tidak mencukupi (Tersedia: {$product->stock} {$product->unit}). Transaksi dibatalkan untuk mencegah stok negatif.",
                    ], 422);
                }

                $itemSubtotal = $product->sell_price * $qty;
                $itemCost = $product->buy_price * $qty;

                $subtotal += $itemSubtotal;
                $totalCost += $itemCost;

                $itemsData[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'buy_price' => $product->buy_price,
                    'sell_price' => $product->sell_price,
                    'subtotal' => $itemSubtotal,
                ];
            }

            // Hitung diskon nominal
            $discountAmount = 0;
            if ($discountPercent > 0) {
                $discountAmount = ($subtotal * $discountPercent) / 100;
            } elseif ($request->filled('discount_amount')) {
                $discountAmount = (float) $request->discount_amount;
            }

            $grandTotal = max(0, $subtotal - $discountAmount);
            $paymentMethod = $request->payment_method;
            $cashPaid = $paymentMethod === 'cash' ? (float) $request->cash_paid : $grandTotal;

            if ($paymentMethod === 'cash' && $cashPaid < $grandTotal) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jumlah uang tunai yang dibayarkan kurang dari total belanja.',
                ], 422);
            }

            $cashChange = $paymentMethod === 'cash' ? ($cashPaid - $grandTotal) : 0;
            $invoiceNumber = 'KB-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            // Simpan Order
            $order = Order::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => $user->id,
                'customer_id' => $customerId,
                'subtotal' => $subtotal,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'requires_approval' => (bool) $approvedBy,
                'approved_by' => $approvedBy,
                'grand_total' => $grandTotal,
                'total_cost' => $totalCost,
                'payment_method' => $paymentMethod,
                'cash_paid' => $cashPaid,
                'cash_change' => $cashChange,
                'notes' => $request->notes,
            ]);

            // Simpan Order Items & Kurangi Stok
            foreach ($itemsData as $data) {
                $product = $data['product'];
                $qty = $data['quantity'];
                $stockBefore = $product->stock;
                $stockAfter = $stockBefore - $qty;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_sku' => $product->sku,
                    'product_name' => $product->name,
                    'buy_price' => $data['buy_price'],
                    'sell_price' => $data['sell_price'],
                    'quantity' => $qty,
                    'discount' => 0,
                    'subtotal' => $data['subtotal'],
                ]);

                // Kurangi stok barang
                $product->update(['stock' => $stockAfter]);

                // Catat mutasi stok penjualan
                StockMutation::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'type' => 'sale',
                    'quantity' => $qty,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_number' => $invoiceNumber,
                    'notes' => "Penjualan Kasir POS",
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil diproses!',
                'order_id' => $order->id,
                'invoice_number' => $order->invoice_number,
                'print_url' => route('pos.receipt', $order->id),
            ]);
        });
    }

    public function receipt(Order $order)
    {
        $order->load(['items.product', 'user', 'customer']);
        $storeName = Setting::get('store_name', 'Kios Blessing');
        $storeAddress = Setting::get('store_address', 'Jl. Kasih Karunia No. 12, Manado');
        $storePhone = Setting::get('store_phone', '081234567890');
        $receiptFooter = Setting::get('receipt_footer', 'Terima kasih telah berbelanja di Kios Blessing!');

        return view('pos.receipt', compact('order', 'storeName', 'storeAddress', 'storePhone', 'receiptFooter'));
    }
}
