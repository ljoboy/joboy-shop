<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_homepage_displays_products(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Smartphone Galaxy S24 Ultra');
    }

    public function test_product_detail_page(): void
    {
        $product = Product::first();

        $response = $this->get('/produit/' . $product->slug);

        $response->assertStatus(200);
        $response->assertSee($product->name);
    }

    public function test_checkout_creates_order_and_returns_whatsapp_link(): void
    {
        $product = Product::first();

        $payload = [
            'customer_name' => 'Alice Test',
            'customer_phone' => '+33699887766',
            'customer_email' => 'alice@example.com',
            'customer_address' => '10 Rue de Paris',
            'cart' => [
                [
                    'id' => $product->id,
                    'quantity' => 2,
                ]
            ]
        ];

        $response = $this->postJson('/checkout', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'order_number', 'redirect_url']);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Alice Test',
            'customer_phone' => '+33699887766',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_customer_can_track_order_by_number(): void
    {
        $order = Order::first();

        $response = $this->get('/suivi-commande?order_number=' . $order->order_number);

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertSee($order->customer_name);
    }
}
