<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BotController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $bots = Bot::where('user_id', $user->id)->get();
        
        return view('bots.index', compact('bots'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $user = auth()->user();
        
        $response = Http::withToken($user->api_token ?? '')
            ->post(env('BACKEND_URL') . '/api/bots', [
                'name' => $request->name
            ]);

        if ($response->successful()) {
            return redirect()->route('bots.index')
                ->with('success', 'Бот создан!');
        }

        return back()->withErrors(['name' => 'Ошибка создания бота']);
    }

    public function destroy(Bot $bot)
    {
        $user = auth()->user();
        
        if ($bot->user_id !== $user->id) {
            abort(403);
        }

        Http::withToken($user->api_token ?? '')
            ->delete(env('BACKEND_URL') . '/api/bots/' . $bot->id);

        return redirect()->route('bots.index')
            ->with('success', 'Бот удалён!');
    }
}
