<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;

class JwtAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Получаем токен из заголовка, URL или cookie
        $token = $request->bearerToken() 
            ?? $request->query('token') 
            ?? $request->cookie('access_token')
            ?? session('access_token');
        
        if (!$token) {
            // Если нет токена - редирект на login
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
        }
        
        try {
            // Декодируем JWT
            $secret = env('JWT_SECRET');
            // Если JWT_SECRET не установлен - используем APP_KEY
            if (!$secret) {
                $secret = config('app.key');
            }
            // Убираем префикс base64: если есть
            if ($secret && str_starts_with($secret, 'base64:')) {
                $secret = base64_decode(substr($secret, 7));
            }
            // Если секрет всё ещё пустой - выбрасываем ошибку
            if (empty($secret)) {
                throw new \RuntimeException('JWT_SECRET is not configured. Please set JWT_SECRET in your .env file.');
            }
            
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

            // Находим пользователя по userId из токена
            $payload = json_decode(json_encode($decoded), true);
            $userId = $payload['userId'] ?? $decoded->userId ?? null;

            if (!$userId) {
                throw new \Exception('Invalid token: userId not found');
            }

            $user = User::find($userId);
            
            if (!$user) {
                throw new \Exception('User not found');
            }
            
            // Устанавливаем auth
            auth()->login($user);
            
            // Сохраняем токен в session для последующих запросов
            session(['access_token' => $token]);
            
            return $next($request);
            
        } catch (\Firebase\JWT\ExpiredException $e) {
            // Токен истёк
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Token expired'], 401);
            }
            return redirect()->route('login')->with('error', 'Сессия истекла');
            
        } catch (\Exception $e) {
            // Другие ошибки
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Invalid token'], 401);
            }
            return redirect()->route('login')->with('error', 'Ошибка аутентификации');
        }
    }
}
