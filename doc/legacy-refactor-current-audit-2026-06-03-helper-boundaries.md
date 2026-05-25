# Отчет: что осталось от старого подхода

Дата анализа: 2026-06-03

Ветка: `migrate_to-php8`

Область анализа: `_core`, `_project`, `cli`, `htdocs`; без `vendor` и `legacy_assets`.

Важно: это срез текущей dirty рабочей копии. В проекте уже есть большой незакоммиченный слой PHP 8 / DI / adapter-модернизации, поэтому отчет описывает текущее состояние workspace, а не чистый git baseline.

## Короткий вывод

Основной старый service-locator/procedural каркас уже почти снят: широкие gateway-вызовы (`containerService`, `getContainerService`, `blockService`, `resolveService`, `legacyService`, `container_registry::get`, `::instance`, `parse_ini_file`, `call_user_func*`, `eval`) в production PHP-срезе не найдены. Прямой dynamic construction вида `new $className(...)` тоже не найден.

Оставшийся долг теперь сосредоточен в других местах: direct global helper calls, ручная загрузка файлов, static bootstrap compatibility facade, config/meta/reflection runtime, process-global request/session adapters и legacy template/meta слой.

## Статический срез

| Метрика | Текущее значение | Что означает |
|---|---:|---|
| Production PHP-файлов | 750 | Размер анализируемого runtime-среза. |
| Broad legacy gateway patterns | 0 | Старые service-locator/dispatcher входы по широкому grep не найдены. |
| Dynamic `new $className(...)` | 0 | Прямое variable-class construction не найдено. |
| `*.ini` в production-срезе | 0 | INI migration в текущем срезе закрыта. |
| Service constructors без явных base deps | 0 | Старые `parent::__construct()` / `parent::__construct($allowIni)` хвосты не найдены. |
| Direct helper calls | 73 строки / 74 вызова | Общий grep по compatibility helpers, включая DI-boundary fallback closures. |
| Direct helper calls вне `_core/functions.php` и `_core/di/*` | 1 строка | Runtime direct global helper calls в текущем grep-срезе очищены; оставшаяся строка в `_core/service/timer.php` является method-name false-positive, а не global helper call. |
| `include` / `require` grep matches | 205 | Ручная загрузка еще живет в bootstrap/defaults/loader/installer/template boundaries. |
| Raw superglobals / `$GLOBALS` matches | 20 | В основном native request/session adapters и diagnostics. |
| Reflection / `class_exists` / configured instantiator matches | 303 | Config/meta-driven class resolution остается важным runtime механизмом. |
| `*.meta.php` | 54 | Legacy meta-runtime остается отдельным динамическим слоем. |
| `*.tpl` | 46 | Template runtime еще требует отдельного compatibility refactor. |
| Top-level function definitions | 11 | В основном `_core/functions.php` плюс ADOdb callback. |
| Direct exception construction | 8 | Все совпадения находятся в named DI exception factories. |

## Helper хвост

| Helper | Вызовов | Комментарий |
|---|---:|---|
| `array_val` | 13 | Runtime-срез вне `_core/functions.php` и `_core/di/*` очищен; остались helper definition и DI-boundary fallback closures. |
| `adduceToArray` | 17 | Runtime-срез вне `_core/functions.php` и `_core/di/*` очищен; остались helper definition и DI-boundary fallback closures. |
| `get_class_name` | 7 | Runtime config/config-row/template/root-html/block-base очищены; общий счетчик держится из-за DI-boundary fallback для `short_class_name_resolver` и method-name false-positive в timer row. |
| `get_class_alt` | 13 | В основном DI-boundary class-name resolver fallback-и; entity-description, entity-designer, plain fatal, template fatal и base data уже используют injected/native resolvers. |
| `array_merge_recursive_alt` | 9 | Хороший кандидат на injected recursive merger там, где логика не является composition boundary. |
| `is_array_alt` | 9 | Runtime entity delegate, base-model-row и root-html checks очищены; остались helper definition и DI-boundary fallback-и `array_like_checker`. |
| `get_ns_name` | 3 | Runtime model/spec-file callers очищены; остались helper definition и DI-boundary fallback-и `namespace_resolver`. |
| `explode_alt` | 1 | Осталась только helper definition; log-viewer date split и tools upgrade argument split уже переведены на native `explode()`. |
| `increaseNum` / `decreaseNum` | 2 | Только helper definitions; production callers не видны. |

После закрытия `form`, `number` validator, `urlMaker`, image services, `soap`, `request`, base service, `curl`, `session`, `cache/memcache`, `user/base`, `user/entity`, `user/config`, `locale`, `meta/maker`, `header`, `config/config-row`, entity delegate, entity-description, entity-designer, plain-fatal, template-fatal, base-data, base-model-row, log-viewer, tools-upgrade, root-html, block-base, spec-file-row и model-entity helper-срезов прямых runtime global helper calls вне `_core/functions.php` и `_core/di/*` больше не видно. `_core/service/timer.php` в grep-хвосте является method-name false-positive.

Отдельно: в `_core/block` и `_project/block` прямых `adduceToArray`, `array_merge_recursive_alt`, `array_val` уже нет. Этот block-срез можно считать закрытым для текущего helper-кластера.

## Таблица оставшегося долга

| Приоритет | Участок | Что осталось от старого подхода | Примеры файлов | Риск | Что рефакторить дальше |
|---:|---|---|---|---|---|
| 1 | Runtime helper calls | Прямых global helper calls вне `_core/functions.php` и `_core/di/*` больше не видно; grep-хвост держится на `_core/service/timer.php`, где `get_class_name()` является методом row object. | `_core/service/timer.php` false-positive | Низкий | Держать source guards; следующий долг искать уже не в direct helper callers, а в manual loading / bootstrap / dynamic runtime слоях. |
| 2 | `array_val` runtime cleanup | Прямых `array_val` вызовов вне `_core/functions.php` и `_core/di/*` больше нет. | DI fallback-и в `_core/di/*`, helper definition в `_core/functions.php` | Низкий | Держать source guards; дальше удалять DI fallback-и только после полной уверенности, что все composition roots дают `array_value_reader`. |
| 3 | Model helper calls | Runtime model/spec-file direct helper callers очищены; namespace lookup идет через injected `namespaceResolver`. | `_core/base/model/entity.php`, `_core/base/model/spec_file/row.php` | Низкий | Держать source guards; дальнейший model-долг уже связан не с helper calls, а с dynamic config/reflection contracts. |
| 4 | Template form runtime | Закрыто для текущего helper-кластера: `template/type/base` и `template/type/form` получают `arrayValueReader` / `arrayAdducer` через DI. Fallback-обертки остались только в `template_service_factory` boundary. | `_core/service/template/type/base.php`, `_core/service/template/type/form.php` | Низкий | Держать source guard; следующий долг в template слое уже не array helpers, а meta/template runtime совместимость. |
| 6 | Manual loading layer | 205 `include` / `require` matches. Большая часть уже допустима как explicit boundary, но allowlist широкий. | `_core/bootstrap/*provider_factory.php`, `_core/di/*provider_factory.php`, `_core/bootstrap/loader.php`, `_core/adapter/compiled_template_loader.php`, `htdocs/install/incl/*` | Средний | Сужать allowlist по одному boundary-семейству. Hidden loads поднимать в explicit provider/defaults factories или adapter boundary. |
| 7 | Static bootstrap compatibility facade | Static API уже тоньше, но runtime все еще поддерживает legacy facade и compatibility bootstrap entrypoints. | `_core/bootstrap.php`, `_core/bootstrap/application.php`, `_core/bootstrap/context*.php` | Средний | Оставить static `bootstrap` как BC-shell, а новые consumers переводить на explicit `application` / `context` / runner objects. |
| 8 | Config/meta/reflection runtime | Прямого `new $className` нет, но 303 совпадения `class_exists`/reflection/configured instantiation показывают сильную зависимость от class-name strings. | `_core/di/configured_class_instantiator.php`, `_core/di/*_factory.php`, `_core/service/entity.php`, `_core/service/tab.php`, `_core/base/model/entity.php` | Средний | Разделить internal typed factory map и внешний extension API. Reflection оставить только для настоящих extension points. |
| 9 | Process-global request/session boundary | Raw globals уже в adapters, но mutable process state все еще является boundary. | `_core/adapter/request_input_native_environment.php`, `_core/adapter/request_input_globals.php`, `_core/adapter/adodb_session_native_environment.php`, `_core/adapter/adodb_session_globals.php`, `_core/service/error.php` | Средний | Выше adapter-слоя передавать request/session context objects; держать guard против superglobals вне native adapters/diagnostics. |
| 10 | Legacy template/meta runtime | 54 meta-файла и 46 template-файлов остаются динамической подсистемой. | `_core/service/template/*`, `_core/view/parser/*`, `_core/block/**/*.meta.php`, `_project/**/*.meta.php` | Средний | Планировать отдельным срезом после DI/bootstrap cleanup: typed contracts вокруг parser/compiled-template, старый runtime оставить compatibility adapter-ом. |
| 11 | Top-level utility functions | `_core/functions.php` все еще содержит compatibility helpers. | `_core/functions.php`, `_core/service/database/adodb.php` | Низкий-средний | Не удалять массово. Сначала убрать callers из runtime-сервисов, потом оставить файл как тонкую BC-зону или разбить на injectable utility services. |
| 12 | Legacy comments / string refs | Исполняемых `new fatalException` нет, но остались comment-only alias imports и meta-string `bootstrap::parsePath`. | `_core/plain/captcha.php`, `_core/plain/db_file.php`, `_core/plain/video.php`, `_project/app/__tools/main/upgrade_blocks.meta.php` | Низкий | Удалить отдельным cleanup-срезом, чтобы source inventory был чище. |

## Рекомендуемая очередь

| Шаг | Зона | Почему сейчас | Минимальная проверка |
|---:|---|---|---|
| 1 | Helper guard maintenance | Runtime direct helper callers очищены; теперь главный риск - регрессия через новые direct calls. | Focused tests + source guard по конкретному файлу. |
| 2 | Model project helper calls | Остались namespace helpers вне service layer. | Focused model/project tests + source guard. |
| 3 | Manual loading allowlist | После helper cleanup следующий источник старого подхода - широкая ручная загрузка. | `unit/_core/LegacyDiSourceInventoryTest.php`, grep loading statements, `git diff --check`. |
| 4 | Template/meta runtime plan | Рискованная динамическая подсистема; ее лучше не смешивать с helper DI cleanup. | Template/parser behavior tests, compiled-template smoke. |

## Что уже не главный долг

| Участок | Почему |
|---|---|
| Старые service-locator gateway names | По текущему production grep 0 совпадений. |
| Dynamic `new $className(...)` | По текущему production grep 0 совпадений. |
| INI config loader | `*.ini` в production-срезе не найдены, `parse_ini_file()` не найден. |
| Service constructors без base deps | Старые `parent::__construct()` / `$allowIni` хвосты не найдены. |
| Direct service fatal construction в leaf classes | Осталось только в `_core/factory/service_exception_factory.php`. |
| Block-layer array helpers | В `_core/block` и `_project/block` прямых `adduceToArray`, `array_merge_recursive_alt`, `array_val` нет. |
| `tab` service array helpers | В `_core/service/tab.php` прямых `array_val`, `is_array_alt`, `adduceToArray`, `array_merge_recursive_alt` нет. |
| `form` service array/class helpers | В `_core/service/form.php` прямых `array_val`, `adduceToArray`, `array_merge_recursive_alt`, `get_class_alt` нет; зависимости приходят через constructor/factory DI. |
| `number` form validator array helper | В `_core/service/form/validator/number.php` прямого `array_val` нет; lookup идет через `arrayValueReader`, который прокидывает `form` service. |
| `urlMaker` tab delegate array helper | В `_core/service/tab/delegate/urlMaker.php` прямого `array_val` нет; array-config lookup идет через `arrayValueReader`, который прокидывает `tab` service. |
| Image services array helpers | В `_core/service/image_modify.php` и `_core/service/image_draw.php` прямого `array_val` нет; coordinate lookup идет через injected `arrayValueReader`. |
| SOAP service array helpers | В `_core/service/soap.php` прямого `array_val` нет; SOAP params и `SoapVar` metadata читаются через injected `arrayValueReader`. |
| Request service array helpers | В `_core/service/request.php` прямых `array_val`, `adduceToArray`, `array_merge_recursive_alt` нет; request получает `arrayAdducer`, `recursiveMerger`, `arrayValueReader` через `request_service_factory` и `application_core_service_creator`. |
| Base service class/cache helpers | В `_core/base/service.php` прямых `get_class_name` и `array_val` нет; class-name и cache lookup читаются через support callables из `bootstrap_runtime`. |
| Curl service array helpers | В `_core/service/curl.php` прямых `array_val` и `adduceToArray` нет; headers/cookie lookup читаются через injected `arrayAdducer` / `curlArrayValueReader`. |
| Session service array helper | В `_core/service/session.php` прямого `array_val` нет; `_compareSystem()` читает request/session params через inherited injected `arrayValueReader`, который прокидывается через `session_service_factory` и `application_session_service_creator`. |
| Session state buffer helper | В `_core/service/session_state.php` прямого `array_val` нет; buffer lookup идет через injected `arrayValueReader`, который прокидывается из `application_state_registry`. |
| Database service error-data helper | В `_core/service/database.php` прямого `array_val` нет; error-data lookup идет через injected `arrayValueReader`, который прокидывается из `application_database_service_creator` в `database_service_factory`. |
| Entity service reverse-link helper | В `_core/service/entity.php` прямого `array_val` нет; reverse-link cache lookup идет через inherited injected `arrayValueReader`, который прокидывается из `application_entity_service_creator` в `entity_service_factory`. |
| Entity service delegate array-like helper | В `_core/service/entity.php` прямого `is_array_alt` нет; delegate config проверяется через injected `arrayLikeChecker`, который прокидывается из `application_entity_service_creator` через `entity_service_factory`, а compatibility fallback живет в DI-boundary `array_like_checker`. |
| Entity description class-name helper | В `_core/service/entity/description.php` прямого `get_class_alt` нет; cache metadata class-name строится через injected `classNameResolver`, который прокидывается из entity service runtime в `entity_description_factory`, а compatibility fallback живет только на DI-boundary. |
| Entity designer class-name helper | В `_core/service/entity/designer.php` прямого `get_class_alt` нет; unknown SQL-part class-name строится через injected `classNameResolver`, который прокидывается из entity service runtime через `entity_designer_factory`. |
| Plain fatal class-name helper | В `_core/exception/plain/fatal.php` прямого `get_class_alt` нет; controller class-name для лога строится через injected `classNameResolver`, который прокидывается из `application_support_service_registrar` через `plain_exception_factory`. |
| Template fatal class-name helper | В `_core/exception/template/fatal.php` прямого `get_class_alt` нет; template class-name для лога строится через injected `classNameResolver`, который прокидывается из `application_support_service_registrar` через `template_exception_factory`. |
| Base data class-name helper | В `_core/base/data.php` прямого `get_class_alt` нет; `_isThisClass()` использует injected/inherited `classNameResolver` с native fallback, не сериализуя closure-state. |
| Base model row array-like helper | В `_core/base/model/row.php` прямого `is_array_alt` нет; `getFields()` использует injected `arrayLikeChecker`, который наследуется из entity service row-dependencies, а native fallback оставляет семантику `array` / `ArrayAccess`. |
| Log viewer date split helper | В `_project/app/__log_viewer/main/get_log_data.php` прямого `explode_alt` нет; дата для `dateService()` берется через native `explode('_', (string)$data['date'], 2)[0]`, потому что legacy `$num` здесь не используется. |
| Tools upgrade argument split helper | В `_project/app/__tools/main/upgrade_blocks.php` прямого `explode_alt` нет; `getOneEntityByKey` argument split идет через private `splitArgumentList()`, который сохраняет старое padding-поведение через native `explode()` + `array_pad()`. |
| Root HTML helper boundaries | В `_core/block/root/html.php` прямых `get_class_name` и `is_array_alt` нет; fallback title использует injected `shortClassNameResolver`, а `setEmbedCssByMeta()` использует injected `arrayLikeChecker`, которые прокидываются через block/tab dependency bundle из support registrar. |
| Block base short class-name helper | В `_core/block/base.php` прямого `get_class_name` нет; template lookup по parent path использует injected `shortClassNameResolver` через block/tab dependency bundle, с native fallback для прямого test/runtime construction. |
| Spec-file row namespace helper | В `_core/base/model/spec_file/row.php` прямого `get_ns_name` нет; file-data entity namespace строится через inherited `namespaceName()` / injected `namespaceResolver`, который прокидывается из entity service row-dependency bundle. |
| Model entity namespace helper | В `_core/base/model/entity.php` прямого `get_ns_name` нет; connection namespace lookup использует injected `namespaceResolver`, который прокидывается через `model_entity_factory` из entity service. |
| Template service short class-name helper | В `_core/service/template.php` прямого `get_class_name` нет; compiled-template class short-name строится через injected `shortClassNameResolver`, который прокидывается из `application_content_service_creator` через `template_service_factory`, а base-service class-name dependency получает injected `classNameResolver`. |
| Bootstrap loader alias-arg helper | В `_core/bootstrap/loader.php` прямого `array_val` нет; `cnt_alias_arg` читается через injected `arrayValueReader`, который прокидывается из `application::defineObj()` через контейнер. |
| Tools counter subnav array helper | В `_project/app/__tools/design/nav/counter_subnav.php` прямого `array_val` нет; main-request lookup идет через inherited block `arrayValueReader()`. |
| Cache memcache engine array helpers | В `_core/service/cache/memcache.php` прямого `array_val` нет; HOST/PORT читаются через injected `arrayValueReader`, который прокидывается из `cache_engine_factory`. |
| User base engine array helpers | В `_core/service/user/base.php` прямых `array_val` и `adduceToArray` нет; `getId()` и `_set()` читают данные через injected `arrayValueReader`, который прокидывается из `user_engine_factory`. |
| User entity engine array/class helpers | В `_core/service/user/entity.php` прямых `array_val`, `adduceToArray`, `get_class_alt` нет; login lookup, map normalization и class-name lookup идут через injected callables из `user_engine_factory`. |
| User config engine array helper | В `_core/service/user/config.php` прямого `array_val` нет; login lookup в `makePasswordHash()` идет через inherited injected `arrayValueReader`. |
| User service role normalization | В `_core/service/user.php` прямого `adduceToArray` нет; `removeRole()` нормализует роль через injected `arrayAdducer`, который прокидывается из `application_user_service_creator` через `user_service_factory`, включая restored session users. |
| Locale service normalization helpers | В `_core/service/locale.php` прямого `adduceToArray` нет; language list normalization идет через injected `arrayAdducer`, который прокидывается из `locale_service_factory` и `application_core_service_creator`. |
| Application service used-names normalization | В `_core/service/application.php` прямого `adduceToArray` нет; `used_names` нормализуется через injected `arrayAdducer`, который прокидывается из `application_core_service_creator` через `application_service_factory`. |
| Debug service meta normalization | В `_core/service/debug.php` прямого `adduceToArray` нет; `_reduceMetaArray()` нормализует metadata через injected `arrayAdducer`, который прокидывается из `application_core_service_creator` через `debug_service_factory`. |
| Template base array helper | В `_core/service/template/type/base.php` прямого `array_val` нет; чтение атрибутов идет через injected `arrayValueReader`. |
| Template form array helpers | В `_core/service/template/type/form.php` прямых `array_val` и `adduceToArray` нет; форма использует inherited `arrayValueReader` / `arrayAdducer`. |
| Meta maker helper operations | В `_core/base/meta/maker.php` прямых `adduceToArray`, `array_merge_recursive_alt` и `get_class_alt` нет; normalizer, recursive merger и class-name resolver приходят из `meta_maker_factory`, который регистрируется через `application_support_service_registrar`. |
| Header service response-code merger | В `_core/service/header.php` прямого `array_merge_recursive_alt` нет; response-code merge идет через injected `recursiveMerger`, который прокидывается из `application_core_service_creator` через `header_service_factory`. |
| Config service short class-name resolver | В `_core/service/config.php` и `_core/service/config/row.php` прямого `get_class_name` нет; short-name lookup идет через injected `shortClassNameResolver`, который прокидывается из `application_infrastructure_service_creator` / `config_service_factory` / `config_row_factory`. |

## Команды среза

| Проверка | Результат |
|---|---|
| `find _core _project cli htdocs -type f -name '*.php' \| wc -l` | 750 |
| `rg -n -e 'containerService' -e 'getContainerService' -e 'blockService' -e 'resolveService' -e 'legacyService' -e 'container_registry::get' -e '::instance\(' -e 'parse_ini_file\(' -e 'call_user_func' -e 'eval\(' _core _project cli htdocs -g '*.php'` | 0 matches |
| `rg -n -P 'new\s+\$[A-Za-z_][A-Za-z0-9_]*\s*\(' _core _project cli htdocs -g '*.php'` | 0 matches |
| `rg --files -g '*.ini' _core _project cli htdocs` | 0 matches |
| `rg -n -P 'parent::__construct\((?:\)\|true\)\|false\)\|\$allowIni\)\|empty\(self::\$instances\)\))' _core/service _core/base -g '*.php'` | 0 matches |
| `rg -n '\b(adduceToArray\|array_merge_recursive_alt\|array_val\|get_class_name\|get_class_alt\|get_ns_name\|is_array_alt\|explode_alt\|increaseNum\|decreaseNum)\s*\(' _core _project cli htdocs -g '*.php'` | 73 matching lines / 74 calls |
| Same helper grep excluding `_core/functions.php` and `_core/di/*` | 1 matching line; `_core/service/timer.php` is a method-call false-positive |
| `rg -n '\b(require\|require_once\|include\|include_once)\b' _core _project cli htdocs -g '*.php' \| wc -l` | 205 |
| `rg -n '\$_(GET\|POST\|REQUEST\|COOKIE\|SERVER\|SESSION\|FILES)\|\$GLOBALS' _core _project cli htdocs -g '*.php'` | 20 matches |
| `rg -n -P '\bclass_exists\s*\(\|\binterface_exists\s*\(\|\btrait_exists\s*\(\|\bnew\s+\\Reflection\|\bReflectionClass\b\|configured_class_instantiator' _core _project cli htdocs -g '*.php' \| wc -l` | 303 |
| `find _core _project cli htdocs -type f -name '*.meta.php' \| wc -l` | 54 |
| `find _core _project cli htdocs -type f -name '*.tpl' \| wc -l` | 46 |

## Валидация

В этом проходе после статического аудита были закрыты `form`, `number` validator, `urlMaker`, image service, SOAP, request, base-service, curl, session, cache/memcache, user/base, user/entity, user/config, locale, session-state, database и entity helper-срезы. Проверки: `php -l` по затронутым production/test файлам; focused PHPUnit `unit/_core/service/FormTest.php unit/_core/di/FormServiceFactoryTest.php unit/_core/di/ApplicationFormServiceCreatorTest.php unit/_core/di/ApplicationSupportServiceRegistrarTest.php` -> `OK (333 tests, 129483 assertions)`; focused PHPUnit `unit/_core/service/form/validator/BaseTest.php unit/_core/service/form/validator/NumberTest.php unit/_core/service/FormTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (342 tests, 129369 assertions)`; focused PHPUnit `unit/_core/service/tab/delegate/UrlMakerTest.php unit/_core/di/TabDelegateFactoryTest.php unit/_core/service/TabTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (342 tests, 129408 assertions)`; focused PHPUnit `unit/_core/service/ImageModifyTest.php unit/_core/service/ImageDrawTest.php unit/_core/di/ImageModifyServiceFactoryTest.php unit/_core/di/ApplicationUtilityServiceCreatorTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (346 tests, 129490 assertions)`; focused PHPUnit `unit/_core/service/SoapTest.php unit/_core/di/SoapServiceFactoryTest.php unit/_core/di/ApplicationUtilityServiceCreatorTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (328 tests, 129398 assertions)`; focused PHPUnit `unit/_core/service/RequestTest.php unit/_core/di/RequestServiceFactoryTest.php unit/_core/di/ApplicationCoreServiceCreatorTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (331 tests, 129399 assertions)`; focused PHPUnit `unit/_core/base/ServiceTest.php unit/_core/service/BootstrapRuntimeTest.php unit/_core/di/BootstrapRuntimeServiceFactoryTest.php unit/_core/bootstrap/BootstrapRuntimeServiceDefaultsProviderFactoryTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (357 tests, 129528 assertions)`; focused PHPUnit `unit/_core/service/CurlTest.php unit/_core/di/CurlServiceFactoryTest.php unit/_core/di/ApplicationClientServiceCreatorTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (329 tests, 129421 assertions)`; focused PHPUnit `unit/_core/service/SessionTest.php unit/_core/di/SessionServiceFactoryTest.php unit/_core/di/ApplicationSessionServiceCreatorTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (331 tests, 129445 assertions)`; focused PHPUnit `unit/_core/service/cache/MemcacheTest.php unit/_core/di/CacheEngineFactoryTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (326 tests, 129361 assertions)`; focused PHPUnit `unit/_core/service/user/BaseTest.php unit/_core/di/UserEngineFactoryTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (326 tests, 129379 assertions)`; focused PHPUnit `unit/_core/service/user/EntityTest.php unit/_core/service/user/BaseTest.php unit/_core/di/UserEngineFactoryTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (337 tests, 129435 assertions)`; focused user PHPUnit `unit/_core/service/user/ConfigTest.php unit/_core/service/user/BaseTest.php unit/_core/service/user/EntityTest.php unit/_core/di/UserEngineFactoryTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (347 tests, 129469 assertions)`; focused locale/resolver PHPUnit `unit/_core/service/LocaleTest.php unit/_core/di/LocaleServiceFactoryTest.php unit/_core/di/ApplicationCoreServiceCreatorTest.php unit/_core/service/RequestTest.php unit/_core/di/RequestServiceFactoryTest.php unit/_core/service/FormTest.php unit/_core/service/TabTest.php unit/_core/di/TabServiceFactoryTest.php unit/_core/di/ApplicationNavigationServiceCreatorTest.php unit/_core/service/SoapTest.php unit/_core/di/SoapServiceFactoryTest.php unit/_core/di/ApplicationUtilityServiceCreatorTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (420 tests, 130059 assertions)`; focused session-state PHPUnit `unit/_core/service/SessionStateTest.php unit/_core/service/SessionTest.php unit/_core/di/SessionServiceFactoryTest.php unit/_core/di/ApplicationSessionServiceCreatorTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (342 tests, 129518 assertions)`; focused database PHPUnit `unit/_core/service/DatabaseTest.php unit/_core/di/DatabaseServiceFactoryTest.php unit/_core/di/ApplicationDatabaseServiceCreatorTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (335 tests, 129494 assertions)`; focused entity/database PHPUnit `unit/_core/service/EntityTest.php unit/_core/di/EntityServiceFactoryTest.php unit/_core/di/ApplicationEntityServiceCreatorTest.php unit/_core/service/DatabaseTest.php unit/_core/di/DatabaseServiceFactoryTest.php unit/_core/di/ApplicationDatabaseServiceCreatorTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (351 tests, 129698 assertions)`.

Дополнительно закрыт bootstrap-loader helper-срез: focused PHPUnit `unit/_core/bootstrap/LoaderTest.php unit/_core/bootstrap/ApplicationTest.php unit/_core/bootstrap/BootstrapSourceTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (334 tests, 129570 assertions)`.

Дополнительно закрыт tools counter-subnav helper-срез: focused PHPUnit `unit/_project/AppToolsDesignNavTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (315 tests, 129350 assertions)`.

Дополнительно закрыт application-service `adduceToArray` helper-срез: focused PHPUnit `unit/_core/service/ApplicationTest.php unit/_core/di/ApplicationServiceFactoryTest.php unit/_core/di/ApplicationCoreServiceCreatorTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (333 tests, 129472 assertions)`.

Дополнительно закрыт debug-service `adduceToArray` helper-срез: focused PHPUnit `unit/_core/service/DebugTest.php unit/_core/di/DebugServiceFactoryTest.php unit/_core/di/ApplicationCoreServiceCreatorTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (336 tests, 129499 assertions)`.

Дополнительно закрыт user-service `adduceToArray` helper-срез: focused PHPUnit `unit/_core/service/UserTest.php unit/_core/di/UserServiceFactoryTest.php unit/_core/di/ApplicationUserServiceCreatorTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (345 tests, 129552 assertions)`.

Дополнительно закрыт meta-maker helper-срез: focused PHPUnit `unit/_core/base/meta/MakerTest.php unit/_core/di/MetaMakerFactoryTest.php unit/_core/di/ApplicationSupportServiceRegistrarTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (334 tests, 129581 assertions)`.

Дополнительно закрыт header-service `array_merge_recursive_alt` helper-срез: focused PHPUnit `unit/_core/service/HeaderTest.php unit/_core/di/HeaderServiceFactoryTest.php unit/_core/di/ApplicationCoreServiceCreatorTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (336 tests, 129555 assertions)`.

Дополнительно закрыт config/config-row `get_class_name` helper-срез: focused PHPUnit `unit/_core/service/ConfigTest.php unit/_core/service/config/RowTest.php unit/_core/di/ConfigServiceFactoryTest.php unit/_core/di/ConfigRowFactoryTest.php unit/_core/di/ApplicationInfrastructureServiceCreatorTest.php unit/_core/di/ApplicationServiceCreatorDefaultsProviderFactoryTest.php unit/_core/di/ApplicationSupportServiceRegistrarTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (419 tests, 131698 assertions)`.

Дополнительно закрыт entity-service delegate `is_array_alt` helper-срез: focused PHPUnit `unit/_core/service/EntityTest.php unit/_core/di/EntityServiceFactoryTest.php unit/_core/di/ApplicationEntityServiceCreatorTest.php unit/_core/di/ApplicationSupportServiceRegistrarTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (398 tests, 131610 assertions)`.

Дополнительно закрыт template-service `get_class_name` helper-срез: focused PHPUnit `unit/_core/service/TemplateTest.php unit/_core/di/TemplateServiceFactoryTest.php unit/_core/di/ApplicationContentServiceCreatorTest.php unit/_core/di/ApplicationSupportServiceRegistrarTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (397 tests, 131549 assertions)`.

Дополнительно закрыт entity-description `get_class_alt` helper-срез: focused PHPUnit `unit/_core/service/entity/DescriptionTest.php unit/_core/di/EntityDescriptionFactoryTest.php unit/_core/service/EntityTest.php unit/_core/di/EntityServiceFactoryTest.php unit/_core/di/ApplicationEntityServiceCreatorTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (344 tests, 129701 assertions)`.

Дополнительно закрыт entity-designer `get_class_alt` helper-срез: focused PHPUnit `unit/_core/service/entity/DesignerTest.php unit/_core/di/EntityModelFactoriesTest.php unit/_core/service/EntityTest.php unit/_core/di/EntityServiceFactoryTest.php unit/_core/di/ApplicationEntityServiceCreatorTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (350 tests, 129773 assertions)`.

Дополнительно закрыт plain-fatal `get_class_alt` helper-срез: focused PHPUnit `unit/_core/exception/plain/FatalTest.php unit/_core/di/PlainExceptionFactoryTest.php unit/_core/di/ApplicationSupportServiceRegistrarTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (324 tests, 129562 assertions)`.

Дополнительно закрыт template-fatal `get_class_alt` helper-срез: focused PHPUnit `unit/_core/exception/template/FatalTest.php unit/_core/di/TemplateExceptionFactoryTest.php unit/_core/di/ApplicationSupportServiceRegistrarTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (324 tests, 129572 assertions)`.

Дополнительно закрыт base-data `get_class_alt` helper-срез: focused PHPUnit `unit/_core/base/DataTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (327 tests, 129509 assertions)`.

Дополнительно закрыт base-model-row `is_array_alt` helper-срез: focused PHPUnit `unit/_core/base/model/RowTest.php unit/_core/service/EntityTest.php unit/_core/di/EntityServiceFactoryTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (342 tests, 129685 assertions)`.

Дополнительно закрыт log-viewer `explode_alt` helper-срез: focused PHPUnit `unit/_project/AppLogViewerDataTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (319 tests, 129469 assertions)`.

Дополнительно закрыт tools-upgrade `explode_alt` helper-срез: focused PHPUnit `unit/_project/AppToolsUpgradeBlocksTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (320 tests, 129476 assertions)`.

Дополнительно закрыт root-html `get_class_name` / `is_array_alt` helper-срез: focused PHPUnit `unit/_core/block/root/HtmlTest.php unit/_core/block/BaseTest.php unit/_core/service/TabTest.php unit/_core/di/TabServiceFactoryTest.php unit/_core/di/ApplicationNavigationServiceCreatorTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (386 tests, 129965 assertions)`.

Дополнительно закрыт block-base `get_class_name` helper-срез: focused PHPUnit `unit/_core/block/root/HtmlTest.php unit/_core/block/BaseTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (359 tests, 129719 assertions)`.

Дополнительно закрыт spec-file-row `get_ns_name` helper-срез: focused PHPUnit `unit/_core/base/model/RowTest.php unit/_core/base/model/spec_file/RowTest.php unit/_core/service/EntityTest.php unit/_core/di/EntityServiceFactoryTest.php unit/_core/di/ApplicationEntityServiceCreatorTest.php unit/_core/di/ApplicationSupportServiceRegistrarTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (353 tests, 129894 assertions)`.

Дополнительно закрыт model-entity `get_ns_name` helper-срез: focused PHPUnit `unit/_core/base/model/EntityTest.php unit/_core/di/EntityModelFactoriesTest.php unit/_core/service/EntityTest.php unit/_core/di/EntityServiceFactoryTest.php unit/_core/di/ApplicationEntityServiceCreatorTest.php unit/_core/di/ApplicationSupportServiceRegistrarTest.php unit/_core/LegacyDiSourceInventoryTest.php` -> `OK (355 tests, 129980 assertions)`.
