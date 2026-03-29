# 🤖 VK Neuro-Agents Control Panel

Система управления нейро-агентами ВКонтакте с полной SSL защитой.

**🔐 Полная SSL изоляция: HTTPS + PostgreSQL SSL**

## 🚀 Быстрый старт

### 🐳 Запуск в Docker (рекомендуется)

```bash
# Запуск проекта
./scripts/start-docker.sh

# Проверка статуса
docker compose ps

# Просмотр логов
docker compose logs -f
```

**Доступ к сервисам**:
- Frontend: https://localhost:443 (HTTPS)

**Внутренние сервисы** (не доступны наружу):
- Backend API: только внутри Docker сети
- PostgreSQL: только внутри Docker сети (с SSL)
- Redis: только внутри Docker сети

---

### 🔧 Локальная разработка

```bash
# Копирование переменных окружения
cp .env.example .env

# Редактирование .env с вашими данными
nano .env
```

**Обновите переменные**:
```env
FRONTEND_URL=https://yourdomain.com
VK_CLIENT_ID=your_vk_client_id
VK_CLIENT_SECRET=your_vk_client_secret
VK_REDIRECT_URI=https://yourdomain.com
YOOMONEY_ACCOUNT_NUMBER=your_account
YOOMONEY_API_KEY=your_api_key
```

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

📖 **Подробная документация**: [docs/LETSENCRYPT-SSL.md](docs/LETSENCRYPT-SSL.md)

---

## 📚 Документация

- [📋 Полная спецификация](PROJECT-SPEC.md)
- [📡 API документация](docs/API.md)
- [🚀 Инструкция по деплою](docs/DEPLOYMENT.md)
- [💳 ЮMoney P2P интеграция](docs/YOOMONEY-P2P.md)
- [🗺️ Roadmap](ROADMAP.md)
- [📝 Changelog](CHANGELOG.md)

## 🛠 Технологический стек

### Backend
- **Runtime**: Node.js 20+
- **Framework**: Express.js
- **Language**: TypeScript
- **Database ORM**: Prisma
- **Database**: PostgreSQL (Supabase)
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

## 📋 Требования

- Docker 20+
- Docker Compose 2+
- Node.js 18+ (для локальной разработки)
- OpenSSL (для генерации секретов)
- VK Developers приложение (для OAuth)

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

## 📞 Поддержка

- GitHub Issues: Для багов и feature requests
- Email: support@yourdomain.com

---

*Версия: 1.1.0 | Дата: 28 марта 2026*
