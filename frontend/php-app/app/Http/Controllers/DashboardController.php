<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    /**
     * Dashboard - главная страница пользователя
     */
    public function index()
    {
        $user = auth()->user();
        
        // Статистика из БД
        $botsCount = Bot::where('user_id', $user->id)->count();
        $activeBots = Bot::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();
        $inactiveBots = Bot::where('user_id', $user->id)
            ->where('status', 'inactive')
            ->count();
        
        // Платежи
        $paymentsCount = Payment::where('user_id', $user->id)->count();
        $totalSpent = Payment::where('user_id', $user->id)
            ->where('status', 'succeeded')
            ->sum('amount');
        
        // Последние 5 платежей
        $recentPayments = Payment::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Последние боты
        $recentBots = Bot::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        
        return view('dashboard', compact(
            'botsCount',
            'activeBots',
            'inactiveBots',
            'paymentsCount',
            'totalSpent',
            'recentPayments',
            'recentBots'
        ));
    }
}
