# 🚀 Инструкция по запуску проекта

## Предварительные требования

- Docker 20+
- Docker Compose 2+
- Node.js 18+
- npm или pnpm

---

## Шаг 1: Подготовка окружения

### 1.1 Проверка Docker

```bash
docker --version
docker compose version
```

### 1.2 Проверка Node.js

```bash
node --version  # Должно быть 18+
npm --version
```

---

## Шаг 2: Настройка переменных окружения

### 2.1 Копирование файлов

```bash
cd /home/vidserv/web-vk-bot

# Копирование .env файлов
cp .env.example .env
cp frontend/.env.example frontend/.env
```

### 2.2 Генерация секретов

```bash
# Генерация JWT_SECRET
JWT_SECRET=$(openssl rand -hex 32)
echo "JWT_SECRET=$JWT_SECRET" >> .env

# Генерация POSTGRES_PASSWORD
POSTGRES_PASSWORD=$(openssl rand -hex 32)
# Замените строку в .env
sed -i "s/POSTGRES_PASSWORD=.*/POSTGRES_PASSWORD=$POSTGRES_PASSWORD/" .env
```

### 2.3 Настройка VK OAuth

1. Перейдите на https://vk.com/dev
2. Создайте новое приложение
3. Выберите тип "Website"
4. Укажите Redirect URI: `http://localhost:3000/login`
5. Скопируйте Client ID и Client Secret
6. Обновите `.env`:

```env
VK_CLIENT_ID=your_actual_client_id
VK_CLIENT_SECRET=your_actual_client_secret
VK_REDIRECT_URI=http://localhost:3000/login
```

7. Обновите `frontend/.env`:

```env
VITE.VK_CLIENT_ID=your_actual_client_id
VITE.VK_REDIRECT_URI=http://localhost:3000/login
```

---

## Шаг 3: Запуск инфраструктуры (Docker)

### 3.1 Запуск сервисов

```bash
cd /home/vidserv/web-vk-bot

# Запуск базовых сервисов
docker compose up -d nginx-proxy-manager n8n nocodb supabase redis watchtower
```

### 3.2 Проверка статуса

```bash
docker compose ps
```

Должны быть запущены:
- ✅ npm (Nginx Proxy Manager)
- ✅ n8n
- ✅ nocodb
- ✅ supabase (PostgreSQL)
- ✅ redis
- ✅ watchtower

### 3.3 Проверка логов

```bash
# Все логи
docker compose logs -f

# Логи конкретного сервиса
docker compose logs supabase
```

---

## Шаг 4: Настройка Backend

### 4.1 Установка зависимостей

```bash
cd backend
npm install
```

### 4.2 Генерация Prisma клиента

```bash
npx prisma generate
```

### 4.3 Применение миграций

```bash
# Создание и применение миграций
npx prisma migrate dev --name init

# Или применение существующих миграций
npx prisma migrate deploy
```

### 4.4 Заполнение базы данных (Seed)

```bash
# Если есть seed данные
npx prisma db seed
```

### 4.5 Запуск Backend

```bash
# Режим разработки (с auto-reload)
npm run dev

# Или production сборка
npm run build
npm start
```

Backend должен быть доступен по адресу: http://localhost:4000

Проверка:
```bash
curl http://localhost:4000/health
# Ожидаемый ответ: {"status":"ok","timestamp":"..."}
```

---

## Шаг 5: Настройка Frontend

### 5.1 Установка зависимостей

```bash
cd frontend
npm install
```

### 5.2 Запуск Frontend

```bash
# Режим разработки (с hot-reload)
npm run dev

# Или production сборка
npm run build
npm run preview
```

Frontend должен быть доступен по адресу: http://localhost:3000

---

## Шаг 6: Проверка работы

### 6.1 Открыть браузер

Перейдите на http://localhost:3000

### 6.2 Вход через VK

1. Нажмите "Войти через VK"
2. Авторизуйтесь в VK
3. Разрешите доступ к приложению
4. Должна произойти переадресация обратно на /login с кодом
5. Автоматический вход в систему

### 6.3 Проверка API

```bash
# Проверка health endpoint
curl http://localhost:4000/health

# Проверка API (требуется токен)
curl -H "Authorization: Bearer YOUR_TOKEN" http://localhost:4000/api/auth/me
```

---

## Шаг 7: Настройка админ-панелей

### 7.1 Nginx Proxy Manager

1. Откройте http://localhost:81
2. Логин: `admin@example.com`
3. Пароль: `changeme`
4. Смените пароль при первом входе

### 7.2 NocoDB

1. Откройте http://localhost:8080
2. Создайте суперадмина
3. Подключитесь к базе данных:
   - Host: `supabase`
   - Port: `5432`
   - Database: `vk_bot`
   - User: `postgres`
   - Password: из `.env`

### 7.3 n8n

1. Откройте http://localhost:5678
2. Создайте аккаунт
3. Настройте workflow для VK бота

---

## Шаг 8: Регистрация суперадмина

### 8.1 Первый пользователь

Первый пользователь, вошедший через VK, автоматически получает роль `user`.

### 8.2 Повышение до суперадмина

Через NocoDB:

1. Откройте таблицу `users`
2. Найдите своего пользователя
3. Измените `role` с `user` на `superadmin`
4. Сохраните

Или через SQL:

```sql
UPDATE users SET role = 'superadmin' WHERE email = 'your@email.com';
```

### 8.3 Вход в админку

После повышения роли:
1. Выйдите из системы
2. Войдите снова
3. В меню появится пункт "Админка"

---

## 🔧 Полезные команды

### Управление Docker

```bash
# Остановка всех сервисов
docker compose down

# Перезапуск сервиса
docker compose restart backend

# Просмотр логов
docker compose logs -f backend

# Пересоздание контейнера
docker compose up -d --force-recreate backend
```

### Backend команды

```bash
cd backend

# Запуск в режиме разработки
npm run dev

# Сборка
npm run build

# Запуск production версии
npm start

# Линтинг
npm run lint

# Тесты
npm test
```

### Frontend команды

```bash
cd frontend

# Запуск в режиме разработки
npm run dev

# Сборка
npm run build

# Preview production сборки
npm run preview

# Линтинг
npm run lint
```

### Prisma команды

```bash
cd backend

# Генерация клиента
npx prisma generate

# Создание миграции
npx prisma migrate dev --name migration_name

# Применение миграций
npx prisma migrate deploy

# Открытие Prisma Studio (GUI для БД)
npx prisma studio

# Сброс базы данных
npx prisma migrate reset
```

---

## 🐛 Troubleshooting

### Backend не запускается

**Проблема**: Ошибка подключения к базе данных

**Решение**:
```bash
# Проверьте, что PostgreSQL запущен
docker compose ps supabase

# Проверьте DATABASE_URL в .env
cat .env | grep DATABASE_URL

# Проверьте логи PostgreSQL
docker compose logs supabase
```

### Frontend не видит backend

**Проблема**: Ошибки CORS или 404

**Решение**:
```bash
# Проверьте VITE_API_URL в frontend/.env
cat frontend/.env | grep VITE_API_URL

# Должно быть: VITE_API_URL=http://localhost:4000
```

### VK OAuth не работает

**Проблема**: redirect_uri mismatch

**Решение**:
1. Проверьте Redirect URI в настройках VK приложения
2. Должно совпадать с `VITE.VK_REDIRECT_URI` в `frontend/.env`
3. По умолчанию: `http://localhost:3000/login`

### Prisma миграции не применяются

**Проблема**: Ошибка миграций

**Решение**:
```bash
# Сброс базы данных (осторожно! удалит все данные)
cd backend
npx prisma migrate reset

# Или применение существующих миграций
npx prisma migrate deploy
```

---

## 📊 Мониторинг

### Проверка статуса сервисов

```bash
docker compose ps
```

### Просмотр логов

```bash
# Все сервисы
docker compose logs -f

# Конкретный сервис
docker compose logs -f backend
docker compose logs -f frontend
```

### Статистика использования ресурсов

```bash
docker stats
```

---

## ✅ Чек-лист успешного запуска

- [ ] Docker сервисы запущены (`docker compose ps`)
- [ ] Backend доступен (http://localhost:4000/health возвращает OK)
- [ ] Frontend доступен (http://localhost:3000 открывается)
- [ ] VK OAuth настроен и работает
- [ ] База данных подключена
- [ ] Первый пользователь создан
- [ ] Роль суперадмина назначена
- [ ] NocoDB подключен к базе данных
- [ ] n8n настроен

---

## 📞 Поддержка

При возникновении проблем:

1. Проверьте логи: `docker compose logs -f`
2. Проверьте документацию в папке `docs/`
3. Создайте issue на GitHub

---

*Версия: 1.1.0 | Дата: 28 марта 2026*
