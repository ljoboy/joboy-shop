@extends('layouts.app')

@section('title', 'Catalogue Produits - Boutique WhatsApp')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-8 sm:p-12 text-white shadow-xl mb-12 flex flex-col md:flex-row items-center justify-between">
        <div class="max-w-xl">
            <span class="inline-block bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider mb-3">Commande Directe WhatsApp</span>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight mb-4">Achetez vos articles en toute simplicité</h1>
            <p class="text-emerald-100 text-lg mb-6">Parcourez notre catalogue, sélectionnez vos articles et validez directement votre commande via WhatsApp avec un vendeur dédié.</p>
            <a href="#catalogue" class="inline-flex items-center bg-white text-emerald-700 font-bold px-6 py-3 rounded-xl shadow-lg hover:bg-emerald-50 transition">
                <i class="fa-solid fa-store mr-2"></i> Voir le catalogue
            </a>
        </div>
        <div class="mt-8 md:mt-0">
            <i class="fa-brands fa-whatsapp text-9xl text-white/20"></i>
        </div>
    </div>

    <!-- Catalogue Header -->
    <div id="catalogue" class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 pb-4 border-b border-gray-200">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Nos Articles à la Vente</h2>
            <p class="text-gray-500 text-sm">Découvrez nos produits disponibles dès maintenant</p>
        </div>
        <div class="mt-4 sm:mt-0 text-gray-500 text-sm">
            <span class="font-semibold text-gray-800">{{ $products->count() }}</span> article(s) disponible(s)
        </div>
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($products as $product)
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-md border border-gray-100 transition-all duration-300 flex flex-col overflow-hidden group">
                <!-- Image container -->
                <div class="relative h-56 bg-gray-100 overflow-hidden">
                    @if($product->image)
                        <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <i class="fa-solid fa-image text-4xl"></i>
                        </div>
                    @endif
                    <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-md text-emerald-700 font-bold px-3 py-1 rounded-full text-sm shadow">
                        {{ number_format($product->price, 2, ',', ' ') }} €
                    </div>
                </div>

                <!-- Product details -->
                <div class="p-5 flex-grow flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-lg text-gray-900 group-hover:text-emerald-600 transition mb-2">
                            <a href="{{ route('product.show', $product->slug) }}">{{ $product->name }}</a>
                        </h3>
                        <p class="text-gray-600 text-sm line-clamp-2 mb-4">{{ $product->description }}</p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex items-center justify-between space-x-2">
                        <a href="{{ route('product.show', $product->slug) }}" class="text-gray-600 hover:text-emerald-600 text-sm font-semibold transition">
                            Détails <i class="fa-solid fa-arrow-right text-xs ml-1"></i>
                        </a>

                        <button @click="addToCart({{ json_encode($product) }})" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl font-medium text-sm transition shadow flex items-center space-x-1">
                            <i class="fa-solid fa-plus text-xs"></i>
                            <span>Ajouter</span>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center">
                <i class="fa-solid fa-box-open text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-xl font-bold text-gray-700">Aucun produit disponible</h3>
                <p class="text-gray-500 mt-1">Revenez plus tard pour découvrir nos nouveaux articles.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
