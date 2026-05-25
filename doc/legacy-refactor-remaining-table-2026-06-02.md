# Отчет: остатки старого подхода и очередь рефакторинга

Дата анализа: 2026-06-02

Область анализа: `_core`, `_project`, `htdocs`, `cli`; без `vendor` и `legacy_assets`.

Важно: отчет построен по текущей dirty рабочей копии. В проекте уже есть большой незакоммиченный слой PHP 8/DI/adapters модернизации, поэтому это срез текущего workspace, а не чистого git baseline.

## Короткий вывод

Проект уже заметно ушел от старого service-locator/procedural подхода. По текущему production-срезу не найдены прежние gateway/dispatcher маркеры (`containerService`, `getContainerService`, `blockService`, `resolveService`, `legacyService`, `container_registry::get()`, `::instance()`, `parse_ini_file()`, `call_user_func*`, `eval()`), а прямой dynamic construction вида `new $className(...)` отсутствует.

Главный оставшийся долг теперь не в массовом service locator. Он сосредоточен в explicit composition/loading boundaries: static bootstrap facade, ручной `require_once`, process-global request/session окружение, config/meta-driven construction, template/meta runtime и крупные legacy service/block классы. View router construction уже вынесен из parser/block runtime в `_core/factory/view_router_factory.php`, view keeper / loader keeper construction вынесен в explicit DI factories, delayed meta construction вынесен в `_core/factory/delayed_meta_factory.php`, base service / single service fatal construction вынесен в `_core/factory/service_exception_factory.php`, `log`/`matcher` service fatal leaf throw-sites идут через inherited `createServiceFatalException()`, config service fatal construction в `_core/service/config.php` идет через inherited `createServiceFatalException()`, а config row service fatal construction идет через injected factory from `config_row_factory`.

## Статический срез

| Метрика | Значение | Вывод |
|---|---:|---|
| Production PHP-файлов | 740 | Размер текущего production-среза после DI/defaults/provider factory модернизации и добавления database/config/service exception factory boundaries. |
| Legacy service-locator / dispatcher markers | 0 | Старые gateway-вызовы по broad grep не найдены. |
| Dynamic `new $className(...)` | 0 | Прямой variable-class construction в production-срезе не найден. |
| `bootstrap::...` refs | 1 meta string / 0 executable refs | Runtime почти снят с прямых static-вызовов; осталась строка-алиас в meta-файле. |
| `include` / `require` matches | 203 | Ручная загрузка еще живет в bootstrap/defaults/loaders/installer/template boundaries. |
| Raw superglobals / `$GLOBALS` matches | 20 | Основной доступ локализован в native request/session adapters и request/error diagnostics. |
| Top-level procedural functions | 12 | Остались helper functions, installer entrypoint helper и ADOdb error handler. |
| Template/meta runtime | 54 `*.meta.php`, 46 `*.tpl` | Legacy template/meta подсистема остается отдельным динамическим слоем. |

## Таблица остаточного долга

| Приоритет | Зона | Что осталось от старого подхода | Где смотреть | Риск | Что рефакторить дальше |
|---:|---|---|---|---|---|
| 1 | Static bootstrap lifecycle | `bootstrap` остается compatibility facade с process-level `static $application`; методы только делегируют в application/context, но lifecycle ownership еще не полностью явный. | `_core/bootstrap.php`; особенно `setApplication()`, `resetContext()`, `init()`, `run()`, `runCli()`, `applicationFromDefaults()` | Средний | Оставить `bootstrap` как BC-shell, но новые/переписываемые consumers переводить на explicit `application` / `context` / runner objects. |
| 2 | Manual loading layer | В production остается 203 `include` / `require` совпадений. Часть легитимна для composition roots, но allowlist все еще широкий. | `_core/bootstrap/*defaults_provider_factory.php`, `_core/bootstrap/application.php`, `_core/bootstrap/loader.php`, `_core/di/*provider_factory.php`, `htdocs/install/incl/*` | Средний | Сужать allowlist по одному boundary-семейству: переносить hidden loads вверх в explicit defaults/provider factories и закреплять source guard-ами. |
| 3 | Bootstrap/context composition boundaries | Context/bootstrap factories уже стали явными composition roots, но все еще держат много ручного wiring-а и `new` в provider factories. | `_core/factory/bootstrap/context_defaults_provider_factory.php`, `_core/factory/bootstrap/context_core_defaults_provider_factory.php`, `_core/factory/bootstrap/context_error_handling_defaults_provider_factory.php`, `_core/factory/bootstrap/context_support_defaults_provider_factory.php`, `_core/factory/bootstrap/bootstrap_runtime_defaults_provider_factory.php` | Средний | Продолжать дробить крупные context/bootstrap subgraphs на smaller provider factories только там, где это уменьшает связность, не плодя бессмысленные wrapper-и. |
| 4 | Application-container composition | DI dependency provider уже constructor-injected, но root composition все еще собирает registry/factory-provider/registrar/creator/container callable вручную. | `_core/factory/bootstrap/application_container_defaults_provider_factory.php`, `_core/factory/bootstrap/application_container_factory_callable_factory.php`, `_core/di/application_container_dependency_provider.php` | Низкий-средний | Держать concrete wiring только в bootstrap composition boundaries; не возвращать `new`/loading в `application_container_dependency_provider`. |
| 5 | Process-global request/session окружение | Raw globals изолированы adapter-ами, но runtime state остается mutable process-global boundary. | `_core/adapter/request_input_native_environment.php`, `_core/adapter/adodb_session_native_environment.php`, `_core/service/request.php`, `_core/service/error.php` | Средний | Выше adapter-слоя передавать request/session context objects; запретить новый прямой access к superglobals вне adapters/diagnostics. |
| 6 | Config/meta-driven construction | Прямого `new $className(...)` уже нет, но class-name based config и reflection/configured construction остаются extension boundary. | `_core/di/configured_class_instantiator.php`, `_core/factory/configured_service_factory.php`, `_core/block/**/*.meta.php`, `_project/**/*.meta.php` | Низкий-средний | Разделить internal typed factory map и внешний plugin/config extension API; reflection оставить только для настоящих extension points. |
| 7 | Legacy template/meta runtime | PHP meta files, `.tpl`, parser state, compiled-template loader и file discovery остаются динамическим legacy-слоем; parser-to-router construction уже вынесен в DI boundary. | `_core/service/template/*`, `_core/view/parser/*`, `_core/block/**/*.meta.php`, `_project/**/*.meta.php` | Средний | После DI/bootstrap cleanup выделить typed contracts вокруг parser/compiled-template; старый runtime оставить compatibility adapter-ом. |
| 8 | Крупные legacy service/block классы | Часть классов уже получает зависимости, но все еще содержит много orchestration logic и локальных `new`; `tab` снят с прямого view-definer construction, parser/block runtime снят с прямого view-router construction, router/loader-state сняты с прямого view keeper construction, meta maker снят с прямого delayed meta construction, `entity` снят с прямого entity-description/entity-snippet construction, `entity/description` снят с прямого descriptor construction, `block/base` снят с прямого meta-maker construction, `transfer` снят с прямого transfer exception construction, base service / single service сняты с прямого service fatal exception construction, `log`/`matcher` сняты с прямого service fatal leaf construction, `config` снят с прямого service fatal construction, а `config/row` снят с прямого service fatal sub-object construction. | `_core/service/tab.php`, `_core/block/base.php`, `_core/bootstrap/loader.php`, `_core/service/request.php`, `_core/service/entity.php`, `_core/service/entity/description.php`, `_core/service/transfer.php`, `_core/view/parser*.php`, `_core/view/router.php`, `_core/view/router/loader_state.php`, `_core/base/meta/maker.php`, `_core/base/service.php`, `_core/base/service/single.php`, `_core/service/log.php`, `_core/service/matcher.php`, `_core/service/config.php`, `_core/service/config/row.php` | Средний | Брать только узкими behavioral срезами: выделять state/context/factory objects рядом с тестами, не делать массовый rewrite. |
| 9 | Procedural utility/functions layer | Глобальные helper functions остаются compatibility API, хотя больше не выглядят главным DI-долгом. | `_core/functions.php`, `cli/install.php`, `_core/service/database/adodb.php` | Низкий | Не трогать массово. Переносить в typed helpers/adapters только когда конкретная функция мешает тестируемости, autoload или изоляции состояния. |

## Рекомендуемый порядок

| Шаг | Работа | Почему сейчас | Минимальная проверка |
|---:|---|---|---|
| 1 | Зафиксировать guards для manual loading/globals/service-locator. | Большая часть DI-графа уже вынесена; теперь важно не дать старому подходу вернуться. | `LegacyDiSourceInventoryTest`, grep по forbidden patterns, `git diff --check`. |
| 2 | Истончать `bootstrap` facade. | Static facade остается самым заметным compatibility-хвостом. | Bootstrap/application/context tests, CLI smoke, bootstrap smoke, grep по `bootstrap::`. |
| 3 | Классифицировать оставшиеся exception throw-sites. | Database exception и config row factories закрыты; следующий construction-risk слой - exception families с runtime/context зависимостями. | Source inventory + focused tests по выбранной exception family. |
| 4 | Дробить context/bootstrap provider factories. | Это следующий плотный слой ручного wiring-а после DI provider cleanup. | Focused tests для выбранного provider factory, bootstrap smoke, full PHPUnit. |
| 5 | Поднять request/session context над native adapters. | Process-global state мешает изоляции тестов и будущему runtime reuse. | Request/session tests, grep superglobals, error diagnostics tests. |
| 6 | Отдельно планировать template/meta modernization. | Это поведенческий слой с высоким риском регрессий; его лучше не смешивать с DI cleanup. | Template/parser behavior tests, compiled-template smoke. |

## Что уже не считать главным долгом

| Участок | Почему |
|---|---|
| Старые service-locator gateway names | По текущему production-grep 0 совпадений. |
| Dynamic `new $className(...)` | По текущему production-grep 0 совпадений. |
| Concrete `new` внутри named provider factories | Это допустимый composition boundary, если leaf providers/services получают зависимости через constructor injection. |
| Native cURL/filesystem/header/session calls внутри adapters | Это intentional adapter layer; долг только если такие вызовы появляются вне adapters/entrypoints. |

## Команды среза

| Проверка | Результат |
|---|---|
| `find _core _project cli htdocs -type f -name '*.php'` | 740 PHP-файлов |
| `rg -n "\b(require\|require_once\|include\|include_once)\b" _core _project cli htdocs -g '*.php'` | 203 matches |
| `rg -n "containerService|getContainerService|blockService|resolveService|legacyService|container_registry::get|::instance\(|parse_ini_file\(|call_user_func|eval\(" ...` | 0 matches |
| `rg -n -P 'new\s+\$[A-Za-z_][A-Za-z0-9_]*\s*\(' ...` | 0 matches |
| `rg -n "bootstrap::" _core _project cli htdocs -g '*.php'` | 1 meta string / 0 executable refs |
| `rg -n '\$_(GET\|POST\|REQUEST\|COOKIE\|SERVER\|SESSION\|FILES)\|\$GLOBALS' ...` | 20 matches |
| `find _core _project cli htdocs -type f -name '*.meta.php'` | 54 meta files |
| `find _core _project cli htdocs -type f -name '*.tpl'` | 46 tpl files |
| Focused PHPUnit для transfer exception factory slice | `OK (279 tests, 114100 assertions)` |
| `rg -n -F 'new \fan\project\view\router' _core unit -g '*.php'` | Concrete view router construction appears only in `_core/factory/view_router_factory.php` plus test assertions. |
| Focused PHPUnit для view router factory slice | `OK (339 tests, 116819 assertions)` |
| `rg -n -F 'new \fan\project\view\keeper' _core unit -g '*.php'` | Concrete view keeper construction appears only in `_core/factory/view_keeper_factory.php`, `_core/factory/view_loader_json_keeper_factory.php`, `_core/factory/view_loader_text_keeper_factory.php` plus test assertions. |
| `rg -n -F 'new \fan\project\base\meta\delayed' _core unit -g '*.php'` | Concrete delayed meta construction appears only in `_core/factory/delayed_meta_factory.php` plus test doubles/assertions. |
| `rg -n -F 'new \fan\project\exception\service\database' _core unit -g '*.php'` | Concrete database exception construction appears only in `_core/di/database_exception_factory.php` plus test assertions. |
| `rg -n -F 'new \fan\core\service\config\row' _core -g '*.php'` | Concrete config row construction appears only in `_core/factory/config_row_factory.php`. |
| `rg -n -F 'new \fan\project\exception\block\local' _core -g '*.php'` | 0 matches. |
| Focused PHPUnit для view keeper factory slice | `OK (359 tests, 118287 assertions)` |
| Focused PHPUnit для delayed meta factory slice | `OK (385 tests, 120876 assertions)` |
| Focused PHPUnit для database exception factory slice | `OK (398 tests, 121721 assertions)` |
| Focused PHPUnit для config row factory slice | `OK (369 tests, 122149 assertions)` |
| Focused PHPUnit для block local exception slice | `OK (294 tests, 119815 assertions)` |
| Focused PHPUnit для meta maker block fatal exception slice | `OK (38 tests, 257 assertions)` |
| Legacy DI source inventory после meta maker block fatal exception slice | `OK (270 tests, 119637 assertions)` |
| Focused PHPUnit для view router block fatal exception slice | `OK (333 tests, 120695 assertions)` |
| Full PHPUnit после view router block fatal exception slice | `OK (3112 tests, 138584 assertions)` |
| `rg -n -F 'new \fan\project\exception\service\fatal' _core -g '*.php'` | 1 match: expected DI boundary `_core/factory/service_exception_factory.php`. |
| Focused PHPUnit для base service fatal exception slice | `OK (58 tests, 257 assertions)` |
| Source/inventory PHPUnit после base service fatal exception slice | `OK (340 tests, 123056 assertions)` |
| Full PHPUnit после base service fatal exception slice | `OK (3116 tests, 138783 assertions)` |
| Bootstrap smoke после base service fatal exception slice | `array ( )` |
| CLI smoke после base service fatal exception slice | `cli` |
| `git diff --check` после base service fatal exception slice | clean |
| Focused PHPUnit для log/matcher service fatal leaf slice | `OK (297 tests, 121394 assertions)` |
| Full PHPUnit после log/matcher service fatal leaf slice | `OK (3119 tests, 139538 assertions)` |
| Bootstrap smoke после log/matcher service fatal leaf slice | `array ( )` |
| CLI smoke после log/matcher service fatal leaf slice | `cli` |
| Focused PHPUnit для config service fatal slice | `OK (293 tests, 121401 assertions)` |
| Full PHPUnit после config service fatal slice | `OK (3121 tests, 139559 assertions)` |
| Bootstrap smoke после config service fatal slice | `array ( )` |
| CLI smoke после config service fatal slice | `cli` |
| `git diff --check` после config service fatal slice | clean |
| Focused PHPUnit для config row service fatal slice | `OK (351 tests, 123310 assertions)` |
| Full PHPUnit после config row service fatal slice | `OK (3122 tests, 139573 assertions)` |
| Bootstrap smoke после config row service fatal slice | `array ( )` |
| CLI smoke после config row service fatal slice | `cli` |
| Focused PHPUnit для matcher item service fatal slice | `OK (320 tests, 121478 assertions)` |
| Full PHPUnit после matcher item service fatal slice | `OK (3126 tests, 139598 assertions)` |
| Bootstrap smoke после matcher item service fatal slice | `array ( )` |
| CLI smoke после matcher item service fatal slice | `cli` |
| `git diff --check` после matcher item service fatal slice | clean |
| Focused PHPUnit для tab engine service fatal slice | `OK (307 tests, 121447 assertions)` |
| Full PHPUnit после tab engine service fatal slice | `OK (3128 tests, 139606 assertions)` |
| Bootstrap smoke после tab engine service fatal slice | `array ( )` |
| CLI smoke после tab engine service fatal slice | `cli` |
| `git diff --check` после tab engine service fatal slice | clean |
| Focused PHPUnit для infrastructure creator service fatal slice | `OK (276 tests, 121345 assertions)` |
| Full PHPUnit после infrastructure creator service fatal slice | `OK (3129 tests, 139617 assertions)` |
| Bootstrap smoke после infrastructure creator service fatal slice | `array ( )` |
| CLI smoke после infrastructure creator service fatal slice | `cli` |
| `git diff --check` после infrastructure creator service fatal slice | clean |
