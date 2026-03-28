# 📤 Инструкция по загрузке на GitHub

## Вариант 1: Через GitHub CLI (рекомендуется)

### 1. Установите GitHub CLI (если не установлен)

```bash
# Ubuntu/Debian
curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg | sudo dd of=/usr/share/keyrings/githubcli-archive-keyring.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages stable main" | sudo tee /etc/apt/sources.list.d/github-cli.list > /dev/null
sudo apt update
sudo apt install gh -y

# Проверка установки
gh --version
```

### 2. Авторизуйтесь на GitHub

```bash
gh auth login
```

Следуйте инструкциям:
1. Выберите **GitHub.com**
2. Выберите **HTTPS**
3. Нажмите **Login with a browser**
4. Подтвердите код в браузере

### 3. Создайте репозиторий и запушьте

```bash
# Создайте публичный репозиторий
gh repo create vk-neuro-agents --public --source=. --remote=origin --push

# Или приватный
gh repo create vk-neuro-agents --private --source=. --remote=origin --push
```

---

## Вариант 2: Через веб-интерфейс GitHub

### 1. Создайте репозиторий на GitHub

1. Перейдите на https://github.com/new
2. Введите имя репозитория: `vk-neuro-agents`
3. Выберите **Public** или **Private**
4. **НЕ** нажимайте "Initialize this repository with a README"
5. Нажмите **Create repository**

### 2. Добавьте remote и запушьте

```bash
# Добавьте remote (замените USERNAME на ваш логин GitHub)
git remote add origin https://github.com/USERNAME/vk-neuro-agents.git

# Проверьте remote
git remote -v

# Запушьте код
git push -u origin main
```

---

## Вариант 3: Через SSH (если настроен SSH ключ)

### 1. Создайте репозиторий на GitHub

https://github.com/new

### 2. Добавьте SSH remote

```bash
# Добавьте remote (замените USERNAME на ваш логин GitHub)
git remote add origin git@github.com:USERNAME/vk-neuro-agents.git

# Запушьте
git push -u origin main
```

---

## 📋 Проверка после пуша

После загрузки проверьте на GitHub:

1. ✅ Все файлы на месте
2. ✅ README.md отображается корректно
3. ✅ .gitignore работает (нет node_modules, .env)
4. ✅ История коммитов видна

---

## 🔧 Дополнительные команды

### Изменить описание репозитория

```bash
gh repo edit --description "VK Neuro-Agents Control Panel - Система управления нейро-агентами ВКонтакте"
```

### Добавить темы (topics)

```bash
gh repo edit --add-topic vk-bot
gh repo edit --add-topic neuro-agents
gh repo edit --add-topic n8n
gh repo edit --add-topic nocodb
gh repo edit --add-topic docker
```

### Создать релиз

```bash
gh release create v1.0.0 --title "Version 1.0.0" --notes "Initial release"
```

---

## 📊 Структура репозитория

После загрузки структура будет такой:

```
vk-neuro-agents/
├── .env.example              # Шаблон переменных окружения
├── .gitignore               # Git ignore
├── README.md                # Главная документация
├── PROJECT-SPEC.md          # Спецификация проекта
├── ROADMAP.md               # Дорожная карта
├── CHANGELOG.md             # История изменений
├── docker-compose.yml       # Docker Compose
├── backend/                 # Backend код
│   ├── src/
│   ├── prisma/
│   ├── package.json
│   └── Dockerfile
├── frontend/                # Frontend код
│   ├── public/
│   ├── server.js
│   ├── package.json
│   └── Dockerfile
├── docs/                    # Документация
│   ├── NPM-SETUP.md        # Настройка Nginx Proxy Manager
│   ├── API.md              # API документация
│   ├── DEPLOYMENT.md       # Инструкция по деплою
│   ├── YOOMONEY-P2P.md     # ЮMoney интеграция
│   └── ...
├── nginx/                   # Nginx конфигурация
│   └── app.conf
├── scripts/                 # Скрипты
│   ├── init.sh
│   ├── backup.sh
│   └── deploy.sh
└── supabase/               # Миграции БД
    └── migrations/
        └── 000_init.sql
```

---

## 🎯 Ссылка на репозиторий

После создания репозиторий будет доступен по адресу:

```
https://github.com/USERNAME/vk-neuro-agents
```

---

## 📝 Пример README для GitHub

Ваш README.md уже содержит всю необходимую информацию:

- ✅ Описание проекта
- ✅ Быстрый старт
- ✅ Документация
- ✅ Технологический стек
- ✅ Инструкция по настройке Nginx Proxy Manager

---

## 🔐 Security рекомендации

Перед публикацией убедитесь:

1. ✅ `.env` файл НЕ загружен (в .gitignore)
2. ✅ Нет секретов в коде (API ключи, пароли)
3. ✅ `.env.example` содержит только шаблонные значения
4. ✅ `node_modules` исключены из репозитория

---

*Инструкция создана: 28 марта 2026*
