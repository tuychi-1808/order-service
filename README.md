# Order Service

Небольшой backend-сервис обработки заказов на PHP 8.2.

Проект реализован с использованием DDD-подхода, PostgreSQL, RabbitMQ и Docker.  
После создания заказа система сохраняет его в базе данных, публикует событие в RabbitMQ и фоновый worker асинхронно обновляет статус заказа.

## Стек

- PHP 8.2
- PostgreSQL
- RabbitMQ
- Docker
- Nginx
- PSR-4
- DDD

## Запуск проекта

### 1. Запустить контейнеры

```bash
docker compose up -d --build
```

### 2. Установить зависимости

```bash
docker compose exec php composer install
```

### 3. Применить миграции

```bash
docker compose exec -T postgres psql -U orders_user -d orders < migrations/001_create_orders_tables.sql
```

## API

Создать заказ:

```http
POST /orders
```

Получить список заказов:

```http
GET /orders
```

Получить заказ по id:

```http
GET /orders/{id}
```