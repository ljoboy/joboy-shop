<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->superAdmin = User::where('role', 'super_admin')->first();
        $this->admin = User::where('role', 'admin')->first();
        $this->customer = User::where('role', 'customer')->first();
    }

    public function test_non_admin_cannot_access_admin_panel(): void
    {
        $response = $this->actingAs($this->customer)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_dashboard_and_orders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Liste des Commandes');
    }

    public function test_admin_can_update_order_status_and_trigger_whatsapp_notification(): void
    {
        Log::spy();

        $order = Order::first();
        $newStatus = OrderStatus::where('slug', 'expediee')->first();

        $response = $this->actingAs($this->admin)
            ->patch("/admin/orders/{$order->id}/status", [
                'order_status_id' => $newStatus->id,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status_id' => $newStatus->id,
        ]);

        Log::shouldHaveReceived('info')->once();
    }

    public function test_admin_can_crud_products(): void
    {
        // Create product
        $response = $this->actingAs($this->admin)->post('/admin/products', [
            'name' => 'Nouveau Smartphone Test',
            'price' => 299.99,
            'description' => 'Test description',
            'is_active' => '1',
        ]);
        $response->assertRedirect('/admin/products');

        $this->assertDatabaseHas('products', [
            'name' => 'Nouveau Smartphone Test',
            'price' => 299.99,
        ]);

        $product = Product::where('name', 'Nouveau Smartphone Test')->first();

        // Update product
        $response = $this->actingAs($this->admin)->put("/admin/products/{$product->id}", [
            'name' => 'Nouveau Smartphone Test Edité',
            'price' => 349.99,
            'description' => 'Test description updated',
            'is_active' => '1',
        ]);
        $response->assertRedirect('/admin/products');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Nouveau Smartphone Test Edité',
            'price' => 349.99,
        ]);

        // Delete product
        $response = $this->actingAs($this->admin)->delete("/admin/products/{$product->id}");
        $response->assertRedirect('/admin/products');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_admin_can_crud_order_statuses(): void
    {
        // Create status
        $response = $this->actingAs($this->admin)->post('/admin/statuses', [
            'name' => 'En Cours De Verification',
            'badge_color' => 'blue',
            'whatsapp_template' => 'Bonjour {customer_name}, votre commande #{order_number} est en vérification.',
        ]);
        $response->assertRedirect('/admin/statuses');

        $this->assertDatabaseHas('order_statuses', [
            'name' => 'En Cours De Verification',
        ]);
    }

    public function test_super_admin_can_manage_admins(): void
    {
        // Admin cannot manage users
        $response = $this->actingAs($this->admin)->get('/admin/users');
        $response->assertStatus(403);

        // Super Admin can manage users
        $response = $this->actingAs($this->superAdmin)->get('/admin/users');
        $response->assertStatus(200);

        // Super Admin creates a new admin
        $response = $this->actingAs($this->superAdmin)->post('/admin/users', [
            'name' => 'Nouvel Admin',
            'email' => 'newadmin@example.com',
            'role' => 'admin',
            'password' => 'password123',
        ]);
        $response->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@example.com',
            'role' => 'admin',
        ]);
    }
}
