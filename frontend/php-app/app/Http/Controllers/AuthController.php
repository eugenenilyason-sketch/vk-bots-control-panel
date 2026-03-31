<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Показать форму входа
     */
    public function showLogin()
    {
        return view('auth.login');
    }
    
    /**
     * Обработка входа
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        // Поиск пользователя по email
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            return back()->withErrors([
                'email' => 'Пользователь не найден',
            ])->withInput();
        }
        
        // Проверка пароля (используем password_verify напрямую для совместимости)
        if (!password_verify($credentials['password'], $user->password_hash)) {
            return back()->withErrors([
                'password' => 'Неверный пароль',
            ])->withInput();
        }
        
        // Проверка блокировки
        if ($user->is_blocked) {
            return back()->withErrors([
                'email' => 'Аккаунт заблокирован',
            ])->withInput();
        }
        
        // Проверка активности
        if (!$user->is_active) {
            return back()->withErrors([
                'email' => 'Аккаунт не активен',
            ])->withInput();
        }
        
        // Вход в систему
        Auth::login($user);
        
        return redirect()->intended('/dashboard');
    }
    
    /**
     * Показать форму регистрации
     */
    public function showRegister()
    {
        // Проверка: разрешена ли регистрация
        if (!Setting::isRegistrationEnabled()) {
            return view('auth.register', [
                'registrationDisabled' => true,
                'message' => 'Регистрация новых пользователей временно закрыта'
            ]);
        }
        
        return view('auth.register');
    }
    
    /**
     * Обработка регистрации
     */
    public function register(Request $request)
    {
        // Проверка: разрешена ли регистрация
        if (!Setting::isRegistrationEnabled()) {
            return back()->withErrors([
                'email' => 'Регистрация новых пользователей временно закрыта'
            ]);
        }
        
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'username' => 'nullable|string|max:255',
        ]);
        
        $user = User::create([
            'email' => $validated['email'],
            'username' => $validated['username'] ?? null,
            'password_hash' => Hash::make($validated['password']),
            'role' => 'user',
            'is_active' => true,
            'is_blocked' => false,
        ]);
        
        Auth::login($user);
        
        return redirect()->intended('/dashboard');
    }
    
    /**
     * Выход
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}
