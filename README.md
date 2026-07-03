# PHP-FAN 5

Модернизированная версия PHP-FAN — монолитного PHP-фреймворка с собственными маршрутизацией, DI-контейнером, блоками, представлениями и модельным слоем.

## Требования

- PHP 8.3 или новее;
- расширения `curl`, `dom`, `gd`, `json`, `mbstring`, `pdo`;
- Composer 2;
- поддерживаемая PDO-база данных для функций, использующих модели.

Расширения `soap`, `simplexml` и `memcache` нужны только соответствующим дополнительным подсистемам.

## Быстрый запуск

```bash
composer install
composer check
PHP_FAN_COOKIE_SECURE=0 php -S 127.0.0.1:8080 -t htdocs
```

Откройте `http://127.0.0.1:8080`. Отключение `Secure` допустимо только для локального HTTP. В production корнем сайта должен быть только каталог `htdocs`; корень репозитория публиковать нельзя.

## Конфигурация окружения

Основная конфигурация находится в `project/conf/service.php`. Секреты не хранятся в репозитории и передаются через окружение:

```bash
export PHP_FAN_DB_HOST=127.0.0.1
export PHP_FAN_DB_NAME=php_fan
export PHP_FAN_DB_USER=php_fan
export PHP_FAN_DB_PASSWORD='change-me'
export PHP_FAN_TIMEZONE=UTC
```

Дополнительно доступны `PHP_FAN_TEST_DB_*`, `PHP_FAN_DEBUG=1` для локальной диагностики и `PHP_FAN_TIMER_EXEC=1` для явно разрешённого запуска timer-команд. Последний флаг нельзя включать для недоверенных конфигураций.

## Архитектура

```text
htdocs/index.php
  -> web_application_initializer
  -> bootstrap application/context
  -> DI container + service registrars
  -> matcher -> tab/plain handler
  -> blocks -> view router/parser -> response
  -> entity/model -> database_connections -> Illuminate Database
```

- `core/application` — bootstrap, загрузчик, обработка ошибок и запуск запроса;
- `core/di` и `core/factory` — composition root, регистрации и фабрики сервисов;
- `core/service` — инфраструктурные и прикладные сервисы;
- `core/base/model` — legacy Entity/Row API, теперь работающий через адаптер Illuminate Database;
- `core/block` и `core/view` — UI-блоки и формирование ответа;
- `project` — конфигурация и код конкретного приложения;
- `unit` — PHPUnit-тесты;
- `tools` — статические проверки и средства анализа проекта.

## Проверки

```bash
composer smoke    # загрузка entrypoint и autoload
composer analyse  # PHPStan level 1
composer test     # полный PHPUnit suite
composer check    # все проверки последовательно
composer audit    # известные уязвимости зависимостей
```

CI выполняет `composer validate`, установку из lock-файла, smoke, PHPStan и PHPUnit на PHP 8.3.

## Безопасность

- пароли сохраняются через `password_hash`; старые MD5-хэши обновляются после успешного входа;
- session ID принимается только из cookie; cookie имеют `Secure`, `HttpOnly` и `SameSite=Lax`;
- стандартные защитные HTTP-заголовки задаются в секции `header.SECURITY_HEADERS`;
- выполнение timer-команд выключено по умолчанию, аргументы shell экранируются и могут ограничиваться allowlist;
- диагностические публичные скрипты в `htdocs` отсутствуют.

Перед production-развёртыванием настройте TLS, реальные учётные данные БД, почтовый адрес администратора, права на `logs`/`temp_data` и CSP под фактические внешние ресурсы приложения.
