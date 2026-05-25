# Отчет: остатки старого подхода и следующий рефакторинг

Дата анализа: 2026-05-31  
Ветка: `migrate_to-php8`  
Область поиска: `_core`, `_project`, `htdocs`, `cli`, без `vendor`

## Короткий вывод

Самые опасные service-locator входы из production-кода уже убраны: `containerService()`, `getContainerService()`, `blockService()`, `serviceFactory`, `resolveService()`, `::instance()`-fallback-и, `parse_ini_file()` и `legacy_global_functions` сейчас дают 0 совпадений в production PHP-коде.

Рефакторинг еще не завершен полностью. Главные остатки старого подхода теперь не в прямых вызовах service locator, не в service constructors и уже не в service-level DI static state, а в архитектурных швах: static bootstrap facade, тонкий compatibility `container_registry` и прямой доступ к окружению в boundary-классах. Lifecycle application container вынесен из registry static property в `fan\core\di\container_provider`; lifecycle для database connections и storage для database instances/current/default уже вынесены в отдельные DI-объекты `fan\core\service\database_connections` и `fan\core\service\database_pool`; `session` request-wide state вынесен в `fan\core\service\session_state`; `cookie` data/instances вынесены в `fan\core\service\cookie_state`; `json` instance cache вынесен в `fan\core\service\json_state`; `config` cache/engine/instance/app-dependent state вынесен в `fan\core\service\config_state`; `user` instances/current/priority/session state вынесен в `fan\core\service\user_state`; base-service listener bus вынесен из static property в `fan\core\service\service_listener_state`; ADOdb session globals изолированы в injectable `fan\core\service\session\adodb_environment`; installer error/output/sleep state вынесен из `global $isError` в injectable `install_context`; `bootstrap_runtime` получает bootstrap operations через DI из composition/bootstrap factories; `database`, `log`, `request`, `timer`, `json`, `cookie`, `file_system`, `curl`, `soap`, `rest`, `captcha`, `obfuscator`, `pager`, `email`, `date`, `entity`, `role`, `user`, `config`, `application`, `debug`, `cli`, `header`, `matcher`, `plain`, `error`, `template`, `translation`, `locale`, `tab`, `image_modify` и `form` теперь также получают base-service runtime/config/cache dependencies явно.

## Статический срез

| Метрика | Текущее значение | Что означает |
|---|---:|---|
| Файлов в анализируемой области | 509 | `rg --files _core _project htdocs cli --glob '!vendor/**'` |
| PHP-файлов в production-области | 415 | `find _core _project htdocs cli -name '*.php'` |
| Широкие legacy gateway patterns | 0 | Нет production-вызовов `containerService`, `getContainerService`, `blockService`, `serviceFactory`, `resolveService`, `container_registry::get()`, `::instance()`, `parse_ini_file`, `legacy_global_functions` |
| Legacy helper definitions | 0 | Wrapper-функции `ge/gr/se/le/role/msg/transfer_* / d / l` удалены |
| Legacy helper-like calls | 1 | Единственное совпадение - имя класса `transfer_int`, не вызов wrapper-функции |
| Production references to `container_registry` | 1 | Остался сам compatibility registry; explicit `createDefaultContainer()` API удален, service graph вынесен в `application_container_factory`, а container lifecycle хранится в `container_provider` |
| Internal registry static lookups | 0 | Registry helper methods переехали в `application_container_factory`, получают `container_interface` явно и больше не вызывают `self::get()->get(...)` |
| Database registry lifecycle helpers | 0 | `fixDatabaseInstances()` и `closeDatabaseInstances()` удалены; lifecycle вынесен в `database_connections` |
| Database static instance store | 0 | `database` больше не хранит `instances/current/default` в static properties; registry больше не читает эти поля через reflection |
| Raw superglobal matches | 10 | Реальные чтения только в `request_input` и аварийном fallback `exception/fatal.php`; остальные совпадения - комментарии/diagnostic label |
| `parent::__construct(...)` в `_core/service`/`_core/base` | 44 | Часть относится к transfer/model/data classes, часть - к service constructors |
| Минимальные service parent calls без явных base deps | 0 | Корректный grep больше не показывает service constructors без явных base deps |
| Service/base static-state references | 1 | `self::$` и static-field ссылки в `_core/service` и `_core/base/service.php`; остался lookup table в `database`, не DI-state |
| DI-relevant static-state fields | 0 / 0 файлов | Точный service-level DI-state хвост закрыт |

## Таблица остатков и рекомендаций

| Приоритет | Участок | Что осталось от старого подхода | Примеры файлов | Риск | Что рефакторить дальше |
|---:|---|---|---|---|---|
| 1 | Static composition root | Закрыто частично: `container_registry` больше не хранит сам container, больше не содержит service graph и больше не имеет explicit `createDefaultContainer()` API; graph вынесен в non-static `application_container_factory`, но static provider bridge еще остается | `_core/factory/application_container_factory.php`, `_core/di/container_registry.php`, `_core/di/container_provider.php`, `_core/bootstrap.php` | Средний: прямые callers убраны, но compatibility registry еще хранит provider | Дальше передавать provider/container через bootstrap runtime/context и постепенно убрать `container_registry::get()/set()/reset()` из тестовых/legacy paths |
| 1 | Internal registry self-lookups | Закрыто: registry helper methods больше не вызывают `self::get()->get(...)`; container передается явно в config/cache/entity/database/user helpers | `_core/di/container_registry.php`, `unit/_core/FunctionsServiceContainerTest.php` | Низкий: static registry еще остается composition root, но helper graph уже не зависит от internal global lookup | Следующий шаг - выделить non-static factory/provider class и перенести туда registration graph |
| 2 | Bootstrap/static composition root | Service graph собирается через non-static `application_container_factory`, а bootstrap operations передаются в `bootstrap_runtime` через DI; static facade еще остается entrypoint/context holder | `_core/factory/application_container_factory.php`, `_core/bootstrap.php`, `_core/factory/bootstrap/bootstrap_runtime_factory.php` | Средний: усложняет замену bootstrap lifetime и повторную инициализацию | Следующий слой - non-static application runner/context; static bridge оставить только для entrypoint |
| 3 | Base-service constructor dependencies | Закрыто: service constructors без явных base deps больше не находятся grep-проверкой | `_core/service/form.php`, `_core/service/image_modify.php`, `_core/service/tab.php` | Низкий: regression guard теперь фиксирует этот слой | Дальше фокусироваться не на constructor-tail, а на static state/lifetime и `container_registry` |
| 4 | Database service base dependencies | Закрыто: `database` получил `database_pool` и явные base-service dependencies | `_core/service/database.php`, `_core/di/container_registry.php`, `unit/_core/service/DatabaseTest.php` | Низкий: текущий constructor-probe покрывает runtime/config/cache проброс | Дальше использовать этот же pattern для остальных сервисов |
| 5 | Reflection-based project service creation | Registry создает project service classes через `Closure::bind(...) => new $className(...)` | `_core/di/container_registry.php` | Средний: protected constructors скрывают контракт зависимостей и усложняют статическую проверку | Постепенно заменить на явные factories per service; после этого можно раскрывать constructors или добавить интерфейсы factory-контрактов |
| 6 | Raw environment boundary | Прямое чтение `$_SERVER` осталось в request boundary и fatal fallback | `_core/service/request_input.php`, `_core/exception/fatal.php` | Низкий: основной request flow уже локализован, но fatal path все еще процедурный | Оставить `request_input` как допустимый boundary; для fatal path передавать request input/runtime logger из bootstrap error handler |
| 7 | Base-service listener bus | Закрыто: базовый сервис больше не хранит подписчиков событий в `private static array $listeners` | `_core/base/service.php`, `_core/service/service_listener_state.php`, `_core/service/bootstrap_runtime.php` | Низкий: состояние listener bus теперь DI-managed через runtime/container | Дальше удерживать regression guard `base service static listener bus` в source inventory |
| 8 | Bootstrap static state | Закрыто частично: bootstrap lifecycle/config/path/container/pid state вынесен в `fan\core\bootstrap\state`, container/request-input/bootstrap-runtime creation вынесены в injectable `fan\core\bootstrap\context`, `bootstrap_runtime` и `exception\base` больше не содержат прямых `\bootstrap::...` callbacks, а `bootstrap::setContext()` позволяет передать context извне | `_core/bootstrap.php`, `_core/bootstrap/state.php`, `_core/bootstrap/context.php`, `_core/service/bootstrap_runtime.php`, `_core/exception/base.php` | Низкий-средний: legacy entrypoint еще static, но state/dependency creation теперь изолированы и заменяемы | Следующий слой - убрать последний static context holder из runtime bootstrap path, оставив static `bootstrap` только compatibility facade |
| 9 | ADOdb session globals | Закрыто в engine: `session/adodb.php` больше не пишет `$ADODB_SESSION_*` напрямую; запись globals изолирована в injectable boundary | `_core/service/session/adodb.php`, `_core/service/session/adodb_environment.php`, `_core/service/session.php` | Низкий: legacy ADOdb все еще требует globals, но они теперь в одном container-managed adapter seam | Дальше можно добавить restore/snapshot, если понадобится запускать разные ADOdb session configs в одном process |
| 10 | Procedural installer state | Закрыто частично: `global $isError` удален, installer state/output/sleep идут через `install_context`; свободные функции оставлены как тонкий procedural entrypoint | `cli/install.php`, `unit/cli/InstallTest.php` | Низкий: вне основного runtime; error state уже явный, но функции еще глобальные | Дальше можно завернуть функции в `InstallCommand`, если потребуется полностью убрать procedural entrypoint |
| 11 | Source inventory coverage | Единый broad source-check добавлен и фиксирует запрещенные legacy DI-patterns | `unit/_core/LegacyDiSourceInventoryTest.php` | Низкий: regression guard теперь централизован, но allowlist пока не описывает будущие допустимые boundary cases | Расширять этот тест по мере снятия следующих legacy слоев |

## Детальная таблица DI-кандидатов

Эта таблица показывает последние закрытые service constructors и текущий хвост. Корректный grep по `parent::__construct()` без явного `bootstrap_runtime`, `config` и `cacheFactory` сейчас возвращает 0 совпадений.

| Приоритет | Файл | Старый след | Что сделано | Что рефакторить дальше |
|---:|---|---|---|---|
| 1 | `_core/service/user.php` | `parent::__construct()`, `static $instances/currentUsers/prioritySpace/currentUserSpace`, `__unserialize()` вручную дергал `_saveInstance()->_setConfig()` | Constructor получает base deps; instances/current/priority/session state вынесен в injected `user_state`; registry больше не читает user static state через reflection | Закрыто |
| 2 | `_core/service/config.php` | `parent::__construct()`, много static state: `instances`, `egines`, `cache`, `thisConf`, `appDepended` | Constructor получает base deps; cache/engine/instance/app-dependent state вынесен в injected `config_state`; registry больше не читает config `instances` через reflection | Закрыто |
| 3 | `_core/service/session.php` | `parent::__construct(empty(self::$instances))`, static `instances/engine/sr/byCookie/isExpired/bufferData` | Request-wide state вынесен в `session_state`; constructor получает state и base deps из registry | Дальше можно чистить engine-specific globals в `session/adodb.php` |
| 4 | `_core/service/cache.php` | `parent::__construct(empty(self::$instances))`, static `instances` | Instance state вынесен в `cache_state`; normal cache constructor получает state и base deps из registry | Закрыто |
| 5 | `_core/service/application.php` | `parent::__construct($allowIni)` без base deps | Constructor получает base deps из registry; behavior покрыт constructor probe | Закрыто |
| 6 | `_core/service/debug.php` | `parent::__construct($allowIni)` без base deps | Constructor получает base deps из registry; behavior покрыт constructor probe | Закрыто |
| 7 | `_core/service/cli.php` | `parent::__construct($allowIni)` без base deps | Constructor получает base deps из registry; behavior покрыт constructor probe | Закрыто |
| 8 | `_core/service/header.php` | `parent::__construct($allowIni)` без base deps | Constructor получает base deps из registry; behavior покрыт constructor probe | Закрыто |
| 9 | `_core/service/matcher.php` | `parent::__construct($allowIni)` без base deps | Constructor получает base deps из registry; behavior покрыт constructor probe | Закрыто |
| 10 | `_core/service/plain.php` | `parent::__construct($allowIni)` без base deps | Constructor получает base deps из registry; behavior покрыт constructor probe; plain config/header/controller deps уже explicit | Закрыто |
| 11 | `_core/service/error.php` | `parent::__construct($allowIni)` без base deps | Constructor получает base deps из registry; behavior покрыт constructor probe; input/runtime/log/email deps уже explicit | Закрыто |
| 12 | `_core/service/template.php` | `parent::__construct($allowIni)` без base deps | Constructor получает base deps из registry; behavior/source покрыт constructor probe; runtime/translation/tab/session deps уже explicit | Закрыто |
| 13 | `_core/service/translation.php` | `parent::__construct($allowIni)` без base deps | Constructor получает base deps из registry; behavior/source покрыт constructor probe; locale/runtime/tab/error/block/matcher/input deps уже explicit | Закрыто |
| 14 | `_core/service/locale.php` | `parent::__construct($allowIni)` без base deps | Constructor получает base deps из registry; behavior/source покрыт constructor probe; entity/tab/session/request/cookie/matcher deps уже explicit | Закрыто |
| 15 | `_core/service/tab.php` | `parent::__construct($allowIni)` без base deps | Constructor получает base deps из registry; behavior/source покрыт constructor probe; matcher/request/locale/session/input/factory deps уже explicit | Закрыто |
| 16 | `_core/service/image_modify.php` | `parent::__construct(empty(self::$instances))`, static `instances` | Instance state вынесен в `image_modify_state`; constructor получает state и base deps из registry; `image_draw` использует тот же state | Закрыто |
| 17 | `_core/service/form.php` | `parent::__construct(empty(self::$instances))`, static `instances` | Instance state вынесен в `form_state`; constructor получает state и base deps из registry; registry больше не читает form instances через reflection | Закрыто |
| 18 | Остальные constructor candidates | `parent::__construct($allowIni)` / `empty(self::$instances)` без base deps | Не найдено | Дальше чистить static state и composition root |

## Текущий хвост конструкторов

| Приоритет | Файл | Строка | Что осталось | Следующее действие |
|---:|---|---:|---|---|
| Нет | - | Grep не находит оставшихся service constructors без явных base deps | Перейти к static state/lifetime cleanup |

## Static-state hotspots

| Приоритет | Файл | Static state | Почему это старый подход | Рекомендация |
|---:|---|---|---|---|
| 1 | `_core/service/user.php` | Закрыто: `instances`, `currentUsers`, `prioritySpace`, `currentUserSpace` вынесены в `user_state` | User больше не хранит DI-state в static properties | Дальше переходить к composition root/bootstrap state |
| 2 | `_core/service/config.php` | Закрыто: `instances`, `egines`, `cache`, `thisConf`, `appDepended` вынесены в `config_state` | Config больше не хранит DI-state в static properties | Закрыто |
| 3 | Per-key service maps | Закрыто для `rest`, `curl`, `email`, `file_system`; остатки в этом классе долгов теперь ушли в state/factory слой | `_core/service/rest_state.php`, `_core/service/curl_state.php`, `_core/service/email_state.php`, `_core/service/file_system_state.php` | Низкий: regression guard фиксирует отсутствие прежних static fields в сервисах | Закрыто |
| 4 | Date instance/global state | Закрыто: global config и date instances вынесены из `date` static properties в `date_state` | `_core/service/date.php`, `_core/service/date_state.php` | Низкий: service больше не хранит state в static properties | Закрыто |
| 5 | Specialized static buckets | Закрыто для `template/type/form`, `cache/memcache`, `database/adodb`, `tab` | `_core/service/template_form_state.php`, `_core/service/cache_memcache_state.php`, `_core/service/tab_state.php` | Низкий: специализированные buckets вынесены в injected state/callback контекст | Дальше перейти к крупным `config` и `user` |
| 6 | Base-service listener bus | Закрыто: `private static array $listeners` удален из базового сервиса | `_core/service/service_listener_state.php`, `_core/service/bootstrap_runtime.php` | Низкий: listener state теперь живет в runtime/container lifecycle | Закрыто |
| 7 | ADOdb session globals | Закрыто в `session/adodb.php`: прямые `$ADODB_SESSION_*` записи вынесены в injectable `adodb_environment` | `_core/service/session/adodb_environment.php`, `_core/service/session/adodb.php` | Низкий: globals остались только в adapter boundary, не в engine constructor | Закрыто на уровне engine dependency |
| 8 | Installer error state | Закрыто: `global $isError` заменен на `install_context`, который инжектит writer/sleeper и хранит error flag | `cli/install.php` | Низкий: CLI helper больше не держит error state в global переменной | Закрыто на уровне state dependency |

## Точный хвост static state

| Приоритет | Файл | Static поля | Что делать |
|---:|---|---|---|
| - | `_core/service/user.php` | `instances`, `currentUsers`, `prioritySpace`, `currentUserSpace` | Закрыто: состояние вынесено в injected `user_state` |
| - | `_core/service/config.php` | `instances`, `egines`, `cache`, `thisConf`, `appDepended` | Закрыто: состояние вынесено в injected `config_state` |
| - | `_core/service/tab.php` | `errTransfer` | Закрыто: error-transfer stack вынесен в injected `tab_state` |
| - | `_core/base/service.php` | `listeners` | Закрыто: listener bus вынесен в injected `service_listener_state` через `bootstrap_runtime` |

## Что уже закрыто

| Старый механизм | Текущий статус |
|---|---|
| `containerService()` | 0 production occurrences |
| `getContainerService()` | 0 production occurrences; public gateway удален из base service |
| `blockService()` | 0 production occurrences; block dependencies идут через named factories/accessors |
| Generic `serviceFactory` | 0 production occurrences; runner/translation используют explicit handler/tag factories |
| `resolveService()` / service-container setters in base service | 0 production occurrences; base service больше не является service-locator bridge |
| Static `::instance()` production fallback | 0 production occurrences |
| INI loader fallback | `parse_ini_file()` и `_core/service/config/ini.php` удалены из production path |
| Global helper service bridge | `_core/service/legacy_global_functions.php` и wrapper-функции удалены |
| Bootstrap `container_registry::get()` usage | 0 production occurrences; bootstrap держит собственный container через `_getContainer()` |
| Container registry lifecycle | `container_registry` больше не хранит `container_interface` напрямую; lazy lifecycle/default container теперь делегирован injected `container_provider` |
| Bootstrap runtime static facade dependency | `_core/service/bootstrap_runtime.php` больше не содержит прямых `\bootstrap::...` callbacks; они живут в composition/bootstrap factories и передаются через `bootstrapOperations` |
| Exception base static facade dependency | `_core/exception/base.php` больше не содержит прямого `\bootstrap::logError()` fallback; основной путь остается injected runtime logger |
| Database lifecycle static helpers in registry | `fixDatabaseInstances()` и `closeDatabaseInstances()` удалены; `database_connections` стал отдельным DI-сервисом |
| Database static instance storage | `instances/current/default` вынесены из `database` в `database_pool`; registry больше не читает эти поля через reflection |
| Database base-service dependencies | `database` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct()` без deps удален |
| Log base-service dependencies | `log` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct()` без deps удален |
| Request base-service dependencies | `request` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct()` без deps удален |
| Timer base-service dependencies | `timer` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct()` без deps удален |
| Json base-service dependencies | `json` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct(true)` без deps удален |
| Cookie base-service dependencies | `cookie` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct(false)` без deps удален |
| File-system base-service dependencies | `file_system` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct(false)` без deps удален |
| File-system instance state | `file_system` больше не хранит `instances` в static property; per-path instances вынесены в injected `file_system_state` registry dependency |
| Curl base-service dependencies | `curl` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct(true)` без deps удален |
| Curl instance state | `curl` больше не хранит `instances` в static property; per-index/per-url instances вынесены в injected `curl_state`, а `close()` очищает этот state |
| SOAP base-service dependencies | `soap` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct(false)` без deps удален |
| REST base-service dependencies | `rest` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct(false)` без deps удален |
| REST instance/default state | `rest` больше не хранит `instances` и `defaultName` в static properties; per-connection instances и default-name cache вынесены в injected `rest_state` registry dependency |
| Captcha base-service dependencies | `captcha` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct(true)` без deps удален |
| Captcha instance state | `captcha` больше не хранит `instances` в static property; per-form instances вынесены в injected `captcha_state` registry dependency |
| Obfuscator base-service dependencies | `obfuscator` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct(true)` без deps удален |
| Obfuscator instance state | `obfuscator` больше не хранит `instances` в static property; per-type instances вынесены в injected `obfuscator_state` registry dependency |
| Pager base-service dependencies | `pager` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct(true)` без deps удален |
| Email base-service dependencies | `email` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct(true)` без deps удален |
| Email instance state | `email` больше не хранит `instances` в static property; per-name instances вынесены в injected `email_state` registry dependency |
| Date base-service dependencies | `date` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct()` без deps удален |
| Date global/instance state | `date` больше не хранит `globalConfig` и `instances` в static properties; global config и per-date instances вынесены в injected `date_state`, а `modify()` переиспользует этот state |
| Template form render-number state | `template/type/form` больше не хранит `formNumber` в static property; счетчик номеров форм вынесен в injected `template_form_state`, который передается из `template` только form-template классам |
| Memcache keeper state | `cache/memcache` больше не хранит `keepers` в static property; keeper pool вынесен в injected `cache_memcache_state`, который прокидывается из `cache` только memcache engine |
| ADOdb error callback state | `database/adodb` больше не хранит `errorService` в static property; injected error service привязывается к connection через ADOdb `raiseErrorFn` callable |
| Tab error-transfer state | `tab` больше не хранит `errTransfer` в static property; error-transfer stack вынесен в injected `tab_state` |
| Entity base-service dependencies | `entity` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct()` без deps удален |
| Entity instance state | `entity` больше не хранит `instances` в static property; per-collection instances вынесены в injected `entity_state` registry dependency |
| Role base-service dependencies | `role` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct()` без deps удален |
| User base-service dependencies | `user` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; восстановление из session rehydrate-ится через injected deps |
| User state | `user` больше не хранит `instances`, `currentUsers`, `prioritySpace` и `currentUserSpace` в static properties; user instances/current/priority/session bridge вынесены в injected `user_state`, а registry больше не читает user static state через reflection |
| Config base-service dependencies | `config` constructor получает `bootstrap_runtime` и `cacheFactory`; как configurator по умолчанию передает в base service сам себя |
| Config state | `config` больше не хранит `instances`, `egines`, `cache`, `thisConf` и `appDepended` в static properties; config instances/cache/engines/application-dependent files вынесены в injected `config_state`, а registry больше не читает config static state через reflection |
| Application base-service dependencies | `application` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct($allowIni)` без deps удален |
| Debug base-service dependencies | `debug` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct($allowIni)` без deps удален |
| CLI base-service dependencies | `cli` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct($allowIni)` без deps удален |
| Header base-service dependencies | `header` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct($allowIni)` без deps удален |
| Matcher base-service dependencies | `matcher` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct($allowIni)` без deps удален |
| Plain base-service dependencies | `plain` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct($allowIni)` без deps удален |
| Error base-service dependencies | `error` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct($allowIni)` без deps удален |
| Template base-service dependencies | `template` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct($allowIni)` без deps удален |
| Translation base-service dependencies | `translation` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct($allowIni)` без deps удален |
| Locale base-service dependencies | `locale` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct($allowIni)` без deps удален |
| Tab base-service dependencies | `tab` constructor получает `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct($allowIni)` без deps удален |
| Image modify instance state | `image_modify` больше не хранит `instances` в static property; instances для `image_modify`/`image_draw` вынесены в injected `image_modify_state` |
| Image modify base-service dependencies | `image_modify` constructor получает `image_modify_state`, `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct(empty(self::$instances))` удален |
| Form instance state | `form` больше не хранит `instances` в static property; instances вынесены в injected `form_state` |
| Form base-service dependencies | `form` constructor получает `form_state`, `bootstrap_runtime`, `config` и `cacheFactory`; `parent::__construct(empty(self::$instances))` удален |
| Base-service listener bus | `_core/base/service.php` больше не хранит `private static array $listeners`; подписчики событий вынесены в `service_listener_state`, который регистрируется в container и прокидывается через `bootstrap_runtime` |
| ADOdb session environment | `_core/service/session/adodb.php` больше не пишет `$ADODB_SESSION_*` напрямую; legacy globals вынесены в `session/adodb_environment.php`, registered as `adodb_session_environment` |
| Installer global error state | `cli/install.php` больше не использует `global $isError`; error/output/sleep state вынесен в `install_context`, `runInstall()` принимает context явно |
| Registry helper static lookups | Все registry helper methods, включая config/cache/entity/database/user helpers, получают `container_interface $container` явно и больше не вызывают `self::get()->get(...)` внутри метода |
| Cookie state | `cookie` больше не хранит `instances` и request cookie `data` в static properties; оба состояния вынесены в injected `cookie_state` |
| Json instance state | `json` больше не хранит `instances` в static property; instances по base64-mode вынесены в injected `json_state` registry dependency |
| Pager instance state | `pager` больше не хранит `instances` в static property; per-block instances вынесены в injected `pager_state` registry dependency |
| Session request-wide state | `session` больше не хранит `instances`, `engine`, `sr`, `byCookie`, `isExpired`, `bufferData` в static properties; это состояние вынесено в injected `session_state` |
| Cache instance state | `cache` больше не хранит instances в static property; instances вынесены в injected `cache_state` |
| Broad legacy DI source inventory | Добавлен `unit/_core/LegacyDiSourceInventoryTest.php` |

## Рекомендуемый порядок работ

| Шаг | Рефакторинг | Минимальная проверка |
|---:|---|---|
| 1 | Вынести registration graph из `container_registry` в application/container factory object | Bootstrap tests + integration smoke + full PHPUnit |
| 2 | Убрать последний static context holder из runtime bootstrap path | Bootstrap/runner tests + source inventory + full PHPUnit |
| 3 | Изолировать оставшиеся boundary globals: fatal fallback `$_SERVER`, optional restore/snapshot для ADOdb session globals | Boundary adapter tests + source inventory |
| 4 | Разобрать оставшиеся harmless static helpers по риску (lookup tables, view loader caches) | Focused behavior tests + full PHPUnit |

## Контрольные команды

| Цель | Команда |
|---|---|
| Широкий legacy gateway audit | `rg -n "container_registry::get\\(|::instance\\(|resolveService\\(|parse_ini_file|containerService\\(|getContainerService\\(|blockService\\(|serviceFactory|legacyService\\(|setServiceContainer\\(|getServiceContainer\\(|legacy_global_functions|fixDatabaseInstances|closeDatabaseInstances" _core _project htdocs cli --glob '*.php'` |
| Global helper definitions | `rg -n "function\\s+(?:ge|gr|se|le|role|msg|transfer_out|transfer_int|transfer_sham|d|l)\\s*\\(" _core _project htdocs cli --glob '*.php'` |
| Helper-like calls in templates/PHP | `rg -n "\\b(?:ge|gr|role|msg|transfer_out|transfer_int|transfer_sham|d|l|getUser|dms|dma|dateL2M|dateM2L|msgAlt)\\s*\\(" _core _project htdocs cli --glob '!vendor/**'` |
| Raw environment access | `rg -n '\\$_(GET|POST|REQUEST|SERVER|SESSION|COOKIE|FILES)' _core _project htdocs cli --glob '*.php'` |
| Constructor DI candidates | `rg -n "parent::__construct\\((?:\\)|true\\)|false\\)|\\$allowIni\\)|empty\\(self::\\$instances\\)\\))" _core/service _core/base --glob '*.php'` |
| Database static storage audit | `rg -n "private static array \\\\$instances|private static \\?object \\\\$currentInstance|private static \\?object \\\\$defaultInstance|self::\\$instances|self::\\$currentInstance|self::\\$defaultInstance" _core/service/database.php` |
| Source inventory regression | `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/LegacyDiSourceInventoryTest.php` |
| Полный регресс | `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml` |
| Чистота diff | `git diff --check` |

## Последняя валидация

| Проверка | Результат |
|---|---|
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/FunctionsServiceContainerTest.php unit/_core/service/ImageModifyTest.php unit/_core/service/ImageDrawTest.php unit/_core/service/DateTest.php unit/_core/service/EmailTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 64 tests / 6838 assertions; проверяет явный container argument в третьем registry helper-кластере |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml` | OK, 1796 tests / 12608 assertions |
| `git diff --check` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/FunctionsServiceContainerTest.php unit/_core/service/RestTest.php unit/_core/service/CookieTest.php unit/_core/service/PagerTest.php unit/_core/service/FormTest.php unit/_core/service/CaptchaTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 88 tests / 6900 assertions; проверяет явный container argument во втором registry helper-кластере |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml` | OK, 1796 tests / 12599 assertions |
| `git diff --check` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/FunctionsServiceContainerTest.php unit/_core/service/JsonTest.php unit/_core/service/CurlTest.php unit/_core/service/ObfuscatorTest.php unit/_core/service/SessionTest.php unit/_core/service/SoapTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 74 tests / 6863 assertions; проверяет явный container argument в selected registry helpers |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml` | OK, 1796 tests / 12584 assertions |
| `git diff --check` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/cli/InstallTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 20 tests / 6625 assertions; проверяет `install_context` вместо `global $isError` |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml` | OK, 1795 tests / 12568 assertions |
| `git diff --check` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/session/AdodbEnvironmentTest.php unit/_core/service/session/AdodbTest.php unit/_core/service/SessionTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/CoreSourceInventoryTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 44 tests / 6800 assertions; проверяет injectable ADOdb session environment |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml` | OK, 1790 tests / 12138 assertions |
| `git diff --check` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/ServiceListenerStateTest.php unit/_core/base/ServiceTest.php unit/_core/service/BootstrapRuntimeTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/CoreSourceInventoryTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 55 tests / 6809 assertions; проверяет вынос base-service listener bus в DI-managed state |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml` | OK, 1784 tests / 12094 assertions |
| `rg -n 'parent::__construct\\((?:\\)|true\\)|false\\)|\\$allowIni\\)|empty\\(self::\\$instances\\)\\))' _core/service _core/base --glob '*.php'` | OK, 0 matches |
| `rg -n "container_registry::get\\(|::instance\\(|resolveService\\(|parse_ini_file|containerService\\(|getContainerService\\(|blockService\\(|serviceFactory|legacyService\\(|setServiceContainer\\(|getServiceContainer\\(|legacy_global_functions|fixDatabaseInstances|closeDatabaseInstances" _core _project htdocs cli --glob '*.php'` | OK, 0 matches |
| `git diff --check` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/LegacyDiSourceInventoryTest.php` | OK, 14 tests / 5754 assertions; свежая проверка после обновления отчета |
| `git diff --check` | OK; свежая проверка после обновления отчета |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/DatabaseConnectionsTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/service/TransferTest.php unit/_core/exception/BaseTest.php unit/_core/exception/Error500Test.php unit/_core/exception/service/FatalTest.php unit/_core/exception/block/FatalTest.php unit/_core/exception/model/ReverseTest.php` | OK, 29 tests / 132 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/LegacyDiSourceInventoryTest.php unit/_core/service/DatabaseConnectionsTest.php unit/_core/FunctionsServiceContainerTest.php` | OK, 26 tests / 5500 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/DatabasePoolTest.php unit/_core/service/DatabaseConnectionsTest.php unit/_core/service/DatabaseTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 47 tests / 5579 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/DatabaseTest.php unit/_core/service/DatabasePoolTest.php unit/_core/service/DatabaseConnectionsTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 48 tests / 5588 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/LogTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 30 tests / 5537 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/RequestTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 33 tests / 5537 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/TimerTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 29 tests / 5535 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/JsonTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 29 tests / 5533 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/CookieTest.php` | OK, 10 tests / 27 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/CookieTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 28 tests / 5522 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/FileSystemTest.php` | OK, 10 tests / 34 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/CookieTest.php unit/_core/service/FileSystemTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 38 tests / 5556 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/CurlTest.php` | OK, 12 tests / 39 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/CookieTest.php unit/_core/service/FileSystemTest.php unit/_core/service/CurlTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 50 tests / 5595 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/SoapTest.php` | OK, 11 tests / 38 assertions |
| `rg -n "self::get\\(\\)->get\\(" _core/di/container_registry.php` | OK, 0 matches after passing container explicitly through registry helpers |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/FunctionsServiceContainerTest.php unit/_core/service/ConfigTest.php unit/_core/service/CacheTest.php unit/_core/service/EntityTest.php unit/_core/service/DatabaseTest.php unit/_core/service/UserTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 83 tests / 6914 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml` | OK, 1806 tests / 12749 assertions |
| `git diff --check -- _core/di/container_registry.php _core/factory/application_container_factory.php unit/_core/di/ContainerProviderTest.php unit/_core/FunctionsServiceContainerTest.php doc/legacy-di-refactor-report.md doc/remaining-legacy-refactor-table.md` | OK |
| `rg -n -- "->factory\\(" _core/di/container_registry.php` | OK, 0 matches; service graph/factory helpers are in `application_container_factory` |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/di/ContainerProviderTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 27 tests / 6762 assertions |
| `rg -n "private static" _core/bootstrap.php` | OK, only `private static ?\fan\core\bootstrap\context $context = null` and `private static function context()` remain |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/bootstrap unit/_core/service/BootstrapRuntimeTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 47 tests / 6768 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/CookieTest.php unit/_core/service/FileSystemTest.php unit/_core/service/CurlTest.php unit/_core/service/SoapTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 61 tests / 5633 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/RestTest.php` | OK, 12 tests / 41 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/CookieTest.php unit/_core/service/FileSystemTest.php unit/_core/service/CurlTest.php unit/_core/service/SoapTest.php unit/_core/service/RestTest.php unit/_core/FunctionsServiceContainerTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 73 tests / 5674 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/CaptchaTest.php` | OK, 12 tests / 37 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/ObfuscatorTest.php` | OK, 8 tests / 28 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/LegacyDiSourceInventoryTest.php unit/_core/service/ObfuscatorTest.php` | OK, 22 tests / 5488 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/PagerTest.php` | OK, 17 tests / 54 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/LegacyDiSourceInventoryTest.php unit/_core/service/PagerTest.php` | OK, 31 tests / 5514 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/EmailTest.php` | OK, 13 tests / 50 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/LegacyDiSourceInventoryTest.php unit/_core/service/EmailTest.php unit/_core/service/email/PhpmailerTest.php` | OK, 37 tests / 5550 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/DateTest.php` | OK, 12 tests / 38 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/LegacyDiSourceInventoryTest.php unit/_core/service/DateTest.php` | OK, 26 tests / 5498 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/EntityTest.php` | OK, 7 tests / 27 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/LegacyDiSourceInventoryTest.php unit/_core/service/EntityTest.php unit/_core/service/entity/DescriptionTest.php unit/_core/base/model/EntityTest.php` | OK, 38 tests / 5541 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/UserTest.php` | OK, 15 tests / 53 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/LegacyDiSourceInventoryTest.php unit/_core/service/UserTest.php` | OK, 29 tests / 5513 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/ConfigTest.php` | OK, 14 tests / 44 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/LegacyDiSourceInventoryTest.php unit/_core/service/ConfigTest.php unit/_core/service/UserTest.php` | OK, 43 tests / 5557 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/SessionTest.php unit/_core/service/SessionStateTest.php unit/_core/service/session/InbuiltTest.php unit/_core/service/session/AdodbTest.php unit/_core/service/session/PearTest.php` | OK, 39 tests / 134 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/FunctionsServiceContainerTest.php unit/_core/service/SessionTest.php unit/_core/service/SessionStateTest.php` | OK, 23 tests / 115 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/CacheTest.php unit/_core/service/CacheStateTest.php unit/_core/service/cache/BaseTest.php unit/_core/service/cache/FileTest.php unit/_core/service/cache/wrapper/FileDataTest.php` | OK, 48 tests / 164 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/FunctionsServiceContainerTest.php unit/_core/service/CacheTest.php unit/_core/service/CacheStateTest.php` | OK, 21 tests / 94 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/ApplicationTest.php` | OK, 11 tests / 29 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/DebugTest.php` | OK, 13 tests / 38 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/CliTest.php` | OK, 11 tests / 28 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/ApplicationTest.php unit/_core/service/DebugTest.php unit/_core/service/CliTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 49 tests / 5583 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/HeaderTest.php` | OK, 10 tests / 35 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/MatcherTest.php` | OK, 9 tests / 32 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/HeaderTest.php unit/_core/service/MatcherTest.php unit/_core/LegacyDiSourceInventoryTest.php` | OK, 33 tests / 5555 assertions |
| `/opt/homebrew/bin/php -l _core/service/plain.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/PlainTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/PlainTest.php` | OK, 13 tests / 41 assertions |
| `/opt/homebrew/bin/php -l _core/service/error.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/ErrorTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/ErrorTest.php` | OK, 9 tests / 30 assertions |
| `/opt/homebrew/bin/php -l _core/service/template.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/TemplateTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/TemplateTest.php` | OK, 8 tests / 31 assertions |
| `/opt/homebrew/bin/php -l _core/service/translation.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/TranslationTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/TranslationTest.php` | OK, 15 tests / 42 assertions |
| `/opt/homebrew/bin/php -l _core/service/locale.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/LocaleTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/LocaleTest.php` | OK, 14 tests / 51 assertions |
| `/opt/homebrew/bin/php -l _core/service/tab_state.php && /opt/homebrew/bin/php -l _core/service/tab.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/TabStateTest.php && /opt/homebrew/bin/php -l unit/_core/service/TabTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/TabTest.php unit/_core/service/TabStateTest.php` | OK, 25 tests / 89 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/TabTest.php` | OK, 18 tests / 63 assertions |
| `/opt/homebrew/bin/php -l _core/service/image_modify.php && /opt/homebrew/bin/php -l _core/service/image_modify_state.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/ImageModifyTest.php && /opt/homebrew/bin/php -l unit/_core/service/ImageModifyStateTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/ImageModifyTest.php unit/_core/service/ImageModifyStateTest.php` | OK, 17 tests / 58 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/ImageDrawTest.php unit/_core/service/ImageModifyTest.php unit/_core/service/ImageModifyStateTest.php` | OK, 24 tests / 77 assertions |
| `/opt/homebrew/bin/php -l _core/service/form.php && /opt/homebrew/bin/php -l _core/service/form_state.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/FormTest.php && /opt/homebrew/bin/php -l unit/_core/service/FormStateTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/FormTest.php unit/_core/service/FormStateTest.php` | OK, 22 tests / 70 assertions |
| `/opt/homebrew/bin/php -l _core/service/cookie.php && /opt/homebrew/bin/php -l _core/service/cookie_state.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/CookieTest.php && /opt/homebrew/bin/php -l unit/_core/service/CookieStateTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/CookieTest.php unit/_core/service/CookieStateTest.php` | OK, 17 tests / 54 assertions |
| `/opt/homebrew/bin/php -l _core/service/json.php && /opt/homebrew/bin/php -l _core/service/json_state.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/JsonTest.php && /opt/homebrew/bin/php -l unit/_core/service/JsonStateTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/JsonTest.php unit/_core/service/JsonStateTest.php` | OK, 17 tests / 57 assertions |
| `/opt/homebrew/bin/php -l _core/service/pager.php && /opt/homebrew/bin/php -l _core/service/pager_state.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/PagerTest.php && /opt/homebrew/bin/php -l unit/_core/service/PagerStateTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/PagerTest.php unit/_core/service/PagerStateTest.php` | OK, 23 tests / 73 assertions |
| `/opt/homebrew/bin/php -l _core/service/captcha.php && /opt/homebrew/bin/php -l _core/service/captcha_state.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/CaptchaTest.php && /opt/homebrew/bin/php -l unit/_core/service/CaptchaStateTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/CaptchaTest.php unit/_core/service/CaptchaStateTest.php` | OK, 18 tests / 56 assertions |
| `/opt/homebrew/bin/php -l _core/service/obfuscator.php && /opt/homebrew/bin/php -l _core/service/obfuscator_state.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/ObfuscatorTest.php && /opt/homebrew/bin/php -l unit/_core/service/ObfuscatorStateTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/ObfuscatorTest.php unit/_core/service/ObfuscatorStateTest.php` | OK, 14 tests / 47 assertions |
| `/opt/homebrew/bin/php -l _core/service/file_system.php && /opt/homebrew/bin/php -l _core/service/file_system_state.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/FileSystemTest.php && /opt/homebrew/bin/php -l unit/_core/service/FileSystemStateTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/FileSystemTest.php unit/_core/service/FileSystemStateTest.php` | OK, 16 tests / 53 assertions |
| `/opt/homebrew/bin/php -l _core/service/email.php && /opt/homebrew/bin/php -l _core/service/email_state.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/EmailTest.php && /opt/homebrew/bin/php -l unit/_core/service/EmailStateTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/EmailTest.php unit/_core/service/EmailStateTest.php unit/_core/service/email/PhpmailerTest.php` | OK, 29 tests / 109 assertions |
| `/opt/homebrew/bin/php -l _core/service/entity.php && /opt/homebrew/bin/php -l _core/service/entity_state.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/EntityTest.php && /opt/homebrew/bin/php -l unit/_core/service/EntityStateTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/EntityTest.php unit/_core/service/EntityStateTest.php unit/_core/service/entity/DescriptionTest.php unit/_core/base/model/EntityTest.php` | OK, 31 tests / 102 assertions |
| `/opt/homebrew/bin/php -l _core/service/curl.php && /opt/homebrew/bin/php -l _core/service/curl_state.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/CurlTest.php && /opt/homebrew/bin/php -l unit/_core/service/CurlStateTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/CurlTest.php unit/_core/service/CurlStateTest.php` | OK, 19 tests / 62 assertions |
| `/opt/homebrew/bin/php -l _core/service/rest.php && /opt/homebrew/bin/php -l _core/service/rest_state.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/RestTest.php && /opt/homebrew/bin/php -l unit/_core/service/RestStateTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/RestTest.php unit/_core/service/RestStateTest.php` | OK, 19 tests / 67 assertions |
| `/opt/homebrew/bin/php -l _core/service/date.php && /opt/homebrew/bin/php -l _core/service/date_state.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/DateTest.php && /opt/homebrew/bin/php -l unit/_core/service/DateStateTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/DateTest.php unit/_core/service/DateStateTest.php` | OK, 19 tests / 62 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/CoreSourceInventoryTest.php` | OK, 3 tests / 446 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/LegacyDiSourceInventoryTest.php` | OK, 14 tests / 5460 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/LegacyDiSourceInventoryTest.php unit/_core/CoreSourceInventoryTest.php` | OK, 17 tests / 6228 assertions |
| `rg -n "private static array \\$instances|private static \\?object \\$engine|protected static \\?object \\$sr|private static \\?bool \\$byCookie|private static bool \\$isExpired|private static array \\$bufferData|self::\\$instances|self::\\$engine|self::\\$sr|self::\\$byCookie|self::\\$isExpired|self::\\$bufferData" _core/service/session.php` | OK, 0 matches |
| `rg -n "private static array \\$instances|self::\\$instances|parent::__construct\\(empty\\(self::\\$instances\\)\\)" _core/service/cache.php` | OK, 0 matches |
| `rg -n 'parent::__construct\\((?:\\)|true\\)|false\\)|\\$allowIni\\)|empty\\(self::\\$instances\\)\\))' _core/service _core/base --glob '*.php'` | OK, 0 matches |
| `rg -n "container_registry::get\\(|::instance\\(|resolveService\\(|parse_ini_file|containerService\\(|getContainerService\\(|blockService\\(|serviceFactory|legacyService\\(|setServiceContainer\\(|getServiceContainer\\(|legacy_global_functions|fixDatabaseInstances|closeDatabaseInstances" _core _project htdocs cli --glob '*.php'` | OK, 0 matches |
| `/opt/homebrew/bin/php -l _core/service/user.php && /opt/homebrew/bin/php -l _core/service/config.php && /opt/homebrew/bin/php -l _core/di/container_registry.php` | OK |
| `/opt/homebrew/bin/php -l unit/_core/service/UserTest.php && /opt/homebrew/bin/php -l unit/_core/service/ConfigTest.php` | OK |
| `/opt/homebrew/bin/php -l _core/service/user_state.php && /opt/homebrew/bin/php -l _core/service/user.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/UserStateTest.php && /opt/homebrew/bin/php -l unit/_core/service/UserTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/UserTest.php unit/_core/service/UserStateTest.php` | OK, 23 tests / 82 assertions |
| `/opt/homebrew/bin/php -l _core/di/container_provider.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/di/ContainerProviderTest.php && /opt/homebrew/bin/php -l unit/_core/FunctionsServiceContainerTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/di/ContainerTest.php unit/_core/di/ContainerProviderTest.php unit/_core/FunctionsServiceContainerTest.php` | OK, 13 tests / 49 assertions |
| `/opt/homebrew/bin/php -l _core/service/cache.php && /opt/homebrew/bin/php -l _core/service/cache_state.php && /opt/homebrew/bin/php -l _core/service/session.php && /opt/homebrew/bin/php -l _core/service/session_state.php && /opt/homebrew/bin/php -l _core/di/container_registry.php` | OK |
| `/opt/homebrew/bin/php -l unit/_core/service/CacheTest.php && /opt/homebrew/bin/php -l unit/_core/service/CacheStateTest.php && /opt/homebrew/bin/php -l unit/_core/service/SessionTest.php && /opt/homebrew/bin/php -l unit/_core/service/SessionStateTest.php` | OK |
| `/opt/homebrew/bin/php -l _core/service/template_form_state.php && /opt/homebrew/bin/php -l _core/service/template/type/form.php && /opt/homebrew/bin/php -l _core/service/template.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/TemplateFormStateTest.php && /opt/homebrew/bin/php -l unit/_core/service/TemplateTest.php && /opt/homebrew/bin/php -l unit/_core/service/template/type/FormTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/TemplateTest.php unit/_core/service/TemplateFormStateTest.php unit/_core/service/template/type/FormTest.php` | OK, 27 tests / 82 assertions |
| `/opt/homebrew/bin/php -l _core/service/cache_memcache_state.php && /opt/homebrew/bin/php -l _core/service/cache/memcache.php && /opt/homebrew/bin/php -l _core/service/cache.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/CacheMemcacheStateTest.php && /opt/homebrew/bin/php -l unit/_core/service/cache/MemcacheTest.php && /opt/homebrew/bin/php -l unit/_core/service/CacheTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/CacheTest.php unit/_core/service/CacheStateTest.php unit/_core/service/CacheMemcacheStateTest.php unit/_core/service/cache/MemcacheTest.php unit/_core/service/cache/BaseTest.php unit/_core/service/cache/FileTest.php unit/_core/service/cache/MemcachedTest.php unit/_core/service/cache/wrapper/FileDataTest.php` | OK, 70 tests / 232 assertions |
| `/opt/homebrew/bin/php -l _core/service/database/adodb.php && /opt/homebrew/bin/php -l unit/_core/service/database/AdodbTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/database/AdodbTest.php` | OK, 12 tests / 35 assertions |
| `/opt/homebrew/bin/php -l _core/service/config_state.php && /opt/homebrew/bin/php -l _core/service/config.php && /opt/homebrew/bin/php -l _core/di/container_registry.php && /opt/homebrew/bin/php -l unit/_core/service/ConfigStateTest.php && /opt/homebrew/bin/php -l unit/_core/service/ConfigTest.php && /opt/homebrew/bin/php -l unit/_core/CoreSourceInventoryTest.php` | OK |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml unit/_core/service/ConfigTest.php unit/_core/service/ConfigStateTest.php` | OK, 21 tests / 71 assertions |
| `/opt/homebrew/bin/php vendor/bin/phpunit --configuration phpunit.xml` | OK, 1775 tests / 11645 assertions |
| `git diff --check` | OK |
