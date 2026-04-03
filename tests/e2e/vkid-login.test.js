// @ts-check
const { test, expect } = require('@playwright/test');

test('VK ID Login Test', async ({ browser }) => {
  console.log('🚀 Starting VK ID Login Test...');
  
  // Создаём контекст с логином
  const context = await browser.newContext({
    viewport: { width: 1920, height: 1080 },
    recordVideo: { dir: 'videos/' },
  });
  
  const page = await context.newPage();
  
  // Включаем логирование консоли
  page.on('console', msg => {
    console.log(`📝 [${msg.type()}] ${msg.text()}`);
  });
  
  page.on('pageerror', error => {
    console.error(`❌ Page Error: ${error.message}`);
  });
  
  page.on('request', request => {
    console.log(`🌐 Request: ${request.method()} ${request.url()}`);
  });
  
  page.on('response', response => {
    console.log(`📨 Response: ${response.status()} ${response.url()}`);
  });
  
  const baseUrl = process.env.BASE_URL || 'https://yourdomain.com';
  console.log('📍 Navigating to login page...');
  await page.goto(baseUrl);
  console.log('✅ Page loaded');

  // Ждём появления кнопки VK ID
  console.log('⏳ Waiting for VK ID button...');
  // Кнопка VK ID имеет текст "Войти с VK ID" и класс или атрибуты VK
  await page.waitForSelector('button:has-text("Войти с VK ID"), [class*="vkid"], [data-vkid]', { timeout: 15000 });
  console.log('✅ VK ID button found');

  // Делаем скриншот
  await page.screenshot({ path: 'screenshots/01-login-page.png' });
  console.log('📸 Screenshot saved');

  // Проверяем localStorage до входа
  const codeVerifierBefore = await page.evaluate(() => localStorage.getItem('vkid_code_verifier'));
  console.log('🔑 code_verifier before:', codeVerifierBefore ? 'EXISTS' : 'NULL');

  // Нажимаем на кнопку VK ID
  const vkButton = await page.$('button:has-text("Войти с VK ID")');
  if (vkButton) {
    console.log('🖱️ Clicking VK ID button...');
    await vkButton.click();
    await page.waitForTimeout(5000);
  } else {
    console.log('⚠️ VK ID button not found, trying alternative selector...');
    // Пробуем найти кнопку по классу VKID
    const altButton = await page.$('[class*="vkid"] button, button[class*="vk"]');
    if (altButton) {
      console.log('🖱️ Clicking alternative VK button...');
      await altButton.click();
      await page.waitForTimeout(5000);
    }
  }
  
  // Проверяем localStorage после
  const codeVerifierAfter = await page.evaluate(() => localStorage.getItem('vkid_code_verifier'));
  console.log('🔑 code_verifier after:', codeVerifierAfter ? 'EXISTS (' + codeVerifierAfter.length + ' chars)' : 'NULL');
  
  const accessToken = await page.evaluate(() => localStorage.getItem('access_token'));
  console.log('🎫 access_token:', accessToken ? 'EXISTS' : 'NULL');
  
  const user = await page.evaluate(() => localStorage.getItem('user'));
  console.log('👤 user:', user ? 'EXISTS' : 'NULL');
  
  // Проверяем URL
  const currentUrl = page.url();
  console.log('📍 Current URL:', currentUrl);
  
  // Проверяем есть ли code в URL
  const urlParams = new URL(currentUrl).searchParams;
  const code = urlParams.get('code');
  const deviceId = urlParams.get('device_id');
  console.log('📋 URL params:', { code: code ? 'YES' : 'NO', device_id: deviceId ? 'YES' : 'NO' });
  
  // Финальный скриншот
  await page.screenshot({ path: 'screenshots/02-after-auth.png' });
  console.log('📸 Final screenshot saved');
  
  // Закрываем
  await context.close();
  console.log('✅ Test completed!');
});
