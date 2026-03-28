# 🚀 Инструкция по загрузке на GitHub

## Способ 1: Автоматический скрипт (рекомендуется)

```bash
cd /home/vidserv/web-vk-bot
./upload-to-github.sh
```

**Скрипт выполнит**:
1. Установку GitHub CLI
2. Авторизацию на GitHub
3. Создание репозитория
4. Загрузку файлов

---

## Способ 2: Вручную

### 1. Установите GitHub CLI

```bash
curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg | sudo dd of=/usr/share/keyrings/githubcli-archive-keyring.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages stable main" | sudo tee /etc/apt/sources.list.d/github-cli.list > /dev/null
sudo apt update
sudo apt install -y gh
```

### 2. Авторизуйтесь

```bash
gh auth login
```

Следуйте инструкциям в терминале.

### 3. Создайте репозиторий и запушьте

```bash
cd /home/vidserv/web-vk-bot
gh repo create vk-neuro-agents --public --source=. --remote=origin --push
```

---

## Способ 3: Через браузер (без CLI)

### 1. Создайте репозиторий

1. Перейдите на https://github.com/new
2. Имя репозитория: `vk-neuro-agents`
3. Выберите **Public**
4. **НЕ** инициализировать README
5. Нажмите **Create repository**

### 2. Запушьте код

```bash
cd /home/vidserv/web-vk-bot

# При пуше введите ваш GitHub логин и токен
git push -u origin main
```

**Для токена**:
1. Перейдите на https://github.com/settings/tokens
2. Generate new token (classic)
3. Выберите scope: `repo`
4. Скопируйте токен
5. Используйте его вместо пароля при пуше

---

## ✅ После загрузки

Ваш репозиторий будет доступен по адресу:

```
https://github.com/eugene-nilyason/vk-neuro-agents
```

---

## 📊 Что загрузится

- ✅ 82 файла
- ✅ ~11,800 строк кода
- ✅ Backend (Node.js + TypeScript)
- ✅ Frontend (HTML + JS)
- ✅ Docker конфигурации
- ✅ Документация (14 файлов)

---

*Выберите удобный способ и выполните команды!*
