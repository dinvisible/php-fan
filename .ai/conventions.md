# PHP-FAN AI Conventions

## General

- Keep `declare(strict_types=1)` in every PHP source file.
- Prefer existing project patterns over introducing a new framework style.
- Keep refactors small and covered by focused tests.
- Do not replace legacy compatibility layers until a narrow adapter boundary exists.

## Dependency Injection

- Use constructor injection and explicit factories for new code.
- Keep string service ids inside DI composition roots when possible.
- For touched DI code, prefer `fan\core\di\service_id` constants over raw service-id strings.
- Do not add new service locator gateways.

## Dynamic Runtime

- Reflection and `class_exists()` are allowed only around documented extension or compatibility boundaries.
- Raw superglobals belong in request/session adapters or diagnostics only.
- Manual `include`/`require` belongs in file-loading adapters and entry points only.

## Blocks, Meta, And Templates

- `*.meta.php` and `*.tpl` are legacy compatibility inputs.
- Do not change their behavior while working on DI or AI tooling.
- When touching meta/template behavior, add or update a focused contract test.

## Testing Policy

- Always run a focused PHPUnit slice for runtime changes.
- Run `php tools/ai_verify.php` before finishing a multi-file change.
- Keep source-inventory tests current when adding or removing architectural guardrails.

## Workspace Hygiene

- Do not track dependency outputs: `vendor/`, `node_modules/`.
- Do not track local virtual environments: `ai/**/.venv/`.
- Do not track runtime logs or cache output.
