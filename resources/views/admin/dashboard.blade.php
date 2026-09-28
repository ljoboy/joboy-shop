@extends('layouts.admin')

@section('title', 'Gestion des Commandes - Backoffice')

@section('content')
<div class="space-y-8">
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Commandes Totales</p>
                <p class="text-3xl font-black text-gray-900 mt-1">{{ $totalOrders }}</p>
            </div>
            <div class="p-3 bg-emerald-100 text-emerald-600 rounded-xl text-xl">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Chiffre d'affaires</p>
                <p class="text-3xl font-black text-emerald-600 mt-1">{{ number_format($totalRevenue, 2, ',', ' ') }} €</p>
            </div>
            <div class="p-3 bg-emerald-100 text-emerald-600 rounded-xl text-xl">
                <i class="fa-solid fa-coins"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Produits Actifs</p>
                <p class="text-3xl font-black text-gray-900 mt-1">{{ $totalProducts }}</p>
            </div>
            <div class="p-3 bg-blue-100 text-blue-600 rounded-xl text-xl">
                <i class="fa-solid fa-box"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Administrateurs</p>
                <p class="text-3xl font-black text-gray-900 mt-1">{{ $totalAdmins }}</p>
            </div>
            <div class="p-3 bg-purple-100 text-purple-600 rounded-xl text-xl">
                <i class="fa-solid fa-user-shield"></i>
            </div>
        </div>
    </div>

    <!-- Filters and Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-gray-900">Liste des Commandes</h2>

            <!-- Status Filters -->
            <div class="flex items-center space-x-2 overflow-x-auto w-full sm:w-auto pb-2 sm:pb-0">
                <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ !$statusFilter ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Tous ({{ $totalOrders }})
                </a>
                @foreach($statuses as $st)
                    <a href="{{ route('admin.dashboard', ['status' => $st->slug]) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap {{ $statusFilter === $st->slug ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $st->name }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase border-b border-gray-200">
                    <tr>
                        <th class="p-4">N° Commande</th>
                        <th class="p-4">Client</th>
                        <th class="p-4">Articles</th>
                        <th class="p-4">Montant Total</th>
                        <th class="p-4">Statut Actuel</th>
                        <th class="p-4">Action Statut (Alerte WhatsApp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="p-4">
                                <span class="font-black text-gray-900 block">{{ $order->order_number }}</span>
                                <span class="text-xs text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </td>

                            <td class="p-4">
                                <span class="font-bold text-gray-800 block">{{ $order->customer_name }}</span>
                                <span class="text-xs text-emerald-600 font-semibold block"><i class="fa-brands fa-whatsapp"></i> {{ $order->customer_phone }}</span>
                                @if($order->customer_address)
                                    <span class="text-xs text-gray-500 truncate block max-w-xs">{{ $order->customer_address }}</span>
                                @endif
                            </td>

                            <td class="p-4">
                                <ul class="text-xs text-gray-600 space-y-1">
                                    @foreach($order->items as $item)
                                        <li>• {{ $item->product_name }} <span class="font-bold">x{{ $item->quantity }}</span></li>
                                    @endforeach
                                </ul>
                            </td>

                            <td class="p-4">
                                <span class="font-extrabold text-emerald-600 text-base">{{ number_format($order->total_amount, 2, ',', ' ') }} €</span>
                            </td>

                            <td class="p-4">
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
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                    {{ $order->status ? $order->status->name : 'Non défini' }}
                                </span>
                            </td>

                            <td class="p-4">
                                <form method="POST" action="{{ route('admin.orders.update-status', $order->id) }}" class="flex items-center space-x-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="order_status_id" class="text-xs rounded-lg border-gray-300 border p-2 focus:ring-emerald-500 focus:border-emerald-500 font-medium">
                                        @foreach($statuses as $st)
                                            <option value="{{ $st->id }}" {{ $order->order_status_id == $st->id ? 'selected' : '' }}>
                                                {{ $st->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" title="Changer le statut & notifier par WhatsApp" class="bg-emerald-600 hover:bg-emerald-700 text-white p-2 rounded-lg text-xs transition shadow flex items-center space-x-1">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500">
                                Aucune commande trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
