<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Kios Blessing');
    }

    public function test_user_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'pemilik@kiosblessing.com',
            'password' => bcrypt('password123'),
            'role' => 'pemilik',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'pemilik@kiosblessing.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
    }

    public function test_cashier_cannot_access_owner_dashboard_or_finances(): void
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $this->actingAs($kasir);

        // Kasir coba akses dashboard -> 403 Forbidden
        $responseDashboard = $this->get('/dashboard');
        $responseDashboard->assertStatus(403);

        // Kasir coba akses finance -> 403 Forbidden
        $responseFinance = $this->get('/finance');
        $responseFinance->assertStatus(403);

        // Kasir dapat akses POS -> 200 OK
        $responsePos = $this->get('/pos');
        $responsePos->assertStatus(200);
    }

    public function test_admin_cannot_access_finances(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        // Admin dilarang akses laporan keuangan -> 403 Forbidden
        $responseFinance = $this->get('/finance');
        $responseFinance->assertStatus(403);

        // Admin diizinkan akses katalog barang -> 200 OK
        $responseProducts = $this->get('/products');
        $responseProducts->assertStatus(200);
    }
}
