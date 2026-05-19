# GoodFeet — Laravel 12 + Vite + Docker

## Стек
- **PHP** 8.2 + Laravel 12
- **MySQL** 8.0
- **Nginx**
- **Vite** (сборка JS/CSS)
- **Docker** + Docker Compose

---

## Структура Docker

```
docker_s/
├── docker-compose.yml   — сервисы: app, nginx, db
├── Dockerfile           — PHP 8.2-fpm + composer + npm
├── nginx.conf           — конфиг веб-сервера
├── php.ini              — лимиты PHP
├── .env.docker          — .env для локальной разработки
└── db/
    └── goodfeet.sql     — дамп базы данных (не коммитить!)
```

---

## Первый запуск (с нуля)

### 1. Клонируй репозиторий
```bash
git clone git@github.com:nad-trssv/goodfeetprod.git
cd goodfeetprod
```

### 2. Скопируй .env для локалки
```powershell
copy docker_s\.env.docker .env
```

### 3. Положи дамп базы в папку
```
docker_s/db/goodfeet.sql
```
> MySQL автоматически выполнит его при первом запуске контейнера.

### 4. Запусти Docker Desktop
Убедись что Docker Desktop запущен (иконка кита в трее).

### 5. Собери и запусти контейнеры
```powershell
cd docker_s
docker-compose up --build
```
> Первый раз занимает 3–5 минут — скачивает образы и устанавливает зависимости.

### 6. Открой сайт
```
http://localhost:8080
```

---

## Повторный запуск (уже собрано)

```powershell
cd docker_s
docker-compose up
```

Остановить:
```powershell
docker-compose down
```

---

## Полезные команды

```powershell
# Зайти внутрь контейнера
docker exec -it goodfeet_app bash

# Artisan команды
docker exec goodfeet_app php artisan migrate
docker exec goodfeet_app php artisan cache:clear
docker exec goodfeet_app php artisan storage:link

# Пересобрать фронтенд
docker exec goodfeet_app npm run build

# Логи
docker-compose logs -f app
docker-compose logs -f db
```

---

## Подключение к базе данных локально

| Параметр | Значение |
|----------|----------|
| Host | `127.0.0.1` |
| Port | `3307` |
| Database | `goodfeet` |
| Username | `goodfeet` |
| Password | `goodfeet` |

> Можно подключиться через TablePlus, DBeaver или любой другой клиент.

---

## Сброс базы данных

Если нужно залить дамп заново:
```powershell
# Удалить volume с данными
docker-compose down -v

# Положи свежий .sql в docker_s/db/
# Запустить снова
docker-compose up --build
```

---

## .env файлы

| Файл | Назначение |
|------|-----------|
| `.env` | Текущий активный конфиг (не коммитить!) |
| `docker_s/.env.docker` | Шаблон для локальной разработки |

> `.env` добавлен в `.gitignore` — никогда не попадёт в репозиторий.

---

## Продакшн

Сайт развёрнут на: **https://goodfeet.ee**  
БД на хостинге: `d137494.mysql.zonevs.eu`
