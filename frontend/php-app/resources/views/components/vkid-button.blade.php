{{-- VK ID Button - Mobile & Desktop Support --}}
{{-- Используем Callback mode для всех устройств (Redirect mode не работает на мобильных) --}}
<div style="text-align: center; margin-top: 24px;" id="vkid-button-container">
    <script nonce="csp_nonce" src="https://unpkg.com/@vkid/sdk@2.6.5/dist-sdk/umd/index.js"></script>
    <script nonce="csp_nonce" type="text/javascript">
        console.log('🔍 VK ID Script started');

        if ('VKIDSDK' in window) {
            console.log('✅ VKIDSDK loaded');
            const VKID = window.VKIDSDK;

            // ВАЖНО: Используем Callback mode для ВСЕХ устройств
            // Redirect mode не работает на мобильных — VK приложение не может вернуть управление браузеру
            // Callback mode использует postMessage для передачи данных, что работает везде
            VKID.Config.init({
                app: {{ config('services.vk.client_id', env('VK_CLIENT_ID')) }},
                redirectUrl: '{{ config('services.vk.redirect_uri', env('VK_REDIRECT_URI', 'https://yourdomain.com')) }}',
                responseMode: VKID.ConfigResponseMode.Callback,
                source: VKID.ConfigSource.LOWCODE,
                scope: 'email,name,avatar',
            });

            console.log('⚙️ Config initialized (Callback mode for all devices)');

            const oneTap = new VKID.OneTap();
            console.log('🎯 OneTap created');

            oneTap.render({
                container: document.currentScript.parentElement,
                showAlternativeLogin: true
            })
            .on(VKID.WidgetEvents.ERROR, function(error) {
                console.error('❌ VK ID Error:', error);
                console.error('Error details:', JSON.stringify(error));
                
                // Показываем пользователю понятное сообщение
                let errorMsg = 'Ошибка VK ID';
                if (error.error_description) {
                    errorMsg += ': ' + error.error_description;
                } else if (error.message) {
                    errorMsg += ': ' + error.message;
                }
                
                // На мобильных показываем альтернативу
                const isMobile = /Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                if (isMobile) {
                    showMobileFallback(errorMsg);
                } else {
                    alert(errorMsg);
                }
            })
            .on(VKID.OneTapInternalEvents.LOGIN_SUCCESS, async function (payload) {
                console.log('✅ LOGIN_SUCCESS event received');
                console.log('Payload:', JSON.stringify(payload));
                
                const code = payload.code;
                const deviceId = payload.device_id;

                if (!code || !deviceId) {
                    console.error('❌ Missing code or device_id in payload');
                    alert('Ошибка: не получен код авторизации');
                    return;
                }

                try {
                    showLoading();

                    // Шаг 1: Обмен кода на токены через VK SDK
                    console.log('🔄 Exchanging code for tokens...');
                    console.log('Code:', code.substring(0, 20) + '...');
                    console.log('Device ID:', deviceId);
                    
                    const tokenData = await VKID.Auth.exchangeCode(code, deviceId);
                    console.log('📊 Tokens received:', {
                        hasAccessToken: !!tokenData.access_token,
                        hasIdToken: !!tokenData.id_token,
                        userId: tokenData.user_id
                    });

                    if (!tokenData.access_token || !tokenData.id_token) {
                        throw new Error('Не получен access_token или id_token');
                    }

                    // Шаг 2: Декодирование id_token (JWT) для получения данных пользователя
                    console.log('🔓 Decoding id_token...');
                    const tokenPayload = decodeIdToken(tokenData.id_token);
                    console.log('📋 ID Token payload:', {
                        sub: tokenPayload.sub,
                        email: tokenPayload.email,
                        firstName: tokenPayload.first_name,
                        lastName: tokenPayload.last_name
                    });

                    // Шаг 3: Отправка на backend
                    console.log('📤 Sending to backend...');
                    await sendToBackend(tokenData, tokenPayload);
                    
                } catch (error) {
                    console.error('❌ Login error:', error);
                    hideLoading();
                    
                    let errorMsg = 'Ошибка входа';
                    if (error.message) {
                        errorMsg += ': ' + error.message;
                    }
                    alert(errorMsg);
                }
            });

            console.log('🎨 Render called');
        } else {
            console.error('❌ VKIDSDK NOT loaded!');
            showMobileFallback('VK ID SDK не загрузился');
        }

        /**
         * Декодирование JWT id_token
         */
        function decodeIdToken(idToken) {
            try {
                const parts = idToken.split('.');
                if (parts.length !== 3) {
                    throw new Error('Invalid token format');
                }
                const base64Url = parts[1];
                const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
                const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
                    return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
                }).join(''));
                return JSON.parse(jsonPayload);
            } catch (error) {
                console.error('❌ Token decode error:', error);
                throw new Error('Не удалось декодировать токен');
            }
        }

        /**
         * Отправка данных на backend
         */
        async function sendToBackend(tokenData, tokenPayload) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            const requestBody = {
                access_token: tokenData.access_token,
                user_id: String(tokenData.user_id || tokenPayload.sub),
                email: tokenPayload.email || '',
                name: (tokenPayload.first_name || '') + ' ' + (tokenPayload.last_name || ''),
                avatar: tokenPayload.avatar || ''
            };

            console.log('📦 Request body:', {
                userId: requestBody.user_id,
                email: requestBody.email,
                hasAccessToken: !!requestBody.access_token
            });

            const response = await fetch('/api/auth/vkid', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(requestBody),
                credentials: 'include'
            });

            console.log('📥 Response status:', response.status);
            const data = await response.json();
            console.log('📊 Response data:', {
                success: data.success,
                hasToken: !!data.data?.access_token
            });

            if (response.ok && data.success) {
                console.log('✅ LOGIN SUCCESS!');
                
                // Сохраняем токены
                sessionStorage.setItem('access_token', data.data.access_token);
                sessionStorage.setItem('user', JSON.stringify(data.data.user));
                localStorage.setItem('access_token', data.data.access_token);
                localStorage.setItem('user', JSON.stringify(data.data.user));
                
                console.log('💾 Tokens saved to storage');
                console.log('🔄 Redirecting to /dashboard...');
                
                // Редирект на dashboard
                setTimeout(() => {
                    window.location.href = '/dashboard?token=' + encodeURIComponent(data.data.access_token);
                }, 500);
            } else {
                hideLoading();
                console.error('❌ Backend login failed:', data);
                alert('Ошибка входа: ' + (data.message || data.error?.message || 'Неизвестная ошибка'));
            }
        }

        /**
         * Показать индикатор загрузки
         */
        function showLoading() {
            const container = document.getElementById('vkid-button-container');
            if (container) {
                container.innerHTML = '<div style="padding: 12px; color: var(--text-muted); font-size: 14px;">⏳ Вход через VK...</div>';
            }
        }

        /**
         * Скрыть индикатор загрузки
         */
        function hideLoading() {
            // Перезагружаем страницу для восстановления кнопки
            setTimeout(() => location.reload(), 1000);
        }

        /**
         * Показать fallback для мобильных при ошибке
         */
        function showMobileFallback(errorMsg) {
            const container = document.getElementById('vkid-button-container');
            if (container) {
                container.innerHTML = `
                    <div style="margin-top: 16px; padding: 16px; background: var(--card-bg, #1a1a2e); border-radius: 8px; border: 1px solid var(--border-color, #333);">
                        <p style="margin: 0 0 8px 0; font-size: 14px; color: var(--text-muted, #888);">
                            ⚠️ ${errorMsg || 'Вход через приложение VK не работает'}
                        </p>
                        <p style="margin: 0; font-size: 13px; color: var(--text-secondary, #666);">
                            Используйте вход через email/пароль или откройте сайт в десктопном браузере
                        </p>
                    </div>
                `;
            }
        }
    </script>
</div>
