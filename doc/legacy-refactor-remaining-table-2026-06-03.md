# Отчет: что осталось от старого подхода и что рефакторить дальше

Дата анализа: 2026-06-03

Область анализа: `core`, `project`, `htdocs`, `cli`; без `vendor` и `legacy_assets`.

Важно: отчет построен по текущей dirty рабочей копии. В проекте уже есть большой незакоммиченный слой PHP 8/DI/adapters модернизации, поэтому это срез текущего workspace, а не чистого git baseline.

## Короткий вывод

Проект уже заметно ушел от старого service-locator/procedural подхода: по production-срезу не найдены старые gateway/dispatcher маркеры (`containerService`, `getContainerService`, `blockService`, `resolveService`, `legacyService`, `container_registry::get()`, `::instance()`, `parse_ini_file()`, `call_user_func*`, `eval()`), а прямой dynamic construction вида `new $className(...)` отсутствует.

Главный оставшийся долг теперь не в массовом service locator, а в явных runtime/composition границах: static bootstrap facade, ручной loading, process-global request/session окружение, config/meta-driven construction, template/meta runtime и policy по оставшимся exception throw-sites. Transfer exception construction уже вынесен из `core/service/transfer.php` в `core/factory/transfer_exception_factory.php`, view router construction вынесен из parser/block runtime в `core/factory/view_router_factory.php`, view keeper / loader keeper construction вынесен в `core/factory/view_keeper_factory.php`, `core/factory/view_loader_json_keeper_factory.php` и `core/factory/view_loader_text_keeper_factory.php`, delayed meta construction вынесен в `core/factory/delayed_meta_factory.php`, database exception construction вынесен из `core/service/database.php` в `core/di/database_exception_factory.php`, config row construction вынесен в `core/factory/config_row_factory.php`, direct block local exception construction в `core/block/base.php` заменен на injected `blockExceptionFactory`, meta maker block fatal construction в `core/base/meta/maker.php::_setSource()` идет через injected block exception factory, view router block fatal construction в `core/view/router.php` получает throwable через тот же injected block exception factory, base service / single service fatal construction вынесен в `core/factory/service_exception_factory.php` и прокинут через bootstrap runtime service defaults, config service fatal construction в `core/service/config.php` теперь идет через inherited `createServiceFatalException()`, а matcher item/base service fatal construction идет через injected factory from `matcher_item_factory` / `matcher\stack`. При этом в leaf/service/model слоях все еще много alias-based `new fatalException(...)`; это отдельный legacy error-policy долг, который надо классифицировать перед механическим DI-переносом.

## Статический срез

| Метрика | Значение | Вывод |
|---|---:|---|
| Production PHP-файлов | 740 | Размер текущего production-среза после DI/defaults/provider factory модернизации и добавления database/config/service exception factory boundaries. |
| Legacy service-locator / dispatcher markers | 0 | Старые gateway-вызовы по broad grep не найдены. |
| Dynamic `new $className(...)` | 0 | Прямой variable-class construction в production-срезе не найден. |
| Alias-based `new fatalException(...)` | 140 | Старый service fatal style еще широко используется в service/model/block/plain слоях; не все эти throw-sites требуют DI, но их надо классифицировать и закрывать source guard-ами по семействам. |
| `fatalException` service alias imports | 34 | Alias скрывает реальный exception class от узких source-grep проверок; для новых срезов лучше искать и fully-qualified, и alias-based construction. |
| `bootstrap::...` refs | 1 meta string / 0 executable refs | Runtime почти снят с прямых static-вызовов; осталась строка-алиас в `project/app/__tools/main/upgrade_blocks.meta.php`. |
| `include` / `require` matches | 203 | Ручная загрузка еще живет в bootstrap/defaults/loaders/installer/template boundaries; рост связан с явными DI factory requires в runtime state composition boundary. |
| Raw superglobals / `$GLOBALS` matches | 20 | Основной доступ локализован в native request/session adapters и request/error diagnostics. |
| Top-level procedural functions | 13 | Остались helpers в `core/functions.php`, installer helper, локальный `conv()` в request service и ADOdb error handler. |
| Template/meta runtime | 54 `*.meta.php`, 46 `*.tpl` | Legacy template/meta подсистема остается отдельным динамическим слоем. |

## Остаточный долг

| Приоритет | Зона | Что осталось от старого подхода | Где смотреть | Риск | Что рефакторить дальше |
|---:|---|---|---|---|---|
| 1 | Прямое runtime construction вне DI boundary | Stateful/service-layer construction с project/core factory objects закрыт для текущей очереди: transfer exception, view router, view keeper, delayed meta, database exception, config row, block local exception, meta maker block fatal, view router block fatal, base service fatal, log/matcher service fatal, config service fatal, config row service fatal, matcher item service fatal, tab engine service fatal и infrastructure creator service fatal construction локализованы в named/injected/facade-passed factories. Fully-qualified service fatal direct construction теперь есть только в `service_exception_factory`. | `core/factory/service_exception_factory.php` | Низкий | Держать source guard: direct `new \fan\project\exception\service\fatal` разрешен только в named factory. Следующим срезом брать alias-based `tab.php`. |
| 2 | Alias-based service fatal policy | 140 `new fatalException(...)` остаются в service/model/block/plain слоях. Часть является локальными domain/runtime guards, часть должна идти через уже появившийся `service_exception_factory`. | `core/service/tab.php`, `core/service/cache*.php`, `core/service/entity*.php`, `core/base/model/*.php`, `core/block/base.php`, `core/plain/image.php` | Средний | Сначала классифицировать по семействам: service facade throw-sites переводить на `createServiceFatalException()`, facade-owned sub-objects получать factory через facade/factory, domain-only exceptions оставить локальными и закрепить policy. |
| 3 | Static bootstrap lifecycle | `bootstrap` остается compatibility facade с process-level `static $application`; body уже тоньше, но ownership lifecycle еще не полностью явный. | `core/bootstrap.php`, `core/bootstrap/application.php`, `core/bootstrap/context.php`, `core/bootstrap/context_bootstrap_operations.php` | Средний | Оставить `bootstrap` как BC-shell, но новые/переписываемые consumers переводить на explicit `application` / `context` / runner objects. |
| 4 | Manual loading layer | В production остается 203 `include` / `require` совпадений. Часть легитимна для composition roots, но allowlist широкий и может скрывать возврат loading-а в leaf classes. | `core/bootstrap/*defaults_provider_factory.php`, `core/bootstrap/application.php`, `core/bootstrap/loader.php`, `core/di/*provider_factory.php`, `htdocs/install/incl/*` | Средний | Сужать allowlist по одному boundary-семейству: переносить hidden loads вверх в explicit defaults/provider factories и закреплять source guard-ами. |
| 5 | Bootstrap/context composition boundaries | Context/bootstrap factories уже стали явными composition roots, но все еще держат много ручного wiring-а и `new` в provider factories. | `core/factory/bootstrap/context_defaults_provider_factory.php`, `core/factory/bootstrap/context_core_defaults_provider_factory.php`, `core/factory/bootstrap/context_error_handling_defaults_provider_factory.php`, `core/factory/bootstrap/context_support_defaults_provider_factory.php`, `core/factory/bootstrap/bootstrap_runtime_defaults_provider_factory.php` | Средний | Дробить только те subgraphs, где это реально уменьшает связность; не плодить wrapper-и без выигрыша в тестируемости. |
| 6 | Process-global request/session окружение | Raw globals в основном завернуты в adapters, но runtime state остается mutable process-global boundary. | `core/adapter/request_input_native_environment.php`, `core/adapter/request_input_globals.php`, `core/adapter/adodb_session_native_environment.php`, `core/adapter/adodb_session_globals.php`, `core/service/request.php`, `core/service/error.php` | Средний | Выше adapter-слоя передавать request/session context objects; запретить новый прямой access к superglobals вне adapters/diagnostics. |
| 7 | Config/meta-driven construction | Прямого `new $className(...)` уже нет, но class-name based config, `class_exists($className)` и reflection/configured construction остаются extension boundary. | `core/di/configured_class_instantiator.php`, `core/factory/configured_service_factory.php`, `core/di/*_factory.php`, `core/block/**/*.meta.php`, `project/**/*.meta.php` | Низкий-средний | Разделить internal typed factory map и внешний plugin/config extension API; reflection оставить только для настоящих extension points. |
| 8 | Legacy template/meta runtime | PHP meta files, `.tpl`, parser state, compiled-template loader и file discovery остаются динамическим legacy-слоем. | `core/service/template/*`, `core/view/parser/*`, `core/block/**/*.meta.php`, `project/**/*.meta.php`, `core/adapter/compiled_template_loader.php` | Средний | После DI/bootstrap cleanup выделить typed contracts вокруг parser/compiled-template; старый runtime оставить compatibility adapter-ом. |
| 9 | Крупные legacy service/block классы | Часть классов уже получает зависимости, но все еще содержит много orchestration logic и локального состояния. | `core/service/tab.php`, `core/block/base.php`, `core/bootstrap/loader.php`, `core/service/request.php`, `core/service/entity.php`, `core/service/database.php` | Средний | Брать только узкими behavioral срезами: выделять state/context/factory objects рядом с тестами, не делать массовый rewrite. |
| 10 | Procedural utility/functions layer | Глобальные helper functions остаются compatibility API, хотя больше не выглядят главным DI-долгом. | `core/functions.php`, `cli/install.php`, `core/service/request.php`, `core/service/database/adodb.php` | Низкий | Не трогать массово. Переносить в typed helpers/adapters только когда конкретная функция мешает тестируемости, autoload или изоляции состояния. |

## Ближайшие DI-срезы

| Очередь | Файл / участок | Текущий old-style фрагмент | Рекомендуемый рефакторинг | Минимальная проверка |
|---:|---|---|---|---|
| Закрыто | `core/view/parser*.php` + `core/block/base.php` | Parser/block runtime больше не создает `\fan\project\view\router\simple/html/json/loader` напрямую. | Concrete router construction локализован в `core/factory/view_router_factory.php`; `tab` прокидывает factory в block dependencies. | `ViewRouterFactoryTest`, parser tests, `BaseTest`, `TabTest`, source guard на `new \fan\project\view\router`. |
| Закрыто | `core/view/router.php` и `core/view/router/loader_state.php` | Router больше не создает default `view\keeper`, loader state больше не создает JSON/text keepers fallback-ом. | Keeper construction локализован в `core/factory/view_keeper_factory.php`, `core/factory/view_loader_json_keeper_factory.php`, `core/factory/view_loader_text_keeper_factory.php`; loader state собирается через `core/factory/view_loader_state_factory.php`. | `ViewKeeperFactoriesTest`, `LoaderStateTest`, `Router`/view tests, source guard на `new \fan\project\view\keeper`. |
| Закрыто | `core/base/meta/maker.php` | `_makeActiveMeta()` больше не создает `\fan\project\base\meta\delayed` напрямую. | Delayed meta construction локализован в `core/factory/delayed_meta_factory.php`; `meta_maker_factory` передает callable в maker. | `DelayedMetaFactoryTest`, `MakerTest`, `MetaMakerFactoryTest`, source guard на delayed construction. |
| Закрыто | `core/service/database.php` | `fixError()` больше не создает `\fan\project\exception\service\database` напрямую. | Database exception construction локализован в `core/di/database_exception_factory.php`; `database_service_factory` прокидывает callable в сервис. | `DatabaseExceptionFactoryTest`, `DatabaseServiceFactoryTest`, `DatabaseTest`, database engine tests, source guard на database exception construction. |
| Закрыто | `core/service/config.php` / `core/di/application_infrastructure_service_creator.php` | Config service и infrastructure creator больше не создают config row напрямую. | Config row construction локализован в `core/factory/config_row_factory.php`; creator получает config row factory-factory через constructor injection. | `ConfigRowFactoryTest`, `ConfigTest`, `RowTest`, `ConfigServiceFactoryTest`, `ApplicationInfrastructureServiceCreatorTest`, source guard на config row construction. |
| Закрыто | `core/block/base.php::getEmbeddedBlock()` | Missing embedded block больше не создает `\fan\project\exception\block\local` напрямую. | Local block exception идет через `_makeBlockException()` и injected `blockExceptionFactory`. | `BaseTest`, `BlockExceptionFactoryTest`, `LegacyDiSourceInventoryTest`, source guard на direct block local construction. |
| Закрыто | `core/base/meta/maker.php::_setSource()` | Unknown meta source type больше не создает `\fan\project\exception\block\fatal` напрямую. | `blockExceptionFactory` прокинут из block dependencies через `meta_maker_factory` в maker; `_setSource()` получает throwable через injected factory. | `MakerTest`, `BaseTest`, `MetaMakerFactoryTest`, `LegacyDiSourceInventoryTest`, source grep на direct meta-maker block fatal construction. |
| Закрыто | `core/view/router.php` | Router validation и unknown keeper больше не создают `\fan\project\exception\block\fatal` напрямую. | `blockExceptionFactory` прокинут из block dependencies в `view_router_factory`, затем в router/loader constructors; router передает настоящий block в exception factory. | `RouterTest`, `ViewRouterFactoryTest`, `BaseTest`, `LegacyDiSourceInventoryTest`, source grep на direct block fatal construction. |
| Закрыто | `core/base/service.php` / `core/base/service/single.php` | Base service и duplicate singleton guard больше не создают `\fan\project\exception\service\fatal` напрямую. | Service fatal construction для base/single локализован в `core/factory/service_exception_factory.php`; factory прокинут через `core/service/bootstrap_runtime.php` и `core/factory/bootstrap/bootstrap_runtime_service_defaults_provider_factory.php`. | `ServiceExceptionFactoryTest`, `ServiceTest`, `SingleTest`, `BootstrapRuntimeTest`, bootstrap/CLI smoke, source grep на оставшиеся concrete service fatal throw-sites. |
| Закрыто | `core/service/log.php` / `core/service/matcher.php` | Invalid log parser и missing matcher item больше не создают `\fan\project\exception\service\fatal` напрямую. | Оба сервиса используют inherited `createServiceFatalException()` и получают throwable из injected service exception factory через base service dependencies/runtime. | `LogTest`, `MatcherTest`, `LegacyDiSourceInventoryTest`, source grep на оставшиеся direct service fatal throw-sites. |
| Закрыто | `core/service/config.php` | Config service больше не импортирует alias `fatalException` и не создает `\fan\project\exception\service\fatal` напрямую для missing data row / unknown engine. | Config service использует inherited `createServiceFatalException()`; direct construction защищен source assertions в `ConfigTest` и allowlist в `LegacyDiSourceInventoryTest`. | `ConfigTest`, `ConfigServiceFactoryTest`, `LegacyDiSourceInventoryTest`, source grep на `fatalException` alias и direct service fatal construction. |
| Закрыто | `core/service/config/row.php` | Dynamic property unset больше не создает `\fan\project\exception\service\fatal` напрямую через facade. | `config_row_factory` получает injected service exception factory из bootstrap runtime через `application_infrastructure_service_creator`; root/sub rows наследуют callable и `__unset()` создает throwable через factory. | `ConfigRowFactoryTest`, `RowTest`, `ApplicationInfrastructureServiceCreatorTest`, `ApplicationServiceCreatorDefaultsProviderFactoryTest`, `FunctionsServiceContainerTest`, `LegacyDiSourceInventoryTest`. |
| Закрыто | `core/service/matcher/item/base.php` / `core/service/matcher/item.php` | Matcher item hierarchy больше не создает `\fan\project\exception\service\fatal` напрямую и больше не импортирует alias `fatalException`. | `matcher` берет service exception factory из bootstrap runtime, передает через `matcher\stack` в `matcher_item_factory`; item/base создают throwable через injected factory и сохраняют plain fatal fallback для no-facade режима. | `MatcherItemFactoryTest`, `StackTest`, `MatcherServiceFactoryTest`, `ItemTest`, `BaseTest`, `LegacyDiSourceInventoryTest`, source grep на matcher direct construction. |
| Закрыто | `core/service/tab/engine.php` | Base tab engine больше не создает `\fan\project\exception\service\fatal` напрямую через facade. | `tab` exposes facade-passed `createServiceFatalExceptionForSubObject()` wrapper over the injected base service exception factory; engine calls that boundary and no longer owns concrete construction. | `EngineTest`, `TabTest`, `SubscriberTest`, `LegacyDiSourceInventoryTest`, source grep. |
| Закрыто | `core/di/application_infrastructure_service_creator.php` | Missing default cache type больше не создает `\fan\project\exception\service\fatal` напрямую в creator boundary. | Creator получает `serviceExceptionFactory()` из `bootstrap_runtime`, валидирует `Throwable` и бросает factory-created exception. | `ApplicationInfrastructureServiceCreatorTest`, `LegacyDiSourceInventoryTest`, source grep. |
| 1 | `core/service/tab.php` | 10 `new fatalException($this, ...)` остаются в самом tab service. | Для самого сервиса заменить на inherited `createServiceFatalException()`, отдельно от tab engine/delegate sub-objects. | `TabTest`, `TabStateTest`, `TabServiceFactoryTest`, source grep на `fatalException` alias. |
| 2 | Остальные alias-based `fatalException` семейства | Многие `throw new fatalException(...)` живут в leaf classes. Не все это DI-долг, но часть мешает тестовой замене/изоляции. | После service/matcher/tab срезов решить policy по семействам: cache, entity/model, block/template, request/header/plain. | Source inventory + focused tests по каждой exception family. |

## Рекомендуемый порядок работ

| Шаг | Работа | Почему сейчас | Проверка |
|---:|---|---|---|
| 1 | Классифицировать оставшиеся exception throw-sites, включая alias `fatalException`. | Stateful construction с factory objects закрыт; следующий риск - решить, какие exceptions являются доменными локальными, а какие требуют DI из-за runtime/facade context. | Source inventory по `new \fan\project\exception\service\fatal`, `new fatalException`, alias imports + focused tests по выбранной exception family. |
| 2 | Сужать manual-loading и globals guards. | После DI-срезов важно не дать старому подходу вернуться в leaf classes. | `LegacyDiSourceInventoryTest`, grep forbidden patterns. |
| 3 | Планировать template/meta modernization отдельно. | Это более рискованный behavioral слой; его лучше не смешивать с текущим DI cleanup. | Template/parser behavior tests, compiled-template smoke. |

## Что уже не считать главным долгом

| Участок | Почему |
|---|---|
| Старые service-locator gateway names | По текущему production-grep 0 совпадений. |
| Dynamic `new $className(...)` | По текущему production-grep 0 совпадений. |
| Concrete `new` внутри named provider factories | Это допустимый composition boundary, если leaf providers/services получают зависимости через constructor injection. |
| Native filesystem/header/session/request calls внутри adapters | Это intentional adapter layer; долг только если такие вызовы появляются вне adapters/entrypoints. |
| Уже вынесенные factories (`view_definer_factory`, `view_router_factory`, `view_keeper_factory`, `view_loader_json_keeper_factory`, `view_loader_text_keeper_factory`, `view_loader_state_factory`, `delayed_meta_factory`, `entity_description_factory`, `entity_snippet_factory`, `entity_descriptor_factory`, `meta_maker_factory`, `transfer_exception_factory`, `database_exception_factory`, `config_row_factory`, `service_exception_factory`) | Они являются текущими DI-boundaries; их надо защищать source guard-ами, а не раскатывать обратно в service/block classes. |

## Команды среза

| Проверка | Результат |
|---|---|
| `find core project cli htdocs -type f -name '*.php'` | 740 PHP-файлов |
| `rg -n -e 'containerService' -e 'getContainerService' -e 'blockService' -e 'resolveService' -e 'legacyService' -e 'container_registry::get' -e '::instance\(' -e 'parse_ini_file\(' -e 'call_user_func' -e 'eval\(' core project cli htdocs -g '*.php'` | 0 matches |
| `rg -n -P 'new\s+\$[A-Za-z_][A-Za-z0-9_]*\s*\(' core project cli htdocs -g '*.php'` | 0 matches |
| `rg -n '\b(require\|require_once\|include\|include_once)\b' core project cli htdocs -g '*.php'` | 203 matches |
| `rg -n '\$_(GET\|POST\|REQUEST\|COOKIE\|SERVER\|SESSION\|FILES)\|\$GLOBALS' core project cli htdocs -g '*.php'` | 20 matches |
| `rg -n 'bootstrap::' core project cli htdocs -g '*.php'` | 1 meta string / 0 executable refs |
| `find core project cli htdocs -type f -name '*.meta.php'` | 54 meta files |
| `find core project cli htdocs -type f -name '*.tpl'` | 46 tpl files |
| `rg -n -F 'new \fan\project\base\transfer' core unit -g '*.php'` | Concrete transfer construction appears only in `core/factory/transfer_exception_factory.php` plus test assertions. |
| `rg -n -F 'new \fan\project\view\router' core unit -g '*.php'` | Concrete view router construction appears only in `core/factory/view_router_factory.php` plus test assertions. |
| `rg -n -F 'new \fan\project\view\keeper' core unit -g '*.php'` | Concrete view keeper construction appears only in `core/factory/view_keeper_factory.php`, `core/factory/view_loader_json_keeper_factory.php`, `core/factory/view_loader_text_keeper_factory.php` plus test assertions. |
| `rg -n -F 'new \fan\project\base\meta\delayed' core unit -g '*.php'` | Concrete delayed meta construction appears only in `core/factory/delayed_meta_factory.php` plus test doubles/assertions. |
| `rg -n -F 'new \fan\project\exception\service\database' core unit -g '*.php'` | Concrete database exception construction appears only in `core/di/database_exception_factory.php` plus test assertions. |
| `rg -n -F 'new \fan\core\service\config\row' core -g '*.php'` | Concrete config row construction appears only in `core/factory/config_row_factory.php`. |
| `rg -n -F 'new \fan\project\service\config\row' core -g '*.php'` | 0 matches. |
| `rg -n -F 'new \fan\project\exception\block\local' core -g '*.php'` | 0 matches. |
| `rg -n -F 'new \fan\project\exception\block\fatal' core -g '*.php'` | 0 production matches. |
| `rg -n -F 'new \fan\project\exception\service\fatal' core -g '*.php'` | 1 match: expected DI boundary `core/factory/service_exception_factory.php`. |
| `rg -n -F 'new fatalException' core project cli htdocs -g '*.php'` | 140 matches across service/model/block/plain layers. |
| `rg -n -F 'use fan\project\exception\service\fatal as fatalException' core project cli htdocs -g '*.php'` | 34 alias imports. |
| `rg -n '$this->_getEngine\(|_getEngine\(' core project cli htdocs -g '*.php'` | 17 matches; config-driven engine lookup still exists in services plus base service. |
| Focused PHPUnit для transfer exception factory slice | `OK (279 tests, 114100 assertions)` |
| Focused PHPUnit для view router factory slice | `OK (339 tests, 116819 assertions)` |
| Focused PHPUnit для view keeper factory slice | `OK (359 tests, 118287 assertions)` |
| Focused PHPUnit для delayed meta factory slice | `OK (385 tests, 120876 assertions)` |
| Focused PHPUnit для database exception factory slice | `OK (398 tests, 121721 assertions)` |
| Focused PHPUnit для config row factory slice | `OK (369 tests, 122149 assertions)` |
| Focused PHPUnit для block local exception slice | `OK (294 tests, 119815 assertions)` |
| Focused PHPUnit для meta maker block fatal exception slice | `OK (38 tests, 257 assertions)` |
| Legacy DI source inventory после meta maker block fatal exception slice | `OK (270 tests, 119637 assertions)` |
| Focused PHPUnit для view router block fatal exception slice | `OK (333 tests, 120695 assertions)` |
| Full PHPUnit после view router block fatal exception slice | `OK (3112 tests, 138584 assertions)` |
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
| Source grep после matcher item service fatal slice | 3 fully-qualified service fatal matches; 0 matcher `new fatalException` matches; 0 matcher `fatalException` alias imports |
| Focused PHPUnit для tab engine service fatal slice | `OK (307 tests, 121447 assertions)` |
| Source grep после tab engine service fatal slice | 2 fully-qualified service fatal matches; 10 tab alias-based `new fatalException` matches remain |
| Full PHPUnit после tab engine service fatal slice | `OK (3128 tests, 139606 assertions)` |
| Bootstrap smoke после tab engine service fatal slice | `array ( )` |
| CLI smoke после tab engine service fatal slice | `cli` |
| `git diff --check` после tab engine service fatal slice | clean |
| Focused PHPUnit для infrastructure creator service fatal slice | `OK (276 tests, 121345 assertions)` |
| Source grep после infrastructure creator service fatal slice | 1 fully-qualified service fatal match in `core/factory/service_exception_factory.php` |
| Full PHPUnit после infrastructure creator service fatal slice | `OK (3129 tests, 139617 assertions)` |
| Bootstrap smoke после infrastructure creator service fatal slice | `array ( )` |
| CLI smoke после infrastructure creator service fatal slice | `cli` |
| `git diff --check` после infrastructure creator service fatal slice | clean |

Примечание: transfer exception factory slice проверен lint/focused PHPUnit/full PHPUnit/bootstrap smoke/CLI smoke/`git diff --check`.
Примечание: view router factory slice проверен lint/focused PHPUnit/full PHPUnit/bootstrap smoke/CLI smoke/`git diff --check`.
Примечание: view keeper factory slice проверен lint/focused PHPUnit/full PHPUnit/bootstrap smoke/CLI smoke/`git diff --check`.
Примечание: delayed meta factory slice проверен lint/focused PHPUnit/full PHPUnit/bootstrap smoke/CLI smoke/source grep/`git diff --check`.
Примечание: database exception factory slice проверен lint/focused PHPUnit/full PHPUnit/bootstrap smoke/CLI smoke/source grep/`git diff --check`.
Примечание: config row factory slice проверен lint/focused PHPUnit/full PHPUnit/bootstrap smoke/CLI smoke/source grep/`git diff --check`.
Примечание: block local exception slice проверен lint/focused PHPUnit/full PHPUnit/bootstrap smoke/CLI smoke/source grep/`git diff --check`.
Примечание: meta maker block fatal exception slice проверен lint/focused PHPUnit/full PHPUnit/bootstrap smoke/CLI smoke/source grep/`git diff --check`.
Примечание: view router block fatal exception slice проверен lint/focused PHPUnit/full PHPUnit/bootstrap smoke/CLI smoke/source grep/`git diff --check`.
Примечание: base service fatal exception slice проверен lint/focused PHPUnit/source inventory/full PHPUnit/bootstrap smoke/CLI smoke/source grep/`git diff --check`; broad guard на `new \fan\project\exception\service\fatal` пока не вводился, потому что concrete service throw-sites еще остаются.
Примечание: log/matcher service fatal leaf slice проверен lint/focused PHPUnit/full PHPUnit/bootstrap smoke/CLI smoke/source grep; source guard добавлен с allowlist для оставшихся direct service fatal throw-sites.
Примечание: config service fatal slice проверен lint/focused PHPUnit/full PHPUnit/bootstrap smoke/CLI smoke/source grep/`git diff --check`; alias `fatalException` удален из `core/service/config.php`.
Примечание: config row service fatal slice проверен lint/focused PHPUnit/full PHPUnit/bootstrap smoke/CLI smoke/source grep; service exception factory прокинут через `config_row_factory`.
Примечание: matcher item service fatal slice проверен lint/focused PHPUnit/full PHPUnit/bootstrap smoke/CLI smoke/source grep/`git diff --check`; service exception factory прокинут через `matcher\stack` и `matcher_item_factory`.
Примечание: tab engine service fatal slice проверен lint/focused PHPUnit/full PHPUnit/bootstrap smoke/CLI smoke/source grep/`git diff --check`; engine получает throwable через facade-passed `createServiceFatalExceptionForSubObject()`.
Примечание: infrastructure creator service fatal slice проверен lint/focused PHPUnit/full PHPUnit/bootstrap smoke/CLI smoke/source grep/`git diff --check`; missing default cache type идет через `bootstrap_runtime->serviceExceptionFactory()`.
