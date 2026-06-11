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
