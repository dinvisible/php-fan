# PHP-FAN Architecture Notes For AI

## High-Level Shape

PHP-FAN is currently a legacy-compatible framework with an explicit DI
composition layer. It is not yet a module-first framework. Do not impose a new
module architecture until the AI map and verification commands are stable.

## Runtime Flow

1. `htdocs/index.php` loads Composer autoload.
2. `web_application_initializer_defaults_factory` builds the web initializer.
3. The bootstrap/application layer creates context, state, loader, container,
   request runner, and runtime services.
4. `application_service_graph_registrar` delegates service registration to
   focused registrars.
5. The runner asks the matcher for the current handler and executes it.

## Main Layers

- `core/application/` - bootstrap context, state, loader, runner, initializer.
- `core/di/` - service graph registration and creator classes.
- `core/factory/` - explicit factory boundaries and default providers.
- `core/adapter/` - file system, request globals, template, logging, and IO boundaries.
- `core/service/` - framework services.
- `core/block/` - legacy block/presentation runtime.
- `core/base/model/` - legacy model/entity runtime.
- `core/view/` - view parsers, routers, keepers.
- `project/` - default project-level classes and config.

## Dynamic Boundaries

The project still intentionally has dynamic compatibility subsystems:

- service ids are string-based in many composition roots;
- class-name strings and reflection exist around config/meta extension points;
- `*.meta.php` files are dynamic metadata;
- `*.tpl` files are legacy templates;
- request/session globals are isolated in adapters.

Keep these dynamic boundaries documented and narrow. New code should prefer
explicit dependencies and typed factories.

## Reflection And Configured-Service Boundaries

Reflection and configured class construction are allowed only when they are
serving one of these documented roles:

- Extension API: project classes, optional adapters, legacy service classes, and
  template/meta integrations that must be selected by name at runtime.
- Composition root: `core/di/*_service_creator.php` and default-provider
  factories that connect configuration to explicit factories.
- Tooling support: AI map/explain/verification helpers and Composer/autoload
  adapters that inspect source or class availability.

Everything else is migration debt. Prefer a named boundary before adding a new
dynamic call:

- inject a `callable` availability checker instead of scattering
  `class_exists(...)`;
- use `configured_class_instantiator` or an explicit factory instead of
  `new \ReflectionClass(...)` in domain code;
- keep direct `configured_service_factory` construction in default providers or
  composition roots;
- add a source-inventory guard when a dynamic boundary is intentionally kept.

Current guard: `LegacyDiSourceInventoryTest::testDynamicReflectionAndConfiguredServiceBoundariesAreInventoried()`.
DI service creators route project service class checks through the named
`projectServiceClassExists(...)` boundary. Direct method-body
`if (!class_exists($className))` checks in `core/di/*_service_creator.php` are
guarded as regressions.

The AI map exposes categorized dynamic boundary locations at
`dynamic_boundaries.locations`. `php fan ai:explain <file> --json` returns the
same file-local list in `dynamic_boundary_details.locations`. Each location has
`boundary_kind`, currently either `method_body_or_runtime_boundary` or
`named_default_closure_boundary`. Treat line-level `class_exists`, `new
\ReflectionClass`, configured factory, include, callback, and dynamic
construction locations as the actionable inventory. Type hints such as
`\ReflectionClass` are not dynamic runtime debt by themselves.
Use `php fan ai:dynamic-boundaries --json` when you need the compact
file/category/count/patterns readout before choosing the next boundary to
refactor.
Use `php fan ai:dynamic-boundaries <file> --json` when you need exact file-local
locations. `boundary_kind = named_default_closure_boundary` means the low-level
call is already behind an injectable default closure; keep it visible in
inventory, but rank it below direct method-body debt.
Examples include `bootstrap_loader_file_storage::load()` file loading and
adapter/service class availability callbacks such as `viewClassExists(...)` or
`timerClassExists(...)`.
Use `php fan ai:dynamic-boundaries --direct --json` when you want only
`method_body_or_runtime_boundary` entries for the next refactor candidate.
Use `php fan ai:dynamic-boundaries --next --json` when you want direct
method-body/runtime candidates with `composition_roots` excluded; this is the
fastest route command after production runtime debt has been reduced.
Use `php fan ai:dynamic-boundaries --debt --json` when you want actionable
named/default `migration_debt` only. Intentional compatibility boundaries stay
visible in `dynamic_boundaries.intentional_named_compatibility_boundaries`, while
`dynamic_boundaries.actionable_named_migration_debt` is the short refactor queue.
Use `php fan ai:dynamic-boundaries --composition --json` for the ranked
composition-root queue; it sorts by location count so DI creator reduction can
start from the noisiest roots without hiding the inventory.
Use `php fan ai:dynamic-boundaries --composition-open --json` for the actionable
composition-root queue. Count `1` composition roots are terminal leaves:
they stay visible in `dynamic_boundaries.terminal_composition_leaves` and in the
full `--composition` inventory, but they are not split targets. Count `2+`
composition roots are listed in `dynamic_boundaries.actionable_composition_roots`
and have `composition_leaf_kind = actionable_composition_root`; count `1`
entries have `composition_leaf_kind = terminal_composition_leaf`.

After `--next`, `--composition-open`, and `--debt` are empty, use
`php fan ai:source-inventory --next --json` as the next refactor route.
`source_inventory.queues` separates clean guards, actionable source-level
residue, bootstrap/default-provider boundaries, composition boundaries,
intentional compatibility boundaries, tooling support, and entrypoint boundaries.
Current post-dynamic work should start from `unmanaged_container_lookups`, while
`bootstrap_container_lookups` and `composition_container_lookups` stay visible as
intentional wiring boundaries. `service_locator_calls` and
`unmanaged_loading_statements` must stay clean.

When `php fan ai:source-inventory --next --json` returns `[]`, the actionable
AI-first refactor route is closed. Do not invent a new extraction target from
classified boundary queues just because they are non-empty. Instead, run the
completion audit:

- `php fan ai:dynamic-boundaries --next --json` must be `[]`.
- `php fan ai:dynamic-boundaries --composition-open --json` must be `[]`.
- `php fan ai:dynamic-boundaries --debt --json` must be `[]`.
- `php fan ai:source-inventory --next --json` must be `[]`.
- `service_locator_calls`, `unmanaged_container_lookups`, and
  `unmanaged_loading_statements` must be `clean` with count `0`.
- `bootstrap_container_lookups` remains a bootstrap/default-provider boundary;
  the current audited surface is `25` lookups across `7` files:
  `core/application/application.php`, `core/application/context.php`,
  `core/di/application_compiled_template_adapter_defaults_provider.php`,
  `core/di/application_image_adapter_defaults_provider.php`,
  `core/di/application_state_registry.php`,
  `core/di/application_storage_adapter_defaults_provider.php`, and
  `core/factory/application_registry_defaults_provider_factory.php`.
- `composition_container_lookups` remains a terminal composition-leaf boundary;
  the current audited surface is `272` one-lookup files and
  `--composition-open` is the guard that prevents new multi-lookup roots.
- loading statements remain classified as intentional adapter, tooling, or
  entrypoint boundaries; `unmanaged_loading_statements` must stay clean.

If any completion-audit command returns a non-empty actionable queue, create a
new measured route from that queue. If only classified bootstrap, composition,
loading, tooling, or entrypoint queues remain, treat the AI-first migration as
complete for code refactoring and keep those queues documented.

Stable boundary-kind/filtering logic lives in `fan\core\ai\dynamic_boundary`;
`tools/ai_map.php` keeps compatibility wrapper functions for CLI/tests.

DI creators and block resolvers may share explicit composition-root bundles such as
`application_creator_common_dependencies` plus bootstrap-runtime/config-cache/config/cache-factory
groups for repeated runtime/config/cache wiring. Larger roots should split into
named dependency surfaces instead of
hiding lookups inside untracked helpers. Current examples include
`base_dependency_factory_group` plus its application/data/media factory groups,
where data factories are split into data-core/data-loader groups and media
factories are split into media-core/media-transfer groups,
`base_dependency_context_group` plus its runtime/request/navigation context groups,
where runtime context is split into tab-runtime/request-input groups, request
context is split into request-role/session groups, and navigation context is
split into reflector/route-locale groups,
`base_dependency_view_meta_group` plus its view/meta/block-factory groups,
where view loading is split into view-factory/view-state groups and meta loading
is split into meta-maker/meta-row-loader groups,
`application_core_service_dependencies` plus its project/request/user-session
groups, where request factory is split into input/runtime/runtime-matcher/runtime-request/transport/transport-json/transport-cookie groups,
request helper is split into array-transform/array-transform-adducer/array-transform-recursive-merger/array-read-class/array-value-reader/class-name-resolver groups,
and core user-session is split into identity/data/date/entity groups, where identity is split
into current-user/session-space/session-factory/current-user-space-factory groups,
`application_core_project_dependencies` plus its error/storage/tab groups,
where tab dependencies are split into tab-context/application-context/route-storage
groups, tab context is split into tab-service/tab-instance/tab-factory/locale groups, error dependencies
are split into error-service/error/error-factory and error-storage/error-log-writer/error-file-storage groups, and storage dependencies are
split into reflection-meta/reflection-class-factory/meta-file-storage and response-loader/header-writer/php-array-file-loader groups,
`application_utility_service_dependencies` plus its core/core-runtime/core-config-cache/core-helper-error/image/image-storage/image-metadata-resource/image-canvas-output/storage/storage-file/storage-class groups,
where core runtime is split into bootstrap-runtime/php-runtime-settings leaves, core config-cache is split into config/cache-factory leaves, core helper-error is split into array-value-reader/error-factory leaves, storage-file is split into php-array-file-loader/soap-wsdl-file-storage leaves, image storage is split into obfuscator-file-storage/image-source-file-storage leaves, image metadata-resource is split into image-metadata-reader/image-resource-factory leaves, and image canvas-output is split into image-canvas-operations/image-output-writer leaves,
`application_client_service_dependencies` plus its payload/payload-array/payload-array-adducer/payload-array-value-reader/payload-request/payload-serialization/payload-serializer-operations/payload-cookie-writer/runtime/runtime-bootstrap/runtime-config-cache/runtime-config/runtime-cache/transport/transport-curl/transport-curl-adapter/transport-curl-factory/transport-serialization/transport-error
groups,
`application_content_service_dependencies` plus its context/runtime/runtime-bootstrap/runtime-config-cache/runtime-config/runtime-cache/storage/storage-php-array-file-loader/storage-translation-file groups,
where context is split into localization/locale/tab-factory/error-block/error/block/request-matcher/matcher/request-input groups,
`application_controller_service_dependencies` plus its plain/plain-route/plain-route-matcher/plain-route-header/plain-config/handler/handler-obfuscator/handler-obfuscator-factory/handler-request/handler-plain-file/runtime/runtime-bootstrap/runtime-config-cache/runtime-config/runtime-cache groups,
`application_core_request_dependencies` plus its request-factory/helper groups, where runtime factory is split into matcher/request groups, transport factory is split into json/cookie groups, array-transform helper is split into array-adducer/recursive-merger groups, and array-read-class helper is split into array-value-reader/class-name-resolver groups,
`application_navigation_tab_dependencies` plus its
core/context/context-routing/context-locale-session/context-input/service-factory/service-factory-application/service-factory-application-debug/service-factory-role-transfer/service-factory-payload/service-factory-runtime/service-factory-config-header/service-factory-error-reflector/model-factory/model-data/model-media/model-user-time/support/support-block/support-block-factory/support-block-exception-meta/support-helper/support-helper-loader/support-helper-array/support-helper-array-transform/support-helper-array-read-check/support-helper-class/support-asset/storage/storage-file/storage-project-tool/storage-upload-limit groups,
where routing context is split into matcher/request groups, locale-session context is split into locale/session-factory groups,
service-factory application-debug/config-header/error-reflector/role-transfer groups are split into application/debug, config/header, error/reflector, and role/transfer leaves, service-factory payload is split into json/data-cookie/data-loader/cookie groups,
model-data is split into entity/pager groups, model-media is split into obfuscator/image-modify groups, model-user-time is split into user/date factory leaves, support asset is split into alias/media-error/image-metadata-reader/error-log-writer groups,
support block factory is split into tab-state/block-factory leaves, support block exception-meta is split into block-exception/meta-row leaves, support array transform is split into array-adducer/recursive-merger leaves, support array read-check is split into array-value-reader/array-like-checker leaves,
support class helper is split into class-name/short-class-name resolver leaves,
and storage file is split into block-meta/block-file/meta-file/root-html groups,
`application_pager_service_dependencies` plus its context/context-entity/context-tab/context-request/runtime/runtime-bootstrap/runtime-config-cache/exception groups,
where runtime config-cache is split into config/cache-factory leaves,
`application_session_service_dependencies` plus its context/factory/runtime
groups, where context is split into application/request/header groups, application context is split into config/application leaves, factory
is split into support/state/cache groups, request context is split into request-input/request leaves, support factory is split into error/date leaves, state factory is split into session/cookie leaves, and runtime is split into
native/bootstrap/array groups, with native runtime split into pear-http-session-loader/native-session leaves and bootstrap runtime split into bootstrap-runtime/php-runtime-settings leaves,
`application_user_service_dependencies` plus its context/context-serialization-config/context-application-request/context-exception-session/factory/application-factory/application-config-factory/application-request-input-factory/identity-factory/support-factory/runtime/runtime-bootstrap-cache/runtime-array groups,
where application-request context is split into request/application leaves, application-request-input factory is split into application/request-input factory leaves, exception-session context is split into error500-exception/session leaves, identity factory is split into session/current-user factory leaves, serialization-config context is split into serializer-operations/config leaves, support factory is split into error/entity factory leaves, and bootstrap-cache runtime is split into bootstrap-runtime/cache-factory leaves,
and
`application_infrastructure_config_cache_dependencies` plus its
factory/factory-config/factory-config-instance/factory-typed-config/factory-cache/factory-config-cache/factory-type-cache/support/support-runtime/support-bootstrap-runtime/support-error-factory/support-loader-serializer/support-php-array-file-loader/support-serializer-operations/support-class-helper/storage/storage-cache-metadata/storage-config-source/exception/exception-factory/exception-core-fatal/exception-error500/request-header/request-input/header-writer groups, and
`application_infrastructure_service_dependencies` plus its runtime/runtime-error/runtime-error-factory/runtime-bootstrap-runtime/runtime-config-cache/runtime-config/runtime-cache-factory/storage
groups. Block helper dependencies similarly use `base_dependency_helper_group`
plus array/array-transform/array-transform-adducer/array-transform-recursive-merger/array-read/array-read-value-reader/array-read-like-checker/class/media-error helper groups,
block application factory wiring uses `base_dependency_application_factory_group`
plus runtime/runtime-application-service/runtime-config/data/data-database/data-user/support/support-error/support-date
factory groups, block data/media wiring splits data-core/entity/json,
data-loader/data-loader-service/pager, media-core/obfuscator/image-modify, and
media-error/image-metadata/error-log groups, block view meta wiring keeps block
factory context behind factory/exception-factory groups, meta wiring splits
meta-maker/state/factory and meta-row-loader/php-array-file-loader/meta-row-factory
groups, route/request context wiring splits request/role and locale/matcher
factory groups, block tab runtime wiring splits tab-service/bootstrap-runtime
groups, block view factory loader wiring splits parser-exception/router groups,
and block storage wiring uses `base_dependency_storage_group`
plus block-file/block-file-storage/meta-file-storage/project-file/project-tool/root-html/upload-limit storage groups.

Factory defaults providers such as `application_runtime_factory_defaults_provider_factory`
and `bootstrap_object_defaults_provider_factory` keep configured construction behind
separate callable provider leaves for configured-service and class-instantiator defaults.
These leaves remain tracked composition roots, while the parent defaults-provider factories
stay thin assembly surfaces.

These bundles must stay
in `dynamic_boundaries.categories.composition_roots`, and service descriptor
extraction must preserve the dependencies represented by named methods such as
`$coreDependencies->requestInput()`, `$coreDependencies->matcherRouteFileStorage()`,
`$utilityDependencies->cacheFactory()`,
`$clientDependencies->serializerOperations()`,
`$contentDependencies->translationFileStorage()`,
`$controllerDependencies->plainFileContext()`, `$tabDependencies->matcher()`,
`$sessionDependencies->cacheFactory()`, `$userDependencies->sessionFactory()`, and
`$infrastructureDependencies->cacheSourceFileMetadata()`,
`$infrastructureDependencies->shortClassNameResolver()`,
`$infrastructureDependencies->fileSystemStorage()`, and
`$pagerDependencies->entityFactory()`.

`core/service/reflector.php` now uses the injected reflection class factory and
is no longer listed as migration debt. `core/base/model/entity_dependencies.php`
owns the default model class availability callback as an extension compatibility
boundary, while `core/base/model/entity.php` keeps only the injected
`modelClassExists(...)` collaborator and has no dynamic-boundary locations.

## Service Reference Provenance

Use the AI map before opening broad DI source files:

- `php fan ai:services <service-id> --json` shows the service descriptor,
  outgoing `dependencies`, runtime arguments, aliases, source edges, source
  locations, and descriptor-local `referenced_by` reverse references.
- `php fan ai:explain <file> --json` shows file-local
  `service_reference_locations`, `related_service_ids`, compact
  `related_service_descriptors`, and dynamic boundary details for one file.
- `services.referenced_locations` is the global service id -> file/line index.
  Use it for blast-radius checks before renaming or moving a service id.
- `source_locations.dependencies` describes outgoing dependencies of a
  descriptor creator/registration path.
- `referenced_by` describes incoming references to one service id from other
  production files.
- `source_locations.aliases` and `source_edges.aliases` describe aliases owned
  by the target service descriptor. Keep the top-level `services.aliases` map as
  the global alias lookup.

When refactoring service graph code, compare the local file view from
`ai:explain` with the service view from `ai:services`. The first tells you what
this file touches; the second tells you what else touches the service.

## AI-First Direction

1. Keep runtime compatibility.
2. Export a machine-readable project map.
3. Add one verification command for agents.
4. Introduce typed service id constants for new or touched DI code.
5. Gradually replace hidden model/entity service lookups with explicit collaborators.
