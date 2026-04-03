{{-- VK ID Button - Universal (все устройства) --}}
@php
    $clientId = config('services.vk.client_id', env('VK_CLIENT_ID'));
    $redirectUrl = rtrim(config('services.vk.redirect_uri', env('VK_REDIRECT_URI', 'https://yourdomain.com')), '/');
@endphp

<div style="text-align: center; margin-top: 24px;" id="vkid-button-container">
    {{-- VK ID SDK OneTap --}}
    <script src="/js/vkid-sdk.js"></script>
    <script type="text/javascript">
        (function() {
            function initVKID() {
                if (!window.VKIDSDK) {
                    console.error('❌ VK ID SDK not loaded');
                    return;
                }

                const VKID = window.VKIDSDK;

                console.log('🔍 VK ID SDK loaded, initializing...');

                // Инициализация SDK
                VKID.Config.init({
                    app: {{ $clientId }},
                    redirectUrl: '{{ $redirectUrl }}/',
                    responseMode: VKID.ConfigResponseMode.Callback,
                    source: VKID.ConfigSource.LOWCODE,
                    scope: 'email,name,avatar',
                });

                console.log('✅ VK ID Config initialized');

                // Создаём OneTap виджет
                const oneTap = new VKID.OneTap();

                oneTap.render({
                    container: document.getElementById('vkid-button-container'),
                    showAlternativeLogin: true
                })
                .on(VKID.WidgetEvents.ERROR, function(error) {
                    console.error('❌ VK ID Error:', error);
                    alert('Ошибка VK ID: ' + (error.error_description || error.message || 'Неизвестная ошибка'));
                })
                .on(VKID.OneTapInternalEvents.LOGIN_SUCCESS, async function (payload) {
                    console.log('✅ LOGIN_SUCCESS:', payload);
                    await processVKLogin(payload.code, payload.device_id);
                });

                console.log('🎨 OneTap rendered');
            }

            async function processVKLogin(code, deviceId) {
                try {
                    const container = document.getElementById('vkid-button-container');
                    container.innerHTML = '<div style="padding: 12px; color: var(--text-muted);">⏳ Вход через VK...</div>';

                    const VKID = window.VKIDSDK;
                    const tokenData = await VKID.Auth.exchangeCode(code, deviceId);
                    console.log('📊 Tokens received');

                    const tokenPayload = decodeIdToken(tokenData.id_token);
                    console.log('📋 ID Token payload:', tokenPayload);

                    await sendToBackend(tokenData, tokenPayload);
                } catch (error) {
                    console.error('❌ Login error:', error);
                    setTimeout(() => location.reload(), 1000);
                    alert('Ошибка: ' + (error.message || 'Попробуйте ещё раз'));
                }
            }

            function decodeIdToken(idToken) {
                const parts = idToken.split('.');
                const base64Url = parts[1];
                const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
                const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
                    return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
                }).join(''));
                return JSON.parse(jsonPayload);
            }

            async function sendToBackend(tokenData, tokenPayload) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                const response = await fetch('/api/auth/vkid', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        access_token: tokenData.access_token,
                        user_id: String(tokenData.user_id || tokenPayload.sub),
                        email: tokenPayload.email || '',
                        name: (tokenPayload.first_name || '') + ' ' + (tokenPayload.last_name || ''),
                        avatar: tokenPayload.avatar || ''
                    }),
                    credentials: 'include'
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    sessionStorage.setItem('access_token', data.data.access_token);
                    sessionStorage.setItem('user', JSON.stringify(data.data.user));
                    localStorage.setItem('access_token', data.data.access_token);
                    localStorage.setItem('user', JSON.stringify(data.data.user));

                    setTimeout(() => {
                        window.location.href = '/dashboard?token=' + encodeURIComponent(data.data.access_token);
                    }, 500);
                } else {
                    setTimeout(() => location.reload(), 1000);
                    alert('Ошибка входа: ' + (data.message || data.error?.message || 'Неизвестная ошибка'));
                }
            }

            // Запуск инициализации при загрузке
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initVKID);
            } else {
                initVKID();
            }
        })();
    </script>
</div>
