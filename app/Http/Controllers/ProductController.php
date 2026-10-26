<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->get('filter') === 'low_stock') {
            $query->whereColumn('stock', '<=', 'min_stock');
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $lowStockCount = Product::whereColumn('stock', '<=', 'min_stock')->count();

        return view('products.index', compact('products', 'categories', 'lowStockCount'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:50|unique:products,sku',
            'barcode' => 'nullable|string|max:50|unique:products,barcode',
            'name' => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0|gte:buy_price',
            'stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:30',
            'min_stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
        ], [
            'sell_price.gte' => 'Harga jual tidak boleh lebih kecil dari harga modal.',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image'] = $path;
        }

        $product = Product::create($validated);

        // Catat mutasi stok awal jika stok > 0
        if ($product->stock > 0) {
            StockMutation::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => 'in',
                'quantity' => $product->stock,
                'stock_before' => 0,
                'stock_after' => $product->stock,
                'reference_number' => 'STK-INIT',
                'notes' => 'Stok awal saat pendaftaran barang',
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Barang baru berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:50|unique:products,barcode,' . $product->id,
            'name' => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0|gte:buy_price',
            'unit' => 'required|string|max:30',
            'min_stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ], [
            'sell_price.gte' => 'Harga jual tidak boleh lebih kecil dari harga modal.',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['is_active'] = $request->has('is_active');

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Data barang berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Barang berhasil dihapus.');
    }
}
