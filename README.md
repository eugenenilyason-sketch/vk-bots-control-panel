# 🤖 VK Neuro-Agents Control Panel

Система управления нейро-агентами ВКонтакте с полной SSL защитой.

**🔐 Полная SSL изоляция: HTTPS + PostgreSQL SSL**

---

## 🚀 Быстрый старт

### 🐳 Запуск в Docker

```bash
# Клонирование репозитория
git clone <repository-url>
cd project-root

# Запуск проекта
docker compose up -d

# Проверка статуса
docker compose ps

# Просмотр логов
docker compose logs -f
```

**Доступ**:
- Frontend: https://localhost:443 (HTTPS)

---

## 🔐 SSL защита

Проект полностью защищён SSL шифрованием:

### Frontend (HTTPS)
- ✅ TLSv1.2 / TLSv1.3
- ✅ Strong cipher suites
- ✅ Security headers (HSTS, X-Frame-Options)
- ✅ Порт 443

### Backend (PostgreSQL SSL)
- ✅ SSL шифрование соединений
- ✅ TLSv1.2 минимум
- ✅ Сертификаты в `supabase/ssl/`

### Let's Encrypt (для production)

```bash
# Получение сертификата
./scripts/get-letsencrypt-cert.sh yourdomain.com email@example.com

# Автоматическое обновление
crontab scripts/letsencrypt-crontab
```

---

## 📚 Документация

### Основная
- 📖 [API документация](docs/API.md)
- 🚀 [Инструкция по деплою](docs/DEPLOYMENT.md)
- 💳 [ЮMoney P2P интеграция](docs/YOOMONEY-P2P.md)
- 🔐 [Let's Encrypt руководство](docs/LETSENCRYPT-SSL.md)

### Дополнительные материалы
- 📋 [Быстрый старт](docs/QUICKSTART.md)
- 📊 [Отчёт о тестировании](docs/TEST-REPORT.md)
- 📝 [История изменений](CHANGELOG.md)

---

## 🛠 Технологический стек

### Backend
- **Runtime**: Node.js 20+
- **Framework**: Express.js
- **Language**: TypeScript
- **Database ORM**: Prisma
- **Database**: PostgreSQL (Supabase) с SSL
- **Auth**: JWT, OAuth 2.0 (VK)
- **Payments**: ЮKassa, ЮMoney P2P

### Frontend
- **Framework**: React 18 + TypeScript
- **Build Tool**: Vite
- **UI Kit**: Material-UI (MUI)
- **Styling**: TailwindCSS
- **State**: Zustand
- **Data Fetching**: TanStack Query (React Query)
- **Routing**: React Router v6
- **Server**: Nginx с HTTPS/SSL

### DevOps
- **Container**: Docker + Docker Compose
- **Database**: PostgreSQL (Supabase) с SSL
- **Cache**: Redis
- **Frontend**: Nginx с HTTPS
- **Monitoring**: Watchtower

---

## 📋 Требования

- Docker 20+
- Docker Compose 2+
- Node.js 18+ (для локальной разработки)
- OpenSSL (для генерации секретов)
- VK Developers приложение (для OAuth)

---

## 🔧 Разработка

### Backend разработка

```bash
cd backend

# Установка зависимостей
npm install

# Генерация Prisma клиента при изменениях схемы
npx prisma generate

# Создание новой миграции
npx prisma migrate dev --name migration_name

# Запуск в режиме разработки (с auto-reload)
npm run dev

# Запуск тестов
npm test
```

### Frontend разработка

```bash
cd frontend

# Установка зависимостей
npm install

# Запуск в режиме разработки (с hot-reload)
npm run dev

# Сборка для production
npm run build

# Preview production сборки
npm run preview
```

---

## 📊 Структура проекта

```
project-root/
├── docker-compose.yml          # Docker Compose (SSL enabled)
├── .env.example                # Шаблон переменных окружения
├── frontend/
│   ├── Dockerfile              # Nginx с SSL
│   ├── nginx-ssl.conf          # SSL конфигурация Nginx
│   ├── ssl/                    # SSL сертификаты
│   └── public/                 # HTML страницы
├── backend/
│   ├── src/
│   │   ├── config/             # Конфигурация (SSL settings)
│   │   ├── routes/             # API endpoints
│   │   └── services/           # Бизнес логика
│   └── prisma/
│       └── schema.prisma       # Database schema
├── supabase/
│   ├── Dockerfile              # PostgreSQL с SSL
│   ├── ssl/                    # SSL сертификаты
│   └── migrations/             # SQL миграции
├── scripts/
│   ├── generate-ssl-certs.sh   # Генерация SSL сертификатов
│   ├── get-letsencrypt-cert.sh # Получение Let's Encrypt
│   ├── renew-letsencrypt-cert.sh # Обновление SSL
│   └── backup.sh               # Бэкап БД
└── docs/
    ├── API.md                  # API документация
    ├── DEPLOYMENT.md           # Инструкция по деплою
    ├── LETSENCRYPT-SSL.md      # Let's Encrypt руководство
    └── YOOMONEY-P2P.md         # ЮMoney интеграция
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
POSTGRES_PASSWORD=your_password
JWT_SECRET=your_secret

# VK OAuth
VK_CLIENT_ID=your_client_id
VK_CLIENT_SECRET=your_client_secret
VK_REDIRECT_URI=https://yourdomain.com

# Payments
YOOKASSA_SHOP_ID=your_shop_id
YOOKASSA_SECRET_KEY=your_secret_key

# SSL
DATABASE_SSL_ENABLED=true
DATABASE_SSL_REJECT_UNAUTHORIZED=false
```

---

## 📞 Поддержка

- GitHub Issues: Для багов и feature requests
- Email: support@yourdomain.com

---

## 📝 Лицензия

MIT

---

*Версия: 1.2.0 | Дата: 29 марта 2026*  
*Статус: ✅ Полная SSL защита*
