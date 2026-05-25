# Актуальный отчет: что осталось от старого подхода

Дата анализа: 2026-06-03

Ветка: `migrate_to-php8`

Область анализа: `_core`, `_project`, `htdocs`, `cli`; без `vendor` и `legacy_assets`.

Важно: это срез текущей dirty рабочей копии. В дереве уже есть большой незакоммиченный слой PHP 8 / DI / adapter-модернизации, поэтому отчет описывает текущее состояние workspace, а не чистый git baseline.

## Короткий вывод

Проект уже почти полностью ушел от старого service-locator/procedural ядра. В production-срезе не найдены broad gateway/dispatcher маркеры: `containerService`, `getContainerService`, `blockService`, `resolveService`, `legacyService`, `container_registry::get()`, `::instance()`, `parse_ini_file()`, `call_user_func*`, `eval()`. Также не найдено прямое dynamic construction вида `new $className(...)`, INI-конфиги в runtime-срезе отсутствуют.

Важное отличие от предыдущих отчетов: nullable DI-хвост вида `private ?\Closure` уже закрыт. Текущий долг теперь не в nullable state, а в том, что DI пока часто выражен через `callable` / `\Closure` factory API: зависимости инжектятся явно, но еще не всегда как типизированные объекты, интерфейсы или узкие factory contracts.

## Статический срез

| Метрика | Текущее значение | Что означает |
|---|---:|---|
| Production PHP-файлов | 760 | Размер анализируемого runtime-среза. |
| Broad legacy gateway patterns | 0 | Старые service-locator/dispatcher входы по широкому grep не найдены. |
| Dynamic `new $className(...)` | 0 | Прямое variable-class construction не найдено. |
| `*.ini` в production-срезе | 0 | INI migration в текущем runtime-срезе закрыта. |
| `private ?\Closure` | 0 | Nullable closure-state хвост закрыт. |
| Nullable `Closure::fromCallable(... ? null : ...)` assignments | 0 | Старый nullable-lazy fallback pattern снят. |
| `private \Closure` | 595 | Основной переходный DI-след: зависимости уже explicit, но часто как closure factory. |
| `?callable` | 877 | Большой BC/API слой: optional callables используются как injectable overrides и fallback hooks. |
| Direct helper call lines | 73 | Общий grep по compatibility helpers; вне `_core/functions.php` и `_core/di/*` остался один method-name false-positive в `_core/service/timer.php`. |
| `include` / `require` grep matches | 211 | Ручная загрузка еще живет в bootstrap/defaults/loader/installer/template boundaries; часть совпадений - строки/метаданные. |
| Raw superglobals / `$GLOBALS` matches | 20 | В основном локализовано в request/session native adapters и diagnostics/comments. |
| `bootstrap::...` refs | 1 | Осталась meta-string ссылка в `_project/app/__tools/main/upgrade_blocks.meta.php`; executable refs не найдены по grep. |
| `class_exists` / reflection / configured instantiator grep matches | 340 | Config/meta/factory-driven runtime все еще сильно зависит от class-name resolution. |
| `*.meta.php` | 54 | Legacy meta-runtime остается отдельным динамическим слоем. |
| `*.tpl` | 46 | Template runtime еще требует отдельного compatibility refactor. |
| Top-level procedural functions | 11 | В основном `_core/functions.php` плюс ADOdb callback. |
| Direct `new \fan\project\exception\...` | 7 | Все совпадения находятся в named DI exception factory boundaries. |
| Direct `new \fan\core\exception\...` | 1 | Только `_core/factory/core_fatal_exception_factory.php`. |
| Direct `new \fan\core\di\*_factory(...)` | 12 | Concrete factory construction сидит в bootstrap/defaults composition roots. |
| `private static` in `_core/service`, `_core/base`, `_core/view`, `_core/bootstrap` | 5 | Остались private static helper methods, не static DI-state fields. |
| Service/base constructors без явных base deps | 0 | Старый `parent::__construct()` / `$allowIni` / `empty(self::$instances)` хвост в service/base constructors по grep не найден. |

## Таблица оставшегося долга

| Приоритет | Участок | Что осталось от старого подхода | Примеры файлов | Риск | Что рефакторить дальше |
|---:|---|---|---|---|---|
| 1 | Callable-heavy DI API | 595 `private \Closure` и 877 `?callable`. Это уже не service locator, но еще переходный стиль: зависимости часто выглядят как anonymous factory hooks, а не как typed service/factory contracts. | `_core/service/template.php`, `_core/service/email.php`, `_core/service/rest.php`, `_core/base/model/row.php`, `_core/bootstrap/*defaults_provider_factory.php` | Средний | Файл-за-файлом заменять generic callables на узкие interfaces/value objects/factory classes. Optional `?callable` оставлять только на внешних BC boundaries и в тестовых seam-ах. |
| 2 | Manual loading layer | 211 `include` / `require` grep matches. Большая часть легитимна для composition roots/adapters, но allowlist широкий и может скрыть возврат loading-а в leaf classes. | `_core/bootstrap/*defaults_provider_factory.php`, `_core/bootstrap/application.php`, `_core/bootstrap/loader.php`, `_core/di/*provider_factory.php`, `_core/adapter/compiled_template_loader.php`, `htdocs/install/incl/*` | Средний | Сужать allowlist по одному boundary-семейству. Hidden loads поднимать в explicit provider/defaults factories или autoload boundary и закреплять в `LegacyDiSourceInventoryTest`. |
| 3 | Bootstrap/context composition graph | Bootstrap уже тоньше, но graph собран большим количеством provider/defaults factories, ручных `require_once`, `class_exists` checks и concrete factory construction. | `_core/bootstrap.php`, `_core/bootstrap/application.php`, `_core/bootstrap/context*.php`, `_core/bootstrap/*defaults_provider_factory.php` | Средний | Оставить static `bootstrap` как BC-shell, а новые consumers вести через explicit `application` / `context` / runner objects. Уменьшать ручной wiring в provider factory слоях без возврата construction в leaf classes. |
| 4 | Config/meta-driven class resolution | Прямого `new $className(...)` нет, но class-name strings, `class_exists($className)`, reflection и configured instantiation остаются важным extension/runtime механизмом. | `_core/di/configured_class_instantiator.php`, `_core/factory/configured_service_factory.php`, `_core/base/model/entity.php`, `_core/service/reflector.php`, `_core/service/entity.php`, `_core/service/tab.php` | Средний | Разделить internal typed factory maps и внешний plugin/config extension API. Reflection оставить только для настоящих extension points и закрыть source guards для внутренних фабрик. |
| 5 | Legacy template/meta runtime | PHP meta files, `.tpl`, compiled template loader, parser state и file discovery остаются динамическим legacy-слоем. | `_core/service/template/*`, `_core/view/parser/*`, `_core/block/**/*.meta.php`, `_project/**/*.meta.php`, `_core/adapter/compiled_template_loader.php` | Средний | Планировать отдельным срезом после DI/bootstrap cleanup: typed contracts вокруг parser/compiled-template, старый runtime оставить compatibility adapter-ом. |
| 6 | Process-global request/session boundary | Raw globals уже в adapters, но request/session state все еще mutable process-level boundary. | `_core/adapter/request_input_native_environment.php`, `_core/adapter/request_input_globals.php`, `_core/adapter/adodb_session_native_environment.php`, `_core/adapter/adodb_session_globals.php`, `_core/service/error.php`, `_core/service/request.php` | Средний | Выше adapter-слоя передавать request/session context objects. Запретить новый direct access к superglobals вне native adapters/diagnostics. |
| 7 | Direct exception construction внутри DI boundary | Leaf/service/runtime throw-sites для service/block/template/database/date/error500/plain/project fatal уже сняты; direct exception `new` остался в named exception factories. | `_core/di/*exception_factory.php` | Низкий | Держать source guards. Следующий реальный долг уже не exception construction, а manual loading/bootstrap composition и config/meta runtime. |
| 8 | Крупные orchestration classes | Часть классов уже получает зависимости явно, но все еще содержит много orchestration logic, state decisions, config lookup и legacy guards. | `_core/block/base.php`, `_core/service/tab.php`, `_core/service/entity.php`, `_core/service/request.php`, `_core/bootstrap/loader.php`, `_core/service/database.php` | Средний | Брать узкими behavioral срезами: выделять state/context/factory objects рядом с тестами, не делать массовый rewrite. |
| 9 | Procedural utility/functions layer | Старые service helper wrappers сняты, но глобальные utility functions и entrypoint callbacks остаются compatibility API. | `_core/functions.php`, `cli/install.php`, `_core/service/database/adodb.php` | Низкий | Не трогать массово. Переносить только когда конкретная функция мешает тестируемости, autoload или изоляции состояния. |
| 10 | Legacy comments / dead references | Остались строковые legacy references. Они не ломают runtime, но мешают source-inventory отчетам и будущим grep-guard-ам. | `_project/app/__tools/main/upgrade_blocks.meta.php`, комментарии в request/model слоях | Низкий | Удалить stale comments/strings отдельным безопасным cleanup-срезом, если они не нужны как миграционная памятка. |

## Ближайшая очередь

| Шаг | Зона | Почему именно она | Минимальная проверка |
|---:|---|---|---|
| 1 | Callable-heavy DI в leaf/runtime классах | Nullable closure уже закрыт; следующий измеримый слой - заменить generic closure factories на typed collaborators там, где контракт стабилен. | `/opt/homebrew/bin/php -l` по touched files, focused PHPUnit рядом с классом, `LegacyDiSourceInventoryTest`, `CoreSourceInventoryTest`, `git diff --check`. |
| 2 | Manual loading allowlist | Loading statement-ов много, и именно здесь старый подход легче всего вернется в leaf code. | `LegacyDiSourceInventoryTest`, source grep по loading statements, `git diff --check`. |
| 3 | Bootstrap/context provider graph | DI уже есть, но composition root стал большим и ручным. | Bootstrap/context tests, CLI/web smoke, source guard на `new \fan\core\di\*_factory`. |
| 4 | Request/session context над adapters | Raw globals локализованы, следующий шаг - не протаскивать mutable process state выше boundary. | Request/session tests + grep superglobals. |
| 5 | Template/meta runtime plan | Слой рискованный и динамический, его лучше не смешивать с текущим DI cleanup. | Template/parser behavior tests + compiled-template smoke. |

## Что уже не главный долг

| Участок | Почему |
|---|---|
| Старые service-locator gateway names | По текущему production-grep 0 совпадений. |
| Dynamic `new $className(...)` | По текущему production-grep 0 совпадений. |
| Nullable closure-state | `private ?\Closure` и nullable `Closure::fromCallable` assignments дают 0 совпадений. |
| `block_context` reflection factory | `block_context` больше не хранит reflection dependency как `private \Closure` / `?callable`; он получает объект `reflection_class_factory` из container graph. |
| `reflector` reflection factory | `reflector` и `reflector_service_factory` больше не принимают reflection dependency как callable; `application_core_service_creator` передает объект `reflection_class_factory`. |
| `debug` reflection factory | `debug` и `debug_service_factory` больше не принимают reflection dependency как callable; `application_core_service_creator` передает объект `reflection_class_factory`. |
| Model entity reflection factory | `_core/base/model/entity.php` и `_core/factory/model_entity_factory.php` больше не хранят reflection dependency как `private \Closure`; defaults provider создает объектный `reflection_class_factory`. |
| Alias-based `new fatalException(...)` | Исполняемых совпадений нет. |
| Direct service fatal construction в leaf classes | Прямой `new \fan\project\exception\service\fatal` остался только в `_core/factory/service_exception_factory.php`. |
| Service constructors без base dependencies | По текущему grep не осталось `parent::__construct()` / `$allowIni` / `empty(self::$instances)` без явного DI-хвоста. |
| Native filesystem/header/session/request calls внутри adapters | Это intentional adapter layer; долг только если такие вызовы появляются вне adapters/entrypoints. |
| Concrete `new` внутри named provider/defaults factories | Это допустимый composition boundary, если leaf providers/services получают зависимости через constructor injection и source guards не дают construction расползаться обратно. |

## Команды среза

| Проверка | Результат |
|---|---|
| `find _core _project cli htdocs -type f -name '*.php'` | 760 PHP-файлов |
| broad gateway grep | 0 matches |
| `rg -n -P 'new\s+\$[A-Za-z_][A-Za-z0-9_]*\s*\(' _core _project cli htdocs -g '*.php'` | 0 matches |
| `rg --files -g '*.ini' _core _project cli htdocs` | 0 matches |
| `rg -n -F 'private ?\Closure' _core _project cli htdocs -g '*.php'` | 0 matches |
| nullable `Closure::fromCallable` assignment grep | 0 matches |
| `rg -n -F 'private \Closure' _core _project cli htdocs -g '*.php'` | 595 matches |
| `rg -n -F '?callable' _core _project cli htdocs -g '*.php'` | 877 matched lines |
| helper grep excluding `_core/functions.php` and `_core/di/*` | 1 false-positive method-name match |
| loading statement grep | 211 matches |
| superglobals / `$GLOBALS` grep | 20 matches |
| `rg -n 'bootstrap::' _core _project cli htdocs -g '*.php'` | 1 meta-string match |
| class resolution / reflection grep | 340 matches |
| `find _core _project cli htdocs -type f -name '*.meta.php'` | 54 files |
| `find _core _project cli htdocs -type f -name '*.tpl'` | 46 files |
| top-level function grep | 11 matches |
| project exception construction grep | 7 matches, all named DI exception factories |
| core exception construction grep | 1 match, `_core/factory/core_fatal_exception_factory.php` |
| direct core DI factory construction grep | 12 matches, all bootstrap/defaults composition roots |
| service/base constructor legacy-tail grep | 0 matches |

## Валидация

Этот проход был статическим аудитом и обновлением отчета. После обновления среза пройдены:

| Проверка | Результат |
|---|---|
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/CoreSourceInventoryTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK: 335 tests, 138534 assertions |
| `rg -n '[ \t]+$' ...` по затронутым PHP-файлам и отчету | 0 matches |
| `git diff --check` | OK |
