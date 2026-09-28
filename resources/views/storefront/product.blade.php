@extends('layouts.app')

@section('title', $product->name . ' - Boutique WhatsApp')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('home') }}" class="inline-flex items-center text-gray-500 hover:text-emerald-600 font-medium mb-6 text-sm transition">
        <i class="fa-solid fa-arrow-left mr-2"></i> Retour au catalogue
    </a>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden grid grid-cols-1 md:grid-cols-2 gap-8 p-6 sm:p-10">
        <!-- Main Image and Gallery -->
        <div class="space-y-4" x-data="{ activeImage: '{{ $product->image }}' }">
            <div class="h-80 sm:h-96 rounded-2xl overflow-hidden bg-gray-50 border border-gray-100 flex items-center justify-center">
                <template x-if="activeImage">
                    <img :src="activeImage" alt="{{ $product->name }}" class="w-full h-full object-cover">
                </template>
                <template x-if="!activeImage">
                    <i class="fa-solid fa-image text-6xl text-gray-300"></i>
                </template>
            </div>

            @if(!empty($product->gallery) && is_array($product->gallery))
                <div class="grid grid-cols-4 gap-3">
                    @if($product->image)
                        <button @click="activeImage = '{{ $product->image }}'" class="h-20 rounded-lg overflow-hidden border-2 border-transparent focus:border-emerald-500">
                            <img src="{{ $product->image }}" class="w-full h-full object-cover">
                        </button>
                    @endif
                    @foreach($product->gallery as $gImg)
                        <button @click="activeImage = '{{ $gImg }}'" class="h-20 rounded-lg overflow-hidden border-2 border-transparent focus:border-emerald-500">
                            <img src="{{ $gImg }}" class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Product Details -->
        <div class="flex flex-col justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 mb-3">{{ $product->name }}</h1>

                <div class="flex items-center space-x-3 mb-6">
                    <span class="text-3xl font-bold text-emerald-600">{{ number_format($product->price, 2, ',', ' ') }} €</span>
                    <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full uppercase">En Stock</span>
                </div>

                <div class="prose text-gray-600 text-sm leading-relaxed mb-8">
                    <h3 class="text-base font-bold text-gray-900 mb-2">Description du produit</h3>
                    <p>{{ $product->description ?? 'Aucune description disponible pour cet article.' }}</p>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <button @click="addToCart({{ json_encode($product) }})" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-3.5 rounded-2xl transition shadow-lg flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-cart-plus text-lg"></i>
                    <span>Ajouter au panier</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
