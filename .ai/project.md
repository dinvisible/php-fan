# PHP-FAN AI Project Contract

This repository contains PHP-FAN 5, a legacy-compatible PHP framework that has
already been moved toward PHP 8, Composer autoloading, explicit dependency
registration, and source-inventory tests.

## Entry Points

- Web entry point: `htdocs/index.php`
- Composer autoload: `vendor/autoload.php`
- Main application initializer: `fan\core\di\web_application_initializer_defaults_factory`

## Source Roots

- `core/` - framework core, adapters, bootstrap/application, DI, services, view, block, model layers.
- `project/` - default project/application layer used by the framework.
- `htdocs/` - public web root and web entry point.
- `tools/` - local developer and AI helper scripts.
- `unit/` - PHPUnit tests and source-inventory guards.

## Important Generated Or Runtime Directories

- `vendor/` is Composer dependency output.
- `node_modules/` is Node dependency output and must not be tracked.
- `logs/` contains runtime logs.
- `temp_data/` contains runtime cache and file-data output.
- `ai/**/.venv/` contains local Python virtual environments and must not be tracked.

## Current State

- PHP requirement: `>=8.2`
- Production PHP uses `declare(strict_types=1)`.
- Composer PSR-4 namespaces are defined in `composer.json`.
- The full PHPUnit suite is expected to run through `php vendor/bin/phpunit --configuration phpunit.xml`.
- Global `composer` may be unavailable; prefer local executable fallbacks.

## AI Goal

Make the framework self-describing for coding agents without breaking legacy
runtime behavior. Prefer small verified refactor slices over broad rewrites.
