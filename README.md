# Symfony 5.4 Core

> Унифицированное микроядро на базе Symfony 5.4 для быстрого построения REST API с гибридным хранением данных (MySQL + MongoDB), встроенным CRUD-конвейером, событийной моделью, аудитом, мягким удалением, RBAC-аутентификацией и инфраструктурой сервисного слоя.

---

## 📑 Содержание

1. [Назначение и философия](#-назначение-и-философия)
2. [Ключевые возможности](#-ключевые-возможности)
3. [Технологический стек](#-технологический-стек)
4. [Системные требования](#-системные-требования)
5. [Установка и запуск](#-установка-и-запуск)
6. [Архитектура ядра](#-архитектура-ядра)
7. [Описание структуры проекта](#-описание-структуры-проекта)
8. [Слой данных (Doctrine ORM + MongoDB ODM)](#-слой-данных-doctrine-orm--mongodb-odm)
9. [CRUD-конвейер ActiveDoctrineCRUD](#-crud-конвейер-activedoctrinecrud)
10. [Событийная модель (RestEvent)](#-событийная-модель-restevent)
11. [Сервисный слой и инфраструктура](#-сервисный-слой-и-инфраструктура)
12. [Безопасность](#-безопасность)
13. [Логирование и мониторинг](#-логирование-и-мониторинг)
14. [Конфигурация](#-конфигурация)
15. [Переменные окружения](#-переменные-окружения)
16. [Пошаговое создание нового API-ресурса](#-пошаговое-создание-нового-api-ресурса)
17. [Структура JSON-ответа](#-структура-json-ответа)
18. [Расширение ядра](#-расширение-ядра)
19. [Лучшие практики](#-лучшие-практики)

---

## 🎯 Назначение и философия

**`core/symfony`** — это переиспользуемое микроядро, инкапсулирующее сквозную инфраструктуру типового REST-сервиса: от HTTP-слоя и роутинга до ORM/ODM, валидации, событий, логирования и взаимодействия с внешними API.

Ядро спроектировано как **«framework-on-framework»** поверх Symfony 5.4 и реализует несколько ключевых принципов:

| Принцип | Реализация |
|---|---|
| **Convention over Configuration** | Контроллеры наследуются от `ActiveDoctrineCRUDController`/`ActiveDoctrineMongoCRUDController` и сразу получают CRUD-методы без шаблонного кода. |
| **Гибридное хранилище** | Реляционные сущности живут в MySQL (`./src/Entity`), документы — в MongoDB (`./src/Document`). Оба подключения конфигурируются через единый контейнер. |
| **Слабая связанность** | Сквозная логика выносится в события (`RestBeforeCreated`, `RestAfterUpdated` и т.д.) и связанные с ними `EntityHandler`-классы, которые подключаются декларативно через `parameters.yaml`. |
| **Единый формат ответа** | Все ответы API оборачиваются в `ResponseData` с предсказуемой структурой `{ success, data, error? }`. |
| **Аудит «из коробки»** | Через `LifecycleEventListener` и набор трейтов все CRUD-операции автоматически проставляют `createdAt/createdBy/updatedAt/updatedBy/deletedAt/deletedBy`. |
| **Мягкое удаление** | SQL-фильтр `SoftDeleteFilter` исключает удалённые записи из выборок, а событийный listener превращает `remove()` в `softDelete()`. |

Точка входа — единый `index.php`, который через `Core\App` поднимает Symfony MicroKernel. Это позволяет запускать ядро как часть более крупной системы (legacy-приложения, шлюзы, фоновые воркеры).

---

## ✨ Ключевые возможности

- 🔁 **Active CRUD для ORM и ODM** — 5 универсальных методов (`restList`, `restShow`, `restCreate`, `restUpdate`, `restDelete`) без наследования шаблонной логики.
- 🎯 **Сериализация по группам** — контроллер декларирует `serializeGroups`, геттеры сущностей аннотируются `@Groups({...})`.
- 🧩 **Гибкая валидация** — Symfony Forms + DTO + кастомные `Constraint`-аннотации (`UniqueField`, `ValidEntityId`, `ValidIetfTag`).
- 🪝 **6 событий RestEvent** — `Before/After × Created/Updated/Deleted` с маршрутизацией на основе карты `entity → handler` из `parameters.yaml`.
- 🗂️ **Документация-cохраняющие ORM-операции** — `LifecycleEventListener` слушает `prePersist`, `preUpdate`, `preRemove` и наполняет аудит-поля.
- 🧮 **Кастомные DBAL-типы** — `tinyint`, `timestamp (UTC)`, `enum` через паттерн `AbstractEnumType`.
- 📡 **HTTP-клиент с ретраями и пагинацией** — `GuzzleRequestHandler` инкапсулирует синхронные/асинхронные вызовы, курсорную пагинацию, чанкинг и middleware-цепочку.
- 🔐 **Аутентификация по заголовку** — `UserHeaderAuthenticator` + `FutureFunctionalBadge` для feature-flag'ов маршрутов.
- 🧱 **Redis-кэш с фабрикой** — абстракция `AbstractRedisCache` поверх `Symfony\Contracts\Cache\CacheInterface`, фабрика провайдеров по имени конфигурации.
- 🪪 **DTO-слой с рефлексией** — `BaseDto` и `ReflectObjectTrait` для самодостаточных объектов обмена без бойлерплейта.
- 📊 **JSON-логи Monolog** — каналы `app`, `security`, `api`, формат `JsonFormatter`.
- 🚦 **Глобальный ErrorController** — единый шаблон 404 через `framework.error_controller`.
- 🧬 **Audit-трейты сущностей** — `AutoCreatedTrait`, `AutoUpdatedTrait`, `AutoDeletedTrait`, `AutoDeletedAtTrait` и т.д.

---

## 🧰 Технологический стек

| Компонент | Версия | Назначение |
|---|---|---|
| PHP | `^7.4` | Язык среды исполнения |
| Symfony Framework Bundle | `5.4` | Базовый фреймворк |
| Symfony HttpKernel / Routing / DI / Config | `5.4` | Базовые компоненты |
| Doctrine ORM + DBAL | `^2.3` (`orm-pack`) | ORM для MySQL/MariaDB |
| Doctrine MongoDB ODM Bundle | `4.6.x` | ODM для MongoDB |
| Symfony Validator | `v5.4.42` | Серверная валидация DTO |
| Symfony Form | `v5.4.40` | Сбор и валидация входящих данных |
| Symfony Serializer | `v5.4.42` | Сериализация с группами |
| FOSRestBundle | `^3.7` | REST view-layers, маршрутизация, формат-контент |
| Security Bundle | `v5.4.*` | Firewall + Authenticator Manager |
| Monolog Bundle | `^3.10` | Структурированное JSON-логирование |
| Doctrine Annotations | `1.14.x` | Аннотации ORM/ODM/Validator |
| Guzzle HTTP Client | (через `GuzzleRequestHandler`) | Внешние HTTP-запросы |
| Guzzle Retry Middleware | (через `ExampleApi`) | Повторы при 429/418 |
| Redis (через `\App\Redis`) | серверное | Сервисный кэш + сессии |

---

## ✅ Системные требования

- PHP **7.4+** с расширениями: `mbstring`, `json`, `pdo`, `mongodb`.
- Composer **2.x**.
- MySQL/MariaDB (активное соединение описывается `MYSQL_*_DATABASE_URL`).
- MongoDB (соединение `MONGO_EXAMPLE_DATABASE_URL` + БД `MONGO_EXAMPLE_DEFAULT_DB`).
- Redis (для `app.cache.session_redis` и сервисного кэша).
- Доступ к внешним API (для `Services/Api/*` клиентов, например `ExampleApi`).

---

## 🚀 Установка и запуск

```bash
# 1. Установка зависимостей
composer install

# 2. Настройка переменных окружения (.env)
APP_SECRET=changeme
APP_ENV=dev

# MySQL
MYSQL_EXAMPLE_DATABASE_URL=mysql://user:pass@host:3306/dbname?serverVersion=8.0&charset=utf8

# MongoDB
MONGO_EXAMPLE_DATABASE_URL=mongodb://user:pass@host:27017
MONGO_EXAMPLE_DEFAULT_DB=example

# Внешние сервисы
CORE_EXAMPLE_HTTP_URL=https://example.com

# 3. (опц.) Прогон миграций
php bin/console doctrine:migrations:migrate

# 4. Запуск встроенного PHP-сервера (для dev)
php -S 0.0.0.0:8080 index.php
```

Точка входа `index.php` минимальна:

```php
require_once(__DIR__ . '/vendor/autoload.php');
(new Core\App())->run();
```

`Core\App` инициализирует `Kernel` в режиме `dev` (по умолчанию) или `prod` (если `App\App::getServiceLabel() === 'production'`), передавая запрос в `Symfony\HttpKernel`.

---

## 🏛️ Архитектура ядра

```
┌──────────────────────────────────────────────────────────────────────┐
│                              index.php                                │
└────────────────┬─────────────────────────────────────────────────────┘
                 │
                 ▼
        ┌────────────────┐
        │   Core\App     │  ← единая точка сборки ядра
        └────────┬───────┘
                 │ handle($request)
                 ▼
       ┌─────────────────────┐
       │ Symfony MicroKernel │ (config/*, bundles/*, services.yaml)
       └────────┬────────────┘
                │
                ▼
   ┌──────────────────────────────┐
   │  Routing → FOSRest → Zone    │  маршруты вида /internal/api/*
   └────────┬─────────────────────┘
            │
            ▼
   ┌────────────────────────────────────────┐
   │  AbstractApiController                 │
   │   ├─ ActiveDoctrineCRUDController      │ → MySQL
   │   └─ ActiveDoctrineMongoCRUDController │ → MongoDB
   └────────┬───────────────────────────────┘
            │ RestCRUDTrait (CRUD-методы)
            ▼
   ┌─────────────────────────────┐
   │ wrapperRespond()            │ → единая структура JSON-ответа
   └────────┬────────────────────┘
            │ dispatch() REST-события
            ▼
   ┌──────────────────────────────┐
   │ RestListener → EntityHandler │ (custom business-logic per entity)
   └────────┬─────────────────────┘
            │ LifecycleEventListener
            ▼
   ┌──────────────────────┐    ┌──────────────────────┐
   │ Doctrine ORM (MySQL) │    │ Doctrine ODM (Mongo) │
   └──────────────────────┘    └──────────────────────┘
```

**Сквозные подсистемы:**

- `Security` — `UserHeaderAuthenticator` + `FutureFunctionalBadge` отсекают запросы без валидного заголовка и управляют feature-flag'ами маршрутов.
- `Monolog` — журналирует исключения, API-запросы и события безопасности в JSON.
- `Redis Cache` — сервисный кэш (`app.cache.session_redis`) и любое количество провайдеров через `RedisCacheFactory`.

---

## 📂 Описание структуры проекта

```
symfony_core/
├── composer.json                 # зависимости ядра
├── index.php                     # единая точка входа
├── config/
│   ├── bundles.php               # подключаемые Symfony-бандлы
│   ├── parameters.yaml           # переменные окружения + реестр event-handler'ов
│   ├── services.yaml             # DI: autowire, tags, public-services
│   ├── routes/
│   │   └── annotations.yaml      # маршрутизация по аннотациям FOSRest
│   └── packages/
│       ├── cache.yaml            # провайдеры Redis-кэша
│       ├── doctrine.yaml         # ORM + lifecycle + soft-delete фильтр
│       ├── doctrine_mongodb.yaml # ODM конфигурация
│       ├── fos_rest.yaml         # зона /internal/api/*, body-listener, форматы
│       ├── framework.yaml        # error_controller и APP_SECRET
│       ├── monolog.yaml          # 3 канала логов + JSON-форматтер
│       └── security.yaml         # firewall `internal_api` + header-authenticator
└── src/
    ├── App.php                   # фасад инициализации Kernel
    ├── Kernel.php                # Symfony MicroKernel
    ├── Controller/
    │   ├── ErrorController.php                       # 404-шаблон всего приложения
    │   ├── AssistantControllers/
    │   │   ├── AbstractApiController.php             # общий каркас API
    │   │   └── ActiveCRUD/
    │   │       ├── ActiveDoctrineCRUDController.php  # CRUD для ORM
    │   │       └── ActiveDoctrineMongoCRUDController.php # CRUD для ODM
    │   └── Internal/Api/V1/Example/
    │       └── ExampleApiController.php              # пример endpoint'а
    ├── Doctrine/                                        # расширения Doctrine
    │   ├── DBAL/Types/                                 # tinyint, timestamp
    │   │   ├── AbstractEnumType.php
    │   │   ├── TinyintType.php
    │   │   └── TimestampType.php
    │   ├── EventListeners/LifecycleEventListener.php   # автоаудит
    │   ├── Filters/SoftDeleteFilter.php                # SQL-фильтр
    │   └── Types/Enum/UsersRoleEnumType.php            # пример ENUM-типа
    ├── Document/                                       # ODM-документы MongoDB
    │   └── ExampleDocument.php
    ├── Entity/                                         # ORM-сущности MySQL
    │   └── Users.php
    ├── Event/
    │   ├── Handlers/                                   # обработчики RestEvent
    │   │   ├── AbstractEntityHandler.php
    │   │   └── Rest/ExampleDocumentEvents.php
    │   ├── Listeners/
    │   │   ├── Interfaces/
    │   │   │   ├── EntityHandlerInterface.php
    │   │   │   └── EntityHandlerEventsInterface.php
    │   │   └── Rest/
    │   │       ├── RestListener.php                   # маршрутизация событий
    │   │       └── Events/                             # 6 RestEvent-классов
    │   └── Subscribers/KernelException.php             # unit-логирование исключений
    ├── Form/
    │   ├── Type/                                       # Symfony-формы
    │   │   └── Example/ExampleMessagesType.php
    │   └── Validator/                                  # Constraint/Validator-пары
    │       ├── Common/IETFtag/
    │       ├── Document/{UniqueField,UniqueCollectionField}/
    │       └── Entities/{UniqueField,EntityId}/
    ├── Repository/
    │   ├── Interfaces/RestRepositoryInterface.php      # контракт репозитория
    │   ├── Entities/UserRepository.php
    │   └── Documents/ExampleDocumentRepository.php
    ├── Security/Authenticators/
    │   ├── UserHeaderAuthenticator.php
    │   └── Badges/FutureFunctionalBadge.php
    ├── Services/
    │   ├── Api/Example/                                # HTTP-клиент внешнего API
    │   ├── Cache/                                      # Redis-абстракция + фабрика
    │   ├── Entity/AbstractEntityService.php
    │   ├── Example/ExampleService.php                  # доменный сервис
    │   └── Helpers/                                    # паттерны/утилиты
    │       ├── Patterns/BaseDto.php
    │       ├── PlugLogger.php
    │       ├── Request/{GuzzleRequestHandler,RequestHandlerException}.php
    │       └── Response/{ResponseData,ApiResponseException}.php
    └── Traits/
        ├── Common/ReflectObjectTrait.php
        ├── Controller/RestCRUDTrait.php
        ├── Entity/Auto{At,By}Trait{x4,2}.php
        ├── Event/{RestEventTrait,HandlerEventTrait}.php
        └── Repository/IterationTrait.php
```

| Путь | Назначение | Подробности |
|---|---|---|
| `./config` | Конфигурация ядра | `bundles.php` — подключение бандлов, `parameters.yaml` — общие параметры, `services.yaml` — DI-контейнер. |
| `./config/packages` | Конфигурации пакетов | `cache.yaml` подключает `RedisCacheFactory`; `doctrine.yaml` дополнительно регистрирует `LifecycleEventListener` и `SoftDeleteFilter`. |
| `./config/routes` | Маршрутизация | По умолчанию — аннотации FOSRest в `src/Controller/`. |
| `./src/Controller` | Контроллеры | `ErrorController::notFound` — глобальный шаблон 404 (`framework.yaml`). |
| `./src/Controller/AssistantControllers/ActiveCRUD` | Active CRUD | `ActiveDoctrineCRUDController` (MySQL) и `ActiveDoctrineMongoCRUDController` (MongoDB). |
| `./src/Controller/Internal/Api` | Реализация endpoint'ов | Маршруты и контроллеры версионируются по namespace `V1`, `V2` и т.д. |
| `./src/Doctrine` | Расширения Doctrine | Конфигурируется в `packages/doctrine.yaml` и `packages/doctrine_mongodb.yaml`. |
| `./src/Document` | ODM-документы MongoDB | DataMapper-классы с аннотациями `MongoDB\Document`/`MongoDB\Field`. |
| `./src/Entity` | ORM-сущности | DataMapper-классы с `ORM\Entity`/`ORM\Column` и группами сериализации. |
| `./src/Event` | Событийная инфраструктура | `Subscribers` (например `KernelException`), `Listeners/Rest` (маршрутизация), `Handlers/Rest` (бизнес-логика). |
| `./src/Event/Handlers` | Хелперы событий | Класс привязывается к сущности через `parameters.yaml → rest_listener_comparison_List`. |
| `./src/Form` | Формы и валидаторы | `Type` — Symfony-формы, `Validator` — кастомные `Constraint`. |
| `./src/Repository` | Репозитории | Разделение по типу хранилища: `Documents/`, `Entities/`. Общий интерфейс — `Interfaces/RestRepositoryInterface`. |
| `./src/Security` | Безопасность | `Authenticators` — классы для firewall'ов; `Badges` — post-auth проверки. |
| `./src/Services` | Доменные и инфраструктурные сервисы | `Helpers/Response/ResponseData` — единый конверт ответа. |
| `./src/Traits` | Переиспользуемые трейты | Содержат аудит, рефлексию, событийные пустые реализации, итерации репозиториев. |

---

## 💾 Слой данных (Doctrine ORM + MongoDB ODM)

### ORM (MySQL/MariaDB)

- Конфигурация: `config/packages/doctrine.yaml`.
- Менеджер сущностей по умолчанию: `mysql`.
- Маппинг аннотациями из `src/Entity` с префиксом `Core\Entity`.
- Подключён `SoftDeleteFilter` (исключает записи с непустым `deleted_at`).
- Зарегистрирован `event.lifecycle_listener`, слушающий `prePersist`, `preUpdate`, `preRemove`.

### ODM (MongoDB)

- Конфигурация: `config/packages/doctrine_mongodb.yaml`.
- Документ-менеджер по умолчанию: `mongodb_example`.
- Маппинг аннотациями `MongoDB\Document` из `src/Document` с префиксом `Core\Document`.

### Кастомные DBAL-типы

| Тип | Назначение | Файл |
|---|---|---|
| `tinyint` | TINYINT с приведением к `int` | `Doctrine/DBAL/Types/TinyintType.php` |
| `timestamp` | DATETIME в UTC + комментарий `(DC2Type:timestamp)` | `Doctrine/DBAL/Types/TimestampType.php` |
| `users_role_enum` | ENUM-роль пользователя | `Doctrine/Types/Enum/UsersRoleEnumType.php` |
| `*_enum` (паттерн) | Шаблон для любых ENUM-полей | `Doctrine/DBAL/Types/AbstractEnumType.php` |

### Аудит «из коробки»

`Doctrine/EventListeners/LifecycleEventListener.php` на событиях ORM:

| Событие | Эффект |
|---|---|
| `prePersist` | Проставляет `createdBy` / `updatedBy` если у сущности есть поле. |
| `preUpdate` | Проставляет `updatedBy`. |
| `preRemove` | Превращает удаление в мягкое: вызывает `softDelete($user)`, persist+flush, отвязывает из UoW. |

Чтобы сущность получила аудит — достаточно подключить соответствующие трейты:

```php
class Article {
    use AutoCreatedTrait;     // createdAt + createdBy
    use AutoUpdatedTrait;     // updatedAt + updatedBy
    use AutoDeletedTrait;     // deletedAt + deletedBy + softDelete(UserInterface)
}
```

### Мягкое удаление

`Core/Doctrine/Filters/SoftDeleteFilter.php` добавляет условие `<alias>.deleted_at IS NULL` в SQL-запросы сущностей, у которых есть поле `deletedAt`. Включён глобально (`enabled: true`).

---

## 🔁 CRUD-конвейер `ActiveDoctrineCRUD`

`ActiveDoctrineCRUDController` и `ActiveDoctrineMongoCRUDController` — это тонкие обёртки над `AbstractApiController` + trait `RestCRUDTrait`. Контроллеру достаточно переопределить три поля:

```php
class ArticleController extends ActiveDoctrineCRUDController
{
    protected ?array $serializeGroups = ['article:read']; // группы сериализации
    protected ?string $entityClass    = Article::class;   // DataMapper
    protected ?string $typeClass      = ArticleType::class; // Symfony Form
}
```

И сразу доступны 5 CRUD-методов:

| Метод | Назначение | События |
|---|---|---|
| `restListAction(Request, ?array $options = null)` | Список с пагинацией | — |
| `restShowAction($id, ?array $options = null)` | Получение по ID | — |
| `restCreateAction(Request, ?array $options = null)` | Создание | `Before/After Created` |
| `restUpdateAction(Request, callable $getEntityOrException, ?array $options = null)` | Обновление | `Before/After Updated` |
| `restDeleteAction($id, ?array $options = null)` | Удаление | `Before/After Deleted` |

Каждый метод оборачивает результат в `wrapperRespond()`, гарантируя:

- ✅ единый формат JSON-ответа (`success`, `data`, `error?`);
- ✅ прокинутые `serializeGroups`;
- ✅ всплытие `ResourceNotFoundException` в HTTP 404;
- ✅ всплытие `ApiResponseException` с указанным статусом и телом.

### Обработка нестандартных сценариев в `restUpdateAction`

`$getEntityOrException` — это коллбек, который должен вернуть модифицируемую сущность или выбросить исключение:

```php
$this->restUpdateAction(
    $request,
    function () use ($id) {
        return $this->getItemOrNotFoundException($id);
    },
    ['statusCode' => Response::HTTP_OK]
);
```

---

## 🪝 Событийная модель (RestEvent)

### 6 событий

В `Core/Event/Listeners/Rest/Events/`:

| Класс | Константа `EVENT_NAME` |
|---|---|
| `RestBeforeCreatedEvent` | `rest.entity.before.created` |
| `RestAfterCreatedEvent` | `rest.entity.after.created` |
| `RestBeforeUpdatedEvent` | `rest.entity.before.updated` |
| `RestAfterUpdatedEvent` | `rest.entity.after.updated` |
| `RestBeforeDeletedEvent` | `rest.entity.before.deleted` |
| `RestAfterDeletedEvent` | `rest.entity.after.deleted` |

Все наследуют `Symfony\Contracts\EventDispatcher\Event`, реализуют `RestEventInterface` (методы `getEntity()`, `getEntityClassName()`) и используют `RestEventTrait`.

### Маршрутизация

`Core/Event/Listeners/Rest/RestListener.php` подписан на все 6 имён через `services.yaml`:

```yaml
Core\Event\Listeners\Rest\RestListener:
  tags:
    - { name: 'kernel.event_listener', event: 'rest.entity.after.created',  method: 'onEntityAfterCreated' }
    - { name: 'kernel.event_listener', event: 'rest.entity.before.created', method: 'onEntityBeforeCreated' }
    # ... остальные 4 события
```

Внутри `RestListener`:

1. Получает FQCN сущности через `$event->getEntityClassName()`.
2. Ищет соответствующий `EntityHandler`-класс в массиве `rest_listener_comparison_List` из `parameters.yaml`.
3. Инстанцирует handler, проверяет, что он реализует `EntityHandlerInterface` + `EntityHandlerEventsInterface`.
4. Передаёт сущность и `ContainerInterface`, вызывает нужный метод (`eventEntityBeforeCreated` и т.д.).

### Handler-классы

- Базовый абстрактный класс — `Core\Event\Handlers\AbstractEntityHandler`.
- Интерфейсы — `EntityHandlerInterface` (`setEntity`, `setContainer`) + `EntityHandlerEventsInterface` (6 методов событий).
- Трейт `HandlerEventTrait` подключает пустые реализации всех 6 методов — handler переопределяет только нужные.

Пример:

```php
class ExampleDocumentEvents extends AbstractEntityHandler implements EntityHandlerEventsInterface
{
    public function eventEntityBeforeCreated(RestBeforeCreatedEvent $event): void
    {
        $this->getExampleService()->newEntityPrepare();
    }

    public function eventEntityAfterDeleted(RestAfterDeletedEvent $event): void
    {
        $this->getExampleService()->deleteWebhook();
    }
}
```

Декларативная регистрация в `config/parameters.yaml`:

```yaml
parameters:
  rest_listener_comparison_List:
    'Core\Document\ExampleDocument': 'Core\Event\Handlers\Rest\ExampleDocumentEvents'
```

### Глобальный Exception-Subscriber

`Core/Event/Subscribers/KernelException.php` подписан на `KernelEvents::EXCEPTION` и журналирует `NotFoundHttpException` (notice) и прочие исключения (warning + dump).

---

## 🛠️ Сервисный слой и инфраструктура

### Доменные сервисы

`Core/Services/Example/ExampleService.php` — пример сервиса, агрегирующего:

- `ExampleApi` — HTTP-клиент внешнего API;
- `DoctrineManagerRegistry` + `MongoManagerRegistry` — для чтения данных из MySQL/Mongo;
- `Mappers\MessagesMappers` — преобразование `MessagesResponseDTO` (DTO от внешнего API) → `ExampleMessagesDTO` (DTO для нашего API).

### HTTP-клиент `GuzzleRequestHandler`

Абстрактный класс `Core/Services/Helpers/Request/GuzzleRequestHandler.php` инкапсулирует:

- `requestHandler` — базовый синхронный вызов с обработкой `RequestException` и `GuzzleException`;
- `requestsWithCursorPaginationHandler` — курсорная пагинация с возможностью вернуть метаданные;
- `requestAsyncHandler` — пул асинхронных запросов Guzzle `Pool` с `concurrency`;
- `requestChunkingHandler` — отправка больших массивов чанками фиксированного размера.

Дочерний класс (`Core/Services/Api/Example/ExampleApi.php`) задаёт `baseRequestUri`, конфигурирует `HandlerStack` (например, добавляет `GuzzleRetryMiddleware` и middleware для подмены `User-Agent`/`Content-Type`) и реализует `requestRejectedHandle`.

### Кэш (`Services/Cache/`)

- `AbstractRedisCache` реализует `Symfony\Contracts\Cache\CacheInterface` поверх любого PSR-совместимого Redis-клиента.
- `RedisCacheFactory::createRedisCache(string $configName)` создаёт инстанс через `\App\Redis::getDb()` или `\App\Sessions::getRedis()`.
- Конфигурация сервиса в `packages/cache.yaml`:

```yaml
app.cache.session_redis:
  class: Core\Services\Cache\RedisCacheService
  factory: ['@Core\Services\Cache\RedisCacheFactory', 'createRedisCache']
  arguments: ['sessions']
```

### Утилиты и паттерны

| Класс | Назначение |
|---|---|
| `Services/Helpers/Response/ResponseData` | Единая обёртка HTTP-ответа `{success, data, error}`. |
| `Services/Helpers/Response/ApiResponseException` | `RuntimeException` с произвольным `content` + `statusCode`. |
| `Services/Helpers/PlugLogger` | No-op реализация `Psr\Log\LoggerInterface` для фолбэка. |
| `Services/Helpers/Patterns/BaseDto` | Базовый класс DTO с гидрацией через рефлексию, `toArray()` и `toJson()`. |
| `Services/Entity/AbstractEntityService` | Скелет доменного сервиса с `setEntity()`/`getEntity()`. |
| `Traits/Common/ReflectObjectTrait` | Альтернативная быстрая реализация `toArray()`/`toJson()` для DTO. |
| `Traits/Repository/IterationTrait` | `iterateFindBy()`-генератор для поэлементной обработки. |

---

## 🔐 Безопасность

Конфигурация `config/packages/security.yaml`:

```yaml
security:
  providers:
    app.user_provider:
      entity:
        class: 'Core\Entity\Users'
        property: 'user_id'

  enable_authenticator_manager: true
  firewalls:
    other:
      pattern: ^/(_(profiler|wdt)|web|VkSender|tests|TestAPI|swoole|others|dist|db|cron|config|App)/
      security: false
    internal_api:
      pattern: ^/internal/api/*
      provider: app.user_provider
      custom_authenticators:
        - Core\Security\Authenticators\UserHeaderAuthenticator
```

- **User provider** загружает пользователя по `user_id` через `Users::class`.
- **Firewall `other`** закрыт правилом `security: false` для служебных путей.
- **Firewall `internal_api`** активен для всех маршрутов `^/internal/api/*` и обслуживается `UserHeaderAuthenticator`.

### Аутентификатор `UserHeaderAuthenticator`

- `supports()` возвращает `true`, если в запросе есть заголовок `Example-User-Id`.
- `authenticate()` использует `SelfValidatingPassport` + `UserBadge` + `FutureFunctionalBadge`.
- `createToken()` отдаёт `RememberMeToken` с firewallName в качестве «секрета».
- При неуспехе — формирует JSON-ответ через `ResponseData` со статусом `401`.

### Badge `FutureFunctionalBadge`

Позволяет отключать/включать маршруты в зависимости от списка «future functional actions»:

```yaml
parameters:
  feature_functional_actions:
    - 'api_v1_example_test_messages_get'

when@dev:
  parameters:
    feature_functional_actions: null   # в dev — все маршруты активны
```

Если текущее имя маршрута присутствует в массиве, `isResolved()` возвращает `false` и валидация паспорта падает → запрос получает 401/403.

---

## 📊 Логирование и мониторинг

`config/packages/monolog.yaml` определяет:

- **Каналы:** `app`, `security`, `api`.
- **Handler'ы:** `main` (json-stream в `var/log/<env>.json`), `stdout`, `api_log`, `security_log`.
- **Форматтер:** `Monolog\Formatter\JsonFormatter` (BATCH_MODE_JSON = true).

Подписчики:

- `Core/Event/Subscribers/KernelException.php` логирует исключения kernel'а.
- `AbstractApiController` использует канал `monolog.logger.api` (если сервис определён) или `PlugLogger`.

---

## ⚙️ Конфигурация

### `config/bundles.php`

Подключены бандлы:

- `FrameworkBundle` — ядро Symfony.
- `DoctrineBundle` — ORM.
- `FOSRestBundle` — REST-инфраструктура.
- `SecurityBundle` — аутентификация.
- `MonologBundle` — логирование.
- `DoctrineMongoDBBundle` — ODM.

### `config/services.yaml`

- `_defaults` включает `autowire`, `autoconfigure`, `public: false`.
- Авто-регистрация всех сервисов из `../src/*` с исключениями (Entity, Document, Doctrine, Trait, Kernel, App).
- Явные объявления:
  - `Core\Event\Listeners\Rest\RestListener` — тегирует 6 listener'ов;
  - `Core\Services\Example\ExampleService` (`public: true` — доступен из контроллеров);
  - `Core\Services\Api\Example\ExampleApi` (`public: true` + DI-аргумент `core.example_api.service_url`).

### `config/parameters.yaml`

```yaml
parameters:
  local: 'ru'

  core.example_api.service_url: '%env(CORE_EXAMPLE_HTTP_URL)%'

  feature_functional_actions:
    - 'api_v1_example_test_messages_get'

  items_list_limit:
    example_document: 10

  rest_listener_comparison_List:
    'Core\Document\ExampleDocument': 'Core\Event\Handlers\Rest\ExampleDocumentEvents'

when@dev:
  parameters:
    feature_functional_actions: null
```

`when@dev` переопределяет параметры только для dev-окружения — здесь включаются все «future»-маршруты.

---

## 🌐 Переменные окружения

| Имя | Назначение |
|---|---|
| `APP_SECRET` | Секрет фреймворка (HTTP-сессии, CSRF). |
| `APP_ENV` | Окружение (`dev`, `prod`, `test`). |
| `MYSQL_EXAMPLE_DATABASE_URL` | DSN-подключения к MySQL (используется `env(resolve:)`). |
| `MONGO_EXAMPLE_DATABASE_URL` | DSN-подключения к MongoDB. |
| `MONGO_EXAMPLE_DEFAULT_DB` | БД по умолчанию в MongoDB. |
| `CORE_EXAMPLE_HTTP_URL` | Базовый URL внешнего `ExampleApi`. |

---

## 🛠️ Пошаговое создание нового API-ресурса

Полный рецепт для ORM-сущности (для ODM-шаги аналогичны, разница — `MongoDB\*` аннотации и `ActiveDoctrineMongoCRUDController`).

### 1. Опишите DataMapper

`./src/Entity/Article.php`:

```php
/**
 * @ORM\Entity(repositoryClass="Core\Repository\Entities\ArticleRepository")
 * @ORM\Table(name="articles")
 */
class Article {
    use AutoCreatedTrait, AutoUpdatedTrait, AutoDeletedTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="title", type="string")
     * @Groups({"article:read"})
     */
    private $title;

    public function getId(): ?int   { return $this->id; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): void { $this->title = $title; }
}
```

### 2. Создайте репозиторий

`./src/Repository/Entities/ArticleRepository.php`:

```php
class ArticleRepository extends ServiceEntityRepository implements RestRepositoryInterface
{
    public function getListPagination(Request $request): array
    {
        return $this->createQueryBuilder('a')->getQuery()->toArray();
    }
}
```

### 3. Реализуйте форму

`./src/Form/Type/ArticleType.php`:

```php
class ArticleType extends AbstractType {
    public function buildForm(FormBuilderInterface $b, array $o): void {
        $b->add('title', TextType::class, [
            'constraints' => [new NotBlank()],
        ]);
    }

    public function configureOptions(OptionsResolver $r): void {
        $r->setDefaults(['data_class' => Article::class]);
    }
}
```

### 4. Реализуйте контроллер

`./src/Controller/Internal/Api/V1/Article/ArticleController.php`:

```php
class ArticleController extends ActiveDoctrineCRUDController
{
    protected ?array   $serializeGroups = ['article:read'];
    protected ?string $entityClass     = Article::class;
    protected ?string $typeClass       = ArticleType::class;

    /** @Route("/api/v1/article/list", methods={"GET"}) */
    public function listAction(Request $request): Response {
        return $this->restListAction($request);
    }

    /** @Route("/api/v1/article/{id}", methods={"GET"}, requirements={"id"="\d+"}) */
    public function showAction(int $id): Response {
        return $this->restShowAction($id);
    }

    /** @Route("/api/v1/article/create", methods={"POST"}) */
    public function createAction(Request $request): Response {
        return $this->restCreateAction($request);
    }

    /** @Route("/api/v1/article/{id}/update", methods={"POST","PUT"}, requirements={"id"="\d+"}) */
    public function updateAction(int $id, Request $request): Response {
        return $this->restUpdateAction($request,
            fn() => $this->getItemOrNotFoundException($id));
    }

    /** @Route("/api/v1/article/{id}/delete", methods={"DELETE"}, requirements={"id"="\d+"}) */
    public function deleteAction(int $id): Response {
        return $this->restDeleteAction($id);
    }
}
```

### 5. (Опционально) Handler для событий

`./src/Event/Handlers/Rest/ArticleEvents.php`:

```php
class ArticleEvents extends AbstractEntityHandler implements EntityHandlerEventsInterface
{
    public function eventEntityAfterCreated(RestAfterCreatedEvent $event): void
    {
        // кастомная логика: например, отправка в стороннюю систему
    }
}
```

### 6. Зарегистрируйте handler

В `config/parameters.yaml`:

```yaml
parameters:
  rest_listener_comparison_List:
    'Core\Entity\Article': 'Core\Event\Handlers\Rest\ArticleEvents'
```

### 7. Добавьте кастомные валидаторы (если нужны)

Каталоги `Form/Validator/Document` или `Form/Validator/Entities` содержат готовые `Constraint/Validator`-пары:

- `UniqueField` — проверка уникальности;
- `ValidEntityId` — существование ссылки на сущность;
- `ValidIetfTag` — BCP 47 теги локалей.

---

## 📦 Структура JSON-ответа

Все ответы API сериализуются через `ResponseData` (`src/Services/Helpers/Response/ResponseData.php`):

```json
{
  "success": true,
  "data": { /* произвольные данные или массив */ },
  "extra_field": "value"
}
```

При ошибке:

```json
{
  "success": false,
  "data": [],
  "error": {
    "message": "...",
    "code": 400
  }
}
```

Особенности:

- `ResourceNotFoundException` → HTTP 404.
- `ApiResponseException` → HTTP status из исключения + тело ошибки из `$content`.
- Неизвестные исключения → HTTP 500 + `Errors::unknown_error($e)` и запись в logger.
- Поле `data` пустое, если пейлоад вернул пустой результат (или `null` при удалении).

---

## 🧩 Расширение ядра

### Добавление нового события RestEvent

1. Создайте класс в `src/Event/Listeners/Rest/Events/RestXxxEvent.php`, реализующий `RestEventInterface`.
2. Объявите константу `EVENT_NAME` и подключите trait `RestEventTrait`.
3. Добавьте метод в `EntityHandlerEventsInterface`.
4. Объявите обработчик в `src/Event/Listeners/Rest/RestListener.php` и зарегистрируйте тег в `services.yaml`.
5. (Опц.) Реализуйте поведение в `RestCRUDTrait`.

### Добавление кастомного DBAL-типа

1. Создайте класс в `src/Doctrine/DBAL/Types/`.
2. Зарегистрируйте в `config/packages/doctrine.yaml`:

```yaml
doctrine:
  dbal:
    types:
      my_type: Core\Doctrine\DBAL\Types\MyType
```

### Добавление нового HTTP-клиента

1. Создайте класс, наследующий `Core/Services/Helpers/Request/GuzzleRequestHandler.php`.
2. Реализуйте `configHandlerStack(HandlerStack $hs)` и `requestRejectedHandle(RequestException $e)`.
3. Зарегистрируйте сервис с аргументами (`baseRequestUri` и т.п.) в `services.yaml`.

---

## 🏆 Лучшие практики

- **Аннотируйте геттеры группой** сериализации (`@Groups({"entity:read"})`), чтобы не утекали приватные/служебные поля.
- **Всегда вызывайте `parent::buildForm`** и передавайте `block_name` из `$request->attributes->get('_route')` — упрощает кастомизацию валидаторов.
- **Используйте `wrapperRespond()`** вместо ручного построения `JsonResponse` — это обеспечивает единый формат.
- **Расширяйте `AbstractApiController` напрямую**, если нужен не-CRUD endpoint (как `ExampleApiController`).
- **Декларативно регистрируйте handler'ы** через `rest_listener_comparison_List` — не инстанцируйте их вручную.
- **Для Mongo-документов** используйте те же трейты (`AutoCreatedTrait` и т.д.) и `@Groups` на геттерах, как и для ORM-сущностей.
- **При работе с внешними API** используйте `BaseDto` для ответов — это позволяет гидрировать объекты без бойлерплейта.
- **Логируйте через каналы Monolog** — `app`, `security`, `api`, не подмешивайте свои форматтеры.
- **В продакшене** ядро стартует в режиме `prod` (см. `Core\App::getServiceLabel()`), кэш должен быть прогрет (`bin/console cache:warmup`).

---

## 📜 Лицензия

Licensed under the MIT License.

---

<p align="center"><sub>Документация актуальна для версии <code>core/symfony 1.0.0</code> · Symfony 5.4 · PHP 7.4+</sub></p>
