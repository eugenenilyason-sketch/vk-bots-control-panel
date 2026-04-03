# 🤖 VK Neuro-Agents Control Panel

Панель управления нейро-агентами ВКонтакте на **PHP/Laravel 11** с полной SSL защитой.

**🔐 Полная SSL изоляция: HTTPS + PostgreSQL SSL**

---

## 📋 О проекте

VK Neuro-Agents — это панель управления для создания и управления ботами ВКонтакте с интеграцией платёжных систем и авторизацией через VK ID.

### 🔑 Возможности

| Раздел | Функции |
|--------|---------|
| **👤 Аутентификация** | Вход через Email/пароль, VK ID OAuth |
| **🤖 Боты** | Создание, редактирование, запуск/остановка ботов |
| **💳 Платежи** | История платежей, пополнение баланса, платёжные методы |
| **⚙️ Настройки** | Профиль, смена пароля |
| **🛡️ Админка** | Управление пользователями, платёжными методами, статистика |

### 👥 Роли пользователей

| Роль | Права |
|------|-------|
| **User** | Управление своими ботами, платежи, настройки |
| **Admin** | Доступ к админ-панели, управление пользователями |
| **Superadmin** | Полный доступ, включая настройку платёжных методов |

### 💳 Платёжные методы

- **YooMoney P2P** — P2P переводы
- **Банковская карта** — эквайринг
- **СБП (QR)** — система быстрых платежей
- **Криптовалюта** — USDT, BTC, ETH

> **Примечание**: Настройка платёжных методов доступна только суперадмину через `/admin/payment-methods`

---

## 🚀 Быстрый старт

### 📋 Требования

| Минимальные | Рекомендуемые |
|-------------|---------------|
| CPU: 2 cores | CPU: 4 cores |
| RAM: 4 GB | RAM: 8 GB |
| Disk: 20 GB | Disk: 50 GB SSD |
| Docker: 20+ | Docker Compose: 2+ |
| OpenSSL | Для генерации секретов |

### 🐳 Запуск в Docker

```bash
# 1. Клонирование репозитория
git clone https://github.com/YOUR_USERNAME/vk-neuro-agents.git
cd vk-neuro-agents

# 2. Инициализация (создание .env и генерация секретов)
./scripts/init.sh

# 3. Настройка переменных окружения
nano .env
# Укажите VK_CLIENT_ID, VK_CLIENT_SECRET, VK_REDIRECT_URI и другие

# 4. Запуск проекта
docker compose up -d

# 5. Создание админа
./scripts/make-admin.sh admin@yourdomain.com superadmin

# 6. Проверка статуса
docker compose ps

# 7. Просмотр логов
docker compose logs -f
```

**Доступ**:
- Frontend: `https://yourdomain.com` (HTTPS)
- Backend API: `http://vk-backend:4000`

**Вход для админа**:
- Email: `admin@yourdomain.com`
- Пароль: (из скрипта `make-admin.sh`)

---

## 🔐 SSL защита

Проект полностью защищён SSL шифрованием:

### Frontend (HTTPS)
- ✅ TLSv1.2 / TLSv1.3
- ✅ Strong cipher suites
- ✅ Security headers (HSTS, X-Frame-Options)

### Backend (PostgreSQL SSL)
- ✅ SSL шифрование соединений
- ✅ TLSv1.2 минимум

### Let's Encrypt (для production)

```bash
# Получение сертификата
./scripts/get-letsencrypt-cert.sh yourdomain.com email@example.com

# Автоматическое обновление (cron)
crontab scripts/letsencrypt-crontab
```

**📖 Полная документация**: [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)

---

## 📚 Документация

### Основная
- 📖 [API документация](docs/API.md)
- 🚀 [Руководство по развёртыванию](docs/DEPLOYMENT.md)

### Скрипты
- 🚀 [init.sh](scripts/init.sh) - Инициализация проекта
- 📦 [deploy.sh](scripts/deploy.sh) - Деплой обновлений
- 💾 [backup.sh](scripts/backup.sh) - Бэкап БД
- 👤 [make-admin.sh](scripts/make-admin.sh) - Создание админа
- 🔐 [get-letsencrypt-cert.sh](scripts/get-letsencrypt-cert.sh) - SSL сертификаты
- 🔄 [renew-letsencrypt-cert.sh](scripts/renew-letsencrypt-cert.sh) - Обновление SSL

---

## 🛠 Технологический стек

### Backend API
- **Runtime**: Node.js 20+
- **Framework**: Express.js
- **Language**: TypeScript
- **Database ORM**: Prisma
- **Auth**: JWT, OAuth 2.0 (VK)

### Frontend (PHP)
- **PHP**: 8.4-FPM
- **Framework**: Laravel 11
- **Database**: PostgreSQL (Supabase) с SSL
- **Cache**: Redis
- **Server**: Nginx с HTTPS/SSL

### Инфраструктура
- **Container**: Docker + Docker Compose
- **Database**: PostgreSQL (Supabase) с SSL
- **Cache**: Redis
- **Monitoring**: Watchtower

---

## 📋 Требования

- Docker 20+
- Docker Compose 2+
- Node.js 18+ (для backend API)
- PHP 8.4+ (для Laravel)
- OpenSSL (для генерации секретов)
- VK Developers приложение (для OAuth)

---

## 🗑️ Удаление проекта

Для полного удаления проекта:

```bash
# Запуск скрипта удаления
./scripts/uninstall.sh
```

**Будет удалено:**
- Все Docker контейнеры
- Все тома (базы данных, кэши)
- Все сети
- Логи приложения
- SSL сертификаты
- .env файлы

**Останется:**
- Исходный код
- Скрипты
- Документация
- Git история

---

## 🔧 Разработка

### Backend API разработка

```bash
cd backend

# Установка зависимостей
npm install

# Генерация Prisma клиента
npx prisma generate

# Создание миграции
npx prisma migrate dev --name migration_name

# Запуск в режиме разработки
npm run dev
```

### Frontend (Laravel) разработка

```bash
cd frontend/php-app

# Установка зависимостей
composer install

# Генерация ключа приложения
php artisan key:generate

# Миграция БД
php artisan migrate

# Запуск в режиме разработки
php artisan serve
```

---

## 📊 Структура проекта

```
web-vk-bot/
├── 📄 docker-compose.yml       # Docker конфигурация
├── 📄 .env.example             # Шаблон переменных окружения
├── 📄 README.md                # Документация
│
├── 📂 frontend/                # Laravel 11 приложение
│   ├── php-app/
│   │   ├── app/
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/    # Контроллеры
│   │   │   │   └── Middleware/     # Middleware
│   │   │   └── Models/             # Модели Eloquent
│   │   ├── config/                 # Конфигурация Laravel
│   │   ├── database/
│   │   │   └── migrations/         # Миграции БД
│   │   ├── resources/
│   │   │   └── views/              # Blade шаблоны
│   │   └── routes/
│   │       └── web.php             # Маршруты
│   ├── nginx-php.conf              # Nginx конфигурация
│   └── public/
│       ├── index.html              # Страница входа
│       └── styles.css              # Глобальные стили
│
├── 📂 backend/                 # Node.js API
│   ├── src/
│   │   ├── config/             # Конфигурация
│   │   ├── routes/             # API маршруты
│   │   └── services/
│   │       └── auth/           # Сервисы аутентификации
│   └── prisma/
│       ├── schema.prisma       # Prisma схема
│       └── migrations/         # Prisma миграции
│
├── 📂 supabase/                # PostgreSQL
│   └── migrations/             # SQL миграции
│
├── 📂 scripts/                 # Скрипты развёртывания
│   ├── init.sh                 # Инициализация проекта
│   ├── deploy.sh               # Деплой обновлений
│   ├── backup.sh               # Бэкап БД
│   ├── make-admin.sh           # Создание админа
│   ├── get-letsencrypt-cert.sh # SSL сертификаты
│   └── renew-letsencrypt-cert.sh # Обновление SSL
│
└── 📂 docs/                    # Документация
    ├── API.md                  # API документация
    └── DEPLOYMENT.md           # Руководство по развёртыванию
```

**⚠️  Не публикуется на GitHub** (в .gitignore):
```
❌ .env                       # Реальные секреты
❌ ssl/                       # SSL сертификаты
❌ backups/                   # Бэкапы БД
❌ logs/                      # Логи
❌ node_modules/              # Зависимости
❌ vendor/                    # PHP зависимости
❌ storage/                   # Laravel storage
```

---

## 🔑 Переменные окружения

Скопируйте `.env.example` в `.env` и настройте:

```bash
cp .env.example .env
nano .env
```

### Основные переменные

```env
# Database
POSTGRES_PASSWORD=your_secure_password
DATABASE_URL=postgresql://postgres:${POSTGRES_PASSWORD}@supabase:5432/vk_bot

# JWT
JWT_SECRET=your_secure_jwt_secret

# VK ID OAuth
VK_CLIENT_ID=your_vk_client_id
VK_CLIENT_SECRET=your_vk_client_secret
VK_REDIRECT_URI=https://yourdomain.com

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Session
SESSION_DRIVER=file

# Redis
REDIS_PASSWORD=your_secure_redis_password
```

> **Важно**: Все секреты генерируются автоматически при запуске `./scripts/init.sh`. Вам нужно указать только VK credentials.
```

---

## 👤 Администрирование

### Создать админа

```bash
# Скрипт создания админа
./scripts/make-admin.sh

# Введите данные:
# - Email
# - Пароль
# - Имя пользователя
# - Роль (superadmin)
```

### Управление пользователями

**Через админ-панель**:
1. Войдите как superadmin
2. `/admin/users` - список пользователей
3. Редактирование, блокировка, смена роли

### Настройки системы

**Через админ-панель**:
1. `/admin/settings` - настройки системы
2. Включение/отключение регистрации
3. Управление платёжными методами

---

## 💳 Платёжные методы

Настройка через админ-панель (`/admin/payment-methods`):

- **YooMoney P2P** - P2P переводы
- **Банковская карта** - эквайринг
- **СБП (QR)** - система быстрых платежей
- **Криптовалюта** - USDT, BTC, ETH

**API ключи шифруются** перед сохранением в БД!

---

## 🔒 Безопасность

### Пароли
- ✅ Хеширование bcrypt
- ✅ Минимум 6 символов
- ✅ Проверка сложности

### Сессии
- ✅ Laravel session cookies
- ✅ CSRF защита
- ✅ Secure cookies (HTTPS only)

### API
- ✅ JWT токены
- ✅ Rate limiting
- ✅ CORS настройки

---

## 📝 Лицензия

MIT

---

*Версия: 2.0.0 | Дата: 30 марта 2026*  
*Статус: ✅ PHP/Laravel 11 + Полная SSL защита*
