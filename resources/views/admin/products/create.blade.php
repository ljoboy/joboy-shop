@extends('layouts.admin')

@section('title', 'Ajouter un Produit - Admin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Nouveau Produit</h1>
        <a href="{{ route('admin.products.index') }}" class="text-gray-500 hover:text-gray-700 text-sm font-semibold">
            <i class="fa-solid fa-arrow-left mr-1"></i> Retour
        </a>
    </div>

    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-gray-200">
        <form method="POST" action="{{ route('admin.products.store') }}" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Nom du Produit *</label>
                <input type="text" name="name" required class="w-full rounded-xl border-gray-300 border p-3 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Ex: Smartphone XYZ">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Prix (€) *</label>
                <input type="number" step="0.01" name="price" required class="w-full rounded-xl border-gray-300 border p-3 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Ex: 199.99">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">URL de l'image principale</label>
                <input type="url" name="image" class="w-full rounded-xl border-gray-300 border p-3 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Ex: https://images.unsplash.com/photo-...">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Quantité en Stock (laisser vide pour illimité)</label>
                <input type="number" name="stock" class="w-full rounded-xl border-gray-300 border p-3 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Ex: 50">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="4" class="w-full rounded-xl border-gray-300 border p-3 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Détails du produit..."></textarea>
            </div>

            <div class="flex items-center space-x-2">
                <input type="checkbox" name="is_active" value="1" id="is_active" checked class="h-4 w-4 text-emerald-600 rounded border-gray-300">
                <label for="is_active" class="text-sm font-medium text-gray-700">Rendre ce produit immédiatement visible en vente</label>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-sm font-bold hover:bg-gray-50 transition">
                    Annuler
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition shadow">
                    Créer le produit
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
