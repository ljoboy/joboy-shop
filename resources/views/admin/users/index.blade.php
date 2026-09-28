@extends('layouts.admin')

@section('title', 'Gestion des Administrateurs - Super Admin')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestion des Administrateurs & Utilisateurs</h1>
            <p class="text-gray-500 text-sm">Ajoutez de nouveaux administrateurs ou modifiez les accès à la plateforme.</p>
        </div>
    </div>

    <!-- Create Admin Form -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
            <i class="fa-solid fa-user-plus text-emerald-600 mr-2"></i>
            Ajouter un Administrateur / Utilisateur
        </h2>

        <form method="POST" action="{{ route('admin.users.store') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Nom Complet *</label>
                <input type="text" name="name" required class="w-full rounded-xl border-gray-300 border p-2.5 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Ex: Marie Curie">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Adresse Email *</label>
                <input type="email" name="email" required class="w-full rounded-xl border-gray-300 border p-2.5 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Ex: marie@example.com">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Téléphone</label>
                <input type="text" name="phone" class="w-full rounded-xl border-gray-300 border p-2.5 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Ex: +33600000000">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Rôle / Accès *</label>
                <select name="role" required class="w-full rounded-xl border-gray-300 border p-2.5 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    <option value="admin">Administrateur Boutique</option>
                    <option value="super_admin">Super Administrateur</option>
                    <option value="customer">Client</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Mot de passe *</label>
                <input type="password" name="password" required class="w-full rounded-xl border-gray-300 border p-2.5 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="********">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl transition shadow text-sm flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Créer l'utilisateur</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">Liste des Comptes</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase border-b border-gray-200">
                    <tr>
                        <th class="p-4">Utilisateur</th>
                        <th class="p-4">Email / Phone</th>
                        <th class="p-4">Rôle</th>
                        <th class="p-4">Inscrit Le</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="p-4 font-bold text-gray-900">
                                {{ $user->name }}
                                @if($user->id === auth()->id())
                                    <span class="text-xs bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full ml-1">(Vous)</span>
                                @endif
                            </td>

                            <td class="p-4">
                                <span class="block text-gray-800 font-medium">{{ $user->email }}</span>
                                <span class="block text-xs text-gray-500">{{ $user->phone ?? 'Aucun téléphone' }}</span>
                            </td>

                            <td class="p-4">
                                @if($user->role === 'super_admin')
                                    <span class="bg-purple-100 text-purple-800 border border-purple-300 text-xs font-extrabold px-2.5 py-1 rounded-full">Super Admin</span>
                                @elseif($user->role === 'admin')
                                    <span class="bg-blue-100 text-blue-800 border border-blue-300 text-xs font-extrabold px-2.5 py-1 rounded-full">Admin</span>
                                @else
                                    <span class="bg-gray-100 text-gray-700 border border-gray-300 text-xs font-bold px-2.5 py-1 rounded-full">Client</span>
                                @endif
                            </td>

                            <td class="p-4 text-xs text-gray-500">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>

                            <td class="p-4 text-right">
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-bold text-xs bg-red-50 p-2 rounded-lg transition">
                                            <i class="fa-solid fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500">
                                Aucun utilisateur inscrit.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
