<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Setting;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StorefrontController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)->latest()->get();
        return view('storefront.index', compact('products'));
    }

    public function showProduct($slug)
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();
        return view('storefront.product', compact('product'));
    }

    public function checkout(Request $request, WhatsAppNotificationService $waService)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
        ]);

        $defaultStatus = OrderStatus::where('is_default', true)->first()
            ?? OrderStatus::orderBy('position')->first();

        $totalAmount = 0;
        $itemsToSave = [];

        foreach ($validated['cart'] as $cartItem) {
            $product = Product::find($cartItem['id']);
            if (!$product || !$product->is_active) {
                continue;
            }
            $qty = (int) $cartItem['quantity'];
            $subtotal = $product->price * $qty;
            $totalAmount += $subtotal;

            $itemsToSave[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $product->price,
                'quantity' => $qty,
                'subtotal' => $subtotal,
            ];
        }

        if (empty($itemsToSave)) {
            return response()->json(['error' => 'Votre panier est vide ou les produits ne sont plus disponibles.'], 422);
        }

        $order = Order::create([
            'order_number' => 'CMD-' . strtoupper(Str::random(8)),
            'user_id' => auth()->check() ? auth()->id() : null,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'] ?? (auth()->check() ? auth()->user()->email : null),
            'customer_address' => $validated['customer_address'] ?? null,
            'total_amount' => $totalAmount,
            'order_status_id' => $defaultStatus?->id,
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($itemsToSave as $itemData) {
            $order->items()->create($itemData);
        }

        $redirectUrl = $waService->getCheckoutRedirectUrl($order);

        return response()->json([
            'success' => true,
            'order_number' => $order->order_number,
            'redirect_url' => $redirectUrl,
        ]);
    }

    public function trackOrder(Request $request)
    {
        $order = null;
        $searched = false;

        if ($request->filled('order_number')) {
            $searched = true;
            $orderNumber = trim($request->input('order_number'));
            $order = Order::with(['status', 'items'])->where('order_number', $orderNumber)->first();
        }

        return view('storefront.track', compact('order', 'searched'));
    }
}
