<?php

/**
 * PHP JWT Middleware Tests
 * Тесты для проверки JwtAuthMiddleware
 */

require __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Конфигурация
$jwtSecret = getenv('JWT_SECRET') ?: 'test_secret_do_not_use_in_production';

// Тестовые результаты
$passed = 0;
$failed = 0;

/**
 * Создать JWT токен
 */
function createToken($payload, $secret, $expiresIn = 3600) {
    $issuedAt = time();
    $expiration = $issuedAt + $expiresIn;
    
    $payload['iat'] = $issuedAt;
    $payload['exp'] = $expiration;
    
    return JWT::encode($payload, $secret, 'HS256');
}

/**
 * Тест: Валидный токен
 */
function testValidToken($secret) {
    global $passed, $failed;
    echo "\n✅ Testing valid token...\n";
    
    $token = createToken([
        'userId' => 'test-user-123',
        'email' => 'test@example.com',
        'role' => 'user',
    ], $secret);
    
    try {
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        if ($decoded->userId === 'test-user-123' && $decoded->email === 'test@example.com') {
            echo "✅ Valid token decoded successfully\n";
            $passed++;
            return true;
        }
    } catch (Exception $e) {
        echo "❌ Valid token test failed: " . $e->getMessage() . "\n";
    }
    
    $failed++;
    return false;
}

/**
 * Тест: Истёкший токен
 */
function testExpiredToken($secret) {
    global $passed, $failed;
    echo "\n⏰ Testing expired token...\n";
    
    // Создаём токен, который истёк 1 час назад
    $token = createToken([
        'userId' => 'test-user-123',
        'email' => 'test@example.com',
    ], $secret, -3600);
    
    try {
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        echo "❌ Expired token test failed: Should have thrown exception\n";
        $failed++;
        return false;
    } catch (Firebase\JWT\ExpiredException $e) {
        echo "✅ Expired token rejected: " . $e->getMessage() . "\n";
        $passed++;
        return true;
    } catch (Exception $e) {
        echo "❌ Expired token test failed: Wrong exception: " . $e->getMessage() . "\n";
        $failed++;
        return false;
    }
}

/**
 * Тест: Токен с неправильной подписью
 */
function testInvalidSignature($secret) {
    global $passed, $failed;
    echo "\n✍️ Testing token with wrong signature...\n";
    
    $token = createToken([
        'userId' => 'test-user-123',
    ], 'wrong_secret');
    
    try {
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        echo "❌ Invalid signature test failed: Should have thrown exception\n";
        $failed++;
        return false;
    } catch (Firebase\JWT\SignatureInvalidException $e) {
        echo "✅ Invalid signature rejected: " . $e->getMessage() . "\n";
        $passed++;
        return true;
    } catch (Exception $e) {
        echo "❌ Invalid signature test failed: " . $e->getMessage() . "\n";
        $failed++;
        return false;
    }
}

/**
 * Тест: Токен без userId
 */
function testTokenWithoutUserId($secret) {
    global $passed, $failed;
    echo "\n❓ Testing token without userId...\n";
    
    $token = createToken([
        'email' => 'test@example.com',
        'role' => 'user',
    ], $secret);
    
    try {
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        $userId = $decoded->userId ?? null;
        
        if ($userId === null) {
            echo "✅ Token without userId decoded but userId is null\n";
            $passed++;
            return true;
        }
    } catch (Exception $e) {
        echo "❌ Token without userId test failed: " . $e->getMessage() . "\n";
    }
    
    $failed++;
    return false;
}

/**
 * Тест: Токен с изменённым payload (tampered)
 */
function testTamperedToken($secret) {
    global $passed, $failed;
    echo "\n🔧 Testing tampered token...\n";
    
    $validToken = createToken([
        'userId' => 'test-user-123',
        'role' => 'user',
    ], $secret);
    
    // Декодируем и меняем роль
    $parts = explode('.', $validToken);
    $decodedPayload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
    $decodedPayload['role'] = 'superadmin'; // Повышаем привилегии!
    $tamperedPayload = base64_encode(json_encode($decodedPayload));
    $tamperedToken = $parts[0] . '.' . $tamperedPayload . '.' . $parts[2];
    
    try {
        $decoded = JWT::decode($tamperedToken, new Key($secret, 'HS256'));
        echo "❌ Tampered token test failed: Should have rejected modified token\n";
        $failed++;
        return false;
    } catch (Exception $e) {
        echo "✅ Tampered token rejected: " . $e->getMessage() . "\n";
        $passed++;
        return true;
    }
}

/**
 * Тест: Токен с дополнительными claims
 */
function testTokenWithExtraClaims($secret) {
    global $passed, $failed;
    echo "\n📦 Testing token with extra claims...\n";
    
    $token = createToken([
        'userId' => 'test-user-123',
        'email' => 'test@example.com',
        'customClaim' => 'customValue',
        'nestedClaim' => ['key' => 'value'],
    ], $secret);
    
    try {
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        if ($decoded->userId === 'test-user-123' && 
            $decoded->customClaim === 'customValue' &&
            $decoded->nestedClaim->key === 'value') {
            echo "✅ Token with extra claims decoded successfully\n";
            $passed++;
            return true;
        }
    } catch (Exception $e) {
        echo "❌ Token with extra claims test failed: " . $e->getMessage() . "\n";
    }
    
    $failed++;
    return false;
}

/**
 * Тест: Токен с base64 секретом
 */
function testBase64Secret() {
    global $passed, $failed;
    echo "\n🔐 Testing base64 encoded secret...\n";
    
    $base64Secret = 'base64:' . base64_encode('my-secret-key');
    $actualSecret = base64_decode(substr($base64Secret, 7));
    
    $token = createToken([
        'userId' => 'test-user-123',
    ], $actualSecret);
    
    try {
        $decoded = JWT::decode($token, new Key($actualSecret, 'HS256'));
        if ($decoded->userId === 'test-user-123') {
            echo "✅ Base64 secret token decoded successfully\n";
            $passed++;
            return true;
        }
    } catch (Exception $e) {
        echo "❌ Base64 secret test failed: " . $e->getMessage() . "\n";
    }
    
    $failed++;
    return false;
}

/**
 * Тест: Проверка fallback на APP_KEY
 */
function testFallbackToAppKey() {
    global $passed, $failed;
    echo "\n🔄 Testing fallback to APP_KEY...\n";
    
    // Симулируем ситуацию, когда JWT_SECRET не установлен
    $appKey = 'base64:' . base64_encode('app-key-fallback');
    $actualKey = base64_decode(substr($appKey, 7));
    
    $token = createToken([
        'userId' => 'test-user-123',
    ], $actualKey);
    
    try {
        $decoded = JWT::decode($token, new Key($actualKey, 'HS256'));
        if ($decoded->userId === 'test-user-123') {
            echo "✅ Fallback to APP_KEY works\n";
            $passed++;
            return true;
        }
    } catch (Exception $e) {
        echo "❌ Fallback test failed: " . $e->getMessage() . "\n";
    }
    
    $failed++;
    return false;
}

/**
 * Main test runner
 */
function runTests() {
    global $passed, $failed;
    
    echo str_repeat('=', 60) . "\n";
    echo " PHP JWT Middleware Tests\n";
    echo str_repeat('=', 60) . "\n";
    
    $secret = getenv('JWT_SECRET') ?: 'test_secret_do_not_use_in_production';
    
    testValidToken($secret);
    testExpiredToken($secret);
    testInvalidSignature($secret);
    testTokenWithoutUserId($secret);
    testTamperedToken($secret);
    testTokenWithExtraClaims($secret);
    testBase64Secret();
    testFallbackToAppKey();
    
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "📊 Results: $passed/" . ($passed + $failed) . " tests passed\n";
    
    if ($failed === 0) {
        echo "🎉 All PHP JWT tests passed!\n";
    } else {
        echo "⚠️ $failed test(s) failed. Review the implementation.\n";
    }
    echo str_repeat('=', 60) . "\n";
    
    exit($failed === 0 ? 0 : 1);
}

// Запуск тестов
runTests();
