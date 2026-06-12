# AI-first refactor plan for PHP-FAN

Дата анализа: 2026-06-11

Дата обновления: 2026-06-12, после P1-P13 AI-first implementation batch.

Ветка: `migrate_to-php8`

Цель: заточить `php-fan-5` под быструю, безопасную и качественную работу AI-агентов без переписывания framework одним рискованным проходом.

## Короткий вывод

Проект уже прошел большую часть тяжелой PHP 8 / DI-модернизации. Это не хаотичный legacy baseline: есть `strict_types`, PSR-4 autoload, явный `container`, dependency registrars/factories, большое покрытие unit-тестами и source-inventory guards.

Первый AI-first слой уже добавлен и расширен до рабочего P1-P13 batch. Framework теперь имеет базовый self-describing contract для агента:

- `.ai/` documentation contract;
- `tools/ai_map.php --json`;
- `tools/ai_verify.php`;
- `tools/ai_explain.php`;
- `tools/ai_static_check.php`;
- `php fan ai:*` bridge;
- `fan\core\di\service_id` для начала типизации service ids;
- `services.descriptors` в AI map;
- `.ai/meta.schema.json` и metadata/template map;
- `.ai/map.schema.json` и `php fan ai:map --validate`;
- реальные `phpstan`/`php-cs-fixer` dev tools с baseline gate;
- hygiene guard против tracked dependency/runtime noise.

Следующий лучший ход не "переписать как Laravel" и не "ввести DDD везде". P14-P18 закрыли следующий AI-friendly слой: `row.php` больше не дергает `getEntityByTable()` напрямую, `core/di` защищен от raw service ids, PHPStan baseline уменьшен, AI map descriptors получили factory metadata, а ключевые `entity.php` service calls заведены за collaborators. Теперь цель - добивать оставшиеся BC fallback-и и static baseline маленькими срезами.

## Статус миграции

| Срез | Статус | Доказательство |
|---|---|---|
| Workspace hygiene | Готово | `.gitignore` расширен; `node_modules/`, `.DS_Store`, virtualenv/runtime noise сняты с tracked set. |
| `.ai/` contract | Готово | `.ai/project.md`, `.ai/architecture.md`, `.ai/commands.md`, `.ai/conventions.md`. |
| AI map | Готово++ | `php tools/ai_map.php --json` строит JSON-карту проекта, service descriptors и metadata/template map; `.ai/map.schema.json`; `php fan ai:map --validate`. |
| AI verify | Готово++ | `php tools/ai_verify.php` запускает tracked-noise check, `git diff --check`, PHP lint changed files, `ai_map` contract validation, static baseline и PHPUnit/focused PHPUnit. |
| Service id constants, DI срез | Готово для текущего среза | `core/di/service_id.php`; DI registrars/creators/adapters/state registry переведены на constants для service ids; raw `factory/get/alias` ids в `core/di` guard-проверены. |
| Model/entity collaborator extraction | Продолжается | `request.php` больше не вызывает `$entity->getService()->getSqlDir()` напрямую; `EntityIdDecoder` callable прокидывается через `model_entity_factory`; `file_data/row.php` больше не вызывает `getEncapsulant()->decryptId()` напрямую. |
| Meta/template schema | Готово+ | `.ai/meta.schema.json`; `ai_map.metadata` экспортирует meta keys, paired templates и template placeholders; `MetaFilesTest` валидирует meta contract и template paths. |
| Static analysis / formatter | Готово+ | `phpstan/phpstan`, `friendsofphp/php-cs-fixer`, `phpstan.neon.dist`, generated `phpstan-baseline.neon`, `.php-cs-fixer.dist.php`, `tools/ai_static_check.php`. |
| Framework CLI bridge | Готово+ | `php fan ai:map --validate`, `ai:services`, `ai:explain`, `ai:verify`, `ai:doctor`, `ai:static`. |

## Текущий срез

Проверено локально:

- `php tools/ai_verify.php`
- результат: `PASS`;
- полный PHPUnit внутри verify проходит через `php vendor/bin/phpunit --configuration phpunit.xml`;
- `php tools/ai_verify.php --changed` запускает focused PHPUnit;
- `php fan ai:doctor --no-phpunit` проходит через CLI bridge;
- `tools/ai_static_check.php` запускает реальные PHPStan/PHP-CS-Fixer, если binaries установлены, и пропускает их только в fallback mode;
- production PHP-срез в `ai_map`: 507 файлов;
- `declare(strict_types=1)` есть во всех 507 production PHP-файлах;
- namespace есть в 460 production PHP-файлах;
- `*.meta.php` в `core/project/htdocs`: 17 файлов;
- `*.tpl` в `core/project/htdocs`: 16 файлов.

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

Статус: закрыто для базового graph-descriptor слоя; продолжается для richer factory/class edges.

`tools/ai_map.php --json` уже экспортирует:

- Composer autoload;
- entrypoints;
- source/test roots;
- meta/template files;
- registered/referenced service ids;
- `service_id` constants;
- `services.descriptors` с registrar files, creator methods, shared/non-shared и direct dependencies.

Оставшийся долг: service graph пока не всегда описывает concrete class/factory/config-key edges. AI иногда все еще вынужден проходить цепочку:

```text
registrar -> creator -> project service class -> factory -> config -> string service id
```

Следующий слой descriptor enrichment:

```php
final class service_descriptor
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $class,
        public readonly ?string $factory,
        public readonly bool $shared,
        public readonly array $dependencies,
        public readonly string $registrar
    ) {}
}
```

Следующий шаг: расширить уже существующий `service_descriptor`, добавив `class`, `factory`, `config_key`, `lifetime_reason` там, где это извлекается статически.

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

### 6. Model/entity слой все еще тянет service locator назад

Текущий grep показывает `getService()` хвост в model/entity слое:

- `core/base/model/entity.php`
- `core/base/model/row.php`
- `core/base/model/spec_file/row.php`
- `core/base/model/spec_file/image/entity.php`
- `core/base/model/spec_file/image/row.php`
- `core/base/model/file_data/row.php`

`core/base/model/request.php` уже не вызывает `$entity->getService()->getSqlDir()` напрямую: SQL directory вынесен в collaborator resolver/accessor.

Это уже не старый глобальный service locator, но для AI это скрытая зависимость. Следующий refactor-срез: выделять collaborators из entity service:

- `EntityDescriptionProvider`
- `EntityDesignerFactory`
- `EntityLookup`
- `EntityIdCodec`
- `EntityFileDataFactory`

Двигаться по одному use case, с focused tests.

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

Статус: config baseline готов, binaries еще не установлены.

Добавлено:

- `phpstan.neon.dist`;
- `phpstan-baseline.neon`;
- `.php-cs-fixer.dist.php`;
- `tools/ai_static_check.php`;
- Composer scripts `ai:*`.

Следующий шаг:

- добавить `phpstan/phpstan` и `friendsofphp/php-cs-fixer` в `require-dev`;
- Rector для механических PHP upgrades/refactors;
- сделать formatter check частью обязательного CI, когда binary появится.

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

## Рекомендуемые следующие PR-срезы после P14-P18

| PR | Название | Почему следующий | Основная проверка |
|---:|---|---|---|
| 1 | P19 Collapse `entity.php` BC fallback count | Новые collaborators уже есть; следующий выигрыш - убрать fallback `getService()` там, где factory всегда инъектит dependency. | `unit/core/base/model/EntityTest.php`; `LegacyDiSourceInventoryTest.php`; `php tools/ai_verify.php --changed` |
| 2 | P20 PHPStan baseline burn-down #2 | Следующие самые изолированные entries: `pear_http_session` adapter/loader или `file_data/entity_member`. | `php tools/ai_static_check.php`; focused adapter tests |
| 3 | P21 AI map JSON Schema validation runner | Schema уже богаче; можно добавить реальную JSON Schema validation command/test, а не только hand-written contract checks. | `php fan ai:map --validate --json`; `unit/core/AiToolingTest.php` |
| 4 | P22 Runtime argument descriptors for creator methods | Сейчас runtime args извлекаются из registration closures; следующий слой - public creator method signatures. | `unit/core/AiToolingTest.php`; `php tools/ai_map.php --json` |
| 5 | P23 Source inventory for direct `$this->service` usage | После `getService()` allowlist стоит закрепить и прямой доступ к service property как explicit BC-boundary. | `unit/core/LegacyDiSourceInventoryTest.php` |

## Что не делать сейчас

- Не переписывать block/meta/template runtime сразу.
- Не удалять `core/functions.php` массово: он уже почти стал BC-зоной.
- Не заменять весь DI на сторонний container одним проходом.
- Не вводить modules/DDD поверх текущего framework, пока не уменьшен оставшийся model service-locator хвост.
- Не делать тяжелый CLI поверх Symfony Console, пока `php fan ai:*` остается достаточно тонким и полезным мостом.
