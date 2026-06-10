# Отчет: что осталось от старого подхода

Дата анализа: 2026-06-03

Область анализа: `core`, `project`, `cli`, `htdocs`; без `vendor` и `legacy_assets`.

Важно: это срез текущей dirty рабочей копии. В проекте уже есть большой незакоммиченный слой PHP 8 / DI / adapter-модернизации, поэтому отчет описывает текущее состояние workspace, а не чистый git baseline.

## Короткий вывод

Старый service-locator / procedural gateway слой почти снят: по runtime-срезу не найдены broad gateway markers, `::instance()`, `parse_ini_file()`, `call_user_func*`, `eval()` и прямой `new $className(...)`. INI-конфиги в production-срезе тоже не найдены.

Оставшийся долг теперь не в одном большом узле, а в нескольких boundary-слоях:

- direct global helper calls;
- ручные `include` / `require`;
- bootstrap/context composition graph;
- config/meta/reflection runtime;
- process-global request/session boundary;
- legacy template/meta runtime.

## Статический срез

| Метрика | Текущее значение | Что означает |
|---|---:|---|
| Production PHP-файлов | 750 | Размер анализируемого runtime-среза. |
| Broad legacy gateway patterns | 0 | `containerService`, `getContainerService`, `blockService`, `resolveService`, `legacyService`, `container_registry::get`, `::instance()`, `parse_ini_file()`, `call_user_func*`, `eval()` не найдены. |
| Dynamic `new $className(...)` | 0 | Прямое variable-class construction не найдено. |
| `*.ini` в production-срезе | 0 | INI migration в текущем runtime-срезе закрыта. |
| Direct helper call lines / calls | 73 строки / 74 вызова | Общий grep по compatibility helpers, включая DI-boundary fallback closures. |
| Direct helper lines вне `core/functions.php` и `core/di/*` | 1 строка | Runtime direct global helper calls в текущем grep-срезе очищены; оставшаяся строка в `core/service/timer.php` является method-name false-positive, а не global helper call. |
| `include` / `require` grep matches | 205 | Ручная загрузка еще живет в bootstrap/defaults/loader/installer/template boundaries. |
| Raw superglobals / `$GLOBALS` grep matches | 20 | Исполняемая часть в основном находится в native request/session adapters и diagnostics; часть совпадений - comments. |
| Reflection / class resolution grep matches | 303 | Config/meta-driven class resolution остается важным runtime механизмом. |
| `*.meta.php` | 54 | Legacy meta-runtime остается отдельным динамическим слоем. |
| `*.tpl` | 46 | Template runtime еще требует отдельного compatibility refactor. |
| Top-level function definitions | 11 | В основном `core/functions.php` плюс ADOdb callback. |
| Direct exception construction | 8 | Все совпадения находятся в named DI exception factories. |

## Helper-хвост

| Helper | Вызовов | Где сконцентрировано | Что делать |
|---|---:|---|---|
| `array_val` | 13 | helper definition и DI fallback boundaries | Runtime-срез вне `core/functions.php` и `core/di/*` очищен; держать guards и не добавлять новые direct calls. |
| `adduceToArray` | 17 | helper definition и DI fallback boundaries | Runtime-срез вне `core/functions.php` и `core/di/*` очищен; держать guards и не добавлять новые direct calls. |
| `get_class_name` | 7 | DI-boundary short-name resolver fallback и timer method-name false-positive | Runtime config/config-row/template/root-html/block-base очищены; общий счетчик держится из-за DI fallback для `short_class_name_resolver`. |
| `get_class_alt` | 13 | Ошибки и DI-boundary class-name resolver fallback-и | Entity-description, entity-designer, plain fatal, template fatal и base data уже используют injected/native resolvers; следующий runtime-хвост - model/base namespace conventions. |
| `array_merge_recursive_alt` | 9 | helper definition и DI fallback boundaries | Runtime-срез вне `core/functions.php` и `core/di/*` очищен; держать guards и не добавлять новые direct calls. |
| `is_array_alt` | 9 | `functions`, DI-boundary `array_like_checker` fallback-и | Runtime entity delegate, base-model-row и root-html checks очищены; прямых runtime callers вне functions/DI больше не видно. |
| `get_ns_name` | 3 | helper definition и DI-boundary `namespace_resolver` fallback-и | Model entity и spec-file row очищены; runtime callers вне functions/DI больше не видны. |
| `explode_alt` | 1 | helper definition | Log viewer date split и tools upgrade argument split уже переведены на native `explode()`; runtime callers больше не видны. |
| `increaseNum` / `decreaseNum` | 2 | Только helper definitions | Удаление возможно только после отдельной BC-проверки. |

## Таблица оставшегося долга

| Приоритет | Участок | Что осталось от старого подхода | Примеры файлов | Риск | Что рефакторить дальше |
|---:|---|---|---|---|---|
| 1 | Runtime helper calls | Прямых global helper calls вне `core/functions.php` и `core/di/*` больше не видно; grep-хвост держится на `core/service/timer.php`, где `get_class_name()` является методом row object. | `core/service/timer.php` false-positive | Низкий | Держать source guards; следующий долг искать уже не в direct helper callers, а в manual loading / bootstrap / dynamic runtime слоях. |
| 2 | Manual loading layer | 205 `include` / `require` grep matches. Большая часть допустима в composition roots/adapters, но allowlist широкий. | `core/bootstrap/*`, `core/di/*provider_factory.php`, `core/bootstrap/loader.php`, `core/adapter/compiled_template_loader.php`, `htdocs/install/incl/*` | Средний | Сужать allowlist по одному boundary-семейству. Hidden loads поднимать в explicit providers/adapters и закреплять в `LegacyDiSourceInventoryTest`. |
| 3 | Bootstrap/context graph | DI уже есть, но composition root большой и ручной: defaults providers, registry/factory-provider/creator chains, concrete factory construction. | `core/bootstrap.php`, `core/bootstrap/application.php`, `core/bootstrap/context*.php`, `core/bootstrap/*defaults_provider_factory.php` | Средний | Оставить static bootstrap как BC-shell, а новые consumers вести через explicit `application` / `context` / runner objects. |
| 4 | Config/meta/reflection runtime | Прямого `new $className` нет, но `class_exists`, reflection и configured instantiation остаются центральным extension mechanism. | `core/di/configured_class_instantiator.php`, `core/factory/configured_service_factory.php`, `core/service/entity.php`, `core/service/tab.php`, `core/base/model/entity.php` | Средний | Разделить internal typed factory maps и внешний extension API. Reflection оставить только в настоящих extension points. |
| 5 | Process-global request/session boundary | Raw globals локализованы лучше, но request/session state все еще завязан на process globals. | `core/adapter/request_input_native_environment.php`, `core/adapter/request_input_globals.php`, `core/adapter/adodb_session_native_environment.php`, `core/adapter/adodb_session_globals.php`, `core/service/error.php` | Средний | Выше adapter-слоя передавать request/session context objects; запретить новые superglobals вне native adapters/diagnostics. |
| 6 | Legacy template/meta runtime | 54 meta-файла и 46 template-файлов остаются динамической подсистемой. | `core/service/template/*`, `core/view/parser/*`, `core/block/**/*.meta.php`, `project/**/*.meta.php` | Средний | Планировать отдельным срезом после helper/bootstrap cleanup: typed contracts вокруг parser/compiled-template, старый runtime оставить compatibility adapter-ом. |
| 7 | Procedural utility/functions layer | `core/functions.php` все еще содержит compatibility helpers. | `core/functions.php`, `core/service/database/adodb.php`, `cli/install.php` | Низкий-средний | Не удалять массово. Сначала убрать runtime callers, потом оставить файл как тонкую BC-зону или разбить на injectable utility services. |
| 8 | Direct exception construction | Прямые exception `new` остались только в named DI exception factories. | `core/factory/fatal_exception_factory.php`, `core/factory/service_exception_factory.php`, `core/factory/core_fatal_exception_factory.php` | Низкий | Держать source guards; это не главный долг сейчас. |
| 9 | Dead comments/string refs | Остались comments/string refs, которые шумят в grep-инвентаризации. | `core/service/request.php`, `core/base/model/file_data/row.php`, plain/controllers comments по старым alias imports | Низкий | Убрать отдельным cleanup-срезом, если они не нужны как миграционная памятка. |

## Рекомендуемая очередь

| Шаг | Зона | Почему сейчас | Минимальная проверка |
|---:|---|---|---|
| 1 | Helper guard maintenance | Runtime direct helper callers очищены; теперь главный риск - регрессия через новые direct calls. | Focused tests + grep guard по конкретному файлу. |
| 2 | Model project helper calls | Остались namespace helpers вне service layer. | Focused model/project tests + source guard. |
| 3 | Manual loading allowlist | После helper cleanup это следующий большой источник старого подхода. | `LegacyDiSourceInventoryTest`; grep loading statements; `git diff --check`. |
| 4 | Template/meta runtime plan | Рискованный динамический слой; лучше не смешивать с DI-helper cleanup. | Template/parser behavior tests + compiled-template smoke. |

## Что уже не главный долг

| Участок | Почему |
|---|---|
| Старые service-locator gateway names | По текущему production grep 0 совпадений. |
| Dynamic `new $className(...)` | По текущему production grep 0 совпадений. |
| INI config loader | `*.ini` и `parse_ini_file()` в production-срезе не найдены. |
| Direct exception construction в leaf classes | Осталось только в named DI exception factories. |
| `form`, `tab/urlMaker`, image services, SOAP helper slices | Уже переведены на injected dependencies; fallback closures остались в DI boundary. |
| `request` service array helpers | В `core/service/request.php` прямых `array_val`, `adduceToArray`, `array_merge_recursive_alt` нет; helpers приходят через `request_service_factory` и `application_core_service_creator`. |
| Base service class/cache helpers | В `core/base/service.php` прямых `get_class_name` и `array_val` нет; class-name/cache lookup идут через `bootstrap_runtime` support callables. |
| Curl service array helpers | В `core/service/curl.php` прямых `array_val` и `adduceToArray` нет; headers/cookie lookup идут через injected `arrayAdducer` / `curlArrayValueReader`. |
| Session service array helper | В `core/service/session.php` прямого `array_val` нет; `_compareSystem()` читает request/session params через inherited injected `arrayValueReader`, который прокидывается через `session_service_factory` и `application_session_service_creator`. |
| Session state buffer helper | В `core/service/session_state.php` прямого `array_val` нет; buffer lookup идет через injected `arrayValueReader`, который прокидывается из `application_state_registry`. |
| Database service error-data helper | В `core/service/database.php` прямого `array_val` нет; error-data lookup идет через injected `arrayValueReader`, который прокидывается из `application_database_service_creator` в `database_service_factory`. |
| Entity service reverse-link helper | В `core/service/entity.php` прямого `array_val` нет; reverse-link cache lookup идет через inherited injected `arrayValueReader`, который прокидывается из `application_entity_service_creator` в `entity_service_factory`. |
| Entity service delegate array-like helper | В `core/service/entity.php` прямого `is_array_alt` нет; delegate config проверяется через injected `arrayLikeChecker`, который прокидывается из `application_entity_service_creator` через `entity_service_factory`, а compatibility fallback живет в DI-boundary `array_like_checker`. |
| Entity description class-name helper | В `core/service/entity/description.php` прямого `get_class_alt` нет; cache metadata class-name строится через injected `classNameResolver`, который прокидывается из entity service runtime в `entity_description_factory`, а compatibility fallback живет только на DI-boundary. |
| Entity designer class-name helper | В `core/service/entity/designer.php` прямого `get_class_alt` нет; unknown SQL-part class-name строится через injected `classNameResolver`, который прокидывается из entity service runtime через `entity_designer_factory`. |
| Plain fatal class-name helper | В `core/exception/plain/fatal.php` прямого `get_class_alt` нет; controller class-name для лога строится через injected `classNameResolver`, который прокидывается из `application_support_service_registrar` через `plain_exception_factory`. |
| Template fatal class-name helper | В `core/exception/template/fatal.php` прямого `get_class_alt` нет; template class-name для лога строится через injected `classNameResolver`, который прокидывается из `application_support_service_registrar` через `template_exception_factory`. |
| Base data class-name helper | В `core/base/data.php` прямого `get_class_alt` нет; `_isThisClass()` использует injected/inherited `classNameResolver` с native fallback, не сериализуя closure-state. |
| Base model row array-like helper | В `core/base/model/row.php` прямого `is_array_alt` нет; `getFields()` использует injected `arrayLikeChecker`, который наследуется из entity service row-dependencies, а native fallback оставляет семантику `array` / `ArrayAccess`. |
| Log viewer date split helper | В `project/app/__log_viewer/main/get_log_data.php` прямого `explode_alt` нет; дата для `dateService()` берется через native `explode('_', (string)$data['date'], 2)[0]`, потому что legacy `$num` здесь не используется. |
| Tools upgrade argument split helper | В `project/app/__tools/main/upgrade_blocks.php` прямого `explode_alt` нет; `getOneEntityByKey` argument split идет через private `splitArgumentList()`, который сохраняет старое padding-поведение через native `explode()` + `array_pad()`. |
| Root HTML helper boundaries | В `core/block/root/html.php` прямых `get_class_name` и `is_array_alt` нет; fallback title использует injected `shortClassNameResolver`, а `setEmbedCssByMeta()` использует injected `arrayLikeChecker`, которые прокидываются через block/tab dependency bundle из support registrar. |
| Block base short class-name helper | В `core/block/base.php` прямого `get_class_name` нет; template lookup по parent path использует injected `shortClassNameResolver` через block/tab dependency bundle, с native fallback для прямого test/runtime construction. |
| Spec-file row namespace helper | В `core/base/model/spec_file/row.php` прямого `get_ns_name` нет; file-data entity namespace строится через inherited `namespaceName()` / injected `namespaceResolver`, который прокидывается из entity service row-dependency bundle. |
| Model entity namespace helper | В `core/base/model/entity.php` прямого `get_ns_name` нет; connection namespace lookup использует injected `namespaceResolver`, который прокидывается через `model_entity_factory` из entity service. |
| Template service short class-name helper | В `core/service/template.php` прямого `get_class_name` нет; compiled-template class short-name строится через injected `shortClassNameResolver`, который прокидывается из `application_content_service_creator` через `template_service_factory`, а base-service class-name dependency получает injected `classNameResolver`. |
| Bootstrap loader alias-arg helper | В `core/bootstrap/loader.php` прямого `array_val` нет; `cnt_alias_arg` читается через injected `arrayValueReader`, который прокидывается из `application::defineObj()` через контейнер. |
| Tools counter subnav array helper | В `project/app/__tools/design/nav/counter_subnav.php` прямого `array_val` нет; main-request lookup идет через inherited block `arrayValueReader()`. |
| Cache memcache engine array helpers | В `core/service/cache/memcache.php` прямого `array_val` нет; HOST/PORT читаются через injected `arrayValueReader`, который прокидывается из `cache_engine_factory`. |
| User base engine array helpers | В `core/service/user/base.php` прямых `array_val` и `adduceToArray` нет; `getId()` и `_set()` читают данные через injected `arrayValueReader`, который прокидывается из `user_engine_factory`. |
| User entity engine array/class helpers | В `core/service/user/entity.php` прямых `array_val`, `adduceToArray`, `get_class_alt` нет; login lookup, map normalization и class-name lookup идут через injected callables из `user_engine_factory`. |
| User config engine array helper | В `core/service/user/config.php` прямого `array_val` нет; login lookup в `makePasswordHash()` идет через inherited injected `arrayValueReader`. |
| User service role normalization | В `core/service/user.php` прямого `adduceToArray` нет; `removeRole()` нормализует роль через injected `arrayAdducer`, который прокидывается из `application_user_service_creator` через `user_service_factory`, включая restored session users. |
| Locale service normalization helpers | В `core/service/locale.php` прямого `adduceToArray` нет; language list normalization идет через injected `arrayAdducer`, который прокидывается из `locale_service_factory` и `application_core_service_creator`. |
| Application service used-names normalization | В `core/service/application.php` прямого `adduceToArray` нет; `used_names` нормализуется через injected `arrayAdducer`, который прокидывается из `application_core_service_creator` через `application_service_factory`. |
| Debug service meta normalization | В `core/service/debug.php` прямого `adduceToArray` нет; `_reduceMetaArray()` нормализует metadata через injected `arrayAdducer`, который прокидывается из `application_core_service_creator` через `debug_service_factory`. |
| Meta maker helper operations | В `core/base/meta/maker.php` прямых `adduceToArray`, `array_merge_recursive_alt` и `get_class_alt` нет; normalizer, recursive merger и class-name resolver приходят из `meta_maker_factory`, который регистрируется через `application_support_service_registrar`. |
| Header service response-code merger | В `core/service/header.php` прямого `array_merge_recursive_alt` нет; response-code merge идет через injected `recursiveMerger`, который прокидывается из `application_core_service_creator` через `header_service_factory`. |
| Config service short class-name resolver | В `core/service/config.php` и `core/service/config/row.php` прямого `get_class_name` нет; short-name lookup идет через injected `shortClassNameResolver`, который прокидывается из `application_infrastructure_service_creator` / `config_service_factory` / `config_row_factory`. |

## Команды среза

| Проверка | Результат |
|---|---|
| `find core project cli htdocs -type f -name '*.php'` | 750 PHP-файлов |
| `rg -n -e 'containerService' -e 'getContainerService' -e 'blockService' -e 'resolveService' -e 'legacyService' -e 'container_registry::get' -e '::instance\(' -e 'parse_ini_file\(' -e 'call_user_func' -e 'eval\(' core project cli htdocs -g '*.php'` | 0 matches |
| `rg -n -P 'new\s+\$[A-Za-z_][A-Za-z0-9_]*\s*\(' core project cli htdocs -g '*.php'` | 0 matches |
| `rg --files -g '*.ini' core project cli htdocs` | 0 matches |
| `rg -n '\b(adduceToArray\|array_merge_recursive_alt\|array_val\|get_class_name\|get_class_alt\|get_ns_name\|is_array_alt\|explode_alt\|increaseNum\|decreaseNum)\s*\(' core project cli htdocs -g '*.php'` | 73 matching lines / 74 calls |
| Same helper grep excluding `core/functions.php` and `core/di/*` | 1 matching line; `core/service/timer.php` is a method-call false-positive |
| `rg -n '\b(require\|require_once\|include\|include_once)\b' core project cli htdocs -g '*.php'` | 205 matches |
| `rg -n '\$_(GET\|POST\|REQUEST\|COOKIE\|SERVER\|SESSION\|FILES)\|\$GLOBALS' core project cli htdocs -g '*.php'` | 20 matches |
| `rg -n -P '\bclass_exists\s*\(|\binterface_exists\s*\(|\btrait_exists\s*\(|\bnew\s+\\Reflection|\bReflectionClass\b|configured_class_instantiator' core project cli htdocs -g '*.php'` | 303 matches |
| `find core project cli htdocs -type f -name '*.meta.php'` | 54 files |
| `find core project cli htdocs -type f -name '*.tpl'` | 46 files |

## Валидация

Дополнительно закрыт meta-maker helper-срез: focused PHPUnit `unit/core/base/meta/MakerTest.php unit/core/di/MetaMakerFactoryTest.php unit/core/di/ApplicationSupportServiceRegistrarTest.php unit/core/LegacyDiSourceInventoryTest.php` -> `OK (334 tests, 129581 assertions)`.

Дополнительно закрыт header-service `array_merge_recursive_alt` helper-срез: focused PHPUnit `unit/core/service/HeaderTest.php unit/core/di/HeaderServiceFactoryTest.php unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/LegacyDiSourceInventoryTest.php` -> `OK (336 tests, 129555 assertions)`.

Дополнительно закрыт config/config-row `get_class_name` helper-срез: focused PHPUnit `unit/core/service/ConfigTest.php unit/core/service/config/RowTest.php unit/core/di/ConfigServiceFactoryTest.php unit/core/di/ConfigRowFactoryTest.php unit/core/di/ApplicationInfrastructureServiceCreatorTest.php unit/core/di/ApplicationServiceCreatorDefaultsProviderFactoryTest.php unit/core/di/ApplicationSupportServiceRegistrarTest.php unit/core/FunctionsServiceContainerTest.php unit/core/LegacyDiSourceInventoryTest.php` -> `OK (419 tests, 131698 assertions)`.

Дополнительно закрыт entity-service delegate `is_array_alt` helper-срез: focused PHPUnit `unit/core/service/EntityTest.php unit/core/di/EntityServiceFactoryTest.php unit/core/di/ApplicationEntityServiceCreatorTest.php unit/core/di/ApplicationSupportServiceRegistrarTest.php unit/core/FunctionsServiceContainerTest.php unit/core/LegacyDiSourceInventoryTest.php` -> `OK (398 tests, 131610 assertions)`.

Дополнительно закрыт template-service `get_class_name` helper-срез: focused PHPUnit `unit/core/service/TemplateTest.php unit/core/di/TemplateServiceFactoryTest.php unit/core/di/ApplicationContentServiceCreatorTest.php unit/core/di/ApplicationSupportServiceRegistrarTest.php unit/core/FunctionsServiceContainerTest.php unit/core/LegacyDiSourceInventoryTest.php` -> `OK (397 tests, 131549 assertions)`.

Дополнительно закрыт entity-description `get_class_alt` helper-срез: focused PHPUnit `unit/core/service/entity/DescriptionTest.php unit/core/di/EntityDescriptionFactoryTest.php unit/core/service/EntityTest.php unit/core/di/EntityServiceFactoryTest.php unit/core/di/ApplicationEntityServiceCreatorTest.php unit/core/LegacyDiSourceInventoryTest.php` -> `OK (344 tests, 129701 assertions)`.

Дополнительно закрыт entity-designer `get_class_alt` helper-срез: focused PHPUnit `unit/core/service/entity/DesignerTest.php unit/core/di/EntityModelFactoriesTest.php unit/core/service/EntityTest.php unit/core/di/EntityServiceFactoryTest.php unit/core/di/ApplicationEntityServiceCreatorTest.php unit/core/LegacyDiSourceInventoryTest.php` -> `OK (350 tests, 129773 assertions)`.

Дополнительно закрыт plain-fatal `get_class_alt` helper-срез: focused PHPUnit `unit/core/exception/plain/FatalTest.php unit/core/di/PlainExceptionFactoryTest.php unit/core/di/ApplicationSupportServiceRegistrarTest.php unit/core/LegacyDiSourceInventoryTest.php` -> `OK (324 tests, 129562 assertions)`.

Дополнительно закрыт template-fatal `get_class_alt` helper-срез: focused PHPUnit `unit/core/exception/template/FatalTest.php unit/core/di/TemplateExceptionFactoryTest.php unit/core/di/ApplicationSupportServiceRegistrarTest.php unit/core/LegacyDiSourceInventoryTest.php` -> `OK (324 tests, 129572 assertions)`.

Дополнительно закрыт base-data `get_class_alt` helper-срез: focused PHPUnit `unit/core/base/DataTest.php unit/core/LegacyDiSourceInventoryTest.php` -> `OK (327 tests, 129509 assertions)`.

Дополнительно закрыт base-model-row `is_array_alt` helper-срез: focused PHPUnit `unit/core/base/model/RowTest.php unit/core/service/EntityTest.php unit/core/di/EntityServiceFactoryTest.php unit/core/LegacyDiSourceInventoryTest.php` -> `OK (342 tests, 129685 assertions)`.

Дополнительно закрыт log-viewer `explode_alt` helper-срез: focused PHPUnit `unit/project/AppLogViewerDataTest.php unit/core/LegacyDiSourceInventoryTest.php` -> `OK (319 tests, 129469 assertions)`.

Дополнительно закрыт tools-upgrade `explode_alt` helper-срез: focused PHPUnit `unit/project/AppToolsUpgradeBlocksTest.php unit/core/LegacyDiSourceInventoryTest.php` -> `OK (320 tests, 129476 assertions)`.

Дополнительно закрыт root-html `get_class_name` / `is_array_alt` helper-срез: focused PHPUnit `unit/core/block/root/HtmlTest.php unit/core/block/BaseTest.php unit/core/service/TabTest.php unit/core/di/TabServiceFactoryTest.php unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/LegacyDiSourceInventoryTest.php` -> `OK (386 tests, 129965 assertions)`.

Дополнительно закрыт block-base `get_class_name` helper-срез: focused PHPUnit `unit/core/block/root/HtmlTest.php unit/core/block/BaseTest.php unit/core/LegacyDiSourceInventoryTest.php` -> `OK (359 tests, 129719 assertions)`.

Дополнительно закрыт spec-file-row `get_ns_name` helper-срез: focused PHPUnit `unit/core/base/model/RowTest.php unit/core/base/model/spec_file/RowTest.php unit/core/service/EntityTest.php unit/core/di/EntityServiceFactoryTest.php unit/core/di/ApplicationEntityServiceCreatorTest.php unit/core/di/ApplicationSupportServiceRegistrarTest.php unit/core/LegacyDiSourceInventoryTest.php` -> `OK (353 tests, 129894 assertions)`.

Дополнительно закрыт model-entity `get_ns_name` helper-срез: focused PHPUnit `unit/core/base/model/EntityTest.php unit/core/di/EntityModelFactoriesTest.php unit/core/service/EntityTest.php unit/core/di/EntityServiceFactoryTest.php unit/core/di/ApplicationEntityServiceCreatorTest.php unit/core/di/ApplicationSupportServiceRegistrarTest.php unit/core/LegacyDiSourceInventoryTest.php` -> `OK (355 tests, 129980 assertions)`.
