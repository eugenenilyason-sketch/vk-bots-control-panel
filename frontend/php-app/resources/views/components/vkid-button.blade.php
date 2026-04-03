{{-- VK ID Button - Mobile & Desktop Support --}}
{{-- На мобильных используем прямой OAuth URL вместо OneTap (OneTap не работает на мобильных) --}}
<div style="text-align: center; margin-top: 24px;" id="vkid-button-container">
    <script nonce="csp_nonce" type="text/javascript">
        console.log('🔍 VK ID Button started');

        // Определяем мобильное устройство
        const isMobile = /Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        console.log('📱 Is mobile:', isMobile);

        const clientId = {{ config('services.vk.client_id', env('VK_CLIENT_ID')) }};
        const redirectUrl = '{{ config('services.vk.redirect_uri', env('VK_REDIRECT_URI', 'https://yourdomain.com')) }}';
        const callbackUrl = redirectUrl.replace(/\/$/, '') + '/auth/vk/callback';

        if (isMobile) {
            // На мобильных используем прямой OAuth URL с callback на наш сайт
            console.log('📱 Mobile: Using direct OAuth URL');

            const container = document.getElementById('vkid-button-container');
            container.innerHTML = `
                <a href="https://id.vk.com/authorize?client_id=${clientId}&redirect_uri=${encodeURIComponent(callbackUrl)}&response_type=code&scope=email,name,avatar&state=mobile_web"
                   style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 24px; background: #0077FF; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; min-width: 200px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                        <path d="M12.785 16.241s.288-.032.436-.194c.136-.148.132-.427.132-.427s-.02-1.304.587-1.496c.598-.189 1.367 1.259 2.182 1.814.616.42 1.084.328 1.084.328l2.178-.03s1.14-.07.599-.964c-.044-.073-.314-.661-1.618-1.869-1.366-1.265-1.183-1.06.462-3.246.999-1.33 1.398-2.142 1.273-2.489-.12-.331-.856-.244-.856-.244l-2.45.015s-.182-.025-.316.056c-.131.079-.216.263-.216.263s-.387 1.028-.903 1.903c-1.09 1.848-1.527 1.946-1.705 1.832-.414-.266-.31-1.075-.31-1.649 0-1.793.272-2.54-.53-2.733-.266-.064-.462-.106-1.142-.113-.872-.009-1.612.003-2.03.208-.278.136-.493.44-.362.457.162.021.528.099.722.365.25.342.241 1.113.241 1.113s.144 2.11-.335 2.372c-.328.18-.778-.187-1.746-1.865-.494-.857-.867-1.802-.867-1.802s-.072-.176-.2-.271c-.155-.115-.372-.151-.372-.151l-2.327.015s-.35.01-.478.162c-.114.135-.009.414-.009.414s1.82 4.258 3.879 6.404c1.887 1.967 4.032 1.838 4.032 1.838h.971z"/>
                    </svg>
                    Войти через VK ID
                </a>
            `;
        } else {
            // На десктопе используем VK ID SDK OneTap
            console.log('🖥️ Desktop: Using VK ID SDK OneTap');
            
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/@vkid/sdk@2.6.5/dist-sdk/umd/index.js';
            script.nonce = 'csp_nonce';
            script.onload = function() {
                if ('VKIDSDK' in window) {
                    console.log('✅ VKIDSDK loaded');
                    const VKID = window.VKIDSDK;

                    VKID.Config.init({
                        app: clientId,
                        redirectUrl: redirectUrl,
                        responseMode: VKID.ConfigResponseMode.Callback,
                        source: VKID.ConfigSource.LOWCODE,
                        scope: 'email,name,avatar',
                    });

                    console.log('⚙️ Config initialized');

                    const oneTap = new VKID.OneTap();
                    console.log('🎯 OneTap created');

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

                    console.log('🎨 Render called');
                } else {
                    console.error('❌ VKIDSDK NOT loaded!');
                }
            };
            document.head.appendChild(script);
        }

        /**
         * Обработка VK ID логина (десктоп)
         */
        async function processVKLogin(code, deviceId) {
            try {
                showLoading();

                const VKID = window.VKIDSDK;
                const tokenData = await VKID.Auth.exchangeCode(code, deviceId);
                console.log('📊 Tokens received');

                const tokenPayload = decodeIdToken(tokenData.id_token);
                console.log('📋 ID Token payload:', tokenPayload);

                await sendToBackend(tokenData, tokenPayload);
            } catch (error) {
                console.error('❌ Login error:', error);
                hideLoading();
                alert('Ошибка: ' + (error.message || 'Попробуйте ещё раз'));
            }
        }

        /**
         * Декодирование JWT id_token
         */
        function decodeIdToken(idToken) {
            const parts = idToken.split('.');
            const base64Url = parts[1];
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));
            return JSON.parse(jsonPayload);
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

            const response = await fetch('/api/auth/vkid', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(requestBody),
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
                hideLoading();
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
            setTimeout(() => location.reload(), 1000);
        }
    </script>
</div>
