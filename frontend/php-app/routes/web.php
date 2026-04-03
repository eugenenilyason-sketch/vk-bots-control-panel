<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Guest routes (login/register)
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Dashboard - JWT based (no Laravel middleware)
Route::get('/dashboard', function() {
    return view('dashboard-jwt');
})->name('dashboard');

// Authenticated routes (Laravel auth)
Route::middleware('auth')->group(function () {
    // Bots - CRUD
    Route::get('/bots', [BotController::class, 'index'])->name('bots.index');
    Route::get('/bots/create', [BotController::class, 'create'])->name('bots.create');
    Route::post('/bots', [BotController::class, 'store'])->name('bots.store');
    Route::get('/bots/{bot}/edit', [BotController::class, 'edit'])->name('bots.edit');
    Route::put('/bots/{bot}', [BotController::class, 'update'])->name('bots.update');
    Route::delete('/bots/{bot}', [BotController::class, 'destroy'])->name('bots.destroy');
    Route::post('/bots/{bot}/start', [BotController::class, 'start'])->name('bots.start');
    Route::post('/bots/{bot}/stop', [BotController::class, 'stop'])->name('bots.stop');
    
    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    
    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    
    // Admin
    Route::get('/admin', [AdminController::class, 'index'])->name('admin');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/users/{user}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::post('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/admin/users/{user}/block', [AdminController::class, 'blockUser'])->name('admin.users.block');
    Route::post('/admin/users/{user}/unblock', [AdminController::class, 'unblockUser'])->name('admin.users.unblock');
    
    // Admin - Payment Methods
    Route::get('/admin/payment-methods', [AdminController::class, 'paymentMethods'])->name('admin.payment-methods');
    Route::post('/admin/payment-methods/{id}', [AdminController::class, 'updatePaymentMethod'])->name('admin.payment-methods.update');
    Route::post('/admin/payment-methods/{id}/delete', [AdminController::class, 'deletePaymentMethod'])->name('admin.payment-methods.delete');
    Route::get('/admin/payment-methods/create', [AdminController::class, 'createPaymentMethod'])->name('admin.payment-methods.create');
    Route::post('/admin/payment-methods', [AdminController::class, 'storePaymentMethod'])->name('admin.payment-methods.store');
    
    // Admin - System Settings
    Route::get('/admin/settings', [AdminController::class, 'systemSettings'])->name('admin.settings');
    Route::post('/admin/settings', [AdminController::class, 'updateSystemSettings'])->name('admin.settings.update');
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
