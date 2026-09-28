@extends('layouts.admin')

@section('title', 'Configuration WhatsApp - Admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Paramètres Boutique & WhatsApp</h1>
        <p class="text-gray-500 text-sm">Configurez le numéro WhatsApp de réception de commande et le service d'envoi automatique de notifications.</p>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" x-data="{ driver: '{{ $settings['whatsapp_driver'] }}' }" class="space-y-6">
        @csrf

        <!-- Store General Config -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200 space-y-4">
            <h2 class="text-lg font-bold text-gray-900 border-b pb-3 flex items-center">
                <i class="fa-solid fa-store text-emerald-600 mr-2"></i> Informations Boutique
            </h2>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Nom de la Boutique *</label>
                <input type="text" name="store_name" value="{{ old('store_name', $settings['store_name']) }}" required class="w-full rounded-xl border-gray-300 border p-3 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Numéro WhatsApp Récepteur des Commandes *</label>
                <p class="text-xs text-gray-500 mb-1">C'est le numéro de téléphone qui recevra les redirections de commande des clients (format international sans le +).</p>
                <input type="text" name="store_whatsapp_phone" value="{{ old('store_whatsapp_phone', $settings['store_whatsapp_phone']) }}" required class="w-full rounded-xl border-gray-300 border p-3 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Ex: 33612345678">
            </div>
        </div>

        <!-- WhatsApp Notification Driver Config -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200 space-y-6">
            <h2 class="text-lg font-bold text-gray-900 border-b pb-3 flex items-center">
                <i class="fa-brands fa-whatsapp text-emerald-600 text-xl mr-2"></i> Mode de Notification WhatsApp Automatique (Changement d'État)
            </h2>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Choisir le Moteur d'envoi *</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <label :class="{ 'border-emerald-600 bg-emerald-50/50': driver === 'log' }" class="p-4 border-2 rounded-2xl cursor-pointer transition flex flex-col items-center text-center">
                        <input type="radio" name="whatsapp_driver" value="log" x-model="driver" class="sr-only">
                        <i class="fa-solid fa-file-lines text-2xl text-amber-500 mb-2"></i>
                        <span class="font-bold text-sm text-gray-900">Simulation / Logs</span>
                        <span class="text-xs text-gray-500 mt-1">Enregistre les messages dans laravel.log (Aucun coût, idéal tests)</span>
                    </label>

                    <label :class="{ 'border-emerald-600 bg-emerald-50/50': driver === 'whatsapp_cloud_api' }" class="p-4 border-2 rounded-2xl cursor-pointer transition flex flex-col items-center text-center">
                        <input type="radio" name="whatsapp_driver" value="whatsapp_cloud_api" x-model="driver" class="sr-only">
                        <i class="fa-brands fa-meta text-2xl text-blue-600 mb-2"></i>
                        <span class="font-bold text-sm text-gray-900">WhatsApp Cloud API</span>
                        <span class="text-xs text-gray-500 mt-1">API Officielle Meta WhatsApp Business</span>
                    </label>

                    <label :class="{ 'border-emerald-600 bg-emerald-50/50': driver === 'twilio' }" class="p-4 border-2 rounded-2xl cursor-pointer transition flex flex-col items-center text-center">
                        <input type="radio" name="whatsapp_driver" value="twilio" x-model="driver" class="sr-only">
                        <i class="fa-solid fa-comments text-2xl text-red-500 mb-2"></i>
                        <span class="font-bold text-sm text-gray-900">Twilio API</span>
                        <span class="text-xs text-gray-500 mt-1">Passerelle API Twilio WhatsApp</span>
                    </label>

                    <label :class="{ 'border-emerald-600 bg-emerald-50/50': driver === 'none' }" class="p-4 border-2 rounded-2xl cursor-pointer transition flex flex-col items-center text-center">
                        <input type="radio" name="whatsapp_driver" value="none" x-model="driver" class="sr-only">
                        <i class="fa-solid fa-bell-slash text-2xl text-gray-400 mb-2"></i>
                        <span class="font-bold text-sm text-gray-900">Désactivé</span>
                        <span class="text-xs text-gray-500 mt-1">Aucune notification automatique envoyée</span>
                    </label>
                </div>
            </div>

            <!-- WhatsApp Cloud API Credentials -->
            <div x-show="driver === 'whatsapp_cloud_api'" class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-4">
                <h3 class="font-bold text-gray-900 text-sm">Identifiants Meta WhatsApp Cloud API</h3>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Permanent Access Token (Meta)</label>
                    <input type="text" name="whatsapp_cloud_api_token" value="{{ old('whatsapp_cloud_api_token', $settings['whatsapp_cloud_api_token']) }}" class="w-full rounded-xl border-gray-300 border p-2.5 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Phone Number ID</label>
                    <input type="text" name="whatsapp_cloud_api_phone_number_id" value="{{ old('whatsapp_cloud_api_phone_number_id', $settings['whatsapp_cloud_api_phone_number_id']) }}" class="w-full rounded-xl border-gray-300 border p-2.5 text-xs font-mono">
                </div>
            </div>

            <!-- Twilio Credentials -->
            <div x-show="driver === 'twilio'" class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-4">
                <h3 class="font-bold text-gray-900 text-sm">Identifiants Twilio WhatsApp</h3>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Account SID</label>
                    <input type="text" name="twilio_account_sid" value="{{ old('twilio_account_sid', $settings['twilio_account_sid']) }}" class="w-full rounded-xl border-gray-300 border p-2.5 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Auth Token</label>
                    <input type="password" name="twilio_auth_token" value="{{ old('twilio_auth_token', $settings['twilio_auth_token']) }}" class="w-full rounded-xl border-gray-300 border p-2.5 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Twilio WhatsApp From Number (Ex: +14155238886)</label>
                    <input type="text" name="twilio_from_phone" value="{{ old('twilio_from_phone', $settings['twilio_from_phone']) }}" class="w-full rounded-xl border-gray-300 border p-2.5 text-xs font-mono">
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-3 rounded-xl transition shadow flex items-center space-x-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Enregistrer la configuration</span>
            </button>
        </div>
    </form>
</div>
@endsection
