# Отчет: что осталось от старого подхода и что рефакторить дальше

Дата анализа: 2026-06-03

Ветка: `migrate_to-php8`

Область анализа: `_core`, `_project`, `cli`, `htdocs`; без `vendor` и `legacy_assets`.

Важно: это срез текущей dirty рабочей копии. В проекте уже есть большой незакоммиченный слой PHP 8 / DI / adapter-модернизации, поэтому отчет описывает текущее состояние workspace, а не чистый git baseline.

## Короткий вывод

Старый service-locator/procedural каркас почти снят. В production-срезе не найдены широкие gateway/dispatcher маркеры `containerService`, `getContainerService`, `blockService`, `resolveService`, `legacyService`, `container_registry::get()`, `::instance()`, `parse_ini_file()`, `call_user_func*`, `eval()`. Прямого dynamic construction вида `new $className(...)` тоже не найдено.

Основной оставшийся долг теперь не в явных gateway-вызовах, а в переходной обвязке вокруг DI и runtime: nullable/lazy closures, ручная загрузка файлов, большой bootstrap/defaults graph, config/meta/reflection runtime, process-global request/session adapters и template/meta слой.

## Статический срез

| Метрика | Текущее значение | Что означает |
|---|---:|---|
| Production PHP-файлов | 759 | Размер runtime-среза в `_core`, `_project`, `cli`, `htdocs`. |
| Broad legacy gateway patterns | 0 | Старые service-locator/dispatcher входы по широкому grep не найдены. |
| Dynamic `new $className(...)` | 0 | Прямое variable-class construction не найдено. |
| `*.ini` в production-срезе | 0 | INI migration в текущем runtime-срезе закрыта. |
| `private ?\Closure` | 82 | Главный маркер незавершенной DI-миграции: зависимость уже выделена, но хранится как nullable lazy fallback. |
| `private ?\Closure` в `_core/bootstrap` | 36 | Bootstrap/defaults graph остается самым плотным участком переходной DI-обвязки. |
| Nullable `Closure::fromCallable` assignments | 100 | Много мест еще используют `$x === null ? null : \Closure::fromCallable($x)` вместо non-null closure с fallback в constructor. |
| Direct helper call lines | 73 | Общий grep по legacy helpers. Вне `_core/functions.php` и `_core/di/*` остался только false-positive: `_core/service/timer.php` вызывает метод row object `get_class_name()`. |
| `include` / `require` matches | 210 | Ручная загрузка еще живет в bootstrap/defaults/loader/installer/template boundaries. |
| Raw superglobals / `$GLOBALS` matches | 20 | В основном локализовано в request/session native adapters и diagnostics/comments. |
| `class_exists` / reflection / configured instantiation | 331 | Config/meta-driven runtime все еще сильно зависит от class-name strings. |
| `*.meta.php` | 54 | Legacy meta-runtime остается отдельным динамическим слоем. |
| `*.tpl` | 46 | Template runtime еще требует отдельного compatibility refactor. |
| Top-level function definitions | 11 | В основном compatibility helpers и ADOdb callback. |
| Direct exception construction | 8 | Все совпадения находятся в named DI exception factory boundaries. |
| Direct `new \fan\core\di\*_factory(...)` | 12 | Concrete factory construction в основном сидит в bootstrap/defaults composition roots. |

## Таблица оставшегося долга

| Приоритет | Участок | Что осталось от старого подхода | Примеры файлов | Риск | Что рефакторить дальше |
|---:|---|---|---|---|---|
| 1 | Nullable/lazy DI closures | 82 `private ?\Closure` и 100 nullable `Closure::fromCallable` assignments. Это уже не старый service locator, но еще переходный стиль: nullable state + private lazy methods вместо обязательных constructor-injected closures. | `_core/factory/bootstrap/bootstrap_runtime_state_defaults_provider_factory.php`, `_core/factory/bootstrap/bootstrap_runtime_service_defaults_provider_factory.php`, `_core/factory/bootstrap/bootstrap_object_defaults_provider_factory.php`, `_core/service/template.php`, `_core/service/template/type/base.php`, `_core/service/email.php`, `_core/service/rest.php` | Средний | Продолжать файл-за-файлом: `private ?\Closure` -> `private \Closure`; optional `?callable` params остаются для BC, но constructor сразу кладет non-null `\Closure::fromCallable($injected ?? default)`. Удалять lazy helper methods там, где они только создают fallback. |
| 2 | Bootstrap/defaults composition graph | Bootstrap graph уже выделен в factories/providers, но остается большим, ручным и насыщенным fallback-ами, `require_once`, `class_exists` checks и concrete factory construction. | `_core/bootstrap/application.php`, `_core/bootstrap/context*.php`, `_core/bootstrap/*defaults_provider_factory.php`, `_core/bootstrap/request_input_*factory.php` | Средний | Оставить static `bootstrap` как BC-shell, а новые consumers вести через explicit `application` / `context` / runner objects. Сужать provider factories по одному семейству и закреплять source assertions. |
| 3 | Manual loading layer | 210 `include` / `require` matches. Большая часть легитимна как composition/loader boundary, но широкий allowlist может скрыть возврат ручной загрузки в leaf classes. | `_core/bootstrap/*provider_factory.php`, `_core/bootstrap/loader.php`, `_core/di/*provider_factory.php`, `_core/adapter/compiled_template_loader.php`, `htdocs/install/incl/*`, `cli/*defaults_provider_factory.php` | Средний | Разделить allowlist по boundary-семействам. Hidden loads поднимать в explicit provider/defaults factories или adapter boundary. Добавить guard против loading statements вне разрешенных boundaries. |
| 4 | Config/meta/reflection runtime | Прямого `new $className` нет, но 331 match по `class_exists`, `ReflectionClass`, `configured_class_instantiator` показывают сильную зависимость от class-name strings. | `_core/di/configured_class_instantiator.php`, `_core/factory/configured_service_factory.php`, `_core/service/entity.php`, `_core/service/tab.php`, `_core/base/model/entity.php`, `htdocs/install/incl/*defaults_provider_factory.php` | Средний | Разделить internal typed factory maps и внешний plugin/config extension API. Reflection оставить только для настоящих extension points. Внутренние сервисы переводить на явные factory contracts. |
| 5 | Process-global request/session boundary | Raw globals уже вынесены в adapters, но request/session state все еще зависит от process-global окружения. | `_core/adapter/request_input_native_environment.php`, `_core/adapter/request_input_globals.php`, `_core/adapter/adodb_session_native_environment.php`, `_core/adapter/adodb_session_globals.php`, `_core/service/error.php` | Средний | Выше adapter-слоя передавать request/session context objects. Закрепить guard: superglobals и `$GLOBALS` допустимы только в native adapters/diagnostics. |
| 6 | Legacy template/meta runtime | 54 meta-файла и 46 template-файлов остаются динамической подсистемой. Это отдельный compatibility слой, не просто DI-долг. | `_core/service/template/*`, `_core/view/parser/*`, `_core/block/**/*.meta.php`, `_project/**/*.meta.php`, `_core/adapter/compiled_template_loader.php` | Средний | Планировать отдельным срезом после DI/bootstrap cleanup: typed contracts вокруг parser/compiled-template, старый runtime оставить compatibility adapter-ом. |
| 7 | Service/template lazy dependencies | В leaf/runtime сервисах еще много nullable dependency closures, особенно в template/email/rest/cache/user/debug. Это делает тесты хрупкими и оставляет runtime fallback logic внутри сервисов. | `_core/service/template.php`, `_core/service/template/type/base.php`, `_core/service/email.php`, `_core/service/rest.php`, `_core/service/cache.php`, `_core/service/user.php`, `_core/base/data.php`, `_core/base/model/entity.php` | Средний | Там, где сервис уже получает factory/callable dependency, сделать ее non-null в constructor. Fallback оставить в constructor, а не в private lazy method. |
| 8 | Direct helper compatibility layer | Runtime direct helper calls почти закрыты: вне `_core/functions.php` и `_core/di/*` найден только false-positive `_core/service/timer.php:253` (`$timerRow->get_class_name()`). Сам compatibility файл еще остается. | `_core/functions.php`, `_core/di/*` fallback closures, `_core/service/timer.php` false-positive | Низкий | Держать source guards. Не удалять helpers массово, пока DI fallback-и и публичная совместимость не закрыты. |
| 9 | Direct exception construction | Прямые `new \fan\...\exception\...` остались только в named DI exception factories. Это уже правильная boundary, но ее нужно удерживать guards. | `_core/factory/fatal_exception_factory.php`, `_core/factory/service_exception_factory.php`, `_core/factory/error500_exception_factory.php`, `_core/factory/plain_exception_factory.php`, `_core/di/template_exception_factory.php` | Низкий | Не рефакторить как первый приоритет. Держать `LegacyDiSourceInventoryTest`; новые direct exception throw-sites должны идти через factory. |
| 10 | Procedural entrypoints / installer | `htdocs/install/*`, `cli/*`, entrypoint wrappers и defaults provider factories еще содержат procedural/loading стиль. Это ближе к composition boundary, но там много ручной логики. | `htdocs/index.php`, `htdocs/install/index.php`, `htdocs/install/incl/*`, `cli/install.php`, `cli/restore_password.php` | Средний | После bootstrap cleanup выделить installer/CLI application objects с явными dependencies, оставив entrypoints тонкими shell-файлами. |
| 11 | Legacy assets | `legacy_assets` исключен из runtime-аудита, но в репозитории остается старый FCKeditor/PHP connector слой. | `legacy_assets/admin/fckeditor/editor/filemanager/connectors/php/*` | Низкий-средний | Решить продуктово: удалить/архивировать вне runtime, или изолировать как unsupported legacy bundle. Не смешивать с DI-рефакторингом `_core`. |

## Рекомендуемая очередь

| Шаг | Зона | Почему сейчас | Минимальная проверка |
|---:|---|---|---|
| 1 | `_core/bootstrap/*defaults_provider_factory.php` nullable closures | Самый плотный и безопасно измеримый хвост после уже начатого DI cleanup. | `/opt/homebrew/bin/php -l` по файлу и тесту, focused PHPUnit для соответствующего factory test + `LegacyDiSourceInventoryTest` / `CoreSourceInventoryTest`, `git diff --check`. |
| 2 | `_core/bootstrap/request_input_*factory.php` | Небольшой кластер с process-global boundary; хорошо закрывается constructor-injected closures. | Request-input bootstrap tests + grep по `private ?\Closure` в `_core/bootstrap/request_input*`. |
| 3 | `_core/service/template*`, `_core/service/email.php`, `_core/service/rest.php` | Самые заметные service-level nullable closure хвосты. | Focused service/factory tests + source assertions против nullable lazy helpers. |
| 4 | Manual loading allowlist | После closure cleanup главный риск старого подхода - ручная загрузка вне boundaries. | New/updated source inventory guard по loading statements. |
| 5 | Config/meta/reflection runtime | Самый рискованный слой; лучше брать после стабилизации DI/composition graph. | Template/parser/model/entity behavior tests + smoke для compiled-template/meta loading. |

## Что уже не главный долг

| Участок | Почему |
|---|---|
| Старые service-locator gateway names | По текущему production grep 0 совпадений. |
| Dynamic `new $className(...)` | По текущему production grep 0 совпадений. |
| INI config loader | `*.ini` и `parse_ini_file()` в production-срезе не найдены. |
| Runtime global helper callers | Вне `_core/functions.php` и `_core/di/*` остался только method-call false-positive. |
| Direct exception construction в leaf classes | Совпадения находятся только в named DI exception factories. |
| Block-layer array helpers | По текущим guard-отчетам этот срез уже закрыт; новый долг не там. |

## Команды среза

| Проверка | Результат |
|---|---|
| `find _core _project cli htdocs -type f -name '*.php' \| wc -l` | 759 |
| `rg -n -e 'containerService' -e 'getContainerService' -e 'blockService' -e 'resolveService' -e 'legacyService' -e 'container_registry::get' -e '::instance\(' -e 'parse_ini_file\(' -e 'call_user_func' -e 'eval\(' _core _project cli htdocs -g '*.php'` | 0 matches |
| `rg -n -P 'new\s+\$[A-Za-z_][A-Za-z0-9_]*\s*\(' _core _project cli htdocs -g '*.php'` | 0 matches |
| `rg --files -g '*.ini' _core _project cli htdocs` | 0 matches |
| `rg -n -F 'private ?\Closure' _core _project cli htdocs -g '*.php' \| wc -l` | 82 |
| `rg -n -F 'private ?\Closure' _core/bootstrap -g '*.php' \| wc -l` | 36 |
| `rg -n -P '\$this->[A-Za-z_][A-Za-z0-9_]*\s*=\s*\$[A-Za-z_][A-Za-z0-9_]*\s*===\s*null\s*\?\s*null\s*:\s*\\Closure::fromCallable\(' _core _project cli htdocs -g '*.php' \| wc -l` | 100 |
| `rg -n '\b(adduceToArray\|array_merge_recursive_alt\|array_val\|get_class_name\|get_class_alt\|get_ns_name\|is_array_alt\|explode_alt\|increaseNum\|decreaseNum)\s*\(' _core _project cli htdocs -g '*.php' \| wc -l` | 73 |
| Same helper grep excluding `_core/functions.php` and `_core/di/*` | 1 match; `_core/service/timer.php` method-call false-positive |
| `rg -n '\b(require\|require_once\|include\|include_once)\b' _core _project cli htdocs -g '*.php' \| wc -l` | 210 |
| `rg -n '\$_(GET\|POST\|REQUEST\|COOKIE\|SERVER\|SESSION\|FILES)\|\$GLOBALS' _core _project cli htdocs -g '*.php' \| wc -l` | 20 |
| `rg -n -P '\bclass_exists\s*\(\|\binterface_exists\s*\(\|\btrait_exists\s*\(\|\bnew\s+\\Reflection\|\bReflectionClass\b\|configured_class_instantiator' _core _project cli htdocs -g '*.php' \| wc -l` | 331 |
| `find _core _project cli htdocs -type f -name '*.meta.php' \| wc -l` | 54 |
| `find _core _project cli htdocs -type f -name '*.tpl' \| wc -l` | 46 |
| `rg -n -P '^\s*function\s+[A-Za-z_][A-Za-z0-9_]*\s*\(' _core _project cli htdocs -g '*.php' \| wc -l` | 11 |
| `rg -n -P 'new\s+\\fan\\(?:core\|project)\\exception\\' _core _project cli htdocs -g '*.php' \| wc -l` | 8 |
| `rg -n -P 'new\s+\\fan\\core\\di\\[A-Za-z_][A-Za-z0-9_]*_factory\s*\(' _core _project cli htdocs -g '*.php' \| wc -l` | 12 |

## Валидация

Этот проход был статическим аудитом и созданием отчета. Кодовые файлы проекта не менялись, PHPUnit не запускался.
