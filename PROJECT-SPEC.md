# 🤖 VK Neuro-Agents Control Panel
## Спецификация проекта системы управления нейро-агентами ВКонтакте

---

## 📋 Оглавление

1. [Обзор проекта](#-обзор-проекта)
2. [Технологический стек](#-технологический-стек)
3. [Архитектура системы](#-архитектура-системы)
4. [Функциональные требования](#-функциональные-требования)
5. [Структура проекта](#-структура-проекта)
6. [Docker Compose конфигурация](#-docker-compose-конфигурация)
7. [API спецификация](#-api-спецификация)
8. [Безопасность](#-безопасность)
9. [Дорожная карта](#-дорожная-карта)

---

## 📖 Обзор проекта

**VK Neuro-Agents Control Panel** — это веб-платформа для управления нейро-агентами в социальной сети ВКонтакте с интеграцией n8n (автоматизация) и NocoDB (база данных).

### Ключевые возможности

- **Для администраторов**: мониторинг, управление пользователями, оплатами, деплой, настройка API
- **Для пользователей**: статистика, управление ботами, оплата, загрузка целевой аудитории
- **Аутентификация**: вход через ВКонтакте
- **Дизайн**: гибрид Stable Diffusion + панель 3x-ui (светлая/тёмная тема)

---

## 🛠 Технологический стек

### Backend
| Компонент | Технология | Назначение |
|-----------|------------|------------|
| **API Framework** | Node.js + Express / Fastify | REST API сервер |
| **База данных** | PostgreSQL (Supabase) | Основное хранилище |
| **No-Code DB** | NocoDB | Админ-панель для данных |
| **Автоматизация** | n8n | Workflow автоматизация ботов |
| **Auth** | OAuth 2.0 (VK) | Социальная авторизация |
| **Payments** | ЮKassa / ЮMoney / CloudPayments | Платёжные шлюзы |
| **P2P Payments** | ЮMoney (физлица) | Переводы на карту/счёт |

### Frontend
| Компонент | Технология | Назначение |
|-----------|------------|------------|
| **Framework** | React 18 + TypeScript | UI библиотека |
| **UI Kit** | Material-UI / Ant Design | Компоненты интерфейса |
| **State** | Zustand / Redux Toolkit | Управление состоянием |
| **Styling** | TailwindCSS + CSS Modules | Стилизация |
| **Theme** | Custom (Stable Diffusion × 3x-ui) | Уникальный дизайн |
| **PWA** | Workbox | Android Web App |

### DevOps
| Компонент | Технология | Назначение |
|-----------|------------|------------|
| **Container** | Docker + Docker Compose | Оркестрация |
| **Secrets** | Docker Secrets / .env | Управление секретами |
| **Logs** | PM2 + Logrotate | Логгирование |

---

## 🏗 Архитектура системы

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         NGINX PROXY MANAGER                              │
│  (Reverse Proxy, SSL, Domain Routing)                                    │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
        ┌───────────────────────────┼───────────────────────────┐
        │                           │                           │
        ▼                           ▼                           ▼
┌───────────────┐          ┌───────────────┐          ┌───────────────┐
│   Frontend    │          │   Backend     │          │   NocoDB      │
│   React App   │◄────────►│   API Server  │◄────────►│   Admin Panel │
│   (Port 3000) │          │   (Port 4000) │          │   (Port 8080) │
└───────────────┘          └───────────────┘          └───────────────┘
                                    │                           │
                                    │                           │
                                    ▼                           ▼
                          ┌───────────────┐          ┌───────────────┐
                          │      n8n      │          │   Supabase    │
                          │  Automation   │          │  PostgreSQL   │
                          │  (Port 5678)  │          │   (Port 5432) │
                          └───────────────┘          └───────────────┘
                                    │
                                    ▼
                          ┌───────────────┐
                          │   VK Bot      │
                          │   Webhook     │
                          └───────────────┘
```

---



### Для администраторов (Admin Panel)

| Модуль | Функции |
|--------|---------|
| **Dashboard** | Статистика пользователей, ботов, доходов, активности |
| **Пользователи** | CRUD пользователей, роли, блокировки, логины |
| **Оплаты** | История платежей, возвраты, подписки, инвойсы |
| **Платёжные методы** | Вкл/выкл методов оплаты (ЮKassa, ЮMoney, карты), настройка ЮMoney (физлица) |
| **Боты** | Мониторинг ботов, логи, перезапуск, настройки |
| **API Keys** | Генерация API ключей (n8n, NocoDB), ротация |
| **Настройки** | Системные настройки, лимиты, тарифы |
| **Деплой** | Deploy проекта, обновления, бэкапы |
| **Суперадмин** | Регистрация суперадмина, RBAC |

### Для пользователей (User Panel)

| Модуль | Функции |
|--------|---------|
| **Dashboard** | Личная статистика, статус ботов, баланс |
| **Мои боты** | Создание, настройка, запуск, остановка ботов |
| **Целевая аудитория** | Загрузка ЦА (CSV, JSON), сегментация, таргетинг |
| **Статистика** | Аналитика ботов, графики, экспорт данных |
| **Оплата** | Пополнение баланса, история, подписки, тарифы | Доступные методы: ЮKassa, ЮMoney (P2P), карты (настраивается админом) |
| **Профиль** | Настройки аккаунта, API keys, уведомления |
| **Поддержка** | Тикеты, FAQ, чат |

### n8n Integration

| Workflow | Описание |
|----------|---------|
| **VK Bot Handler** | Обработка сообщений от пользователей ВК |
| **Auto Response** | Автоматические ответы на основе AI |
| **Payment Webhook** | Обработка webhook от платёжных систем |
| **User Onboarding** | Онбординг новых пользователей |
| **Analytics Collector** | Сбор и агрегация статистики |
| **Notification Sender** | Уведомления (email, Telegram, VK) |
| **Data Sync** | Синхронизация с NocoDB |

### NocoDB Integration

| Таблица | Описание |
|---------|---------|
| **users** | Пользователи системы |
| **bots** | Конфигурации ботов |
| **payments** | Платежи и транзакции |
| **payment_methods** | Методы оплаты (статус, настройки) |
| **yoomoney_p2p** | Настройки ЮMoney P2P (счёт, проверенные пользователи) |
| **messages** | История сообщений ботов |
| **analytics** | Метрики и статистика |
| **target_audiences** | Целевые аудитории |
| **api_keys** | API ключи пользователей |
| **logs** | Системные логи |

---

## 📁 Структура проекта

```
project-root/
├── docker-compose.yml              # Основная Docker Compose конфигурация
├── .env                            # Переменные окружения (секреты)
├── .env.example                    # Шаблон переменных окружения
├── frontend/
│   ├── package.json
│   ├── Dockerfile
│   ├── public/
│   │   └── manifest.json           # PWA manifest
│   └── src/
│       ├── components/             # React компоненты
│       │   ├── Admin/              # Admin panel компоненты
│       │   ├── User/               # User panel компоненты
│       │   ├── Common/             # Общие компоненты
│       │   └── UI/                 # UI Kit компоненты
│       ├── pages/                  # Страницы приложения
│       ├── hooks/                  # Custom React hooks
│       ├── store/                  # State management (Zustand)
│       ├── services/               # API сервисы
│       ├── themes/                 # Темы (light/dark)
│       └── utils/                  # Утилиты
├── backend/
│   ├── package.json
│   ├── Dockerfile
│   ├── src/
│   │   ├── controllers/            # Контроллеры
│   │   ├── middleware/             # Middleware (auth, validation)
│   │   ├── models/                 # Database модели
│   │   ├── routes/                 # API роуты
│   │   ├── services/               # Бизнес логика
│   │   │   ├── vk/                 # VK API сервис
│   │   │   ├── payment/            # Платёжный сервис
│   │   │   ├── auth/               # Auth сервис (VK)
│   │   │   └── n8n/                # n8n интеграция
│   │   └── utils/                  # Утилиты
│   └── tests/                      # Тесты
├── n8n/
│   ├── Dockerfile
│   ├── workflows/                  # Экспортированные workflow JSON
│   └── credentials/                # Credentials (encrypted)
├── nocodb/
│   └── Dockerfile
├── supabase/
│   ├── migrations/                 # SQL миграции
│   └── seeds/                      # Seed данные
├── scripts/
│   ├── init.sh                     # Инициализация проекта
│   ├── backup.sh                   # Бэкап БД
│   └── deploy.sh                   # Деплой скрипт
└── docs/
    ├── API.md                      # API документация
    ├── DEPLOYMENT.md               # Инструкция по деплою
    └── DEVELOPMENT.md              # Инструкция для разработчиков
```

---

## 🐳 Docker Compose конфигурация

### docker-compose.yml

```yaml
version: '3.8'

services:
  # Frontend (React)
  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile
    container_name: vk-frontend
    restart: unless-stopped
    environment:
      - NODE_ENV=production
      - VITE_API_URL=http://backend:4000
    depends_on:
      - backend
    networks:
      - vk-bot-network

  # Backend (Node.js API)
  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: vk-backend
    restart: unless-stopped
    ports:
      - "4000:4000"
    environment:
      - NODE_ENV=production
      - PORT=4000
      - DATABASE_URL=postgresql://postgres:${POSTGRES_PASSWORD}@supabase:5432/vk_bot
      - JWT_SECRET=${JWT_SECRET}
      - VK_CLIENT_ID=${VK_CLIENT_ID}
      - VK_CLIENT_SECRET=${VK_CLIENT_SECRET}
      - YOOKASSA_SHOP_ID=${YOOKASSA_SHOP_ID}
      - YOOKASSA_SECRET_KEY=${YOOKASSA_SECRET_KEY}
      - N8N_API_URL=http://n8n:5678
      - N8N_API_KEY=${N8N_API_KEY}
      - NOCODB_API_URL=http://nocodb:8080
      - NOCODB_API_KEY=${NOCODB_API_KEY}
    volumes:
      - ./backend/logs:/app/logs
    depends_on:
      - supabase
      - n8n
      - nocodb
    networks:
      - vk-bot-network

  # n8n Automation
  n8n:
    image: n8n/n8n:latest
    container_name: n8n
    restart: unless-stopped
    ports:
      - "5678:5678"
    environment:
      - N8N_HOST=${N8N_HOST}
      - N8N_PORT=5678
      - N8N_BASIC_AUTH_ACTIVE=true
      - N8N_BASIC_AUTH_USER=${N8N_BASIC_AUTH_USER}
      - N8N_BASIC_AUTH_PASSWORD=${N8N_BASIC_AUTH_PASSWORD}
      - N8N_JWT_SECRET=${N8N_JWT_SECRET}
      - DB_TYPE=postgresdb
      - DB_POSTGRESDB_HOST=supabase
      - DB_POSTGRESDB_PORT=5432
      - DB_POSTGRESDB_USER=postgres
      - DB_POSTGRESDB_PASSWORD=${POSTGRES_PASSWORD}
      - DB_POSTGRESDB_DATABASE=vk_bot
      - WEBHOOK_URL=https://${N8N_HOST}/
    volumes:
      - ./n8n/data:/home/node/.n8n
      - ./n8n/workflows:/home/node/.n8n/workflows
    depends_on:
      - supabase
    networks:
      - vk-bot-network

  # NocoDB
  nocodb:
    image: nocodb/nocodb:latest
    container_name: nocodb
    restart: unless-stopped
    ports:
      - "8080:8080"
    environment:
      - NC_DB=pg://supabase:5432?u=postgres&p=${POSTGRES_PASSWORD}&d=vk_bot
      - NC_AUTH_JWT_SECRET=${NC_JWT_SECRET}
      - NC_PUBLIC_URL=https://${NOCODB_HOST}
    volumes:
      - ./nocodb/data:/usr/app/data
    depends_on:
      - supabase
    networks:
      - vk-bot-network

  # Supabase (PostgreSQL)
  supabase:
    image: supabase/postgres:15.1.0.117
    container_name: supabase
    restart: unless-stopped
    ports:
      - "5432:5432"
    environment:
      - POSTGRES_USER=postgres
      - POSTGRES_PASSWORD=${POSTGRES_PASSWORD}
      - POSTGRES_DB=vk_bot
      - JWT_SECRET=${JWT_SECRET}
    volumes:
      - supabase_data:/var/lib/postgresql/data
      - ./supabase/migrations:/docker-entrypoint-initdb.d
    networks:
      - vk-bot-network

  # Redis (кэширование, сессии)
  redis:
    image: redis:7-alpine
    container_name: redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    networks:
      - vk-bot-network

networks:
  vk-bot-network:
    driver: bridge

volumes:
  supabase_data:
  redis_data:
```

### .env.example

```bash
# ============= DATABASE =============
POSTGRES_PASSWORD=your_super_secure_postgres_password_here
JWT_SECRET=your_jwt_secret_key_here
NC_JWT_SECRET=your_nocodb_jwt_secret_here

# ============= VK OAuth =============
VK_CLIENT_ID=your_vk_client_id
VK_CLIENT_SECRET=your_vk_client_secret
VK_REDIRECT_URI=https://yourdomain.com/auth/vk/callback

# ============= YooKassa =============
YOOKASSA_SHOP_ID=your_shop_id
YOOKASSA_SECRET_KEY=your_secret_key
YOOKASSA_WEBHOOK_URL=https://yourdomain.com/webhook/yookassa

# ============= N8N =============
N8N_HOST=n8n.yourdomain.com
N8N_BASIC_AUTH_USER=admin
N8N_BASIC_AUTH_PASSWORD=your_n8n_admin_password
N8N_JWT_SECRET=your_n8n_jwt_secret
N8N_API_KEY=your_n8n_api_key

# ============= NocoDB =============
NOCODB_HOST=nocodb.yourdomain.com
NOCODB_API_KEY=your_nocodb_api_key

# ============= Redis =============
REDIS_PASSWORD=your_redis_password

# ============= Application =============
NODE_ENV=production
FRONTEND_URL=https://yourdomain.com
BACKEND_URL=https://api.yourdomain.com
```

---

## 🔌 API спецификация

### Основные эндпоинты

#### Auth
```
POST   /api/auth/vk          - Вход через VK
POST   /api/auth/logout      - Выход
GET    /api/auth/me          - Текущий пользователь
POST   /api/auth/refresh     - Refresh token
```

#### Admin
```
GET    /api/admin/users      - Список пользователей
GET    /api/admin/users/:id  - Профиль пользователя
PUT    /api/admin/users/:id  - Обновление пользователя
DELETE /api/admin/users/:id  - Удаление пользователя
POST   /api/admin/users/:id/block  - Блокировка

GET    /api/admin/payments   - Список платежей
POST   /api/admin/payments/refund  - Возврат

GET    /api/admin/bots       - Список ботов
GET    /api/admin/bots/:id   - Профиль бота
POST   /api/admin/bots/:id/restart - Перезапуск

GET    /api/admin/analytics  - Общая статистика
POST   /api/admin/api-keys   - Генерация API ключа
POST   /api/admin/api-keys/rotate  - Ротация ключа

POST   /api/admin/deploy     - Деплой проекта
POST   /api/admin/backup     - Создание бэкапа
```

#### User
```
GET    /api/user/profile     - Профиль
PUT    /api/user/profile     - Обновление профиля

GET    /api/user/bots        - Мои боты
POST   /api/user/bots        - Создать бота
GET    /api/user/bots/:id    - Профиль бота
PUT    /api/user/bots/:id    - Обновление бота
DELETE /api/user/bots/:id    - Удаление бота
POST   /api/user/bots/:id/start   - Запуск бота
POST   /api/user/bots/:id/stop    - Остановка бота

GET    /api/user/analytics   - Статистика
GET    /api/user/analytics/export  - Экспорт статистики

GET    /api/user/payments    - История платежей
POST   /api/user/payments/create  - Создание платежа
POST   /api/user/payments/webhook - Webhook от платёжки

GET    /api/user/target-audiences  - Мои ЦА
POST   /api/user/target-audiences  - Загрузка ЦА
DELETE /api/user/target-audiences/:id  - Удаление ЦА

GET    /api/user/api-keys    - Мои API ключи
POST   /api/user/api-keys    - Генерация ключа
DELETE /api/user/api-keys/:id  - Отзыв ключа
```

#### Webhooks
```
POST   /webhook/vk           - VK Bot webhook
POST   /webhook/yookassa     - YooKassa webhook
POST   /webhook/n8n/:workflow - n8n workflow webhook
```

---

## 🔒 Безопасность

### Реализуемые меры безопасности

| Категория | Меры |
|-----------|------|
| **Authentication** | OAuth 2.0, JWT tokens, refresh tokens, 2FA (опционально) |
| **Authorization** | RBAC (roles: superadmin, admin, user), middleware guards |
| **Data Protection** | HTTPS/TLS, encryption at rest, hashed passwords (bcrypt) |
| **API Security** | Rate limiting, CORS, input validation, SQL injection prevention |
| **Session Management** | Secure cookies, httpOnly, sameSite, session timeout |
| **Logging** | Audit logs, error logging, security events |
| **Infrastructure** | Docker isolation, network segmentation, secrets management |

### Docker Secrets

```bash
# Генерация секретов
openssl rand -hex 32  # JWT_SECRET
openssl rand -hex 32  # POSTGRES_PASSWORD
openssl rand -hex 32  # N8N_JWT_SECRET
```

---

## 🗺 Дорожная карта

### Phase 1: Foundation (Недели 1-2)
- [ ] Инициализация проекта
- [ ] Docker Compose настройка
- [ ] Supabase + NocoDB деплой
- [ ] n8n деплой и базовая настройка


### Phase 2: Backend Core (Недели 3-4)
- [ ] Node.js API сервер
- [ ] OAuth (VK) интеграция
- [ ] Database schema и миграции
- [ ] JWT authentication
- [ ] Базовые CRUD эндпоинты

### Phase 3: Frontend (Недели 5-6)
- [ ] React приложение
- [ ] UI компоненты (гибрид Stable Diffusion × 3x-ui)
- [ ] Темы (light/dark)
- [ ] Admin panel
- [ ] User panel

### Phase 4: Integrations (Недели 7-8)
- [ ] n8n workflows
- [ ] VK Bot integration
- [ ] Payment gateway (ЮKassa)
- [ ] NocoDB API integration

### Phase 5: Polish & Deploy (Недели 9-10)
- [ ] E2E тесты
- [ ] Security audit
- [ ] Performance optimization
- [ ] Documentation
- [ ] Production deploy

---

## 📊 Monitoring & Analytics

### Метрики для отслеживания

- **Пользователи**: активные, новые, churn rate
- **Боты**: количество, статус, сообщения/день
- **Финансы**: выручка, средний чек, LTV
- **Производительность**: response time, uptime, error rate
- **Инфраструктура**: CPU, memory, disk usage

### Инструменты

- **Logs**: PM2 + Logrotate
- **Metrics**: Prometheus + Grafana (опционально)
- **Uptime**: Uptime Kuma (опционально)

---

## 📚 Документация

### Реализованные документы

1. ✅ `README.md` — Быстрый старт
2. ✅ `docs/API.md` — API документация
3. ✅ `docs/DEPLOYMENT.md` — Инструкция по деплою
4. ✅ `docs/YOOMONEY-P2P.md` — Интеграция ЮMoney P2P
5. ✅ `docs/QUICKSTART.md` — Быстрый старт
6. ✅ `docs/TEST-RUN-REPORT.md` — Отчёт о тестировании

### Планируются

7. `docs/DEVELOPMENT.md` — Гайд для разработчиков
8. `docs/ADMIN.md` — Руководство администратора
9. `docs/USER.md` — Руководство пользователя
10. `docs/N8N_WORKFLOWS.md` — Описание workflow n8n
11. `docs/DATABASE.md` — Схема базы данных

---

## 🎯 KPI успеха

| Метрика | Цель |
|---------|------|
| Uptime | > 99.5% |
| API Response Time | < 200ms |
| Error Rate | < 0.1% |
| User Satisfaction | > 4.5/5 |
| Bot Success Rate | > 95% |

---

## 📞 Поддержка и контакты

- **GitHub Issues**: Для багов и feature requests
- **Email**: support@yourdomain.com
- **Telegram**: @your_support_bot
- **VK Group**: vk.com/your_project

---

*Документ создан: 28 марта 2026 г.*  
*Последнее обновление: 29 марта 2026 г.*  
*Версия спецификации: 1.1.0*  
*Статус: ✅ Реализовано (Docker изоляция, Backend API, Frontend UI)*
