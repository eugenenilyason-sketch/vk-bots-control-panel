<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - VK Neuro-Agents</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <div class="container" style="max-width: 1200px; padding: 40px;">
        <header class="header" style="margin-bottom: 32px;">
            <h1>Dashboard</h1>
            <div id="user-info" style="text-align: right;">
                <div style="font-weight: 600;">Загрузка...</div>
            </div>
        </header>

        <div id="loading" style="text-align: center; padding: 40px;">
            <div style="font-size: 24px;">⏳ Загрузка...</div>
        </div>

        <div id="error" style="display: none; text-align: center; padding: 40px; color: #dc3545;">
            <div style="font-size: 24px;">❌ Ошибка авторизации</div>
            <p id="error-message">Токен не найден или недействителен</p>
            <button onclick="logoutAndRedirect()" class="btn btn-primary" style="margin-top: 16px;">Выйти и войти заново</button>
        </div>

        <div id="dashboard-content" style="display: none;">
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Баланс</div>
                    <div class="stat-value" id="balance">0₽</div>
                    <div style="font-size: 12px; color: var(--text-muted);">Доступно</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Ботов</div>
                    <div class="stat-value" id="bots-count">0</div>
                    <div style="font-size: 12px; color: var(--text-muted);">Активных: <span id="active-bots">0</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Платежей</div>
                    <div class="stat-value" id="payments-count">0</div>
                    <div style="font-size: 12px; color: var(--text-muted);">На сумму: <span id="total-spent">0₽</span></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Статус</div>
                    <div class="stat-value">
                        <span class="badge badge-success">Active</span>
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted);">Аккаунт</div>
                </div>
            </div>

            <!-- User Info -->
            <div style="margin-top: 32px; text-align: right;">
                <button onclick="logoutAndRedirect()" class="btn btn-secondary">Выйти</button>
            </div>
        </div>
    </div>

    <script>
        let hasError = false;

        // Проверка токена при загрузке
        document.addEventListener('DOMContentLoaded', async function() {
            // Получаем токен из URL или localStorage
            const urlParams = new URLSearchParams(window.location.search);
            let token = urlParams.get('token');

            if (!token) {
                token = localStorage.getItem('access_token');
            }

            if (!token) {
                // Нет токена - показываем ошибку
                showError('Токен не найден. Пожалуйста, войдите в систему.');
                return;
            }

            // Сохраняем токен (если из URL)
            if (urlParams.get('token')) {
                localStorage.setItem('access_token', token);
                // Очищаем URL от токена
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            // Загружаем данные пользователя
            try {
                const response = await fetch('/api/user/profile', {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    showDashboard(data.data);
                } else {
                    const errorData = await response.json().catch(() => ({}));
                    showError('Ошибка: ' + (errorData.error?.message || 'Недействительный токен'));
                }
            } catch (error) {
                console.error('Error loading profile:', error);
                showError('Ошибка соединения с сервером. Проверьте подключение и попробуйте снова.');
            }
        });
        
        function showDashboard(user) {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('dashboard-content').style.display = 'block';
            
            document.getElementById('user-info').innerHTML = `
                <div style="font-weight: 600;">${user.username || user.email}</div>
                <div style="font-size: 12px; color: var(--text-secondary);">
                    ${user.role === 'superadmin' ? 'Суперадмин' : (user.role === 'admin' ? 'Админ' : 'Пользователь')}
                </div>
            `;
            
            document.getElementById('balance').textContent = (user.balance || 0) + '₽';
        }
        
        function showError(message) {
            if (hasError) return; // Предотвращаем повторный показ
            hasError = true;
            
            document.getElementById('loading').style.display = 'none';
            document.getElementById('error').style.display = 'block';
            document.getElementById('error-message').textContent = message;
        }
        
        function logoutAndRedirect() {
            // Очищаем токены
            localStorage.removeItem('access_token');
            sessionStorage.removeItem('access_token');
            
            // Делаем настоящий logout через POST запрос
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/logout';
            
            // Добавляем CSRF токен
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            form.appendChild(csrfInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>
