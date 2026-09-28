@extends('layouts.admin')

@section('title', 'Gestion des États de Commande - Admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">États de Commande Personnalisés</h1>
            <p class="text-gray-500 text-sm">Configurez les étapes du traitement des commandes et les modèles de messages WhatsApp automatiques.</p>
        </div>
        <a href="{{ route('admin.statuses.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2.5 rounded-xl transition shadow flex items-center space-x-2 text-sm">
            <i class="fa-solid fa-plus"></i>
            <span>Nouveau Statut</span>
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase border-b border-gray-200">
                    <tr>
                        <th class="p-4">Ordre</th>
                        <th class="p-4">Nom du Statut</th>
                        <th class="p-4">Badge</th>
                        <th class="p-4">Modèle WhatsApp Automatique</th>
                        <th class="p-4">Par Défaut</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($statuses as $status)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="p-4 font-bold text-gray-500">
                                #{{ $status->position }}
                            </td>

                            <td class="p-4">
                                <span class="font-bold text-gray-900 block">{{ $status->name }}</span>
                                <span class="text-xs text-gray-400 font-mono">{{ $status->slug }}</span>
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
                                    $badgeClass = $colorClasses[$status->badge_color] ?? $colorClasses['gray'];
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                    {{ $status->name }}
                                </span>
                            </td>

                            <td class="p-4 max-w-md">
                                <p class="text-xs text-gray-600 italic bg-gray-50 p-2 rounded-lg border border-gray-100 line-clamp-2">
                                    {{ $status->whatsapp_template ?? 'Aucun message configuré.' }}
                                </p>
                            </td>

                            <td class="p-4">
                                @if($status->is_default)
                                    <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-2.5 py-1 rounded-full border border-emerald-200">Oui</span>
                                @else
                                    <span class="text-xs text-gray-400">Non</span>
                                @endif
                            </td>

                            <td class="p-4 text-right space-x-2">
                                <a href="{{ route('admin.statuses.edit', $status->id) }}" class="text-blue-600 hover:text-blue-800 font-bold text-xs bg-blue-50 p-2 rounded-lg transition">
                                    <i class="fa-solid fa-pen"></i> Modifier
                                </a>
                                <form method="POST" action="{{ route('admin.statuses.destroy', $status->id) }}" class="inline" onsubmit="return confirm('Supprimer ce statut ?')">
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
                                Aucun statut configuré.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
