#!/bin/bash
# Установка GitHub CLI и загрузка проекта

echo "🔧 Установка GitHub CLI..."

# Добавляем репозиторий GitHub CLI
curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg | sudo dd of=/usr/share/keyrings/githubcli-archive-keyring.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages stable main" | sudo tee /etc/apt/sources.list.d/github-cli.list > /dev/null

# Обновляем и устанавливаем
sudo apt update
sudo apt install -y gh

echo ""
echo "✅ GitHub CLI установлен!"
echo ""
echo "🔐 Авторизация на GitHub..."
gh auth login

echo ""
echo "📤 Загрузка на GitHub..."
cd /home/vidserv/web-vk-bot

# Создаем репозиторий и пушим
gh repo create vk-neuro-agents --public --source=. --remote=origin --push

echo ""
echo "✅ Загрузка завершена!"
echo ""
echo "🔗 Ваш репозиторий: https://github.com/eugene-nilyason/vk-neuro-agents"
