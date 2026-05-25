# Сводный отчет: остатки старого подхода и следующие рефакторинги

Дата анализа: 2026-06-03

Ветка: `migrate_to-php8`

Область анализа: `_core`, `_project`, `cli`, `htdocs`; без `vendor` и `legacy_assets`.

Важно: это анализ текущей dirty рабочей копии. В проекте уже есть большой незакоммиченный слой PHP 8 / DI / adapter-модернизации, поэтому таблица описывает текущее состояние workspace, а не чистый git baseline.

## Короткий вывод

Старый service-locator/procedural каркас уже в основном разобран. Широкие gateway-паттерны (`containerService`, `getContainerService`, `blockService`, `resolveService`, `legacyService`, `container_registry::get`, `::instance`, `parse_ini_file`, `call_user_func*`, `eval`) в production PHP-срезе не найдены. Прямой dynamic construction вида `new $className(...)` также не найден.

Главный оставшийся долг сейчас не в direct helper callers. Runtime helper-вызовы вне `_core/functions.php` и `_core/di/*` фактически очищены: единственный grep-хвост `_core/service/timer.php:253` является method-call false-positive (`$timerRow->get_class_name()`), а не вызовом глобального helper. Следующий полезный фокус: ручная загрузка файлов, bootstrap/defaults graph, reflection/config-driven factories, process-global adapters и legacy meta/template runtime.

## Статический срез

| Метрика | Текущее значение | Интерпретация |
|---|---:|---|
| Production PHP-файлов | 755 | Размер анализируемого runtime-среза. |
| Broad legacy gateway patterns | 0 | Старые service-locator/dispatcher входы по широкому grep не найдены. |
| Dynamic `new $className(...)` | 0 | Прямое variable-class construction не найдено. |
| `*.ini` в production-срезе | 0 | INI migration в текущем runtime-срезе закрыта. |
| Direct helper lines/calls | 73 строки / 74 вызова | Общий grep по compatibility helpers, включая `_core/functions.php` и DI fallback closures. |
| Direct helper lines вне `_core/functions.php` и `_core/di/*` | 1 строка | Это `_core/service/timer.php:253`, method-call false-positive. |
| `include` / `require` matches | 210 | Ручная загрузка еще живет в bootstrap/defaults/loader/installer/template boundaries. CLI restore-password и install context include-scope зависимости уже заменены explicit provider boundaries. |
| `getService()` / `service()` matches | 19 | Остался доменный Service Locator-хвост в model/entity слое, плюс один exception getter. |
| Raw superglobals / `$GLOBALS` matches | 20 | В основном native request/session adapters, diagnostics и comments. |
| Reflection / `class_exists` / configured instantiator matches | 307 | Config/meta-driven class resolution остается важным runtime механизмом. |
| `*.meta.php` | 54 | Legacy meta-runtime остается отдельной динамической подсистемой. |
| `*.tpl` | 46 | Template runtime еще требует отдельного compatibility refactor. |

## Основная таблица долга

| Приоритет | Зона | Что осталось от старого подхода | Примеры файлов | Риск | Что рефакторить дальше |
|---:|---|---|---|---|---|
| 1 | Manual loading layer | 210 `include` / `require` matches. Часть уже является допустимым boundary, но allowlist все еще слишком широкий и скрывает реальные dependency edges. | `_core/bootstrap/*provider_factory.php`, `_core/di/*provider_factory.php`, `_core/bootstrap/loader.php`, `_core/adapter/compiled_template_loader.php`, `htdocs/install/incl/*`, `cli/*defaults_provider_factory.php` | Средний | Идти маленькими семействами: installer, затем bootstrap provider factories. Hidden loads поднимать в explicit providers/adapters и закреплять source guard в `LegacyDiSourceInventoryTest`. |
| 2 | Bootstrap/defaults composition graph | DI уже есть, но graph собирается вручную через множество defaults/provider/factory классов и точечных `require_once`. Это лучше старого singleton-подхода, но все еще переходный каркас. | `_core/bootstrap.php`, `_core/bootstrap/application.php`, `_core/bootstrap/context*.php`, `_core/bootstrap/*defaults_provider_factory.php` | Средний | Оставить static bootstrap как BC-shell. Новые consumers вести через explicit `application`, `context`, `runner` objects. Постепенно уменьшать ручные `require_once` в provider factories. |
| 3 | Config/meta/reflection runtime | Прямого `new $className(...)` нет, но class-name strings, `class_exists` и `ReflectionClass` остаются центральным механизмом расширения. | `_core/di/configured_class_instantiator.php`, `_core/di/*_factory.php`, `_core/service/entity.php`, `_core/service/tab.php`, `_core/base/model/entity.php`, `_core/service/translation.php` | Средний | Разделить typed internal factory maps и внешний extension API. Reflection оставить только в настоящих extension points, а core services переводить на явные factories. |
| 4 | Model/entity Service Locator хвост | В model/entity слое остались вызовы `getService()` для доступа к entity-service операциям. Это уже не глобальный helper, но все еще обратная зависимость модели на service locator. | `_core/base/model/entity.php`, `_core/base/model/row.php`, `_core/base/model/spec_file/row.php`, `_core/base/model/file_data/row.php`, `_core/base/model/request.php`, `_core/service/entity/description.php`, `_core/service/entity/designer/snippety.php` | Средний | Выделять конкретные collaborators: entity lookup, description factory, designer factory, file-data row factory, encapsulant/id decryptor. Начинать с одного метода/пути и фиксировать тестом поведение. |
| 5 | Process-global request/session boundary | Raw globals в основном локализованы в adapters, но runtime все еще работает с mutable process state через request/session native environment. | `_core/adapter/request_input_native_environment.php`, `_core/adapter/request_input_globals.php`, `_core/adapter/adodb_session_native_environment.php`, `_core/adapter/adodb_session_globals.php`, `_core/service/error.php` | Средний | Выше adapter-слоя передавать request/session context objects. Добавить guard: новые superglobals разрешены только в native adapters/diagnostics. |
| 6 | Legacy template/meta runtime | 54 meta-файла и 46 template-файлов остаются динамической подсистемой с compiled-template совместимостью. | `_core/service/template/*`, `_core/view/parser/*`, `_core/block/**/*.meta.php`, `_project/**/*.meta.php`, `_project/**/*.tpl` | Средний | Делать отдельным срезом после bootstrap/manual-load cleanup: typed contracts вокруг parser/compiled-template, старый runtime оставить compatibility adapter-ом. |
| 7 | Compatibility helpers in `_core/functions.php` | Runtime callers почти очищены, но compatibility helper definitions и DI fallback closures остаются. | `_core/functions.php`, `_core/di/application_support_service_registrar.php`, `_core/di/*factory.php` | Низкий-средний | Не удалять массово. Сначала гарантировать, что все composition roots дают нужные callables. Потом постепенно убирать DI fallback-и и оставлять `_core/functions.php` как узкую BC-зону. |
| 8 | Installer procedural boundary | Installer уже частично переведен на context/application/factory objects, но еще имеет ручные includes и отдельную mini-архитектуру. | `htdocs/install/index.php`, `htdocs/install/incl/base.php`, `htdocs/install/incl/context_factory.php`, `htdocs/install/incl/application_factory.php`, `htdocs/install/incl/php_array_file_loader.php` | Средний | Свести installer composition к одному application factory, затем убрать дублирующиеся `require_once` из `base.php` / `context_factory.php`. |
| 9 | CLI entrypoints | `cli/restore_password.php` уже не включает `_core/ini_cli.php`; `cli/install_context_factory.php` уже не владеет concrete request/storage deps. Оба CLI entrypoints теперь идут через explicit defaults provider boundaries. | `cli/install.php`, `cli/install_context_defaults_provider_factory.php`, `cli/restore_password.php`, `_core/ini_cli.php`, `_core/factory/bootstrap/cli_application_initializer_defaults_provider_factory.php` | Низкий | Держать source guards. Следующий ручной loading долг уже находится в web installer и bootstrap provider factories. |
| 10 | Legacy comments/string refs | Есть неисполняемые строки и comments, которые шумят в grep-инвентаризации. | `_core/service/request.php`, `_core/base/model/file_data/row.php`, `_core/block/root/html.php`, meta-string refs | Низкий | Почистить отдельным documentation/source-inventory срезом, чтобы future grep был чище и меньше давал false positives. |

## Что уже не главный долг

| Участок | Почему |
|---|---|
| Broad service-locator gateways | По текущему production grep 0 совпадений. |
| `new $className(...)` | Прямой dynamic construction не найден. |
| INI config loader | `*.ini` и `parse_ini_file()` в production-срезе не найдены. |
| Runtime direct helper callers | Вне `_core/functions.php` и `_core/di/*` остался только method-call false-positive в timer row. |
| Block-layer array helpers | В `_core/block` и `_project/block` прямых `adduceToArray`, `array_merge_recursive_alt`, `array_val` нет. |
| Service helper slices | Уже закрыты основные helper-срезы: request, base service, curl, session, cache/memcache, user, locale, meta maker, header, config/config-row, entity delegate/description/designer, template, root-html, block-base, spec-file-row, model-entity. |
| Direct exception construction in leaf classes | Оставшиеся прямые constructions находятся в named DI exception factories. |
| `cli/restore_password.php` include-scope bootstrap | Прямой `include __DIR__ . '/../_core/ini_cli.php';` убран; entrypoint теперь получает bootstrap application через `cli_application_initializer_defaults_provider_factory`. |
| `cli/install_context_factory.php` concrete defaults | Прямые `bootstrap_request_input_defaults_provider_factory` / `cli_install_file_storage` зависимости вынесены в `cli/install_context_defaults_provider_factory.php`; сама context factory теперь только injected factory. |

## Рекомендуемая очередь работ

| Шаг | Срез | Почему именно он | Проверка |
|---:|---|---|---|
| 1 | Web installer includes | Локальная зона с несколькими явными `require_once` и оставшимся `self::requestInputFactory()` в `htdocs/install/incl/context_factory.php`; меньше риск сломать runtime request path, чем в общем bootstrap. | `php -l htdocs/install/incl/*.php`; focused `unit/htdocs/*` и installer source guards. |
| 2 | Bootstrap/provider factories | Самый большой источник manual loading; делать после CLI/installer, когда шаблон уже понятен. | Source inventory на allowed loading boundaries; focused bootstrap tests. |
| 3 | Model/entity `getService()` хвост | Это следующий архитектурный долг после helper cleanup: модель все еще тянет сервисные операции назад. | Focused model/entity tests; source guard на новые `getService()` usages. |
| 4 | Template/meta runtime | Самая динамическая и рискованная зона; лучше не смешивать с DI cleanup. | Template/parser behavior tests, compiled-template smoke, `git diff --check`. |

## Команды среза

| Проверка | Результат |
|---|---|
| `rg --files _core _project cli htdocs -g '*.php'` | 755 файлов |
| `rg -n '\b(adduceToArray\|array_merge_recursive_alt\|array_val\|get_class_name\|get_class_alt\|get_ns_name\|is_array_alt\|explode_alt\|increaseNum\|decreaseNum)\s*\(' _core _project cli htdocs -g '*.php' -g '!_core/functions.php' -g '!_core/di/**'` | 1 match: `_core/service/timer.php:253`, method-call false-positive |
| `rg --stats -n '(include\|require)(_once)?\b' _core _project cli htdocs -g '*.php'` | 210 matches |
| `rg --stats -n '\b(service\|getService)\s*\(' _core _project cli htdocs -g '*.php'` | 19 matches |
| `rg -n '\$_(GET\|POST\|REQUEST\|SERVER\|SESSION\|COOKIE\|FILES)\|\$GLOBALS' _core _project cli htdocs -g '*.php'` | 20 matches |
| `rg --stats -n 'class_exists\s*\(\|interface_exists\s*\(\|trait_exists\s*\(\|new\s+\\Reflection\|ReflectionClass\|configured_class_instantiator' _core _project cli htdocs -g '*.php'` | 307 matches |
| `rg --files _core _project cli htdocs -g '*.meta.php'` | 54 files |
| `rg --files _core _project cli htdocs -g '*.tpl'` | 46 files |
