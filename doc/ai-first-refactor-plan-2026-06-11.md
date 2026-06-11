# AI-first refactor plan for PHP-FAN

Дата анализа: 2026-06-11

Ветка: `migrate_to-php8`

Цель: заточить `php-fan-5` под быструю, безопасную и качественную работу AI-агентов без переписывания framework одним рискованным проходом.

## Короткий вывод

Проект уже прошел большую часть тяжелой PHP 8 / DI-модернизации. Это не хаотичный legacy baseline: есть `strict_types`, PSR-4 autoload, явный `container`, dependency registrars/factories, большое покрытие unit-тестами и source-inventory guards.

Следующий лучший ход не "переписать как Laravel" и не "ввести DDD везде". Для AI важнее сделать framework самопоясняющимся:

- добавить машинно-читаемую карту проекта;
- ввести явные AI/documentation manifests;
- сделать быстрые единые verification-команды;
- закрепить source guards против regressions;
- постепенно типизировать service ids, model/entity collaborators и meta/template runtime.

## Текущий срез

Проверено локально:

- `php vendor/bin/phpunit --configuration phpunit.xml`
- результат: `OK (1803 tests, 93739 assertions)`, около 15 секунд;
- `composer` binary в окружении не найден, но локальный PHPUnit работает через `vendor/bin/phpunit`;
- production PHP-срез `core`, `project`, `htdocs`: 497 файлов;
- `declare(strict_types=1)` есть во всех 497 production PHP-файлах;
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

   1803 теста проходят за ~15 секунд. Для AI это очень хороший цикл обратной связи.

## Главные AI-friction points

### 1. Git/workspace hygiene

`.gitignore` сейчас содержит только:

```gitignore
/vendor/
/composer.phar
/.phpunit.cache/
```

При этом `node_modules/` попал в индекс как добавленные файлы. Для AI это критичный шум: поиск, status, diff и планирование начинают видеть зависимости как исходники.

Рекомендуемые действия:

- добавить в `.gitignore`: `/node_modules/`, `/ai/**/.venv/`, `/.DS_Store`, `/logs/**/*.log`, runtime cache/output patterns;
- убрать `node_modules/` из индекса через `git rm --cached -r node_modules` отдельным осознанным шагом;
- добавить source guard, который проваливает тест, если `node_modules` попал в tracked files.

### 2. Нет AI manifest слоя

Сейчас есть директория `ai/nexaCore/.venv`, но нет `.ai/` или `ai/*.md/json`, которые объясняют framework агенту.

Нужно добавить:

```text
.ai/
  project.md
  architecture.md
  commands.md
  conventions.md
  map.schema.json
  map.json
```

Минимальный первый `map.json` должен содержать:

- source roots;
- autoload namespaces;
- entrypoints;
- core layers;
- service ids;
- service aliases;
- config files;
- meta/template files;
- test commands;
- forbidden source patterns;
- known dynamic boundaries.

### 3. Нет единой AI verification команды

Сейчас надежная команда:

```bash
php vendor/bin/phpunit --configuration phpunit.xml
```

Но AI не должен угадывать fallback, если `composer` недоступен.

Нужен единый entrypoint:

```bash
php tools/ai_verify.php
```

Затем можно поднять это в framework CLI:

```bash
php fan ai:verify
```

Первый набор проверок:

- PHP syntax check для измененных production/test файлов;
- PHPUnit full или focused;
- source inventory guards;
- optional `git diff --check`;
- warning, если `node_modules` или `.venv` tracked.

### 4. Service graph не машинно-читаемый

Сейчас graph существует в `application_service_graph_registrar.php`, отдельных registrars и creators. Человеку можно разобраться, но AI вынужден проходить цепочку:

```text
registrar -> creator -> project service class -> factory -> config -> string service id
```

Рекомендуемый промежуточный слой:

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

И команда/скрипт:

```bash
php tools/ai_map.php --json
```

Чтобы AI мог получить список service ids без чтения десятков factories.

### 5. String service ids остаются главным blind spot

Примеры:

- `matcher`
- `request_input`
- `bootstrap_runtime`
- `config`
- `cache`
- `array_value_reader`

План не должен ломать BC. Нужно добавить typed aliases/constants:

```php
final class service_id
{
    public const MATCHER = 'matcher';
    public const REQUEST_INPUT = 'request_input';
    public const BOOTSTRAP_RUNTIME = 'bootstrap_runtime';
}
```

Дальше постепенно заменять строковые ids в новых местах и source guard-ом запрещать новые raw ids вне composition roots.

### 6. Model/entity слой все еще тянет service locator назад

Текущий grep показывает `getService()` хвост в model/entity слое:

- `core/base/model/entity.php`
- `core/base/model/row.php`
- `core/base/model/request.php`
- `core/base/model/spec_file/row.php`
- `core/base/model/spec_file/image/entity.php`
- `core/base/model/spec_file/image/row.php`
- `core/base/model/file_data/row.php`
- `core/service/entity/description.php`

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

`*.meta.php` и `*.tpl` - важная часть PHP-FAN, но для AI это второй язык внутри framework.

Нужно добавить:

- manifest extractor для meta-файлов;
- schema/описание допустимых meta keys;
- tests, которые проверяют meta contracts;
- adapter boundary вокруг template loading/compiled template loading.

### 9. Tooling неполный

Нет найденных конфигов:

- PHPStan/Psalm;
- Rector;
- PHP-CS-Fixer/Pint/ECS.

Для AI желательно добавить минимум:

- PHPStan или Psalm с начальным baseline;
- Rector для механических PHP upgrades/refactors;
- formatter command;
- единый `composer` script или `tools/ai_verify.php`, который работает даже без глобального Composer binary.

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

.ai/
  architecture.md
  commands.md
  conventions.md
  map.schema.json
  map.json
```

### Команды, которые должны появиться первыми

```bash
php tools/ai_map.php --json
php tools/ai_verify.php
php tools/ai_explain.php core/service/matcher.php
```

Позже:

```bash
php fan ai:map
php fan ai:verify
php fan ai:routes
php fan ai:services
php fan ai:changed
php fan ai:doctor
```

## Приоритетный план рефакторинга

### P0. Убрать шум из workspace

Цель: AI должен видеть только исходники и intentional artifacts.

Шаги:

1. Расширить `.gitignore`.
2. Убрать `node_modules/` из индекса отдельным git-срезом.
3. Добавить source test против tracked dependency dirs.
4. Задокументировать, зачем `package.json` с `codex` нужен проекту, либо убрать Node-зависимость, если она случайная.

Проверка:

```bash
git status --short
php vendor/bin/phpunit --configuration phpunit.xml
```

### P1. Добавить `.ai/` documentation contract

Цель: дать агенту первый экран понимания проекта.

Шаги:

1. `.ai/project.md` - что такое PHP-FAN, где entrypoint, какие слои.
2. `.ai/commands.md` - реальные команды, включая fallback без Composer.
3. `.ai/conventions.md` - naming, service ids, meta/template правила, test policy.
4. `.ai/architecture.md` - current architecture и target architecture.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml
```

### P2. Добавить generated AI map

Цель: AI не должен grep-ить все, чтобы понять framework.

Шаги:

1. `tools/ai_map.php --json`.
2. Генерация `.ai/map.json`.
3. Включить туда composer autoload, entrypoints, source roots, test roots, service ids, meta/tpl files.
4. Добавить test, что map генерируется валидным JSON и содержит обязательные sections.

Проверка:

```bash
php tools/ai_map.php --json
php vendor/bin/phpunit --configuration phpunit.xml
```

### P3. Единый verify entrypoint

Цель: одна команда для агента перед завершением работы.

Шаги:

1. `tools/ai_verify.php`.
2. Проверять наличие `vendor/bin/phpunit`.
3. Печатать понятный machine-readable summary.
4. Добавить режим `--changed`, если есть надежный git diff.

Проверка:

```bash
php tools/ai_verify.php
```

### P4. Service graph descriptors

Цель: сделать DI graph читаемым без путешествия по creators/factories.

Шаги:

1. Ввести `service_descriptor`.
2. Добавить reader, который собирает known ids из registrars.
3. Экспортировать service graph в `ai_map`.
4. Постепенно заменить raw string ids на `service_id` constants в новых/изменяемых файлах.

Проверка:

```bash
php tools/ai_map.php --json
php vendor/bin/phpunit --configuration phpunit.xml
```

### P5. Model/entity collaborator extraction

Цель: убрать `getService()` как скрытую зависимость модели.

Шаги:

1. Начать с самого узкого use case в `core/base/model/request.php`.
2. Выделить collaborator вместо `$entity->getService()->getSqlDir()`.
3. Повторить для row/entity id codec и entity lookup.
4. Добавить source guard: новые `getService()` usages запрещены вне текущего allowlist.

Проверка:

```bash
php vendor/bin/phpunit --configuration phpunit.xml unit/core/base/model
```

### P6. Meta/template schema и extractor

Цель: превратить meta/template слой из "магии" в documented compatibility subsystem.

Шаги:

1. Собрать список meta keys из `*.meta.php`.
2. Сгенерировать `.ai/meta-map.json`.
3. Документировать top-level keys: `own`, `common`, `embeddedBlocks`, `carcass`, `externalCss`, `tplVars`.
4. Добавить tests на валидность meta-файлов.

Проверка:

```bash
php tools/ai_map.php --json
php vendor/bin/phpunit --configuration phpunit.xml unit/core/block/MetaFilesTest.php
```

### P7. Static analysis и formatter

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

## Что не делать сейчас

- Не переписывать block/meta/template runtime сразу.
- Не удалять `core/functions.php` массово: он уже почти стал BC-зоной.
- Не заменять весь DI на сторонний container одним проходом.
- Не вводить modules/DDD поверх текущего framework, пока нет `ai_map` и verify-команд.
- Не делать новый CLI поверх Symfony Console до появления простых `tools/ai_*.php` scripts.

## Лучший следующий PR

Самый полезный первый PR:

1. `.gitignore` hygiene.
2. `.ai/project.md`, `.ai/commands.md`, `.ai/conventions.md`.
3. `tools/ai_map.php --json` с минимальной картой.
4. `unit/core/AiMapTest.php`.
5. Документированная verify-команда, которая работает без глобального Composer.

Это даст максимальный прирост AI-удобства при минимальном риске для runtime.
