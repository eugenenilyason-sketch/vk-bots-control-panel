// @ts-check
const { test, expect } = require('@playwright/test');

// Конфигурация
const BASE_URL = process.env.BASE_URL || 'https://yourdomain.com';
const BACKEND_URL = process.env.BACKEND_URL || 'http://localhost:4000';

test.describe('VK ID Authorization Flow', () => {
  test('полный цикл авторизации через VK ID', async ({ browser }) => {
    console.log('🚀 Starting VK ID Authorization Flow Test...');

    const context = await browser.newContext({
      viewport: { width: 1920, height: 1080 },
      recordVideo: { dir: 'videos/' },
    });

    const page = await context.newPage();

    // Логирование
    page.on('console', msg => console.log(`📝 [${msg.type()}] ${msg.text()}`));
    page.on('pageerror', error => console.error(`❌ Page Error: ${error.message}`));

    // Шаг 1: Переход на страницу входа
    console.log('📍 Step 1: Navigate to login page...');
    await page.goto(BASE_URL);
    await expect(page).toHaveTitle(/VK Neuro-Agents/);
    console.log('✅ Login page loaded');

    // Шаг 2: Проверка наличия кнопки VK ID
    console.log('📍 Step 2: Check VK ID button...');
    const vkButton = await page.locator('button:has-text("Войти с VK ID")');
    await expect(vkButton).toBeVisible({ timeout: 10000 });
    console.log('✅ VK ID button found');

    // Шаг 3: Скриншот страницы входа
    await page.screenshot({ path: 'screenshots/auth-01-login.png' });
    console.log('📸 Login screenshot saved');

    // Шаг 4: Проверка localStorage до авторизации
    const tokenBefore = await page.evaluate(() => localStorage.getItem('access_token'));
    expect(tokenBefore).toBeNull();
    console.log('✅ No token before auth');

    // Шаг 5: Клик по кнопке VK ID
    console.log('📍 Step 5: Click VK ID button...');
    await vkButton.click();
    await page.waitForTimeout(3000);

    // Шаг 6: Проверка URL (не должно быть ошибок)
    const currentUrl = page.url();
    console.log('📍 Current URL:', currentUrl);
    expect(currentUrl).toBe(BASE_URL + '/');

    // Шаг 7: Проверка localStorage после попытки авторизации
    const tokenAfter = await page.evaluate(() => localStorage.getItem('access_token'));
    const userAfter = await page.evaluate(() => localStorage.getItem('user'));
    console.log('🎫 Token after click:', tokenAfter ? 'EXISTS' : 'NULL');
    console.log('👤 User after click:', userAfter ? 'EXISTS' : 'NULL');

    // Шаг 8: Финальный скриншот
    await page.screenshot({ path: 'screenshots/auth-02-after-click.png' });
    console.log('📸 After click screenshot saved');

    await context.close();
    console.log('✅ Test completed!');
  });

  test('проверка dashboard с валидным токеном', async ({ browser }) => {
    console.log('🚀 Starting Dashboard with Valid Token Test...');

    // Создаём тестовый токен через API
    const tokenResponse = await createTestToken();
    expect(tokenResponse.success).toBe(true);
    const testToken = tokenResponse.data.access_token;
    console.log('✅ Test token created');

    const context = await browser.newContext();
    const page = await context.newPage();

    // Переход на dashboard с токеном
    console.log('📍 Navigate to dashboard with token...');
    await page.goto(`${BASE_URL}/dashboard?token=${testToken}`);
    await page.waitForTimeout(2000);

    // Проверка загрузки профиля
    const userInfo = await page.locator('#user-info').textContent();
    console.log('👤 User info:', userInfo);
    expect(userInfo).toContain('VK User');

    // Проверка баланса
    const balance = await page.locator('#balance').textContent();
    console.log('💰 Balance:', balance);
    expect(balance).toContain('₽');

    await page.screenshot({ path: 'screenshots/auth-03-dashboard.png' });
    console.log('📸 Dashboard screenshot saved');

    await context.close();
  });

  test('проверка dashboard без токена', async ({ browser }) => {
    console.log('🚀 Starting Dashboard without Token Test...');

    const context = await browser.newContext();
    const page = await context.newPage();

    // Переход на dashboard без токена
    console.log('📍 Navigate to dashboard without token...');
    await page.goto(`${BASE_URL}/dashboard`);
    await page.waitForTimeout(2000);

    // Проверка показа ошибки
    const errorElement = await page.locator('#error');
    await expect(errorElement).toBeVisible({ timeout: 5000 });
    console.log('✅ Error message shown');

    const errorMessage = await page.locator('#error-message').textContent();
    console.log('❌ Error message:', errorMessage);
    expect(errorMessage).toContain('Токен не найден');

    await page.screenshot({ path: 'screenshots/auth-04-no-token.png' });
    console.log('📸 No token screenshot saved');

    await context.close();
  });

  test('проверка dashboard с невалидным токеном', async ({ browser }) => {
    console.log('🚀 Starting Dashboard with Invalid Token Test...');

    const context = await browser.newContext();
    const page = await context.newPage();

    // Переход с невалидным токеном
    const invalidToken = 'invalid.token.here';
    console.log('📍 Navigate to dashboard with invalid token...');
    await page.goto(`${BASE_URL}/dashboard?token=${invalidToken}`);
    await page.waitForTimeout(2000);

    // Проверка показа ошибки
    const errorElement = await page.locator('#error');
    await expect(errorElement).toBeVisible({ timeout: 5000 });
    console.log('✅ Error message shown for invalid token');

    await page.screenshot({ path: 'screenshots/auth-05-invalid-token.png' });
    console.log('📸 Invalid token screenshot saved');

    await context.close();
  });
});

test.describe('Backend API Authorization Tests', () => {
  test('POST /api/auth/vkid - авторизация с access_token', async () => {
    console.log('🚀 Testing POST /api/auth/vkid with access_token...');

    const response = await fetch(`${BACKEND_URL}/api/auth/vkid`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        access_token: 'test_token',
        user_id: '999999',
      }),
    });

    const data = await response.json();
    console.log('📊 Response:', data);

    expect(response.status).toBe(200);
    expect(data.success).toBe(true);
    expect(data.data.access_token).toBeDefined();
    expect(data.data.refresh_token).toBeDefined();
    expect(data.data.user).toBeDefined();
    console.log('✅ VK ID auth successful');
  });

  test('GET /api/user/profile - получение профиля с токеном', async () => {
    console.log('🚀 Testing GET /api/user/profile...');

    // Сначала получаем токен
    const authResponse = await fetch(`${BACKEND_URL}/api/auth/vkid`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        access_token: 'test_token',
        user_id: '999999',
      }),
    });
    const authData = await authResponse.json();
    const token = authData.data.access_token;

    // Запрашиваем профиль
    const profileResponse = await fetch(`${BACKEND_URL}/api/user/profile`, {
      headers: { 'Authorization': `Bearer ${token}` },
    });

    const profileData = await profileResponse.json();
    console.log('📊 Profile Response:', profileData);

    expect(profileResponse.status).toBe(200);
    expect(profileData.success).toBe(true);
    expect(profileData.data.id).toBeDefined();
    expect(profileData.data.email).toBeDefined();
    console.log('✅ Profile retrieved successfully');
  });

  test('GET /api/user/profile - без токена (401)', async () => {
    console.log('🚀 Testing GET /api/user/profile without token...');

    const response = await fetch(`${BACKEND_URL}/api/user/profile`);
    expect(response.status).toBe(401);
    console.log('✅ 401 Unauthorized as expected');
  });

  test('GET /api/user/profile - с невалидным токеном (401)', async () => {
    console.log('🚀 Testing GET /api/user/profile with invalid token...');

    const response = await fetch(`${BACKEND_URL}/api/user/profile`, {
      headers: { 'Authorization': 'Bearer invalid.token' },
    });

    expect(response.status).toBe(401);
    console.log('✅ 401 Unauthorized for invalid token');
  });

  test('GET /health - проверка здоровья backend', async () => {
    console.log('🚀 Testing GET /health...');

    const response = await fetch(`${BACKEND_URL}/health`);
    const data = await response.json();

    expect(response.status).toBe(200);
    expect(data.status).toBe('ok');
    expect(data.timestamp).toBeDefined();
    console.log('✅ Backend health check passed');
  });
});

// Вспомогательная функция для создания тестового токена
async function createTestToken() {
  const response = await fetch(`${BACKEND_URL}/api/auth/vkid`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      access_token: 'test_token',
      user_id: '999999',
    }),
  });
  return await response.json();
}
