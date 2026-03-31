<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/bots', [BotController::class, 'index'])->name('bots.index');
    Route::post('/bots', [BotController::class, 'store'])->name('bots.store');
    Route::delete('/bots/{bot}', [BotController::class, 'destroy'])->name('bots.destroy');
    
    Route::get('/payments', function() {
        return view('payments.index');
    })->name('payments');
    
    Route::get('/settings', function() {
        return view('settings');
    })->name('settings');
    
    Route::get('/admin', function() {
        return view('admin');
    })->middleware('admin')->name('admin');
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
