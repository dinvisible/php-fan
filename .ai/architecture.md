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

## AI-First Direction

1. Keep runtime compatibility.
2. Export a machine-readable project map.
3. Add one verification command for agents.
4. Introduce typed service id constants for new or touched DI code.
5. Gradually replace hidden model/entity service lookups with explicit collaborators.
