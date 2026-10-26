<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Default users for Kios Blessing
        $pemilik = User::updateOrCreate(
            ['email' => 'pemilik@kiosblessing.com'],
            [
                'name' => 'Pemilik Kios Blessing',
                'password' => bcrypt('password123'),
                'role' => 'pemilik',
                'phone' => '081234567890',
                'is_active' => true,
            ]
        );

        $admin = User::updateOrCreate(
            ['email' => 'admin@kiosblessing.com'],
            [
                'name' => 'Admin Operasional',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'phone' => '081234567891',
                'is_active' => true,
            ]
        );

        $kasir = User::updateOrCreate(
            ['email' => 'kasir@kiosblessing.com'],
            [
                'name' => 'Kasir Kios',
                'password' => bcrypt('password123'),
                'role' => 'kasir',
                'phone' => '081234567892',
                'is_active' => true,
            ]
        );

        // 2. Default Settings
        \App\Models\Setting::set('store_name', 'Kios Blessing');
        \App\Models\Setting::set('store_address', 'Jl. Kasih Karunia No. 12, Manado');
        \App\Models\Setting::set('store_phone', '081234567890');
        \App\Models\Setting::set('receipt_footer', 'Terima kasih telah berbelanja di Kios Blessing! Tuhan Memberkati.');
        \App\Models\Setting::set('max_cashier_discount_percent', '10'); // Max 10% diskon kasir tanpa otorisasi pemilik

        // 3. Categories
        $sembako = \App\Models\Category::updateOrCreate(['name' => 'Sembako'], ['description' => 'Bahan pokok kebutuhan sehari-hari']);
        $minuman = \App\Models\Category::updateOrCreate(['name' => 'Minuman'], ['description' => 'Minuman segar, kopi, dan teh']);
        $snack = \App\Models\Category::updateOrCreate(['name' => 'Makanan Ringan'], ['description' => 'Snack, biskuit, dan mie']);
        $rumah = \App\Models\Category::updateOrCreate(['name' => 'Kebutuhan Rumah'], ['description' => 'Sabun, deterjen, dan perlengkapan']);

        // 4. Sample Products
        $products = [
            [
                'sku' => 'PRD-001',
                'barcode' => '899275311101',
                'name' => 'Beras Premium Ramos 5 Kg',
                'category_id' => $sembako->id,
                'brand' => 'Ramos',
                'buy_price' => 68000,
                'sell_price' => 75000,
                'stock' => 20,
                'unit' => 'karung',
                'min_stock' => 5,
                'description' => 'Beras pulen kualitas premium',
            ],
            [
                'sku' => 'PRD-002',
                'barcode' => '899886620021',
                'name' => 'Minyak Goreng Sania 2 Liter',
                'category_id' => $sembako->id,
                'brand' => 'Sania',
                'buy_price' => 32000,
                'sell_price' => 37000,
                'stock' => 25,
                'unit' => 'pouch',
                'min_stock' => 6,
                'description' => 'Minyak goreng kelapa sawit higienis',
            ],
            [
                'sku' => 'PRD-003',
                'barcode' => '899317553805',
                'name' => 'Gula Pasir Gulaku 1 Kg',
                'category_id' => $sembako->id,
                'brand' => 'Gulaku',
                'buy_price' => 15500,
                'sell_price' => 18000,
                'stock' => 4, // Sengaja di bawah min_stock untuk trigger low stock warning
                'unit' => 'kg',
                'min_stock' => 8,
                'description' => 'Gula pasir kristal putih higienis',
            ],
            [
                'sku' => 'PRD-004',
                'barcode' => '899100210123',
                'name' => 'Indomie Goreng Spesial',
                'category_id' => $snack->id,
                'brand' => 'Indofood',
                'buy_price' => 2800,
                'sell_price' => 3500,
                'stock' => 80,
                'unit' => 'pcs',
                'min_stock' => 20,
                'description' => 'Mi instan goreng rasa spesial',
            ],
            [
                'sku' => 'PRD-005',
                'barcode' => '899999900112',
                'name' => 'Air Mineral Le Minerale 600ml',
                'category_id' => $minuman->id,
                'brand' => 'Mayora',
                'buy_price' => 2500,
                'sell_price' => 3500,
                'stock' => 48,
                'unit' => 'botol',
                'min_stock' => 12,
                'description' => 'Air mineral pegunungan botol 600ml',
            ],
            [
                'sku' => 'PRD-006',
                'barcode' => '899234567890',
                'name' => 'Kopi Kapal Api Special Mix 1 Renceng',
                'category_id' => $minuman->id,
                'brand' => 'Kapal Api',
                'buy_price' => 14000,
                'sell_price' => 16500,
                'stock' => 15,
                'unit' => 'renceng',
                'min_stock' => 5,
                'description' => 'Kopi plus gula isi 10 sachet',
            ],
            [
                'sku' => 'PRD-007',
                'barcode' => '899889911223',
                'name' => 'Sabun Mandi Lifebuoy Total 10',
                'category_id' => $rumah->id,
                'brand' => 'Unilever',
                'buy_price' => 3800,
                'sell_price' => 5000,
                'stock' => 30,
                'unit' => 'batang',
                'min_stock' => 10,
                'description' => 'Sabun batang antibakteri 85g',
            ],
        ];

        foreach ($products as $pData) {
            $p = \App\Models\Product::updateOrCreate(['sku' => $pData['sku']], $pData);
            
            // Catat mutasi stok awal jika belum ada
            if (\App\Models\StockMutation::where('product_id', $p->id)->count() === 0) {
                \App\Models\StockMutation::create([
                    'product_id' => $p->id,
                    'user_id' => $admin->id,
                    'type' => 'in',
                    'quantity' => $p->stock,
                    'stock_before' => 0,
                    'stock_after' => $p->stock,
                    'reference_number' => 'STK-INIT',
                    'notes' => 'Stok awal sistem',
                ]);
            }
        }

        // 5. Initial Capital & Expense Sample
        if (\App\Models\Finance::where('type', 'capital')->count() === 0) {
            \App\Models\Finance::create([
                'type' => 'capital',
                'category' => 'Modal Awal',
                'amount' => 5000000,
                'date' => now()->startOfMonth()->toDateString(),
                'description' => 'Penyertaan modal awal operasional kios',
                'user_id' => $pemilik->id,
                'reference_number' => 'MODAL-001',
            ]);

            \App\Models\Finance::create([
                'type' => 'expense',
                'category' => 'Listrik & Utilitas',
                'amount' => 150000,
                'date' => now()->subDays(2)->toDateString(),
                'description' => 'Pembelian token listrik kios',
                'user_id' => $pemilik->id,
                'reference_number' => 'EXP-001',
            ]);
        }

        // 6. Sample Customer
        \App\Models\Customer::updateOrCreate(
            ['phone' => '082198765432'],
            ['name' => 'Ibu Maria', 'address' => 'Kompleks Kios Blok B']
        );
    }
}
