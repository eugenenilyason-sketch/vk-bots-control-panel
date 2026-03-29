# 🤖 VK Neuro-Agents Control Panel

Система управления нейро-агентами ВКонтакте с интеграцией n8n и NocoDB.

**🎯 Работа на одном домене за Nginx Proxy Manager**

## 🚀 Быстрый старт

### 1. Настройка Nginx Proxy Manager

**Выберите вариант развёртывания**:

| Вариант | Архитектура | Сложность | Когда использовать |
|---------|-------------|-----------|-------------------|
| **Одиночный NPM** | Internet → NPM → Сайт | ⭐ Простой | Для большинства проектов |
| **Цепочка NPM** | Internet → NPM (Edge) → NPM (Dev) → Сайт | ⭐⭐⭐ Сложный | Для DMZ, дополнительной безопасности |

📖 **Подробные инструкции**:
- [Одиночный NPM](docs/NPM-SETUP.md) — рекомендуется для начала
- [Цепочка NPM](docs/NPM-CHAIN-SETUP.md) — для сложных сетевых конфигураций

### 2. Клонирование и настройка

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

## 📚 Документация

- [📋 Полная спецификация](PROJECT-SPEC.md)
- [📡 API документация](docs/API.md)
- [🚀 Инструкция по деплою](docs/DEPLOYMENT.md)
- [💳 ЮMoney P2P интеграция](docs/YOOMONEY-P2P.md)
- [🗺️ Roadmap](ROADMAP.md)
- [📝 Changelog](CHANGELOG.md)
- [⚙️ Настройка Nginx Proxy Manager](docs/NPM-SETUP.md)

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

### DevOps
- **Container**: Docker + Docker Compose
- **Proxy**: Nginx Proxy Manager
- **Automation**: n8n
- **Admin Panel**: NocoDB
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
