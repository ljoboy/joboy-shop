@extends('layouts.app')

@section('title', 'Connexion - Boutique WhatsApp')

@section('content')
<div class="max-w-md mx-auto my-12 px-4">
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
        <div class="text-center mb-6">
            <i class="fa-solid fa-lock text-emerald-600 text-4xl mb-2"></i>
            <h1 class="text-2xl font-bold text-gray-900">Connexion</h1>
            <p class="text-gray-500 text-xs mt-1">Accédez à votre espace client ou administration</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Adresse Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border-gray-300 border p-3 text-sm focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Mot de passe *</label>
                <input type="password" name="password" required class="w-full rounded-xl border-gray-300 border p-3 text-sm focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-emerald-600 mr-2">
                    Se souvenir de moi
                </label>
            </div>

            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl transition shadow text-sm">
                Se connecter
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-gray-500">
            Vous n'avez pas de compte ? <a href="{{ route('register') }}" class="text-emerald-600 font-bold hover:underline">Créer un compte</a>
        </div>
    </div>
</div>
@endsection
