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
---

## Журнал действий и уведомления интерфейса

В проекте используются два разных механизма:

1. **Activity Log** — постоянный журнал действий пользователей в базе данных.
2. **Flash alerts** — временные сообщения в интерфейсе после выполнения запроса.

Они решают разные задачи и не заменяют друг друга.

---

### Журнал действий Activity Log

Журнал используется для сохранения важных действий мастеров, администраторов и системы.

Примеры:

- услуга подключена;
- услуга отключена;
- индивидуальные настройки услуги изменены;
- запись перенесена;
- рабочее расписание изменено;
- произошла системная ошибка.

Класс журнала:

```php
use App\Support\ActivityLog;
```

#### Типы записей

```php
ActivityLog::TYPE_INFO;     // 1 — информация
ActivityLog::TYPE_WARNING;  // 2 — предупреждение
ActivityLog::TYPE_ERROR;    // 3 — ошибка
```

Рекомендуется использовать константы, а не передавать числа напрямую.

---

### Информационная запись

```php
use App\Support\ActivityLog;

ActivityLog::make(
    event: 'master_service.enabled',
    message: 'Услуга подключена',
    module: 'services',
    type: ActivityLog::TYPE_INFO,
    subject: $service,
    actor: $user
);
```

В журнале будет сохранено:

```text
Тип: 1
Мастер: имя пользователя
Действие: Услуга подключена
Раздел: services
Объект: ID услуги
Дата: дата и время создания записи
```

Поле `event` является внутренним техническим кодом события. Оно хранится в базе данных, но не обязательно отображается в таблице журнала.

---

### Запись с дополнительными данными

В `properties` можно передать старые и новые значения:

```php
ActivityLog::make(
    event: 'master_service.settings_updated',
    message: 'Индивидуальные настройки услуги изменены',
    module: 'services',
    type: ActivityLog::TYPE_INFO,
    subject: $service,
    actor: $user,
    properties: [
        'before' => [
            'price_override' => null,
            'duration_minutes_override' => null,
        ],
        'after' => [
            'price_override' => 35,
            'duration_minutes_override' => 60,
        ],
    ]
);
```

Поле `properties` хранится в формате JSON и предназначено для подробностей события.

---

### Предупреждение

Тип `2` используется для значимых ситуаций, которые не являются системной ошибкой.

```php
ActivityLog::make(
    event: 'appointment.possible_conflict',
    message: 'Обнаружено возможное пересечение записей',
    module: 'appointments',
    type: ActivityLog::TYPE_WARNING,
    subject: $appointment,
    actor: $user
);
```

Не рекомендуется записывать в журнал обычные ошибки заполнения формы, например:

```text
Поле цены обязательно
Продолжительность должна быть больше нуля
Неверный формат даты
```

Такие ошибки обрабатываются стандартной валидацией Laravel.

---

### Запись ошибки

Тип `3` используется при системной ошибке или исключении.

```php
try {
    // Изменение данных.
} catch (\Throwable $exception) {
    ActivityLog::make(
        event: 'master_service.settings_update_failed',
        message: 'Не удалось изменить индивидуальные настройки услуги',
        module: 'services',
        type: ActivityLog::TYPE_ERROR,
        subject: $service,
        actor: $user,
        properties: [
            'exception_class' => $exception::class,
            'error' => $exception->getMessage(),
        ]
    );

    report($exception);

    return back()->with(
        'error',
        'Не удалось сохранить настройки услуги.'
    );
}
```

Здесь используются два журнала:

- `ActivityLog::make()` создаёт понятную запись для администратора;
- `report($exception)` сохраняет техническую информацию в журнал Laravel.

Технический журнал Laravel находится в:

```text
storage/logs/laravel.log
```

---

### Запись журнала и транзакции

Лог ошибки нужно создавать после отката основной транзакции.

Правильно:

```php
try {
    DB::transaction(function () {
        // Сохранение данных.
    });
} catch (\Throwable $exception) {
    ActivityLog::make(
        event: 'operation.failed',
        message: 'Не удалось выполнить операцию',
        module: 'services',
        type: ActivityLog::TYPE_ERROR,
        properties: [
            'error' => $exception->getMessage(),
        ]
    );

    report($exception);
}
```

Если создать лог ошибки внутри той же транзакции, он также может быть отменён при `rollback`.

Успешный лог также рекомендуется создавать после завершения основной транзакции, чтобы ошибка журнала не откатывала успешно сохранённые данные.

---

### Системная запись без модели

Параметр `subject` необязателен:

```php
ActivityLog::make(
    event: 'system.cache_cleared',
    message: 'Кэш приложения очищен',
    module: 'system',
    type: ActivityLog::TYPE_INFO,
    actor: $user
);
```

---

### Модель в параметре subject

В `subject` можно передать любую Eloquent-модель:

```php
subject: $service
```

```php
subject: $appointment
```

```php
subject: $room
```

Из модели автоматически будет сохранён её ID.

Пример:

```php
ActivityLog::make(
    event: 'appointment.rescheduled',
    message: 'Запись перенесена',
    module: 'appointments',
    type: ActivityLog::TYPE_INFO,
    subject: $appointment,
    actor: $user
);
```

---

## Flash alerts

Flash alerts — это временные сообщения, которые показываются пользователю после перенаправления.

Они не сохраняются в таблице `activity_logs`.

### Успешное действие

```php
return back()->with(
    'success',
    'Настройки услуги сохранены.'
);
```

После перенаправления в сессии будет доступно:

```php
session('success')
```

---

### Ошибка

```php
return back()->with(
    'error',
    'Не удалось сохранить настройки услуги.'
);
```

---

### Предупреждение

```php
return back()->with(
    'warning',
    'Изменения сохранены, но некоторые параметры требуют проверки.'
);
```

---

### Информационное сообщение

```php
return back()->with(
    'info',
    'Настройки услуги не изменились.'
);
```

---

### Возврат введённых данных

При ошибке сохранения можно вернуть заполненные поля формы:

```php
return back()
    ->withInput()
    ->with(
        'error',
        'Не удалось сохранить настройки услуги.'
    );
```

В Blade старые значения доступны через:

```blade
{{ old('price_override') }}
```

---

## Вывод alert-сообщений в Blade

В основном layout или в шаблоне страницы должен быть блок вывода сообщений:

```blade
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Закрыть"
        ></button>
    </div>
@endif

@if (session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        {{ session('info') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Закрыть"
        ></button>
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('warning') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Закрыть"
        ></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Закрыть"
        ></button>
    </div>
@endif
```

---

### Ошибки валидации Laravel

Ошибки формы выводятся отдельно:

```blade
@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

В контроллере используется стандартная валидация:

```php
$validated = $request->validate([
    'price' => [
        'required',
        'numeric',
        'min:0',
    ],
]);
```

При ошибке Laravel автоматически:

- вернёт пользователя на предыдущую страницу;
- передаст ошибки в `$errors`;
- сохранит введённые значения для `old()`.

---

## Совместное использование журнала и alert

После успешной операции обычно используются оба механизма:

```php
ActivityLog::make(
    event: 'master_service.settings_updated',
    message: 'Индивидуальные настройки услуги изменены',
    module: 'services',
    type: ActivityLog::TYPE_INFO,
    subject: $service,
    actor: $user
);

return back()->with(
    'success',
    'Настройки услуги сохранены.'
);
```

Разница:

```text
ActivityLog — постоянная история для администратора.
Flash alert — одноразовое сообщение текущему пользователю.
```

---

## Рекомендации по именованию

Техническое событие:

```text
master_service.enabled
master_service.disabled
master_service.settings_updated
master_service.settings_update_failed
appointment.created
appointment.rescheduled
schedule.updated
```

Сообщение для таблицы журнала:

```text
Услуга подключена
Услуга отключена
Индивидуальные настройки услуги изменены
Не удалось изменить индивидуальные настройки услуги
Запись создана
Запись перенесена
Рабочее расписание изменено
```

`event` используется приложением, а `message` показывается человеку.

---