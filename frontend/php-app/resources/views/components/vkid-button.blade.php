{{-- VK ID Button Component with PKCE --}}
<div style="text-align: center; margin-top: 24px;">
    <script nonce="csp_nonce" src="https://unpkg.com/@vkid/sdk@3.0.0/dist-sdk/umd/index.js"></script>
    <script nonce="csp_nonce" type="text/javascript">
        if ('VKIDSDK' in window) {
            const VKID = window.VKIDSDK;

            // Генерируем code_verifier и code_challenge (PKCE)
            function generateCodeVerifier() {
                const array = new Uint8Array(32);
                crypto.getRandomValues(array);
                return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
            }

            function sha256(plain) {
                const encoder = new TextEncoder();
                const data = encoder.encode(plain);
                return crypto.subtle.digest('SHA-256', data);
            }

            function base64UrlEncode(str) {
                return btoa(String.fromCharCode.apply(null, new Uint8Array(str)))
                    .replace(/\+/g, '-')
                    .replace(/\//g, '_')
                    .replace(/=+$/, '');
            }

            const codeVerifier = generateCodeVerifier();
            let codeChallenge = '';

            sha256(codeVerifier).then(hash => {
                codeChallenge = base64UrlEncode(hash);

                VKID.Config.init({
                    app: 54514184,
                    redirectUrl: 'https://lianium.ru',
                    responseMode: VKID.ConfigResponseMode.Callback,
                    source: VKID.ConfigSource.LOWCODE,
                    scope: 'email',
                    codeChallenge: codeChallenge,
                    codeChallengeMethod: 'S256',
                });

                const oAuth = new VKID.OAuthList();

                oAuth.render({
                    container: document.currentScript.parentElement,
                    oauthList: ['vkid']
                })
                .on(VKID.WidgetEvents.ERROR, function(error) {
                    console.error('VK ID Error:', error);
                    alert('Ошибка VK ID: ' + JSON.stringify(error));
                })
                .on(VKID.OAuthListInternalEvents.LOGIN_SUCCESS, function (payload) {
                    const code = payload.code;
                    const deviceId = payload.device_id;

                    // Отправляем код и code_verifier на backend
                    fetch('/api/auth/vkid', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ 
                            code, 
                            device_id: deviceId,
                            code_verifier: codeVerifier
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = '/dashboard';
                        } else {
                            alert('Ошибка входа: ' + (data.error?.message || 'Неизвестная ошибка'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ошибка соединения с сервером');
                    });
                });
            });
        }
    </script>
</div>
