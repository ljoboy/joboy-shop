@extends('layouts.app')

@section('title', 'Mon Compte - Mes Commandes')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sm:p-8 mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Bonjour, {{ auth()->user()->name }} !</h1>
            <p class="text-gray-500 text-sm mt-1">Retrouvez l'historique complet et le suivi de toutes vos commandes.</p>
        </div>
        <div class="mt-4 sm:mt-0 text-sm bg-emerald-50 text-emerald-800 font-semibold px-4 py-2 rounded-xl border border-emerald-200">
            <i class="fa-solid fa-user-check mr-1"></i> Client vérifié : {{ auth()->user()->email }}
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900 flex items-center">
                <i class="fa-solid fa-box-archive text-emerald-600 mr-2"></i>
                Mes Commandes ({{ $orders->count() }})
            </h2>
            <a href="{{ route('track.order') }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700">
                <i class="fa-solid fa-magnifying-glass mr-1"></i> Suivi rapide par N°
            </a>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($orders as $order)
                <div class="p-6 hover:bg-gray-50/50 transition">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Commande</span>
                            <h3 class="text-lg font-black text-gray-900">{{ $order->order_number }}</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Du {{ $order->created_at->format('d/m/Y à H:i') }}</p>
                        </div>

                        <div class="flex items-center space-x-4">
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
                            <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                <i class="fa-solid fa-circle text-[8px] mr-2"></i>
                                {{ $order->status ? $order->status->name : 'Non défini' }}
                            </span>

                            <div class="text-right">
                                <span class="text-xs text-gray-400 uppercase block font-bold">Total</span>
                                <span class="text-lg font-bold text-emerald-600">{{ number_format($order->total_amount, 2, ',', ' ') }} €</span>
                            </div>
                        </div>
                    </div>

                    <!-- Order items summary -->
                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 text-sm">
                        <p class="font-semibold text-gray-700 text-xs uppercase mb-2">Articles commandés :</p>
                        <div class="space-y-1">
                            @foreach($order->items as $item)
                                <div class="flex justify-between text-xs text-gray-600">
                                    <span>• {{ $item->product_name }} <strong class="text-gray-800">x{{ $item->quantity }}</strong></span>
                                    <span class="font-bold text-gray-800">{{ number_format($item->subtotal, 2, ',', ' ') }} €</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-gray-500">
                    <i class="fa-solid fa-receipt text-gray-300 text-5xl mb-3"></i>
                    <p class="font-medium">Vous n'avez pas encore passé de commande.</p>
                    <a href="{{ route('home') }}" class="mt-4 inline-block bg-emerald-600 text-white text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-emerald-700 transition">
                        Découvrir la boutique
                    </a>
                </div>
            @forelse
        </div>
    </div>
</div>
@endsection
