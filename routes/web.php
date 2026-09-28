<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\OrderStatusController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

// --- Storefront Routes ---
Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/produit/{slug}', [StorefrontController::class, 'showProduct'])->name('product.show');
Route::post('/checkout', [StorefrontController::class, 'checkout'])->name('checkout');
Route::get('/suivi-commande', [StorefrontController::class, 'trackOrder'])->name('track.order');

// --- Auth Routes ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// --- Customer Account Routes ---
Route::middleware(['auth'])->prefix('mon-compte')->name('customer.')->group(function () {
    Route::get('/commandes', [CustomerController::class, 'dashboard'])->name('dashboard');
});

// --- Admin Backoffice Routes ---
Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::patch('/orders/{order}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.update-status');

    // Products Management
    Route::resource('products', ProductController::class)->except(['show']);

    // Custom Order Statuses Management
    Route::resource('statuses', OrderStatusController::class)->except(['show']);

    // Admin Users Management (Super Admin)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // WhatsApp Settings
    Route::get('/settings', [AdminController::class, 'editSettings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
});
