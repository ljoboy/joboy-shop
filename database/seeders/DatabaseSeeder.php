<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Super Admin & Admin Users
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@whatsapp-store.com'],
            [
                'name' => 'Super Admin',
                'phone' => '+33600000001',
                'role' => 'super_admin',
                'password' => Hash::make('password123'),
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'manager@whatsapp-store.com'],
            [
                'name' => 'Gestionnaire Boutique',
                'phone' => '+33600000002',
                'role' => 'admin',
                'password' => Hash::make('password123'),
            ]
        );

        $customer = User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Jean Dupont',
                'phone' => '+33612345678',
                'role' => 'customer',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Default Order Statuses
        $statuses = [
            [
                'name' => 'En attente',
                'slug' => 'en-attente',
                'badge_color' => 'yellow',
                'is_default' => true,
                'position' => 1,
                'whatsapp_template' => "Bonjour {customer_name}, votre commande #{order_number} a bien été reçue ! Statut : EN ATTENTE DE CONFIRMATION. Total : {total_amount} €. Merci pour votre confiance !",
            ],
            [
                'name' => 'En préparation',
                'slug' => 'en-preparation',
                'badge_color' => 'blue',
                'is_default' => false,
                'position' => 2,
                'whatsapp_template' => "Bonjour {customer_name}, votre commande #{order_number} est désormais EN COURS DE PRÉPARATION dans nos ateliers. Nous vous informerons dès son expédition !",
            ],
            [
                'name' => 'Expédiée',
                'slug' => 'expediee',
                'badge_color' => 'indigo',
                'is_default' => false,
                'position' => 3,
                'whatsapp_template' => "Bonne nouvelle {customer_name} ! Votre commande #{order_number} a été EXPÉDIÉE. Elle sera bientôt livrée à votre adresse.",
            ],
            [
                'name' => 'Livrée',
                'slug' => 'livree',
                'badge_color' => 'green',
                'is_default' => false,
                'position' => 4,
                'whatsapp_template' => "Bonjour {customer_name}, votre commande #{order_number} a été marquée comme LIVRÉE. Nous espérons que vos articles vous plairont !",
            ],
            [
                'name' => 'Annulée',
                'slug' => 'annulee',
                'badge_color' => 'red',
                'is_default' => false,
                'position' => 5,
                'whatsapp_template' => "Bonjour {customer_name}, votre commande #{order_number} a été ANNULÉE. N'hésitez pas à nous contacter pour toute question.",
            ],
        ];

        foreach ($statuses as $statusData) {
            OrderStatus::updateOrCreate(['slug' => $statusData['slug']], $statusData);
        }

        // 3. Settings Defaults
        Setting::set('store_name', 'Boutique WhatsApp Pro');
        Setting::set('store_whatsapp_phone', '33612345678');
        Setting::set('currency_symbol', '€');
        Setting::set('whatsapp_driver', 'log'); // 'none', 'log', 'whatsapp_cloud_api', 'twilio'
        Setting::set('whatsapp_cloud_api_token', '');
        Setting::set('whatsapp_cloud_api_phone_number_id', '');
        Setting::set('twilio_account_sid', '');
        Setting::set('twilio_auth_token', '');
        Setting::set('twilio_from_phone', '');

        // 4. Sample Products
        $products = [
            [
                'name' => 'Smartphone Galaxy S24 Ultra',
                'slug' => 'smartphone-galaxy-s24-ultra',
                'description' => 'Un smartphone haut de gamme avec écran AMOLED 6.8 pouces, appareil photo 200 MP et processeur ultra rapide.',
                'price' => 1199.00,
                'image' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=600&auto=format&fit=crop',
                'is_active' => true,
                'stock' => 15,
            ],
            [
                'name' => 'Casque Sans Fil Noise-Cancelling',
                'slug' => 'casque-sans-fil-noise-cancelling',
                'description' => 'Casque audio circum-aural avec réduction de bruit active, autonomie de 30 heures et son haute fidélité.',
                'price' => 249.99,
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop',
                'is_active' => true,
                'stock' => 30,
            ],
            [
                'name' => 'Montre Connectée Sport Pro',
                'slug' => 'montre-connectee-sport-pro',
                'description' => 'Montre intelligente avec suivi cardiaque, GPS intégré, étanche jusqu\'à 50m et autonomie 7 jours.',
                'price' => 179.50,
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop',
                'is_active' => true,
                'stock' => 20,
            ],
            [
                'name' => 'Enceinte Bluetooth Portable',
                'slug' => 'enceinte-bluetooth-portable',
                'description' => 'Enceinte étanche IPX7 avec basses puissantes, microphone intégré pour appels et 12h de lecture.',
                'price' => 89.90,
                'image' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&auto=format&fit=crop',
                'is_active' => true,
                'stock' => 50,
            ],
        ];

        foreach ($products as $pData) {
            Product::updateOrCreate(['slug' => $pData['slug']], $pData);
        }

        // 5. Sample Order for demo
        $defaultStatus = OrderStatus::where('is_default', true)->first();
        $product1 = Product::first();

        if ($defaultStatus && $product1) {
            $order = Order::create([
                'order_number' => 'CMD-' . strtoupper(Str::random(8)),
                'user_id' => $customer->id,
                'customer_name' => 'Jean Dupont',
                'customer_phone' => '+33612345678',
                'customer_email' => 'client@example.com',
                'customer_address' => '12 Rue de la Paix, 75002 Paris',
                'total_amount' => $product1->price,
                'order_status_id' => $defaultStatus->id,
                'notes' => 'Livraison l\'après-midi de préférence.',
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product1->id,
                'product_name' => $product1->name,
                'unit_price' => $product1->price,
                'quantity' => 1,
                'subtotal' => $product1->price,
            ]);
        }
    }
}
