<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

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
     * VK ID OAuth callback (для мобильных)
     */
    public function vkCallback(Request $request)
    {
        $code = $request->get('code');
        $deviceId = $request->get('device_id');

        if (!$code || !$deviceId) {
            return redirect()->route('login')->with('error', 'Ошибка авторизации VK ID');
        }

        // Обмениваем code на токены через VK ID API
        $response = Http::asForm()->post('https://id.vk.com/oauth2/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => config('services.vk.client_id', env('VK_CLIENT_ID')),
            'client_secret' => config('services.vk.client_secret', env('VK_CLIENT_SECRET')),
            'redirect_uri' => config('services.vk.redirect_uri', env('VK_REDIRECT_URI')),
            'device_id' => $deviceId,
        ]);

        if (!$response->successful()) {
            return redirect()->route('login')->with('error', 'Ошибка обмена кода на токены VK ID');
        }

        $tokenData = $response->json();

        if (!isset($tokenData['access_token']) || !isset($tokenData['id_token'])) {
            return redirect()->route('login')->with('error', 'Не получен токен VK ID');
        }

        // Декодируем id_token
        $idTokenParts = explode('.', $tokenData['id_token']);
        if (count($idTokenParts) !== 3) {
            return redirect()->route('login')->with('error', 'Неверный формат токена VK ID');
        }

        $base64Url = $idTokenParts[1];
        $base64 = strtr($base64Url, '-_', '+/');
        $jsonPayload = urldecode(implode('', array_map(function($c) {
            return '%' . str_pad(dechex(ord($c)), 2, '0', STR_PAD_LEFT);
        }, str_split(base64_decode($base64)))));
        $tokenPayload = json_decode($jsonPayload, true);

        if (!$tokenPayload) {
            return redirect()->route('login')->with('error', 'Не удалось декодировать токен VK ID');
        }

        // Находим или создаём пользователя
        $userId = $tokenData['user_id'] ?? $tokenPayload['sub'] ?? null;
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Не получен ID пользователя VK');
        }

        $user = User::where('vk_id', $userId)->first();

        if (!$user) {
            $email = $tokenPayload['email'] ?? $userId . '@vkid.local';
            $username = ($tokenPayload['first_name'] ?? '') . ' ' . ($tokenPayload['last_name'] ?? '');
            $username = trim($username) ?: 'VK User ' . $userId;

            $user = User::create([
                'vk_id' => $userId,
                'email' => $email,
                'username' => $username,
                'role' => 'user',
                'is_active' => true,
                'is_blocked' => false,
            ]);
        }

        // Генерируем JWT токен через backend API
        $backendResponse = Http::post('http://backend:4000/api/auth/vkid', [
            'access_token' => $tokenData['access_token'],
            'user_id' => $userId,
            'email' => $tokenPayload['email'] ?? '',
            'name' => ($tokenPayload['first_name'] ?? '') . ' ' . ($tokenPayload['last_name'] ?? ''),
        ]);

        if (!$backendResponse->successful()) {
            return redirect()->route('login')->with('error', 'Ошибка создания сессии');
        }

        $backendData = $backendResponse->json();
        $accessToken = $backendData['data']['access_token'] ?? null;

        if (!$accessToken) {
            return redirect()->route('login')->with('error', 'Не получен токен доступа');
        }

        // Редирект на dashboard с токеном
        return redirect()->route('dashboard', ['token' => $accessToken]);
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
