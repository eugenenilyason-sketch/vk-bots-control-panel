# 📋 Настройка цепочки NPM: Internet → NPM (Edge) → NPM (Dev) → Сайт

**Сценарий**: Двухуровневая архитектура с внешним и внутренним Nginx Proxy Manager

---

## 🎯 Когда это нужно

Этот сценарий подходит если:
- ✅ У вас есть DMZ (демилитаризованная зона)
- ✅ Сайт находится во внутренней сети
- ✅ Требуется дополнительный уровень безопасности
- ✅ Нужно разделение внешнего и внутреннего трафика

---

## 🏗 Архитектура и термины

```
┌─────────────┐      HTTPS 443      ┌─────────────┐      HTTP 80      ┌─────────────┐      HTTP      ┌─────────────┐
│   Internet  │ ──────────────────► │  NPM Edge   │ ──────────────────► │  NPM Dev    │ ────────────► │    Сайт     │
│             │                     │ (Внешний)   │                     │ (Внутренний)│                │  (Port 3000)│
└─────────────┘                     │ 192.168.1.10│                     │ 192.168.1.20│                │             │
                                    └─────────────┘                     └─────────────┘                └─────────────┘
                                           │                                    │
                                           │                                    │
                                    SSL Let's Encrypt                     SSL: None (HTTP)
                                    Порт 443 (HTTPS)                      Порт 80 (HTTP)
```

### Компоненты

| Компонент | Назначение | SSL | Порт |
|-----------|------------|-----|------|
| **NPM Edge (Внешний)** | Терминация SSL, первый уровень | ✅ Let's Encrypt | 443 |
| **NPM Dev (Внутренний)** | Проксирование на сайт | ❌ None | 80 |
| **Сайт** | Приложение | - | 3000 |

---

## 📋 Предварительные требования

### 1. Сетевая конфигурация

**Статические IP адреса**:
```bash
# Внешний NPM (Edge)
IP: 192.168.1.10
Порты: 80, 81 (админка), 443

# Внутренний NPM (Dev)
IP: 192.168.1.20
Порты: 80, 81 (админка), 443

# Сайт (Docker контейнер)
IP: 192.168.1.30 (или имя контейнера)
Порт: 3000
```

### 2. Домен и DNS

```bash
# DNS запись (A record)
dev.example.com  →  Публичный IP Внешнего NPM

# Пример для Cloudflare:
Type: A
Name: dev
Content: 203.0.113.10  # Публичный IP
Proxy: DNS only (серый облако)
```

### 3. Проверка доступности

```bash
# С внешнего NPM проверьте доступность внутреннего
ping 192.168.1.20
curl -H "Host: dev.example.com" http://192.168.1.20

# Проверка порта 80 (не 81!)
telnet 192.168.1.20 80
nc -zv 192.168.1.20 80
```

---

## 🔧 Шаг 1: Настройка Внутреннего NPM (Dev)

**Логин**: `http://192.168.1.20:81`

### 1.1 Создание Proxy Host

1. Перейдите в **Hosts** → **Proxy Hosts** → **Add Proxy Host**

2. **Basic Settings**:
   ```
   Domain Names: dev.example.com
   Forward Hostname/IP: 192.168.1.30  # IP сайта
   Forward Port: 3000
   Cache Assets: ☑ Включить
   Block Common Exploits: ☑ Включить
   Websockets Support: ☑ Включить
   ```

3. **SSL Tab**:
   ```
   SSL Certificate: None
   Force SSL: ☐ Выключить
   HTTP/2 Support: ☐ Выключить
   ```

4. **Advanced Tab** (опционально, для безопасности):
   ```nginx
   # Разрешить только с IP внешнего NPM
   allow 192.168.1.10;
   deny all;
   
   # Заголовки для корректной передачи протокола
   proxy_set_header X-Forwarded-Proto $scheme;
   proxy_set_header X-Forwarded-SSL $ssl_protocol;
   proxy_set_header X-Forwarded-Host $host;
   proxy_set_header X-Forwarded-Port $server_port;
   ```

5. Нажмите **Save**

### 1.2 Проверка внутреннего NPM

```bash
# С любого устройства в локальной сети
curl -H "Host: dev.example.com" http://192.168.1.20

# Должен вернуться HTML сайта
```

---

## 🔧 Шаг 2: Настройка Внешнего NPM (Edge)

**Логин**: `http://192.168.1.10:81`

### 2.1 Создание Proxy Host

1. Перейдите в **Hosts** → **Proxy Hosts** → **Add Proxy Host**

2. **Basic Settings**:
   ```
   Domain Names: dev.example.com
   Forward Hostname/IP: 192.168.1.20  # IP внутреннего NPM
   Forward Port: 80  # Порт прокси (НЕ 81!)
   Cache Assets: ☑ Включить
   Block Common Exploits: ☑ Включить
   Websockets Support: ☑ Включить
   ```

3. **SSL Tab**:
   ```
   SSL Certificate: Request a new SSL Certificate
   Force SSL: ☑ Включить (Redirect HTTP to HTTPS)
   HTTP/2 Support: ☑ Включить
   HSTS Enabled: ☑ Включить (опционально)
   ```

4. **Advanced Tab**:
   ```nginx
   # Заголовки для передачи информации о протоколе
   proxy_set_header X-Forwarded-Proto $scheme;
   proxy_set_header X-Forwarded-SSL $ssl_protocol;
   proxy_set_header X-Forwarded-Host $host;
   proxy_set_header X-Forwarded-Port $server_port;
   
   # Таймауты для долгих запросов
   proxy_connect_timeout 60s;
   proxy_send_timeout 60s;
   proxy_read_timeout 60s;
   ```

5. Нажмите **Save**

### 2.2 Проверка внешнего NPM

```bash
# Проверка HTTPS
curl -I https://dev.example.com

# Ожидаемый ответ:
# HTTP/2 200
# server: nginx
# ...
```

---

## 🔧 Шаг 3: Настройка сайта (приложение)

### 3.1 Для Node.js / Express

```javascript
// Доверие прокси-заголовкам
app.set('trust proxy', 1);

// Middleware для проверки HTTPS
app.use((req, res, next) => {
  if (req.headers['x-forwarded-proto'] !== 'https') {
    return res.redirect(`https://${req.headers.host}${req.url}`);
  }
  next();
});
```

### 3.2 Для React / Vite (frontend)

```javascript
// vite.config.ts
export default defineConfig({
  server: {
    proxy: {
      '/api': {
        target: 'http://backend:4000',
        changeOrigin: true,
      }
    }
  }
});
```

### 3.3 Для PHP / WordPress

```php
// wp-config.php
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 
    $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// Или в .htaccess
RewriteEngine On
RewriteCond %{HTTP:X-Forwarded-Proto} !https
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
```

---

## 🐛 Решение проблем

### Проблема 1: ERR_TOO_MANY_REDIRECTS

**Симптом**: Браузер показывает цикл перенаправлений

**Причина**: Сайт считает соединение HTTP и редиректит на HTTPS

**Решение**:

1. **На Внутреннем NPM** (Advanced):
   ```nginx
   proxy_set_header X-Forwarded-Proto $scheme;
   ```

2. **На сайте** (Node.js пример):
   ```javascript
   app.set('trust proxy', 1);
   ```

3. **Проверка заголовков**:
   ```bash
   curl -I https://dev.example.com | grep -i x-forwarded
   # Должно быть: X-Forwarded-Proto: https
   ```

---

### Проблема 2: 502 Bad Gateway

**Симптом**: Внешний NPM не может подключиться к внутреннему

**Причина**: Неправильный порт или фаервол

**Решение**:

1. **Проверьте порт** (должен быть 80, не 81!):
   ```bash
   # На сервере внутреннего NPM
   netstat -tlnp | grep :80
   ```

2. **Проверьте фаервол**:
   ```bash
   # На сервере внутреннего NPM
   sudo ufw allow from 192.168.1.10 to any port 80
   sudo ufw status
   ```

3. **Проверьте доступность**:
   ```bash
   # С внешнего NPM
   curl -H "Host: dev.example.com" http://192.168.1.20
   ```

---

### Проблема 3: SSL сертификат не выпускается

**Симптом**: Let's Encrypt validation failed

**Причина**: DNS или фаервол блокируют проверку

**Решение**:

1. **Проверьте DNS**:
   ```bash
   nslookup dev.example.com
   # Должен вернуть публичный IP внешнего NPM
   ```

2. **Откройте порт 80 для проверки**:
   ```bash
   # На внешнем NPM
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   ```

3. **Повторите выпуск сертификата** в интерфейсе NPM

---

### Проблема 4: Не виден реальный IP клиента

**Симптом**: В логах только IP внешнего NPM

**Решение**:

1. **Проверьте заголовок X-Forwarded-For**:
   ```bash
   curl -I https://dev.example.com
   ```

2. **На сайте используйте заголовок**:
   ```javascript
   const clientIP = req.headers['x-forwarded-for']?.split(',')[0] || req.ip;
   ```

---

## 🔒 Безопасность

### 1. Ограничение доступа к внутреннему NPM

**На Внутреннем NPM** (Advanced):
```nginx
# Разрешить только с внешнего NPM
allow 192.168.1.10;
deny all;

# Логирование заблокированных запросов
access_log /var/log/nginx/blocked.log;
```

### 2. Security заголовки

**На Внешнем NPM** (Advanced):
```nginx
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```

### 3. Rate Limiting

**На Внешнем NPM** (Advanced):
```nginx
limit_req_zone $binary_remote_addr zone=one:10m rate=10r/s;

server {
  location / {
    limit_req zone=one burst=20 nodelay;
    # ... остальные настройки
  }
}
```

---

## 📊 Мониторинг и логи

### Просмотр логов

```bash
# Внешний NPM
docker logs npm --tail=100

# Внутренний NPM
docker logs npm-dev --tail=100

# В реальном времени
docker logs -f npm
```

### Метрики для отслеживания

| Метрика | Норма | Критично |
|---------|-------|----------|
| Response Time | < 200ms | > 1000ms |
| Error Rate | < 0.1% | > 1% |
| SSL Expiry | > 30 дней | < 7 дней |
| Uptime | > 99.5% | < 95% |

---

## ✅ Чек-лист успешной настройки

- [ ] DNS запись указывает на публичный IP внешнего NPM
- [ ] Внешний NPM имеет SSL сертификат Let's Encrypt
- [ ] Внутренний NPM настроен на SSL: None
- [ ] Forward Port внутреннего NPM = 80 (не 81!)
- [ ] Сайт доступен по HTTPS через внешний NPM
- [ ] Нет цикла перенаправлений
- [ ] Заголовок X-Forwarded-Proto передается корректно
- [ ] Фаерволы настроены правильно
- [ ] Логи показывают реальные IP клиентов

---

## 📞 Поддержка

При возникновении проблем:

1. Проверьте логи: `docker logs npm --tail=100`
2. Проверьте доступность: `curl -I https://dev.example.com`
3. Проверьте заголовки: `curl -I https://dev.example.com | grep -i forwarded`

---

*Версия: 1.0 | Дата: 29 марта 2026*
