<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    /**
     * Страница настроек
     */
    public function index()
    {
        return view('settings');
    }
    
    /**
     * Обновление профиля
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'username' => 'required|string|max:255|min:3',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ], [
            'username.min' => 'Имя должно быть не менее 3 символов',
            'email.unique' => 'Этот email уже занят',
        ]);
        
        $user->update($validated);
        
        return back()->with('success', 'Профиль обновлён!');
    }
    
    /**
     * Смена пароля
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ], [
            'password.min' => 'Пароль должен быть не менее 6 символов',
            'password.confirmed' => 'Пароли не совпадают',
        ]);
        
        // Проверка текущего пароля
        if (!Hash::check($validated['current_password'], $user->password_hash)) {
            return back()->withErrors(['current_password' => 'Неверный текущий пароль']);
        }
        
        // Обновление пароля
        $user->update([
            'password_hash' => Hash::make($validated['password']),
        ]);
        
        return back()->with('success', 'Пароль изменён!');
    }
}
