# Удалена аутентификация через Одноклассники (OK)

**Дата**: 28 марта 2026  
**Причина**: Упрощение проекта, фокус на VK

---

## 📝 Изменения

### Удалённые упоминания OK

| Файл | Изменения |
|------|-----------|
| `PROJECT-SPEC.md` | Удалена OK OAuth из спецификации, skills, docker-compose, API endpoints |
| `.env.example` | Удалены переменные `OK_CLIENT_ID`, `OK_CLIENT_SECRET`, `OK_REDIRECT_URI` |
| `.env` | Удалены переменные OK OAuth |
| `docs/API.md` | Удалён endpoint `POST /api/auth/ok` |
| `docs/DEPLOYMENT.md` | Удалён раздел про настройку OK Developers |
| `ROADMAP.md` | Удалены задачи по OK OAuth интеграции |
| `SUMMARY.md` | Удалены упоминания OK |
| `README.md` | Обновлена информация об Auth |
| `CHANGELOG.md` | Обновлена информация об Auth |
| `.qwen/skills.json` | Удалена OK OAuth из описания skills и roles |

---

## 🔐 Оставшаяся аутентификация

Теперь поддерживается **только один провайдер**:

### ВКонтакте (VK)
- ✅ OAuth 2.0
- ✅ Автоматическая регистрация пользователя
- ✅ Получение данных профиля
- ✅ Refresh tokens

---

## 📋 Обновлённые переменные окружения

### Было:
```env
# ============= VK OAuth =============
VK_CLIENT_ID=your_vk_client_id
VK_CLIENT_SECRET=your_vk_client_secret
VK_REDIRECT_URI=https://yourdomain.com/auth/vk/callback

# ============= OK OAuth =============
OK_CLIENT_ID=your_ok_client_id
OK_CLIENT_SECRET=your_ok_client_secret
OK_REDIRECT_URI=https://yourdomain.com/auth/ok/callback
```

### Стало:
```env
# ============= VK OAuth =============
VK_CLIENT_ID=your_vk_client_id
VK_CLIENT_SECRET=your_vk_client_secret
VK_REDIRECT_URI=https://yourdomain.com/auth/vk/callback
```

---

## 📡 Обновлённые API endpoints

### Было:
```
POST   /api/auth/vk          - Вход через VK
POST   /api/auth/ok          - Вход через OK  ❌ УДАЛЕНО
POST   /api/auth/logout      - Выход
GET    /api/auth/me          - Текущий пользователь
POST   /api/auth/refresh     - Refresh token
```

### Стало:
```
POST   /api/auth/vk          - Вход через VK
POST   /api/auth/logout      - Выход
GET    /api/auth/me          - Текущий пользователь
POST   /api/auth/refresh     - Refresh token
```

---

## 🎯 Влияние на проект

### Положительное:
- ✅ Упрощение кодовой базы
- ✅ Меньше зависимостей
- ✅ Быстрее разработка
- ✅ Проще тестирование
- ✅ Меньше потенциальных багов

### Отрицательное:
- ⚠️ Пользователи не могут войти через OK
- ⚠️ Меньше охват аудитории

---

## 📊 Статистика изменений

| Изменено файлов | Удалено строк кода | Удалено endpoints |
|-----------------|-------------------|-------------------|
| 10 | ~50+ | 1 |

---

## 🔄 Миграция (если OK был нужен)

Если в будущем потребуется вернуть OK OAuth:

1. Вернуть переменные в `.env.example`
2. Добавить endpoint `POST /api/auth/ok` в backend
3. Добавить кнопку входа в frontend
4. Обновить документацию

---

## ✅ Статус

- [x] Удалены упоминания OK из документации
- [x] Удалены переменные окружения
- [x] Удалены API endpoints
- [x] Обновлены skills и roles
- [x] Сервисы перезапущены

---

*Изменение внесено: 28 марта 2026*
