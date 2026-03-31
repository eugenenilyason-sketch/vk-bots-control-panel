<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BotController extends Controller
{
    /**
     * Список ботов пользователя
     */
    public function index()
    {
        $user = auth()->user();
        $bots = Bot::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('bots.index', compact('bots'));
    }
    
    /**
     * Форма создания бота
     */
    public function create()
    {
        return view('bots.create');
    }
    
    /**
     * Создание нового бота
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:3',
        ], [
            'name.required' => 'Введите название бота',
            'name.min' => 'Название должно быть не менее 3 символов',
        ]);
        
        $user = auth()->user();
        
        // Создаём локально (Node.js backend API пока не интегрирован)
        Bot::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'status' => 'inactive',
            'config' => [],
            'messages_sent' => 0,
            'messages_received' => 0,
        ]);
        
        return redirect()->route('bots.index')
            ->with('success', 'Бот успешно создан!');
    }
    
    /**
     * Форма редактирования бота
     */
    public function edit(Bot $bot)
    {
        // Проверка прав
        if ($bot->user_id !== auth()->id()) {
            abort(403, 'Доступ запрещён');
        }
        
        return view('bots.edit', compact('bot'));
    }
    
    /**
     * Обновление бота
     */
    public function update(Request $request, Bot $bot)
    {
        // Проверка прав
        if ($bot->user_id !== auth()->id()) {
            abort(403, 'Доступ запрещён');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:3',
            'status' => 'required|in:active,inactive,blocked',
        ]);
        
        $bot->update($validated);
        
        return redirect()->route('bots.index')
            ->with('success', 'Бот обновлён!');
    }
    
    /**
     * Удаление бота
     */
    public function destroy(Bot $bot)
    {
        // Проверка прав
        if ($bot->user_id !== auth()->id()) {
            abort(403, 'Доступ запрещён');
        }
        
        $bot->delete();
        
        return redirect()->route('bots.index')
            ->with('success', 'Бот удалён!');
    }
    
    /**
     * Запуск бота
     */
    public function start(Bot $bot)
    {
        if ($bot->user_id !== auth()->id()) {
            abort(403);
        }
        
        $bot->update(['status' => 'active']);
        
        return back()->with('success', 'Бот запущен!');
    }
    
    /**
     * Остановка бота
     */
    public function stop(Bot $bot)
    {
        if ($bot->user_id !== auth()->id()) {
            abort(403);
        }
        
        $bot->update(['status' => 'inactive']);
        
        return back()->with('success', 'Бот остановлен!');
    }
}
