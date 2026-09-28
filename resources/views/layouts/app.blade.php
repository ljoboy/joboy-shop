<!DOCTYPE html>
<html lang="fr" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Boutique WhatsApp Pro')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full flex flex-col text-gray-800 antialiased" x-data="cartApp()">

    <!-- Header Navigation -->
    <header class="bg-white shadow-sm sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center space-x-2 text-emerald-600 font-bold text-xl">
                <i class="fa-brands fa-whatsapp text-2xl"></i>
                <span>{{ \App\Models\Setting::get('store_name', 'Boutique WhatsApp') }}</span>
            </a>

            <nav class="flex items-center space-x-4">
                <a href="{{ route('home') }}" class="text-gray-600 hover:text-emerald-600 font-medium px-3 py-2 rounded-md transition">Catalogue</a>
                <a href="{{ route('track.order') }}" class="text-gray-600 hover:text-emerald-600 font-medium px-3 py-2 rounded-md transition">Suivre ma commande</a>

                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="bg-emerald-100 text-emerald-700 hover:bg-emerald-200 font-semibold px-3 py-2 rounded-lg text-sm transition">
                            <i class="fa-solid fa-gauge mr-1"></i> Admin Panel
                        </a>
                    @else
                        <a href="{{ route('customer.dashboard') }}" class="text-gray-600 hover:text-emerald-600 font-medium px-3 py-2 rounded-md transition">
                            Mon compte
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-red-600 px-3 py-2 text-sm transition">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-emerald-600 font-medium px-3 py-2 text-sm transition">Connexion</a>
                    <a href="{{ route('register') }}" class="bg-emerald-600 text-white hover:bg-emerald-700 font-medium px-4 py-2 rounded-lg text-sm transition shadow-sm">Créer un compte</a>
                @endauth

                <!-- Cart Button -->
                <button @click="cartOpen = true" class="relative bg-emerald-600 text-white p-2.5 rounded-full hover:bg-emerald-700 transition shadow">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span x-show="cartCount > 0" x-text="cartCount" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"></span>
                </button>
            </nav>
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200 flex items-center">
                <i class="fa-solid fa-circle-check text-green-600 text-lg mr-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200 flex items-center">
                <i class="fa-solid fa-circle-exclamation text-red-600 text-lg mr-3"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Cart Slide-Over / Modal -->
    <div x-cloak x-show="cartOpen" class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <div x-show="cartOpen" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="cartOpen = false"></div>

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div x-show="cartOpen" x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="pointer-events-auto w-screen max-w-md">
                        <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                            <div class="flex-1 overflow-y-auto px-4 py-6 sm:px-6">
                                <div class="flex items-start justify-between">
                                    <h2 class="text-lg font-bold text-gray-900" id="slide-over-title">Mon Panier</h2>
                                    <button @click="cartOpen = false" class="text-gray-400 hover:text-gray-500">
                                        <i class="fa-solid fa-xmark text-xl"></i>
                                    </button>
                                </div>

                                <div class="mt-8">
                                    <template x-if="cart.length === 0">
                                        <div class="text-center py-12">
                                            <i class="fa-solid fa-basket-shopping text-gray-300 text-5xl mb-3"></i>
                                            <p class="text-gray-500">Votre panier est vide.</p>
                                        </div>
                                    </template>

                                    <div class="flow-root">
                                        <ul role="list" class="-my-6 divide-y divide-gray-200">
                                            <template x-for="(item, index) in cart" :key="item.id">
                                                <li class="flex py-6">
                                                    <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
                                                        <img :src="item.image || 'https://via.placeholder.com/150'" :alt="item.name" class="h-full w-full object-cover object-center">
                                                    </div>

                                                    <div class="ml-4 flex flex-1 flex-col">
                                                        <div>
                                                            <div class="flex justify-between text-base font-medium text-gray-900">
                                                                <h3 x-text="item.name"></h3>
                                                                <p class="ml-4 font-bold text-emerald-600" x-text="formatPrice(item.price * item.quantity)"></p>
                                                            </div>
                                                        </div>
                                                        <div class="flex flex-1 items-end justify-between text-sm">
                                                            <div class="flex items-center space-x-2 border rounded-lg px-2 py-1">
                                                                <button @click="decreaseQty(index)" class="text-gray-500 hover:text-emerald-600 font-bold px-1">-</button>
                                                                <span x-text="item.quantity" class="font-medium text-gray-800"></span>
                                                                <button @click="increaseQty(index)" class="text-gray-500 hover:text-emerald-600 font-bold px-1">+</button>
                                                            </div>

                                                            <button @click="removeFromCart(index)" class="font-medium text-red-600 hover:text-red-500 text-xs">
                                                                <i class="fa-solid fa-trash mr-1"></i> Supprimer
                                                            </button>
                                                        </div>
                                                    </div>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div x-show="cart.length > 0" class="border-t border-gray-200 px-4 py-6 sm:px-6">
                                <div class="flex justify-between text-base font-bold text-gray-900 mb-4">
                                    <p>Total</p>
                                    <p class="text-emerald-600" x-text="formatPrice(cartTotal)"></p>
                                </div>
                                <p class="mt-0.5 text-xs text-gray-500 mb-4">La commande sera transmise directement via WhatsApp.</p>

                                <button @click="checkoutOpen = true; cartOpen = false" class="w-full rounded-lg bg-emerald-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-emerald-700 transition flex items-center justify-center space-x-2">
                                    <i class="fa-brands fa-whatsapp text-xl"></i>
                                    <span>Commander via WhatsApp</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div x-cloak x-show="checkoutOpen" class="relative z-50">
        <div x-show="checkoutOpen" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="checkoutOpen = false"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="checkoutOpen" class="relative transform overflow-hidden rounded-2xl bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div class="flex items-center justify-between pb-4 border-b">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center">
                            <i class="fa-brands fa-whatsapp text-emerald-600 text-2xl mr-2"></i>
                            Finaliser ma commande
                        </h3>
                        <button @click="checkoutOpen = false" class="text-gray-400 hover:text-gray-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submitCheckout" class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom et Prénom *</label>
                            <input type="text" x-model="checkoutForm.customer_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" placeholder="Ex: Jean Dupont">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Numéro WhatsApp *</label>
                            <input type="text" x-model="checkoutForm.customer_phone" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" placeholder="Ex: +33612345678">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Adresse Email (facultatif)</label>
                            <input type="email" x-model="checkoutForm.customer_email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" placeholder="Ex: jean@example.com">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Adresse de Livraison (facultatif)</label>
                            <textarea x-model="checkoutForm.customer_address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" placeholder="Ex: 12 Rue de la Paix, Paris"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Instructions / Notes (facultatif)</label>
                            <textarea x-model="checkoutForm.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" placeholder="Ex: Prévenir avant la livraison"></textarea>
                        </div>

                        <div x-show="errorMessage" class="p-3 text-sm text-red-600 bg-red-50 rounded-lg" x-text="errorMessage"></div>

                        <div class="mt-5 sm:mt-6 flex space-x-3">
                            <button type="button" @click="checkoutOpen = false" class="inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                Annuler
                            </button>
                            <button type="submit" :disabled="loading" class="inline-flex w-full justify-center rounded-lg bg-emerald-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-emerald-700 items-center space-x-2">
                                <span x-show="!loading" class="flex items-center">
                                    <i class="fa-brands fa-whatsapp mr-2 text-lg"></i> Envoyer sur WhatsApp
                                </span>
                                <span x-show="loading">Traitement...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-8 mt-12 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm">
            <p>&copy; {{ date('Y') }} {{ \App\Models\Setting::get('store_name', 'Boutique WhatsApp Pro') }}. Tous droits réservés.</p>
            <p class="mt-2 text-gray-500">Vente directe & suivi personnalisé via WhatsApp.</p>
        </div>
    </footer>

    <script>
        function cartApp() {
            return {
                cartOpen: false,
                checkoutOpen: false,
                loading: false,
                errorMessage: '',
                cart: JSON.parse(localStorage.getItem('wa_cart') || '[]'),
                checkoutForm: {
                    customer_name: '{{ auth()->check() ? auth()->user()->name : '' }}',
                    customer_phone: '{{ auth()->check() ? auth()->user()->phone : '' }}',
                    customer_email: '{{ auth()->check() ? auth()->user()->email : '' }}',
                    customer_address: '',
                    notes: ''
                },

                get cartCount() {
                    return this.cart.reduce((sum, item) => sum + item.quantity, 0);
                },

                get cartTotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },

                addToCart(product) {
                    let existing = this.cart.find(i => i.id === product.id);
                    if (existing) {
                        existing.quantity++;
                    } else {
                        this.cart.push({
                            id: product.id,
                            name: product.name,
                            price: parseFloat(product.price),
                            image: product.image,
                            quantity: 1
                        });
                    }
                    this.saveCart();
                    this.cartOpen = true;
                },

                increaseQty(index) {
                    this.cart[index].quantity++;
                    this.saveCart();
                },

                decreaseQty(index) {
                    if (this.cart[index].quantity > 1) {
                        this.cart[index].quantity--;
                    } else {
                        this.cart.splice(index, 1);
                    }
                    this.saveCart();
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                    this.saveCart();
                },

                saveCart() {
                    localStorage.setItem('wa_cart', JSON.stringify(this.cart));
                },

                formatPrice(price) {
                    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(price);
                },

                async submitCheckout() {
                    if (this.cart.length === 0) return;
                    this.loading = true;
                    this.errorMessage = '';

                    try {
                        let response = await fetch('{{ route("checkout") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                ...this.checkoutForm,
                                cart: this.cart
                            })
                        });

                        let data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.error || 'Une erreur est survenue.');
                        }

                        // Clear cart
                        this.cart = [];
                        this.saveCart();
                        this.checkoutOpen = false;

                        // Redirect to WhatsApp URL
                        window.location.href = data.redirect_url;
                    } catch (e) {
                        this.errorMessage = e.message;
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
