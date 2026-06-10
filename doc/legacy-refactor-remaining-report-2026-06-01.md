# Отчет: что осталось от старого подхода

Дата анализа: 2026-06-01

Область анализа: `core`, `project`, `htdocs`, `cli`; без `vendor` и `legacy_assets`.

## Краткий вывод

Проект уже заметно переведен в сторону dependency injection: старые service-locator gateway, `::instance()`, `parse_ini_file()`, `call_user_func*`, прямой `eval()` и локальные DI-factory `defaultConfiguredServiceFactory()` в текущем production-срезе не найдены.

Главный оставшийся долг теперь не в массовых прямых вызовах native API, а в архитектурных границах совместимости: static bootstrap lifecycle, static container registry bridge, nullable self-default factories, ручная include/require-композиция, request/session окружение через globals и legacy template/meta parsing.

## Статический срез

| Метрика | Значение | Вывод |
|---|---:|---|
| Production PHP-файлов | 530 | `core`, `project`, `htdocs`, `cli`; без `vendor` и `legacy_assets`. |
| `include` / `require` matches | 176 | Остаются entrypoint, bootstrap, loader, adapter и DI composition boundaries. |
| Dynamic `new $class...` | 1 | Единственное совпадение: `core/factory/configured_service_factory.php`; это явный extension/config boundary. |
| DI factories with `defaultConfiguredServiceFactory()` | 0 | Закрыт прежний хвост, где каждая factory сама создавала `configured_service_factory`. |
| Legacy dispatcher/config patterns | 0 | Не найдены `call_user_func*`, `parse_ini_file()`, `eval()` и `::instance()`. |
| Raw superglobals / `$GLOBALS` | 22 grep matches | Реальный доступ сосредоточен в `core/service/request_input.php` и `core/service/session/adodb_session_globals.php`; часть совпадений - комментарии/diagnostics. |
| Nullable/default factory hooks in DI/bootstrap | 307 grep matches | Большая часть - composition root (`application_container_factory`, `context`), но есть несколько self-default точек вне центрального root. |
| Static `default*` factory helpers in DI/bootstrap | 65 grep matches | Оставшиеся helpers сосредоточены в composition root; leaf-level `email_engine_factory` больше не владеет mailer default. |

## Что осталось и что рефакторить дальше

| Приоритет | Участок | Что осталось от старого подхода | Примеры файлов | Риск | Что делать дальше |
|---:|---|---|---|---|---|
| 1 | Static bootstrap lifecycle | `bootstrap` остается static entrypoint-ом и хранит static `$contextState`. Default state graph вынесен в `core/bootstrap/context_registry_state_default_factory.php`, `setContext()` / `setContextFactory()` теперь устанавливают injected state напрямую без предварительного создания default graph, `context_registry_state` больше не имеет mutable setters, config loading вынесен в `core/bootstrap/bootstrap_config_loader.php`, а error-handler setup вынесен в `core/bootstrap/bootstrap_error_handler_setup.php`. Даже так lifecycle все еще стартует через static facade. | `core/bootstrap.php`, `core/bootstrap/context_registry_state.php`, `core/bootstrap/context_registry_state_default_factory.php`, `core/bootstrap/bootstrap_config_loader.php`, `core/bootstrap/bootstrap_error_handler_setup.php`, `core/factory/bootstrap/context_factory.php`, `core/bootstrap/state.php` | Средний | Вынести ownership context/runtime в non-static application/bootstrap runner; `bootstrap` оставить тонкой compatibility-оболочкой для старых entrypoint-ов. |
| 2 | Static container registry bridge | `container_registry` остается static мостом к container, но больше не знает напрямую про `application_container_factory` и `container_provider_factory`. Normal bootstrap path создает registry state через `core/bootstrap/container_registry_state_factory.php` и устанавливает его из `core/factory/bootstrap/context_container_factory.php`; implicit default graph fallback удален. `container_registry::get()` без configured state теперь явно падает, а test-only `set()`, `setProvider()` и `setProviderFactory()` hooks сняты со static registry/state API. Registry больше не подгружает provider/state dependencies сам. | `core/di/container_registry.php`, `core/bootstrap/container_registry_state_factory.php`, `core/factory/bootstrap/context_container_factory.php`, `core/factory/bootstrap/context_defaults_factory.php`, `core/di/container_registry_state.php`, `core/di/container_registry_state_factory.php`, `core/di/container_provider_factory.php`, `core/factory/application_container_factory.php` | Средний | Сужать static facade API: normal code должен получать container/context через bootstrap/application objects, а `container_registry` оставить временным facade для явной legacy/test injection. |
| 3 | Composition-root concrete defaults | Leaf-level `email_engine_factory` больше не создает `php_mailer`/`PHPMailer`; concrete mailer default теперь живет в `application_container_factory`. Это соответствует DI, но увеличивает размер central composition root. | `core/factory/application_container_factory.php`, `core/di/email_engine_factory.php` | Низкий-средний | Следующий слой - дробить `application_container_factory` по bounded areas, чтобы concrete defaults не концентрировались в одном классе. |
| 4 | Central composition root слишком большой | `application_container_factory` уже играет роль composition root, но содержит десятки nullable factory hooks и `default*Factory()` методов. Это лучше старого service-locator подхода, но класс стал крупной точкой знания обо всех сервисах. | `core/factory/application_container_factory.php` | Средний | Разбить composition по bounded areas: bootstrap/runtime, service factories, model/entity, IO adapters. Сначала без изменения публичного container API. |
| 5 | Configured extension construction | Dynamic construction централизован в одном boundary, но этот boundary все еще создает классы по строковому имени из config/meta. | `core/factory/configured_service_factory.php`, `core/di/*_factory.php` | Низкий-средний | Разделить internal construction и plugin/config extension API: internal services перевести на typed factory map, dynamic `new $class` оставить только для настоящих extension points. |
| 6 | Manual include/require layer | Ручная загрузка файлов еще нужна для bootstrap, loader, PHP-array storage, compiled templates, project tools и lazy factory/adapter boundaries. | `core/bootstrap.php`, `core/bootstrap/loader.php`, `core/factory/application_container_factory.php`, `core/adapter/compiled_template_loader.php`, `htdocs/install/index.php` | Низкий-средний | Закрепить allowlist source-guard-ами: runtime services не должны делать `require/include`; новые file-load cases оформлять только как adapters/factories/entrypoints. |
| 7 | Raw request/session environment | Superglobals уже локализованы, но модель окружения остается legacy: request/session читаются и мутируются через `$GLOBALS` boundary. | `core/service/request_input.php`, `core/service/session/adodb_session_globals.php`, `core/service/request.php` | Средний | Ввести явные request/session context objects выше adapter-уровня; `$GLOBALS` оставить только в самом нижнем compatibility adapter. |
| 8 | Native PHP side effects | Большинство native side effects вынесено в adapters, но границы остаются жестко привязаны к PHP runtime: headers, cookies, error log, ini settings, error handlers, GD/image API. | `core/adapter/header_writer.php`, `core/adapter/cookie_writer.php`, `core/adapter/error_log_writer.php`, `core/bootstrap/php_runtime_settings.php`, `core/adapter/warning_capture.php`, `core/adapter/image_*` | Низкий | Считать эти классы intentional adapter layer; покрывать тестами и не позволять native API возвращаться в services. |
| 9 | Legacy template/meta parsing | Template/meta слой все еще живет на строковых правилах, file discovery и runtime parsing. Это уже следующий архитектурный слой после DI/lifecycle. | `core/service/template/*`, `core/block/**/*.meta.php`, `project/**/*.meta.php` | Низкий-средний | После стабилизации lifecycle выделить typed template/meta contracts и оставить старый parsing как compatibility adapter. |

## Рекомендуемый порядок

| Шаг | Работа | Почему сейчас |
|---:|---|---|
| 1 | Сузить static `container_registry` facade API | Implicit fallback удален; следующий долг - убрать зависимость normal/test paths от static `container_registry::get()` и передавать container/context явно. |
| 2 | Сузить `bootstrap::$contextState` до compatibility facade | После registry ownership cleanup перейти к главному static lifecycle owner. |
| 3 | Разбить `application_container_factory` на smaller composition roots | Mailer concrete default и многие service defaults теперь корректно живут в composition root, но класс остается слишком концентрированным. |
| 4 | Разделить `application_container_factory` на smaller composition roots | Снизит концентрацию nullable hooks и `default*Factory()` методов. |
| 5 | Ужесточить allowlist для include/require, globals и native API | Защитит уже проделанный DI-рефакторинг от регресса. |
| 6 | Перейти к template/meta contracts | Идти туда лучше после lifecycle cleanup, потому что слой более рискованный и строково-динамический. |

## Проверки, на которых основан срез

| Проверка | Результат |
|---|---|
| `rg --files core project htdocs cli -g '*.php' -g '!vendor/**' -g '!legacy_assets/**'` | 530 production PHP-файлов |
| `rg -n '\b(?:include|include_once|require|require_once)\b' ...` | 176 matches |
| `rg -n 'new\s+\$[A-Za-z_][A-Za-z0-9_]*\s*\(' ...` | 1 match: `core/factory/configured_service_factory.php` |
| `rg -l 'defaultConfiguredServiceFactory\s*\(' core/di -g '*.php'` | 0 files |
| `rg -n '\$_(?:GET|POST|REQUEST|SERVER|COOKIE|SESSION|FILES|ENV)\b|\$GLOBALS\b' ...` | 22 matches |
| `rg -n '\b(?:call_user_func|call_user_func_array|parse_ini_file|eval)\s*\(|::instance\s*\(' ...` | 0 matches |

## Последняя валидация

| Проверка | Результат |
|---|---|
| `/opt/homebrew/bin/php -l core/bootstrap.php && /opt/homebrew/bin/php -l core/bootstrap/context.php && /opt/homebrew/bin/php -l core/factory/bootstrap/context_defaults_factory.php && /opt/homebrew/bin/php -l core/bootstrap/bootstrap_error_handler_setup.php && /opt/homebrew/bin/php -l unit/core/bootstrap/BootstrapErrorHandlerSetupTest.php && /opt/homebrew/bin/php -l unit/core/bootstrap/ContextTest.php && /opt/homebrew/bin/php -l unit/core/bootstrap/ContextDefaultsFactoryTest.php && /opt/homebrew/bin/php -l unit/core/bootstrap/BootstrapContextFacadeTest.php && /opt/homebrew/bin/php -l unit/core/bootstrap/BootstrapSourceTest.php && /opt/homebrew/bin/php -l unit/core/bootstrap/ContextFactoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/core/bootstrap unit/core/LegacyDiSourceInventoryTest.php unit/core/FunctionsServiceContainerTest.php` | OK, 284 tests / 29210 assertions |
| `rg` metrics | OK, files=624, php_files=530, include/require=176, dynamic_new=1, superglobals/`$GLOBALS`=22, di_default_configured_files=0, di_static_default_helpers=65 |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml` | OK, 2446 tests / 37866 assertions |
| `/opt/homebrew/bin/php -l core/di/container_registry.php && /opt/homebrew/bin/php -l unit/core/di/ContainerProviderTest.php && /opt/homebrew/bin/php -l unit/core/FunctionsServiceContainerTest.php && /opt/homebrew/bin/php -l unit/core/LegacyDiSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ContainerProviderTest.php unit/core/FunctionsServiceContainerTest.php unit/core/LegacyDiSourceInventoryTest.php unit/core/bootstrap/ContainerRegistryStateFactoryTest.php unit/core/bootstrap/ContextContainerFactoryTest.php unit/core/bootstrap/ContextDefaultsFactoryTest.php unit/core/bootstrap/ContextTest.php unit/core/block/BaseTest.php unit/core/base/DataTest.php unit/core/exception/BaseTest.php` | OK, 239 tests / 28876 assertions |
| `rg` metrics | OK, files=621, php_files=527, include/require=172, dynamic_new=1, superglobals/`$GLOBALS`=21, di_default_configured_files=0, di_static_default_helpers=65 |
| `/opt/homebrew/bin/php -r 'require "core/bootstrap.php"; bootstrap::resetContext(); var_export(bootstrap::getConfigCache());'` | OK, bootstrap smoke возвращает `array ()` |
| `git diff --check` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml` | OK, 2439 tests / 37654 assertions |
| `/opt/homebrew/bin/php -l core/di/email_engine_factory.php && /opt/homebrew/bin/php -l core/factory/application_container_factory.php && /opt/homebrew/bin/php -l unit/core/di/EmailEngineFactoryTest.php && /opt/homebrew/bin/php -l unit/core/FunctionsServiceContainerTest.php && /opt/homebrew/bin/php -l unit/core/LegacyDiSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/EmailEngineFactoryTest.php unit/core/di/EmailServiceFactoryTest.php unit/core/service/EmailTest.php unit/core/service/email/PhpmailerTest.php unit/core/adapter/PhpMailerTest.php unit/core/FunctionsServiceContainerTest.php unit/core/LegacyDiSourceInventoryTest.php` | OK, 213 tests / 28555 assertions |
| `/opt/homebrew/bin/php -r 'require "core/bootstrap.php"; bootstrap::resetContext(); var_export(bootstrap::getConfigCache());'` | OK, bootstrap smoke возвращает `array ()` |
| `rg` metrics | OK, files=620, php_files=526, include/require=169, dynamic_new=1, superglobals/`$GLOBALS`=22, di_default_configured_files=0, di_static_default_helpers=65 |
| `git diff --check` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml` | OK, 2436 tests / 37526 assertions |
| `/opt/homebrew/bin/php -l core/di/container_registry.php && /opt/homebrew/bin/php -l core/di/container_registry_state.php && /opt/homebrew/bin/php -l core/di/container_registry_state_factory.php && /opt/homebrew/bin/php -l core/di/container_provider_factory.php && /opt/homebrew/bin/php -l unit/core/di/ContainerProviderTest.php && /opt/homebrew/bin/php -l unit/core/FunctionsServiceContainerTest.php && /opt/homebrew/bin/php -l unit/core/LegacyDiSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ContainerProviderTest.php unit/core/FunctionsServiceContainerTest.php unit/core/LegacyDiSourceInventoryTest.php` | OK, 185 tests / 27886 assertions |
| `/opt/homebrew/bin/php -r 'require "core/bootstrap.php"; bootstrap::resetContext(); var_export(bootstrap::getConfigCache());'` | OK, bootstrap smoke возвращает `array ()` |
| `git diff --check` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml` | OK, 2435 tests / 36989 assertions |
