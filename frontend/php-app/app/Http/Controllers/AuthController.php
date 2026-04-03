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
        // Если пользователь уже авторизован - редирект на dashboard с новым токеном
        if (Auth::check()) {
            $user = Auth::user();
            $token = $this->generateJWT($user);
            return redirect()->route('dashboard', ['token' => $token]);
        }
        
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

        // Генерируем JWT токен для dashboard
        $token = $this->generateJWT($user);

        // Редирект на dashboard с токеном
        return redirect()->route('dashboard', ['token' => $token]);
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

    /**
     * Генерация JWT токена
     */
    private function generateJWT(User $user): string
    {
        $secret = config('jwt.secret', env('JWT_SECRET', env('APP_KEY')));
        if (!$secret) {
            $secret = 'default_secret_change_in_production';
        }

        // Создаём JWT токен вручную
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = json_encode([
            'iss' => url('/'),
            'iat' => time(),
            'exp' => time() + (60 * 60), // 1 час
            'userId' => $user->id,
            'email' => $user->email,
            'vkId' => $user->vk_id ? (string)$user->vk_id : null,
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
}
