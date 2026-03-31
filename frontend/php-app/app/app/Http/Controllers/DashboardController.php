<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $botsCount = Bot::where('user_id', $user->id)->count();
        $activeBots = Bot::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();
        
        $payments = [];
        try {
            $response = Http::withToken($user->api_token ?? '')
                ->get(env('BACKEND_URL') . '/api/payments?limit=5');
            $payments = $response->json()['data'] ?? [];
        } catch (\Exception $e) {
            \Log::error('Failed to load payments: ' . $e->getMessage());
        }
        
        return view('dashboard', compact('botsCount', 'activeBots', 'payments'));
    }
}
