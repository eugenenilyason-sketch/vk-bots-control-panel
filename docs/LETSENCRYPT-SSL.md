# 🔐 Let's Encrypt SSL Certificate Guide

Руководство по получению и установке SSL сертификата Let's Encrypt для PostgreSQL.

---

## 📋 Требования

1. **Доменное имя**, указывающее на ваш сервер
2. **Открытый порт 80** для проверки домена
3. **certbot** установлен на сервере
4. **Nginx/Apache** или любой веб-сервер для проверки

---

## 🚀 Быстрый старт

### 1. Установка certbot

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install -y certbot

# CentOS/RHEL
sudo yum install -y certbot
```

### 2. Получение сертификата

```bash
cd project-root

# Запуск скрипта
./scripts/get-letsencrypt-cert.sh yourdomain.com email@example.com
```

**Пример**:
```bash
./scripts/get-letsencrypt-cert.sh example.com admin@example.com
```

### 3. Проверка

```bash
# Проверка SSL в PostgreSQL
docker exec supabase psql -U postgres -d vk_bot -c "SHOW ssl;"
# Должно вернуть: on
```

---

## 📝 Пошаговая инструкция

### Шаг 1: Подготовка домена

Убедитесь, что домен указывает на ваш сервер:

```bash
ping yourdomain.com
# Должен вернуть IP вашего сервера
```

### Шаг 2: Откройте порт 80

```bash
# Для UFW
sudo ufw allow 80/tcp

# Для iptables
sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT
```

### Шаг 3: Создайте директорию для проверки

```bash
mkdir -p /var/www/letsencrypt
```

### Шаг 4: Запустите скрипт

```bash
./scripts/get-letsencrypt-cert.sh yourdomain.com email@example.com
```

Скрипт автоматически:
1. ✅ Проверит наличие certbot
2. ✅ Получит сертификат Let's Encrypt
3. ✅ Скопирует сертификаты в `supabase/ssl/`
4. ✅ Установит правильные права
5. ✅ Пересоберёт образ PostgreSQL
6. ✅ Перезапустит PostgreSQL
7. ✅ Проверит SSL

---

## 🔄 Автоматическое обновление

Сертификат Let's Encrypt действителен **90 дней**.

### Настройка cron

```bash
# Установка crontab
crontab scripts/scripts/letsencrypt-crontab

# Проверка
crontab -l
```

### Ручное обновление

```bash
./scripts/renew-letsencrypt-cert.sh yourdomain.com
```

---

## 📁 Структура файлов

```
scripts/
├── get-letsencrypt-cert.sh      # Получение сертификата
├── renew-letsencrypt-cert.sh    # Обновление сертификата
├── letsencrypt-crontab          # Crontab для автообновления
└── generate-ssl-certs.sh        # Генерация самоподписанных сертификатов
```

---

## 🔍 Проверка сертификата

### Проверка срока действия

```bash
sudo certbot certificates
```

### Проверка SSL в PostgreSQL

```bash
docker exec supabase psql -U postgres -d vk_bot -c "SHOW ssl;"
docker exec supabase psql -U postgres -d vk_bot \
  -c "SELECT name, setting FROM pg_settings WHERE name LIKE 'ssl%';"
```

### Проверка подключения с SSL

```bash
docker exec -it supabase psql -U postgres -d vk_bot \
  -c "SELECT * FROM pg_stat_ssl WHERE pid = pg_backend_pid();"
```

---

## 🛠️ Решение проблем

### Ошибка: "Failed authorization procedure"

**Причина**: Домен не указывает на сервер или порт 80 закрыт.

**Решение**:
```bash
# Проверка DNS
nslookup yourdomain.com

# Проверка порта
sudo netstat -tlnp | grep :80
```

### Ошибка: "Certificate already exists"

**Решение**:
```bash
# Удаление старого сертификата
sudo certbot delete --cert-name yourdomain.com

# Получение нового
./scripts/get-letsencrypt-cert.sh yourdomain.com email@example.com
```

### PostgreSQL не запускается после обновления

**Проверка логов**:
```bash
docker logs supabase --tail 50
```

**Проверка прав**:
```bash
ls -la supabase/ssl/
# server.key должен иметь права 600
# server.crt должен иметь права 644
```

---

## 📊 Сравнение сертификатов

| Тип | Срок действия | Стоимость | Доверие |
|-----|---------------|-----------|---------|
| **Let's Encrypt** | 90 дней | Бесплатно | ✅ Доверенный CA |
| **Самоподписанный** | 365 дней | Бесплатно | ❌ Не доверенный |
| **Коммерческий** | 1-2 года | Платно | ✅ Доверенный CA |

---

## 🔐 Безопасность

### Рекомендуемые настройки

- ✅ **TLSv1.2** минимум
- ✅ **4096-bit** RSA ключ
- ✅ **Strong ciphers** (HIGH:MEDIUM:+3DES:!aNULL)

### Обновление безопасности

```bash
# Регулярное обновление certbot
sudo apt-get update && sudo apt-get upgrade certbot

# Проверка конфигурации SSL
sudo certbot certificates
```

---

## 📞 Поддержка

При возникновении проблем:

1. Проверьте логи certbot: `/var/log/letsencrypt/letsencrypt.log`
2. Проверьте логи PostgreSQL: `docker logs supabase`
3. Убедитесь, что домен доступен из интернета

---

*Версия: 1.0 | Дата: 29 марта 2026*
