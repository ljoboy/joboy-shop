<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Dashboard & Orders List
    public function dashboard(Request $request)
    {
        $statusFilter = $request->get('status');

        $ordersQuery = Order::with(['status', 'items'])->latest();
        if ($statusFilter) {
            $ordersQuery->whereHas('status', function($q) use ($statusFilter) {
                $q->where('slug', $statusFilter);
            });
        }
        $orders = $ordersQuery->paginate(15);

        $statuses = OrderStatus::orderBy('position')->get();
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalAdmins = User::whereIn('role', ['super_admin', 'admin'])->count();
        $totalRevenue = Order::sum('total_amount');

        return view('admin.dashboard', compact('orders', 'statuses', 'statusFilter', 'totalOrders', 'totalProducts', 'totalAdmins', 'totalRevenue'));
    }

    // Update Order Status & Send Notification
    public function updateOrderStatus(Request $request, Order $order, WhatsAppNotificationService $waService)
    {
        $validated = $request->validate([
            'order_status_id' => 'required|exists:order_statuses,id',
        ]);

        $oldStatusId = $order->order_status_id;
        $order->update(['order_status_id' => $validated['order_status_id']]);

        // Send WhatsApp notification if status changed
        if ($oldStatusId != $validated['order_status_id']) {
            $waService->sendStatusUpdateNotification($order);
        }

        return redirect()->back()->with('success', 'Statut de la commande mis à jour avec succès et notification WhatsApp envoyée/enregistrée.');
    }

    // --- Settings & WhatsApp Config ---
    public function editSettings()
    {
        $settings = [
            'store_name' => Setting::get('store_name', 'Boutique WhatsApp Pro'),
            'store_whatsapp_phone' => Setting::get('store_whatsapp_phone', '33612345678'),
            'whatsapp_driver' => Setting::get('whatsapp_driver', 'log'),
            'whatsapp_cloud_api_token' => Setting::get('whatsapp_cloud_api_token', ''),
            'whatsapp_cloud_api_phone_number_id' => Setting::get('whatsapp_cloud_api_phone_number_id', ''),
            'twilio_account_sid' => Setting::get('twilio_account_sid', ''),
            'twilio_auth_token' => Setting::get('twilio_auth_token', ''),
            'twilio_from_phone' => Setting::get('twilio_from_phone', ''),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'store_name' => 'required|string|max:255',
            'store_whatsapp_phone' => 'required|string|max:50',
            'whatsapp_driver' => 'required|in:none,log,whatsapp_cloud_api,twilio',
            'whatsapp_cloud_api_token' => 'nullable|string',
            'whatsapp_cloud_api_phone_number_id' => 'nullable|string',
            'twilio_account_sid' => 'nullable|string',
            'twilio_auth_token' => 'nullable|string',
            'twilio_from_phone' => 'nullable|string',
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        return redirect()->back()->with('success', 'Paramètres mis à jour avec succès.');
    }
}
