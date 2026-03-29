# 📝 Changelog

Все изменения в проекте VK Neuro-Agents Control Panel.

---

## [1.2.0] — 29 марта 2026

### Добавлено

#### 🔐 SSL шифрование Frontend

**Новые возможности**:
- ✅ Nginx с HTTPS поддержкой (порт 443)
- ✅ TLSv1.2 / TLSv1.3 протоколы
- ✅ Strong cipher suites (ECDHE/DHE)
- ✅ Security headers (HSTS, X-Frame-Options, X-XSS-Protection)
- ✅ Gzip сжатие
- ✅ HTTP/2 поддержка

**Файлы**:
- `frontend/Dockerfile` — обновлён с SSL поддержкой
- `frontend/nginx-ssl.conf` — SSL конфигурация Nginx
- `frontend/ssl/` — SSL сертификаты

#### 🔐 SSL шифрование PostgreSQL

**Новые возможности**:
- ✅ SSL шифрование соединений
- ✅ TLSv1.2 минимум
- ✅ Strong ciphers
- ✅ Сертификаты в `supabase/ssl/`

**Файлы**:
- `supabase/Dockerfile` — кастомный образ с SSL
- `supabase/ssl-init.sh` — скрипт инициализации SSL
- `scripts/generate-ssl-certs.sh` — генерация сертификатов

#### 🔐 Let's Encrypt интеграция

**Новые возможности**:
- ✅ Автоматическое получение сертификатов
- ✅ Webroot validation
- ✅ Автообновление каждые 90 дней
- ✅ Cron конфигурация

**Файлы**:
- `scripts/get-letsencrypt-cert.sh` — получение сертификата
- `scripts/renew-letsencrypt-cert.sh` — обновление
- `scripts/letsencrypt-crontab` — cron задача
- `docs/LETSENCRYPT-SSL.md` — документация

---

## [1.1.0] — 29 марта 2026

### Изменено

#### 🧹 Удаление n8n и NocoDB

**Удалено из проекта**:
- ❌ Сервис n8n (будет использоваться отдельно)
- ❌ Сервис NocoDB (будет использоваться отдельно)
- ❌ N8N_* и NOCODB_* переменные окружения
- ❌ Упоминания в документации

**Осталось в проекте**:
- ✅ Frontend (React)
- ✅ Backend (Node.js)
- ✅ Database (PostgreSQL/Supabase)
- ✅ Cache (Redis)

---

## [1.0.0] — 28 марта 2026

### Добавлено

#### 📦 Инициализация проекта

- ✅ Полная спецификация проекта (PROJECT-SPEC.md)
- ✅ Docker Compose конфигурация (7 сервисов)
- ✅ SQL миграции (5 таблиц)
- ✅ Скрипты (init, backup, deploy, start-docker)
- ✅ Документация (README, API, DEPLOYMENT, YOOMONEY-P2P)

#### 🛠 Технологический стек

- **Frontend**: React 18 + TypeScript + TailwindCSS
- **Backend**: Node.js + Express
- **Database**: PostgreSQL (Supabase)
- **Cache**: Redis
- **DevOps**: Docker + Docker Compose

#### 📋 Функциональность

**Для администраторов**:
- Dashboard со статистикой
- Управление пользователями
- Управление оплатами
- Мониторинг ботов
- Генерация API ключей
- Деплой и бэкапы

**Для пользователей**:
- Личный dashboard
- Управление ботами
- Загрузка ЦА
- Статистика
- Оплата и баланс

---

*Формат: Keep a Changelog*  
*Версия: SemVer*
