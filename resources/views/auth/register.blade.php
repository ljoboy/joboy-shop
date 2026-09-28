@extends('layouts.app')

@section('title', 'Inscription - Boutique WhatsApp')

@section('content')
<div class="max-w-md mx-auto my-12 px-4">
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
        <div class="text-center mb-6">
            <i class="fa-solid fa-user-plus text-emerald-600 text-4xl mb-2"></i>
            <h1 class="text-2xl font-bold text-gray-900">Créer un compte</h1>
            <p class="text-gray-500 text-xs mt-1">Rejoignez-nous pour suivre vos commandes facilement</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Nom et Prénom *</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border-gray-300 border p-3 text-sm focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Adresse Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border-gray-300 border p-3 text-sm focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Téléphone WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded-xl border-gray-300 border p-3 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Ex: +33612345678">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Mot de passe *</label>
                <input type="password" name="password" required class="w-full rounded-xl border-gray-300 border p-3 text-sm focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Confirmer le mot de passe *</label>
                <input type="password" name="password_confirmation" required class="w-full rounded-xl border-gray-300 border p-3 text-sm focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl transition shadow text-sm">
                S'inscrire
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-gray-500">
            Déjà inscrit ? <a href="{{ route('login') }}" class="text-emerald-600 font-bold hover:underline">Se connecter</a>
        </div>
    </div>
</div>
@endsection
