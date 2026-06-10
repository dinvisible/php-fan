# Отчет: что осталось от старого подхода

Дата анализа: 2026-06-03

Область анализа: `core`, `project`, `cli`, `htdocs`; без `vendor` и `legacy_assets`.

Важно: это срез текущей dirty рабочей копии. В проекте уже есть большой незакоммиченный слой PHP 8, DI, adapters и source-inventory тестов, поэтому отчет описывает текущий workspace, а не чистый git baseline.

## Короткий вывод

Старый service-locator/procedural gateway слой практически снят: в production-срезе не найдены broad gateway markers, `::instance()`, `parse_ini_file()`, `call_user_func*`, `eval()` и прямой `new $className(...)`. INI-конфиги в production-срезе тоже не найдены.

Главный оставшийся долг теперь находится не в одном файле, а в runtime boundaries:

- ручная загрузка файлов;
- большой bootstrap/context composition graph;
- config/meta/reflection runtime;
- process-global request/session environment;
- legacy template/meta подсистема;
- compatibility helpers, которые уже почти вынесены в DI-boundary, но еще остаются как BC-слой.

## Статический срез

| Метрика | Текущее значение | Вывод |
|---|---:|---|
| Production PHP-файлов | 759 | Размер текущего runtime-среза растет из-за новой DI/bootstrap/adapters инфраструктуры. |
| Broad legacy gateway patterns | 0 | `containerService`, `getContainerService`, `blockService`, `resolveService`, `legacyService`, `container_registry::get`, `::instance()`, `parse_ini_file()`, `call_user_func*`, `eval()` не найдены. |
| Dynamic `new $className(...)` | 0 | Прямое variable-class construction не найдено. |
| `*.ini` в production-срезе | 0 | INI migration в текущем runtime-срезе закрыта. |
| Direct helper call lines / calls | 73 строки / 74 вызова | Общий grep по compatibility helpers, включая `core/functions.php` и DI fallback closures. |
| Direct helper lines вне `core/functions.php` и `core/di/*` | 1 строка | Осталась строка `core/service/timer.php`, но это method-call false-positive: `$timerRow->get_class_name()`. |
| `include` / `require` grep matches | 210 | Ручная загрузка еще жива в bootstrap/defaults/provider/installer/template boundaries; есть несколько false-positive строк в meta/template arrays. |
| Raw superglobals / `$GLOBALS` matches | 20 | В основном native request/session adapters и diagnostics; часть совпадений в комментариях. |
| Reflection / class resolution matches | 327 | Config/meta-driven class resolution остается центральным runtime механизмом. |
| `*.meta.php` | 54 | Legacy meta-runtime остается отдельным динамическим слоем. |
| `*.tpl` | 46 | Template runtime еще требует отдельного compatibility refactor. |
| Top-level function definitions | 11 | В основном `core/functions.php` плюс ADOdb callback. |
| Direct `new \fan\project\exception...` | 7 | Все совпадения находятся в named DI exception factories. |
| `new fatalException(...)` alias | 0 | Alias-based service fatal хвост закрыт в текущем срезе. |
| `bootstrap::` refs | 1 строка | Осталась meta-string замена в `project/app/__tools/main/upgrade_blocks.meta.php`; исполняемых прямых refs не видно. |

## Таблица оставшегося долга

| Приоритет | Участок | Что осталось от старого подхода | Примеры файлов | Риск | Что рефакторить дальше |
|---:|---|---|---|---|---|
| 1 | Manual loading layer | 210 `include` / `require` grep matches. Большая часть уже осознанно разрешена как composition/adapter boundary, но allowlist очень широкий. | `core/bootstrap/*defaults_provider_factory.php`, `core/di/*provider_factory.php`, `core/bootstrap/application.php`, `core/bootstrap/loader.php`, `core/adapter/compiled_template_loader.php`, `htdocs/install/incl/*` | Средний | Сужать allowlist по одному семейству. Hidden loads поднимать в explicit providers/adapters и закреплять в `LegacyDiSourceInventoryTest`. |
| 2 | Bootstrap/context composition graph | Static `bootstrap` стал тонким facade, но lifecycle все еще process-level: singleton `application`, lazy defaults, ручное wiring дерево. | `core/bootstrap.php`, `core/bootstrap/application.php`, `core/bootstrap/context*.php`, `core/bootstrap/application_container_*` | Средний | Оставить `bootstrap` как BC-shell, а новые consumers вести через explicit `application`, `context`, runner/container objects. |
| 3 | Config/meta/reflection runtime | Прямого `new $className` нет, но class-name strings, `class_exists`, `ReflectionClass` и `configured_class_instantiator` остаются главным extension mechanism. | `core/di/configured_class_instantiator.php`, `core/factory/configured_service_factory.php`, `core/service/entity.php`, `core/service/tab.php`, `core/base/model/entity.php` | Средний | Разделить internal typed factory maps и внешний plugin/config extension API. Reflection оставить только в настоящих extension points. |
| 4 | Process-global request/session boundary | Superglobals локализованы лучше, но request/session state все еще читается из `$GLOBALS`, `$_SERVER` и mutable session root через native environment adapters. | `core/adapter/request_input_native_environment.php`, `core/adapter/request_input_globals.php`, `core/adapter/adodb_session_native_environment.php`, `core/adapter/adodb_session_globals.php`, `core/service/error.php` | Средний | Выше adapter-слоя передавать request/session context objects; держать guard против новых superglobals вне adapters/diagnostics. |
| 5 | Legacy template/meta runtime | 54 meta-файла и 46 template-файлов остаются динамической подсистемой с parser/compiled-template совместимостью. | `core/service/template/*`, `core/view/parser/*`, `core/block/**/*.meta.php`, `project/**/*.meta.php`, `core/adapter/compiled_template_loader.php` | Средний | Планировать отдельным срезом: typed contracts вокруг parser/compiled-template, старый runtime оставить compatibility adapter-ом. |
| 6 | Compatibility helper layer | Runtime direct helper callers фактически очищены, но `core/functions.php` и DI fallback closures все еще являются BC-слоем. | `core/functions.php`, `core/di/application_support_service_registrar.php`, service factory fallback closures | Низкий-средний | Не удалять массово. Сначала закрепить source guards; затем убирать fallback-и только после гарантии, что все composition roots передают typed callables. |
| 7 | Installer/CLI compatibility roots | Installer и CLI уже получили factories/providers, но все еще имеют отдельную ручную загрузку и локальный composition root. | `cli/install.php`, `cli/install_context_defaults_provider_factory.php`, `htdocs/install/index.php`, `htdocs/install/incl/*` | Средний | Свести installer/CLI composition к тем же bootstrap defaults primitives, где это не ломает автономный installer. |
| 8 | Large legacy service/block classes | Многие классы уже получают зависимости через DI, но orchestration/state все еще крупные и смешивают доменную логику с runtime coordination. | `core/service/tab.php`, `core/service/entity.php`, `core/service/request.php`, `core/service/database.php`, `core/block/base.php` | Средний | Брать узкими behavioral срезами: выделять state/context/factory objects рядом с существующими тестами, без массового rewrite. |
| 9 | Source inventory maintenance | Guard-тесты уже закрывают много старых паттернов, но allowlist по loading/reflection все еще широкая и может стать новым мусорным ящиком. | `unit/core/LegacyDiSourceInventoryTest.php`, `unit/core/CoreSourceInventoryTest.php` | Низкий-средний | После каждого закрытого среза ужесточать allowlist и добавлять точечный forbidden-pattern тест. |
| 10 | Dead comments/string refs | Остались grep-шум и старые string/meta refs, которые не являются runtime-долгом, но мешают аудиту. | `project/app/__tools/main/upgrade_blocks.meta.php`, комментарии в request/model слоях | Низкий | Отдельный cleanup-срез, если нужно сделать source inventory чище. |

## Что уже не считать главным долгом

| Участок | Почему |
|---|---|
| Старые service-locator gateway names | По текущему production grep 0 совпадений. |
| Dynamic `new $className(...)` | По текущему production grep 0 совпадений. |
| INI config loader | `*.ini` и `parse_ini_file()` в production-срезе не найдены. |
| Alias-based `new fatalException(...)` | По текущему production grep 0 совпадений. |
| Direct concrete exception creation in leaf classes | Осталась только в named DI exception factories. |
| Runtime direct helper calls | Вне `core/functions.php` и `core/di/*` остался один false-positive method call. |
| Concrete construction in named DI factories | Это текущий composition boundary, а не legacy smell сам по себе. |
| Native filesystem/header/session/request calls inside adapters | Это intentional adapter layer; долг возникает только если такие вызовы возвращаются в services/blocks/models. |

## Рекомендуемая очередь

| Шаг | Зона | Почему сейчас | Минимальная проверка |
|---:|---|---|---|
| 1 | Сузить loading allowlist | Это самый широкий оставшийся old-style boundary после helper/exception cleanup. | `LegacyDiSourceInventoryTest`, grep loading statements, focused tests для выбранного provider family. |
| 2 | Разобрать bootstrap/context graph | Static facade уже тонкий, но lifecycle еще не полностью explicit. | Bootstrap/context unit tests, bootstrap smoke через `/opt/homebrew/bin/php`. |
| 3 | Спроектировать reflection policy | 327 class-resolution matches показывают, что dynamic extension API остается большим. | Source inventory по `class_exists` / `ReflectionClass`, focused factory tests. |
| 4 | Закрепить globals policy | Superglobals сейчас локализованы, но это легко сломать новым кодом. | Guard: запретить `$_*` / `$GLOBALS` вне adapters/diagnostics. |
| 5 | Отдельный template/meta plan | Самый рискованный behavioral слой; не смешивать с DI cleanup. | Template/parser behavior tests, compiled-template smoke. |

## Команды среза

| Проверка | Результат |
|---|---|
| `find core project cli htdocs -type f -name '*.php' \| wc -l` | 759 |
| Broad legacy gateway grep | 0 matches |
| `rg -n -P 'new\s+\$[A-Za-z_][A-Za-z0-9_]*\s*\(' core project cli htdocs -g '*.php'` | 0 matches |
| `rg --files -g '*.ini' core project cli htdocs` | 0 matches |
| Helper grep | 73 matching lines / 74 calls |
| Helper grep excluding `core/functions.php` and `core/di/*` | 1 matching line; `core/service/timer.php` method-call false-positive |
| Loading grep | 210 matches |
| Superglobal grep | 20 matches |
| Reflection/class-resolution grep | 327 matches |
| `find core project cli htdocs -type f -name '*.meta.php' \| wc -l` | 54 |
| `find core project cli htdocs -type f -name '*.tpl' \| wc -l` | 46 |
| `rg -n -F 'new \fan\project\exception\service\fatal' core project cli htdocs -g '*.php'` | 1 match: `core/factory/service_exception_factory.php` |
| `rg -n -F 'new fatalException' core project cli htdocs -g '*.php'` | 0 matches |
| `rg -n 'bootstrap::' core project cli htdocs -g '*.php'` | 1 meta-string match |

## Валидация

Код в этом проходе не менялся. Добавлен только этот отчет.
