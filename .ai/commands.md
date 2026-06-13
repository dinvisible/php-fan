# PHP-FAN Commands For AI

Run commands from the repository root.

## Test Suite

Preferred full test command:

```bash
php vendor/bin/phpunit --configuration phpunit.xml
```

Composer script, when a global Composer binary exists:

```bash
composer test
```

## AI Map

Machine-readable project map:

```bash
php tools/ai_map.php --json
```

Markdown summary:

```bash
php tools/ai_map.php
```

Validate the map contract:

```bash
php fan ai:map --validate
```

Dynamic-boundary queues:

```bash
php fan ai:dynamic-boundaries --next --json
php fan ai:dynamic-boundaries --debt --json
php fan ai:dynamic-boundaries --composition --json
php fan ai:dynamic-boundaries --composition-open --json
```

Source-inventory queues after dynamic-boundary debt is clean:

```bash
php fan ai:source-inventory --json
php fan ai:source-inventory --next --json
php fan ai:source-inventory unmanaged_container_lookups --json
php fan ai:source-inventory bootstrap_container_lookups --json
php fan ai:source-inventory composition_container_lookups --json
```

## Completion Audit

When the refactor route looks clean, prove it with the full completion-audit
set:

```bash
php fan ai:dynamic-boundaries --next --json
php fan ai:dynamic-boundaries --composition-open --json
php fan ai:dynamic-boundaries --debt --json
php fan ai:source-inventory --next --json
php fan ai:source-inventory --json
php tools/ai_verify.php --json
git diff --check
```

Expected actionable outputs at completion:

- `dynamic-boundaries --next`: `[]`
- `dynamic-boundaries --composition-open`: `[]`
- `dynamic-boundaries --debt`: `[]`
- `source-inventory --next`: `[]`
- `service_locator_calls`, `unmanaged_container_lookups`, and
  `unmanaged_loading_statements`: `clean`, count `0`

Non-empty bootstrap, composition, intentional loading, tooling, and entrypoint
queues are allowed only when classified and documented in `.ai/architecture.md`.

## AI Verification

Preferred verification command for agents:

```bash
php tools/ai_verify.php
```

## Focused PHPUnit

For a narrow change, run the related test file plus source guards, for example:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/di/ContainerTest.php unit/core/LegacyDiSourceInventoryTest.php
```

## Source Inventory

Useful local checks:

```bash
rg --files core project htdocs tools unit -g '*.php'
rg -n "getService\\s*\\(" core/base/model core/service/entity -g '*.php'
rg -n "class_exists\\s*\\(|ReflectionClass|configured_class_instantiator" core project htdocs -g '*.php'
```

## Git Hygiene

Dependency and runtime-output directories must not be tracked:

```bash
git ls-files node_modules
git ls-files 'ai/**/.venv'
```
