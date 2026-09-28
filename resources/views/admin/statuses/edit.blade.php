@extends('layouts.admin')

@section('title', 'Modifier le Statut - Admin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Modifier le Statut</h1>
        <a href="{{ route('admin.statuses.index') }}" class="text-gray-500 hover:text-gray-700 text-sm font-semibold">
            <i class="fa-solid fa-arrow-left mr-1"></i> Retour
        </a>
    </div>

    <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-gray-200">
        <form method="POST" action="{{ route('admin.statuses.update', $status->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Nom du Statut *</label>
                <input type="text" name="name" value="{{ old('name', $status->name) }}" required class="w-full rounded-xl border-gray-300 border p-3 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Couleur du Badge *</label>
                <select name="badge_color" required class="w-full rounded-xl border-gray-300 border p-3 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    <option value="yellow" {{ $status->badge_color == 'yellow' ? 'selected' : '' }}>Jaune (En attente)</option>
                    <option value="blue" {{ $status->badge_color == 'blue' ? 'selected' : '' }}>Bleu (En cours)</option>
                    <option value="indigo" {{ $status->badge_color == 'indigo' ? 'selected' : '' }}>Indigo (Expédition)</option>
                    <option value="green" {{ $status->badge_color == 'green' ? 'selected' : '' }}>Vert (Terminé / Livré)</option>
                    <option value="red" {{ $status->badge_color == 'red' ? 'selected' : '' }}>Rouge (Annulé)</option>
                    <option value="gray" {{ $status->badge_color == 'gray' ? 'selected' : '' }}>Gris (Neutre)</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Position dans la liste</label>
                <input type="number" name="position" value="{{ old('position', $status->position) }}" class="w-full rounded-xl border-gray-300 border p-3 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Modèle de Message WhatsApp Automatique</label>
                <p class="text-xs text-gray-500 mb-2">Variables disponibles: <code class="bg-gray-100 text-emerald-700 px-1 py-0.5 rounded">{customer_name}</code>, <code class="bg-gray-100 text-emerald-700 px-1 py-0.5 rounded">{order_number}</code>, <code class="bg-gray-100 text-emerald-700 px-1 py-0.5 rounded">{status_name}</code>, <code class="bg-gray-100 text-emerald-700 px-1 py-0.5 rounded">{total_amount}</code>, <code class="bg-gray-100 text-emerald-700 px-1 py-0.5 rounded">{created_at}</code></p>
                <textarea name="whatsapp_template" rows="4" class="w-full rounded-xl border-gray-300 border p-3 focus:ring-emerald-500 focus:border-emerald-500 text-sm">{{ old('whatsapp_template', $status->whatsapp_template) }}</textarea>
            </div>

            <div class="flex items-center space-x-2">
                <input type="checkbox" name="is_default" value="1" id="is_default" {{ $status->is_default ? 'checked' : '' }} class="h-4 w-4 text-emerald-600 rounded border-gray-300">
                <label for="is_default" class="text-sm font-medium text-gray-700">Définir comme statut par défaut lors de la création d'une commande</label>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                <a href="{{ route('admin.statuses.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-sm font-bold hover:bg-gray-50 transition">
                    Annuler
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition shadow">
                    Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
