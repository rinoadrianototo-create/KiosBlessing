<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosAndStockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('max_cashier_discount_percent', '10');
    }

    public function test_cashier_can_checkout_and_stock_decreases_correctly(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $cat = Category::create(['name' => 'Minuman']);
        $product = Product::create([
            'sku' => 'PRD-TEST-1',
            'name' => 'Kopi Test',
            'category_id' => $cat->id,
            'buy_price' => 2000,
            'sell_price' => 3000,
            'stock' => 10,
            'unit' => 'pcs',
            'min_stock' => 2,
        ]);

        $this->actingAs($kasir);

        $response = $this->postJson('/pos/checkout', [
            'items' => [
                ['id' => $product->id, 'quantity' => 3],
            ],
            'payment_method' => 'cash',
            'cash_paid' => 10000,
            'discount_percent' => 0,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        // Pastikan stok berkurang dari 10 menjadi 7
        $this->assertEquals(7, $product->fresh()->stock);

        // Pastikan order dan order items tersimpan
        $this->assertDatabaseHas('orders', [
            'subtotal' => 9000,
            'grand_total' => 9000,
            'total_cost' => 6000, // 3 x 2000
            'cash_paid' => 10000,
            'cash_change' => 1000,
        ]);
    }

    public function test_pos_prevents_negative_stock(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $cat = Category::create(['name' => 'Sembako']);
        $product = Product::create([
            'sku' => 'PRD-TEST-2',
            'name' => 'Beras Test',
            'category_id' => $cat->id,
            'buy_price' => 50000,
            'sell_price' => 60000,
            'stock' => 2, // hanya ada 2
            'unit' => 'karung',
            'min_stock' => 1,
        ]);

        $this->actingAs($kasir);

        // Kasir coba jual 5 (melebihi stok 2)
        $response = $this->postJson('/pos/checkout', [
            'items' => [
                ['id' => $product->id, 'quantity' => 5],
            ],
            'payment_method' => 'cash',
            'cash_paid' => 300000,
        ]);

        $response->assertStatus(422);
        // Stok tidak berubah
        $this->assertEquals(2, $product->fresh()->stock);
    }

    public function test_discount_exceeding_cashier_limit_requires_owner_approval(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir', 'is_active' => true]);
        $owner = User::factory()->create([
            'role' => 'pemilik',
            'password' => bcrypt('secret123'),
            'is_active' => true,
        ]);

        $cat = Category::create(['name' => 'Snack']);
        $product = Product::create([
            'sku' => 'PRD-TEST-3',
            'name' => 'Biskuit Test',
            'category_id' => $cat->id,
            'buy_price' => 4000,
            'sell_price' => 5000,
            'stock' => 20,
            'unit' => 'pcs',
            'min_stock' => 5,
        ]);

        $this->actingAs($kasir);

        // Kasir coba beri diskon 25% (melebihi batas 10%) tanpa password
        $response = $this->postJson('/pos/checkout', [
            'items' => [
                ['id' => $product->id, 'quantity' => 2],
            ],
            'discount_percent' => 25,
            'payment_method' => 'cash',
            'cash_paid' => 10000,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['needs_approval' => true]);

        // Kasir coba beri diskon 25% DENGAN password pemilik yang benar
        $responseWithAuth = $this->postJson('/pos/checkout', [
            'items' => [
                ['id' => $product->id, 'quantity' => 2],
            ],
            'discount_percent' => 25,
            'payment_method' => 'cash',
            'cash_paid' => 10000,
            'owner_approval_password' => 'secret123',
        ]);

        $responseWithAuth->assertStatus(200);
        $responseWithAuth->assertJson(['status' => 'success']);
    }
}
