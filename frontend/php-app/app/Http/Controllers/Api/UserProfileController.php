<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    /**
     * Get current user profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'error' => [
                    'message' => 'User not found',
                ],
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role,
                'balance' => $user->balance ?? 0,
                'vk_id' => $user->vk_id,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    /**
     * VK ID authentication (from frontend SDK)
     */
    public function vkidAuth(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'access_token' => 'required|string',
            'user_id' => 'required|string',
            'email' => 'nullable|email',
            'name' => 'nullable|string',
            'avatar' => 'nullable|string',
        ]);

        // Find or create user
        $user = User::where('vk_id', $validated['user_id'])->first();

        if (!$user) {
            $email = $validated['email'] ?? $validated['user_id'] . '@vkid.local';
            $username = trim($validated['name']) ?: 'VK User ' . $validated['user_id'];

            $user = User::create([
                'vk_id' => $validated['user_id'],
                'email' => $email,
                'username' => $username,
                'role' => 'user',
                'is_active' => true,
                'is_blocked' => false,
                'balance' => 0,
            ]);
        }

        // Generate JWT token
        $token = $this->generateJWT($user);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'username' => $user->username,
                    'role' => $user->role,
                    'balance' => $user->balance ?? 0,
                ],
                'access_token' => $token,
            ],
        ]);
    }

    /**
     * Generate JWT token
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
            'exp' => time() + (60 * 60), // 1 hour
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
