# Общие Blade-компоненты интерфейса

Компоненты находятся в `resources/views/components`. Они сами обрабатывают отсутствие изображения, безопасно экранируют текст и сохраняют единый стиль.

## Аватар

```blade
<x-ui.avatar :user="$master" :size="48" />
<x-ui.avatar :user="$master" :size="58" :online="$master->is_online" />
<x-ui.avatar :name="$clientName" :src="$photoUrl" :size="40" />
```

Если фотографии нет, компонент показывает инициалы. Внешний сервис генерации аватаров не используется.

## Изображение услуги

```blade
<x-ui.service-image :service="$service" class="rounded-3 w-100" />
<x-ui.service-image :service="$service" :width="56" :height="56" class="rounded" />
```

Можно передать `eager="true"` только для изображения, которое сразу видно в первом экране. Остальные изображения загружаются лениво.

## Статус записи

```blade
<x-appointments.status :status="$appointment->status" />
```

Цвет и локализованное название выбираются компонентом. Для пустого значения выводится «Статус не указан».
