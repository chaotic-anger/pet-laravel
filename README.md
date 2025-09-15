# pet-laravel

Небольшой проект Laravel 12 + React Starter Kit с API постов, комментариями и их рейтингом.

## Развёртывание

В проекте уже присутствует готовое Docker-окружение для разработки.

Корневой `compose.yml` подтягивает `docker/compose.base.yml`. В набор для разработки входят:

- PHP 8.3 (сборка на основе https://github.com/mlocati/docker-php-extension-installer)
    - Composer
    - Xdebug
    - NPM
- MySQL 8
- Nginx

Если требуется своя связка или переопределение каких-то сервисов, то вы можете создать `compose.override.yml` и описать
своё окружение - вызов `docker compose` автоматически подхватит override-файл вместо основного.

### Инструкция

1) Скопировать `.env.example` в `.env` (раскомментировать `DB_*`)
2) Запуск контейнеров: `docker compose up -d`
3) Миграции: `docker compose exec -it php php artisan migrate`

- `--seed` для заполнения первичных данных.
- `migrate:fresh` для пересоздания БД.

4) Фронт: `docker compose exec -it php npm install` и `docker compose exec -it php npm run build`
5) Тесты: `docker compose exec -it php php artisan test`

## Backend

Родное от стартового проекта почти не тронуто, исключения будут упомянуты ниже.

- `App\Api` - Контекст API, собран по принципу bounded context (насколько Laravel это вообще позволяет).
- `bootstrap/*`
    - выделены и настроены маршруты для API
    - добавлена простенькая реализация API Problem
    - в AppServiceProvider настройка политик и событий
- `routes/api.php` - маршруты API

## Frontend

- `resources/js/lib/api.ts` - API-клиент на axios
- `resources/js/components/app-posts.tsx` - компонент списка постов с пагинацией
- `resources/js/components/app-post-detail.tsx` - компонент детальной поста и комментарии

### Frontend - Известные проблемы

- Нет редактирования постов и комментариев
- Не отображается текущий голос пользователя в комментарии
