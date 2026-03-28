# MCP Servers Configuration

Конфигурация Model Context Protocol серверов для проекта платёжного интерфейса ЮKassa.

## 📦 Установленные серверы

### Базовые серверы

| Сервер | Команда | Назначение |
|--------|---------|------------|
| **Filesystem** | `@modelcontextprotocol/server-filesystem` | Безопасная работа с файлами проекта |
| **Git** | `@modelcontextprotocol/server-git` | Управление версионированием |
| **Fetch** | `@modelcontextprotocol/server-fetch` | Получение данных из API |
| **Sequential Thinking** | `@modelcontextprotocol/server-sequential-thinking` | Решение сложных задач |
| **Memory** | `@modelcontextprotocol/server-memory` | Сохранение контекста |

### Автоматизация (аналоги n8n)

| Сервер | Команда | Назначение |
|--------|---------|------------|
| **Make** | `@modelcontextprotocol/server-make` | Сценарии автоматизации рабочих процессов |
| **Pipedream** | `mcp-server-pipedream` | Интеграция с 2500+ API |

### Базы данных (аналоги NocoDB)

| Сервер | Команда | Назначение |
|--------|---------|------------|
| **Airtable** | `@modelcontextprotocol/server-airtable` | No-code базы данных |
| **Baserow** | `mcp-server-baserow` | Open-source базы данных |
| **PostgreSQL** | `@modelcontextprotocol/server-postgresql` | Классические SQL базы (отключен) |

## 🔧 Установка

MCP серверы устанавливаются автоматически через `npx` при первом запуске.

Для предварительной установки:

```bash
# Базовые серверы
npm install -g @modelcontextprotocol/server-filesystem
npm install -g @modelcontextprotocol/server-git
npm install -g @modelcontextprotocol/server-fetch
npm install -g @modelcontextprotocol/server-sequential-thinking
npm install -g @modelcontextprotocol/server-memory

# Автоматизация (аналоги n8n)
npm install -g @modelcontextprotocol/server-make
npm install -g mcp-server-pipedream

# Базы данных (аналоги NocoDB)
npm install -g @modelcontextprotocol/server-airtable
npm install -g mcp-server-baserow
npm install -g @modelcontextprotocol/server-postgresql
```

## 📁 Расположение конфигурации

- **Глобально:** `~/.qwen/mcp.json`
- **Проект:** `.qwen/mcp.json`

## 🚀 Использование

Серверы автоматически активируются при запуске Qwen Code в директории проекта.

### Примеры использования

**Filesystem:**
- Чтение/запись файлов проекта
- Поиск по кодовой базе
- Мониторинг изменений

**Git:**
- Просмотр истории коммитов
- Создание веток
- Управление изменениями

**Fetch:**
- Получение данных из API ЮKassa
- Загрузка документации

**Make (аналог n8n):**
- Создание сценариев автоматизации
- Интеграция с внешними сервисами
- Оркестрация рабочих процессов

**Pipedream:**
- Быстрая интеграция с 2500+ API
- Триггеры и действия для вебхуков
- Обработка событий платежей

**Airtable / Baserow (аналоги NocoDB):**
- Создание таблиц для хранения транзакций
- Управление данными клиентов
- Отчётность по платежам

## 🔒 Безопасность

Filesystem сервер ограничен директорией проекта:
```
/home/vidserv/bot-reg-int
```

Доступ за пределы директории заблокирован.

## 📎 Ссылки

- [MCP Specification](https://modelcontextprotocol.io/)
- [MCP Servers Registry](https://github.com/modelcontextprotocol/servers)
- [MCP Market](https://mcpmarket.com/)
