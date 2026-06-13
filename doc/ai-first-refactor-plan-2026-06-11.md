# AI-first refactor plan for PHP-FAN

Дата анализа: 2026-06-11

Дата обновления: 2026-06-13, после P334-P338 completion-hardening audit; actionable AI-first refactor queues закрыты, P339-P343 предложены как post-completion maintenance route.

Ветка: `migrate_to-php8`

Цель: заточить `php-fan-5` под быструю, безопасную и качественную работу AI-агентов без переписывания framework одним рискованным проходом.

## Короткий вывод

Проект уже прошел большую часть тяжелой PHP 8 / DI-модернизации. Это не хаотичный legacy baseline: есть `strict_types`, PSR-4 autoload, явный `container`, dependency registrars/factories, большое покрытие unit-тестами и source-inventory guards.

Первый AI-first слой уже добавлен и расширен до рабочего P1-P338 batch. Framework теперь имеет базовый self-describing contract для агента:

- `.ai/` documentation contract;
- `tools/ai_map.php --json`;
- `tools/ai_verify.php`;
- `tools/ai_explain.php`;
- `tools/ai_static_check.php`;
- `php fan ai:*` bridge;
- `fan\core\di\service_id` для начала типизации service ids;
- `services.descriptors` в AI map;
- `.ai/meta.schema.json` и metadata/template map;
- `.ai/map.schema.json`, schema subset validation и `php fan ai:map --validate`;
- реальные `phpstan`/`php-cs-fixer` dev tools с baseline gate;
- hygiene guard против tracked dependency/runtime noise.
- model/entity collaborators для lookup/designer/description/ns-prefix/collection-key/sql-dir/row dependencies/related rows, сгруппированные в `entity_dependencies`;
- raw service-id regression guard для `core/di`;
- `services.descriptors.factory_origin`, `factory_arguments`, `creator_method_arguments`, `aliases` с line provenance, `referenced_by`, `class`, `factory`, `config_key`, `lifetime_reason`, `source_edges` и точные `source_locations` для class/config/factory-parameter/runtime/dependency/alias edges в AI map;
- `services.referenced_locations` для global file/line provenance по service references;
- categorized `dynamic_boundaries`, schema-backed `category_reasons`, line-level `dynamic_boundaries.locations` with per-location `boundary_kind` в AI map, `service_reference_locations`, `dynamic_boundary_category`, `dynamic_boundary_details.locations`, `dynamic_boundary_details.boundary_kind`, `related_service_ids` и compact `related_service_descriptors` в `ai:explain`;
- documented service reference provenance workflow, DI creator availability-boundary policy и `--next` dynamic-boundary workflow в `.ai/architecture.md`;
- `fan\core\ai\dynamic_boundary` как первый tiny support surface для стабильной AI-map логики;
- `php fan ai:dynamic-boundaries --composition --json` для ranked DI composition-root queue и `php fan ai:dynamic-boundaries --composition-open --json` для actionable count `2+` queue;
- `dynamic_boundaries.composition_leaf_policy`, `terminal_composition_leaves` и `actionable_composition_roots` в AI map;
- `php fan ai:dynamic-boundaries --debt --json` для actionable named/default migration debt;
- `dynamic_boundaries.named_migration_debt_policy`, `actionable_named_migration_debt` и `intentional_named_compatibility_boundaries` в AI map;
- `php fan ai:source-inventory --next --json` и `source_inventory.queues` для post-dynamic source-guard маршрута;
- `application_creator_common_dependencies` как первый explicit DI composition-root bundle с preserved service descriptor provenance;
- block dependency groups для `base_dependency_resolver`, tab dependency groups для navigation creator, content/controller/core/utility/client/session/user dependency bundles, infrastructure config/cache/service dependency groups и pager dependency groups;
- PHPStan baseline сожжен до 0 entries.

Следующий лучший ход не "переписать как Laravel" и не "ввести DDD везде". P289-P293 закрыли последнюю очередь count `2`, P294-P298 закрепили политику terminal composition leaves, P299-P303 отделили actionable named debt от intentional compatibility, P304-P308 вынесли последние two named debt defaults из `entity.php` и `block/base.php`, P309-P313 добавили post-dynamic source-inventory route, P314-P318 отделили bootstrap/default-provider boundaries и вынесли первый pager registrar lookup, P319-P323 вынесли session/client/utility registrar state lookups, P324-P328 закрыли user/infrastructure registrars и первый support `SERVICE_DEPENDENCIES` cluster, P329-P333 вынесли оставшиеся support registrar clusters, а P334-P338 закрепили completion audit. Сейчас dynamic queues чистые, `source_inventory.next` пустой, `service_locator_calls`, `unmanaged_container_lookups` и `unmanaged_loading_statements` тоже чистые. Обязательный AI-first code refactor route закрыт; оставшиеся bootstrap/composition/loading surfaces классифицированы как intentional boundaries.

## Статус миграции

| Срез | Статус | Доказательство |
|---|---|---|
| Workspace hygiene | Готово | `.gitignore` расширен; `node_modules/`, `.DS_Store`, virtualenv/runtime noise сняты с tracked set. |
| `.ai/` contract | Готово | `.ai/project.md`, `.ai/architecture.md`, `.ai/commands.md`, `.ai/conventions.md`. |
| AI map | Готово++++++++++++ | `php tools/ai_map.php --json` строит JSON-карту проекта, service descriptors, concrete class/factory/config edges, dependency/runtime/alias edge lists, descriptor `referenced_by`, referenced service locations, точные source locations для class/config/factory-parameter/runtime/dependency/alias edges, aliases с line provenance, categorized dynamic boundaries и category reasons, factory metadata, creator method arguments и metadata/template map; `.ai/map.schema.json` проверяется schema subset validator; `php fan ai:map --validate`. |
| AI verify | Готово+++ | `php tools/ai_verify.php` запускает tracked-noise check, `git diff --check`, PHP lint changed files, `ai_map` contract validation, static baseline и PHPUnit/focused PHPUnit. |
| Service id constants, DI срез | Готово для текущего среза | `core/di/service_id.php`; DI registrars/creators/adapters/state registry переведены на constants для service ids; raw `factory/get/alias` ids в `core/di` guard-проверены. |
| Model/entity collaborator extraction | P14-P33 закрыты | `core/base/model/**` больше не содержит production `->getService()` call sites; `entity.php` имеет collaborators для lookup/designer/description/ns-prefix/collection-key/sql-dir/row dependencies/related rows; factory path использует `entity_dependencies`. |
| Meta/template schema | Готово+ | `.ai/meta.schema.json`; `ai_map.metadata` экспортирует meta keys, paired templates и template placeholders; `MetaFilesTest` валидирует meta contract и template paths. |
| Static analysis / formatter | Готово+++ | `phpstan/phpstan`, `friendsofphp/php-cs-fixer`, `phpstan.neon.dist`, пустой `phpstan-baseline.neon`, `.php-cs-fixer.dist.php`, `tools/ai_static_check.php`. |
| Framework CLI bridge | Готово+++++++ | `php fan ai:map --validate`, `ai:services [service-id]`, `ai:explain`, `ai:verify`, `ai:doctor`, `ai:static`; single-service path покрыт happy/negative tests и показывает `referenced_by`; explain path возвращает file-local service reference locations, dynamic boundary details, related service ids и compact related service descriptors. |

## Текущий срез

Проверено локально после P334-P338:

- focused P139 PHPUnit: `107 tests`, `47526 assertions`;
- focused P140 PHPUnit: `118 tests`, `50096 assertions`;
- focused P141 PHPUnit: `113 tests`, `48475 assertions`;
- focused P142 PHPUnit: `149 tests`, `51862 assertions`;
- focused P143 PHPUnit: `166 tests`, `56262 assertions`;
- focused P144-P148 PHPUnit: `219 tests`, `59078 assertions`;
- focused P149-P153 PHPUnit: `250 tests`, `63726 assertions`;
- focused P154-P158 PHPUnit: `191 tests`, `59917 assertions`;
- focused P159-P163 PHPUnit: `231 tests`, `69989 assertions`;
- focused P164-P168 PHPUnit: `263 tests`, `71174 assertions`;
- focused P169-P173 PHPUnit: `229 tests`, `83892 assertions`;
- focused P174-P178 PHPUnit: `229 tests`, `75084 assertions`;
- focused P179-P183 PHPUnit: `176 tests`, `66469 assertions`;
- focused P184-P188 PHPUnit: `187 tests`, `68880 assertions`;
- focused P189-P193 PHPUnit: `154 tests`, `68046 assertions`;
- focused P194-P198 PHPUnit: `143 tests`, `67767 assertions`;
- focused P199-P203 PHPUnit: `255 tests`, `92128 assertions`;
- focused P204-P208 PHPUnit: `250 tests`, `80940 assertions`;
- focused P209-P213 PHPUnit: `271 tests`, `99901 assertions`;
- focused P214-P218 PHPUnit: `199 tests`, `79606 assertions`;
- focused P219-P223 PHPUnit: `270 tests`, `87331 assertions`;
- focused P224-P228 PHPUnit: `219 tests`, `83951 assertions`;
- focused P229-P233 PHPUnit: `259 tests`, `92993 assertions`;
- focused P234-P238 PHPUnit: `262 tests`, `98801 assertions`;
- focused P239-P243 PHPUnit: `262 tests`, `109157 assertions`;
- focused P244-P248 PHPUnit: `241 tests`, `92917 assertions`;
- focused P249-P253 PHPUnit: `200 tests`, `82853 assertions`;
- focused P254-P258 PHPUnit: `144 tests`, `78897 assertions`;
- focused P259-P263 PHPUnit: `216 tests`, `93625 assertions`;
- focused P264-P268 PHPUnit: `229 tests`, `95372 assertions`;
- focused P269-P273 PHPUnit: `223 tests`, `97607 assertions`;
- focused P274-P278 PHPUnit: `233 tests`, `99966 assertions`;
- focused P279-P283 PHPUnit: `237 tests`, `112957 assertions`;
- focused P284-P288 PHPUnit: `256 tests`, `105688 assertions`;
- focused P289-P293 PHPUnit: `360 tests`, `157716 assertions`;
- focused P294-P298 PHPUnit: `336 tests`, `155872 assertions`;
- focused P299-P303 PHPUnit: `336 tests`, `155914 assertions`;
- focused P304-P308 PHPUnit: `392 tests`, `157009 assertions`;
- focused P309-P313 PHPUnit: `339 tests`, `156548 assertions`;
- focused P314-P318 PHPUnit: `17 tests`, `3143 assertions`;
- focused P319-P323 PHPUnit: `39 tests`, `3673 assertions`;
- focused P324-P328 PHPUnit: `37 tests`, `3779 assertions`;
- focused P329-P333 PHPUnit: `17 tests`, `3561 assertions`;
- focused P334-P338 PHPUnit: `16 tests`, `3457 assertions`;
- full `php tools/ai_verify.php --json`: `pass`; PHPUnit `1920 tests`, `179499 assertions`;
- `php fan ai:dynamic-boundaries --next --json`: `[]`;
- `php fan ai:dynamic-boundaries --composition-open --json`: `[]`;
- `dynamic_boundaries.terminal_composition_leaves`: `290`;
- `dynamic_boundaries.actionable_composition_roots`: `0`;
- `php fan ai:dynamic-boundaries --debt --json`: `[]`;
- `dynamic_boundaries.actionable_named_migration_debt`: `0`;
- `dynamic_boundaries.intentional_named_compatibility_boundaries`: `4`;
- dynamic boundary categories: `extension_api = 17`, `composition_roots = 466`, `tooling_support = 1`, `migration_debt = 0`;
- `php fan ai:source-inventory --next --json`: `[]`;
- `source_inventory.queues.service_locator_calls`: `clean`, count `0`;
- `source_inventory.queues.unmanaged_container_lookups`: `clean`, count `0`;
- `source_inventory.queues.unmanaged_loading_statements`: `clean`, count `0`;
- `source_inventory.queues.bootstrap_container_lookups`: `bootstrap_boundary`, count `25` / `7` files;
- `source_inventory.queues.intentional_loading_boundaries`: count `12` / `7` files;
- `source_inventory.queues.tooling_loading_statements`: count `7` / `4` files;
- `source_inventory.queues.entrypoint_loading_statements`: count `1` / `1` file;
- `source_inventory.queues.composition_container_lookups`: count `272`;
- `core/base/model/entity.php`: dynamic-boundary count `0`; default model class availability moved to `core/base/model/entity_dependencies.php`;
- `core/block/base.php`: dynamic-boundary count `0`; default delayed-meta/block-exception class availability moved to `core/block/base_dependency_defaults.php`;
- `php fan ai:dynamic-boundaries --composition --json`: ranked composition-root inventory готов и показывает terminal leaves вместо fake split targets;
- `core/di/application_content_service_dependencies.php`: count снизился с `11` до facade `0`; новые groups: context (`6`), runtime (`3`), storage (`2`);
- `core/di/application_navigation_tab_service_factory_dependencies.php`: count снизился с `11` до facade `0`; новые groups: application (`4`), runtime (`4`), payload (`3`);
- `core/di/application_controller_service_creator.php`: count снизился с `10` до `1` named class-exists boundary; новый `application_controller_service_dependencies.php` имеет `9` lookups;
- `core/di/application_controller_service_dependencies.php`: count снизился с `9` до facade `0`; новые groups: plain (`3`), handler (`3`), runtime (`3`);
- `core/di/application_core_request_dependencies.php`: count снизился с `9` до facade `0`; новые groups: request-factory (`5`), helper (`4`);
- `core/di/application_infrastructure_service_creator.php`: count снизился с `9` до `1` named class-exists boundary; новые groups: service facade (`0`), runtime (`4`), storage (`1`);
- `core/di/application_pager_service_creator.php`: count снизился с `9` до `1` named class-exists boundary; новые groups: service facade (`0`), context (`3`), runtime (`3`), exception (`1`);
- `core/block/base_dependency_context_group.php`: count снизился с `9` до facade `0`; новые groups: runtime/request/navigation по `3`;
- `core/block/base_dependency_view_meta_group.php`: count снизился с `9` до facade `0`; новые groups: meta (`4`), view (`3`), block-factory (`2`);
- `core/block/base_dependency_helper_group.php`: count снизился с `7` до facade `0`; новые groups: array (`4`), class (`1`), media-error (`2`);
- `core/di/application_navigation_tab_support_helper_dependencies.php`: count снизился с `7` до facade `0`; новые groups: loader (`1`), array (`4`), class (`2`);
- `core/di/application_user_factory_dependencies.php`: count снизился с `7` до facade `0`; новые groups: application (`3`), identity (`2`), support (`2`);
- `core/block/base_dependency_application_factory_group.php`: count снизился с `6` до facade `0`; новые groups: runtime (`2`), data (`2`), support (`2`);
- `core/di/application_content_context_dependencies.php`: count снизился с `6` до facade `0`; новые groups: localization (`2`), error-block (`2`), request-matcher (`2`);
- `core/di/application_navigation_tab_model_factory_dependencies.php`: count снизился с `6` до facade `0`; новые groups: data (`2`), media (`2`), user-time (`2`);
- `core/di/application_user_context_dependencies.php`: count снизился с `6` до facade `0`; новые groups: serialization-config (`2`), application-request (`2`), exception-session (`2`);
- `core/di/application_utility_core_dependencies.php`: count снизился с `6` до facade `0`; новые groups: runtime (`2`), config-cache (`2`), helper-error (`2`);
- `core/di/application_utility_image_dependencies.php`: count снизился с `6` до facade `0`; новые groups: storage (`2`), metadata-resource (`2`), canvas-output (`2`);
- `core/block/base_dependency_storage_group.php`: count снизился с `5` до facade `0`; новые groups: block-file (`2`), project-file (`2`), upload-limit (`1`);
- `core/di/application_client_payload_dependencies.php`: count снизился с `5` до facade `0`; новые groups: array (`2`), request (`1`), serialization (`2`);
- `core/di/application_core_project_tab_dependencies.php`: count снизился с `5` до facade `0`; новые groups: tab-context (`3`), application-context (`1`), route-storage (`1`);
- `core/di/application_core_request_factory_dependencies.php`: count снизился с `5` до facade `0`; новые groups: input (`1`), runtime (`2`), transport (`2`);
- `core/di/application_core_user_session_dependencies.php`: count снизился с `5` до facade `0`; новые groups: identity (`3`), data (`2`);
- `core/di/application_infrastructure_config_cache_support_dependencies.php`: count снизился с `5` до facade `0`; новые groups: runtime-support (`2`), loader-serializer (`2`), class-helper (`1`);
- `core/di/application_navigation_tab_context_dependencies.php`: count снизился с `5` до facade `0`; новые groups: routing (`2`), locale-session (`2`), input (`1`);
- `core/di/application_navigation_tab_storage_dependencies.php`: count снизился с `5` до facade `0`; новые groups: file-storage (`3`), project-tool-storage (`1`), upload-limit-storage (`1`);
- `core/di/application_session_context_dependencies.php`: count снизился с `5` до facade `0`; новые groups: application-context (`2`), request-context (`2`), header-context (`1`);
- `core/di/application_session_factory_dependencies.php`: count снизился с `5` до facade `0`; новые groups: support-factory (`2`), state-factory (`2`), cache-factory (`1`);
- `core/di/application_session_runtime_dependencies.php`: count снизился с `5` до facade `0`; новые groups: native-runtime (`2`), bootstrap-runtime (`2`), array-runtime (`1`);
- `core/block/base_dependency_array_helper_group.php`: count снизился с `4` до facade `0`; новые groups: array-transform (`2`), array-read (`2`);
- `core/block/base_dependency_data_factory_group.php`: count снизился с `4` до facade `0`; новые groups: data-core (`2`), data-loader (`2`);
- `core/block/base_dependency_meta_loader_group.php`: count снизился с `4` до facade `0`; новые groups: meta-maker (`2`), meta-row-loader (`2`);
- `core/di/application_client_transport_dependencies.php`: count снизился с `4` до facade `0`; новые groups: curl-transport (`2`), serialization-transport (`1`), error-transport (`1`);
- `core/di/application_core_project_error_dependencies.php`: count снизился с `4` до facade `0`; новые groups: error-service (`2`), error-storage (`2`);
- `core/di/application_core_project_storage_dependencies.php`: count снизился с `4` до facade `0`; новые groups: reflection-meta (`2`), response-loader (`2`);
- `core/di/application_core_request_helper_dependencies.php`: count снизился с `4` до facade `0`; новые groups: array-transform (`2`), array-read-class (`2`);
- `core/di/application_infrastructure_config_cache_exception_dependencies.php`: count снизился с `4` до facade `0`; новые groups: exception-factory (`2`), request-header (`2`);
- `core/di/application_infrastructure_config_cache_factory_dependencies.php`: count снизился с `4` до facade `0`; новые groups: config-factory (`2`), cache-factory (`2`);
- `core/di/application_infrastructure_runtime_dependencies.php`: count снизился с `4` до facade `0`; новые groups: runtime-error (`2`), runtime-config-cache (`2`);
- `core/di/application_navigation_tab_service_factory_application_dependencies.php`: count снизился с `4` до facade `0`; новые groups: role-transfer (`2`), application-debug (`2`);
- `core/di/application_navigation_tab_service_factory_runtime_dependencies.php`: count снизился с `4` до facade `0`; новые groups: config-header (`2`), error-reflector (`2`);
- `core/di/application_navigation_tab_support_array_helper_dependencies.php`: count снизился с `4` до facade `0`; новые groups: array-transform (`2`), array-read-check (`2`);
- `core/di/application_navigation_tab_support_block_dependencies.php`: count снизился с `4` до facade `0`; новые groups: block-factory (`2`), block-exception-meta (`2`);
- `core/block/base_dependency_media_factory_group.php`: count снизился с `3` до facade `0`; новые groups: media-core (`2`), media-transfer (`1`);
- `core/block/base_dependency_navigation_context_group.php`: count снизился с `3` до facade `0`; новые groups: reflector (`1`), route-locale (`2`);
- `core/block/base_dependency_request_context_group.php`: count снизился с `3` до facade `0`; новые groups: request-role (`2`), session (`1`);
- `core/block/base_dependency_runtime_context_group.php`: count снизился с `3` до facade `0`; новые groups: tab-runtime (`2`), request-input (`1`);
- `core/block/base_dependency_view_loader_group.php`: count снизился с `3` до facade `0`; новые groups: view-factory (`2`), view-state (`1`);
- `core/di/application_client_runtime_dependencies.php`: count снизился с `3` до facade `0`; новые groups: bootstrap-runtime (`1`), config-cache-runtime (`2`);
- `core/di/application_content_runtime_dependencies.php`: count снизился с `3` до facade `0`; новые groups: bootstrap-runtime (`1`), config-cache-runtime (`2`);
- `core/di/application_controller_handler_dependencies.php`: count снизился с `3` до facade `0`; новые groups: obfuscator-handler (`2`), plain-file-handler (`1`);
- `core/di/application_controller_plain_dependencies.php`: count снизился с `3` до facade `0`; новые groups: plain-route (`2`), plain-config (`1`);
- `core/di/application_controller_runtime_dependencies.php`: count снизился с `3` до facade `0`; новые groups: bootstrap-runtime (`1`), config-cache-runtime (`2`);
- `core/di/application_core_project_tab_context_dependencies.php`: count снизился с `3` до facade `0`; новые groups: tab-service (`2`), locale (`1`);
- `core/di/application_core_user_identity_dependencies.php`: count снизился с `3` до facade `0`; новые groups: current-identity (`1`), session-space (`2`);
- `core/di/application_creator_common_dependencies.php`: count снизился с `3` до facade `0`; новые groups: bootstrap-runtime (`1`), config-cache (`2`);
- `core/di/application_navigation_tab_file_storage_dependencies.php`: count снизился с `3` до facade `0`; новые groups: block-meta-file-storage (`2`), root-html-file-storage (`1`);
- `core/di/application_navigation_tab_service_factory_payload_dependencies.php`: count снизился с `3` до facade `0`; новые groups: json-payload (`1`), data-cookie-payload (`2`);
- `core/di/application_navigation_tab_support_asset_dependencies.php`: count снизился с `3` до facade `0`; новые groups: alias-asset (`1`), media-error-asset (`2`);
- `core/di/application_pager_context_dependencies.php`: count снизился с `3` до facade `0`; новые groups: entity-context (`1`), tab-context (`1`), request-context (`1`);
- `core/di/application_pager_runtime_dependencies.php`: count снизился с `3` до facade `0`; новые groups: bootstrap-runtime (`1`), config-cache-runtime (`2`);
- `core/block/base_dependency_application_runtime_factory_group.php`: count снизился с `2` до facade `0`; новые groups: application-service-runtime (`1`), config-application-runtime (`1`);
- `core/block/base_dependency_application_support_factory_group.php`: count снизился с `2` до facade `0`; новые groups: error-application-support (`1`), date-application-support (`1`);
- `core/block/base_dependency_array_read_helper_group.php`: count снизился с `2` до facade `0`; новые groups: array-value-reader (`1`), array-like-checker (`1`);
- `core/block/base_dependency_array_transform_helper_group.php`: count снизился с `2` до facade `0`; новые groups: array-adducer (`1`), recursive-merger (`1`);
- `core/block/base_dependency_block_factory_context_group.php`: count снизился с `2` до facade `0`; новые groups: block-factory (`1`), block-exception-factory (`1`);
- `core/block/base_dependency_block_file_storage_group.php`: count снизился с `2` до facade `0`; новые groups: block-file-storage-block (`1`), block-file-storage-meta (`1`);
- `core/block/base_dependency_data_core_factory_group.php`: count снизился с `2` до facade `0`; новые groups: entity-data-core (`1`), json-data-core (`1`);
- `core/block/base_dependency_data_loader_factory_group.php`: count снизился с `2` до facade `0`; новые groups: data-loader-service (`1`), pager-data-loader (`1`);
- `core/block/base_dependency_media_core_factory_group.php`: count снизился с `2` до facade `0`; новые groups: obfuscator-media-core (`1`), image-modify-media-core (`1`);
- `core/block/base_dependency_media_error_helper_group.php`: count снизился с `2` до facade `0`; новые groups: image-metadata-media-error (`1`), error-log-media-error (`1`);
- `core/block/base_dependency_meta_maker_group.php`: count снизился с `2` до facade `0`; новые groups: meta-maker-state (`1`), meta-maker-factory (`1`);
- `core/block/base_dependency_meta_row_loader_group.php`: count снизился с `2` до facade `0`; новые groups: php-array-file-loader (`1`), meta-row-factory (`1`);
- `core/block/base_dependency_project_file_storage_group.php`: count снизился с `2` до facade `0`; новые groups: project-tool-file-storage (`1`), root-html-file-storage (`1`);
- `core/block/base_dependency_request_role_context_group.php`: count снизился с `2` до facade `0`; новые groups: request-factory-context (`1`), role-factory-context (`1`);
- `core/block/base_dependency_route_locale_context_group.php`: count снизился с `2` до facade `0`; новые groups: locale-factory-context (`1`), matcher-factory-context (`1`);
- `core/block/base_dependency_tab_runtime_context_group.php`: count снизился с `2` до facade `0`; новые groups: tab-service-runtime (`1`), bootstrap-runtime (`1`);
- `core/block/base_dependency_view_factory_loader_group.php`: count снизился с `2` до facade `0`; новые groups: view-parser-exception-factory (`1`), view-router-factory (`1`);
- `core/di/application_client_array_payload_dependencies.php`: count снизился с `2` до facade `0`; новые groups: array-adducer (`1`), array-value-reader (`1`);
- `core/di/application_client_config_cache_runtime_dependencies.php`: count снизился с `2` до facade `0`; новые groups: config-runtime (`1`), cache-runtime (`1`);
- `core/di/application_client_curl_transport_dependencies.php`: count снизился с `2` до facade `0`; новые groups: curl-adapter (`1`), curl-factory (`1`);
- `core/di/application_client_serialization_payload_dependencies.php`: count снизился с `2` до facade `0`; новые groups: serializer-operations-payload (`1`), cookie-writer-payload (`1`);
- `core/di/application_content_config_cache_runtime_dependencies.php`: count снизился с `2` до facade `0`; новые groups: config-runtime (`1`), cache-runtime (`1`);
- `core/di/application_content_error_block_context_dependencies.php`: count снизился с `2` до facade `0`; новые groups: error-context (`1`), block-context (`1`);
- `core/di/application_content_localization_context_dependencies.php`: count снизился с `2` до facade `0`; новые groups: locale-context (`1`), tab-factory-context (`1`);
- `core/di/application_content_request_matcher_context_dependencies.php`: count снизился с `2` до facade `0`; новые groups: matcher-context (`1`), request-input-context (`1`);
- `core/di/application_content_storage_dependencies.php`: count снизился с `2` до facade `0`; новые groups: php-array-file-loader-storage (`1`), translation-file-storage (`1`);
- `core/di/application_controller_config_cache_runtime_dependencies.php`: count снизился с `2` до facade `0`; новые groups: config-runtime (`1`), cache-runtime (`1`);
- `core/di/application_controller_obfuscator_handler_dependencies.php`: count снизился с `2` до facade `0`; новые groups: obfuscator-factory-handler (`1`), request-handler (`1`);
- `core/di/application_controller_plain_route_dependencies.php`: count снизился с `2` до facade `0`; новые groups: matcher-plain-route (`1`), header-plain-route (`1`);
- `core/di/application_core_project_error_service_dependencies.php`: count снизился с `2` до facade `0`; новые groups: error-context (`1`), error-factory-service (`1`);
- `core/di/application_core_project_error_storage_dependencies.php`: count снизился с `2` до facade `0`; новые groups: error-log-writer-storage (`1`), error-file-storage (`1`);
- `core/di/application_core_project_reflection_meta_dependencies.php`: count снизился с `2` до facade `0`; новые groups: reflection-class-factory-meta (`1`), meta-file-storage (`1`);
- `core/di/application_core_project_response_loader_dependencies.php`: count снизился с `2` до facade `0`; новые groups: header-writer-response-loader (`1`), php-array-file-loader-response-loader (`1`);
- `core/di/application_core_project_tab_service_context_dependencies.php`: count снизился с `2` до facade `0`; новые groups: tab-instance-service-context (`1`), tab-factory-service-context (`1`);
- `core/di/application_core_request_array_read_class_helper_dependencies.php`: count снизился с `2` до facade `0`; новые groups: array-value-reader-helper (`1`), class-name-resolver-helper (`1`);
- `core/di/application_user_application_factory_dependencies.php`: count снизился с `3` до facade `0`; новые groups: config-factory (`1`), application-request-input-factory (`2`);
- `core/di/application_user_runtime_dependencies.php`: count снизился с `3` до facade `0`; новые groups: bootstrap-cache-runtime (`2`), array-runtime (`1`);
- `core/di/application_utility_storage_dependencies.php`: count снизился с `3` до facade `0`; новые groups: file-storage (`2`), class-storage (`1`);
- `core/block/base_dependency_application_data_factory_group.php`: count снизился с `2` до facade `0`; новые groups: database (`1`), user (`1`);
- `core/di/application_user_application_request_input_factory_dependencies.php`: count снизился с `2` до facade `0`; новые groups: application-factory (`1`), request-input-factory (`1`);
- `core/di/application_user_bootstrap_cache_runtime_dependencies.php`: count снизился с `2` до facade `0`; новые groups: bootstrap-runtime (`1`), cache-factory (`1`);
- `core/di/application_user_exception_session_context_dependencies.php`: count снизился с `2` до facade `0`; новые groups: error500-exception-factory (`1`), session (`1`);
- `core/di/application_user_identity_factory_dependencies.php`: count снизился с `2` до facade `0`; новые groups: session-factory (`1`), current-user-factory (`1`);
- `core/di/application_user_serialization_config_context_dependencies.php`: count снизился с `2` до facade `0`; новые groups: serializer-operations (`1`), config (`1`);
- `core/di/application_user_support_factory_dependencies.php`: count снизился с `2` до facade `0`; новые groups: error-factory (`1`), entity-factory (`1`);
- `core/di/application_utility_config_cache_core_dependencies.php`: count снизился с `2` до facade `0`; новые groups: config (`1`), cache-factory (`1`);
- `core/di/application_utility_file_storage_dependencies.php`: count снизился с `2` до facade `0`; новые groups: php-array-file-loader (`1`), soap-wsdl-file-storage (`1`);
- `core/di/application_utility_helper_error_core_dependencies.php`: count снизился с `2` до facade `0`; новые groups: array-value-reader (`1`), error-factory (`1`);
- `core/di/application_utility_image_canvas_output_dependencies.php`: count снизился с `2` до facade `0`; новые groups: image-canvas-operations (`1`), image-output-writer (`1`);
- `core/di/application_utility_image_metadata_resource_dependencies.php`: count снизился с `2` до facade `0`; новые groups: image-metadata-reader (`1`), image-resource-factory (`1`);
- `core/di/application_utility_image_storage_dependencies.php`: count снизился с `2` до facade `0`; новые groups: obfuscator-file-storage (`1`), image-source-file-storage (`1`);
- `core/di/application_utility_runtime_core_dependencies.php`: count снизился с `2` до facade `0`; новые groups: bootstrap-runtime (`1`), php-runtime-settings (`1`);
- `core/factory/application_runtime_factory_defaults_provider_factory.php`: count снизился с `2` до assembly `0`; configured-construction defaults вынесены в runtime configured-service/class-instantiator provider leaves (`1` + `1`);
- `core/factory/bootstrap_object_defaults_provider_factory.php`: count снизился с `2` до assembly `0`; configured-construction defaults вынесены в bootstrap object configured-service/class-instantiator provider leaves (`1` + `1`);
- `tools/ai_service_map.php`: named dependency methods для `$common`, `$coreDependencies`, `$utilityDependencies`, `$clientDependencies`, `$contentDependencies`, `$controllerDependencies`, `$tabDependencies`, `$sessionDependencies`, `$userDependencies`, `$infrastructureDependencies`, `$pagerDependencies` сохраняют dependencies/source locations.

Актуальный direct readout на 2026-06-13:

- `php fan ai:dynamic-boundaries --next --json`: `[]`;
- `php fan ai:dynamic-boundaries --direct --json`: только tracked `composition_roots`;
- самые шумные roots теперь все count `1`: `core/block/base_dependency_application_service_runtime_factory_group.php`, `core/block/base_dependency_array_adducer_helper_group.php`, `core/block/base_dependency_array_like_checker_helper_group.php`, `core/block/base_dependency_array_value_reader_helper_group.php`, `core/block/base_dependency_block_exception_factory_group.php`;
- next refactor должен добавить tooling-политику terminal composition leaves: count `1` roots остаются tracked inventory, но перестают выглядеть как actionable split queue.

## Что уже хорошо для AI

1. **Строгий PHP baseline**

   `composer.json` требует PHP `>=8.2`, все production PHP-файлы уже используют `strict_types`.

2. **Нормальный autoload**

   `composer.json` задает PSR-4 namespaces:

   - `fan\core\adapter\`
   - `fan\core\bootstrap\`
   - `fan\core\di\`
   - `fan\core\runtime\`
   - `fan\core\`
   - `fan\project\`
   - `fan\app\`

3. **Entry point стал чистым**

   `htdocs/index.php` уже сведен к Composer autoload и `web_application_initializer_defaults_factory`.

4. **DI уже выделен**

   `core/di/container.php`, `application_*_service_registrar.php` и `application_*_service_creator.php` дают явную точку для дальнейшей типизации service graph.

5. **Есть regression guards**

   `unit/core/LegacyDiSourceInventoryTest.php` уже запрещает старые service-locator и dynamic-construction паттерны.

6. **Тесты быстрые**

   Полный PHPUnit suite проходит примерно за 15-17 секунд. Для AI это очень хороший цикл обратной связи.

## Закрытые и оставшиеся AI-friction points

### 1. Git/workspace hygiene

Статус: закрыто для первого среза.

`.gitignore` расширен. `tools/ai_verify.php` теперь проверяет tracked dependency/runtime noise и падает, если в tracked files попали `node_modules/`, `.venv`, `.DS_Store` или logs.

### 2. Нет AI manifest слоя

Статус: базовый слой закрыт.

Добавлено:

```text
.ai/
  project.md
  architecture.md
  commands.md
  conventions.md
```

Snapshot decision: committed `.ai/map.json` пока не нужен. Карта содержит `generated_at`, поэтому committed snapshot будет создавать churn; для release/update процесса оставлен `php tools/ai_map.php --write`, а стабильный контракт закреплен через `.ai/map.schema.json` и `php fan ai:map --validate`.

### 3. Нет единой AI verification команды

Статус: закрыто.

Единый entrypoint:

```bash
php tools/ai_verify.php
```

Поднято также в framework CLI:

```bash
php fan ai:verify
php fan ai:doctor
```

### 4. Service graph не машинно-читаемый

Статус: закрыто для текущего graph-descriptor слоя; продолжается для richer provenance/line-level edges.

`tools/ai_map.php --json` уже экспортирует:

- Composer autoload;
- entrypoints;
- source/test roots;
- meta/template files;
- registered/referenced service ids;
- `service_id` constants;
- `services.descriptors` с registrar files, creator methods, shared/non-shared, direct dependencies, aliases с line/source-location provenance, factory origin, factory arguments, creator method arguments, concrete/project service class, factory names, config keys, lifetime reason, source edges и source locations;
- `services.referenced_locations` с file/line provenance для global service references.

Оставшийся долг: service graph уже описывает common concrete edges, dependency/runtime edge lists и точные class/config/factory-parameter/runtime/dependency/alias source lines. Следующий provenance layer должен закрыть alias edges в `source_edges`, file-local reference locations в `ai:explain` и descriptor-local reverse reference summaries. AI все еще иногда открывает source, чтобы понять контекст вокруг edge:

```text
registrar -> creator method -> конкретный branch/config read/factory argument
```

Следующий слой descriptor enrichment: alias edge consistency, file-local service references и schema-backed dynamic-boundary reasons под будущий `core/ai`.

### 5. String service ids остаются главным blind spot

Статус: начато, P2 registrars и центральные creators переведены.

Добавлен `fan\core\di\service_id`. P2 перевел infrastructure/content/navigation/client/session/user/utility registrars и core/infrastructure/navigation creators на constants.

Оставшийся долг: raw ids остаются в части creators, adapter/state registries и compatibility/config boundaries.

Примеры:

- `matcher`
- `request_input`
- `bootstrap_runtime`
- `config`
- `cache`
- `array_value_reader`

Дальше постепенно заменять строковые ids в touched DI files и source guard-ом запрещать новые raw ids вне allowlist. Не стоит делать global replace: часть строк является config key, template key или runtime type.

### 6. Model/entity слой больше не тянет service locator назад

Статус: закрыто для production `core/base/model/**`.

P24-P28 убрали последние соседние model call sites:

- `core/base/model/file_data/row.php`;
- `core/base/model/row.php`;
- `core/base/model/spec_file/row.php`;
- `core/base/model/spec_file/image/entity.php`;
- `core/base/model/spec_file/image/row.php`.

Это уже не старый глобальный service locator. Новый долг другого типа: `entity.php` получил много callable-collaborators, и эту форму нужно стабилизировать, чтобы AI не ошибался в порядке constructor arguments.

Следующий refactor-срез:

- сгруппировать model entity collaborators в named dependency object;
- оставить BC constructor path;
- перевести tests/factory на именованные границы;
- запретить новый прямой `getService()` в model слое без allowlist.

### 7. Reflection/config runtime должен стать явным extension API

В production PHP есть десятки `class_exists`, `ReflectionClass`, `configured_class_instantiator` точек. Это нормально для legacy-compatible framework, но AI нужна граница:

- internal core wiring должен быть typed/factory-map;
- reflection оставить только для documented extension points;
- extension points описывать в `.ai/map.json`.

### 8. Meta/template runtime остается динамическим

Статус: базовая карта и schema готовы; нужны contract tests и path validation.

`*.meta.php` и `*.tpl` - важная часть PHP-FAN, но для AI это второй язык внутри framework.

Уже добавлено:

- manifest extractor для meta-файлов;
- schema/описание допустимых meta keys;
- paired meta/template map;
- template placeholder extraction.

Нужно добавить:

- tests, которые проверяют meta contracts;
- validation существующих block/template paths;
- adapter boundary вокруг template loading/compiled template loading.

### 9. Tooling неполный

Статус: реальные tools подключены, baseline пустой.

Добавлено:

- `phpstan.neon.dist`;
- пустой `phpstan-baseline.neon`;
- `.php-cs-fixer.dist.php`;
- `tools/ai_static_check.php`;
- Composer scripts `ai:*`.

Следующий шаг:

- Rector для механических PHP upgrades/refactors;
- сделать formatter check частью обязательного CI.

## Рекомендуемая архитектура целевого AI-first слоя

Не менять framework форму резко. Добавить AI-native слой поверх текущей архитектуры:

```text
core/
  ai/
    project_map.php
    project_map_builder.php
    service_graph_reader.php
    source_inventory.php
    verification_plan.php

tools/
  ai_map.php
  ai_verify.php
  ai_explain.php
  ai_static_check.php

fan
  ai:map
  ai:services
  ai:explain
  ai:verify
  ai:doctor
  ai:static

.ai/
  architecture.md
  commands.md
  conventions.md
  map.schema.json
  map.json
```

### Уже добавленные AI-команды

```bash
php tools/ai_map.php --json
php tools/ai_explain.php core/service/matcher.php --json
php tools/ai_verify.php
php tools/ai_static_check.php
php fan ai:map --json
php fan ai:services --json
php fan ai:explain core/service/matcher.php --json
php fan ai:verify
php fan ai:doctor
php fan ai:static
```

## Приоритетный план рефакторинга

### P0. Foundation slice: AI visibility

Статус: готово.

Что закрыто:

- workspace hygiene;
- `.ai/` docs;
- `tools/ai_map.php`;
- `tools/ai_verify.php`;
- первый `service_id` срез;
- tests: `AiToolingTest`, `ServiceIdTest`, updated DI source tests.

Проверка:

```bash
php tools/ai_verify.php
```

### P1. Service graph descriptors

Статус: готово.

Цель: сделать DI graph читаемым без путешествия по registrars/creators/factories.

Шаги:

1. Ввести `core/di/service_descriptor.php`.
2. Добавить descriptor registry/reader для registered ids.
3. Для каждого service id экспортировать:
   - id;
   - registrar file;
   - creator method, если видно статически;
   - factory callable/config key, если видно статически;
   - shared/non-shared, если есть;
   - direct container dependencies из creator body.
4. Расширить `tools/ai_map.php --json`: `services.descriptors`.
5. Добавить тесты на обязательные ids: `request`, `matcher`, `bootstrap_runtime`, `config`, `cache`, `json`, `tab`, `session`, `user`.

Проверка:

```bash
php tools/ai_map.php --json
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php unit/core/di
```

### P2. Продолжить `service_id` migration

Статус: готово для запланированного среза, продолжается для оставшихся creators/registries.

Цель: raw service-id strings должны постепенно оставаться только в compatibility/config boundaries.

Шаги:

1. Перевести следующие registrars на `service_id` constants:
   - `application_infrastructure_service_registrar`;
   - `application_content_service_registrar`;
   - `application_navigation_service_registrar`;
   - `application_client_service_registrar`;
   - `application_session_service_registrar`;
   - `application_user_service_registrar`;
   - `application_utility_service_registrar`.
2. Переводить creator-классы маленькими срезами, начиная с самых центральных:
   - `application_core_service_creator`;
   - `application_infrastructure_service_creator`;
   - `application_navigation_service_creator`.
3. Добавить source guard: новые raw ids запрещены в touched DI files, кроме allowlist.
4. `ai_map` должен резолвить constants и raw ids одинаково.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di unit/core/AiToolingTest.php
php tools/ai_verify.php
```

### P3. Model/entity collaborator extraction

Статус: начато, первый extraction готов.

Цель: убрать `getService()` как скрытую зависимость модели.

Шаги:

1. Зафиксировать текущий allowlist `getService()` usages тестом.
2. Начать с самого узкого use case:
   - `core/base/model/request.php`: заменить `$entity->getService()->getSqlDir()` на injected/collaborator accessor.
3. Следующие collaborators:
   - `EntitySqlDirectoryProvider`;
   - `EntityLookup`;
   - `EntityIdCodec`;
   - `EntityDescriptionProvider`;
   - `EntityDesignerFactory`;
   - `EntityFileDataFactory`.
4. После каждого extracted collaborator уменьшать allowlist.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/base/model unit/core/service/EntityTest.php unit/core/di/EntityModelFactoriesTest.php
php tools/ai_verify.php
```

### P4. Meta/template map and schema

Статус: готово для базового AI map/schema слоя.

Цель: превратить meta/template слой из "магии" в documented compatibility subsystem.

Шаги:

1. Добавить `tools/ai_meta_map.php` или расширить `tools/ai_map.php` секцией `metadata`.
2. Собрать top-level keys из `*.meta.php`.
3. Сгенерировать `.ai/meta.schema.json`.
4. Документировать common keys:
   - `own`;
   - `common`;
   - `embeddedBlocks`;
   - `carcass`;
   - `externalCss`;
   - `tplVars`.
5. Добавить tests на валидность meta-файлов и links на существующие block/template paths.

Проверка:

```bash
php tools/ai_map.php --json
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/MetaFilesTest.php unit/core/AiToolingTest.php
```

### P5. `ai_explain` for source-local context

Статус: готово.

Цель: дать агенту быстрый локальный контекст по файлу без чтения всего проекта.

Шаги:

1. Добавить `tools/ai_explain.php <path>`.
2. Для PHP-файла выводить:
   - namespace/class;
   - public methods;
   - constructor dependencies;
   - container ids referenced;
   - related tests, если они есть;
   - nearby dynamic boundaries.
3. Использовать токены PHP, не regex-only parsing, где это разумно.

Проверка:

```bash
php tools/ai_explain.php core/service/matcher.php
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
```

### P6. Verification quality upgrades

Статус: готово.

Цель: сделать `ai_verify` удобным для больших и маленьких изменений.

Шаги:

1. Добавить режим `--changed`:
   - lint changed PHP files;
   - run focused tests по простому mapping, если возможно;
   - source-inventory tests всегда.
2. Добавить `--json` output с tail stdout/stderr для failed commands.
3. Добавить `--no-phpunit` alias к текущему `--skip-phpunit`.
4. Добавить generation check: `ai_map` должен строиться во время verify.

Проверка:

```bash
php tools/ai_verify.php --changed
php tools/ai_verify.php --json
```

### P7. Static analysis and formatter

Статус: baseline готов; реальные binaries еще нужно добавить в Composer dev dependencies отдельным срезом.

Цель: дать AI строгие автоматические rails.

Шаги:

1. Добавить PHPStan/Psalm с baseline.
2. Добавить formatter config.
3. Добавить `composer` scripts и fallback в `tools/ai_verify.php`.
4. Сделать documented policy: AI всегда запускает focused tests + `ai_verify`.

Проверка:

```bash
php tools/ai_verify.php
```

### P8. Framework CLI bridge

Статус: готово.

Цель: перевести standalone tools в настоящий framework-facing интерфейс.

Шаги:

1. Добавить минимальный `fan` CLI entrypoint или использовать существующий bootstrap CLI путь, если он есть.
2. Команды:
   - `php fan ai:map`;
   - `php fan ai:verify`;
   - `php fan ai:services`;
   - `php fan ai:doctor`.
3. На первом этапе команды могут делегировать в `tools/ai_*.php`.
4. Не вводить тяжелую CLI framework-зависимость, пока standalone tools не стабилизированы.

Проверка:

```bash
php fan ai:map
php fan ai:verify
php tools/ai_verify.php
```

### P9. Finish service id migration in remaining DI creators

Статус: выполнено в P9.

Цель: убрать raw service-id strings из DI code paths, где это безопасно и не является config/template ключом.

Сделано:

1. Снят inventory raw ids в `core/di/*_service_creator.php`, `application_adapter_registry.php`, `application_state_registry.php`.
2. Строки разделены на категории:
   - service id;
   - config key;
   - runtime type argument;
   - template/meta key.
3. Constants добавлены только для service ids.
4. DI creators/registries переведены на `service_id::*`:
   - `application_user_service_creator`;
   - `application_session_service_creator`;
   - `application_client_service_creator`;
   - `application_content_service_creator`;
   - `application_utility_service_creator`;
   - `application_adapter_registry`;
   - `application_state_registry`;
   - support/adapter/state registration paths.
5. Source guard: raw `factory/get/alias` service ids в `core/di` не возвращаются.

Проверка:

```bash
php fan ai:doctor
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/Application*Service*Test.php
```

### P10. Shrink model `getService()` allowlist

Статус: первый runtime-refactor срез выполнен; дальнейшие extractions остаются следующими шагами.

Цель: превратить hidden entity service calls в явные collaborators.

Порядок extraction:

1. `EntityIdCodec`: выполнено. `model_entity_factory` прокидывает callable decoder, `entity.php` использует `decodeEntityId()`, `file_data/row.php` больше не вызывает `getEncapsulant()->decryptId()` напрямую.
2. `EntityLookup`: заменить `getEntityByTable()` в `row.php`.
3. `EntityDescriptionProvider`: заменить `getDescription()` в `entity.php`.
4. `EntityDesignerFactory`: заменить `getDesigner()` в `entity.php`.
5. `EntityFileDataFactory`: заменить spec-file entity/file_data creation.

Правило: после каждого extraction уменьшать allowlist в `LegacyDiSourceInventoryTest`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/base/model unit/core/LegacyDiSourceInventoryTest.php
php tools/ai_verify.php --changed
```

### P11. Meta contract validation tests

Статус: выполнено в P11.

Цель: сделать `.meta.php` не просто видимыми в map, а проверяемыми контрактами.

Сделано:

1. `unit/core/block/MetaFilesTest.php` расширен contract-проверками.
2. Проверяется, что meta inventory из `ai_map` включает известные core meta files.
3. Проверяется `.ai/meta.schema.json`.
4. Проверяются paired template paths.
5. Проверяются `embeddedBlocks` и `default_tpl` без runtime bootstrap.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/MetaFilesTest.php unit/core/AiToolingTest.php
php fan ai:map --json
```

### P12. Real static analysis tools

Статус: выполнено в P12.

Цель: сделать `tools/ai_static_check.php` не только config guard, но реальным static-analysis gate.

Сделано:

1. Установить dev tools:

   ```bash
   php composer.phar require --dev phpstan/phpstan friendsofphp/php-cs-fixer
   ```

2. `tools/ai_static_check.php` запускает реальные binaries через `PHP_BINARY`.
3. PHPStan переведен в sandbox-friendly `--debug --no-progress` режим.
4. Создан generated `phpstan-baseline.neon` на 22 существующие legacy errors.
5. PHP-CS-Fixer оставлен как безопасный baseline check без рискованной массовой правки legacy-кода.
6. Исправлен реальный PHPStan blocker: `project/block/admin/root.php` больше не наследуется от собственного alias.

Проверка:

```bash
php tools/ai_static_check.php
php tools/ai_verify.php
```

### P13. AI map schema and optional snapshot

Статус: выполнено в P13.

Цель: закрепить формат `ai_map` для будущих AI tools и CI.

Сделано:

1. Добавлен `.ai/map.schema.json`.
2. Добавлен `php fan ai:map --validate`.
3. `tools/ai_verify.php` валидирует AI map contract во время `ai_map_build`.
4. Решение по snapshot: committed `.ai/map.json` пока не нужен из-за `generated_at` churn; `--write` оставлен для release/update процесса.

Проверка:

```bash
php fan ai:map --json
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
```

### P14. EntityLookup collaborator for model rows

Статус: выполнено 2026-06-12.

Цель: убрать прямые `$entity->getService()->getEntityByTable()` из `core/base/model/row.php` и уменьшить `getService()` allowlist.

Почему это первый следующий шаг:

- `EntityIdCodec` уже закрыт в P10;
- `getEntityByTable()` остался ровно в двух runtime call sites:
  - `row::getTopRow()`;
  - `row::getBottomRowset()`;
- поведение можно покрыть локальными unit tests без поднятия полного app bootstrap.

План:

1. Добавить в `core/base/model/entity.php` метод-границу, например `findEntityByTable(string $tableName, ?string $connectionName = null): ?object`.
2. Поддержать optional collaborator/callable, например `entityLookup`, в `entity::__construct()` и `setEntityDependencies()`.
3. Прокинуть default lookup из `model_entity_factory` через `$entityService->getEntityByTable(...)`.
4. Заменить в `core/base/model/row.php`:
   - `$ett->getService()->getEntityByTable(...)`;
   - `$curEtt->getService()->getEntityByTable(...)`;
   на `$ett->findEntityByTable(...)` / `$curEtt->findEntityByTable(...)`.
5. Добавить/обновить tests:
   - `unit/core/base/model/EntityTest.php`;
   - `unit/core/base/model/RowTest.php`;
   - `unit/core/di/EntityModelFactoriesTest.php`;
   - `unit/core/LegacyDiSourceInventoryTest.php`.
6. Уменьшить allowlist в `testModelServiceLocatorUsageIsPinnedToMigrationAllowlist()`:
   - `core/base/model/row.php`: с `3` до `1`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/base/model/EntityTest.php unit/core/base/model/RowTest.php unit/core/di/EntityModelFactoriesTest.php unit/core/LegacyDiSourceInventoryTest.php
php tools/ai_verify.php --changed
```

Итог:

- `entity::findEntityByTable()` добавлен как явная граница;
- `model_entity_factory` передает default lookup из entity service;
- `row::getTopRow()` и `row::getBottomRowset()` больше не вызывают `getService()->getEntityByTable()`;
- `core/base/model/row.php` allowlist снижен с `3` до `1`.

### P15. PHPUnit guard for raw service ids in `core/di`

Статус: выполнено 2026-06-12.

Цель: закрепить P9 не grep-командой, а постоянным regression guard.

План:

1. Добавить тест в `unit/core/LegacyDiSourceInventoryTest.php` или отдельный `unit/core/di/ServiceIdSourceInventoryTest.php`.
2. Проверять production files под `core/di` на запрет:
   - `->factory('...')`;
   - `->alias('...')`;
   - `$container->get('...')`;
   - `$this->container()->get('...')`;
   - `$this->context()->container()->get('...')`.
3. Разрешать raw strings только там, где это не service id:
   - config section names;
   - project class suffixes вроде `getProjectServiceClassName('curl')`;
   - runtime scalar arguments.
4. Если появятся false positives, добавлять узкий allowlist с file+pattern, а не общий skip.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/LegacyDiSourceInventoryTest.php
php tools/ai_verify.php --changed
```

Итог: `LegacyDiSourceInventoryTest::testCoreDiServiceIdsUseNamedConstants()` закрепляет запрет на raw service-id strings для production files в `core/di`.

### P16. PHPStan baseline burn-down, first 3 entries

Статус: выполнено 2026-06-12.

Цель: начать уменьшать `phpstan-baseline.neon`, не расширяя suppression.

Первый порядок лучше такой:

1. `core/error/demonstrator.php`: исправить undefined `$file`/`$line`.
   - Это, вероятно, настоящий bug/edge-case.
   - Риск низкий: локальная ошибка переменных.
2. `core/service/obfuscator.php`: объявить/инъецировать `$engine` явно или заменить доступ на существующий accessor/state.
   - Риск средний: надо понять runtime contract сервиса.
3. `core/base/model/entity.php`: решить `designer` type alias.
   - Варианты: import/rename concrete designer type, interface, или осознанный docblock.
   - Риск средний: затрагивает model query API.

Правило: после каждого fixed error перегенерировать baseline и проверять, что count уменьшается, а не переписывается шумом.

Проверка:

```bash
php tools/ai_static_check.php
php tools/ai_verify.php --changed
```

Итог:

- `core/error/demonstrator.php`: `$file`/`$line` инициализируются перед `headers_sent`;
- `core/service/obfuscator.php`: добавлено явное поле `$engine`;
- `core/base/model/entity.php`: фантомный `designer` type заменен на `object` contract с проверкой `assemble()`/`getAdjustedParam()`;
- `phpstan-baseline.neon` уменьшен на закрытые entries.

### P17. Expand AI map descriptors to constructor/factory origins

Статус: выполнено 2026-06-12.

Цель: сделать `ai_map.services.descriptors` полезнее для автоматического рефакторинга DI.

План:

1. Для каждого service descriptor добавить:
   - `factory_origin`: registrar file + creator method/factory callable;
   - `factory_arguments`: container deps vs runtime args;
   - `shared`: уже есть, сохранить;
   - `aliases`: обратная связь alias -> target.
2. В `.ai/map.schema.json` добавить новые optional fields.
3. Обновить `php_fan_ai_validate_map_contract()`.
4. Добавить assertions в `unit/core/AiToolingTest.php`.

Проверка:

```bash
php fan ai:map --validate --json
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php tools/ai_verify.php --changed
```

Итог: service descriptors теперь включают `factory_origin`, `factory_arguments.container_dependencies`, `factory_arguments.runtime_arguments` и обратные `aliases`; schema и contract validation обновлены.

### P18. Next entity service-locator extractions after `EntityLookup`

Статус: выполнено 2026-06-12.

Цель: продолжать выносить hidden service calls из `entity.php` по одному use case.

Порядок:

1. `EntityDescriptionProvider`: заменить `getDescription()` service call.
2. `EntityDesignerFactory`: заменить `getDesigner()` service call.
3. `EntityNamespaceResolver`: закрыть оставшиеся `getNsPrefix()` call sites или явно закрепить их как BC-boundary.
4. `EntityMainParamProvider`: заменить `getCollectionKey()` в `getMainParam()`.

Правило: каждый срез должен уменьшать `getService()` allowlist или документировать, почему конкретный call site остается BC-boundary.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/base/model/EntityTest.php unit/core/LegacyDiSourceInventoryTest.php
php tools/ai_verify.php --changed
```

Итог:

- `getDescription()` идет через injected `descriptionProvider`;
- `getDesigner()` идет через injected `designerFactory`;
- `getNsPrefix()` закрыт `namespacePrefixResolver`;
- `getCollectionKey()` закрыт `collectionKeyProvider`;
- legacy fallback-и внутри `entity.php` оставлены как BC-boundary до следующего среза.

### P19. Collapse `entity.php` BC fallback count

Статус: выполнено 2026-06-12.

Цель: оставить `entity::getService()` как BC-accessor, но убрать внутренние `->getService()->...` fallback-и из `core/base/model/entity.php`.

Почему это следующий лучший шаг:

- P14-P18 уже добавили collaborators для всех больших service calls;
- текущий `LegacyDiSourceInventoryTest` все еще разрешает `core/base/model/entity.php => 7`;
- эти 7 call sites теперь можно заменить default closures в constructor/factory без изменения внешнего API.

План:

1. Добавить `sqlDirectoryProvider` callable для `getSqlDirectory()`.
2. В `entity::__construct()` собрать default collaborators из `$service`, если они не переданы явно:
   - `entityIdDecoder`;
   - `entityLookup`;
   - `designerFactory`;
   - `descriptionProvider`;
   - `namespacePrefixResolver`;
   - `collectionKeyProvider`;
   - `sqlDirectoryProvider`.
3. В методах `decodeEntityId()`, `findEntityByTable()`, `createDesigner()`, `loadDescription()`, `entityNamespacePrefix()`, `entityCollectionKey()`, `getSqlDirectory()` вызывать только collaborator.
4. Если collaborator не настроен, бросать явный `RuntimeException` с именем missing dependency.
5. Обновить `model_entity_factory` и `EntityModelFactoriesTest`.
6. Уменьшить allowlist:
   - `core/base/model/entity.php`: с `7` до `0`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/base/model/EntityTest.php unit/core/di/EntityModelFactoriesTest.php unit/core/LegacyDiSourceInventoryTest.php
php tools/ai_verify.php --changed
```

Итог:

- `sqlDirectoryProvider` добавлен в `entity.php` и `model_entity_factory`;
- default collaborators собираются в constructor из entity service;
- `decodeEntityId()`, `findEntityByTable()`, `createDesigner()`, `loadDescription()`, `entityNamespacePrefix()`, `entityCollectionKey()` и `getSqlDirectory()` больше не делают fallback через `getService()`;
- `core/base/model/entity.php` снят с `getService()` allowlist.

### P20. PHPStan baseline burn-down #2

Статус: выполнено 2026-06-12.

Цель: уменьшить `phpstan-baseline.neon` с 11 entries, не добавляя новых suppressions.

Рекомендуемый порядок:

1. `core/adapter/pear_http_session.php` + `pear_http_session_loader.php`.
   - Закрыть 9 entries вокруг неизвестного `HTTP_Session`.
   - Лучший путь: typed adapter/facade для PEAR session static API + явная availability boundary.
   - Не лучший путь: расширять baseline или размазывать `class_exists()` по callers.
2. `core/base/model/file_data/row.php`.
   - Закрыть `entity_member::getCurrentMember()` через collaborator, похожий на `EntityIdDecoder`.
   - Это уменьшит coupling file-data row к фантомному runtime entity class.
3. `core/service/timer.php`.
   - Заменить `fan\model\timer_program\row` phantom type на object contract или local interface-like assertion.

Проверка:

```bash
php tools/ai_static_check.php --json
php vendor/bin/phpunit --configuration phpunit.xml unit/core/base/model/file_data/RowTest.php unit/core/service/TimerTest.php
```

Итог:

- `pear_http_session` больше не содержит raw `HTTP_Session::` calls; PEAR static API закрыт callable-boundary;
- `pear_http_session_loader` создает adapter без несуществующего `ensureAvailable()`;
- `file_data/row.php::setPersonalAccess()` использует injected current user collaborator вместо `entity_member::getCurrentMember()`;
- `timer::_runProgram()` больше не типизирован phantom class `fan\model\timer_program\row`;
- `phpstan-baseline.neon` уменьшен с 11 entries до пустого `ignoreErrors: []`.

### P21. AI map JSON Schema validation runner

Статус: выполнено 2026-06-12.

Цель: `php fan ai:map --validate` должен проверять не только hand-written contract, но и `.ai/map.schema.json` как schema contract.

План:

1. Добавить `php_fan_ai_validate_json_schema_subset()` или подключаемый validator boundary.
2. Валидировать generated map против `.ai/map.schema.json`.
3. В ошибках возвращать path-like указатели: `services.descriptors.cache.factory_arguments.runtime_arguments`.
4. Добавить negative fixture test: искусственно удалить `factory_origin` и проверить понятную ошибку.
5. Обновить `.ai/commands.md`, если появится отдельная команда.

Проверка:

```bash
php fan ai:map --validate --json
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
```

Итог:

- добавлен локальный `php_fan_ai_validate_json_schema_subset()`;
- `php_fan_ai_validate_map_contract()` теперь прогоняет generated map против `.ai/map.schema.json`;
- errors получают path-like указатели вроде `$.services.descriptors.cache...`;
- добавлен negative test на drift service descriptor shape.

### P22. Runtime argument descriptors for creator methods

Статус: выполнено 2026-06-12.

Цель: сейчас `factory_arguments.runtime_arguments` извлекается из registration closures; следующий слой - signatures public creator methods.

План:

1. Расширить `php_fan_ai_service_creator_methods()`:
   - `parameters`;
   - `runtime_arguments`;
   - `container_dependencies`;
   - `optional_arguments`.
2. Добавить в descriptor `creator_method_arguments`, keyed by method name.
3. Для creator methods классифицировать:
   - `container_interface $container` как container boundary;
   - scalar/mixed method params как runtime args;
   - params with default values как optional args.
4. Добавить assertions на `createCacheService`, `createDateService`, `createSessionService`.

Проверка:

```bash
php tools/ai_map.php --json
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
```

Итог:

- `php_fan_ai_service_creator_methods()` извлекает `parameters`, `runtime_arguments`, `container_dependencies`, `optional_arguments`;
- service descriptors получили `creator_method_arguments`, keyed by method name;
- `.ai/map.schema.json` описывает новый shape;
- тесты закрепляют `createCacheService`, `createDateService`, `createSessionService`.

### P23. Source inventory for direct service property access

Статус: выполнено 2026-06-12.

Цель: после уменьшения `getService()` allowlist закрепить прямые обращения к service state как явные BC-boundaries.

План:

1. Добавить guard в `LegacyDiSourceInventoryTest`.
2. Начать с узкого scope `core/base/model/**/*.php`.
3. Запрещать:
   - `$this->service->...`;
   - `$entity->service`;
   - прямое чтение/запись service property вне constructor/BC accessor.
4. Разрешить только documented boundaries:
   - `protected ?object $service`;
   - `public function getService(): object`;
   - constructor assignment `$this->service = $service`.
5. После model scope расширить на `core/base/service` отдельным P-срезом.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/LegacyDiSourceInventoryTest.php
php tools/ai_verify.php --changed
```

Итог:

- `LegacyDiSourceInventoryTest::testModelServicePropertyAccessIsPinnedToEntityBoundary()` запрещает direct service property access в `core/base/model/**`;
- разрешены только `entity.php` constructor assignment и BC getter boundary;
- guard закрепляет, что новые model collaborators не обходятся прямым `$this->service`.

## Рекомендуемые следующие PR-срезы после P334-P338

P334-P338 закрыли completion-hardening pass:

- P334 закрепил `php fan ai:source-inventory --next --json` как `[]`;
- P335 зафиксировал bootstrap/default-provider surface как classified boundary: `25` lookups / `7` files;
- P336 зафиксировал terminal composition leaves: `272` composition lookups, `290` terminal leaves, `--composition-open []`;
- P337 зафиксировал loading boundaries: intentional `12 / 7`, tooling `7 / 4`, entrypoint `1 / 1`, unmanaged loading `0`;
- P338 собрал final audit proof и добавил completion workflow в `.ai/architecture.md` и `.ai/commands.md`.

Измеренный 2026-06-13 completion readout после P334-P338:

- `php fan ai:dynamic-boundaries --next --json`: `[]`;
- `php fan ai:dynamic-boundaries --composition-open --json`: `[]`;
- `php fan ai:dynamic-boundaries --debt --json`: `[]`;
- `php fan ai:source-inventory --next --json`: `[]`;
- `service_locator_calls`: `clean`, count `0`;
- `unmanaged_container_lookups`: `clean`, count `0`;
- `unmanaged_loading_statements`: `clean`, count `0`;
- `bootstrap_container_lookups`: `bootstrap_boundary`, count `25` / `7` files;
- `composition_container_lookups`: `composition_boundary`, count `272` / `272` files;
- `intentional_loading_boundaries`: `intentional_compatibility`, count `12` / `7` files;
- `tooling_loading_statements`: `tooling_support`, count `7` / `4` files;
- `entrypoint_loading_statements`: `entrypoint_boundary`, count `1` / `1` file;
- `explicit_native_construction_boundaries`: `intentional_compatibility`, count `0`.

Вывод: обязательный AI-first code refactor route закрыт. Оставшиеся non-zero очереди не являются refactor backlog: они classified/documented bootstrap, composition, loading, tooling и entrypoint boundaries. Новый код должен сохранять completion audit зеленым; если какая-то actionable queue снова станет non-empty, новый measured refactor route начинается именно с нее.

Следующий лучший ход: post-completion maintenance, чтобы этот результат было проще удерживать и выпускать.

| PR | Название | Почему следующий | Основная проверка |
|---:|---|---|---|
| 1 | P339 Package final migration report | Свести completion proof, changed surfaces и команды в короткий release-ready отчет. | migration plan + `php tools/ai_verify.php --json` |
| 2 | P340 Add boundary-owner index | Добавить owner/intent index для bootstrap/composition/loading queues, чтобы будущий AI не открывал широкие файлы без маршрута. | `.ai/architecture.md`; `AiToolingTest` |
| 3 | P341 Tighten new-code contributor checklist | Превратить completion audit в checklist для новых DI/service changes. | `.ai/conventions.md`; docs guards |
| 4 | P342 Measure AI-map runtime and cache candidates | После роста source inventory проверить, не стал ли `ai_map` слишком медленным для agents. | timed `php tools/ai_map.php --json`; no behavior changes |
| 5 | P343 Optional bootstrap-boundary experiment | Только если нужен следующий реальный code refactor: выбрать один bootstrap boundary и доказать, стоит ли его переносить или оставить intentional. | `bootstrap_container_lookups` queue; focused bootstrap tests |

Definition of done для post-completion maintenance:

- release report summarizes why code refactor is complete;
- boundary-owner index explains every remaining non-zero classified queue;
- contributor checklist prevents regressions into unmanaged source debt;
- AI-map performance is measured rather than guessed;
- optional bootstrap experiment creates a new measured route only if evidence shows it is worth changing.

### P24. Remove `file_data/row.php` service-locator tail

Статус: выполнено 2026-06-12.

Итог:

- `entity::fileDataRowDependencies()` добавлен как явная boundary;
- `model_entity_factory` прокидывает default dependency provider из entity service;
- `file_data/row.php::setDependenciesFromEntityService()` больше не вызывает `$entity->getService()`;
- source tests закрепляют отсутствие `->getService()` в file-data row.

### P25. Extract spec-file model factories

Статус: выполнено 2026-06-12.

Итог:

- `entity::specFileImageRowDependencies()` закрывает dependency bootstrapping для image row;
- `entity::createRelatedEntityRow(string $entityName): object` закрывает related row creation;
- `spec_file/row.php` и `spec_file/image/entity.php` больше не создают related rows через service locator;
- spec-file source tests закрепляют новый factory boundary и failure message.

### P26. Remove last `core/base/model/row.php` service access

Статус: выполнено 2026-06-12.

Итог:

- `entity::rowDependencies()` добавлен как явная dependency boundary;
- `row.php::setDependenciesFromEntityService()` больше не вызывает `$entity->getService()`;
- `LegacyDiSourceInventoryTest::testModelServiceLocatorUsageIsPinnedToMigrationAllowlist()` теперь ожидает пустой allowlist.

### P27. Service descriptor concrete edges

Статус: выполнено 2026-06-12.

Итог:

- `service_descriptor` расширен полями `class`, `factory`, `configKey`, `lifetimeReason`, `sourceEdges`;
- `tools/ai_map.php` извлекает common project service classes, factory parameters и config keys;
- `.ai/map.schema.json` и hand-written contract validation требуют новый descriptor shape;
- `AiToolingTest` закрепляет concrete edges для `cache`, `request`, `date`, `session`.

### P28. Extend service-state source inventory to `core/base/service`

Статус: выполнено 2026-06-12.

Итог:

- inventory `rg --line-number -- '->service\b|\$service\b' core/base/service` не нашел текущих production matches;
- `LegacyDiSourceInventoryTest::testBaseServiceLayerDoesNotUseServiceStateLocatorAliases()` закрепляет пустой allowlist для `core/base/service/**`;
- P28 остался guard-only срезом без runtime refactor.

### P29. Collapse model entity collaborator list

Статус: выполнено 2026-06-12.

Итог:

- добавлен `core/base/model/entity_dependencies.php`;
- `entity::__construct()` и `setEntityDependencies()` принимают named dependency object, сохраняя legacy callable path;
- `model_entity_factory` создает `entity_dependencies` и передает один object вместо callables 12..22;
- `EntityModelFactoriesTest` проверяет named dependencies, а не numeric tail;
- `CoreSourceInventoryTest` классифицирует новый production file.

### P30. Split AI map service parsing into builder API

Статус: выполнено 2026-06-12.

Итог:

- добавлен `tools/ai_service_map.php`;
- `php_fan_ai_service_map()` теперь делегирует orchestration в `php_fan_ai_service_map_builder`;
- CLI/tool contract сохранен: `php fan ai:map --validate --json` проходит без изменения command surface.

### P31. Add descriptor provenance line edges

Статус: выполнено 2026-06-12.

Итог:

- `service_descriptor` получил `sourceLocations`;
- `.ai/map.schema.json` требует `source_locations`;
- `tools/ai_map.php` добавляет registration/creator/class/factory/config-key locations;
- `AiToolingTest` закрепляет provenance для `cache` и `date`;
- старые `source_edges` сохранены для совместимости.

### P32. Tighten model collaborator contracts with typed doubles

Статус: выполнено 2026-06-12.

Итог:

- `EntityTest` покрывает invalid result для `rowDependencies()`;
- `EntityTest` покрывает invalid result для `fileDataRowDependencies()`;
- `EntityTest` покрывает invalid result для `specFileImageRowDependencies()`;
- `EntityTest` покрывает invalid result для `createRelatedEntityRow()`;
- failure messages закреплены как явный AI-facing contract.

### P33. Extend service-state inventory to root `core/base/service.php` source shapes

Статус: выполнено 2026-06-12.

Итог:

- inventory root `core/base/service.php` показывает только typed `service_listener_state`/`service_single_state` boundaries;
- `LegacyDiSourceInventoryTest::testRootBaseServiceStateShapesArePinnedToInjectedStateBoundaries()` запрещает возврат `$listeners`, `$instances`, `->service` и `$service` aliases;
- P33 остался guard-only без runtime rewrite.

### P34. Guard model entity factory against positional collaborator tail regressions

Статус: выполнено 2026-06-12.

Итог:

- `LegacyDiSourceInventoryTest::testModelEntityFactoryUsesNamedEntityDependenciesInsteadOfPositionalTail()` закрепляет `new entity_dependencies(` в `model_entity_factory`;
- guard проверяет, что call into configured service factory получает один named object в collaborator slot;
- прежние collaborator variables не могут снова появиться в positional tail около factory call;
- `EntityModelFactoriesTest` остается runtime-проверкой, source guard закрывает regression shape.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/EntityModelFactoriesTest.php unit/core/LegacyDiSourceInventoryTest.php
```

### P35. Move descriptor extraction helpers into service-map builder module

Статус: выполнено 2026-06-12.

Итог:

- service descriptor extraction helpers теперь живут в `tools/ai_service_map.php`;
- `tools/ai_map.php` оставлен orchestration/common-map entrypoint и делегирует `new php_fan_ai_service_map_builder(...)`;
- JSON shape не изменен;
- `AiToolingTest::testAiServiceMapBuilderOwnsServiceDescriptorExtractionHelpers()` закрепляет ownership boundary.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:map --validate --json
```

### P36. Improve source_locations to exact edge lines

Статус: выполнено 2026-06-12.

Итог:

- `php_fan_ai_extract_public_creator_methods()` сохраняет `body_line` отдельно от declaration `line`;
- class/config source locations считают line от body offset и указывают на фактические строки `getProjectServiceClassName(...)` / `$config->get(...)`;
- `AiToolingTest` сверяет lines с реальным source для `cache` и `date`;
- factory parameter locations позже уточнены в P40 до строк конкретных `$...Factory` parameters.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php tools/ai_map.php --json
```

### P37. Add service descriptor explain path

Статус: выполнено 2026-06-12.

Итог:

- добавлен `php_fan_ai_service_descriptor(string $root, string $serviceId): ?array`;
- `php fan ai:services [service-id] [--json]` поддерживает single-service lookup;
- JSON path возвращает один descriptor, а не полный graph;
- `AiToolingTest::testFanCliBridgeRunsAiCommands()` проверяет `php fan ai:services cache --json`.

Проверка:

```bash
php fan ai:services cache --json
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
```

### P38. Inventory dynamic reflection/configured-service boundaries

Статус: выполнено 2026-06-12.

Итог:

- собран inventory по `ReflectionClass`, `configured_service_factory`, `configured_class_instantiator`, `class_exists`;
- `LegacyDiSourceInventoryTest::testDynamicReflectionAndConfiguredServiceBoundariesAreInventoried()` закрепляет текущий allowlist production/tooling boundaries;
- guard не запрещает documented extension points, но делает новые dynamic construction call sites видимыми;
- policy/refactor split закрыт в P42-P43.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/LegacyDiSourceInventoryTest.php
php tools/ai_verify.php --changed
```

### P39. Move remaining service-map parser-only helpers behind builder ownership

Статус: выполнено 2026-06-12.

Итог:

- parser helpers для fluent factory calls, creator methods, parameter parsing, source locations и service id constants перенесены в `tools/ai_service_map.php`;
- `tools/ai_map.php` оставлен common project map entrypoint и metadata/template owner;
- `tools/ai_explain.php` продолжает получать service dependency helpers через `ai_map.php` -> `ai_service_map.php`;
- `AiToolingTest::testAiServiceMapBuilderOwnsServiceDescriptorExtractionHelpers()` запрещает drift service parser helpers обратно в `tools/ai_map.php`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:map --validate --json
```

### P40. Add exact source locations for factory argument edges

Статус: выполнено 2026-06-12.

Итог:

- `php_fan_ai_extract_public_creator_methods()` сохраняет `parameters_line`;
- `php_fan_ai_factory_parameter_locations_from_arguments()` считает line по offset конкретного `$...Factory` parameter token;
- `source_locations.factories` для `cache` теперь указывает на строки `callable $cacheEngineFactory` и `callable $cacheServiceFactory`, а не на declaration line метода;
- registration/runtime argument provenance выделена в следующий P44.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:services cache --json
```

### P41. Harden single-service CLI path with negative/format tests

Статус: выполнено 2026-06-12.

Итог:

- `AiToolingTest::testFanCliBridgeRunsAiCommands()` проверяет single descriptor JSON shape для `cache`;
- text output для `php fan ai:services cache` проверяет service summary, class и factory context;
- unknown service id возвращает exit code `1`, пустой STDOUT и readable STDERR;
- full `ai:services --json` behavior сохранен.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:services missing --json
```

### P42. Document reflection/configured-service boundary policy

Статус: выполнено 2026-06-12.

Итог:

- `.ai/architecture.md` получил раздел `Reflection And Configured-Service Boundaries`;
- policy разделяет dynamic calls на extension API, composition root и tooling support;
- everything else обозначено как migration debt;
- policy рекомендует named availability checker, `configured_class_instantiator`/factory boundaries и source-inventory guard для intentionally kept dynamic calls.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/LegacyDiSourceInventoryTest.php
php tools/ai_verify.php --changed
```

### P43. Start reducing dynamic construction debt in one narrow boundary

Статус: выполнено 2026-06-12.

Итог:

- `application_infrastructure_service_creator` получил injectable `projectServiceClassExists` boundary;
- direct `if (!class_exists($className))` в creator methods заменены на `$this->projectServiceClassExists($className)`;
- default `class_exists($className)` остался единственной BC implementation point внутри constructor default;
- `ApplicationInfrastructureServiceCreatorTest::testProjectServiceClassAvailabilityCheckIsInjected()` проверяет runtime behavior;
- `LegacyDiSourceInventoryTest::testInfrastructureCreatorUsesNamedProjectServiceAvailabilityBoundary()` закрепляет source shape.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/LegacyDiSourceInventoryTest.php
php tools/ai_verify.php --changed
```

### P44. Add source locations for registration runtime argument edges

Статус: выполнено 2026-06-12.

Итог:

- добавлен extractor `php_fan_ai_runtime_argument_locations_from_source()`;
- `source_locations.runtime_arguments` стал обязательной частью descriptor schema;
- `cache` descriptor показывает runtime arg `type` на строке registration closure;
- `AiToolingTest` сверяет line provenance с реальной строкой registrar source.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:services cache --json
```

### P45. Export categorized dynamic boundaries in AI map

Статус: выполнено 2026-06-12.

Итог:

- добавлен `php_fan_ai_dynamic_boundaries_map()`;
- `dynamic_boundaries.categories` экспортирует `extension_api`, `composition_roots`, `tooling_support`, `migration_debt`;
- `.ai/map.schema.json` требует categories shape;
- `AiToolingTest` проверяет representative files в каждой категории.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:map --validate --json
```

### P46. Continue reducing direct project service class checks in one more DI creator

Статус: выполнено 2026-06-12.

Итог:

- выбран `application_content_service_creator` как узкий one-method boundary;
- добавлен injectable `projectServiceClassExists`;
- direct method-body `if (!class_exists($className))` заменен на `$this->projectServiceClassExists($className)`;
- `ApplicationContentServiceCreatorTest::testProjectServiceClassAvailabilityCheckIsInjected()` покрывает runtime behavior;
- `LegacyDiSourceInventoryTest::testContentCreatorUsesNamedProjectServiceAvailabilityBoundary()` закрепляет source shape.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/LegacyDiSourceInventoryTest.php
php tools/ai_verify.php --changed
```

### P47. Add source locations for container dependency edges

Статус: выполнено 2026-06-12.

Итог:

- добавлен `php_fan_ai_container_dependency_locations_from_source()`;
- `source_locations.dependencies` стал обязательной частью descriptor schema;
- dependency locations собираются и из registration closure, и из creator method body;
- self-edges фильтруются, чтобы `source_locations.dependencies` не противоречил top-level `dependencies`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:services cache --json
```

### P48. Add AI explain support for dynamic-boundary category hints

Статус: выполнено 2026-06-12.

Итог:

- добавлен `php_fan_ai_dynamic_boundary_category_for_file()`;
- `php fan ai:explain <file> --json` возвращает `dynamic_boundary_category`;
- старый `dynamic_boundaries` boolean summary сохранен;
- `AiToolingTest` проверяет `composition_roots`, `extension_api` и non-boundary `null`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:explain core/di/application_infrastructure_service_creator.php --json
```

### P49. Add dependency/runtime argument lists to `source_edges`

Статус: выполнено 2026-06-12.

Цель: синхронизировать `source_edges` с новыми `source_locations.dependencies` и `source_locations.runtime_arguments`.

Итог:

- `dependencies` и `runtime_arguments` добавлены в `source_edges`;
- `service_descriptor` docblock и `.ai/map.schema.json` обновлены;
- `AiToolingTest` проверяет, что `source_edges.dependencies` совпадает с top-level `dependencies`;
- `AiToolingTest` проверяет, что `source_edges.runtime_arguments` совпадает с `factory_arguments.runtime_arguments`;
- `cache` descriptor закрепляет `service_id::CONFIG` в dependency edges и `type` в runtime argument edges.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:services cache --json
```

### P50. Guard dynamic-boundary category coverage against P38 inventory

Статус: выполнено 2026-06-12.

Цель: не дать category map отстать от dynamic boundary inventory.

Итог:

- P38 allowlist вынесен в `dynamicReflectionBoundaryInventoryFiles()`;
- добавлен guard `testDynamicReflectionBoundaryInventoryHasAiMapCategories()`;
- каждый inventoried dynamic boundary теперь должен иметь category в `php_fan_ai_dynamic_boundaries_map()['categories']`;
- `migration_debt` остается явной category, а не implicit fallback.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/LegacyDiSourceInventoryTest.php
php fan ai:map --validate --json
```

### P51. Continue project service availability boundary in controller creator

Статус: выполнено 2026-06-12.

Цель: продолжить P43/P46 на еще одном маленьком creator-е.

Итог:

- `application_controller_service_creator` переведен на injectable `projectServiceClassExists`;
- default `class_exists($className)` остался в constructor default boundary;
- focused runtime test проверяет injected checker и failure message;
- source guard закрепляет отсутствие direct method-body `if (!class_exists($className))`;
- общий trait/base class пока не введен: pattern еще намеренно остается локальным и проверяемым.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationControllerServiceCreatorTest.php unit/core/LegacyDiSourceInventoryTest.php
```

### P52. Add service-id cross-links to `ai:explain` for DI creators/registrars

Статус: выполнено 2026-06-12.

Цель: когда агент объясняет `core/di/*_service_creator.php` или registrar, сразу видеть affected service ids.

Итог:

- `ai:explain` получил поле `related_service_ids`;
- creator files связываются через `source_locations.creator_methods`;
- registrar files связываются через `registrar_files`;
- non-DI files сохраняют стабильный empty-array output;
- CLI JSON path проверяет наличие `related_service_ids`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:explain core/di/application_infrastructure_service_creator.php --json
```

### P53. Add alias/constant registration provenance

Статус: выполнено 2026-06-12.

Цель: довести registrar provenance для aliases/constants до того же уровня, что service registrations.

Итог:

- `services.aliases` теперь хранит `target`, `file` и `line`;
- `.ai/map.schema.json` требует alias `line`;
- добавлен temp fixture test для line provenance `->alias(...)`;
- registration location для `service_id::CACHE` закреплен against real source line;
- следующий P54 поднимет alias edges еще ближе к descriptors через `source_locations.aliases`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:map --validate --json
```

### P54. Add descriptor `source_locations.aliases`

Статус: выполнено 2026-06-12.

Цель: сделать alias provenance видимым прямо в descriptor, рядом с `registrations`, `classes`, `factories`, `config_keys`, `runtime_arguments` и `dependencies`.

Итог:

- `source_locations.aliases` добавлен в descriptor schema;
- target descriptor собирает alias name -> file/line;
- top-level `services.aliases` сохранен как global lookup;
- temp fixture test проверяет согласованность target descriptor `aliases` и `source_locations.aliases`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:map --validate --json
```

### P55. Add source locations for global referenced service ids

Статус: выполнено 2026-06-12.

Цель: `services.referenced` уже показывает used ids, но AI нужны file/line locations для references вне descriptor creator/registration body.

Итог:

- добавлен `services.referenced_locations`;
- extractor собирает `$container->get(...)`, `$this->container()->get(...)`, `$this->context()->container()->get(...)` и resolved `service_id::...` references;
- `.ai/map.schema.json` и hand-written validation требуют source-location map;
- `AiToolingTest` проверяет real line provenance для `service_id::CONFIG` reference.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:map --validate --json
```

### P56. Continue project service availability boundary in session/client creator

Статус: выполнено 2026-06-12.

Цель: продолжить P43/P46/P51 маленьким DI creator-срезом и не размазывать direct `class_exists($className)` по method bodies.

Итог:

- выбран `application_client_service_creator`, потому что там три одинаковых project class availability checks;
- добавлен injectable `projectServiceClassExists`;
- focused runtime test проверяет injected checker для `curl`;
- source inventory guard закрепляет один default `class_exists($className)` boundary и три вызова `$this->projectServiceClassExists($className)`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationSessionServiceCreatorTest.php unit/core/di/ApplicationClientServiceCreatorTest.php unit/core/LegacyDiSourceInventoryTest.php
```

### P57. Add dynamic-boundary reason/detail to `ai:explain`

Статус: выполнено 2026-06-12.

Цель: `dynamic_boundary_category` говорит "куда относится файл", но не объясняет "какой pattern найден и почему это допустимо".

Итог:

- добавлен `dynamic_boundary_details` в `ai:explain`;
- details возвращают `category`, `matched_entry`, `reason`, `patterns`;
- pattern list покрывает `class_exists`, `ReflectionClass`, `configured_service_factory`, `configured_class_instantiator`, `container_get`, `dynamic_new`, include/callback/eval families;
- text output остается compact и показывает только count/pattern summary.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:explain core/di/application_infrastructure_service_creator.php --json
```

### P58. Add compact related descriptor summaries to `ai:explain`

Статус: выполнено 2026-06-12.

Цель: после `related_service_ids` дать агенту краткий контекст по связанным services без отдельного full `ai:services` lookup.

Итог:

- добавлен `related_service_descriptors`;
- для каждого related id возвращаются stable поля `id`, `class`, `factory`, `config_key`, `dependencies`;
- explain строит project map один раз и переиспользует его для ids/summaries;
- tests покрывают creator/registrar examples и non-DI empty behavior.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:explain core/di/application_infrastructure_service_registrar.php --json
```

### P59. Add alias list to descriptor `source_edges`

Статус: выполнено 2026-06-12.

Цель: после P54 alias locations есть, но `source_edges` пока не отражает alias edges рядом с classes/dependencies/factories/config/runtime.

Итог:

- `aliases` добавлен в descriptor `source_edges`;
- `service_descriptor` docblock и `.ai/map.schema.json` обновлены;
- `AiToolingTest` проверяет, что `source_edges.aliases` совпадает с descriptor `aliases`;
- top-level `services.aliases` сохранен как global lookup.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:map --validate --json
```

### P60. Add file-local service reference locations to `ai:explain`

Статус: выполнено 2026-06-12.

Цель: P55 добавил global `services.referenced_locations`; теперь explain должен показывать references текущего файла без ручного поиска по full AI map.

Итог:

- `service_reference_locations` добавлен в `ai:explain`;
- locations фильтруются из `services.referenced_locations` по текущему file;
- output сгруппирован по service id и сохраняет line/value locations;
- tests покрывают DI creator и non-DI examples.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:explain core/di/application_infrastructure_service_creator.php --json
```

### P61. Continue project service availability boundary in session creator

Статус: выполнено 2026-06-12.

Цель: перевести `application_session_service_creator` на тот же named availability boundary, что infrastructure/content/controller/client creators.

Итог:

- добавлен injectable `projectServiceClassExists`;
- method-body `if (!class_exists($className))` заменен на named boundary;
- focused runtime test проверяет injected checker;
- source inventory guard закрепляет один default `class_exists($className)` boundary.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationSessionServiceCreatorTest.php unit/core/LegacyDiSourceInventoryTest.php
```

### P62. Schema-contract dynamic-boundary reasons/details

Статус: выполнено 2026-06-12.

Цель: `dynamic_boundaries.category_reasons` уже есть в AI map, но schema пока принимает его через broad `additionalProperties`; сделать shape явным.

Итог:

- `category_reasons` добавлен в `.ai/map.schema.json`;
- schema требует keys для всех categories;
- `AiToolingTest` закрепляет schema и map reasons;
- backward-compatible `categories` shape сохранен.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:map --validate --json
```

### P63. Add reverse reference summary to service descriptors

Статус: выполнено 2026-06-12.

Цель: single service descriptor должен показывать, какие production files reference this service id, чтобы AI видел blast radius без full `services.referenced_locations`.

Итог:

- добавлен descriptor field `referenced_by`;
- source data берется из `services.referenced` и `services.referenced_locations`;
- shape ограничен до `files` и `locations`;
- `cache` descriptor и CLI `ai:services cache --json` показывают reverse reference summary.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:services cache --json
```

### P64. Continue project service availability boundary in pager/navigation creators

Статус: выполнено 2026-06-12.

Цель: убрать direct method-body `class_exists($className)` из двух маленьких DI creators с single project class checks.

Итог:

- `application_pager_service_creator` переведен на injectable `projectServiceClassExists`;
- `application_navigation_service_creator` переведен тем же pattern-ом;
- добавлены runtime tests injected checker-ом;
- source inventory guard закрепляет один default `class_exists($className)` boundary в каждом creator-е.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationPagerServiceCreatorTest.php unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/LegacyDiSourceInventoryTest.php
```

### P65. Continue project service availability boundary in user creator

Статус: выполнено 2026-06-12.

Цель: перевести `application_user_service_creator` на named availability boundary и убрать direct `if (!class_exists($className))`.

Итог:

- добавлен injectable `projectServiceClassExists`;
- user creator focused runtime test проверяет injected checker;
- source guard добавлен рядом с existing creator boundary guards;
- shared abstraction пока не введен.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationUserServiceCreatorTest.php unit/core/LegacyDiSourceInventoryTest.php
```

### P66. Start utility creator availability boundary split

Статус: выполнено 2026-06-12.

Цель: начать перевод `application_utility_service_creator` без большого risky rewrite.

Итог:

- `application_utility_service_creator` переведен целиком, потому что все four checks используют один и тот же availability pattern;
- добавлен injectable `projectServiceClassExists`;
- focused runtime test проверяет injected checker через `createObfuscatorService`;
- source inventory guard закрепляет четыре calls через `$this->projectServiceClassExists($className)` и один default boundary.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationUtilityServiceCreatorTest.php unit/core/LegacyDiSourceInventoryTest.php
```

### P67. Add reference/alias edge consistency guard

Статус: выполнено 2026-06-12.

Цель: теперь edge families богаче; нужен generic guard, который проверяет consistency между list edges, locations и reverse-reference summaries.

Итог:

- `AiToolingTest::testAiMapReferenceAndAliasEdgesStayConsistent()` проверяет `source_edges.aliases === aliases`;
- проверяется, что `source_locations.aliases.value` соответствует descriptor aliases;
- проверяется, что `referenced_by.files` совпадает с files из `referenced_by.locations`;
- проверяется, что `service_reference_locations` explain subset совпадает с global `services.referenced_locations` for file.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:map --validate --json
```

### P68. Document reference provenance workflow

Статус: выполнено 2026-06-12.

Цель: `.ai/architecture.md` должен объяснять, когда AI-агенту смотреть `referenced_locations`, descriptor `referenced_by`, `service_reference_locations` и `related_service_descriptors`.

Итог:

- добавлен раздел `Service Reference Provenance`;
- разделены outgoing dependencies, aliases, global references, file-local references и reverse references;
- описаны команды `php fan ai:services <service-id> --json` и `php fan ai:explain <file> --json`;
- `AiToolingTest::testArchitectureDocsDescribeServiceReferenceProvenance()` закрепляет ключевые термины.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:explain core/di/application_infrastructure_service_creator.php --json
```

### P69. Start core creator availability boundary for request/role/reflector

Статус: выполнено 2026-06-12.

Цель: начать перевод большого `application_core_service_creator` на named availability boundary.

Итог:

- `application_core_service_creator` получил injectable `projectServiceClassExists`;
- request/role/reflector class availability checks переведены на named boundary в составе полного P69-P71 pass;
- `ApplicationCoreServiceCreatorTest::testProjectServiceClassAvailabilityCheckIsInjected()` проверяет injected checker и то, что factory не вызывается после negative availability result.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/LegacyDiSourceInventoryTest.php
```

### P70. Continue core creator availability boundary for application/debug/header/error

Статус: выполнено 2026-06-12.

Цель: продолжить тот же pattern в середине `application_core_service_creator`.

Итог:

- `createApplicationService` переведен на `$this->projectServiceClassExists($className)`;
- `createDebugService` переведен на `$this->projectServiceClassExists($className)`;
- `createHeaderService` переведен на `$this->projectServiceClassExists($className)`;
- `createErrorService` переведен на `$this->projectServiceClassExists($className)`;
- source guard для core creator закрепляет один constructor default `class_exists($className)` boundary.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/LegacyDiSourceInventoryTest.php
```

### P71. Finish core creator availability boundary for matcher/timer/locale

Статус: выполнено 2026-06-12.

Цель: убрать последние direct method-body `if (!class_exists($className))` из core creator.

Итог:

- `createMatcherService` переведен на named availability boundary;
- `createTimerService` переведен на named availability boundary;
- `createLocaleService` переведен на named availability boundary;
- `LegacyDiSourceInventoryTest::testCoreCreatorUsesNamedProjectServiceAvailabilityBoundary()` закрепляет 10 calls через `$this->projectServiceClassExists($className)` и отсутствие direct method-body check.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/LegacyDiSourceInventoryTest.php
```

### P72. Add guard against method-body direct `if (!class_exists($className))` in DI creators

Статус: выполнено 2026-06-12.

Цель: после закрытия core creator сделать regression невозможным для всех `core/di/*_service_creator.php`.

Итог:

- добавлен `LegacyDiSourceInventoryTest::testDiServiceCreatorsDoNotUseDirectMethodBodyProjectClassExistsChecks()`;
- direct method-body `if (!class_exists($className))` запрещен для всех `core/di/*_service_creator.php`;
- constructor default `static fn(string $className): bool => class_exists($className)` остается единственной BC implementation point в каждом converted creator-е.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/LegacyDiSourceInventoryTest.php
```

### P73. Refresh dynamic-boundary inventory after DI creator debt reduction

Статус: выполнено 2026-06-12.

Цель: после сокращения DI creator dynamic debt обновить inventory/policy так, чтобы AI map отражал новую реальность.

Итог:

- `.ai/architecture.md` обновлен: DI service creators теперь описаны как named `projectServiceClassExists(...)` boundary;
- direct method-body checks описаны как regression, а не как текущий долг;
- `AiToolingTest::testArchitectureDocsDescribeDiCreatorAvailabilityBoundaries()` закрепляет policy wording;
- `php fan ai:explain core/di/application_core_service_creator.php --json` подтверждает `composition_roots` category, named-boundary reason и related descriptors.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php
php fan ai:map --validate --json
```

### P74. Add line-level dynamic-boundary details to AI map

Статус: выполнено 2026-06-12.

Цель: сделать `dynamic_boundaries` не только category summary, но и точной картой file/line/pattern для dynamic calls.

Итог:

- добавлен `dynamic_boundaries.locations`;
- location shape: `file`, `line`, `pattern`, `value`;
- `.ai/map.schema.json` требует locations;
- hand-written contract validator проверяет dynamic location shape;
- line-level extractor игнорирует comments/strings и ищет actionable dynamic operations.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:map --validate --json
```

### P75. Reuse dynamic-boundary details in `ai:explain`

Статус: выполнено 2026-06-12.

Цель: чтобы `php fan ai:explain <file> --json` показывал exact dynamic-boundary locations для одного файла без отдельного grep.

Итог:

- `dynamic_boundary_details.locations` берется из AI map как file-local subset;
- `patterns` считаются из locations, а не отдельным broad regex;
- text output показывает count dynamic boundary locations;
- `AiToolingTest` сравнивает map/explain locations для DI creator.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:explain core/di/application_core_service_creator.php --json
```

### P76. Start reflector service reflection-factory boundary tightening

Статус: выполнено 2026-06-12.

Цель: начать сужать reflection debt вне DI creators, не ломая project extension behavior.

Итог:

- `core/service/reflector.php` уже использует injected reflection class factory;
- dynamic-boundary extractor больше не считает `\ReflectionClass` type hints runtime debt;
- `core/service/reflector.php` снят из `migration_debt` category;
- `php fan ai:explain core/service/reflector.php --json` теперь возвращает `dynamic_boundary_category = null` и empty details.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/LegacyDiSourceInventoryTest.php
php fan ai:explain core/service/reflector.php --json
```

### P77. Start model entity dynamic metadata boundary tightening

Статус: выполнено 2026-06-12.

Цель: начать уменьшать `core/base/model/entity.php` dynamic metadata/configured-class debt после уже закрытого service-locator хвоста.

Итог:

- `entity_dependencies` получил optional `modelClassExists`;
- `entity.php::_getClassName()` больше не вызывает direct `class_exists($className)`;
- model class availability спрятан за named lazy `modelClassExists(...)` boundary;
- runtime test проверяет injected availability checker и отсутствие fallback, когда project class считается доступным;
- source guards закрепляют отсутствие `!class_exists($className)` в model entity path.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/base/model/EntityTest.php unit/core/LegacyDiSourceInventoryTest.php
php fan ai:explain core/base/model/entity.php --json
```

### P78. Add guard for new dynamic-boundary details coverage

Статус: выполнено 2026-06-12.

Цель: после P74-P77 новые dynamic boundary details должны быть полными и не расходиться с allowlist/category reasons.

Итог:

- `AiToolingTest::testDynamicBoundaryLocationsHaveCategoryReasonsAndExplainSubsets()` проверяет category/reason для every map location;
- тот же guard проверяет, что `ai:explain` locations не расходятся с `ai:map`;
- `.ai/architecture.md` описывает `dynamic_boundaries.locations`, `dynamic_boundary_details.locations`, `modelClassExists(...)` и исключение plain `\ReflectionClass` type hints из runtime dynamic debt;
- focused suite после P74-P78: `347 tests`, `86590 assertions`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php
php fan ai:map --validate --json
```

### P79. Narrow adapter class availability boundaries

Статус: выполнено 2026-06-12.

Цель: начать сокращать class availability checks в adapter extension API без изменения loader behavior.

Итог:

- `core/adapter/bootstrap_loader_file_storage.php` получил injectable `symbolExists` boundary;
- default behavior сохраняет `class_exists/interface_exists/trait_exists(..., false)`;
- include/load behavior не изменен;
- runtime/source tests закрепляют named boundary и отсутствие прямого `return class_exists(...)` в публичном `symbolExists()`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/adapter/BootstrapLoaderFileStorageTest.php
php fan ai:explain core/adapter/bootstrap_loader_file_storage.php --json
```

### P80. Tighten optional extension service class checks

Статус: выполнено 2026-06-12.

Цель: оформить optional class checks в service extension points как named boundaries.

Итог:

- `core/service/plain.php` перенес controller class availability в `controllerClassExists(...)`;
- `core/factory/plain_service_factory.php` пробрасывает optional checker без нарушения прежнего argument order;
- runtime test подтверждает, что controller factory не вызывается, если injected checker вернул false;
- source guard запрещает возвращение `if (!class_exists($controllerClass))` в method body.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/service/PlainTest.php
php fan ai:map --validate --json
```

### P81. Split configured factory provider defaults

Статус: выполнено 2026-06-12.

Цель: уменьшить duplicated configured factory/instantiator construction в default-provider factories.

Итог:

- `application_runtime_factory_defaults_provider_factory` получил отдельные `configuredServiceFactoryProvider` и `classInstantiatorProvider`;
- default `configuredConstructionBoundaryFactory` теперь собирается через эти providers;
- BC path для injected `configuredConstructionBoundaryFactory` сохранен;
- runtime/source tests закрепляют provider split.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationRuntimeFactoryDefaultsProviderFactoryTest.php
php tools/ai_static_check.php --json
```

### P82. Start block base dynamic boundary reduction

Статус: выполнено 2026-06-12.

Цель: `core/block/base.php` имеет самый шумный non-DI dynamic-boundary profile; начать с одного narrow class availability path.

Итог:

- `_makeBlockException()` больше не вызывает direct `class_exists($class)`;
- class availability вынесен в `blockExceptionClassExists(...)`;
- boundary можно инъектировать через `setBlockDependencies(['blockExceptionClassExists' => ...])`;
- runtime/source tests закрепляют fallback на fatal exception через injected checker.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php
php fan ai:explain core/block/base.php --json
```

### P83. Add dynamic-boundary diff/readout command

Статус: выполнено 2026-06-12.

Цель: дать агенту короткую команду/вывод по dynamic-boundary inventory без чтения полного `ai:map`.

Итог:

- добавлен `php fan ai:dynamic-boundaries [--json]`;
- readout показывает `file`, `category`, `count`, `patterns`, `reason` в JSON mode;
- text mode показывает compact строку `file [category] N location(s): patterns`;
- AI map command list включает `fan_ai_dynamic_boundaries`;
- CLI bridge tests покрывают JSON и text output.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:dynamic-boundaries --json
```

### P84. Continue adapter availability-boundary narrowing

Статус: выполнено 2026-06-12.

Цель: продолжить уменьшать adapter dynamic boundary noise после `bootstrap_loader_file_storage`.

Итог:

- выбран `core/adapter/zend_autoloader.php`, потому что `project_tool_loader` и `compiled_template_loader` уже имели injectable boundaries;
- добавлены `classExists`, `isReadable`, `fileLoader` closures;
- static `zend_autoloader::load(...)` оставлен как BC shell над `loadPath(...)`;
- добавлен `unit/core/adapter/ZendAutoloaderTest.php`;
- source inventory allowlist обновлен: variable require теперь documented adapter boundary.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/adapter/ZendAutoloaderTest.php unit/core/adapter/ZendAutoloaderLoaderTest.php
php fan ai:dynamic-boundaries --json
```

### P85. Continue optional extension service checks

Статус: выполнено 2026-06-12.

Цель: повторить P80 для следующего service extension point.

Итог:

- выбран `core/adapter/pear_http_session.php`: один прямой `HTTP_Session` availability check и существующий adapter test;
- добавлен optional `httpSessionClassExists` checker;
- default static-call boundary сохранен;
- runtime test проверяет unavailable `HTTP_Session` без вызова static API;
- source guard запрещает возврат `if (!class_exists($className))`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/adapter/PearHttpSessionTest.php
php fan ai:explain core/adapter/pear_http_session.php --json
```

### P86. Reduce next `core/block/base.php` dynamic path

Статус: выполнено 2026-06-12.

Цель: продолжить уменьшать block base debt по одному безопасному path.

Итог:

- `setDynamicMeta()` больше не делает direct delayed-meta `class_exists`;
- добавлен injectable `delayedMetaClassExists` boundary через `setBlockDependencies(...)`;
- default behavior сохраняет `class_exists($className, false)`;
- runtime test проверяет, что delayed meta не разворачивается, когда checker возвращает false;
- source guard закрепляет named boundary.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php
php fan ai:explain core/block/base.php --json
```

### P87. Add dynamic-boundary summary regression guard

Статус: выполнено 2026-06-12.

Цель: сделать compact readout не просто CLI convenience, а stable contract для следующих refactor циклов.

Итог:

- добавлен direct helper test для `php_fan_ai_dynamic_boundary_summary(...)`;
- test закрепляет shape: `file`, `category`, `reason`, `count`, `patterns`;
- sort order закреплен как category + file;
- patterns проверяются как unique/sorted.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:dynamic-boundaries
```

### P88. Normalize configured provider split across remaining defaults

Статус: выполнено 2026-06-12.

Цель: после P81 найти оставшиеся default-provider factories с duplicated configured construction.

Итог:

- production scan показал configured construction только в двух default-provider roots:
  `application_runtime_factory_defaults_provider_factory.php` и `bootstrap_object_defaults_provider_factory.php`;
- оба уже используют `configuredServiceFactoryProvider` и `classInstantiatorProvider`;
- добавлен cross-file source guard, который закрепляет этот allowlist;
- guard дополнительно запрещает nested `new configured_service_factory(new configured_class_instantiator(...))` в этих roots.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/LegacyDiSourceInventoryTest.php unit/core/di/ApplicationRuntimeFactoryDefaultsProviderFactoryTest.php unit/core/application/BootstrapObjectDefaultsProviderFactoryTest.php
php tools/ai_static_check.php --json
```

### P89. Tighten `twig_template_service` optional class checks

Статус: выполнено 2026-06-12.

Цель: собрать Twig optional class availability checks за named boundary без изменения template rendering API.

Итог:

- `twig_template_service` получил optional `twigClassExists` dependency;
- `twig_template_file::fetch()` больше не содержит прямой тройной `class_exists(...)` chain;
- Twig availability проверяется через `twigClassesAvailable()`/`twigClassExists(...)`;
- `unit/core/adapter/TwigTemplateServiceTest.php` симулирует отсутствие Twig classes без Composer/environment mutation;
- readability check остается до Twig availability check.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/adapter/TwigTemplateServiceTest.php
php fan ai:explain core/adapter/twig_template_service.php --json
```

### P90. Tighten `translation` dynamic class resolution

Статус: выполнено 2026-06-12.

Цель: начать уменьшать dynamic class resolution в `core/service/translation.php` по одному безопасному path.

Итог:

- выбран service-tag/class-resolution branch в `_getTag(...)`;
- добавлен optional `translationClassExists` dependency через constructor и `setTranslationDependencies(...)`;
- `service|...` tag branch больше не вызывает direct project service `class_exists(...)` в method body;
- fallback behavior для неизвестных translation tags сохранен;
- stale `$callback` внутри tag loop сброшен на каждой итерации;
- test проверяет injected available path для `service|tab`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/service/TranslationTest.php
php fan ai:explain core/service/translation.php --json
```

### P91. Continue `core/block/base.php` container/dynamic reduction

Статус: выполнено 2026-06-12.

Цель: после class availability paths начать вынимать один `container_get`/factory fallback из block runtime.

Итог:

- выбран `templateFactory`, потому что `tab->getBlockDependencies()` уже передавал этот key, а `base::setBlockDependencies()` его игнорировал;
- `templateFactory` добавлен в explicit dependency propagation без добавления несуществующего `container->get('template')` fallback;
- runtime test проверяет, что `templateService(...)` использует injected factory и передает arguments;
- blast radius ограничен одним factory key.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php
php fan ai:dynamic-boundaries --json
```

### P92. Improve dynamic-boundary readout usefulness

Статус: выполнено 2026-06-12.

Цель: ускорить выбор следующего boundary без чтения полного map.

Итог:

- `php fan ai:dynamic-boundaries [file] [--json]` поддерживает optional file argument;
- file JSON mode возвращает `file`, `category`, `boundary_kind`, `reason`, `count`, `patterns`, `locations`;
- file text mode печатает summary и line-level locations;
- all-files JSON mode остался массивом summary entries;
- CLI bridge tests покрывают JSON/text file mode.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:dynamic-boundaries core/block/base.php --json
```

### P93. Reduce adapter file-loading inventory noise

Статус: выполнено 2026-06-12.

Цель: отличать low-level default closure locations from method-body dynamic debt для adapters с named file loaders.

Итог:

- добавлен `php_fan_ai_dynamic_boundary_kind_for_file(...)`;
- `compiled_template_loader`, `project_tool_loader`, `zend_autoloader` маркируются как `named_default_closure_boundary`;
- locations не скрываются из inventory;
- `ai:explain` dynamic details и `ai:dynamic-boundaries` summary показывают `boundary_kind`;
- `.ai/architecture.md` описывает, что named default closure boundary ранжируется ниже direct method-body debt.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:map --validate --json
```

### P94. Tighten translation debug tab availability

Статус: выполнено 2026-06-12.

Цель: закрыть прямой `class_exists('\fan\core\service\tab', false)` внутри `translationDebugAllowed()` отдельной named boundary.

Итог:

- добавлен `translationLoadedClassExists(...)`;
- default behavior сохраняет `class_exists($className, false)`;
- `translationDebugAllowed()` больше не содержит direct `class_exists`;
- focused test проверяет unavailable core tab без tab factory.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/service/TranslationTest.php
php fan ai:explain core/service/translation.php --json
```

### P95. Add Twig template file IO boundaries

Статус: выполнено 2026-06-12.

Цель: после P89 оставить Twig class availability named, но вынести template readability/reader за adapter dependencies.

Итог:

- `twig_template_service` и `twig_template_file` получили optional `isReadable`/`fileReader`;
- unreadable exception text сохранен;
- successful render test читает template через injected reader;
- source guard запрещает возврат direct `is_readable($this->templatePath)` и `file_get_contents($this->templatePath)` в `fetch()`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/adapter/TwigTemplateServiceTest.php
php fan ai:explain core/adapter/twig_template_service.php --json
```

### P96. Split `block/base` dependency resolution from runtime

Статус: выполнено 2026-06-12.

Цель: уменьшить шум `core/block/base.php` от `resolveBlockDependencies(...)`, где file-mode readout сейчас показывает основную массу `container_get`.

Итог:

- добавлен `core/block/base_dependency_resolver.php`;
- `base::resolveBlockDependencies(...)` делегирует resolver-у;
- constructor BC path сохранен;
- `core/block/base.php` file-mode dynamic count снизился до 2 named default closure locations;
- 43 container lookups остались видимыми в `base_dependency_resolver.php` как `composition_roots`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/service/TabTest.php
php fan ai:dynamic-boundaries core/block/base.php --json
```

### P97. Add dynamic-boundary kind filters/ranking

Статус: выполнено 2026-06-12.

Цель: сделать `boundary_kind` actionable: AI должен быстро получить direct method-body debt без уже обернутых default closures.

Итог:

- добавлен `php fan ai:dynamic-boundaries --direct --json`;
- добавлен общий `--kind=method_body_or_runtime_boundary|named_default_closure_boundary`;
- default all-files behavior сохранен;
- file mode поддерживает тот же filter;
- text mode печатает `boundary_kind` на каждой location.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:dynamic-boundaries --json
```

### P98. Add per-location boundary kind to map schema

Статус: выполнено 2026-06-12.

Цель: перенести `boundary_kind` с file summary/detail уровня на line-level locations, чтобы mixed files ранжировались точнее.

Итог:

- `dynamic_boundaries.locations[*].boundary_kind` стал required schema field;
- validator проверяет допустимые значения;
- `ai:explain` и `ai:dynamic-boundaries` используют actual location kinds для summary;
- `.ai/architecture.md` описывает `--direct` workflow.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:map --validate --json
```

### P99. Tighten `bootstrap_loader_file_storage` include boundary

Статус: выполнено 2026-06-13.

Цель: убрать direct `file_include` debt из `core/adapter/bootstrap_loader_file_storage.php` или явно перевести его в named default closure boundary.

Итог:

- добавлен injectable `fileLoader`;
- public `load()` теперь делегирует `($this->fileLoader)($path, $way)`;
- current include/include_once/require/require_once `way` semantics сохранены в default closure;
- `bootstrap_loader_file_storage.php` добавлен в named default closure file rank;
- `--direct` больше не показывает adapter include path как direct runtime debt.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/adapter/BootstrapLoaderFileStorageTest.php
php fan ai:dynamic-boundaries core/adapter/bootstrap_loader_file_storage.php --json
```

### P100. Tighten `locale` optional class availability

Статус: выполнено 2026-06-13.

Цель: вынести direct class availability checks из `core/service/locale.php` в named dependency.

Итог:

- добавлен optional `localeLoadedClassExists`;
- `_getSession(false)` больше не вызывает direct `class_exists('\fan\core\service\session', false)`;
- `_defineLanguage()` больше не вызывает direct `class_exists('\fan\core\service\matcher', false)`;
- no-autoload behavior сохранен через default closure;
- runtime test проверяет, что unavailable session class не вызывает session factory.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/service/LocaleTest.php
php fan ai:explain core/service/locale.php --json
```

### P101. Tighten `tab` optional class availability

Статус: выполнено 2026-06-13.

Цель: сократить direct class availability в `core/service/tab.php`.

Итог:

- добавлен optional `viewClassExists`;
- `_setViewClass()` больше не вызывает direct `class_exists($class, true)`;
- default autoloading behavior сохранен через default closure;
- runtime test проверяет, что injected checker может разрешить parser class без реального class load.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/service/TabTest.php
php fan ai:explain core/service/tab.php --json
```

### P102. Tighten `timer` optional class availability

Статус: выполнено 2026-06-13.

Цель: закрыть маленький direct class availability check в `core/service/timer.php`.

Итог:

- добавлен optional `timerClassExists`;
- `_runProgram()` больше не вызывает direct `class_exists($className)`;
- default autoloading behavior сохранен через default closure;
- runtime test проверяет unavailable timer program class без вызова program factory.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/service/TimerTest.php
php fan ai:explain core/service/timer.php --json
```

### P103. Reduce `core/base/transfer/int.php` migration debt

Статус: выполнено 2026-06-13.

Цель: вынести direct class availability check из `core/base/transfer/int.php`.

Итог:

- добавлен `ensure_transfer_int_class_loaded(...)`;
- compatibility autoload trigger сохранен;
- direct `class_exists(transfer_int::class);` заменен на named boundary;
- file-mode readout теперь `boundary_kind = named_default_closure_boundary`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/base/transfer/IntTest.php
php fan ai:dynamic-boundaries core/base/transfer/int.php --json
```

### P104. Tighten `core/service/block_context.php` class availability boundary

Статус: выполнено 2026-06-13.

Цель: закрыть последний production non-composition direct `class_exists` из `php fan ai:dynamic-boundaries --direct --json`.

Итог:

- добавлен injectable `projectTabClassExists`;
- `getCurrentBlockInfo()` больше не вызывает direct `class_exists('\fan\project\service\tab', false)`;
- public behavior сохранен: когда project tab class unavailable, возвращается `[null, null]`;
- file-mode readout стал `named_default_closure_boundary`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/service/BlockContextTest.php
php fan ai:dynamic-boundaries core/service/block_context.php --json
```

### P105. Decide `tools/composer_autoload.php` direct-boundary policy

Статус: выполнено 2026-06-13.

Цель: сделать explicit решение по tooling-support direct `class_exists`: оставить documented direct boundary или завернуть в named helper.

Итог:

- выбран wrapping вместо documented direct exception;
- добавлен `php_fan_composer_autoload_symbol_exists(...)`;
- project symbol checks сохраняют no-autoload behavior;
- core symbol checks сохраняют autoload behavior;
- `tools/composer_autoload.php` теперь readout-ится как `named_default_closure_boundary`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:dynamic-boundaries tools/composer_autoload.php --json
```

### P106. Add non-composition next-candidate report mode

Статус: выполнено 2026-06-13.

Цель: дать агенту короткую команду/readout для следующего refactor candidate после очистки production direct debt.

Итог:

- добавлен `php fan ai:dynamic-boundaries --next --json`;
- `--next` означает direct method-body/runtime locations без `composition_roots`;
- existing `--direct` behavior сохранен;
- helper-level summary filter и CLI path покрыты тестами;
- `.ai/architecture.md` описывает новый workflow.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:dynamic-boundaries --direct --json
```

### P107. Start extracting stable AI map helpers toward `core/ai`

Статус: выполнено 2026-06-13.

Цель: уменьшить рост `tools/ai_map.php`, не ломая CLI/schema contract.

Итог:

- добавлен `core/ai/dynamic_boundary.php`;
- `kindForFile`, `kindForLocation`, `kindForLocations`, `filterLocations` вынесены в `fan\core\ai\dynamic_boundary`;
- `tools/ai_map.php` оставляет compatibility wrapper functions;
- JSON/schema/CLI shape не менялся.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:map --validate --json
```

### P108. Plan first DI composition-root reduction strategy

Статус: выполнено 2026-06-13.

Цель: после cleanup runtime debt выбрать безопасную стратегию уменьшения composition-root шума без скрытия container wiring.

Итог:

- `--next` после P104-P108 возвращает `[]`;
- `--direct` показывает только composition roots;
- выбран следующий DI route: сначала ranked report, затем narrow bundle extraction в `application_core_service_creator`, guard, повтор на `application_navigation_service_creator`, re-score;
- visibility composition roots остается обязательной частью AI map.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/LegacyDiSourceInventoryTest.php unit/core/AiToolingTest.php
php fan ai:dynamic-boundaries --direct --json
```

### P109. Add DI composition-root ranked report

Статус: выполнено 2026-06-13.

Итог:

- `php fan ai:dynamic-boundaries --composition --json` добавлен как ranked composition-root readout;
- `--direct` не ослаблен и по-прежнему показывает direct dynamic boundaries;
- `tools/ai_map.php` умеет include-filter по categories и count-desc sorting;
- `unit/core/AiToolingTest.php` проверяет helper-level sorting и CLI JSON contract.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/AiToolingTest.php
php fan ai:dynamic-boundaries --composition --json
```

### P110. Split `application_core_service_creator` core dependency bundle

Статус: выполнено 2026-06-13.

Итог:

- добавлен `fan\core\di\application_creator_common_dependencies`;
- `application_core_service_creator` использует explicit bundle для `bootstrapRuntime()`, `config()` и `cacheFactory()`;
- service descriptor dependencies/source locations для `request` и `application` не потеряли `bootstrap_runtime`, `config`, `cache`;
- count `application_core_service_creator.php` снизился с `77` до `43`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php
php fan ai:dynamic-boundaries core/di/application_core_service_creator.php --json
```

### P111. Add creator bundle source guard

Статус: выполнено 2026-06-13.

Итог:

- `application_creator_common_dependencies.php` добавлен в `dynamic_boundaries.categories.composition_roots`;
- `LegacyDiSourceInventoryTest` закрепляет bundle как composition-root-only source;
- AI map contract проверяет, что request descriptor сохраняет dependencies и `source_locations`;
- dynamic-boundary regex теперь учитывает `$this->container->get(...)`, чтобы bundle не выпадал из inventory.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/LegacyDiSourceInventoryTest.php unit/core/AiToolingTest.php
php fan ai:dynamic-boundaries core/di/application_creator_common_dependencies.php --json
```

### P112. Apply same pattern to `application_navigation_service_creator`

Статус: выполнено 2026-06-13.

Итог:

- `application_navigation_service_creator` использует `application_creator_common_dependencies` для tab service runtime/config/cache tail;
- factory argument order сохранен;
- count `application_navigation_service_creator.php` снизился с `45` до `42`;
- source tests закрепляют bundle use в navigation creator.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php
php fan ai:dynamic-boundaries core/di/application_navigation_service_creator.php --json
```

### P113. Re-score composition roots and update route

Статус: выполнено 2026-06-13.

Итог:

- `php fan ai:dynamic-boundaries --next --json` остается `[]`;
- `php fan ai:dynamic-boundaries --direct --json` показывает только `composition_roots`;
- top roots после P109-P113: `base_dependency_resolver.php` (`43`), `application_core_service_creator.php` (`43`), `application_navigation_service_creator.php` (`42`), `application_user_service_creator.php` (`37`), `application_infrastructure_service_creator.php` (`36`);
- P114-P118 ниже выбраны как следующий measured DI/root route.

Проверка:

```bash
php fan ai:dynamic-boundaries --composition --json
php tools/ai_verify.php --json
```

### P114. Split `base_dependency_resolver` dependency groups

Статус: выполнено 2026-06-13.

Итог:

- `base_dependency_resolver` оставлен thin orchestrator без direct `container->get`;
- добавлены `base_dependency_context_group`, `base_dependency_factory_group`, `base_dependency_helper_group`, `base_dependency_storage_group`, `base_dependency_view_meta_group`;
- block dependency keys сохранены;
- source guards закрепляют group files как `composition_roots`;
- count `core/block/base_dependency_resolver.php` снизился с `43` до `0`, groups имеют counts `13/9/9/7/5`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/LegacyDiSourceInventoryTest.php unit/core/AiToolingTest.php --filter 'Base|BlockDependency|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_resolver.php --json
```

### P115. Continue `application_navigation_service_creator` bundle split

Статус: выполнено 2026-06-13.

Итог:

- добавлен `application_navigation_tab_dependencies` facade;
- tab-specific dependencies разнесены по `application_navigation_tab_core_dependencies`, `application_navigation_tab_support_dependencies`, `application_navigation_tab_storage_dependencies`;
- `createTabService()` сохранил factory argument order;
- `tools/ai_service_map.php` сохраняет `tab` dependencies/source locations через `$tabDependencies->...`;
- count `application_navigation_service_creator.php` снизился с `42` до `1`, groups имеют counts `22/14/5`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|AiTooling|ApplicationNavigation|DynamicBoundary|Composition|TabDependencies'
php fan ai:services tab --json
```

### P116. Split `application_user_service_creator` user/session dependency bundle

Статус: выполнено 2026-06-13.

Итог:

- добавлен `application_user_service_dependencies`;
- `createUserService()`, `loadCurrentUsers()`, `setUserServiceDependencies()`, `getCurrentUserSpace()` и related helpers используют named bundle;
- user rehydrate path сохранен;
- `tools/ai_service_map.php` сохраняет `user` dependencies/source locations через `$userDependencies->...`;
- count `application_user_service_creator.php` снизился с `37` до `1`, bundle count `16`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationUserServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'User|AiTooling|ApplicationUser|DynamicBoundary|Composition|UserServiceDependencies'
php fan ai:services user --json
```

### P117. Split `application_infrastructure_service_creator` config/cache dependency bundle

Статус: выполнено 2026-06-13.

Итог:

- добавлен `application_infrastructure_config_cache_dependencies`;
- `createConfigService()`, `createConfigCache()`, `createCacheService()`, config-cache fatal closure, `createError500Exception()` и service fatal path используют named bundle;
- unrelated `json`/`file_system` branches intentionally left for later;
- `tools/ai_service_map.php` сохраняет `cache`/`config` dependencies/source locations через `$infrastructureDependencies->...`;
- count `application_infrastructure_service_creator.php` снизился с `36` до `9`, bundle count `15`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationInfrastructureServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Infrastructure|AiTooling|ApplicationInfrastructure|DynamicBoundary|Composition|ConfigCacheDependencies|CacheCreator'
php fan ai:services cache --json
php fan ai:services config --json
```

### P118. Re-score DI roots and update AI route

Статус: выполнено 2026-06-13.

Итог:

- `php fan ai:dynamic-boundaries --next --json` теперь показывает только `core/service/block_context.php` (`2`);
- `php fan ai:dynamic-boundaries --composition --json` top roots: `application_core_service_creator.php` (`43`), `application_utility_service_creator.php` (`31`), `application_client_service_creator.php` (`22`), `application_navigation_tab_core_dependencies.php` (`22`), `application_session_service_creator.php` (`19`);
- P119-P123 были выбраны как следующий measured route и закрыты следующим batch.

Проверка:

```bash
php fan ai:dynamic-boundaries --next --json
php fan ai:dynamic-boundaries --composition --json
php tools/ai_verify.php --json
```

### P119. Remove `block_context` `--next` debt

Статус: выполнено 2026-06-13.

Итог:

- `block_context` больше не принимает container и не вызывает `$container->get`;
- lazy `tab` и `bootstrap_runtime` access передаются из support registrar как explicit collaborators;
- project tab availability boundary сохранен как injectable `projectTabClassExists`;
- `php fan ai:dynamic-boundaries --next --json` вернулся к `[]`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/service/BlockContextTest.php unit/core/di/ApplicationSupportServiceRegistrarTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'BlockContext|SupportServiceRegistrar'
php fan ai:dynamic-boundaries --next --json
```

### P120. Split remaining `application_core_service_creator` dependency families

Статус: выполнено 2026-06-13.

Итог:

- добавлен `application_core_service_dependencies`;
- `application_core_service_creator.php` снизился с `43` до `1`;
- новый tracked bundle имеет count `27`;
- `tools/ai_service_map.php` сохраняет `request`/`application`/related descriptor dependencies через `$coreDependencies->...`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Request|Application|DynamicBoundary|Composition|AiTooling|CoreServiceDependencies'
php fan ai:dynamic-boundaries core/di/application_core_service_creator.php --json
php fan ai:dynamic-boundaries core/di/application_core_service_dependencies.php --json
php fan ai:services request --json
```

### P121. Split `application_utility_service_creator` utility dependency bundle

Статус: выполнено 2026-06-13.

Итог:

- добавлен `application_utility_service_dependencies`;
- `application_utility_service_creator.php` снизился с `31` до `1`;
- новый tracked bundle имеет count `15`;
- `date` descriptor сохраняет dependencies и config keys `date`, `TIMEZONE`, `DEFAULT_FORMAT` через named dependencies + local `$config->get(...)`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationUtilityServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Utility|Date|Soap|Image|Obfuscator|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_utility_service_creator.php --json
php fan ai:dynamic-boundaries core/di/application_utility_service_dependencies.php --json
php fan ai:services date --json
```

### P122. Split `application_client_service_creator` client dependency bundle

Статус: выполнено 2026-06-13.

Итог:

- добавлен `application_client_service_dependencies`;
- `application_client_service_creator.php` снизился с `22` до `1`;
- новый tracked bundle имеет count `12`;
- `cookie`/`rest` config-key provenance сохранен через local `$config->get(...)`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationClientServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Client|Curl|Rest|Cookie|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_client_service_creator.php --json
php fan ai:dynamic-boundaries core/di/application_client_service_dependencies.php --json
php fan ai:services cookie --json
```

### P123. Re-score and choose next DI/session route

Статус: выполнено 2026-06-13.

Итог:

- `php fan ai:dynamic-boundaries --next --json` вернулся к `[]`;
- top composition roots после P119-P123: `application_core_service_dependencies.php` (`27`), `application_navigation_tab_core_dependencies.php` (`22`), `application_session_service_creator.php` (`19`), `application_user_service_dependencies.php` (`16`), `application_infrastructure_config_cache_dependencies.php` (`15`), `application_utility_service_dependencies.php` (`15`);
- P124-P128 ниже выбраны как следующий measured route.

Проверка:

```bash
php fan ai:dynamic-boundaries --next --json
php fan ai:dynamic-boundaries --composition --json
php tools/ai_verify.php --json
```

### P124. Split `application_core_service_dependencies` family groups

Статус: выполнено 2026-06-13.

Цель: уменьшить самый крупный tracked bundle (`27`) без возврата lookups в `application_core_service_creator`.

Итог:

- `application_core_service_dependencies.php` стал фасадом с direct count `0`;
- добавлены `application_core_project_dependencies.php` (`13`), `application_core_request_dependencies.php` (`9`), `application_core_user_session_dependencies.php` (`5`);
- `application_core_service_creator.php` продолжает работать через `$coreDependencies`;
- `ApplicationCoreServiceCreatorTest` и inventory guards закрепляют новые groups как tracked `composition_roots`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Request|Role|Locale|Application|DynamicBoundary|Composition|AiTooling|CoreServiceDependencies'
```

### P125. Split `application_navigation_tab_core_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab core group (`22`) через smaller domain groups.

Итог:

- `application_navigation_tab_core_dependencies.php` стал фасадом с direct count `0`;
- добавлены `application_navigation_tab_context_dependencies.php` (`5`), `application_navigation_tab_service_factory_dependencies.php` (`11`), `application_navigation_tab_model_factory_dependencies.php` (`6`);
- внешний `$tabDependencies->...` contract не изменен;
- `tab` descriptor provenance обновлен на новые group files.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|DynamicBoundary|Composition|AiTooling|NavigationTabDependencies'
```

### P126. Extract `application_session_service_creator` dependencies

Статус: выполнено 2026-06-13.

Цель: снизить session creator (`19`) и отделить project class boundary от service wiring.

Итог:

- добавлен `application_session_service_dependencies.php` (`15`);
- `application_session_service_creator.php` снизился с `19` до named `class_exists` boundary (`1`);
- `createFatalException()` больше не делает direct `container->get`;
- `tools/ai_service_map.php` распознает `$sessionDependencies->...`;
- `session` descriptor сохраняет `database`/`session` config keys и dependency source locations.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationSessionServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Session|DynamicBoundary|Composition|AiTooling|SessionServiceDependencies'
php fan ai:services session --json
```

### P127. Split next user/infrastructure/utility dependency family

Статус: выполнено 2026-06-13.

Цель: выбрать по measured readout один из roots `application_user_service_dependencies.php` (`16`), `application_infrastructure_config_cache_dependencies.php` (`15`) или `application_utility_service_dependencies.php` (`15`) и уменьшить его coherent family.

Итог:

- measured readout после P126 выбрал `application_user_service_dependencies.php` (`16`) как top root;
- `application_user_service_dependencies.php` стал фасадом с direct count `0`;
- добавлены `application_user_context_dependencies.php` (`6`), `application_user_factory_dependencies.php` (`7`), `application_user_runtime_dependencies.php` (`3`);
- внешний `$userDependencies->...` contract и user descriptor source locations сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationUserServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'User|DynamicBoundary|Composition|AiTooling|UserServiceDependencies'
php fan ai:services user --json
```

### P128. Re-score and choose content/controller route

Статус: выполнено 2026-06-13.

Цель: после P124-P127 проверить, поднимаются ли `application_content_service_creator`, `application_controller_service_creator`, pager/infrastructure roots, затем выбрать P129-P133.

Итог:

- `php fan ai:dynamic-boundaries --next --json`: `[]`;
- top composition roots после P124-P128: `application_infrastructure_config_cache_dependencies.php` (`15`), `application_session_service_dependencies.php` (`15`), `application_utility_service_dependencies.php` (`15`), `application_navigation_tab_support_dependencies.php` (`14`), `base_dependency_factory_group.php` (`13`), `application_content_service_creator.php` (`13`), `application_core_project_dependencies.php` (`13`);
- full `php tools/ai_verify.php --json`: `pass`; PHPUnit `1912 tests`, `103188 assertions`;
- P129-P133 ниже выбраны как следующий measured route.

Проверка:

```bash
php fan ai:dynamic-boundaries --next --json
php fan ai:dynamic-boundaries --composition --json
php tools/ai_verify.php --json
```

### P129. Split `application_infrastructure_config_cache_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить один из трех крупнейших roots (`15`) без потери `cache`/`config` descriptor provenance.

Итог:

- `application_infrastructure_config_cache_dependencies.php` стал фасадом с direct count `0`;
- добавлены `application_infrastructure_config_cache_factory_dependencies.php` (`4`), `application_infrastructure_config_cache_support_dependencies.php` (`5`), `application_infrastructure_config_cache_storage_dependencies.php` (`2`), `application_infrastructure_config_cache_exception_dependencies.php` (`4`);
- внешний `$infrastructureDependencies->...` contract не изменен;
- `cache`/`config` descriptor provenance сохранен через named methods в creator.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationInfrastructureServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Infrastructure|AiTooling|ApplicationInfrastructure|DynamicBoundary|Composition|ConfigCacheDependencies|CacheCreator'
php fan ai:services cache --json
php fan ai:services config --json
```

### P130. Split `application_session_service_dependencies`

Статус: выполнено 2026-06-13.

Цель: разложить новый session dependency bundle (`15`) на меньшие coherent groups после того, как creator уже очищен.

Итог:

- `application_session_service_dependencies.php` стал фасадом с direct count `0`;
- добавлены `application_session_context_dependencies.php` (`5`), `application_session_factory_dependencies.php` (`5`), `application_session_runtime_dependencies.php` (`5`);
- `createSessionService()` contract не менялся;
- `session` descriptor сохраняет dependencies/source locations и config keys `session`, `database`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationSessionServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Session|DynamicBoundary|Composition|AiTooling|SessionServiceDependencies'
php fan ai:services session --json
```

### P131. Split `application_utility_service_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить utility bundle (`15`) и отделить date/soap/image/obfuscator helpers.

Итог:

- `application_utility_service_dependencies.php` стал фасадом с direct count `0`;
- добавлены `application_utility_core_dependencies.php` (`6`), `application_utility_image_dependencies.php` (`6`), `application_utility_storage_dependencies.php` (`3`);
- `date` descriptor сохраняет dependencies/source locations и config keys `date`, `TIMEZONE`, `DEFAULT_FORMAT`;
- utility creator продолжает читать один `$utilityDependencies` bundle.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationUtilityServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Utility|Date|Soap|Image|Obfuscator|AiTooling|DynamicBoundary|Composition'
php fan ai:services date --json
```

### P132. Split `application_navigation_tab_support_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab support bundle (`14`) перед переходом к content/controller roots.

Итог:

- `application_navigation_tab_support_dependencies.php` стал фасадом с direct count `0`;
- добавлены `application_navigation_tab_support_block_dependencies.php` (`4`), `application_navigation_tab_support_helper_dependencies.php` (`7`), `application_navigation_tab_support_asset_dependencies.php` (`3`);
- внешний `$tabDependencies->...` contract не изменен;
- `tab` descriptor сохраняет dependencies/source locations.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|DynamicBoundary|Composition|AiTooling|NavigationTabDependencies'
php fan ai:services tab --json
```

### P133. Re-score content/core-project/factory roots

Статус: выполнено 2026-06-13.

Цель: после P129-P132 пересчитать composition roots и выбрать, что реально стало следующим: content creator, core project group, block factory group, controller или pager.

Итог:

- `php fan ai:dynamic-boundaries --next --json`: `[]`;
- top composition roots после P129-P133: `base_dependency_factory_group.php` (`13`), `application_content_service_creator.php` (`13`), `application_core_project_dependencies.php` (`13`), `application_client_service_dependencies.php` (`12`), `application_navigation_tab_service_factory_dependencies.php` (`11`), `application_controller_service_creator.php` (`10`);
- full `php tools/ai_verify.php --json`: `pass`; PHPUnit `1912 tests`, `105527 assertions`;
- P134-P138 ниже выбраны как следующий measured route.

Проверка:

```bash
php fan ai:dynamic-boundaries --next --json
php fan ai:dynamic-boundaries --composition --json
php tools/ai_verify.php --json
```

### P134. Split `base_dependency_factory_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block factory group (`13`) без возврата direct lookups в `base_dependency_resolver`.

Итог:

- `base_dependency_factory_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_application_factory_group.php` (`6`), `base_dependency_data_factory_group.php` (`4`), `base_dependency_media_factory_group.php` (`3`);
- `base_dependency_resolver` остался без direct `->get(` и продолжает собирать block dependency groups;
- focused PHPUnit: `147 tests`, `50220 assertions`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/LegacyDiSourceInventoryTest.php unit/core/AiToolingTest.php --filter 'Base|BlockDependency|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_factory_group.php --json
```

### P135. Extract `application_content_service_creator` dependencies

Статус: выполнено 2026-06-13.

Цель: снизить content creator (`13`) через named dependency bundle и отделить project class boundary от wiring.

Итог:

- `application_content_service_creator.php` снизился до count `1` (`class_exists` boundary);
- добавлен `application_content_service_dependencies.php` с count `11`;
- `$contentDependencies` добавлен в `tools/ai_service_map.php`;
- `translation` descriptor сохраняет dependencies/source locations через named dependency methods;
- focused PHPUnit: `105 tests`, `46700 assertions`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationContentServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Content|Block|AiTooling|DynamicBoundary|Composition'
php fan ai:services translation --json
```

### P136. Split `application_core_project_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core project group (`13`) после P124 facade split.

Итог:

- `application_core_project_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_project_error_dependencies.php` (`4`), `application_core_project_storage_dependencies.php` (`4`), `application_core_project_tab_dependencies.php` (`5`);
- внешний `$coreDependencies->...` contract сохранен;
- `application` descriptor сохраняет source locations через прежние creator calls;
- focused PHPUnit: `117 tests`, `50015 assertions`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Application|Tab|Error|DynamicBoundary|Composition'
php fan ai:services application --json
```

### P137. Split `application_client_service_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить client bundle (`12`) после P122 extraction.

Итог:

- `application_client_service_dependencies.php` стал фасадом с count `0`;
- добавлены `application_client_payload_dependencies.php` (`5`), `application_client_runtime_dependencies.php` (`3`), `application_client_transport_dependencies.php` (`4`);
- `cookie`/`rest` config-key provenance в client creator сохранен;
- focused PHPUnit: `107 tests`, `47274 assertions`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationClientServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Client|Curl|Rest|Cookie|AiTooling|DynamicBoundary|Composition'
php fan ai:services cookie --json
```

### P138. Re-score navigation/controller/pager route

Статус: выполнено 2026-06-13.

Цель: после P134-P137 пересчитать composition roots и выбрать следующий route по фактическому top list.

Итог:

- `php fan ai:dynamic-boundaries --next --json`: `[]`;
- зафиксированный на P138 top composition roots: `application_content_service_dependencies.php` (`11`), `application_navigation_tab_service_factory_dependencies.php` (`11`), `application_controller_service_creator.php` (`10`), `base_dependency_context_group.php` (`9`), `base_dependency_view_meta_group.php` (`9`), `application_core_request_dependencies.php` (`9`), `application_infrastructure_service_creator.php` (`9`), `application_pager_service_creator.php` (`9`);
- P139-P143 были выбраны как следующий measured route;
- full `php tools/ai_verify.php --json`: `pass`; PHPUnit `1914 tests`, `107329 assertions`.

Проверка:

```bash
php fan ai:dynamic-boundaries --next --json
php fan ai:dynamic-boundaries --composition --json
php tools/ai_verify.php --json
```

### P139. Split `application_content_service_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить content dependency bundle (`11`) после P135 extraction.

Итог:

- `application_content_service_dependencies.php` стал фасадом с count `0`;
- добавлены `application_content_context_dependencies.php` (`6`), `application_content_runtime_dependencies.php` (`3`), `application_content_storage_dependencies.php` (`2`);
- внешний `$contentDependencies->...` contract сохранен;
- `translation` descriptor сохраняет dependencies/source locations;
- focused PHPUnit: `107 tests`, `47526 assertions`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationContentServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Content|Translation|AiTooling|DynamicBoundary|Composition'
php fan ai:services translation --json
```

### P140. Split `application_navigation_tab_service_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab service-factory bundle (`11`) без изменения `$tabDependencies` contract.

Итог:

- `application_navigation_tab_service_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_service_factory_application_dependencies.php` (`4`), `application_navigation_tab_service_factory_runtime_dependencies.php` (`4`), `application_navigation_tab_service_factory_payload_dependencies.php` (`3`);
- source locations для `tab` descriptor сохранены через прежние `$tabDependencies->...` calls;
- focused PHPUnit: `118 tests`, `50096 assertions`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|AiTooling|DynamicBoundary|Composition'
php fan ai:services tab --json
```

### P141. Extract `application_controller_service_creator` dependencies

Статус: выполнено 2026-06-13.

Цель: снизить controller creator (`10`) через named dependency bundle и отделить project class boundary от wiring.

Итог:

- `application_controller_service_creator.php` снизился до count `1` (`class_exists` boundary);
- добавлен `application_controller_service_dependencies.php` с count `9`;
- `$controllerDependencies` добавлен в `tools/ai_service_map.php`;
- `plain` descriptor сохраняет `matcher`, `config`, `header`, `obfuscator`, `request`, `plain_file_context`, runtime/cache dependencies;
- focused PHPUnit: `113 tests`, `48475 assertions`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationControllerServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Controller|Plain|AiTooling|DynamicBoundary|Composition'
php fan ai:services plain --json
```

### P142. Split `base_dependency_context_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block context group (`9`) без возврата direct lookups в `base_dependency_resolver`.

Итог:

- `base_dependency_context_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_runtime_context_group.php`, `base_dependency_request_context_group.php`, `base_dependency_navigation_context_group.php` по `3`;
- block dependency keys сохранены;
- focused PHPUnit: `149 tests`, `51862 assertions`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_context_group.php --json
```

### P143. Split `base_dependency_view_meta_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block view/meta group (`9`) отдельно от context dependencies.

Итог:

- `base_dependency_view_meta_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_meta_loader_group.php` (`4`), `base_dependency_view_loader_group.php` (`3`), `base_dependency_block_factory_context_group.php` (`2`);
- block dependency keys и view/meta source guards сохранены;
- focused PHPUnit: `166 tests`, `56262 assertions`;
- `php fan ai:dynamic-boundaries --next --json`: `[]`;
- top composition roots после P139-P143: `application_controller_service_dependencies.php` (`9`), `application_core_request_dependencies.php` (`9`), `application_infrastructure_service_creator.php` (`9`), `application_pager_service_creator.php` (`9`), `base_dependency_helper_group.php` (`7`), `application_navigation_tab_support_helper_dependencies.php` (`7`), `application_user_factory_dependencies.php` (`7`);
- full `php tools/ai_verify.php --json`: `pass`; PHPUnit `1916 tests`, `109665 assertions`.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|View|Meta|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_view_meta_group.php --json
```

### P144. Split `application_controller_service_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить controller dependency bundle (`9`) после P141 extraction.

Итог:

- `application_controller_service_dependencies.php` стал фасадом с count `0`;
- добавлены `application_controller_plain_dependencies.php` (`3`), `application_controller_handler_dependencies.php` (`3`), `application_controller_runtime_dependencies.php` (`3`);
- внешний `$controllerDependencies->...` contract и `plain` descriptor provenance сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationControllerServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Controller|Plain|AiTooling|DynamicBoundary|Composition'
php fan ai:services plain --json
```

### P145. Split `application_core_request_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core request group (`9`) внутри existing core dependency facade.

Итог:

- `application_core_request_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_request_factory_dependencies.php` (`5`) и `application_core_request_helper_dependencies.php` (`4`);
- внешний `$coreDependencies->...` contract и `request`/`locale` descriptor provenance сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Request|Locale|AiTooling|DynamicBoundary|Composition'
php fan ai:services request --json
```

### P146. Extract `application_infrastructure_service_creator` dependencies

Статус: выполнено 2026-06-13.

Цель: снизить infrastructure creator (`9`) через named dependency bundles outside already-split config-cache dependencies.

Итог:

- `application_infrastructure_service_creator.php` снизился до count `1` named class-exists boundary;
- добавлен `application_infrastructure_service_dependencies.php` facade (`0`) с runtime (`4`) и storage (`1`) groups;
- JSON/file-system wiring переведен на `$infrastructureDependencies->...`;
- `fileSystemStorage()` добавлен в named dependency mapping.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationInfrastructureServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Infrastructure|FileSystem|Json|Cache|AiTooling|DynamicBoundary|Composition'
php fan ai:services file_system --json
```

### P147. Extract `application_pager_service_creator` dependencies

Статус: выполнено 2026-06-13.

Цель: снизить pager creator (`9`) и отделить project class boundary от pager wiring.

Итог:

- `application_pager_service_creator.php` снизился до count `1` named class-exists boundary;
- добавлен `application_pager_service_dependencies.php` facade (`0`) с context (`3`), runtime (`3`), exception (`1`) groups;
- `$pagerDependencies` добавлен в `tools/ai_service_map.php`;
- `pager` descriptor provenance сохранен.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationPagerServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Pager|AiTooling|DynamicBoundary|Composition'
php fan ai:services pager --json
```

### P148. Split `base_dependency_helper_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block helper group (`7`) after context/view-meta splits.

Итог:

- `base_dependency_helper_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_array_helper_group.php` (`4`), `base_dependency_class_helper_group.php` (`1`), `base_dependency_media_error_helper_group.php` (`2`);
- block dependency keys сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Helper|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_helper_group.php --json
```

Focused verification для P144-P148:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationControllerServiceCreatorTest.php unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/di/ApplicationInfrastructureServiceCreatorTest.php unit/core/di/ApplicationPagerServiceCreatorTest.php unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Controller|Core|Request|Infrastructure|FileSystem|Json|Pager|Base|BlockDependency|Helper|AiTooling|DynamicBoundary|Composition'
```

Результат: `219 tests`, `59078 assertions`.

### P149. Split `application_navigation_tab_support_helper_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab support helper bundle (`7`) после service-factory split.

Итог:

- `application_navigation_tab_support_helper_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_support_loader_dependencies.php` (`1`), `application_navigation_tab_support_array_helper_dependencies.php` (`4`), `application_navigation_tab_support_class_helper_dependencies.php` (`2`);
- внешний `$tabDependencies->...` contract сохранен.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|Helper|AiTooling|DynamicBoundary|Composition'
php fan ai:services tab --json
```

### P150. Split `application_user_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить user factory bundle (`7`) без изменения `$userDependencies` contract.

Итог:

- `application_user_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_user_application_factory_dependencies.php` (`3`), `application_user_identity_factory_dependencies.php` (`2`), `application_user_support_factory_dependencies.php` (`2`);
- `user` descriptor dependencies/source locations сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationUserServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'User|AiTooling|DynamicBoundary|Composition'
php fan ai:services user --json
```

### P151. Split `base_dependency_application_factory_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block application factory group (`6`) после helper split.

Итог:

- `base_dependency_application_factory_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_application_runtime_factory_group.php` (`2`), `base_dependency_application_data_factory_group.php` (`2`), `base_dependency_application_support_factory_group.php` (`2`);
- block dependency keys сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Application|Factory|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_application_factory_group.php --json
```

### P152. Split `application_content_context_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить content context group (`6`) внутри already-facaded content dependencies.

Итог:

- `application_content_context_dependencies.php` стал фасадом с count `0`;
- добавлены `application_content_localization_context_dependencies.php` (`2`), `application_content_error_block_context_dependencies.php` (`2`), `application_content_request_matcher_context_dependencies.php` (`2`);
- внешний `$contentDependencies->...` contract сохранен.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationContentServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Content|Translation|AiTooling|DynamicBoundary|Composition'
php fan ai:services translation --json
```

### P153. Split `application_navigation_tab_model_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab model factory bundle (`6`) как следующий measured navigation root.

Итог:

- `application_navigation_tab_model_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_data_model_factory_dependencies.php` (`2`), `application_navigation_tab_media_model_factory_dependencies.php` (`2`), `application_navigation_tab_user_time_model_factory_dependencies.php` (`2`);
- внешний `$tabDependencies->...` contract сохранен.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|Model|Factory|AiTooling|DynamicBoundary|Composition'
php fan ai:services tab --json
```

Focused verification для P149-P153:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/di/ApplicationUserServiceCreatorTest.php unit/core/di/ApplicationContentServiceCreatorTest.php unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|User|Content|Translation|Base|BlockDependency|Application|Factory|AiTooling|DynamicBoundary|Composition'
```

Результат: `250 tests`, `63726 assertions`.

### P154. Split `application_user_context_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить user context bundle (`6`) как текущий top root.

Итог:

- `application_user_context_dependencies.php` стал фасадом с count `0`;
- добавлены `application_user_serialization_config_context_dependencies.php` (`2`), `application_user_application_request_context_dependencies.php` (`2`), `application_user_exception_session_context_dependencies.php` (`2`);
- внешний `$userDependencies->...` contract сохранен.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationUserServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'User|AiTooling|DynamicBoundary|Composition'
php fan ai:services user --json
```

### P155. Split `application_utility_core_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить utility core bundle (`6`) без изменения utility creator contract.

Итог:

- `application_utility_core_dependencies.php` стал фасадом с count `0`;
- добавлены `application_utility_runtime_core_dependencies.php` (`2`), `application_utility_config_cache_core_dependencies.php` (`2`), `application_utility_helper_error_core_dependencies.php` (`2`);
- `$utilityDependencies` service descriptor provenance сохранен.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationUtilityServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Utility|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P156. Split `application_utility_image_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить utility image bundle (`6`) как второй utility top root.

Итог:

- `application_utility_image_dependencies.php` стал фасадом с count `0`;
- добавлены `application_utility_image_storage_dependencies.php` (`2`), `application_utility_image_metadata_resource_dependencies.php` (`2`), `application_utility_image_canvas_output_dependencies.php` (`2`);
- аргументы image-related utility services сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationUtilityServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Utility|Image|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_utility_image_dependencies.php --json
```

### P157. Split `base_dependency_storage_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block storage group (`5`) и сохранить optional storage lookups явными.

Итог:

- `base_dependency_storage_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_block_file_storage_group.php` (`2`), `base_dependency_project_file_storage_group.php` (`2`), `base_dependency_upload_limit_storage_group.php` (`1`);
- optional `has/get` поведение сохранено.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Storage|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_storage_group.php --json
```

### P158. Split `application_client_payload_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить client payload bundle (`5`) без изменения client service creator contract.

Итог:

- `application_client_payload_dependencies.php` стал фасадом с count `0`;
- добавлены `application_client_array_payload_dependencies.php` (`2`), `application_client_request_payload_dependencies.php` (`1`), `application_client_serialization_payload_dependencies.php` (`2`);
- `$clientDependencies` descriptor dependencies/source locations сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationClientServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Client|Payload|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

Focused verification для P154-P158:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationUserServiceCreatorTest.php unit/core/di/ApplicationUtilityServiceCreatorTest.php unit/core/di/ApplicationClientServiceCreatorTest.php unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'User|Utility|Image|Client|Payload|Base|BlockDependency|Storage|AiTooling|DynamicBoundary|Composition'
```

Результат: `191 tests`, `59917 assertions`.

### P159. Split `application_core_project_tab_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core project tab bundle (`5`) как текущий top root.

Итог:

- `application_core_project_tab_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_project_tab_context_dependencies.php` (`3`), `application_core_project_application_context_dependencies.php` (`1`), `application_core_project_route_storage_dependencies.php` (`1`);
- внешний `$coreDependencies->...` contract и tab/project descriptor provenance сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Project|Tab|AiTooling|DynamicBoundary|Composition'
php fan ai:services tab --json
```

### P160. Split `application_core_request_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core request factory bundle (`5`) после P146 request split.

Итог:

- `application_core_request_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_request_input_factory_dependencies.php` (`1`), `application_core_request_runtime_factory_dependencies.php` (`2`), `application_core_request_transport_factory_dependencies.php` (`2`);
- request factory argument behavior сохранен.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Request|Factory|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_core_request_factory_dependencies.php --json
```

### P161. Split `application_core_user_session_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core user/session bundle (`5`) без изменения current-user/session factory semantics.

Итог:

- `application_core_user_session_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_user_identity_dependencies.php` (`3`) и `application_core_user_data_factory_dependencies.php` (`2`);
- current-user/session/date/entity factory semantics и `$coreDependencies` service descriptor provenance сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|User|Session|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P162. Split `application_infrastructure_config_cache_support_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить infrastructure config-cache support group (`5`) без изменения config/cache factory wiring.

Итог:

- `application_infrastructure_config_cache_support_dependencies.php` стал фасадом с count `0`;
- добавлены `application_infrastructure_config_cache_runtime_support_dependencies.php` (`2`), `application_infrastructure_config_cache_loader_serializer_dependencies.php` (`2`), `application_infrastructure_config_cache_class_helper_dependencies.php` (`1`);
- config/cache factory wiring и `$infrastructureDependencies` descriptor dependencies/source locations сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationInfrastructureServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Infrastructure|Config|Cache|Support|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P163. Split `application_navigation_tab_context_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab context group (`5`) без изменения `$tabDependencies` contract.

Итог:

- `application_navigation_tab_context_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_routing_context_dependencies.php` (`2`), `application_navigation_tab_locale_session_context_dependencies.php` (`2`), `application_navigation_tab_input_context_dependencies.php` (`1`);
- `$tabDependencies` contract и tab descriptor provenance сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|Context|AiTooling|DynamicBoundary|Composition'
php fan ai:services tab --json
```

Focused verification для P159-P163:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/di/ApplicationInfrastructureServiceCreatorTest.php unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Project|Request|User|Session|Infrastructure|Config|Cache|Support|Navigation|Tab|Context|AiTooling|DynamicBoundary|Composition'
```

Результат: `231 tests`, `69989 assertions`.

### P164. Split `application_navigation_tab_storage_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab storage bundle (`5`) без изменения `$tabDependencies` storage contract.

Итог:

- `application_navigation_tab_storage_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_file_storage_dependencies.php` (`3`), `application_navigation_tab_project_tool_storage_dependencies.php` (`1`), `application_navigation_tab_upload_limit_storage_dependencies.php` (`1`);
- tab descriptor provenance и storage method names сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|Storage|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_navigation_tab_storage_dependencies.php --json
```

### P165. Split `application_session_context_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить session context bundle (`5`) без изменения session creator context wiring.

Итог:

- `application_session_context_dependencies.php` стал фасадом с count `0`;
- добавлены `application_session_application_context_dependencies.php` (`2`), `application_session_request_context_dependencies.php` (`2`), `application_session_header_context_dependencies.php` (`1`);
- `$sessionDependencies` method contract сохранен.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationSessionServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Session|Context|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P166. Split `application_session_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить session factory bundle (`5`) без изменения factory argument behavior.

Итог:

- `application_session_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_session_support_factory_dependencies.php` (`2`), `application_session_state_factory_dependencies.php` (`2`), `application_session_cache_factory_dependencies.php` (`1`);
- namespace/group/path/domain/type аргументы сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationSessionServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Session|Factory|AiTooling|DynamicBoundary|Composition'
php fan ai:services session --json
```

### P167. Split `application_session_runtime_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить session runtime bundle (`5`) и сохранить runtime/native session wiring.

Итог:

- `application_session_runtime_dependencies.php` стал фасадом с count `0`;
- добавлены `application_session_native_runtime_dependencies.php` (`2`), `application_session_bootstrap_runtime_dependencies.php` (`2`), `application_session_array_runtime_dependencies.php` (`1`);
- `$sessionDependencies` runtime method names сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationSessionServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Session|Runtime|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_session_runtime_dependencies.php --json
```

### P168. Split `base_dependency_array_helper_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block array helper group (`4`) без изменения legacy block dependency keys.

Итог:

- `base_dependency_array_helper_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_array_transform_helper_group.php` (`2`) и `base_dependency_array_read_helper_group.php` (`2`);
- ключи массива `dependencies()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Array|Helper|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_array_helper_group.php --json
```

Focused verification для P164-P168:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/di/ApplicationSessionServiceCreatorTest.php unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|Storage|Session|Context|Factory|Runtime|Base|BlockDependency|Array|Helper|AiTooling|DynamicBoundary|Composition'
```

Результат: `263 tests`, `71174 assertions`.

### P169. Split `base_dependency_data_factory_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block data factory bundle (`4`) без изменения legacy block dependency keys.

Итог:

- `base_dependency_data_factory_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_data_core_factory_group.php` (`2`) и `base_dependency_data_loader_factory_group.php` (`2`);
- ключи массива `entityFactory`, `jsonFactory`, `dataLoaderFactory`, `pagerFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Data|Factory|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_data_factory_group.php --json
```

### P170. Split `base_dependency_meta_loader_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block meta loader bundle (`4`) без изменения meta-maker construction dependencies.

Итог:

- `base_dependency_meta_loader_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_meta_maker_group.php` (`2`) и `base_dependency_meta_row_loader_group.php` (`2`);
- ключи массива `metaMakerState`, `metaMakerFactory`, `phpArrayFileLoader`, `metaRowFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Meta|Loader|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P171. Split `application_client_transport_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить client transport bundle (`4`) без изменения client service creator contract.

Итог:

- `application_client_transport_dependencies.php` стал фасадом с count `0`;
- добавлены `application_client_curl_transport_dependencies.php` (`2`), `application_client_serialization_transport_dependencies.php` (`1`) и `application_client_error_transport_dependencies.php` (`1`);
- curl URL argument, callable factory behavior и client service creator contract сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationClientServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Client|Transport|AiTooling|DynamicBoundary|Composition'
php fan ai:services client --json
```

### P172. Split `application_core_project_error_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core project error bundle (`4`) без изменения project error wiring.

Итог:

- `application_core_project_error_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_project_error_service_dependencies.php` (`2`) и `application_core_project_error_storage_dependencies.php` (`2`);
- `$coreDependencies` method contract и project error wiring сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Project|Error|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_core_project_error_dependencies.php --json
```

### P173. Split `application_core_project_storage_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core project storage bundle (`4`) без изменения reflection/meta/header/loader wiring.

Итог:

- `application_core_project_storage_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_project_reflection_meta_dependencies.php` (`2`) и `application_core_project_response_loader_dependencies.php` (`2`);
- `$coreDependencies` method contract и reflection/meta/header/loader wiring сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Project|Storage|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

Focused verification для P169-P173:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/di/ApplicationClientServiceCreatorTest.php unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Data|Meta|Loader|Client|Transport|Core|Project|Error|Storage|AiTooling|DynamicBoundary|Composition'
```

Результат: `229 tests`, `83892 assertions`.

### P174. Split `application_core_request_helper_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core request helper bundle (`4`) без изменения helper method names.

Итог:

- `application_core_request_helper_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_request_array_transform_helper_dependencies.php` (`2`) и `application_core_request_array_read_class_helper_dependencies.php` (`2`);
- `$coreDependencies` method contract и helper method names сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Request|Helper|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_core_request_helper_dependencies.php --json
```

### P175. Split `application_infrastructure_config_cache_exception_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить config-cache exception bundle (`4`) без изменения exception/request/header wiring.

Итог:

- `application_infrastructure_config_cache_exception_dependencies.php` стал фасадом с count `0`;
- добавлены `application_infrastructure_config_cache_exception_factory_dependencies.php` (`2`) и `application_infrastructure_config_cache_request_header_dependencies.php` (`2`);
- `$infrastructureDependencies` method contract и exception/request/header wiring сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationInfrastructureServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Infrastructure|Config|Cache|Exception|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_infrastructure_config_cache_exception_dependencies.php --json
```

### P176. Split `application_infrastructure_config_cache_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить config-cache factory bundle (`4`) без изменения config/cache factory arguments.

Итог:

- `application_infrastructure_config_cache_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_infrastructure_config_cache_config_factory_dependencies.php` (`2`) и `application_infrastructure_config_cache_cache_factory_dependencies.php` (`2`);
- config/cache factory arguments и callable forwarding сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationInfrastructureServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Infrastructure|Config|Cache|Factory|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P177. Split `application_infrastructure_runtime_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить infrastructure runtime bundle (`4`) без изменения runtime/config/cache semantics.

Итог:

- `application_infrastructure_runtime_dependencies.php` стал фасадом с count `0`;
- добавлены `application_infrastructure_runtime_error_dependencies.php` (`2`) и `application_infrastructure_runtime_config_cache_dependencies.php` (`2`);
- `$infrastructureDependencies` method contract и runtime/config/cache semantics сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationInfrastructureServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Infrastructure|Runtime|Config|Cache|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_infrastructure_runtime_dependencies.php --json
```

### P178. Split `application_navigation_tab_service_factory_application_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab application factory bundle (`4`) без изменения role/transfer/application/debug factory behavior.

Итог:

- `application_navigation_tab_service_factory_application_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_service_factory_role_transfer_dependencies.php` (`2`) и `application_navigation_tab_service_factory_application_debug_dependencies.php` (`2`);
- `$tabDependencies` method contract и role/transfer/application/debug factory behavior сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|Application|Factory|AiTooling|DynamicBoundary|Composition'
php fan ai:services tab --json
```

Focused verification для P174-P178:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/di/ApplicationInfrastructureServiceCreatorTest.php unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Request|Helper|Infrastructure|Config|Cache|Runtime|Navigation|Tab|Application|Factory|DynamicBoundary|Composition'
```

Результат: `229 tests`, `75084 assertions`.

### P179. Split `application_navigation_tab_service_factory_runtime_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab runtime factory bundle (`4`) без изменения callable factory signatures.

Итог:

- `application_navigation_tab_service_factory_runtime_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_service_factory_config_header_dependencies.php` (`2`) и `application_navigation_tab_service_factory_error_reflector_dependencies.php` (`2`);
- `$tabDependencies` method contract и callable factory signatures сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|Runtime|Factory|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_navigation_tab_service_factory_runtime_dependencies.php --json
```

### P180. Split `application_navigation_tab_support_array_helper_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab array helper bundle (`4`) без изменения support helper method names.

Итог:

- `application_navigation_tab_support_array_helper_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_support_array_transform_dependencies.php` (`2`) и `application_navigation_tab_support_array_read_check_dependencies.php` (`2`);
- `$tabDependencies` method contract и support helper method names сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|Support|Array|Helper|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P181. Split `application_navigation_tab_support_block_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab support block bundle (`4`) без изменения tab/block/exception/meta behavior.

Итог:

- `application_navigation_tab_support_block_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_support_block_factory_dependencies.php` (`2`) и `application_navigation_tab_support_block_exception_meta_dependencies.php` (`2`);
- `$tabDependencies` method contract и tab/block/exception/meta behavior сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|Support|Block|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P182. Split `base_dependency_media_factory_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block media factory group (`3`) без изменения legacy block dependency keys.

Итог:

- `base_dependency_media_factory_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_media_core_factory_group.php` (`2`) и `base_dependency_media_transfer_factory_group.php` (`1`);
- ключи массива `obfuscatorFactory`, `imageModifyFactory`, `logFactory`, `transferFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Media|Factory|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_media_factory_group.php --json
```

### P183. Split `base_dependency_navigation_context_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block navigation context group (`3`) без изменения legacy block dependency keys.

Итог:

- `base_dependency_navigation_context_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_reflector_context_group.php` (`1`) и `base_dependency_route_locale_context_group.php` (`2`);
- ключи `reflectorFactory`, `localeFactory`, `matcherFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Navigation|Context|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_navigation_context_group.php --json
```

Focused verification для P179-P183:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|Support|Runtime|Block|Base|Media|Context|AiTooling|DynamicBoundary|Composition'
```

Результат: `176 tests`, `66469 assertions`.

### P184. Split `base_dependency_request_context_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block request context group (`3`) без изменения legacy block dependency keys.

Итог:

- `base_dependency_request_context_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_request_role_context_group.php` (`2`) и `base_dependency_session_context_group.php` (`1`);
- ключи `requestFactory`, `roleFactory`, `sessionFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Request|Context|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_request_context_group.php --json
```

### P185. Split `base_dependency_runtime_context_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block runtime context group (`3`) без изменения legacy block dependency keys.

Итог:

- `base_dependency_runtime_context_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_tab_runtime_context_group.php` (`2`) и `base_dependency_request_input_context_group.php` (`1`);
- ключи `tab`, `runtime`, `requestInputFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Runtime|Context|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P186. Split `base_dependency_view_loader_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block view loader group (`3`) без изменения legacy block dependency keys.

Итог:

- `base_dependency_view_loader_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_view_factory_loader_group.php` (`2`) и `base_dependency_view_state_loader_group.php` (`1`);
- ключи `viewParserExceptionFactory`, `viewRouterFactory`, `viewLoaderState` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|View|Loader|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P187. Split `application_client_runtime_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить client runtime bundle (`3`) без изменения bootstrap/config/cache semantics.

Итог:

- `application_client_runtime_dependencies.php` стал фасадом с count `0`;
- добавлены `application_client_bootstrap_runtime_dependencies.php` (`1`) и `application_client_config_cache_runtime_dependencies.php` (`2`);
- `$clientDependencies` method contract и bootstrap/config/cache semantics сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationClientServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Client|Runtime|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_client_runtime_dependencies.php --json
```

### P188. Split `application_content_runtime_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить content runtime bundle (`3`) без изменения bootstrap/config/cache semantics.

Итог:

- `application_content_runtime_dependencies.php` стал фасадом с count `0`;
- добавлены `application_content_bootstrap_runtime_dependencies.php` (`1`) и `application_content_config_cache_runtime_dependencies.php` (`2`);
- `$contentDependencies` method contract и bootstrap/config/cache semantics сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationContentServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Content|Runtime|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_content_runtime_dependencies.php --json
```

Focused verification для P184-P188:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/di/ApplicationClientServiceCreatorTest.php unit/core/di/ApplicationContentServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Request|Runtime|View|Loader|Client|Content|AiTooling|DynamicBoundary|Composition'
```

Результат: `187 tests`, `68880 assertions`.

### P189. Split `application_controller_handler_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить controller handler bundle (`3`) без изменения obfuscator/request/plain-file semantics.

Итог:

- `application_controller_handler_dependencies.php` стал фасадом с count `0`;
- добавлены `application_controller_obfuscator_handler_dependencies.php` (`2`) и `application_controller_plain_file_handler_dependencies.php` (`1`);
- методы `obfuscatorFactory()`, `request()`, `plainFileContext()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationControllerServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Controller|Handler|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_controller_handler_dependencies.php --json
```

### P190. Split `application_controller_plain_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить controller plain bundle (`3`) без изменения matcher/config/header semantics.

Итог:

- `application_controller_plain_dependencies.php` стал фасадом с count `0`;
- добавлены `application_controller_plain_route_dependencies.php` (`2`) и `application_controller_plain_config_dependencies.php` (`1`);
- методы `matcher()`, `plainConfigFactory()`, `header()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationControllerServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Controller|Plain|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P191. Split `application_controller_runtime_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить controller runtime bundle (`3`) без изменения bootstrap/config/cache semantics.

Итог:

- `application_controller_runtime_dependencies.php` стал фасадом с count `0`;
- добавлены `application_controller_bootstrap_runtime_dependencies.php` (`1`) и `application_controller_config_cache_runtime_dependencies.php` (`2`);
- методы `bootstrapRuntime()`, `config()`, `cacheFactory()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationControllerServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Controller|Runtime|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_controller_runtime_dependencies.php --json
```

### P192. Split `application_core_project_tab_context_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core project tab context bundle (`3`) без изменения tab/locale behavior.

Итог:

- `application_core_project_tab_context_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_project_tab_service_context_dependencies.php` (`2`) и `application_core_project_locale_context_dependencies.php` (`1`);
- методы `tab()`, `tabFactory()`, `locale()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Project|Tab|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_core_project_tab_context_dependencies.php --json
```

### P193. Split `application_core_user_identity_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core user identity bundle (`3`) без изменения current-user/session/current-user-space factory signatures.

Итог:

- `application_core_user_identity_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_user_current_identity_dependencies.php` (`1`) и `application_core_user_session_space_dependencies.php` (`2`);
- методы `currentUserFactory()`, `sessionFactory()`, `currentUserSpaceFactory()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|User|Identity|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

Focused verification для P189-P193:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationControllerServiceCreatorTest.php unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Controller|Core|Project|Tab|User|Identity|AiTooling|DynamicBoundary|Composition'
```

Результат: `154 tests`, `68046 assertions`.

### P194. Split `application_creator_common_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить common bootstrap/config/cache bundle (`3`) без изменения shared method names.

Итог:

- `application_creator_common_dependencies.php` стал фасадом с count `0`;
- добавлены `application_creator_bootstrap_runtime_dependencies.php` (`1`) и `application_creator_config_cache_dependencies.php` (`2`);
- методы `bootstrapRuntime()`, `config()`, `cacheFactory()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Common|Core|Navigation|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_creator_common_dependencies.php --json
```

### P195. Split `application_navigation_tab_file_storage_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab file-storage group (`3`) без изменения storage method names.

Итог:

- `application_navigation_tab_file_storage_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_block_meta_file_storage_dependencies.php` (`2`) и `application_navigation_tab_root_html_file_storage_dependencies.php` (`1`);
- методы `blockFileStorage()`, `metaFileStorage()`, `rootHtmlFileStorage()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|Storage|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_navigation_tab_file_storage_dependencies.php --json
```

### P196. Split `application_navigation_tab_service_factory_payload_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab payload group (`3`) без изменения json/data-loader/cookie factory signatures.

Итог:

- `application_navigation_tab_service_factory_payload_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_json_payload_dependencies.php` (`1`) и `application_navigation_tab_data_cookie_payload_dependencies.php` (`2`);
- методы `jsonFactory()`, `dataLoaderFactory()`, `cookieFactory()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|Payload|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P197. Split `application_navigation_tab_support_asset_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation support asset group (`3`) без изменения asset support dependencies.

Итог:

- `application_navigation_tab_support_asset_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_alias_asset_dependencies.php` (`1`) и `application_navigation_tab_media_error_asset_dependencies.php` (`2`);
- методы `tabAliasFileStorage()`, `imageMetadataReader()`, `errorLogWriter()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|Asset|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P198. Split `application_pager_context_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить pager context group (`3`) без изменения entity/tab/request semantics.

Итог:

- `application_pager_context_dependencies.php` стал фасадом с count `0`;
- добавлены `application_pager_entity_context_dependencies.php` (`1`), `application_pager_tab_context_dependencies.php` (`1`) и `application_pager_request_context_dependencies.php` (`1`);
- методы `entityFactory()`, `tab()`, `tabFactory()`, `requestFactory()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationPagerServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Pager|Context|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_pager_context_dependencies.php --json
```

Focused verification для P194-P198:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/di/ApplicationPagerServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Common|Core|Navigation|Tab|Payload|Asset|Storage|Pager|AiTooling|DynamicBoundary|Composition'
```

Результат: `143 tests`, `67767 assertions`.

### P199. Split `application_pager_runtime_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить pager runtime bundle (`3`) без изменения bootstrap/config/cache semantics.

Итог:

- `application_pager_runtime_dependencies.php` стал фасадом с count `0`;
- добавлены `application_pager_bootstrap_runtime_dependencies.php` (`1`) и `application_pager_config_cache_runtime_dependencies.php` (`2`);
- методы `bootstrapRuntime()`, `config()`, `cacheFactory()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationPagerServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Pager|Runtime|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_pager_runtime_dependencies.php --json
```

### P200. Split `application_user_application_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить user application factory bundle (`3`) без изменения config/application/request-input factory signatures.

Итог:

- `application_user_application_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_user_config_factory_dependencies.php` (`1`) и `application_user_application_request_input_factory_dependencies.php` (`2`);
- методы `configFactory()`, `applicationFactory()`, `requestInputFactory()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationUserServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'User|Application|Factory|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_user_application_factory_dependencies.php --json
```

### P201. Split `application_user_runtime_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить user runtime bundle (`3`) без изменения bootstrap/cache/array behavior.

Итог:

- `application_user_runtime_dependencies.php` стал фасадом с count `0`;
- добавлены `application_user_bootstrap_cache_runtime_dependencies.php` (`2`) и `application_user_array_runtime_dependencies.php` (`1`);
- методы `bootstrapRuntime()`, `cacheFactory()`, `arrayAdducer()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationUserServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'User|Runtime|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P202. Split `application_utility_storage_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить utility storage bundle (`3`) без изменения loader/storage/resolver semantics.

Итог:

- `application_utility_storage_dependencies.php` стал фасадом с count `0`;
- добавлены `application_utility_file_storage_dependencies.php` (`2`) и `application_utility_class_storage_dependencies.php` (`1`);
- методы `phpArrayFileLoader()`, `soapWsdlFileStorage()`, `classNameResolver()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationUtilityServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Utility|Storage|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P203. Split `base_dependency_application_data_factory_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block application data factory group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_application_data_factory_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_database_application_data_factory_group.php` (`1`) и `base_dependency_user_application_data_factory_group.php` (`1`);
- ключи `databaseFactory`, `userFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Application|Data|Factory|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_application_data_factory_group.php --json
```

Focused verification для P199-P203:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationPagerServiceCreatorTest.php unit/core/di/ApplicationUserServiceCreatorTest.php unit/core/di/ApplicationUtilityServiceCreatorTest.php unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Pager|User|Utility|Storage|Runtime|Application|Data|Factory|BlockDependency|AiTooling|DynamicBoundary|Composition'
```

Результат: `255 tests`, `92128 assertions`.

### P204. Split `base_dependency_application_runtime_factory_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block application runtime factory group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_application_runtime_factory_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_application_service_runtime_factory_group.php` (`1`) и `base_dependency_config_application_runtime_factory_group.php` (`1`);
- ключи `applicationFactory`, `configFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Application|Runtime|Factory|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_application_runtime_factory_group.php --json
```

### P205. Split `base_dependency_application_support_factory_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block application support factory group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_application_support_factory_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_error_application_support_factory_group.php` (`1`) и `base_dependency_date_application_support_factory_group.php` (`1`);
- ключи `errorFactory`, `dateFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Application|Support|Factory|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_application_support_factory_group.php --json
```

### P206. Split `base_dependency_array_read_helper_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block array read helper group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_array_read_helper_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_array_value_reader_helper_group.php` (`1`) и `base_dependency_array_like_checker_helper_group.php` (`1`);
- ключи `arrayValueReader`, `arrayLikeChecker` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Array|Read|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P207. Split `base_dependency_array_transform_helper_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block array transform helper group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_array_transform_helper_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_array_adducer_helper_group.php` (`1`) и `base_dependency_recursive_merger_helper_group.php` (`1`);
- ключи `arrayAdducer`, `recursiveMerger` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Array|Transform|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P208. Split `base_dependency_block_factory_context_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block factory context group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_block_factory_context_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_block_factory_group.php` (`1`) и `base_dependency_block_exception_factory_group.php` (`1`);
- ключи `blockFactory`, `blockExceptionFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Factory|Context|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_block_factory_context_group.php --json
```

Focused verification для P204-P208:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Application|Runtime|Support|Array|Read|Transform|Factory|Context|AiTooling|DynamicBoundary|Composition'
```

Результат: `250 tests`, `80940 assertions`.

### P209. Split `base_dependency_block_file_storage_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block file storage group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_block_file_storage_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_block_file_storage_block_group.php` (`1`) и `base_dependency_block_file_storage_meta_group.php` (`1`);
- ключи `blockFileStorage`, `metaFileStorage` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Storage|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_block_file_storage_group.php --json
```

### P210. Split `base_dependency_data_core_factory_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block data core factory group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_data_core_factory_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_entity_data_core_factory_group.php` (`1`) и `base_dependency_json_data_core_factory_group.php` (`1`);
- ключи `entityFactory`, `jsonFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Data|Core|Factory|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_data_core_factory_group.php --json
```

### P211. Split `base_dependency_data_loader_factory_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block data loader factory group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_data_loader_factory_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_data_loader_service_factory_group.php` (`1`) и `base_dependency_pager_data_loader_factory_group.php` (`1`);
- ключи `dataLoaderFactory`, `pagerFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Data|Loader|Factory|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P212. Split `base_dependency_media_core_factory_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block media core factory group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_media_core_factory_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_obfuscator_media_core_factory_group.php` (`1`) и `base_dependency_image_modify_media_core_factory_group.php` (`1`);
- ключи `obfuscatorFactory`, `imageModifyFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Media|Core|Factory|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P213. Split `base_dependency_media_error_helper_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block media error helper group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_media_error_helper_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_image_metadata_media_error_helper_group.php` (`1`) и `base_dependency_error_log_media_error_helper_group.php` (`1`);
- ключи `imageMetadataReader`, `errorLogWriter` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Media|Error|Helper|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_media_error_helper_group.php --json
```

Focused verification для P209-P213:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Storage|Data|Core|Loader|Media|Error|Factory|Helper|AiTooling|DynamicBoundary|Composition'
```

Результат: `271 tests`, `99901 assertions`.

### P214. Split `base_dependency_meta_maker_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block meta maker group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_meta_maker_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_meta_maker_state_group.php` (`1`) и `base_dependency_meta_maker_factory_group.php` (`1`);
- ключи `metaMakerState`, `metaMakerFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Meta|Maker|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_meta_maker_group.php --json
```

### P215. Split `base_dependency_meta_row_loader_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block meta row loader group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_meta_row_loader_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_php_array_file_loader_group.php` (`1`) и `base_dependency_meta_row_factory_group.php` (`1`);
- ключи `phpArrayFileLoader`, `metaRowFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Meta|Row|Loader|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_meta_row_loader_group.php --json
```

### P216. Split `base_dependency_project_file_storage_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block project file storage group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_project_file_storage_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_project_tool_file_storage_group.php` (`1`) и `base_dependency_root_html_file_storage_group.php` (`1`);
- ключи `projectToolFileStorage`, `rootHtmlFileStorage` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Project|Storage|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P217. Split `base_dependency_request_role_context_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block request role context group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_request_role_context_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_request_factory_context_group.php` (`1`) и `base_dependency_role_factory_context_group.php` (`1`);
- ключи `requestFactory`, `roleFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Request|Role|Context|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P218. Split `base_dependency_route_locale_context_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block route locale context group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_route_locale_context_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_locale_factory_context_group.php` (`1`) и `base_dependency_matcher_factory_context_group.php` (`1`);
- ключи `localeFactory`, `matcherFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Route|Locale|Context|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_route_locale_context_group.php --json
```

Focused verification для P214-P218:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Meta|Maker|Row|Project|Storage|Request|Role|Route|Locale|AiTooling|DynamicBoundary|Composition'
```

Результат: `199 tests`, `79606 assertions`.

### P219. Split `base_dependency_tab_runtime_context_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block tab runtime context group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_tab_runtime_context_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_tab_service_runtime_context_group.php` (`1`) и `base_dependency_bootstrap_runtime_context_group.php` (`1`);
- ключи `tab`, `runtime` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Tab|Runtime|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_tab_runtime_context_group.php --json
```

### P220. Split `base_dependency_view_factory_loader_group`

Статус: выполнено 2026-06-13.

Цель: уменьшить block view factory loader group (`2`) без изменения dependency keys.

Итог:

- `base_dependency_view_factory_loader_group.php` стал фасадом с count `0`;
- добавлены `base_dependency_view_parser_exception_factory_loader_group.php` (`1`) и `base_dependency_view_router_factory_loader_group.php` (`1`);
- ключи `viewParserExceptionFactory`, `viewRouterFactory` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|View|Factory|Loader|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/block/base_dependency_view_factory_loader_group.php --json
```

### P221. Split `application_client_array_payload_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить client array payload group (`2`) без изменения public dependency methods.

Итог:

- `application_client_array_payload_dependencies.php` стал фасадом с count `0`;
- добавлены `application_client_array_adducer_payload_dependencies.php` (`1`) и `application_client_array_value_reader_payload_dependencies.php` (`1`);
- методы `arrayAdducer()`, `arrayValueReader()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationClientServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Client|Array|Payload|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P222. Split `application_client_config_cache_runtime_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить client config/cache runtime group (`2`) без изменения public dependency methods.

Итог:

- `application_client_config_cache_runtime_dependencies.php` стал фасадом с count `0`;
- добавлены `application_client_config_runtime_dependencies.php` (`1`) и `application_client_cache_runtime_dependencies.php` (`1`);
- методы `config()`, `cacheFactory()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationClientServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Client|Config|Cache|Runtime|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P223. Split `application_client_curl_transport_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить client curl transport group (`2`) без изменения public dependency methods.

Итог:

- `application_client_curl_transport_dependencies.php` стал фасадом с count `0`;
- добавлены `application_client_curl_adapter_transport_dependencies.php` (`1`) и `application_client_curl_factory_transport_dependencies.php` (`1`);
- методы `curlAdapter()`, `curlFactory()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationClientServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Client|Curl|Transport|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_client_curl_transport_dependencies.php --json
```

Focused verification для P219-P223:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/BaseTest.php unit/core/di/ApplicationClientServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Base|BlockDependency|Tab|Runtime|View|Factory|Loader|Client|Array|Payload|Config|Cache|Curl|Transport|AiTooling|DynamicBoundary|Composition'
```

Результат: `270 tests`, `87331 assertions`.

### P224. Split `application_client_serialization_payload_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить client serialization payload group (`2`) без изменения public dependency methods.

Итог:

- `application_client_serialization_payload_dependencies.php` стал фасадом с count `0`;
- добавлены `application_client_serializer_operations_payload_dependencies.php` (`1`) и `application_client_cookie_writer_payload_dependencies.php` (`1`);
- методы `serializerOperations()`, `cookieWriter()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationClientServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Client|Serialization|Payload|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_client_serialization_payload_dependencies.php --json
```

### P225. Split `application_content_config_cache_runtime_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить content config/cache runtime group (`2`) без изменения public dependency methods.

Итог:

- `application_content_config_cache_runtime_dependencies.php` стал фасадом с count `0`;
- добавлены `application_content_config_runtime_dependencies.php` (`1`) и `application_content_cache_runtime_dependencies.php` (`1`);
- методы `config()`, `cacheFactory()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationContentServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Content|Config|Cache|Runtime|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P226. Split `application_content_error_block_context_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить content error/block context group (`2`) без изменения public dependency methods.

Итог:

- `application_content_error_block_context_dependencies.php` стал фасадом с count `0`;
- добавлены `application_content_error_context_dependencies.php` (`1`) и `application_content_block_context_dependencies.php` (`1`);
- методы `error()`, `blockContext()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationContentServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Content|Error|Block|Context|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P227. Split `application_content_localization_context_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить content localization context group (`2`) без изменения public dependency methods.

Итог:

- `application_content_localization_context_dependencies.php` стал фасадом с count `0`;
- добавлены `application_content_locale_context_dependencies.php` (`1`) и `application_content_tab_factory_context_dependencies.php` (`1`);
- методы `locale()`, `tabFactory()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationContentServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Content|Localization|Locale|Tab|Context|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_content_localization_context_dependencies.php --json
```

### P228. Split `application_content_request_matcher_context_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить content request/matcher context group (`2`) без изменения public dependency methods.

Итог:

- `application_content_request_matcher_context_dependencies.php` стал фасадом с count `0`;
- добавлены `application_content_matcher_context_dependencies.php` (`1`) и `application_content_request_input_context_dependencies.php` (`1`);
- методы `matcher()`, `requestInput()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationContentServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Content|Request|Matcher|Context|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_content_request_matcher_context_dependencies.php --json
```

Focused verification для P224-P228:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationClientServiceCreatorTest.php unit/core/di/ApplicationContentServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Client|Content|Serialization|Payload|Config|Cache|Runtime|Error|Block|Localization|Locale|Tab|Request|Matcher|Context|AiTooling|DynamicBoundary|Composition'
```

Результат: `219 tests`, `83951 assertions`.

### P229. Split `application_content_storage_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить content storage group (`2`) без изменения public dependency methods.

Итог:

- `application_content_storage_dependencies.php` стал фасадом с count `0`;
- добавлены `application_content_php_array_file_loader_storage_dependencies.php` (`1`) и `application_content_translation_file_storage_dependencies.php` (`1`);
- методы `phpArrayFileLoader()`, `translationFileStorage()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationContentServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Content|Storage|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_content_storage_dependencies.php --json
```

### P230. Split `application_controller_config_cache_runtime_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить controller config/cache runtime group (`2`) без изменения public dependency methods.

Итог:

- `application_controller_config_cache_runtime_dependencies.php` стал фасадом с count `0`;
- добавлены `application_controller_config_runtime_dependencies.php` (`1`) и `application_controller_cache_runtime_dependencies.php` (`1`);
- методы `config()`, `cacheFactory()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationControllerServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Controller|Config|Cache|Runtime|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P231. Split `application_controller_obfuscator_handler_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить controller obfuscator handler group (`2`) без изменения public dependency methods.

Итог:

- `application_controller_obfuscator_handler_dependencies.php` стал фасадом с count `0`;
- добавлены `application_controller_obfuscator_factory_handler_dependencies.php` (`1`) и `application_controller_request_handler_dependencies.php` (`1`);
- методы `obfuscatorFactory()`, `request()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationControllerServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Controller|Obfuscator|Handler|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P232. Split `application_controller_plain_route_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить controller plain route group (`2`) без изменения public dependency methods.

Итог:

- `application_controller_plain_route_dependencies.php` стал фасадом с count `0`;
- добавлены `application_controller_matcher_plain_route_dependencies.php` (`1`) и `application_controller_header_plain_route_dependencies.php` (`1`);
- методы `matcher()`, `header()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationControllerServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Controller|Plain|Route|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_controller_plain_route_dependencies.php --json
```

### P233. Split `application_core_project_error_service_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core project error service group (`2`) без изменения public dependency methods.

Итог:

- `application_core_project_error_service_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_project_error_context_dependencies.php` (`1`) и `application_core_project_error_factory_service_dependencies.php` (`1`);
- методы `error()`, `errorFactory()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Project|Error|Service|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_core_project_error_service_dependencies.php --json
```

Focused verification для P229-P233:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationContentServiceCreatorTest.php unit/core/di/ApplicationControllerServiceCreatorTest.php unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Content|Controller|Core|Project|Storage|Config|Cache|Runtime|Obfuscator|Handler|Plain|Route|Error|Service|AiTooling|DynamicBoundary|Composition'
```

Результат: `259 tests`, `92993 assertions`.

### P234. Split `application_core_project_error_storage_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core project error storage group (`2`) без изменения public dependency methods.

Итог:

- `application_core_project_error_storage_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_project_error_log_writer_storage_dependencies.php` (`1`) и `application_core_project_error_file_storage_dependencies.php` (`1`);
- методы `errorLogWriter()`, `errorFileStorage()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Project|Error|Storage|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_core_project_error_storage_dependencies.php --json
```

### P235. Split `application_core_project_reflection_meta_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core project reflection/meta group (`2`) без изменения public dependency methods.

Итог:

- `application_core_project_reflection_meta_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_project_reflection_class_factory_meta_dependencies.php` (`1`) и `application_core_project_meta_file_storage_dependencies.php` (`1`);
- методы `reflectionClassFactory()`, `metaFileStorage()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Project|Reflection|Meta|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P236. Split `application_core_project_response_loader_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core project response loader group (`2`) без изменения public dependency methods.

Итог:

- `application_core_project_response_loader_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_project_header_writer_response_loader_dependencies.php` (`1`) и `application_core_project_php_array_file_loader_response_loader_dependencies.php` (`1`);
- методы `headerWriter()`, `phpArrayFileLoader()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Project|Response|Loader|AiTooling|DynamicBoundary|Composition'
php fan ai:map --validate --json
```

### P237. Split `application_core_project_tab_service_context_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core project tab service context group (`2`) без изменения public dependency methods.

Итог:

- `application_core_project_tab_service_context_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_project_tab_instance_service_context_dependencies.php` (`1`) и `application_core_project_tab_factory_service_context_dependencies.php` (`1`);
- методы `tab()`, `tabFactory()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Project|Tab|Service|Context|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_core_project_tab_service_context_dependencies.php --json
```

### P238. Split `application_core_request_array_read_class_helper_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core request array-read/class-helper group (`2`) без изменения public dependency methods.

Итог:

- `application_core_request_array_read_class_helper_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_request_array_value_reader_helper_dependencies.php` (`1`) и `application_core_request_class_name_resolver_helper_dependencies.php` (`1`);
- методы `arrayValueReader()`, `classNameResolver()` сохранены.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Request|Array|Read|Class|Helper|AiTooling|DynamicBoundary|Composition'
php fan ai:dynamic-boundaries core/di/application_core_request_array_read_class_helper_dependencies.php --json
```

Focused verification для P234-P238:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Project|Error|Storage|Reflection|Meta|Response|Loader|Tab|Service|Context|Request|Array|Read|Class|Helper|AiTooling|DynamicBoundary|Composition'
```

Результат: `262 tests`, `98801 assertions`.

### P239. Split `application_core_request_array_transform_helper_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core request array-transform helper group (`2`) без изменения public dependency methods.

Итог:

- `application_core_request_array_transform_helper_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_request_array_adducer_transform_helper_dependencies.php` (`1`) и `application_core_request_recursive_merger_transform_helper_dependencies.php` (`1`);
- методы `arrayAdducer()` и `recursiveMerger()` сохранены.

### P240. Split `application_core_request_runtime_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core request runtime factory group (`2`) без изменения public dependency methods.

Итог:

- `application_core_request_runtime_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_request_matcher_factory_runtime_dependencies.php` (`1`) и `application_core_request_request_factory_runtime_dependencies.php` (`1`);
- методы `matcherFactory()` и `requestFactory()` сохранены.

### P241. Split `application_core_request_transport_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core request transport factory group (`2`) без изменения public dependency methods.

Итог:

- `application_core_request_transport_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_request_json_transport_factory_dependencies.php` (`1`) и `application_core_request_cookie_transport_factory_dependencies.php` (`1`);
- методы `jsonFactory()` и `cookieFactory()` сохранены.

### P242. Split `application_core_user_data_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core user data factory group (`2`) без изменения public dependency methods.

Итог:

- `application_core_user_data_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_user_date_data_factory_dependencies.php` (`1`) и `application_core_user_entity_data_factory_dependencies.php` (`1`);
- методы `dateFactory()` и `entityFactory()` сохранены.

### P243. Split `application_core_user_session_space_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить core user session-space group (`2`) без изменения public dependency methods.

Итог:

- `application_core_user_session_space_dependencies.php` стал фасадом с count `0`;
- добавлены `application_core_user_session_factory_session_space_dependencies.php` (`1`) и `application_core_user_current_user_space_factory_session_space_dependencies.php` (`1`);
- методы `sessionFactory()` и `currentUserSpaceFactory()` сохранены.

Focused verification для P239-P243:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Core|Request|Array|Transform|Runtime|Factory|Transport|User|Session|Data|Space|AiTooling|DynamicBoundary|Composition'
```

Результат: `262 tests`, `109157 assertions`.

### P244. Split `application_creator_config_cache_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить common creator config/cache group (`2`) без изменения public dependency methods.

Итог:

- `application_creator_config_cache_dependencies.php` стал фасадом с count `0`;
- добавлены `application_creator_config_dependencies.php` (`1`) и `application_creator_cache_factory_dependencies.php` (`1`);
- методы `config()` и `cacheFactory()` сохранены.

### P245. Split `application_infrastructure_config_cache_cache_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить infrastructure config-cache cache-factory group (`2`) без изменения public dependency methods.

Итог:

- `application_infrastructure_config_cache_cache_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_infrastructure_config_cache_config_cache_factory_dependencies.php` (`1`) и `application_infrastructure_config_cache_type_cache_factory_dependencies.php` (`1`);
- методы `configCacheFactory()` и `cacheFactory()` сохранены.

### P246. Split `application_infrastructure_config_cache_config_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить infrastructure config-cache config-factory group (`2`) без изменения public dependency methods.

Итог:

- `application_infrastructure_config_cache_config_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_infrastructure_config_cache_config_instance_dependencies.php` (`1`) и `application_infrastructure_config_cache_typed_config_factory_dependencies.php` (`1`);
- методы `config()` и `configFactory()` сохранены.

### P247. Split `application_infrastructure_config_cache_exception_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить infrastructure config-cache exception-factory group (`2`) без изменения public dependency methods.

Итог:

- `application_infrastructure_config_cache_exception_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_infrastructure_config_cache_core_fatal_exception_factory_dependencies.php` (`1`) и `application_infrastructure_config_cache_error500_exception_factory_dependencies.php` (`1`);
- методы `coreFatalExceptionFactory()` и `error500ExceptionFactory()` сохранены.

### P248. Split `application_infrastructure_config_cache_loader_serializer_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить infrastructure config-cache loader/serializer group (`2`) без изменения public dependency methods.

Итог:

- `application_infrastructure_config_cache_loader_serializer_dependencies.php` стал фасадом с count `0`;
- добавлены `application_infrastructure_config_cache_php_array_file_loader_dependencies.php` (`1`) и `application_infrastructure_config_cache_serializer_operations_dependencies.php` (`1`);
- методы `phpArrayFileLoader()` и `serializerOperations()` сохранены.

Focused verification для P244-P248:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationCoreServiceCreatorTest.php unit/core/di/ApplicationInfrastructureServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Creator|Infrastructure|Config|Cache|Factory|Loader|Serializer|Exception|AiTooling|DynamicBoundary|Composition'
```

Результат: `241 tests`, `92917 assertions`.

### P249. Split `application_infrastructure_config_cache_request_header_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить infrastructure config-cache request/header group (`2`) без изменения public dependency methods.

Итог:

- `application_infrastructure_config_cache_request_header_dependencies.php` стал фасадом с count `0`;
- добавлены `application_infrastructure_config_cache_request_input_dependencies.php` (`1`) и `application_infrastructure_config_cache_header_writer_dependencies.php` (`1`);
- методы `requestInput()` и `headerWriter()` сохранены.

### P250. Split `application_infrastructure_config_cache_runtime_support_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить infrastructure config-cache runtime-support group (`2`) без изменения public dependency methods.

Итог:

- `application_infrastructure_config_cache_runtime_support_dependencies.php` стал фасадом с count `0`;
- добавлены `application_infrastructure_config_cache_bootstrap_runtime_support_dependencies.php` (`1`) и `application_infrastructure_config_cache_error_factory_runtime_support_dependencies.php` (`1`);
- методы `bootstrapRuntime()` и `errorFactory()` сохранены.

### P251. Split `application_infrastructure_config_cache_storage_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить infrastructure config-cache storage group (`2`) без изменения public dependency methods.

Итог:

- `application_infrastructure_config_cache_storage_dependencies.php` стал фасадом с count `0`;
- добавлены `application_infrastructure_config_cache_cache_source_file_metadata_storage_dependencies.php` (`1`) и `application_infrastructure_config_cache_config_source_file_storage_dependencies.php` (`1`);
- методы `cacheSourceFileMetadata()` и `configSourceFileStorage()` сохранены.

### P252. Split `application_infrastructure_runtime_config_cache_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить infrastructure runtime config-cache group (`2`) без изменения public dependency methods.

Итог:

- `application_infrastructure_runtime_config_cache_dependencies.php` стал фасадом с count `0`;
- добавлены `application_infrastructure_runtime_config_dependencies.php` (`1`) и `application_infrastructure_runtime_cache_factory_dependencies.php` (`1`);
- методы `config()` и `cacheFactory()` сохранены.

### P253. Split `application_infrastructure_runtime_error_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить infrastructure runtime error group (`2`) без изменения public dependency methods.

Итог:

- `application_infrastructure_runtime_error_dependencies.php` стал фасадом с count `0`;
- добавлены `application_infrastructure_runtime_error_factory_dependencies.php` (`1`) и `application_infrastructure_runtime_bootstrap_runtime_error_dependencies.php` (`1`);
- методы `errorFactory()` и `bootstrapRuntime()` сохранены.

Focused verification для P249-P253:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationInfrastructureServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Infrastructure|Config|Cache|Runtime|Error|Storage|Request|Header|AiTooling|DynamicBoundary|Composition'
```

Результат: `200 tests`, `82853 assertions`.

### P254. Split `application_navigation_tab_block_meta_file_storage_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab block/meta file-storage group (`2`) без изменения public dependency methods.

Итог:

- `application_navigation_tab_block_meta_file_storage_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_block_file_storage_dependencies.php` (`1`) и `application_navigation_tab_meta_file_storage_dependencies.php` (`1`);
- методы `blockFileStorage()` и `metaFileStorage()` сохранены.

### P255. Split `application_navigation_tab_data_cookie_payload_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab data/cookie payload group (`2`) без изменения public dependency methods.

Итог:

- `application_navigation_tab_data_cookie_payload_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_data_loader_payload_dependencies.php` (`1`) и `application_navigation_tab_cookie_payload_dependencies.php` (`1`);
- методы `dataLoaderFactory()` и `cookieFactory()` сохранены.

### P256. Split `application_navigation_tab_data_model_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab data model-factory group (`2`) без изменения public dependency methods.

Итог:

- `application_navigation_tab_data_model_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_entity_model_factory_dependencies.php` (`1`) и `application_navigation_tab_pager_model_factory_dependencies.php` (`1`);
- методы `entityFactory()` и `pagerFactory()` сохранены.

### P257. Split `application_navigation_tab_locale_session_context_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab locale/session context group (`2`) без изменения public dependency methods.

Итог:

- `application_navigation_tab_locale_session_context_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_locale_context_dependencies.php` (`1`) и `application_navigation_tab_session_factory_context_dependencies.php` (`1`);
- методы `locale()` и `sessionFactory()` сохранены.

### P258. Split `application_navigation_tab_media_error_asset_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab media-error asset group (`2`) без изменения public dependency methods.

Итог:

- `application_navigation_tab_media_error_asset_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_image_metadata_reader_asset_dependencies.php` (`1`) и `application_navigation_tab_error_log_writer_asset_dependencies.php` (`1`);
- методы `imageMetadataReader()` и `errorLogWriter()` сохранены.

Focused verification для P254-P258:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|Storage|Payload|Model|Locale|Session|Media|Asset|AiTooling|DynamicBoundary|Composition'
```

Результат: `144 tests`, `78897 assertions`.

### P259. Split `application_navigation_tab_media_model_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab media model-factory group (`2`) без изменения public dependency methods.

Итог:

- `application_navigation_tab_media_model_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_obfuscator_model_factory_dependencies.php` (`1`) и `application_navigation_tab_image_modify_model_factory_dependencies.php` (`1`);
- методы `obfuscatorFactory()` и `imageModifyFactory()` сохранены.

### P260. Split `application_navigation_tab_routing_context_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab routing context group (`2`) без изменения public dependency methods.

Итог:

- `application_navigation_tab_routing_context_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_matcher_routing_context_dependencies.php` (`1`) и `application_navigation_tab_request_routing_context_dependencies.php` (`1`);
- методы `matcher()` и `request()` сохранены.

### P261. Split `application_navigation_tab_service_factory_application_debug_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab application/debug factory group (`2`) без изменения public dependency methods.

Итог:

- `application_navigation_tab_service_factory_application_debug_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_application_factory_application_debug_dependencies.php` (`1`) и `application_navigation_tab_debug_factory_application_debug_dependencies.php` (`1`);
- методы `applicationFactory()` и `debugFactory()` сохранены.

### P262. Split `application_navigation_tab_service_factory_config_header_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab config/header factory group (`2`) без изменения public dependency methods.

Итог:

- `application_navigation_tab_service_factory_config_header_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_config_factory_config_header_dependencies.php` (`1`) и `application_navigation_tab_header_factory_config_header_dependencies.php` (`1`);
- методы `configFactory()` и `headerFactory()` сохранены.

### P263. Split `application_navigation_tab_service_factory_error_reflector_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab error/reflector factory group (`2`) без изменения public dependency methods.

Итог:

- `application_navigation_tab_service_factory_error_reflector_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_error_factory_error_reflector_dependencies.php` (`1`) и `application_navigation_tab_reflector_factory_error_reflector_dependencies.php` (`1`);
- методы `errorFactory()` и `reflectorFactory()` сохранены.

Focused verification для P259-P263:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|Model|Routing|Factory|Config|Header|Error|Reflector|AiTooling|DynamicBoundary|Composition'
```

Результат: `216 tests`, `93625 assertions`.

### P264. Split `application_navigation_tab_service_factory_role_transfer_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab role/transfer factory group (`2`) без изменения public dependency methods.

Итог:

- `application_navigation_tab_service_factory_role_transfer_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_role_factory_role_transfer_dependencies.php` (`1`) и `application_navigation_tab_transfer_factory_role_transfer_dependencies.php` (`1`);
- методы `roleFactory()` и `transferFactory()` сохранены.

### P265. Split `application_navigation_tab_support_array_read_check_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab array read/check group (`2`) без изменения public dependency methods.

Итог:

- `application_navigation_tab_support_array_read_check_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_array_value_reader_read_check_dependencies.php` (`1`) и `application_navigation_tab_array_like_checker_read_check_dependencies.php` (`1`);
- методы `arrayValueReader()` и `arrayLikeChecker()` сохранены.

### P266. Split `application_navigation_tab_support_array_transform_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab array transform group (`2`) без изменения public dependency methods.

Итог:

- `application_navigation_tab_support_array_transform_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_array_adducer_transform_dependencies.php` (`1`) и `application_navigation_tab_recursive_merger_transform_dependencies.php` (`1`);
- методы `arrayAdducer()` и `recursiveMerger()` сохранены.

### P267. Split `application_navigation_tab_support_block_exception_meta_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab block exception/meta group (`2`) без изменения public dependency methods.

Итог:

- `application_navigation_tab_support_block_exception_meta_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_block_exception_factory_exception_meta_dependencies.php` (`1`) и `application_navigation_tab_meta_row_factory_exception_meta_dependencies.php` (`1`);
- методы `blockExceptionFactory()` и `metaRowFactory()` сохранены.

### P268. Split `application_navigation_tab_support_block_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab block factory group (`2`) без изменения public dependency methods.

Итог:

- `application_navigation_tab_support_block_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_tab_state_block_factory_dependencies.php` (`1`) и `application_navigation_tab_block_factory_instance_block_factory_dependencies.php` (`1`);
- методы `tabState()` и `blockFactory()` сохранены.

Focused verification для P264-P268:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|Role|Transfer|Array|Block|Factory|Exception|Meta|AiTooling|DynamicBoundary|Composition'
```

Результат: `229 tests`, `95372 assertions`.

Full verification после P264-P268:

```bash
php tools/ai_verify.php --json
php tools/ai_static_check.php --json
php fan ai:map --validate
php fan ai:dynamic-boundaries --next --json
php fan ai:dynamic-boundaries --composition --json
```

Результат: `ai_verify` pass, PHPUnit `1918 tests`, `158964 assertions`; `ai_static_check` pass; `ai:map --validate` pass; `--next` вернул `[]`; top composition roots начинаются с `application_navigation_tab_support_class_helper_dependencies.php`, `application_navigation_tab_user_time_model_factory_dependencies.php`, `application_pager_config_cache_runtime_dependencies.php`, `application_session_application_context_dependencies.php`, `application_session_bootstrap_runtime_dependencies.php`.

### P269. Split `application_navigation_tab_support_class_helper_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab class helper group (`2`) без изменения public dependency methods.

Итог:

- `application_navigation_tab_support_class_helper_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_class_name_resolver_class_helper_dependencies.php` (`1`) и `application_navigation_tab_short_class_name_resolver_class_helper_dependencies.php` (`1`);
- методы `classNameResolver()` и `shortClassNameResolver()` сохранены.

### P270. Split `application_navigation_tab_user_time_model_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить navigation tab user/time model-factory group (`2`) без изменения public dependency methods.

Итог:

- `application_navigation_tab_user_time_model_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_navigation_tab_user_factory_user_time_model_factory_dependencies.php` (`1`) и `application_navigation_tab_date_factory_user_time_model_factory_dependencies.php` (`1`);
- методы `userFactory()` и `dateFactory()` сохранены.

### P271. Split `application_pager_config_cache_runtime_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить pager config/cache runtime group (`2`) без изменения public dependency methods.

Итог:

- `application_pager_config_cache_runtime_dependencies.php` стал фасадом с count `0`;
- добавлены `application_pager_config_config_cache_runtime_dependencies.php` (`1`) и `application_pager_cache_factory_config_cache_runtime_dependencies.php` (`1`);
- методы `config()` и `cacheFactory()` сохранены.

### P272. Split `application_session_application_context_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить session application context group (`2`) без изменения public dependency methods.

Итог:

- `application_session_application_context_dependencies.php` стал фасадом с count `0`;
- добавлены `application_session_config_application_context_dependencies.php` (`1`) и `application_session_application_instance_application_context_dependencies.php` (`1`);
- методы `config()` и `application()` сохранены.

### P273. Split `application_session_bootstrap_runtime_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить session bootstrap runtime group (`2`) без изменения public dependency methods.

Итог:

- `application_session_bootstrap_runtime_dependencies.php` стал фасадом с count `0`;
- добавлены `application_session_bootstrap_bootstrap_runtime_dependencies.php` (`1`) и `application_session_php_runtime_settings_bootstrap_runtime_dependencies.php` (`1`);
- методы `bootstrapRuntime()` и `phpRuntimeSettings()` сохранены.

Focused verification для P269-P273:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationNavigationServiceCreatorTest.php unit/core/di/ApplicationPagerServiceCreatorTest.php unit/core/di/ApplicationSessionServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Navigation|Tab|Class|User|Date|Pager|Config|Cache|Session|Application|Bootstrap|Runtime|AiTooling|DynamicBoundary|Composition'
```

Результат: `223 tests`, `97607 assertions`.

Full verification после P269-P273:

```bash
php tools/ai_verify.php --json
php tools/ai_static_check.php --json
php fan ai:map --validate
php fan ai:dynamic-boundaries --next --json
php fan ai:dynamic-boundaries --composition --json
```

Результат: `ai_verify` pass, PHPUnit `1918 tests`, `160758 assertions`; `ai_static_check` pass; `ai:map --validate` pass; `--next` вернул `[]`; top composition roots начинаются с `application_session_native_runtime_dependencies.php`, `application_session_request_context_dependencies.php`, `application_session_state_factory_dependencies.php`, `application_session_support_factory_dependencies.php`, `application_user_application_request_context_dependencies.php`.

### P274. Split `application_session_native_runtime_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить session native runtime group (`2`) без изменения public dependency methods.

Итог:

- `application_session_native_runtime_dependencies.php` стал фасадом с count `0`;
- добавлены `application_session_pear_http_session_loader_native_runtime_dependencies.php` (`1`) и `application_session_native_session_native_runtime_dependencies.php` (`1`);
- методы `pearHttpSessionLoader()` и `nativeSession()` сохранены.

### P275. Split `application_session_request_context_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить session request context group (`2`) без изменения public dependency methods.

Итог:

- `application_session_request_context_dependencies.php` стал фасадом с count `0`;
- добавлены `application_session_request_input_request_context_dependencies.php` (`1`) и `application_session_request_instance_request_context_dependencies.php` (`1`);
- методы `requestInput()` и `request()` сохранены.

### P276. Split `application_session_state_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить session state factory group (`2`) без изменения public dependency methods.

Итог:

- `application_session_state_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_session_session_factory_state_factory_dependencies.php` (`1`) и `application_session_cookie_factory_state_factory_dependencies.php` (`1`);
- методы `sessionFactory()` и `cookieFactory()` сохранены.

### P277. Split `application_session_support_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить session support factory group (`2`) без изменения public dependency methods.

Итог:

- `application_session_support_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_session_error_factory_support_factory_dependencies.php` (`1`) и `application_session_date_factory_support_factory_dependencies.php` (`1`);
- методы `errorFactory()` и `dateFactory()` сохранены.

### P278. Split `application_user_application_request_context_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить user application/request context group (`2`) без изменения public dependency methods.

Итог:

- `application_user_application_request_context_dependencies.php` стал фасадом с count `0`;
- добавлены `application_user_request_application_request_context_dependencies.php` (`1`) и `application_user_application_instance_application_request_context_dependencies.php` (`1`);
- методы `request()` и `application()` сохранены.

Focused verification для P274-P278:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationSessionServiceCreatorTest.php unit/core/di/ApplicationUserServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Session|Native|Request|State|Support|Factory|User|Application|AiTooling|DynamicBoundary|Composition'
```

Результат: `233 tests`, `99966 assertions`.

Full verification после P274-P278:

```bash
php tools/ai_verify.php --json
php tools/ai_static_check.php --json
php fan ai:map --validate
php fan ai:dynamic-boundaries --next --json
php fan ai:dynamic-boundaries --composition --json
```

Результат: `ai_verify` pass, PHPUnit `1918 tests`, `162553 assertions`; `ai_static_check` pass; `ai:map --validate` pass; `--next` вернул `[]`; top composition roots начинаются с `application_user_application_request_input_factory_dependencies.php`, `application_user_bootstrap_cache_runtime_dependencies.php`, `application_user_exception_session_context_dependencies.php`, `application_user_identity_factory_dependencies.php`, `application_user_serialization_config_context_dependencies.php`.

### P279. Split `application_user_application_request_input_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить user application/request-input factory group (`2`) без изменения public dependency methods.

Итог:

- `application_user_application_request_input_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_user_application_factory_application_request_input_factory_dependencies.php` (`1`) и `application_user_request_input_factory_application_request_input_factory_dependencies.php` (`1`);
- методы `applicationFactory()` и `requestInputFactory()` сохранены.

### P280. Split `application_user_bootstrap_cache_runtime_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить user bootstrap/cache runtime group (`2`) без изменения public dependency methods.

Итог:

- `application_user_bootstrap_cache_runtime_dependencies.php` стал фасадом с count `0`;
- добавлены `application_user_bootstrap_runtime_bootstrap_cache_runtime_dependencies.php` (`1`) и `application_user_cache_factory_bootstrap_cache_runtime_dependencies.php` (`1`);
- методы `bootstrapRuntime()` и `cacheFactory()` сохранены.

### P281. Split `application_user_exception_session_context_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить user exception/session context group (`2`) без изменения public dependency methods.

Итог:

- `application_user_exception_session_context_dependencies.php` стал фасадом с count `0`;
- добавлены `application_user_error500_exception_factory_exception_session_context_dependencies.php` (`1`) и `application_user_session_exception_session_context_dependencies.php` (`1`);
- методы `error500ExceptionFactory()` и `session()` сохранены.

### P282. Split `application_user_identity_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить user identity factory group (`2`) без изменения public dependency methods.

Итог:

- `application_user_identity_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_user_session_factory_identity_factory_dependencies.php` (`1`) и `application_user_current_user_factory_identity_factory_dependencies.php` (`1`);
- методы `sessionFactory()` и `currentUserFactory()` сохранены.

### P283. Split `application_user_serialization_config_context_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить user serialization/config context group (`2`) без изменения public dependency methods.

Итог:

- `application_user_serialization_config_context_dependencies.php` стал фасадом с count `0`;
- добавлены `application_user_serializer_operations_serialization_config_context_dependencies.php` (`1`) и `application_user_config_serialization_config_context_dependencies.php` (`1`);
- методы `serializerOperations()` и `config()` сохранены.

Focused verification для P279-P283:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationUserServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'User|Application|Request|Input|Bootstrap|Cache|Exception|Session|Identity|Serialization|Config|Factory|AiTooling|DynamicBoundary|Composition'
```

Результат: `237 tests`, `112957 assertions`.

Full verification после P279-P283:

```bash
php tools/ai_verify.php --json
php tools/ai_static_check.php --json
php fan ai:map --validate
php fan ai:dynamic-boundaries --next --json
php fan ai:dynamic-boundaries --composition --json
```

Результат: `ai_verify` pass, PHPUnit `1918 tests`, `164346 assertions`; `ai_static_check` pass; `ai:map --validate` pass; `--next` вернул `[]`; top composition roots начинаются с `application_user_support_factory_dependencies.php`, `application_utility_config_cache_core_dependencies.php`, `application_utility_file_storage_dependencies.php`, `application_utility_helper_error_core_dependencies.php`, `application_utility_image_canvas_output_dependencies.php`.

### P284. Split `application_user_support_factory_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить user support factory group (`2`) без изменения public dependency methods.

Итог:

- `application_user_support_factory_dependencies.php` стал фасадом с count `0`;
- добавлены `application_user_error_factory_support_factory_dependencies.php` (`1`) и `application_user_entity_factory_support_factory_dependencies.php` (`1`);
- методы `errorFactory()` и `entityFactory()` сохранены.

### P285. Split `application_utility_config_cache_core_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить utility config/cache core group (`2`) без изменения public dependency methods.

Итог:

- `application_utility_config_cache_core_dependencies.php` стал фасадом с count `0`;
- добавлены `application_utility_config_config_cache_core_dependencies.php` (`1`) и `application_utility_cache_factory_config_cache_core_dependencies.php` (`1`);
- методы `config()` и `cacheFactory()` сохранены.

### P286. Split `application_utility_file_storage_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить utility file-storage group (`2`) без изменения public dependency methods.

Итог:

- `application_utility_file_storage_dependencies.php` стал фасадом с count `0`;
- добавлены `application_utility_php_array_file_loader_file_storage_dependencies.php` (`1`) и `application_utility_soap_wsdl_file_storage_file_storage_dependencies.php` (`1`);
- методы `phpArrayFileLoader()` и `soapWsdlFileStorage()` сохранены.

### P287. Split `application_utility_helper_error_core_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить utility helper/error core group (`2`) без изменения public dependency methods.

Итог:

- `application_utility_helper_error_core_dependencies.php` стал фасадом с count `0`;
- добавлены `application_utility_array_value_reader_helper_error_core_dependencies.php` (`1`) и `application_utility_error_factory_helper_error_core_dependencies.php` (`1`);
- методы `arrayValueReader()` и `errorFactory()` сохранены.

### P288. Split `application_utility_image_canvas_output_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить utility image canvas/output group (`2`) без изменения public dependency methods.

Итог:

- `application_utility_image_canvas_output_dependencies.php` стал фасадом с count `0`;
- добавлены `application_utility_image_canvas_operations_canvas_output_dependencies.php` (`1`) и `application_utility_image_output_writer_canvas_output_dependencies.php` (`1`);
- методы `imageCanvasOperations()` и `imageOutputWriter()` сохранены.

Focused verification для P284-P288:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationUserServiceCreatorTest.php unit/core/di/ApplicationUtilityServiceCreatorTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'User|Utility|Support|Factory|Config|Cache|File|Storage|Helper|Error|Image|Canvas|Output|AiTooling|DynamicBoundary|Composition'
```

Результат: `256 tests`, `105688 assertions`.

Full verification после P284-P288:

```bash
php tools/ai_verify.php --json
php tools/ai_static_check.php --json
php fan ai:map --validate
php fan ai:dynamic-boundaries --next --json
php fan ai:dynamic-boundaries --composition --json
```

Результат: `ai_verify` pass, PHPUnit `1918 tests`, `166140 assertions`; `ai_static_check` pass; `ai:map --validate` pass; `--next` вернул `[]`; top composition roots начинаются с `application_utility_image_metadata_resource_dependencies.php`, `application_utility_image_storage_dependencies.php`, `application_utility_runtime_core_dependencies.php`, `application_runtime_factory_defaults_provider_factory.php`, `bootstrap_object_defaults_provider_factory.php`.

### P289. Split `application_utility_image_metadata_resource_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить utility image metadata/resource group (`2`) без изменения public dependency methods.

Итог:

- `application_utility_image_metadata_resource_dependencies.php` стал фасадом с count `0`;
- добавлены `application_utility_image_metadata_reader_metadata_resource_dependencies.php` (`1`) и `application_utility_image_resource_factory_metadata_resource_dependencies.php` (`1`);
- методы `imageMetadataReader()` и `imageResourceFactory()` сохранены.

### P290. Split `application_utility_image_storage_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить utility image storage group (`2`) без изменения public dependency methods.

Итог:

- `application_utility_image_storage_dependencies.php` стал фасадом с count `0`;
- добавлены `application_utility_obfuscator_file_storage_image_storage_dependencies.php` (`1`) и `application_utility_image_source_file_storage_image_storage_dependencies.php` (`1`);
- методы `obfuscatorFileStorage()` и `imageSourceFileStorage()` сохранены.

### P291. Split `application_utility_runtime_core_dependencies`

Статус: выполнено 2026-06-13.

Цель: уменьшить utility runtime core group (`2`) без изменения public dependency methods.

Итог:

- `application_utility_runtime_core_dependencies.php` стал фасадом с count `0`;
- добавлены `application_utility_bootstrap_runtime_runtime_core_dependencies.php` (`1`) и `application_utility_php_runtime_settings_runtime_core_dependencies.php` (`1`);
- методы `bootstrapRuntime()` и `phpRuntimeSettings()` сохранены.

### P292. Split `application_runtime_factory_defaults_provider_factory`

Статус: выполнено 2026-06-13.

Цель: уменьшить runtime defaults provider factory (`2`) без изменения constructor injection behavior.

Итог:

- `application_runtime_factory_defaults_provider_factory.php` стал thin assembly surface с dynamic-boundary count `0`;
- добавлены `application_runtime_configured_service_provider.php` (`1`) и `application_runtime_class_instantiator_provider.php` (`1`);
- injected `configuredServiceFactoryProvider` и `classInstantiatorProvider` behavior сохранен.

### P293. Split `bootstrap_object_defaults_provider_factory`

Статус: выполнено 2026-06-13.

Цель: уменьшить bootstrap object defaults provider factory (`2`) без изменения constructor injection behavior.

Итог:

- `bootstrap_object_defaults_provider_factory.php` стал thin assembly surface с dynamic-boundary count `0`;
- добавлены `bootstrap_object_configured_service_provider.php` (`1`) и `bootstrap_object_class_instantiator_provider.php` (`1`);
- injected `configuredServiceFactoryProvider` и `classInstantiatorProvider` behavior сохранен.

Focused verification для P289-P293:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ApplicationUtilityServiceCreatorTest.php unit/core/di/ApplicationRuntimeFactoryDefaultsProviderFactoryTest.php unit/core/application/BootstrapObjectDefaultsProviderFactoryTest.php unit/core/di/ApplicationRuntimeFactoryProviderTest.php unit/core/di/ApplicationContainerDependencyProviderTest.php unit/core/CoreSourceInventoryTest.php unit/core/AiToolingTest.php unit/core/LegacyDiSourceInventoryTest.php --filter 'Utility|Runtime|Bootstrap|Object|Defaults|Provider|Factory|Configured|DynamicBoundary|Composition|Inventory|AiTooling|Container'
```

Результат: `360 tests`, `157716 assertions`.

Full verification после P289-P293:

```bash
php tools/ai_verify.php --json
php tools/ai_static_check.php --json
php fan ai:map --validate
php fan ai:dynamic-boundaries --next --json
php fan ai:dynamic-boundaries --composition --json
```

Результат: `ai_verify` pass, PHPUnit `1918 tests`, `167932 assertions`; `ai_static_check` pass; `ai:map --validate` pass; `--next` вернул `[]`; top composition roots теперь все count `1`, начиная с `base_dependency_application_service_runtime_factory_group.php`, `base_dependency_array_adducer_helper_group.php`, `base_dependency_array_like_checker_helper_group.php`, `base_dependency_array_value_reader_helper_group.php`, `base_dependency_block_exception_factory_group.php`.

## Что не делать сейчас

- Не переписывать block/meta/template runtime сразу.
- Не удалять `core/functions.php` массово: он уже почти стал BC-зоной.
- Не заменять весь DI на сторонний container одним проходом.
- Не вводить modules/DDD поверх текущего framework, пока не уменьшен оставшийся model service-locator хвост.
- Не делать тяжелый CLI поверх Symfony Console, пока `php fan ai:*` остается достаточно тонким и полезным мостом.
