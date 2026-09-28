<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;

class WhatsAppNotificationService
{
    /**
     * Build the WhatsApp redirection URL for a newly created order.
     */
    public function getCheckoutRedirectUrl(Order $order): string
    {
        $storePhone = Setting::get('store_whatsapp_phone', '33612345678');
        // Sanitize phone number (digits only)
        $cleanPhone = preg_replace('/[^0-9]/', '', $storePhone);

        $message = "Bonjour ! Je souhaite valider ma commande sur la boutique.\n\n";
        $message .= "📦 *N° de Commande :* {$order->order_number}\n";
        $message .= "👤 *Nom :* {$order->customer_name}\n";
        $message .= "📞 *Téléphone :* {$order->customer_phone}\n";
        if ($order->customer_address) {
            $message .= "📍 *Adresse :* {$order->customer_address}\n";
        }
        $message .= "\n🛒 *Détail des articles :*\n";

        foreach ($order->items as $item) {
            $message .= "• {$item->product_name} x{$item->quantity} — " . number_format($item->subtotal, 2, ',', ' ') . " €\n";
        }

        $message .= "\n💰 *Total :* " . number_format($order->total_amount, 2, ',', ' ') . " €\n";
        if ($order->notes) {
            $message .= "📝 *Note :* {$order->notes}\n";
        }

        $message .= "\nMerci de confirmer la prise en charge de ma commande !";

        return "https://wa.me/{$cleanPhone}?text=" . urlencode($message);
    }

    /**
     * Send backend notification when an order status is modified.
     */
    public function sendStatusUpdateNotification(Order $order): bool
    {
        $status = $order->status;
        if (!$status || empty($status->whatsapp_template)) {
            return false;
        }

        $driver = Setting::get('whatsapp_driver', 'log');
        if ($driver === 'none' || empty($driver)) {
            return true;
        }

        // Replace placeholders
        $message = str_replace(
            [
                '{customer_name}',
                '{order_number}',
                '{status_name}',
                '{total_amount}',
                '{created_at}',
            ],
            [
                $order->customer_name,
                $order->order_number,
                $status->name,
                number_format($order->total_amount, 2, ',', ' '),
                $order->created_at->format('d/m/Y H:i'),
            ],
            $status->whatsapp_template
        );

        $customerPhone = preg_replace('/[^0-9]/', '', $order->customer_phone);

        switch ($driver) {
            case 'log':
                \Illuminate\Support\Facades\Log::info("[WhatsApp Notification Simulator] To: {$customerPhone} | Message: {$message}");
                return true;

            case 'whatsapp_cloud_api':
                return $this->sendViaWhatsAppCloudApi($customerPhone, $message);

            case 'twilio':
                return $this->sendViaTwilio($customerPhone, $message);

            default:
                return false;
        }
    }

    protected function sendViaWhatsAppCloudApi(string $phone, string $message): bool
    {
        $token = Setting::get('whatsapp_cloud_api_token');
        $phoneId = Setting::get('whatsapp_cloud_api_phone_number_id');

        if (!$token || !$phoneId) {
            \Illuminate\Support\Facades\Log::warning("WhatsApp Cloud API credentials missing.");
            return false;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->post("https://graph.facebook.com/v18.0/{$phoneId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $phone,
                    'type' => 'text',
                    'text' => ['preview_url' => false, 'body' => $message],
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("WhatsApp Cloud API error: " . $e->getMessage());
            return false;
        }
    }

    protected function sendViaTwilio(string $phone, string $message): bool
    {
        $sid = Setting::get('twilio_account_sid');
        $token = Setting::get('twilio_auth_token');
        $from = Setting::get('twilio_from_phone');

        if (!$sid || !$token || !$from) {
            \Illuminate\Support\Facades\Log::warning("Twilio WhatsApp credentials missing.");
            return false;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => 'whatsapp:' . $from,
                    'To' => 'whatsapp:' . (str_starts_with($phone, '+') ? $phone : '+' . $phone),
                    'Body' => $message,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Twilio WhatsApp error: " . $e->getMessage());
            return false;
        }
    }
}
