@extends('layouts.admin')

@section('title', 'Gestion des Produits - Admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Catalogue des Produits</h1>
            <p class="text-gray-500 text-sm">Gérez les articles mis en vente sur la boutique.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2.5 rounded-xl transition shadow flex items-center space-x-2 text-sm">
            <i class="fa-solid fa-plus"></i>
            <span>Nouveau Produit</span>
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase border-b border-gray-200">
                    <tr>
                        <th class="p-4">Image</th>
                        <th class="p-4">Nom</th>
                        <th class="p-4">Prix</th>
                        <th class="p-4">Stock</th>
                        <th class="p-4">Statut</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="p-4">
                                <div class="h-12 w-12 rounded-lg bg-gray-100 overflow-hidden border border-gray-200 flex items-center justify-center">
                                    @if($product->image)
                                        <img src="{{ $product->image }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fa-solid fa-image text-gray-400"></i>
                                    @endif
                                </div>
                            </td>

                            <td class="p-4">
                                <span class="font-bold text-gray-900 block">{{ $product->name }}</span>
                                <span class="text-xs text-gray-500 line-clamp-1 max-w-xs">{{ $product->description }}</span>
                            </td>

                            <td class="p-4 font-extrabold text-emerald-600">
                                {{ number_format($product->price, 2, ',', ' ') }} €
                            </td>

                            <td class="p-4 text-gray-700 font-semibold">
                                {{ $product->stock !== null ? $product->stock : 'Illimité' }}
                            </td>

                            <td class="p-4">
                                @if($product->is_active)
                                    <span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-1 rounded-full border border-green-200">En Vente</span>
                                @else
                                    <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2.5 py-1 rounded-full border border-gray-200">Inactif</span>
                                @endif
                            </td>

                            <td class="p-4 text-right space-x-2">
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="text-blue-600 hover:text-blue-800 font-bold text-xs bg-blue-50 p-2 rounded-lg transition">
                                    <i class="fa-solid fa-pen"></i> Modifier
                                </a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}" class="inline" onsubmit="return confirm('Supprimer ce produit ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-bold text-xs bg-red-50 p-2 rounded-lg transition">
                                        <i class="fa-solid fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500">
                                Aucun produit dans le catalogue.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection
