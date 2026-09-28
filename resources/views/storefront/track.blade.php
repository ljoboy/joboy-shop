@extends('layouts.app')

@section('title', 'Suivi de commande - Boutique WhatsApp')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-10 text-center">
        <i class="fa-solid fa-magnifying-glass-location text-emerald-600 text-5xl mb-4"></i>
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Suivre ma commande</h1>
        <p class="text-gray-500 mb-8 text-sm">Entrez votre numéro de commande (ex: CMD-XXXXXX) pour connaître l'état d'avancement de votre livraison.</p>

        <form method="GET" action="{{ route('track.order') }}" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto mb-8">
            <input type="text" name="order_number" value="{{ request('order_number') }}" required placeholder="Numéro de commande (ex: CMD-12345678)" class="flex-grow rounded-xl border-gray-300 border p-3 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-3 rounded-xl transition shadow flex items-center justify-center space-x-2">
                <i class="fa-solid fa-search"></i>
                <span>Rechercher</span>
            </button>
        </form>

        @if($searched)
            @if($order)
                <div class="mt-8 text-left border-t border-gray-100 pt-8">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between bg-emerald-50 p-4 rounded-2xl border border-emerald-100 mb-6">
                        <div>
                            <span class="text-xs uppercase font-bold text-emerald-800 tracking-wider">Commande N°</span>
                            <h2 class="text-2xl font-black text-emerald-900">{{ $order->order_number }}</h2>
                            <p class="text-xs text-emerald-700 mt-1">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        <div class="mt-3 sm:mt-0">
                            @php
                                $colorClasses = [
                                    'yellow' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                    'blue' => 'bg-blue-100 text-blue-800 border-blue-300',
                                    'indigo' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                                    'green' => 'bg-green-100 text-green-800 border-green-300',
                                    'red' => 'bg-red-100 text-red-800 border-red-300',
                                    'gray' => 'bg-gray-100 text-gray-800 border-gray-300',
                                ];
                                $badgeColor = $order->status ? $order->status->badge_color : 'gray';
                                $badgeClass = $colorClasses[$badgeColor] ?? $colorClasses['gray'];
                            @endphp
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-extrabold border {{ $badgeClass }}">
                                <i class="fa-solid fa-circle text-xs mr-2"></i>
                                {{ $order->status ? $order->status->name : 'Non défini' }}
                            </span>
                        </div>
                    </div>

                    <!-- Customer Info & Items -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <h3 class="font-bold text-gray-800 text-sm mb-2 flex items-center">
                                <i class="fa-solid fa-user text-emerald-600 mr-2"></i> Client
                            </h3>
                            <p class="text-sm font-semibold text-gray-900">{{ $order->customer_name }}</p>
                            <p class="text-xs text-gray-600">{{ $order->customer_phone }}</p>
                            @if($order->customer_address)
                                <p class="text-xs text-gray-600 mt-1"><i class="fa-solid fa-location-dot mr-1"></i> {{ $order->customer_address }}</p>
                            @endif
                        </div>

                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <h3 class="font-bold text-gray-800 text-sm mb-2 flex items-center">
                                <i class="fa-solid fa-receipt text-emerald-600 mr-2"></i> Résumé
                            </h3>
                            <p class="text-sm text-gray-600">Nombre d'articles : <span class="font-bold text-gray-900">{{ $order->items->sum('quantity') }}</span></p>
                            <p class="text-sm text-gray-600 mt-1">Montant total : <span class="text-lg font-black text-emerald-600">{{ number_format($order->total_amount, 2, ',', ' ') }} €</span></p>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="border border-gray-100 rounded-2xl overflow-hidden mb-6">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 text-gray-600 text-xs uppercase border-b border-gray-100">
                                <tr>
                                    <th class="p-3">Article</th>
                                    <th class="p-3 text-center">Quantité</th>
                                    <th class="p-3 text-right">Prix Unitaire</th>
                                    <th class="p-3 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($order->items as $item)
                                    <tr>
                                        <td class="p-3 font-medium text-gray-900">{{ $item->product_name }}</td>
                                        <td class="p-3 text-center font-bold">{{ $item->quantity }}</td>
                                        <td class="p-3 text-right">{{ number_format($item->unit_price, 2, ',', ' ') }} €</td>
                                        <td class="p-3 text-right font-bold text-emerald-600">{{ number_format($item->subtotal, 2, ',', ' ') }} €</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="p-6 bg-red-50 text-red-700 rounded-2xl border border-red-100 text-center">
                    <i class="fa-solid fa-triangle-exclamation text-3xl text-red-500 mb-2"></i>
                    <p class="font-bold">Aucune commande trouvée avec ce numéro.</p>
                    <p class="text-xs mt-1">Vérifiez la saisie et réessayez.</p>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
