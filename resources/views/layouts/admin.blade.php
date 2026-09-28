<!DOCTYPE html>
<html lang="fr" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration Boutique')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full font-sans antialiased text-gray-800">
    <div class="min-h-full flex flex-col">
        <!-- Top Navbar -->
        <header class="bg-gray-900 text-white sticky top-0 z-30 shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2 text-emerald-400 font-extrabold text-xl">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Admin Panel</span>
                    </a>
                    <span class="text-xs bg-gray-800 text-emerald-400 px-2.5 py-1 rounded-full font-semibold border border-emerald-500/30">
                        {{ auth()->user()->role === 'super_admin' ? 'Super Admin' : 'Admin' }}
                    </span>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="{{ route('home') }}" target="_blank" class="text-gray-300 hover:text-white text-sm font-medium flex items-center space-x-1">
                        <i class="fa-solid fa-store"></i>
                        <span class="hidden sm:inline">Voir le site</span>
                    </a>

                    <div class="h-4 w-px bg-gray-700"></div>

                    <div class="flex items-center space-x-2 text-sm">
                        <span class="font-medium text-gray-200">{{ auth()->user()->name }}</span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>

            <!-- Admin Submenu Navigation -->
            <div class="bg-gray-800 border-t border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex space-x-2 sm:space-x-6 overflow-x-auto py-2 text-sm font-medium">
                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                        <i class="fa-solid fa-cart-flatbed mr-1.5"></i> Commandes
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.products.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                        <i class="fa-solid fa-box-open mr-1.5"></i> Produits
                    </a>
                    <a href="{{ route('admin.statuses.index') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.statuses.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                        <i class="fa-solid fa-tags mr-1.5"></i> États de commande
                    </a>
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.users.index') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.users.*') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                            <i class="fa-solid fa-users-gear mr-1.5"></i> Administrateurs
                        </a>
                    @endif
                    <a href="{{ route('admin.settings') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.settings') ? 'bg-emerald-600 text-white' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">
                        <i class="fa-brands fa-whatsapp mr-1.5"></i> Configuration WhatsApp
                    </a>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            @if(session('success'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-xl bg-green-50 border border-green-200 flex items-center">
                    <i class="fa-solid fa-circle-check text-green-600 text-lg mr-3"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 mb-4 text-sm text-red-800 rounded-xl bg-red-50 border border-red-200 flex items-center">
                    <i class="fa-solid fa-circle-exclamation text-red-600 text-lg mr-3"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif
        </div>

        <!-- Page Content -->
        <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @yield('content')
        </main>
    </div>
</body>
</html>
