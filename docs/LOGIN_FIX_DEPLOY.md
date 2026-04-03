# 🔧 Инструкция по деплою исправлений входа

## Что было исправлено

### Проблема
При входе через email/password dashboard показывал ошибку "Токен не найден", потому что:
1. AuthController не генерировал JWT токен
2. Не было API endpoint `/api/user/profile`
3. Nginx проксировал все `/api` на backend, игнорируя PHP API

### Решение

| Файл | Изменения |
|------|-----------|
| `AuthController.php` | ✅ Добавлена генерация JWT токена при входе |
| `routes/api.php` | ✅ Создан API endpoint `/api/user/profile` |
| `Api/UserProfileController.php` | ✅ Новый контроллер для профиля |
| `JwtAuthMiddleware.php` | ✅ Исправлено получение userId из JWT |
| `nginx-php.conf` | ✅ Добавлен маршрут `/api/user` для PHP |
| `dashboard-jwt.blade.php` | ✅ Улучшены сообщения об ошибках |
| `composer.json` | ✅ Добавлен `firebase/php-jwt` |

---

## 🚀 Шаги деплоя

### 1. Установить зависимости (в Docker контейнере)

```bash
docker compose exec frontend composer install
```

Это установит `firebase/php-jwt` пакет.

### 2. Проверить JWT_SECRET

Убедитесь, что в `.env` есть `JWT_SECRET`:

```bash
grep JWT_SECRET .env
```

Если нет или пустой — можно использовать `APP_KEY`:
```env
JWT_SECRET=your_secret_key_here
```

Или сгенерировать:
```bash
# Использовать APP_KEY (уже есть)
# Или создать новый:
openssl rand -base64 32
```

### 3. Перезапустить frontend

```bash
docker compose restart frontend
```

### 4. Проверить логи

```bash
docker compose logs -f frontend
```

Не должно быть ошибок при загрузке.

---

## 🧪 Тестирование

### Тест 1: Вход через email/password

1. Откройте `https://yourdomain.com`
2. Введите email и пароль
3. Нажмите "Войти"
4. **Ожидание**: редирект на `/dashboard?token=eyJ...`
5. Dashboard должен загрузиться без ошибок

### Тест 2: Проверка JWT токена

После входа проверьте URL — должен быть параметр `token`:
```
/dashboard?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### Тест 3: API endpoint

```bash
# Получить токен из URL
TOKEN="eyJhbGciOiJIUzI1NiIs..."

# Запросить профиль
curl -H "Authorization: Bearer $TOKEN" \
     -H "Content-Type: application/json" \
     https://yourdomain.com/api/user/profile
```

**Ожидание**: JSON с данными пользователя

### Тест 4: VK ID вход

1. Нажмите "Войти через VK ID"
2. Авторизуйтесь в VK
3. **Ожидание**: редирект на dashboard без ошибок

---

## 🐛 Troubleshooting

### Ошибка: "Class 'Firebase\JWT\JWT' not found"

**Причина**: Composer зависимости не установлены

**Решение**:
```bash
docker compose exec frontend composer install
docker compose restart frontend
```

### Ошибка: "Token не найден" после входа

**Причина**: AuthController не генерирует JWT

**Решение**:
1. Проверьте, что `AuthController.php` имеет метод `generateJWT()`
2. Убедитесь, что login() вызывает `generateJWT($user)`
3. Перезапустите контейнер

### Ошибка: 404 на `/api/user/profile`

**Причина**: Nginx не направляет запрос в PHP

**Решение**:
```bash
# Проверить nginx конфигурацию
docker compose exec frontend nginx -t

# Перезапустить nginx
docker compose exec frontend nginx -s reload
```

### Ошибка: "Invalid token"

**Причина**: JWT_SECRET не совпадает при генерации и проверке

**Решение**:
1. Проверьте `.env`: `JWT_SECRET=...`
2. Убедитесь, что значение одинаковое для генерации и верификации
3. Если используете APP_KEY — убедитесь, что он установлен

---

## 📊 Мониторинг

### Логи входа

```bash
# Все запросы к login
docker compose logs -f frontend | grep -i "login"

# JWT генерация
docker compose logs -f frontend | grep -i "jwt"

# API запросы профиля
docker compose logs -f frontend | grep -i "user/profile"
```

### Проверка токенов

```bash
# Расшифровать JWT токен (для отладки)
echo "eyJ..." | cut -d'.' -f2 | base64 -d | jq
```

---

## ✅ Чеклист после деплоя

- [ ] `composer install` выполнен успешно
- [ ] `JWT_SECRET` установлен в `.env`
- [ ] Frontend перезапущен
- [ ] Вход через email/password работает
- [ ] Dashboard загружается без ошибок
- [ ] `/api/user/profile` возвращает данные
- [ ] Вход через VK ID работает
- [ ] Логи не содержат ошибок

---

*Дата: 3 апреля 2026*
*Статус: ✅ Готово к деплою*
