{{-- VK ID Button - WORKING --}}
<div style="text-align: center; margin-top: 24px;">
    <script nonce="csp_nonce" src="https://unpkg.com/@vkid/sdk@2.6.5/dist-sdk/umd/index.js"></script>
    <script nonce="csp_nonce" type="text/javascript">
        console.log('🔍 VK ID Script started');
        
        if ('VKIDSDK' in window) {
            console.log('✅ VKIDSDK loaded');
            const VKID = window.VKIDSDK;

            VKID.Config.init({
                app: {{ config('services.vk.client_id', env('VK_CLIENT_ID')) }},
                redirectUrl: '{{ config('services.vk.redirect_uri', env('VK_REDIRECT_URI', 'https://yourdomain.com')) }}',
                responseMode: VKID.ConfigResponseMode.Callback,
                source: VKID.ConfigSource.LOWCODE,
                scope: 'email,name,avatar',
            });

            console.log('⚙️ Config initialized');

            const oneTap = new VKID.OneTap();
            console.log('🎯 OneTap created');

            oneTap.render({
                container: document.currentScript.parentElement,
                showAlternativeLogin: true
            })
            .on(VKID.WidgetEvents.ERROR, function(error) {
                console.error('❌ VK ID Error:', error);
                alert('Ошибка VK ID: ' + JSON.stringify(error));
            })
            .on(VKID.OneTapInternalEvents.LOGIN_SUCCESS, async function (payload) {
                console.log('✅ LOGIN_SUCCESS:', payload);
                const code = payload.code;
                const deviceId = payload.device_id;

                try {
                    // Шаг 1: Обмен кода на токены через VK SDK
                    console.log('🔄 Exchanging code for tokens...');
                    const tokenData = await VKID.Auth.exchangeCode(code, deviceId);
                    console.log('📊 Tokens received:', tokenData);
                    
                    // Шаг 2: Декодирование id_token (JWT) для получения данных пользователя
                    console.log('🔓 Decoding id_token...');
                    const idTokenParts = tokenData.id_token.split('.');
                    const base64Url = idTokenParts[1];
                    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
                    const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
                        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
                    }).join(''));
                    const tokenPayload = JSON.parse(jsonPayload);
                    console.log('📋 ID Token payload:', tokenPayload);
                    
                    console.log('📤 Sending to backend...');
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    console.log('🔑 CSRF Token:', csrfToken ? csrfToken.substring(0, 20) + '...' : 'MISSING');
                    
                    const requestBody = {
                        access_token: tokenData.access_token,
                        user_id: String(tokenData.user_id || tokenPayload.sub),
                        email: tokenPayload.email || '',
                        name: (tokenPayload.first_name || '') + ' ' + (tokenPayload.last_name || ''),
                        avatar: ''
                    };
                    console.log('📦 Request body:', requestBody);
                    
                    const response = await fetch('/api/auth/vkid', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(requestBody),
                        credentials: 'include' // Важно для cookie!
                    });
                    
                    console.log('📥 Response status:', response.status, response.ok);
                    const data = await response.json();
                    console.log('📊 Response data:', data);
                    
                    if (response.ok && data.success) {
                        console.log('✅ LOGIN SUCCESS!');
                        // Сохраняем в session storage (сохранится после перезагрузки)
                        sessionStorage.setItem('access_token', data.data.access_token);
                        sessionStorage.setItem('user', JSON.stringify(data.data.user));
                        // Также в localStorage
                        localStorage.setItem('access_token', data.data.access_token);
                        localStorage.setItem('user', JSON.stringify(data.data.user));
                        console.log('💾 Tokens saved to storage');
                        console.log('🔄 Redirecting to /dashboard...');
                        // Редирект с токеном в URL
                        setTimeout(() => {
                            window.location.href = '/dashboard?token=' + encodeURIComponent(data.data.access_token);
                        }, 500);
                    } else {
                        console.error('❌ Login failed:', data);
                        alert('Ошибка входа: ' + (data.message || data.error?.message || 'Неизвестная ошибка'));
                    }
                } catch (error) {
                    console.error('❌ Error:', error);
                    alert('Ошибка: ' + (error.message || 'Попробуйте ещё раз'));
                }
            });
            
            console.log('🎨 Render called');
        } else {
            console.error('❌ VKIDSDK NOT loaded!');
        }
    </script>
</div>
