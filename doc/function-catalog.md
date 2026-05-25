# Каталог функций

Документ сгенерирован по PHP-токенам. Он описывает функции и методы production-кода, CLI, tools, htdocs и unit-тестов, исключая vendor, libraries и legacy assets.

- Сгенерировано: 2026-05-24 22:55:03 UTC
- Описано функций и методов: 2528
- Файлов с функциями: 290

## `_core/base/data.php`

### `fan\core\base\data::__construct`

- Расположение: `_core/base/data.php:79`
- Сигнатура: `function __construct($data = null, $key = null, $superior = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\base\data`.

### `fan\core\base\data::get`

- Расположение: `_core/base/data.php:100`
- Сигнатура: `function get($key = null, $default = null, $logError = false)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\base\data`.

### `fan\core\base\data::set`

- Расположение: `_core/base/data.php:125`
- Сигнатура: `function set($key, $value, $rewriteExisting = true, $convArray = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\base\data`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\data::toArray`

- Расположение: `_core/base/data.php:178`
- Сигнатура: `function toArray()`
- Описание: Выполняет логику `to array` и возвращает вычисленный результат.

### `fan\core\base\data::isFullRewrite`

- Расположение: `_core/base/data.php:195`
- Сигнатура: `function isFullRewrite()`
- Описание: Проверяет условие или валидирует данные `full rewrite` и возвращает результат либо выбрасывает исключение.

### `fan\core\base\data::_restoreSetters`

- Расположение: `_core/base/data.php:207`
- Сигнатура: `function _restoreSetters()`
- Описание: Выполняет логику `restore setters` и возвращает вычисленный результат.

### `fan\core\base\data::_setSetter`

- Расположение: `_core/base/data.php:220`
- Сигнатура: `function _setSetter(object|string $setter)`
- Описание: Выполняет логику `set setter` и возвращает вычисленный результат.

### `fan\core\base\data::_checkSetter`

- Расположение: `_core/base/data.php:235`
- Сигнатура: `function _checkSetter()`
- Описание: Выполняет логику `check setter` и возвращает вычисленный результат.

### `fan\core\base\data::_getMultilevelData`

- Расположение: `_core/base/data.php:275`
- Сигнатура: `function _getMultilevelData(mixed $key, mixed $default, $logError)`
- Описание: Выполняет логику `get multilevel data` и возвращает вычисленный результат.

### `fan\core\base\data::_makeSubData`

- Расположение: `_core/base/data.php:295`
- Сигнатура: `function _makeSubData($key, $value)`
- Описание: Выполняет логику `make sub data` и возвращает вычисленный результат.

### `fan\core\base\data::_isThisClass`

- Расположение: `_core/base/data.php:308`
- Сигнатура: `function _isThisClass(mixed $object)`
- Описание: Выполняет логику `is this class` и возвращает вычисленный результат.

### `fan\core\base\data::_checkSetterClass`

- Расположение: `_core/base/data.php:321`
- Сигнатура: `function _checkSetterClass(array $link, string $class)`
- Описание: Выполняет логику `check setter class` и возвращает вычисленный результат.

### `fan\core\base\data::_getSubData`

- Расположение: `_core/base/data.php:331`
- Сигнатура: `function _getSubData()`
- Описание: Выполняет логику `get sub data` и возвращает вычисленный результат.

### `fan\core\base\data::_logError`

- Расположение: `_core/base/data.php:351`
- Сигнатура: `function _logError(string $errKey, array $replacement = [])`
- Описание: Выполняет логику `log error` и возвращает вычисленный результат.
- Побочные эффекты: логирует или сообщает об ошибках

### `fan\core\base\data::__set`

- Расположение: `_core/base/data.php:374`
- Сигнатура: `function __set($key, $value)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\data`.

### `fan\core\base\data::__get`

- Расположение: `_core/base/data.php:386`
- Сигнатура: `function __get($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\data`.

### `fan\core\base\data::__isset`

- Расположение: `_core/base/data.php:398`
- Сигнатура: `function __isset($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\data`.

### `fan\core\base\data::__unset`

- Расположение: `_core/base/data.php:410`
- Сигнатура: `function __unset($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\data`.

### `fan\core\base\data::__toString`

- Расположение: `_core/base/data.php:424`
- Сигнатура: `function __toString()`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\data`.

### `fan\core\base\data::offsetExists`

- Расположение: `_core/base/data.php:462`
- Сигнатура: `function offsetExists(mixed $key): bool`
- Описание: Выполняет логику `offset exists` и возвращает вычисленный результат.

### `fan\core\base\data::offsetGet`

- Расположение: `_core/base/data.php:474`
- Сигнатура: `function offsetGet(mixed $key): mixed`
- Описание: Выполняет логику `offset get` и возвращает вычисленный результат.

### `fan\core\base\data::offsetSet`

- Расположение: `_core/base/data.php:487`
- Сигнатура: `function offsetSet(mixed $key, mixed $value): void`
- Описание: Выполняет workflow-логику `offset set`.

### `fan\core\base\data::offsetUnset`

- Расположение: `_core/base/data.php:499`
- Сигнатура: `function offsetUnset(mixed $key): void`
- Описание: Выполняет workflow-логику `offset unset`.

### `fan\core\base\data::current`

- Расположение: `_core/base/data.php:509`
- Сигнатура: `function current(): mixed`
- Описание: Выполняет логику `current` и возвращает вычисленный результат.

### `fan\core\base\data::key`

- Расположение: `_core/base/data.php:519`
- Сигнатура: `function key(): mixed`
- Описание: Выполняет логику `key` и возвращает вычисленный результат.

### `fan\core\base\data::next`

- Расположение: `_core/base/data.php:529`
- Сигнатура: `function next(): void`
- Описание: Выполняет workflow-логику `next`.

### `fan\core\base\data::rewind`

- Расположение: `_core/base/data.php:539`
- Сигнатура: `function rewind(): void`
- Описание: Выполняет workflow-логику `rewind`.

### `fan\core\base\data::valid`

- Расположение: `_core/base/data.php:549`
- Сигнатура: `function valid(): bool`
- Описание: Выполняет логику `valid` и возвращает вычисленный результат.

### `fan\core\base\data::count`

- Расположение: `_core/base/data.php:559`
- Сигнатура: `function count(): int`
- Описание: Выполняет логику `count` и возвращает вычисленный результат.

### `fan\core\base\data::__serialize`

- Расположение: `_core/base/data.php:569`
- Сигнатура: `function __serialize(): array`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\data`.

### `fan\core\base\data::serialize`

- Расположение: `_core/base/data.php:584`
- Сигнатура: `function serialize(): string`
- Описание: Выполняет логику `serialize` и возвращает вычисленный результат.
- Побочные эффекты: сериализует или десериализует данные

### `fan\core\base\data::__unserialize`

- Расположение: `_core/base/data.php:596`
- Сигнатура: `function __unserialize(array $recover): void`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\data`.

### `fan\core\base\data::unserialize`

- Расположение: `_core/base/data.php:608`
- Сигнатура: `function unserialize($recover): void`
- Описание: Выполняет workflow-логику `unserialize`.
- Побочные эффекты: сериализует или десериализует данные

### `fan\core\base\data::restoreSerializedData`

- Расположение: `_core/base/data.php:620`
- Сигнатура: `function restoreSerializedData(array $recover): void`
- Описание: Выполняет workflow-логику `restore serialized data`.

## `_core/base/expression_evaluator.php`

### `fan\core\base\expression_evaluator::evaluate`

- Расположение: `_core/base/expression_evaluator.php:19`
- Сигнатура: `function evaluate(string $expression, callable $resolver): mixed`
- Описание: Выполняет логику `evaluate` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\expression_evaluator::__construct`

- Расположение: `_core/base/expression_evaluator.php:35`
- Сигнатура: `function __construct(string $expression, callable $resolver)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\base\expression_evaluator`.

### `fan\core\base\expression_evaluator::tokenize`

- Расположение: `_core/base/expression_evaluator.php:48`
- Сигнатура: `function tokenize(string $expression): array`
- Описание: Выполняет логику `tokenize` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\expression_evaluator::readString`

- Расположение: `_core/base/expression_evaluator.php:103`
- Сигнатура: `function readString(string $expression, int $offset): array`
- Описание: Получает, читает или вычисляет данные `string` в рамках этого метода класса `fan\core\base\expression_evaluator`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\expression_evaluator::readNumber`

- Расположение: `_core/base/expression_evaluator.php:131`
- Сигнатура: `function readNumber(string $expression, int $offset): array`
- Описание: Получает, читает или вычисляет данные `number` в рамках этого метода класса `fan\core\base\expression_evaluator`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\expression_evaluator::readIdentifier`

- Расположение: `_core/base/expression_evaluator.php:148`
- Сигнатура: `function readIdentifier(string $expression, int $offset): array`
- Описание: Получает, читает или вычисляет данные `identifier` в рамках этого метода класса `fan\core\base\expression_evaluator`.

### `fan\core\base\expression_evaluator::parseOr`

- Расположение: `_core/base/expression_evaluator.php:160`
- Сигнатура: `function parseOr(): mixed`
- Описание: Создает, разбирает, форматирует или конвертирует данные `or` для этого метода класса `fan\core\base\expression_evaluator`.

### `fan\core\base\expression_evaluator::parseXor`

- Расположение: `_core/base/expression_evaluator.php:175`
- Сигнатура: `function parseXor(): mixed`
- Описание: Создает, разбирает, форматирует или конвертирует данные `xor` для этого метода класса `fan\core\base\expression_evaluator`.

### `fan\core\base\expression_evaluator::parseAnd`

- Расположение: `_core/base/expression_evaluator.php:189`
- Сигнатура: `function parseAnd(): mixed`
- Описание: Создает, разбирает, форматирует или конвертирует данные `and` для этого метода класса `fan\core\base\expression_evaluator`.

### `fan\core\base\expression_evaluator::parseEquality`

- Расположение: `_core/base/expression_evaluator.php:204`
- Сигнатура: `function parseEquality(): mixed`
- Описание: Создает, разбирает, форматирует или конвертирует данные `equality` для этого метода класса `fan\core\base\expression_evaluator`.

### `fan\core\base\expression_evaluator::parseComparison`

- Расположение: `_core/base/expression_evaluator.php:224`
- Сигнатура: `function parseComparison(): mixed`
- Описание: Создает, разбирает, форматирует или конвертирует данные `comparison` для этого метода класса `fan\core\base\expression_evaluator`.

### `fan\core\base\expression_evaluator::parseAdditive`

- Расположение: `_core/base/expression_evaluator.php:244`
- Сигнатура: `function parseAdditive(): mixed`
- Описание: Создает, разбирает, форматирует или конвертирует данные `additive` для этого метода класса `fan\core\base\expression_evaluator`.

### `fan\core\base\expression_evaluator::parseMultiplicative`

- Расположение: `_core/base/expression_evaluator.php:259`
- Сигнатура: `function parseMultiplicative(): mixed`
- Описание: Создает, разбирает, форматирует или конвертирует данные `multiplicative` для этого метода класса `fan\core\base\expression_evaluator`.

### `fan\core\base\expression_evaluator::parseUnary`

- Расположение: `_core/base/expression_evaluator.php:278`
- Сигнатура: `function parseUnary(): mixed`
- Описание: Создает, разбирает, форматирует или конвертирует данные `unary` для этого метода класса `fan\core\base\expression_evaluator`.

### `fan\core\base\expression_evaluator::parsePrimary`

- Расположение: `_core/base/expression_evaluator.php:300`
- Сигнатура: `function parsePrimary(): mixed`
- Описание: Создает, разбирает, форматирует или конвертирует данные `primary` для этого метода класса `fan\core\base\expression_evaluator`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\expression_evaluator::matchOperator`

- Расположение: `_core/base/expression_evaluator.php:334`
- Сигнатура: `function matchOperator(array $operators): ?string`
- Описание: Выполняет логику `match operator` и возвращает вычисленный результат.

### `fan\core\base\expression_evaluator::consume`

- Расположение: `_core/base/expression_evaluator.php:351`
- Сигнатура: `function consume(string $type): bool`
- Описание: Выполняет логику `consume` и возвращает вычисленный результат.

### `fan\core\base\expression_evaluator::next`

- Расположение: `_core/base/expression_evaluator.php:365`
- Сигнатура: `function next(): ?array`
- Описание: Выполняет логику `next` и возвращает вычисленный результат.

### `fan\core\base\expression_evaluator::peek`

- Расположение: `_core/base/expression_evaluator.php:375`
- Сигнатура: `function peek(): ?array`
- Описание: Выполняет логику `peek` и возвращает вычисленный результат.

### `fan\core\base\expression_evaluator::isEnd`

- Расположение: `_core/base/expression_evaluator.php:385`
- Сигнатура: `function isEnd(): bool`
- Описание: Проверяет условие или валидирует данные `end` и возвращает результат либо выбрасывает исключение.

## `_core/base/meta/delayed.php`

### `fan\core\base\meta\delayed::__construct`

- Расположение: `_core/base/meta/delayed.php:41`
- Сигнатура: `function __construct($obj, $method, $arguments)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\base\meta\delayed`.

### `fan\core\base\meta\delayed::getValue`

- Расположение: `_core/base/meta/delayed.php:53`
- Сигнатура: `function getValue()`
- Описание: Получает, читает или вычисляет данные `value` в рамках этого метода класса `fan\core\base\meta\delayed`.

## `_core/base/meta/maker.php`

### `fan\core\base\meta\maker::__construct`

- Расположение: `_core/base/meta/maker.php:102`
- Сигнатура: `function __construct(\fan\core\block\base $block)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\base\meta\maker`.

### `fan\core\base\meta\maker::__set`

- Расположение: `_core/base/meta/maker.php:120`
- Сигнатура: `function __set($key, $value)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\meta\maker`.

### `fan\core\base\meta\maker::__get`

- Расположение: `_core/base/meta/maker.php:132`
- Сигнатура: `function __get($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\meta\maker`.

### `fan\core\base\meta\maker::__call`

- Расположение: `_core/base/meta/maker.php:145`
- Сигнатура: `function __call($method, $arguments = [])`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\meta\maker`.

### `fan\core\base\meta\maker::getIterator`

- Расположение: `_core/base/meta/maker.php:155`
- Сигнатура: `function getIterator(): \Traversable`
- Описание: Получает, читает или вычисляет данные `iterator` в рамках этого метода класса `fan\core\base\meta\maker`.

### `fan\core\base\meta\maker::getBlock`

- Расположение: `_core/base/meta/maker.php:164`
- Сигнатура: `function getBlock()`
- Описание: Получает, читает или вычисляет данные `block` в рамках этого метода класса `fan\core\base\meta\maker`.

### `fan\core\base\meta\maker::setContainerMeta`

- Расположение: `_core/base/meta/maker.php:176`
- Сигнатура: `function setContainerMeta($containerMeta)`
- Описание: Устанавливает, добавляет или сохраняет данные `container meta` в рамках этого метода класса `fan\core\base\meta\maker`.

### `fan\core\base\meta\maker::setMainBlockMeta`

- Расположение: `_core/base/meta/maker.php:187`
- Сигнатура: `function setMainBlockMeta()`
- Описание: Устанавливает, добавляет или сохраняет данные `main block meta` в рамках этого метода класса `fan\core\base\meta\maker`.

### `fan\core\base\meta\maker::assembleTab`

- Расположение: `_core/base/meta/maker.php:202`
- Сигнатура: `function assembleTab()`
- Описание: Создает, разбирает, форматирует или конвертирует данные `tab` для этого метода класса `fan\core\base\meta\maker`.

### `fan\core\base\meta\maker::assembleBlock`

- Расположение: `_core/base/meta/maker.php:219`
- Сигнатура: `function assembleBlock()`
- Описание: Создает, разбирает, форматирует или конвертирует данные `block` для этого метода класса `fan\core\base\meta\maker`.

### `fan\core\base\meta\maker::assembleOther`

- Расположение: `_core/base/meta/maker.php:237`
- Сигнатура: `function assembleOther()`
- Описание: Создает, разбирает, форматирует или конвертирует данные `other` для этого метода класса `fan\core\base\meta\maker`.

### `fan\core\base\meta\maker::assembleEmbeded`

- Расположение: `_core/base/meta/maker.php:257`
- Сигнатура: `function assembleEmbeded(string $blockName)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `embeded` для этого метода класса `fan\core\base\meta\maker`.

### `fan\core\base\meta\maker::getMixSrcMeta`

- Расположение: `_core/base/meta/maker.php:273`
- Сигнатура: `function getMixSrcMeta()`
- Описание: Получает, читает или вычисляет данные `mix src meta` в рамках этого метода класса `fan\core\base\meta\maker`.

### `fan\core\base\meta\maker::getOrder`

- Расположение: `_core/base/meta/maker.php:291`
- Сигнатура: `function getOrder($key)`
- Описание: Получает, читает или вычисляет данные `order` в рамках этого метода класса `fan\core\base\meta\maker`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\meta\maker::getMeta`

- Расположение: `_core/base/meta/maker.php:307`
- Сигнатура: `function getMeta(string|array|null $key = null, mixed $default = null)`
- Описание: Получает, читает или вычисляет данные `meta` в рамках этого метода класса `fan\core\base\meta\maker`.

### `fan\core\base\meta\maker::setMeta`

- Расположение: `_core/base/meta/maker.php:320`
- Сигнатура: `function setMeta(string|array $key, mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `meta` в рамках этого метода класса `fan\core\base\meta\maker`.

### `fan\core\base\meta\maker::getSource`

- Расположение: `_core/base/meta/maker.php:333`
- Сигнатура: `function getSource(mixed $key = null)`
- Описание: Получает, читает или вычисляет данные `source` в рамках этого метода класса `fan\core\base\meta\maker`.

### `fan\core\base\meta\maker::_defineBlockMeta`

- Расположение: `_core/base/meta/maker.php:346`
- Сигнатура: `function _defineBlockMeta(array $paths)`
- Описание: Выполняет workflow-логику `define block meta`.

### `fan\core\base\meta\maker::_defineFolderMeta`

- Расположение: `_core/base/meta/maker.php:371`
- Сигнатура: `function _defineFolderMeta(array $paths)`
- Описание: Выполняет workflow-логику `define folder meta`.

### `fan\core\base\meta\maker::_loadBlockSource`

- Расположение: `_core/base/meta/maker.php:388`
- Сигнатура: `function _loadBlockSource(string $class, string $path)`
- Описание: Выполняет логику `load block source` и возвращает вычисленный результат.

### `fan\core\base\meta\maker::_setSource`

- Расположение: `_core/base/meta/maker.php:407`
- Сигнатура: `function _setSource(string $type, array $data)`
- Описание: Выполняет логику `set source` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\meta\maker::_mergeMeta`

- Расположение: `_core/base/meta/maker.php:425`
- Сигнатура: `function _mergeMeta(mixed $srcData, mixed $addData, ?string $type)`
- Описание: Выполняет логику `merge meta` и возвращает вычисленный результат.

### `fan\core\base\meta\maker::_makeActiveMeta`

- Расположение: `_core/base/meta/maker.php:447`
- Сигнатура: `function _makeActiveMeta(string $method, $arguments = [], string|object|null $obj = null, bool $delayed = true)`
- Описание: Выполняет логику `make active meta` и возвращает вычисленный результат.

## `_core/base/meta/row.php`

### `fan\core\base\meta\row::__construct`

- Расположение: `_core/base/meta/row.php:51`
- Сигнатура: `function __construct(maker $maker,array $data, ?row $parent = null, $keyName = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\base\meta\row`.

### `fan\core\base\meta\row::makeData`

- Расположение: `_core/base/meta/row.php:72`
- Сигнатура: `function makeData(array $data)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `data` для этого метода класса `fan\core\base\meta\row`.

### `fan\core\base\meta\row::mergeData`

- Расположение: `_core/base/meta/row.php:89`
- Сигнатура: `function mergeData(array $data, $rewriteExisting = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `data` в рамках этого метода класса `fan\core\base\meta\row`.

### `fan\core\base\meta\row::_makeSubData`

- Расположение: `_core/base/meta/row.php:107`
- Сигнатура: `function _makeSubData($key, $value)`
- Описание: Выполняет логику `make sub data` и возвращает вычисленный результат.

## `_core/base/model/entity.php`

### `fan\core\base\model\entity::__construct`

- Расположение: `_core/base/model/entity.php:107`
- Сигнатура: `function __construct(\fan\core\service\entity $service, $name, $param = [])`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::__set`

- Расположение: `_core/base/model/entity.php:137`
- Сигнатура: `function __set($key, $value)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\model\entity`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\model\entity::__get`

- Расположение: `_core/base/model/entity.php:151`
- Сигнатура: `function __get($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\model\entity`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\model\entity::getNewRow`

- Расположение: `_core/base/model/entity.php:167`
- Сигнатура: `function getNewRow()`
- Описание: Получает, читает или вычисляет данные `new row` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getRowById`

- Расположение: `_core/base/model/entity.php:180`
- Сигнатура: `function getRowById(mixed $rowId, bool $idIsEncrypt = false)`
- Описание: Получает, читает или вычисляет данные `row by id` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getRowByParam`

- Расположение: `_core/base/model/entity.php:198`
- Сигнатура: `function getRowByParam(mixed $param = null, int|float $offset = 0, ?string $orderBy = null)`
- Описание: Получает, читает или вычисляет данные `row by param` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getRowOrCreate`

- Расположение: `_core/base/model/entity.php:213`
- Сигнатура: `function getRowOrCreate(?array $loadParam = null, array $saveParam = [], bool $saveNew = true)`
- Описание: Получает, читает или вычисляет данные `row or create` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getRowByKey`

- Расположение: `_core/base/model/entity.php:232`
- Сигнатура: `function getRowByKey(string $queryKey, mixed $param = null, int|float $offset = 0, ?string $orderBy = null)`
- Описание: Получает, читает или вычисляет данные `row by key` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getRowByQuery`

- Расположение: `_core/base/model/entity.php:247`
- Сигнатура: `function getRowByQuery(string|\fan\core\service\entity\designer $query, mixed $param = null, int|float $offset = 0)`
- Описание: Получает, читает или вычисляет данные `row by query` в рамках этого метода класса `fan\core\base\model\entity`.
- Побочные эффекты: выполняет database операции

### `fan\core\base\model\entity::getRowsetByParam`

- Расположение: `_core/base/model/entity.php:264`
- Сигнатура: `function getRowsetByParam(mixed $param = null, int|float $qtt = -1, int|float $offset = -1, string $orderBy = '')`
- Описание: Получает, читает или вычисляет данные `rowset by param` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getRowsetByKey`

- Расположение: `_core/base/model/entity.php:282`
- Сигнатура: `function getRowsetByKey(string $queryKey, mixed $param = null, int|float $qtt = -1, int|float $offset = -1, string $orderBy = '')`
- Описание: Получает, читает или вычисляет данные `rowset by key` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getRowsetByQuery`

- Расположение: `_core/base/model/entity.php:298`
- Сигнатура: `function getRowsetByQuery(string|\fan\core\service\entity\designer $query, mixed $param = null, int|float $qtt = -1, int|float $offset = -1)`
- Описание: Получает, читает или вычисляет данные `rowset by query` в рамках этого метода класса `fan\core\base\model\entity`.
- Побочные эффекты: выполняет database операции

### `fan\core\base\model\entity::getCountByParam`

- Расположение: `_core/base/model/entity.php:313`
- Сигнатура: `function getCountByParam(mixed $param = null)`
- Описание: Получает, читает или вычисляет данные `count by param` в рамках этого метода класса `fan\core\base\model\entity`.
- Побочные эффекты: выполняет database операции

### `fan\core\base\model\entity::getCountByKey`

- Расположение: `_core/base/model/entity.php:327`
- Сигнатура: `function getCountByKey(string $queryKey, mixed $param = null)`
- Описание: Получает, читает или вычисляет данные `count by key` в рамках этого метода класса `fan\core\base\model\entity`.
- Побочные эффекты: выполняет database операции

### `fan\core\base\model\entity::getCountByQuery`

- Расположение: `_core/base/model/entity.php:341`
- Сигнатура: `function getCountByQuery(string|\fan\core\service\entity\designer $query, mixed $param = null)`
- Описание: Получает, читает или вычисляет данные `count by query` в рамках этого метода класса `fan\core\base\model\entity`.
- Побочные эффекты: выполняет database операции

### `fan\core\base\model\entity::getTableName`

- Расположение: `_core/base/model/entity.php:374`
- Сигнатура: `function getTableName()`
- Описание: Получает, читает или вычисляет данные `table name` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getParamById`

- Расположение: `_core/base/model/entity.php:389`
- Сигнатура: `function getParamById(mixed $rowId, bool $idIsEncrypt = false)`
- Описание: Получает, читает или вычисляет данные `param by id` в рамках этого метода класса `fan\core\base\model\entity`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\model\entity::getDataByParam`

- Расположение: `_core/base/model/entity.php:427`
- Сигнатура: `function &getDataByParam(mixed $param = null, int|float $qtt = -1, int|float $offset = -1, ?string $orderBy = null, bool $onlyOne = false)`
- Описание: Получает, читает или вычисляет данные `data by param` в рамках этого метода класса `fan\core\base\model\entity`.
- Побочные эффекты: выполняет database операции

### `fan\core\base\model\entity::getDataByQuery`

- Расположение: `_core/base/model/entity.php:447`
- Сигнатура: `function &getDataByQuery(string|\fan\core\service\entity\designer $query, mixed $param = null, int|float $qtt = -1, int|float $offset = -1, bool $onlyOne = false)`
- Описание: Получает, читает или вычисляет данные `data by query` в рамках этого метода класса `fan\core\base\model\entity`.
- Побочные эффекты: выполняет database операции

### `fan\core\base\model\entity::setSQL`

- Расположение: `_core/base/model/entity.php:466`
- Сигнатура: `function setSQL(string $queryKey, string $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `s q l` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getSQL`

- Расположение: `_core/base/model/entity.php:478`
- Сигнатура: `function getSQL(string $queryKey)`
- Описание: Получает, читает или вычисляет данные `s q l` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getSnippetyDesigner`

- Расположение: `_core/base/model/entity.php:489`
- Сигнатура: `function getSnippetyDesigner(string $queryKey)`
- Описание: Получает, читает или вычисляет данные `snippety designer` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::setConnection`

- Расположение: `_core/base/model/entity.php:505`
- Сигнатура: `function setConnection(mixed $connection = null, $extraKey = 0)`
- Описание: Устанавливает, добавляет или сохраняет данные `connection` в рамках этого метода класса `fan\core\base\model\entity`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\model\entity::getConnection`

- Расположение: `_core/base/model/entity.php:531`
- Сигнатура: `function getConnection()`
- Описание: Получает, читает или вычисляет данные `connection` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::setConnectionName`

- Расположение: `_core/base/model/entity.php:546`
- Сигнатура: `function setConnectionName(string $connectionName)`
- Описание: Устанавливает, добавляет или сохраняет данные `connection name` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getConnectionName`

- Расположение: `_core/base/model/entity.php:556`
- Сигнатура: `function getConnectionName()`
- Описание: Получает, читает или вычисляет данные `connection name` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::setConnectionKey`

- Расположение: `_core/base/model/entity.php:568`
- Сигнатура: `function setConnectionKey(mixed $connectionKey)`
- Описание: Устанавливает, добавляет или сохраняет данные `connection key` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getConnectionKey`

- Расположение: `_core/base/model/entity.php:578`
- Сигнатура: `function getConnectionKey()`
- Описание: Получает, читает или вычисляет данные `connection key` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getMainParam`

- Расположение: `_core/base/model/entity.php:587`
- Сигнатура: `function getMainParam()`
- Описание: Получает, читает или вычисляет данные `main param` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getName`

- Расположение: `_core/base/model/entity.php:608`
- Сигнатура: `function getName($showAlter = false)`
- Описание: Получает, читает или вычисляет данные `name` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getService`

- Расположение: `_core/base/model/entity.php:618`
- Сигнатура: `function getService()`
- Описание: Получает, читает или вычисляет данные `service` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getConfig`

- Расположение: `_core/base/model/entity.php:630`
- Сигнатура: `function getConfig(mixed $key = null, mixed $default = null)`
- Описание: Получает, читает или вычисляет данные `config` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getDesigner`

- Расположение: `_core/base/model/entity.php:642`
- Сигнатура: `function getDesigner(string $type = 'select')`
- Описание: Получает, читает или вычисляет данные `designer` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getDescription`

- Расположение: `_core/base/model/entity.php:654`
- Сигнатура: `function getDescription($param = [])`
- Описание: Получает, читает или вычисляет данные `description` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getRequestLoader`

- Расположение: `_core/base/model/entity.php:668`
- Сигнатура: `function getRequestLoader(array $sql = [])`
- Описание: Получает, читает или вычисляет данные `request loader` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getRowClassName`

- Расположение: `_core/base/model/entity.php:685`
- Сигнатура: `function getRowClassName()`
- Описание: Получает, читает или вычисляет данные `row class name` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getRowsetClassName`

- Расположение: `_core/base/model/entity.php:697`
- Сигнатура: `function getRowsetClassName()`
- Описание: Получает, читает или вычисляет данные `rowset class name` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getRequestClassName`

- Расположение: `_core/base/model/entity.php:709`
- Сигнатура: `function getRequestClassName()`
- Описание: Получает, читает или вычисляет данные `request class name` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getTableStatus`

- Расположение: `_core/base/model/entity.php:722`
- Сигнатура: `function getTableStatus()`
- Описание: Получает, читает или вычисляет данные `table status` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::getCheckKey`

- Расположение: `_core/base/model/entity.php:734`
- Сигнатура: `function getCheckKey(int $reduce = 0)`
- Описание: Получает, читает или вычисляет данные `check key` в рамках этого метода класса `fan\core\base\model\entity`.

### `fan\core\base\model\entity::_init`

- Расположение: `_core/base/model/entity.php:763`
- Сигнатура: `function _init($param)`
- Описание: Выполняет логику `init` и возвращает вычисленный результат.

### `fan\core\base\model\entity::_defineTableName`

- Расположение: `_core/base/model/entity.php:777`
- Сигнатура: `function _defineTableName(array $param = [])`
- Описание: Выполняет логику `define table name` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\model\entity::_getPropertyList`

- Расположение: `_core/base/model/entity.php:795`
- Сигнатура: `function _getPropertyList()`
- Описание: Выполняет логику `get property list` и возвращает вычисленный результат.

### `fan\core\base\model\entity::_setConnectionParam`

- Расположение: `_core/base/model/entity.php:809`
- Сигнатура: `function _setConnectionParam(array $param)`
- Описание: Выполняет логику `set connection param` и возвращает вычисленный результат.

### `fan\core\base\model\entity::_getClassName`

- Расположение: `_core/base/model/entity.php:853`
- Сигнатура: `function _getClassName(string $key)`
- Описание: Выполняет логику `get class name` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\model\entity::_getSqlAsString`

- Расположение: `_core/base/model/entity.php:891`
- Сигнатура: `function _getSqlAsString(string|\fan\core\service\entity\designer $query, mixed $param)`
- Описание: Выполняет логику `get sql as string` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения; выполняет database операции

### `fan\core\base\model\entity::_getRowByData`

- Расположение: `_core/base/model/entity.php:908`
- Сигнатура: `function _getRowByData(&$data = null)`
- Описание: Выполняет логику `get row by data` и возвращает вычисленный результат.

## `_core/base/model/file_data/row.php`

### `fan\core\base\model\file_data\row::__construct`

- Расположение: `_core/base/model/file_data/row.php:58`
- Сигнатура: `function __construct(\fan\core\base\model\entity $entity, &$data = [], ?\fan\core\base\model\rowset $rowset = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\base\model\file_data\row`.
- Побочные эффекты: читает PHP superglobals

### `fan\core\base\model\file_data\row::getInfoPath`

- Расположение: `_core/base/model/file_data/row.php:75`
- Сигнатура: `function getInfoPath(mixed $idVal)`
- Описание: Получает, читает или вычисляет данные `info path` в рамках этого метода класса `fan\core\base\model\file_data\row`.

### `fan\core\base\model\file_data\row::setAllowLoadInfo`

- Расположение: `_core/base/model/file_data/row.php:96`
- Сигнатура: `function setAllowLoadInfo(bool $loadInfo = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `allow load info` в рамках этого метода класса `fan\core\base\model\file_data\row`.

### `fan\core\base\model\file_data\row::loadById`

- Расположение: `_core/base/model/file_data/row.php:110`
- Сигнатура: `function loadById($idVal = null, $idIsEncrypt = false)`
- Описание: Получает, читает или вычисляет данные `by id` в рамках этого метода класса `fan\core\base\model\file_data\row`.
- Побочные эффекты: работает с файловой системой

### `fan\core\base\model\file_data\row::saveInfoFile`

- Расположение: `_core/base/model/file_data/row.php:146`
- Сигнатура: `function saveInfoFile(mixed $path, $addCond = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `info file` в рамках этого метода класса `fan\core\base\model\file_data\row`.
- Побочные эффекты: работает с файловой системой

### `fan\core\base\model\file_data\row::getFilePath`

- Расположение: `_core/base/model/file_data/row.php:186`
- Сигнатура: `function getFilePath(?int $id = null, bool $checkAddCondition = true, $srcName = '')`
- Описание: Получает, читает или вычисляет данные `file path` в рамках этого метода класса `fan\core\base\model\file_data\row`.

### `fan\core\base\model\file_data\row::getMainFilePath`

- Расположение: `_core/base/model/file_data/row.php:210`
- Сигнатура: `function getMainFilePath(int $id)`
- Описание: Получает, читает или вычисляет данные `main file path` в рамках этого метода класса `fan\core\base\model\file_data\row`.

### `fan\core\base\model\file_data\row::checkCreatedDir`

- Расположение: `_core/base/model/file_data/row.php:232`
- Сигнатура: `function checkCreatedDir(string $filePath)`
- Описание: Проверяет условие или валидирует данные `created dir` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: работает с файловой системой; может выбрасывать исключения

### `fan\core\base\model\file_data\row::getContentDisposition`

- Расположение: `_core/base/model/file_data/row.php:275`
- Сигнатура: `function getContentDisposition()`
- Описание: Получает, читает или вычисляет данные `content disposition` в рамках этого метода класса `fan\core\base\model\file_data\row`.

### `fan\core\base\model\file_data\row::prepareOutput`

- Расположение: `_core/base/model/file_data/row.php:287`
- Сигнатура: `function prepareOutput(mixed $contentDisposition = null)`
- Описание: Выполняет логику `prepare output` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние; использует service locator/helpers фреймворка

### `fan\core\base\model\file_data\row::setFormFile`

- Расположение: `_core/base/model/file_data/row.php:314`
- Сигнатура: `function setFormFile(string $formKey, array $addKeys = [], string $fileType = 'other', string $decription = '')`
- Описание: Устанавливает, добавляет или сохраняет данные `form file` в рамках этого метода класса `fan\core\base\model\file_data\row`.

### `fan\core\base\model\file_data\row::setUrlFile`

- Расположение: `_core/base/model/file_data/row.php:337`
- Сигнатура: `function setUrlFile(string $url, string $fileType = 'other', string $decription = '')`
- Описание: Устанавливает, добавляет или сохраняет данные `url file` в рамках этого метода класса `fan\core\base\model\file_data\row`.
- Побочные эффекты: работает с файловой системой; использует service locator/helpers фреймворка

### `fan\core\base\model\file_data\row::setLocalFile`

- Расположение: `_core/base/model/file_data/row.php:374`
- Сигнатура: `function setLocalFile($srcPath, string $fileType = 'other', $mimeType = 'application/octet-stream', string $decription = '', $name = null, $deleteOrigin = false)`
- Описание: Устанавливает, добавляет или сохраняет данные `local file` в рамках этого метода класса `fan\core\base\model\file_data\row`.
- Побочные эффекты: работает с файловой системой

### `fan\core\base\model\file_data\row::delete`

- Расположение: `_core/base/model/file_data/row.php:397`
- Сигнатура: `function delete()`
- Описание: Удаляет или сбрасывает состояние `данные` для этого метода класса `fan\core\base\model\file_data\row`.

### `fan\core\base\model\file_data\row::save`

- Расположение: `_core/base/model/file_data/row.php:411`
- Сигнатура: `function save()`
- Описание: Устанавливает, добавляет или сохраняет данные `данные` в рамках этого метода класса `fan\core\base\model\file_data\row`.

### `fan\core\base\model\file_data\row::getFileField`

- Расположение: `_core/base/model/file_data/row.php:427`
- Сигнатура: `function getFileField(string $keyType, string $formKey, array $addKeys = [])`
- Описание: Получает, читает или вычисляет данные `file field` в рамках этого метода класса `fan\core\base\model\file_data\row`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\base\model\file_data\row::deleteCurrentFile`

- Расположение: `_core/base/model/file_data/row.php:445`
- Сигнатура: `function deleteCurrentFile(?string $filePath = null, $infoPath = null)`
- Описание: Удаляет или сбрасывает состояние `current file` для этого метода класса `fan\core\base\model\file_data\row`.
- Побочные эффекты: работает с файловой системой

### `fan\core\base\model\file_data\row::setAccessType`

- Расположение: `_core/base/model/file_data/row.php:471`
- Сигнатура: `function setAccessType(string $key, bool $save = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `access type` в рамках этого метода класса `fan\core\base\model\file_data\row`.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения

### `fan\core\base\model\file_data\row::setPersonalAccess`

- Расположение: `_core/base/model/file_data/row.php:497`
- Сигнатура: `function setPersonalAccess(string $membType = 'owner', ?string $expireDate = null, int|float $accessQtt = -1, int|float $membId = 0)`
- Описание: Устанавливает, добавляет или сохраняет данные `personal access` в рамках этого метода класса `fan\core\base\model\file_data\row`.

### `fan\core\base\model\file_data\row::removePersonalAccess`

- Расположение: `_core/base/model/file_data/row.php:520`
- Сигнатура: `function removePersonalAccess(int $removeType = 1, ?int $membId = null)`
- Описание: Удаляет или сбрасывает состояние `personal access` для этого метода класса `fan\core\base\model\file_data\row`.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения

### `fan\core\base\model\file_data\row::checkAccess`

- Расположение: `_core/base/model/file_data/row.php:538`
- Сигнатура: `function checkAccess()`
- Описание: Проверяет условие или валидирует данные `access` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения

### `fan\core\base\model\file_data\row::checkIsOwner`

- Расположение: `_core/base/model/file_data/row.php:585`
- Сигнатура: `function checkIsOwner()`
- Описание: Проверяет условие или валидирует данные `is owner` и возвращает результат либо выбрасывает исключение.

### `fan\core\base\model\file_data\row::getEntityPA`

- Расположение: `_core/base/model/file_data/row.php:601`
- Сигнатура: `function getEntityPA($membId = null)`
- Описание: Получает, читает или вычисляет данные `entity p a` в рамках этого метода класса `fan\core\base\model\file_data\row`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\base\model\file_data\row::prepareUpdateFile`

- Расположение: `_core/base/model/file_data/row.php:627`
- Сигнатура: `function prepareUpdateFile(string $name, string $mimeType, string $fileType, string $decription)`
- Описание: Выполняет логику `prepare update file` и возвращает вычисленный результат.

### `fan\core\base\model\file_data\row::_getFileNs`

- Расположение: `_core/base/model/file_data/row.php:652`
- Сигнатура: `function _getFileNs()`
- Описание: Выполняет логику `get file ns` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_core/base/model/request.php`

### `fan\core\base\model\request::__construct`

- Расположение: `_core/base/model/request.php:39`
- Сигнатура: `function __construct(\fan\core\base\model\entity $entity)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\base\model\request`.

### `fan\core\base\model\request::__set`

- Расположение: `_core/base/model/request.php:54`
- Сигнатура: `function __set($key, $value)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\model\request`.

### `fan\core\base\model\request::__get`

- Расположение: `_core/base/model/request.php:66`
- Сигнатура: `function __get($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\model\request`.

### `fan\core\base\model\request::__call`

- Расположение: `_core/base/model/request.php:80`
- Сигнатура: `function __call($method, $args)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\model\request`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\model\request::get`

- Расположение: `_core/base/model/request.php:101`
- Сигнатура: `function get(string $key)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\base\model\request`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\model\request::set`

- Расположение: `_core/base/model/request.php:120`
- Сигнатура: `function set(string $key, string $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\base\model\request`.

### `fan\core\base\model\request::setRequests`

- Расположение: `_core/base/model/request.php:133`
- Сигнатура: `function setRequests(array $sql)`
- Описание: Устанавливает, добавляет или сохраняет данные `requests` в рамках этого метода класса `fan\core\base\model\request`.

### `fan\core\base\model\request::toArray`

- Расположение: `_core/base/model/request.php:144`
- Сигнатура: `function toArray()`
- Описание: Выполняет логику `to array` и возвращает вычисленный результат.

### `fan\core\base\model\request::getEntity`

- Расположение: `_core/base/model/request.php:154`
- Сигнатура: `function getEntity()`
- Описание: Получает, читает или вычисляет данные `entity` в рамках этого метода класса `fan\core\base\model\request`.

### `fan\core\base\model\request::_loadSQL`

- Расположение: `_core/base/model/request.php:167`
- Сигнатура: `function _loadSQL(string $key)`
- Описание: Выполняет логику `load s q l` и возвращает вычисленный результат.

### `fan\core\base\model\request::_checkSQLfile`

- Расположение: `_core/base/model/request.php:180`
- Сигнатура: `function _checkSQLfile(string $key)`
- Описание: Выполняет логику `check s q lfile` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_core/base/model/row.php`

### `fan\core\base\model\row::__construct`

- Расположение: `_core/base/model/row.php:88`
- Сигнатура: `function __construct(\fan\core\base\model\entity $entity, &$data = [], ?\fan\core\base\model\rowset $rowset = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::_runAfterLoad`

- Расположение: `_core/base/model/row.php:104`
- Сигнатура: `function _runAfterLoad()`
- Описание: Выполняет workflow-логику `run after load`.

### `fan\core\base\model\row::_runAfterLoadFail`

- Расположение: `_core/base/model/row.php:112`
- Сигнатура: `function _runAfterLoadFail()`
- Описание: Выполняет workflow-логику `run after load fail`.

### `fan\core\base\model\row::_runBeforeInsert`

- Расположение: `_core/base/model/row.php:120`
- Сигнатура: `function _runBeforeInsert()`
- Описание: Выполняет workflow-логику `run before insert`.

### `fan\core\base\model\row::_runBeforeUpdate`

- Расположение: `_core/base/model/row.php:128`
- Сигнатура: `function _runBeforeUpdate()`
- Описание: Выполняет workflow-логику `run before update`.

### `fan\core\base\model\row::_runAfterInsert`

- Расположение: `_core/base/model/row.php:138`
- Сигнатура: `function _runAfterInsert($changed)`
- Описание: Выполняет workflow-логику `run after insert`.

### `fan\core\base\model\row::_runAfterUpdate`

- Расположение: `_core/base/model/row.php:148`
- Сигнатура: `function _runAfterUpdate($changed)`
- Описание: Выполняет workflow-логику `run after update`.

### `fan\core\base\model\row::_runAfterSave`

- Расположение: `_core/base/model/row.php:158`
- Сигнатура: `function _runAfterSave($changed)`
- Описание: Выполняет workflow-логику `run after save`.

### `fan\core\base\model\row::_runAfterDelete`

- Расположение: `_core/base/model/row.php:168`
- Сигнатура: `function _runAfterDelete(mixed $delId)`
- Описание: Выполняет workflow-логику `run after delete`.

### `fan\core\base\model\row::getConfig`

- Расположение: `_core/base/model/row.php:183`
- Сигнатура: `function getConfig(?string $key = null, mixed $default = null)`
- Описание: Получает, читает или вычисляет данные `config` в рамках этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::loadById`

- Расположение: `_core/base/model/row.php:196`
- Сигнатура: `function loadById($rowId, $idIsEncrypt = false)`
- Описание: Получает, читает или вычисляет данные `by id` в рамках этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::loadByParam`

- Расположение: `_core/base/model/row.php:212`
- Сигнатура: `function loadByParam(array|object $param, int|float $offset = 0, ?string $orderBy = null)`
- Описание: Получает, читает или вычисляет данные `by param` в рамках этого метода класса `fan\core\base\model\row`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\model\row::initIdOnly`

- Расположение: `_core/base/model/row.php:241`
- Сигнатура: `function initIdOnly($rowId)`
- Описание: Запускает или обрабатывает workflow `id only` для этого метода класса `fan\core\base\model\row`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\model\row::get`

- Расположение: `_core/base/model/row.php:267`
- Сигнатура: `function get(int|float $fieldName, mixed $defaultVal = null, $allowException = true)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::getByLocal`

- Расположение: `_core/base/model/row.php:300`
- Сигнатура: `function getByLocal(string $name, mixed $defaultVal = null, bool $allowException = true)`
- Описание: Получает, читает или вычисляет данные `by local` в рамках этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::set`

- Расположение: `_core/base/model/row.php:314`
- Сигнатура: `function set($fieldName, array|entity $value, $allowException = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::setByLocal`

- Расположение: `_core/base/model/row.php:359`
- Сигнатура: `function setByLocal(string $name, mixed $value = null, bool $allowException = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `by local` в рамках этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::toArray`

- Расположение: `_core/base/model/row.php:369`
- Сигнатура: `function toArray()`
- Описание: Выполняет логику `to array` и возвращает вычисленный результат.

### `fan\core\base\model\row::getFields`

- Расположение: `_core/base/model/row.php:381`
- Сигнатура: `function getFields(mixed $keys = null, $allExists = true)`
- Описание: Получает, читает или вычисляет данные `fields` в рамках этого метода класса `fan\core\base\model\row`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\model\row::setFields`

- Расположение: `_core/base/model/row.php:412`
- Сигнатура: `function setFields(mixed $fields, bool $isSave = false)`
- Описание: Устанавливает, добавляет или сохраняет данные `fields` в рамках этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::getTopRow`

- Расположение: `_core/base/model/row.php:433`
- Сигнатура: `function getTopRow(string $byField, bool $logEmptyVal = false)`
- Описание: Получает, читает или вычисляет данные `top row` в рамках этого метода класса `fan\core\base\model\row`.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках

### `fan\core\base\model\row::getBottomRowset`

- Расположение: `_core/base/model/row.php:478`
- Сигнатура: `function getBottomRowset($tableName, $qtt = -1, $offset = -1, $orderBy = '')`
- Описание: Получает, читает или вычисляет данные `bottom rowset` в рамках этого метода класса `fan\core\base\model\row`.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках

### `fan\core\base\model\row::revert`

- Расположение: `_core/base/model/row.php:512`
- Сигнатура: `function revert()`
- Описание: Выполняет логику `revert` и возвращает вычисленный результат.

### `fan\core\base\model\row::save`

- Расположение: `_core/base/model/row.php:526`
- Сигнатура: `function save()`
- Описание: Устанавливает, добавляет или сохраняет данные `данные` в рамках этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::delete`

- Расположение: `_core/base/model/row.php:550`
- Сигнатура: `function delete()`
- Описание: Удаляет или сбрасывает состояние `данные` для этого метода класса `fan\core\base\model\row`.
- Побочные эффекты: может выбрасывать исключения; выполняет database операции

### `fan\core\base\model\row::getId`

- Расположение: `_core/base/model/row.php:586`
- Сигнатура: `function getId($allowException = true, $useSourceValue = false, $alwaysArray = false)`
- Описание: Получает, читает или вычисляет данные `id` в рамках этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::setId`

- Расположение: `_core/base/model/row.php:609`
- Сигнатура: `function setId(mixed $idVal)`
- Описание: Устанавливает, добавляет или сохраняет данные `id` в рамках этого метода класса `fan\core\base\model\row`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\model\row::getEntity`

- Расположение: `_core/base/model/row.php:630`
- Сигнатура: `function getEntity()`
- Описание: Получает, читает или вычисляет данные `entity` в рамках этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::getRowset`

- Расположение: `_core/base/model/row.php:639`
- Сигнатура: `function getRowset()`
- Описание: Получает, читает или вычисляет данные `rowset` в рамках этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::checkIsLoad`

- Расположение: `_core/base/model/row.php:649`
- Сигнатура: `function checkIsLoad()`
- Описание: Проверяет условие или валидирует данные `is load` и возвращает результат либо выбрасывает исключение.

### `fan\core\base\model\row::getSrcFields`

- Расположение: `_core/base/model/row.php:659`
- Сигнатура: `function getSrcFields()`
- Описание: Получает, читает или вычисляет данные `src fields` в рамках этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::getChangedElm`

- Расположение: `_core/base/model/row.php:669`
- Сигнатура: `function getChangedElm()`
- Описание: Получает, читает или вычисляет данные `changed elm` в рамках этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::getDefaultValue`

- Расположение: `_core/base/model/row.php:679`
- Сигнатура: `function getDefaultValue()`
- Описание: Получает, читает или вычисляет данные `default value` в рамках этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::getDebugInfo`

- Расположение: `_core/base/model/row.php:695`
- Сигнатура: `function getDebugInfo()`
- Описание: Получает, читает или вычисляет данные `debug info` в рамках этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::setShowError`

- Расположение: `_core/base/model/row.php:718`
- Сигнатура: `function setShowError(bool $showError)`
- Описание: Устанавливает, добавляет или сохраняет данные `show error` в рамках этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::_fixLoadedData`

- Расположение: `_core/base/model/row.php:732`
- Сигнатура: `function _fixLoadedData(&$data)`
- Описание: Выполняет логику `fix loaded data` и возвращает вычисленный результат.

### `fan\core\base\model\row::_insertRow`

- Расположение: `_core/base/model/row.php:750`
- Сигнатура: `function _insertRow()`
- Описание: Выполняет логику `insert row` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках; выполняет database операции

### `fan\core\base\model\row::_updateRow`

- Расположение: `_core/base/model/row.php:796`
- Сигнатура: `function _updateRow()`
- Описание: Выполняет логику `update row` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения; логирует или сообщает об ошибках; выполняет database операции

### `fan\core\base\model\row::_getFieldInfo`

- Расположение: `_core/base/model/row.php:835`
- Сигнатура: `function _getFieldInfo(string $fieldName, bool $allowException = true, $forse = false)`
- Описание: Выполняет логику `get field info` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\model\row::_getFullFieldsInfo`

- Расположение: `_core/base/model/row.php:856`
- Сигнатура: `function _getFullFieldsInfo($forse = false)`
- Описание: Выполняет логику `get full fields info` и возвращает вычисленный результат.

### `fan\core\base\model\row::_isStringType`

- Расположение: `_core/base/model/row.php:871`
- Сигнатура: `function _isStringType(string $type)`
- Описание: Выполняет логику `is string type` и возвращает вычисленный результат.

### `fan\core\base\model\row::_isNumberType`

- Расположение: `_core/base/model/row.php:883`
- Сигнатура: `function _isNumberType(string $type)`
- Описание: Выполняет логику `is number type` и возвращает вычисленный результат.

### `fan\core\base\model\row::_getFieldValue`

- Расположение: `_core/base/model/row.php:898`
- Сигнатура: `function _getFieldValue(int|float $fieldName, mixed $defaultVal = null, $allowException = true)`
- Описание: Выполняет логику `get field value` и возвращает вычисленный результат.

### `fan\core\base\model\row::_setFieldValue`

- Расположение: `_core/base/model/row.php:915`
- Сигнатура: `function _setFieldValue($fieldName, array|entity $value, $allowException = true)`
- Описание: Выполняет логику `set field value` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках

### `fan\core\base\model\row::_setDefault`

- Расположение: `_core/base/model/row.php:952`
- Сигнатура: `function _setDefault()`
- Описание: Выполняет логику `set default` и возвращает вычисленный результат.

### `fan\core\base\model\row::_resetProperty`

- Расположение: `_core/base/model/row.php:970`
- Сигнатура: `function _resetProperty($full = true)`
- Описание: Выполняет логику `reset property` и возвращает вычисленный результат.

### `fan\core\base\model\row::_getCurrentLocal`

- Расположение: `_core/base/model/row.php:987`
- Сигнатура: `function _getCurrentLocal()`
- Описание: Выполняет логику `get current local` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\base\model\row::_getDefaultLocal`

- Расположение: `_core/base/model/row.php:1001`
- Сигнатура: `function _getDefaultLocal()`
- Описание: Выполняет логику `get default local` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\base\model\row::_checkGetWrongValue`

- Расположение: `_core/base/model/row.php:1018`
- Сигнатура: `function _checkGetWrongValue(string $fieldName)`
- Описание: Выполняет workflow-логику `check get wrong value`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\model\row::_restoreProperties`

- Расположение: `_core/base/model/row.php:1033`
- Сигнатура: `function _restoreProperties()`
- Описание: Выполняет логику `restore properties` и возвращает вычисленный результат.

### `fan\core\base\model\row::_convToString`

- Расположение: `_core/base/model/row.php:1046`
- Сигнатура: `function _convToString(mixed $val)`
- Описание: Выполняет логику `conv to string` и возвращает вычисленный результат.

### `fan\core\base\model\row::__set`

- Расположение: `_core/base/model/row.php:1070`
- Сигнатура: `function __set($fieldName, $value)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::__get`

- Расположение: `_core/base/model/row.php:1082`
- Сигнатура: `function __get($fieldName)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::__call`

- Расположение: `_core/base/model/row.php:1094`
- Сигнатура: `function __call($method, $args)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\model\row`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\model\row::__toString`

- Расположение: `_core/base/model/row.php:1110`
- Сигнатура: `function __toString()`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\model\row`.

### `fan\core\base\model\row::offsetSet`

- Расположение: `_core/base/model/row.php:1128`
- Сигнатура: `function offsetSet($fieldName, mixed $value)`
- Описание: Выполняет workflow-логику `offset set`.

### `fan\core\base\model\row::offsetExists`

- Расположение: `_core/base/model/row.php:1140`
- Сигнатура: `function offsetExists($fieldName)`
- Описание: Выполняет логику `offset exists` и возвращает вычисленный результат.

### `fan\core\base\model\row::offsetUnset`

- Расположение: `_core/base/model/row.php:1152`
- Сигнатура: `function offsetUnset($fieldName)`
- Описание: Выполняет workflow-логику `offset unset`.

### `fan\core\base\model\row::offsetGet`

- Расположение: `_core/base/model/row.php:1164`
- Сигнатура: `function offsetGet($fieldName)`
- Описание: Выполняет логику `offset get` и возвращает вычисленный результат.

### `fan\core\base\model\row::serialize`

- Расположение: `_core/base/model/row.php:1174`
- Сигнатура: `function serialize()`
- Описание: Выполняет логику `serialize` и возвращает вычисленный результат.
- Побочные эффекты: сериализует или десериализует данные

### `fan\core\base\model\row::unserialize`

- Расположение: `_core/base/model/row.php:1193`
- Сигнатура: `function unserialize($data)`
- Описание: Выполняет workflow-логику `unserialize`.
- Побочные эффекты: использует service locator/helpers фреймворка; сериализует или десериализует данные

## `_core/base/model/rowset.php`

### `fan\core\base\model\rowset::__construct`

- Расположение: `_core/base/model/rowset.php:34`
- Сигнатура: `function __construct(\fan\core\base\model\entity $entity, &$data)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\base\model\rowset`.

### `fan\core\base\model\rowset::toArray`

- Расположение: `_core/base/model/rowset.php:56`
- Сигнатура: `function toArray($recursive = false)`
- Описание: Выполняет логику `to array` и возвращает вычисленный результат.

### `fan\core\base\model\rowset::getRowsById`

- Расположение: `_core/base/model/rowset.php:75`
- Сигнатура: `function getRowsById()`
- Описание: Получает, читает или вычисляет данные `rows by id` в рамках этого метода класса `fan\core\base\model\rowset`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\model\rowset::getArrayAssoc`

- Расположение: `_core/base/model/rowset.php:98`
- Сигнатура: `function getArrayAssoc($fields = [], bool $excludeId = true, $keyPrefix = null)`
- Описание: Получает, читает или вычисляет данные `array assoc` в рамках этого метода класса `fan\core\base\model\rowset`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\model\rowset::getColumn`

- Расположение: `_core/base/model/rowset.php:147`
- Сигнатура: `function getColumn($columnName, $idAsKey = true)`
- Описание: Получает, читает или вычисляет данные `column` в рамках этого метода класса `fan\core\base\model\rowset`.

### `fan\core\base\model\rowset::getArrayHash`

- Расположение: `_core/base/model/rowset.php:165`
- Сигнатура: `function getArrayHash(mixed $keyField, mixed $valField)`
- Описание: Получает, читает или вычисляет данные `array hash` в рамках этого метода класса `fan\core\base\model\rowset`.

### `fan\core\base\model\rowset::getEntity`

- Расположение: `_core/base/model/rowset.php:179`
- Сигнатура: `function getEntity()`
- Описание: Получает, читает или вычисляет данные `entity` в рамках этого метода класса `fan\core\base\model\rowset`.

### `fan\core\base\model\rowset::_isScalarId`

- Расположение: `_core/base/model/rowset.php:191`
- Сигнатура: `function _isScalarId()`
- Описание: Выполняет логику `is scalar id` и возвращает вычисленный результат.

### `fan\core\base\model\rowset::serialize`

- Расположение: `_core/base/model/rowset.php:203`
- Сигнатура: `function serialize()`
- Описание: Выполняет логику `serialize` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения; сериализует или десериализует данные

### `fan\core\base\model\rowset::unserialize`

- Расположение: `_core/base/model/rowset.php:216`
- Сигнатура: `function unserialize($recover)`
- Описание: Выполняет workflow-логику `unserialize`.
- Побочные эффекты: может выбрасывать исключения; сериализует или десериализует данные

## `_core/base/model/spec_file/image/entity.php`

### `fan\core\base\model\spec_file\image\entity::rotateImageById`

- Расположение: `_core/base/model/spec_file/image/entity.php:40`
- Сигнатура: `function rotateImageById(int|float $id, int|float $angle)`
- Описание: Выполняет workflow-логику `rotate image by id`.

### `fan\core\base\model\spec_file\image\entity::getImgTagById`

- Расположение: `_core/base/model/spec_file/image/entity.php:55`
- Сигнатура: `function getImgTagById(int|float $id, string $cssClass = '', ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `img tag by id` в рамках этого метода класса `fan\core\base\model\spec_file\image\entity`.

### `fan\core\base\model\spec_file\image\entity::getImgTagByCode`

- Расположение: `_core/base/model/spec_file/image/entity.php:69`
- Сигнатура: `function getImgTagByCode(string $code, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `img tag by code` в рамках этого метода класса `fan\core\base\model\spec_file\image\entity`.

### `fan\core\base\model\spec_file\image\entity::replaceCodeToImgTag`

- Расположение: `_core/base/model/spec_file/image/entity.php:86`
- Сигнатура: `function replaceCodeToImgTag(string $code, ?array $linkTbl = NULL, string $keyField = 'id_file_data')`
- Описание: Выполняет логику `replace code to img tag` и возвращает вычисленный результат.

### `fan\core\base\model\spec_file\image\entity::advReplaceCodeToImgTag`

- Расположение: `_core/base/model/spec_file/image/entity.php:119`
- Сигнатура: `function advReplaceCodeToImgTag(string $code, array $param, ?array $linkTbl = NULL, string $keyField = 'id_file_data')`
- Описание: Выполняет логику `adv replace code to img tag` и возвращает вычисленный результат.

### `fan\core\base\model\spec_file\image\entity::prepareImgEtt`

- Расположение: `_core/base/model/spec_file/image/entity.php:168`
- Сигнатура: `function prepareImgEtt(&$repl, array $matches, array $pos, array $linkTbl, string $keyField, bool $adv)`
- Описание: Выполняет логику `prepare img ett` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_core/base/model/spec_file/image/row.php`

### `fan\core\base\model\spec_file\image\row::getTemplate`

- Расположение: `_core/base/model/spec_file/image/row.php:34`
- Сигнатура: `function getTemplate()`
- Описание: Получает, читает или вычисляет данные `template` в рамках этого метода класса `fan\core\base\model\spec_file\image\row`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\base\model\spec_file\image\row::setFormFile`

- Расположение: `_core/base/model/spec_file/image/row.php:55`
- Сигнатура: `function setFormFile($formKey, $addKeys = [], $decription = '', $alt = '')`
- Описание: Устанавливает, добавляет или сохраняет данные `form file` в рамках этого метода класса `fan\core\base\model\spec_file\image\row`.

### `fan\core\base\model\spec_file\image\row::setUrlFile`

- Расположение: `_core/base/model/spec_file/image/row.php:72`
- Сигнатура: `function setUrlFile($url, $decription = '', $alt = '')`
- Описание: Устанавливает, добавляет или сохраняет данные `url file` в рамках этого метода класса `fan\core\base\model\spec_file\image\row`.

### `fan\core\base\model\spec_file\image\row::setLocalFile`

- Расположение: `_core/base/model/spec_file/image/row.php:91`
- Сигнатура: `function setLocalFile($srcPath, $decription = '', $alt = '', $name = null, $deleteOrigin = false)`
- Описание: Устанавливает, добавляет или сохраняет данные `local file` в рамках этого метода класса `fan\core\base\model\spec_file\image\row`.

### `fan\core\base\model\spec_file\image\row::getImageData`

- Расположение: `_core/base/model/spec_file/image/row.php:107`
- Сигнатура: `function getImageData()`
- Описание: Получает, читает или вычисляет данные `image data` в рамках этого метода класса `fan\core\base\model\spec_file\image\row`.

### `fan\core\base\model\spec_file\image\row::rotateImage`

- Расположение: `_core/base/model/spec_file/image/row.php:126`
- Сигнатура: `function rotateImage(int|float $angle, $bgrColor = 0xFFFFFF, int|float $fix = 0)`
- Описание: Выполняет workflow-логику `rotate image`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\base\model\spec_file\image\row::advGetImgTag`

- Расположение: `_core/base/model/spec_file/image/row.php:142`
- Сигнатура: `function advGetImgTag(string $type, array $param)`
- Описание: Выполняет логику `adv get img tag` и возвращает вычисленный результат.

### `fan\core\base\model\spec_file\image\row::getImgTag`

- Расположение: `_core/base/model/spec_file/image/row.php:182`
- Сигнатура: `function getImgTag($param = null)`
- Описание: Получает, читает или вычисляет данные `img tag` в рамках этого метода класса `fan\core\base\model\spec_file\image\row`.

### `fan\core\base\model\spec_file\image\row::saveImage`

- Расположение: `_core/base/model/spec_file/image/row.php:198`
- Сигнатура: `function saveImage($alt = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `image` в рамках этого метода класса `fan\core\base\model\spec_file\image\row`.

### `fan\core\base\model\spec_file\image\row::checkAccess`

- Расположение: `_core/base/model/spec_file/image/row.php:220`
- Сигнатура: `function checkAccess()`
- Описание: Проверяет условие или валидирует данные `access` и возвращает результат либо выбрасывает исключение.

### `fan\core\base\model\spec_file\image\row::checkIsOwner`

- Расположение: `_core/base/model/spec_file/image/row.php:230`
- Сигнатура: `function checkIsOwner()`
- Описание: Проверяет условие или валидирует данные `is owner` и возвращает результат либо выбрасывает исключение.

### `fan\core\base\model\spec_file\image\row::fetchHtml`

- Расположение: `_core/base/model/spec_file/image/row.php:244`
- Сигнатура: `function fetchHtml($type, $param)`
- Описание: Получает, читает или вычисляет данные `html` в рамках этого метода класса `fan\core\base\model\spec_file\image\row`.

### `fan\core\base\model\spec_file\image\row::setUrl`

- Расположение: `_core/base/model/spec_file/image/row.php:262`
- Сигнатура: `function setUrl(&$param, string $key, $defPrefix, $defSuffix)`
- Описание: Устанавливает, добавляет или сохраняет данные `url` в рамках этого метода класса `fan\core\base\model\spec_file\image\row`.

### `fan\core\base\model\spec_file\image\row::setParam`

- Расположение: `_core/base/model/spec_file/image/row.php:281`
- Сигнатура: `function setParam(&$param, string $key, mixed $val)`
- Описание: Устанавливает, добавляет или сохраняет данные `param` в рамках этого метода класса `fan\core\base\model\spec_file\image\row`.

### `fan\core\base\model\spec_file\image\row::setMainImgParam`

- Расположение: `_core/base/model/spec_file/image/row.php:297`
- Сигнатура: `function setMainImgParam(&$param, $full = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `main img param` в рамках этого метода класса `fan\core\base\model\spec_file\image\row`.

### `fan\core\base\model\spec_file\image\row::resizeImage`

- Расположение: `_core/base/model/spec_file/image/row.php:318`
- Сигнатура: `function resizeImage(&$param)`
- Описание: Выполняет workflow-логику `resize image`.

### `fan\core\base\model\spec_file\image\row::getImageSize`

- Расположение: `_core/base/model/spec_file/image/row.php:362`
- Сигнатура: `function getImageSize(string $path): array|false`
- Описание: Получает, читает или вычисляет данные `image size` в рамках этого метода класса `fan\core\base\model\spec_file\image\row`.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках

## `_core/base/model/spec_file/row.php`

### `fan\core\base\model\spec_file\row::delete`

- Расположение: `_core/base/model/spec_file/row.php:33`
- Сигнатура: `function delete()`
- Описание: Удаляет или сбрасывает состояние `данные` для этого метода класса `fan\core\base\model\spec_file\row`.

### `fan\core\base\model\spec_file\row::runAfterDelete`

- Расположение: `_core/base/model/spec_file/row.php:48`
- Сигнатура: `function runAfterDelete(mixed $delId)`
- Описание: Запускает или обрабатывает workflow `after delete` для этого метода класса `fan\core\base\model\spec_file\row`.

### `fan\core\base\model\spec_file\row::getEntityFile`

- Расположение: `_core/base/model/spec_file/row.php:58`
- Сигнатура: `function getEntityFile()`
- Описание: Получает, читает или вычисляет данные `entity file` в рамках этого метода класса `fan\core\base\model\spec_file\row`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\base\model\spec_file\row::get_is_deleted`

- Расположение: `_core/base/model/spec_file/row.php:75`
- Сигнатура: `function get_is_deleted()`
- Описание: Получает, читает или вычисляет данные `is deleted` в рамках этого метода класса `fan\core\base\model\spec_file\row`.

### `fan\core\base\model\spec_file\row::get_src_name`

- Расположение: `_core/base/model/spec_file/row.php:85`
- Сигнатура: `function get_src_name()`
- Описание: Получает, читает или вычисляет данные `src name` в рамках этого метода класса `fan\core\base\model\spec_file\row`.

### `fan\core\base\model\spec_file\row::setAccessType`

- Расположение: `_core/base/model/spec_file/row.php:101`
- Сигнатура: `function setAccessType(string $key, bool $save = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `access type` в рамках этого метода класса `fan\core\base\model\spec_file\row`.

### `fan\core\base\model\spec_file\row::setPersonalAccess`

- Расположение: `_core/base/model/spec_file/row.php:119`
- Сигнатура: `function setPersonalAccess(string $membType = 'owner', ?string $expireDate = null, int|float $accessQtt = -1, int|float $memrId = 0)`
- Описание: Устанавливает, добавляет или сохраняет данные `personal access` в рамках этого метода класса `fan\core\base\model\spec_file\row`.

### `fan\core\base\model\spec_file\row::removePersonalAccess`

- Расположение: `_core/base/model/spec_file/row.php:132`
- Сигнатура: `function removePersonalAccess(int $removeType = 1, int|float|null $membId = null)`
- Описание: Удаляет или сбрасывает состояние `personal access` для этого метода класса `fan\core\base\model\spec_file\row`.

### `fan\core\base\model\spec_file\row::checkAccess`

- Расположение: `_core/base/model/spec_file/row.php:142`
- Сигнатура: `function checkAccess()`
- Описание: Проверяет условие или валидирует данные `access` и возвращает результат либо выбрасывает исключение.

### `fan\core\base\model\spec_file\row::checkIsOwner`

- Расположение: `_core/base/model/spec_file/row.php:152`
- Сигнатура: `function checkIsOwner()`
- Описание: Проверяет условие или валидирует данные `is owner` и возвращает результат либо выбрасывает исключение.

## `_core/base/service.php`

### `fan\core\base\service::__construct`

- Расположение: `_core/base/service.php:63`
- Сигнатура: `function __construct($allowIni = true)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\base\service`.

### `fan\core\base\service::checkName`

- Расположение: `_core/base/service.php:81`
- Сигнатура: `function checkName($name): string`
- Описание: Проверяет условие или валидирует данные `name` и возвращает результат либо выбрасывает исключение.

### `fan\core\base\service::isSingleton`

- Расположение: `_core/base/service.php:93`
- Сигнатура: `function isSingleton();`
- Описание: Проверяет условие или валидирует данные `singleton` и возвращает результат либо выбрасывает исключение.

### `fan\core\base\service::isEnabled`

- Расположение: `_core/base/service.php:100`
- Сигнатура: `function isEnabled()`
- Описание: Проверяет условие или валидирует данные `enabled` и возвращает результат либо выбрасывает исключение.

### `fan\core\base\service::resetEnabled`

- Расположение: `_core/base/service.php:110`
- Сигнатура: `function resetEnabled()`
- Описание: Удаляет или сбрасывает состояние `enabled` для этого метода класса `fan\core\base\service`.

### `fan\core\base\service::getConfig`

- Расположение: `_core/base/service.php:125`
- Сигнатура: `function getConfig($key = null, $default = null)`
- Описание: Получает, читает или вычисляет данные `config` в рамках этого метода класса `fan\core\base\service`.

### `fan\core\base\service::setExceptionDbOper`

- Расположение: `_core/base/service.php:141`
- Сигнатура: `function setExceptionDbOper(?string $exceptionDbOper = null): self`
- Описание: Устанавливает, добавляет или сохраняет данные `exception db oper` в рамках этого метода класса `fan\core\base\service`.

### `fan\core\base\service::getExceptionDbOper`

- Расположение: `_core/base/service.php:158`
- Сигнатура: `function getExceptionDbOper(): ?string`
- Описание: Получает, читает или вычисляет данные `exception db oper` в рамках этого метода класса `fan\core\base\service`.

### `fan\core\base\service::getExceptionLogType`

- Расположение: `_core/base/service.php:168`
- Сигнатура: `function getExceptionLogType(): string`
- Описание: Получает, читает или вычисляет данные `exception log type` в рамках этого метода класса `fan\core\base\service`.

### `fan\core\base\service::setExceptionLogType`

- Расположение: `_core/base/service.php:180`
- Сигнатура: `function setExceptionLogType($exceptionLog): self`
- Описание: Устанавливает, добавляет или сохраняет данные `exception log type` в рамках этого метода класса `fan\core\base\service`.

### `fan\core\base\service::addListener`

- Расположение: `_core/base/service.php:200`
- Сигнатура: `function addListener(string $eventName, callable $callBack): self`
- Описание: Устанавливает, добавляет или сохраняет данные `listener` в рамках этого метода класса `fan\core\base\service`.

### `fan\core\base\service::_saveInstance`

- Расположение: `_core/base/service.php:212`
- Сигнатура: `function _saveInstance()`
- Описание: Выполняет логику `save instance` и возвращает вычисленный результат.

### `fan\core\base\service::_setConfig`

- Расположение: `_core/base/service.php:222`
- Сигнатура: `function _setConfig()`
- Описание: Выполняет логику `set config` и возвращает вычисленный результат.

### `fan\core\base\service::_getCacheData`

- Расположение: `_core/base/service.php:237`
- Сигнатура: `function _getCacheData(string $key, mixed $default = null)`
- Описание: Выполняет логику `get cache data` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\base\service::_setCacheData`

- Расположение: `_core/base/service.php:254`
- Сигнатура: `function _setCacheData(string $key, mixed $value): self`
- Описание: Выполняет логику `set cache data` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\base\service::_getConfigurator`

- Расположение: `_core/base/service.php:271`
- Сигнатура: `function _getConfigurator(): object`
- Описание: Выполняет логику `get configurator` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\base\service::_getEngine`

- Расположение: `_core/base/service.php:284`
- Сигнатура: `function _getEngine($name, $object = true)`
- Описание: Выполняет логику `get engine` и возвращает вычисленный результат.

### `fan\core\base\service::_getDelegate`

- Расположение: `_core/base/service.php:318`
- Сигнатура: `function _getDelegate($class)`
- Описание: Выполняет логику `get delegate` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\service::_extensionCall`

- Расположение: `_core/base/service.php:340`
- Сигнатура: `function _extensionCall(string $method, array $args): bool`
- Описание: Выполняет логику `extension call` и возвращает вычисленный результат.

### `fan\core\base\service::_makeServiceException`

- Расположение: `_core/base/service.php:357`
- Сигнатура: `function _makeServiceException( string $logErrMsg, ?string $exceptionDbOper = 'rollback', int $code = E_USER_ERROR, ?\Exception $previous = null ): void`
- Описание: Выполняет workflow-логику `make service exception`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\service::_subscribeForService`

- Расположение: `_core/base/service.php:381`
- Сигнатура: `function _subscribeForService(string $serviceName, string $eventName, callable $callBack): void`
- Описание: Выполняет workflow-логику `subscribe for service`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\base\service::_broadcastMessage`

- Расположение: `_core/base/service.php:402`
- Сигнатура: `function _broadcastMessage(string $eventName, mixed $data): void`
- Описание: Выполняет логику `broadcast message` и возвращает вычисленный результат.

### `fan\core\base\service::__call`

- Расположение: `_core/base/service.php:425`
- Сигнатура: `function __call(string $method, array $args)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\base\service`.
- Побочные эффекты: может выбрасывать исключения

## `_core/base/service/multi.php`

### `fan\core\base\service\multi::resetEnabledAll`

- Расположение: `_core/base/service/multi.php:29`
- Сигнатура: `function resetEnabledAll()`
- Описание: Удаляет или сбрасывает состояние `enabled all` для этого метода класса `fan\core\base\service\multi`.

### `fan\core\base\service\multi::isSingleton`

- Расположение: `_core/base/service/multi.php:43`
- Сигнатура: `function isSingleton()`
- Описание: Проверяет условие или валидирует данные `singleton` и возвращает результат либо выбрасывает исключение.

## `_core/base/service/single.php`

### `fan\core\base\service\single::instance`

- Расположение: `_core/base/service/single.php:34`
- Сигнатура: `function instance()`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.

### `fan\core\base\service\single::isSingleton`

- Расположение: `_core/base/service/single.php:53`
- Сигнатура: `function isSingleton()`
- Описание: Проверяет условие или валидирует данные `singleton` и возвращает результат либо выбрасывает исключение.

### `fan\core\base\service\single::_saveInstance`

- Расположение: `_core/base/service/single.php:65`
- Сигнатура: `function _saveInstance()`
- Описание: Выполняет логику `save instance` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

## `_core/base/timer_program.php`

### `fan\core\base\timer_program::setTimerRow`

- Расположение: `_core/base/timer_program.php:40`
- Сигнатура: `function setTimerRow($timerRow)`
- Описание: Устанавливает, добавляет или сохраняет данные `timer row` в рамках этого метода класса `fan\core\base\timer_program`.

### `fan\core\base\timer_program::getTimerRow`

- Расположение: `_core/base/timer_program.php:50`
- Сигнатура: `function getTimerRow()`
- Описание: Получает, читает или вычисляет данные `timer row` в рамках этого метода класса `fan\core\base\timer_program`.

### `fan\core\base\timer_program::setPeriod`

- Расположение: `_core/base/timer_program.php:62`
- Сигнатура: `function setPeriod(int|float $period)`
- Описание: Устанавливает, добавляет или сохраняет данные `period` в рамках этого метода класса `fan\core\base\timer_program`.

### `fan\core\base\timer_program::getPeriod`

- Расположение: `_core/base/timer_program.php:74`
- Сигнатура: `function getPeriod()`
- Описание: Получает, читает или вычисляет данные `period` в рамках этого метода класса `fan\core\base\timer_program`.

## `_core/base/transfer.php`

### `fan\core\base\transfer::__construct`

- Расположение: `_core/base/transfer.php:42`
- Сигнатура: `function __construct($newUri, $newQueryString = null, $dbOper = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\base\transfer`.

### `fan\core\base\transfer::getTransferType`

- Расположение: `_core/base/transfer.php:57`
- Сигнатура: `function getTransferType()`
- Описание: Получает, читает или вычисляет данные `transfer type` в рамках этого метода класса `fan\core\base\transfer`.

### `fan\core\base\transfer::getRequest`

- Расположение: `_core/base/transfer.php:67`
- Сигнатура: `function getRequest()`
- Описание: Получает, читает или вычисляет данные `request` в рамках этого метода класса `fan\core\base\transfer`.

### `fan\core\base\transfer::getHost`

- Расположение: `_core/base/transfer.php:83`
- Сигнатура: `function getHost()`
- Описание: Получает, читает или вычисляет данные `host` в рамках этого метода класса `fan\core\base\transfer`.

### `fan\core\base\transfer::isShiftCurrent`

- Расположение: `_core/base/transfer.php:93`
- Сигнатура: `function isShiftCurrent()`
- Описание: Проверяет условие или валидирует данные `shift current` и возвращает результат либо выбрасывает исключение.

### `fan\core\base\transfer::getNewUri`

- Расположение: `_core/base/transfer.php:103`
- Сигнатура: `function getNewUri()`
- Описание: Получает, читает или вычисляет данные `new uri` в рамках этого метода класса `fan\core\base\transfer`.

### `fan\core\base\transfer::getNewQueryString`

- Расположение: `_core/base/transfer.php:113`
- Сигнатура: `function getNewQueryString()`
- Описание: Получает, читает или вычисляет данные `new query string` в рамках этого метода класса `fan\core\base\transfer`.

## `_core/base/transfer/out.php`

### `fan\core\base\transfer\out::__construct`

- Расположение: `_core/base/transfer/out.php:28`
- Сигнатура: `function __construct($newUrn, $newQueryString = null, $dbOper = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\base\transfer\out`.

## `_core/base/transfer/sham.php`

### `fan\core\base\transfer\sham::__construct`

- Расположение: `_core/base/transfer/sham.php:28`
- Сигнатура: `function __construct($newUrn, $newQueryString = null, $dbOper = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\base\transfer\sham`.

## `_core/base/transfer/transfer_int.php`

### `fan\core\base\transfer\transfer_int::__construct`

- Расположение: `_core/base/transfer/transfer_int.php:28`
- Сигнатура: `function __construct($newUrn, $newQueryString = null, $dbOper = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\base\transfer\transfer_int`.

## `_core/block/admin/base.php`

### `fan\core\block\admin\base::getHashArray`

- Расположение: `_core/block/admin/base.php:31`
- Сигнатура: `function getHashArray(array $arg, ?array $arrMerge = null, $mergeBefore = true)`
- Описание: Получает, читает или вычисляет данные `hash array` в рамках этого метода класса `fan\core\block\admin\base`.

### `fan\core\block\admin\base::getIncludedArray`

- Расположение: `_core/block/admin/base.php:49`
- Сигнатура: `function getIncludedArray()`
- Описание: Получает, читает или вычисляет данные `included array` в рамках этого метода класса `fan\core\block\admin\base`.

### `fan\core\block\admin\base::getRowset`

- Расположение: `_core/block/admin/base.php:121`
- Сигнатура: `function getRowset(array $arg)`
- Описание: Получает, читает или вычисляет данные `rowset` в рамках этого метода класса `fan\core\block\admin\base`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\base::defineRetKeys`

- Расположение: `_core/block/admin/base.php:154`
- Сигнатура: `function defineRetKeys(&$retKeys, array $retData, int $depth, int $i)`
- Описание: Выполняет логику `define ret keys` и возвращает вычисленный результат.

### `fan\core\block\admin\base::setTemplateVar`

- Расположение: `_core/block/admin/base.php:180`
- Сигнатура: `function setTemplateVar(string $key, mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `template var` в рамках этого метода класса `fan\core\block\admin\base`.

### `fan\core\block\admin\base::getTemplateCode`

- Расположение: `_core/block/admin/base.php:200`
- Сигнатура: `function getTemplateCode(array $addVars = [])`
- Описание: Получает, читает или вычисляет данные `template code` в рамках этого метода класса `fan\core\block\admin\base`.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_core/block/admin/data.php`

### `fan\core\block\admin\data::init`

- Расположение: `_core/block/admin/data.php:50`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\core\block\admin\data`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\data::validateData`

- Расположение: `_core/block/admin/data.php:108`
- Сигнатура: `function validateData(&$edit, &$insert)`
- Описание: Проверяет условие или валидирует данные `data` и возвращает результат либо выбрасывает исключение.

### `fan\core\block\admin\data::parseData`

- Расположение: `_core/block/admin/data.php:121`
- Сигнатура: `function parseData($edit, $insert)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `data` для этого метода класса `fan\core\block\admin\data`.

### `fan\core\block\admin\data::saveRow`

- Расположение: `_core/block/admin/data.php:136`
- Сигнатура: `function saveRow(\fan\core\base\model\row $row, array $data, array $fields, array $addFields = [])`
- Описание: Устанавливает, добавляет или сохраняет данные `row` в рамках этого метода класса `fan\core\block\admin\data`.

### `fan\core\block\admin\data::deleteData`

- Расположение: `_core/block/admin/data.php:162`
- Сигнатура: `function deleteData($del)`
- Описание: Удаляет или сбрасывает состояние `data` для этого метода класса `fan\core\block\admin\data`.

### `fan\core\block\admin\data::checkDBerror`

- Расположение: `_core/block/admin/data.php:175`
- Сигнатура: `function checkDBerror(\fan\core\base\model\row $row, $errPref = '')`
- Описание: Проверяет условие или валидирует данные `d berror` и возвращает результат либо выбрасывает исключение.

### `fan\core\block\admin\data::getMainData`

- Расположение: `_core/block/admin/data.php:198`
- Сигнатура: `function getMainData($data, $force = [])`
- Описание: Получает, читает или вычисляет данные `main data` в рамках этого метода класса `fan\core\block\admin\data`.

### `fan\core\block\admin\data::initTplVar`

- Расположение: `_core/block/admin/data.php:248`
- Сигнатура: `function initTplVar()`
- Описание: Запускает или обрабатывает workflow `tpl var` для этого метода класса `fan\core\block\admin\data`.

### `fan\core\block\admin\data::getAddParam`

- Расположение: `_core/block/admin/data.php:258`
- Сигнатура: `function getAddParam()`
- Описание: Получает, читает или вычисляет данные `add param` в рамках этого метода класса `fan\core\block\admin\data`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\data::getExtraData`

- Расположение: `_core/block/admin/data.php:273`
- Сигнатура: `function getExtraData()`
- Описание: Получает, читает или вычисляет данные `extra data` в рамках этого метода класса `fan\core\block\admin\data`.

### `fan\core\block\admin\data::getCondition`

- Расположение: `_core/block/admin/data.php:288`
- Сигнатура: `function getCondition()`
- Описание: Получает, читает или вычисляет данные `condition` в рамках этого метода класса `fan\core\block\admin\data`.

### `fan\core\block\admin\data::getContentData`

- Расположение: `_core/block/admin/data.php:303`
- Сигнатура: `function getContentData()`
- Описание: Получает, читает или вычисляет данные `content data` в рамках этого метода класса `fan\core\block\admin\data`.

### `fan\core\block\admin\data::getFieldLabel`

- Расположение: `_core/block/admin/data.php:316`
- Сигнатура: `function getFieldLabel($name)`
- Описание: Получает, читает или вычисляет данные `field label` в рамках этого метода класса `fan\core\block\admin\data`.

### `fan\core\block\admin\data::doValidate`

- Расположение: `_core/block/admin/data.php:331`
- Сигнатура: `function doValidate(&$data, string $type, int|float|null $id = null)`
- Описание: Выполняет логику `do validate` и возвращает вычисленный результат.

### `fan\core\block\admin\data::rule_is_required`

- Расположение: `_core/block/admin/data.php:370`
- Сигнатура: `function rule_is_required(mixed $value)`
- Описание: Выполняет логику `rule is required` и возвращает вычисленный результат.

### `fan\core\block\admin\data::rule_is_int`

- Расположение: `_core/block/admin/data.php:383`
- Сигнатура: `function rule_is_int(mixed $value, array $data)`
- Описание: Выполняет логику `rule is int` и возвращает вычисленный результат.

### `fan\core\block\admin\data::rule_is_float`

- Расположение: `_core/block/admin/data.php:405`
- Сигнатура: `function rule_is_float(mixed $value, array $data)`
- Описание: Выполняет логику `rule is float` и возвращает вычисленный результат.

### `fan\core\block\admin\data::rule_is_date`

- Расположение: `_core/block/admin/data.php:428`
- Сигнатура: `function rule_is_date(mixed $value, array $data)`
- Описание: Выполняет логику `rule is date` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\data::rule_is_email`

- Расположение: `_core/block/admin/data.php:449`
- Сигнатура: `function rule_is_email(mixed $value, array $data)`
- Описание: Выполняет логику `rule is email` и возвращает вычисленный результат.

### `fan\core\block\admin\data::rule_is_alphalogin`

- Расположение: `_core/block/admin/data.php:465`
- Сигнатура: `function rule_is_alphalogin(mixed $value, array $data)`
- Описание: Выполняет логику `rule is alphalogin` и возвращает вычисленный результат.

### `fan\core\block\admin\data::rule_is_alphanumeric`

- Расположение: `_core/block/admin/data.php:481`
- Сигнатура: `function rule_is_alphanumeric(mixed $value, array $data)`
- Описание: Выполняет логику `rule is alphanumeric` и возвращает вычисленный результат.

### `fan\core\block\admin\data::rule_match_regexp`

- Расположение: `_core/block/admin/data.php:497`
- Сигнатура: `function rule_match_regexp(mixed $value, array $data)`
- Описание: Выполняет логику `rule match regexp` и возвращает вычисленный результат.

### `fan\core\block\admin\data::rule_equal_to`

- Расположение: `_core/block/admin/data.php:513`
- Сигнатура: `function rule_equal_to(mixed $value, array $data)`
- Описание: Выполняет логику `rule equal to` и возвращает вычисленный результат.

### `fan\core\block\admin\data::rule_not_equal_to`

- Расположение: `_core/block/admin/data.php:533`
- Сигнатура: `function rule_not_equal_to(mixed $value, array $data)`
- Описание: Выполняет логику `rule not equal to` и возвращает вычисленный результат.

### `fan\core\block\admin\data::rule_greater_than`

- Расположение: `_core/block/admin/data.php:553`
- Сигнатура: `function rule_greater_than(mixed $value, array $data)`
- Описание: Выполняет логику `rule greater than` и возвращает вычисленный результат.

### `fan\core\block\admin\data::rule_lesser_than`

- Расположение: `_core/block/admin/data.php:577`
- Сигнатура: `function rule_lesser_than(mixed $value, array $data)`
- Описание: Выполняет логику `rule lesser than` и возвращает вычисленный результат.

### `fan\core\block\admin\data::rule_greater_or_equal_to`

- Расположение: `_core/block/admin/data.php:601`
- Сигнатура: `function rule_greater_or_equal_to(mixed $value, array $data)`
- Описание: Выполняет логику `rule greater or equal to` и возвращает вычисленный результат.

### `fan\core\block\admin\data::rule_lesser_or_equal_to`

- Расположение: `_core/block/admin/data.php:621`
- Сигнатура: `function rule_lesser_or_equal_to(mixed $value, array $data)`
- Описание: Выполняет логику `rule lesser or equal to` и возвращает вычисленный результат.

## `_core/block/admin/data_form.php`

### `fan\core\block\admin\data_form::validateData`

- Расположение: `_core/block/admin/data_form.php:35`
- Сигнатура: `function validateData(&$edit, &$insert)`
- Описание: Проверяет условие или валидирует данные `data` и возвращает результат либо выбрасывает исключение.

### `fan\core\block\admin\data_form::parseData`

- Расположение: `_core/block/admin/data_form.php:57`
- Сигнатура: `function parseData($edit, $insert)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `data` для этого метода класса `fan\core\block\admin\data_form`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\data_form::initTplVar`

- Расположение: `_core/block/admin/data_form.php:79`
- Сигнатура: `function initTplVar()`
- Описание: Запускает или обрабатывает workflow `tpl var` для этого метода класса `fan\core\block\admin\data_form`.

### `fan\core\block\admin\data_form::getMainData`

- Расположение: `_core/block/admin/data_form.php:97`
- Сигнатура: `function getMainData($data, $force = [])`
- Описание: Получает, читает или вычисляет данные `main data` в рамках этого метода класса `fan\core\block\admin\data_form`.

### `fan\core\block\admin\data_form::getContentData`

- Расположение: `_core/block/admin/data_form.php:113`
- Сигнатура: `function getContentData($cacheEnable = true)`
- Описание: Получает, читает или вычисляет данные `content data` в рамках этого метода класса `fan\core\block\admin\data_form`.

### `fan\core\block\admin\data_form::getFieldLabel`

- Расположение: `_core/block/admin/data_form.php:135`
- Сигнатура: `function getFieldLabel($name)`
- Описание: Получает, читает или вычисляет данные `field label` в рамках этого метода класса `fan\core\block\admin\data_form`.

### `fan\core\block\admin\data_form::getCurrentRow`

- Расположение: `_core/block/admin/data_form.php:152`
- Сигнатура: `function getCurrentRow($cacheEnable)`
- Описание: Получает, читает или вычисляет данные `current row` в рамках этого метода класса `fan\core\block\admin\data_form`.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_core/block/admin/data_info.php`

### `fan\core\block\admin\data_info::getMainData`

- Расположение: `_core/block/admin/data_info.php:30`
- Сигнатура: `function getMainData($data, $force = [])`
- Описание: Получает, читает или вычисляет данные `main data` в рамках этого метода класса `fan\core\block\admin\data_info`.

### `fan\core\block\admin\data_info::getExtraData`

- Расположение: `_core/block/admin/data_info.php:43`
- Сигнатура: `function getExtraData()`
- Описание: Получает, читает или вычисляет данные `extra data` в рамках этого метода класса `fan\core\block\admin\data_info`.

## `_core/block/admin/data_table.php`

### `fan\core\block\admin\data_table::validateData`

- Расположение: `_core/block/admin/data_table.php:35`
- Сигнатура: `function validateData(&$edit, &$insert)`
- Описание: Проверяет условие или валидирует данные `data` и возвращает результат либо выбрасывает исключение.

### `fan\core\block\admin\data_table::validateDataOnce`

- Расположение: `_core/block/admin/data_table.php:60`
- Сигнатура: `function validateDataOnce(&$data, $type, &$tmpErr)`
- Описание: Проверяет условие или валидирует данные `data once` и возвращает результат либо выбрасывает исключение.

### `fan\core\block\admin\data_table::parseData`

- Расположение: `_core/block/admin/data_table.php:83`
- Сигнатура: `function parseData($edit, $insert)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `data` для этого метода класса `fan\core\block\admin\data_table`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\data_table::deleteData`

- Расположение: `_core/block/admin/data_table.php:130`
- Сигнатура: `function deleteData($del)`
- Описание: Удаляет или сбрасывает состояние `data` для этого метода класса `fan\core\block\admin\data_table`.

### `fan\core\block\admin\data_table::initTplVar`

- Расположение: `_core/block/admin/data_table.php:154`
- Сигнатура: `function initTplVar()`
- Описание: Запускает или обрабатывает workflow `tpl var` для этого метода класса `fan\core\block\admin\data_table`.

### `fan\core\block\admin\data_table::getExtraData`

- Расположение: `_core/block/admin/data_table.php:198`
- Сигнатура: `function getExtraData()`
- Описание: Получает, читает или вычисляет данные `extra data` в рамках этого метода класса `fan\core\block\admin\data_table`.

### `fan\core\block\admin\data_table::getContentData`

- Расположение: `_core/block/admin/data_table.php:218`
- Сигнатура: `function getContentData($cacheEnable = true)`
- Описание: Получает, читает или вычисляет данные `content data` в рамках этого метода класса `fan\core\block\admin\data_table`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\data_table::getArrayAssoc`

- Расположение: `_core/block/admin/data_table.php:271`
- Сигнатура: `function getArrayAssoc(\fan\core\base\model\entity $ett, string $ettKey, array $fld, int|float $qtt, int|float $offset, string $order, $excludeId = true)`
- Описание: Получает, читает или вычисляет данные `array assoc` в рамках этого метода класса `fan\core\block\admin\data_table`.

### `fan\core\block\admin\data_table::getFieldLabel`

- Расположение: `_core/block/admin/data_table.php:287`
- Сигнатура: `function getFieldLabel($name)`
- Описание: Получает, читает или вычисляет данные `field label` в рамках этого метода класса `fan\core\block\admin\data_table`.

### `fan\core\block\admin\data_table::loadEntityById`

- Расположение: `_core/block/admin/data_table.php:304`
- Сигнатура: `function loadEntityById(int|float $id)`
- Описание: Получает, читает или вычисляет данные `entity by id` в рамках этого метода класса `fan\core\block\admin\data_table`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\data_table::definePager`

- Расположение: `_core/block/admin/data_table.php:318`
- Сигнатура: `function definePager(int|float $page, $ett, $ettKey)`
- Описание: Выполняет логику `define pager` и возвращает вычисленный результат.

## `_core/block/admin/form_pattern.php`

### `fan\core\block\admin\form_pattern::init`

- Расположение: `_core/block/admin/form_pattern.php:26`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\core\block\admin\form_pattern`.

## `_core/block/admin/index.php`

### `fan\core\block\admin\index::init`

- Расположение: `_core/block/admin/index.php:27`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\core\block\admin\index`.

### `fan\core\block\admin\index::initRequired`

- Расположение: `_core/block/admin/index.php:37`
- Сигнатура: `function initRequired()`
- Описание: Запускает или обрабатывает workflow `required` для этого метода класса `fan\core\block\admin\index`.

## `_core/block/admin/select_dependent.php`

### `fan\core\block\admin\select_dependent::init`

- Расположение: `_core/block/admin/select_dependent.php:26`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\core\block\admin\select_dependent`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\select_dependent::do_load_next_list`

- Расположение: `_core/block/admin/select_dependent.php:49`
- Сигнатура: `function do_load_next_list($data)`
- Описание: Выполняет логику `do load next list` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_core/block/admin/structure.php`

### `fan\core\block\admin\structure::init`

- Расположение: `_core/block/admin/structure.php:26`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\core\block\admin\structure`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\structure::initTplVar`

- Расположение: `_core/block/admin/structure.php:68`
- Сигнатура: `function initTplVar()`
- Описание: Запускает или обрабатывает workflow `tpl var` для этого метода класса `fan\core\block\admin\structure`.

### `fan\core\block\admin\structure::getAddParam`

- Расположение: `_core/block/admin/structure.php:77`
- Сигнатура: `function getAddParam()`
- Описание: Получает, читает или вычисляет данные `add param` в рамках этого метода класса `fan\core\block\admin\structure`.

### `fan\core\block\admin\structure::getExtraData`

- Расположение: `_core/block/admin/structure.php:87`
- Сигнатура: `function getExtraData()`
- Описание: Получает, читает или вычисляет данные `extra data` в рамках этого метода класса `fan\core\block\admin\structure`.

### `fan\core\block\admin\structure::getCondition`

- Расположение: `_core/block/admin/structure.php:97`
- Сигнатура: `function getCondition()`
- Описание: Получает, читает или вычисляет данные `condition` в рамках этого метода класса `fan\core\block\admin\structure`.

## `_core/block/admin/upload_file.php`

### `fan\core\block\admin\upload_file::finishConstruct`

- Расположение: `_core/block/admin/upload_file.php:41`
- Сигнатура: `function finishConstruct($container, $containerMeta, $allowSetEmbedded = true)`
- Описание: Запускает или обрабатывает workflow `construct` для этого метода класса `fan\core\block\admin\upload_file`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\upload_file::init`

- Расположение: `_core/block/admin/upload_file.php:64`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\core\block\admin\upload_file`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\upload_file::checkMainTableId`

- Расположение: `_core/block/admin/upload_file.php:137`
- Сигнатура: `function checkMainTableId(&$mainRow, &$data, $main, $link)`
- Описание: Проверяет условие или валидирует данные `main table id` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\upload_file::checkLinkTableId`

- Расположение: `_core/block/admin/upload_file.php:157`
- Сигнатура: `function checkLinkTableId(&$linkRow, &$data, $main, $link)`
- Описание: Проверяет условие или валидирует данные `link table id` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\upload_file::getFileLineData`

- Расположение: `_core/block/admin/upload_file.php:176`
- Сигнатура: `function getFileLineData($data, $link)`
- Описание: Получает, читает или вычисляет данные `file line data` в рамках этого метода класса `fan\core\block\admin\upload_file`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\upload_file::getFileOneData`

- Расположение: `_core/block/admin/upload_file.php:195`
- Сигнатура: `function getFileOneData($mainRow, $main, $link)`
- Описание: Получает, читает или вычисляет данные `file one data` в рамках этого метода класса `fan\core\block\admin\upload_file`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\upload_file::getFileData`

- Расположение: `_core/block/admin/upload_file.php:213`
- Сигнатура: `function getFileData(mixed $fileId)`
- Описание: Получает, читает или вычисляет данные `file data` в рамках этого метода класса `fan\core\block\admin\upload_file`.

## `_core/block/admin/upload_flash.php`

### `fan\core\block\admin\upload_flash::finishConstruct`

- Расположение: `_core/block/admin/upload_flash.php:41`
- Сигнатура: `function finishConstruct($container, $containerMeta, $allowSetEmbedded = true)`
- Описание: Запускает или обрабатывает workflow `construct` для этого метода класса `fan\core\block\admin\upload_flash`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\upload_flash::init`

- Расположение: `_core/block/admin/upload_flash.php:64`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\core\block\admin\upload_flash`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\upload_flash::checkMainTableId`

- Расположение: `_core/block/admin/upload_flash.php:137`
- Сигнатура: `function checkMainTableId(&$mainRow, &$data, $main, $link)`
- Описание: Проверяет условие или валидирует данные `main table id` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\upload_flash::checkLinkTableId`

- Расположение: `_core/block/admin/upload_flash.php:157`
- Сигнатура: `function checkLinkTableId(&$linkRow, &$data, $main, $link)`
- Описание: Проверяет условие или валидирует данные `link table id` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\upload_flash::getFlashLineData`

- Расположение: `_core/block/admin/upload_flash.php:176`
- Сигнатура: `function getFlashLineData($data, $link)`
- Описание: Получает, читает или вычисляет данные `flash line data` в рамках этого метода класса `fan\core\block\admin\upload_flash`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\upload_flash::getFlashOneData`

- Расположение: `_core/block/admin/upload_flash.php:195`
- Сигнатура: `function getFlashOneData($mainRow, $main, $link)`
- Описание: Получает, читает или вычисляет данные `flash one data` в рамках этого метода класса `fan\core\block\admin\upload_flash`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\upload_flash::getFlashData`

- Расположение: `_core/block/admin/upload_flash.php:213`
- Сигнатура: `function getFlashData(mixed $flashId)`
- Описание: Получает, читает или вычисляет данные `flash data` в рамках этого метода класса `fan\core\block\admin\upload_flash`.

## `_core/block/admin/upload_image.php`

### `fan\core\block\admin\upload_image::finishConstruct`

- Расположение: `_core/block/admin/upload_image.php:46`
- Сигнатура: `function finishConstruct($container, $containerMeta, $allowSetEmbedded = true)`
- Описание: Запускает или обрабатывает workflow `construct` для этого метода класса `fan\core\block\admin\upload_image`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\upload_image::init`

- Расположение: `_core/block/admin/upload_image.php:93`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\core\block\admin\upload_image`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\upload_image::operationDeleteImage`

- Расположение: `_core/block/admin/upload_image.php:156`
- Сигнатура: `function operationDeleteImage(&$data, \fan\core\base\model\row $mainRow, \fan\core\base\model\row $linkRow, \fan\core\base\model\spec_file\image\row $img, array $main, array $link)`
- Описание: Выполняет workflow-логику `operation delete image`.

### `fan\core\block\admin\upload_image::operationUploadImage`

- Расположение: `_core/block/admin/upload_image.php:182`
- Сигнатура: `function operationUploadImage(&$data, \fan\core\base\model\row $mainRow, \fan\core\base\model\row $linkRow, \fan\core\base\model\spec_file\image\row $img, array $main, array $link)`
- Описание: Выполняет workflow-логику `operation upload image`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\upload_image::operationSetAttributes`

- Расположение: `_core/block/admin/upload_image.php:204`
- Сигнатура: `function operationSetAttributes(&$data, \fan\core\base\model\spec_file\image\row $img)`
- Описание: Выполняет workflow-логику `operation set attributes`.

### `fan\core\block\admin\upload_image::checkMainTableId`

- Расположение: `_core/block/admin/upload_image.php:224`
- Сигнатура: `function checkMainTableId(&$mainRow, &$data, $main, $link)`
- Описание: Проверяет условие или валидирует данные `main table id` и возвращает результат либо выбрасывает исключение.

### `fan\core\block\admin\upload_image::checkLinkTableId`

- Расположение: `_core/block/admin/upload_image.php:244`
- Сигнатура: `function checkLinkTableId(&$linkRow, &$data, $main, $link)`
- Описание: Проверяет условие или валидирует данные `link table id` и возвращает результат либо выбрасывает исключение.

### `fan\core\block\admin\upload_image::getImageLineData`

- Расположение: `_core/block/admin/upload_image.php:263`
- Сигнатура: `function getImageLineData($data, $link)`
- Описание: Получает, читает или вычисляет данные `image line data` в рамках этого метода класса `fan\core\block\admin\upload_image`.

### `fan\core\block\admin\upload_image::getImageOneData`

- Расположение: `_core/block/admin/upload_image.php:287`
- Сигнатура: `function getImageOneData($mainRow, $main, $link)`
- Описание: Получает, читает или вычисляет данные `image one data` в рамках этого метода класса `fan\core\block\admin\upload_image`.

### `fan\core\block\admin\upload_image::getImageData`

- Расположение: `_core/block/admin/upload_image.php:305`
- Сигнатура: `function getImageData($imgId)`
- Описание: Получает, читает или вычисляет данные `image data` в рамках этого метода класса `fan\core\block\admin\upload_image`.

### `fan\core\block\admin\upload_image::getRow`

- Расположение: `_core/block/admin/upload_image.php:325`
- Сигнатура: `function getRow(string $ettName, mixed $id = null)`
- Описание: Получает, читает или вычисляет данные `row` в рамках этого метода класса `fan\core\block\admin\upload_image`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\upload_image::getEntity`

- Расположение: `_core/block/admin/upload_image.php:343`
- Сигнатура: `function getEntity(string $ettName)`
- Описание: Получает, читает или вычисляет данные `entity` в рамках этого метода класса `fan\core\block\admin\upload_image`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\admin\upload_image::getEttImageName`

- Расположение: `_core/block/admin/upload_image.php:354`
- Сигнатура: `function getEttImageName()`
- Описание: Получает, читает или вычисляет данные `ett image name` в рамках этого метода класса `fan\core\block\admin\upload_image`.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_core/block/base.php`

### `fan\core\block\base::__construct`

- Расположение: `_core/block/base.php:153`
- Сигнатура: `function __construct($blockName = null, ?\fan\core\service\tab $tab = null, ?base $container = null, $containerMeta = [], $fullConstr = true)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\block\base`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\base::finishConstruct`

- Расположение: `_core/block/base.php:184`
- Сигнатура: `function finishConstruct($container = null, $containerMeta = [], $allowSetEmbedded = true)`
- Описание: Запускает или обрабатывает workflow `construct` для этого метода класса `fan\core\block\base`.

### `fan\core\block\base::init`

- Расположение: `_core/block/base.php:226`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\core\block\base`.

### `fan\core\block\base::initRequired`

- Расположение: `_core/block/base.php:234`
- Сигнатура: `function initRequired()`
- Описание: Запускает или обрабатывает workflow `required` для этого метода класса `fan\core\block\base`.

### `fan\core\block\base::runAfterInit`

- Расположение: `_core/block/base.php:242`
- Сигнатура: `function runAfterInit()`
- Описание: Запускает или обрабатывает workflow `after init` для этого метода класса `fan\core\block\base`.

### `fan\core\block\base::getBlockName`

- Расположение: `_core/block/base.php:252`
- Сигнатура: `function getBlockName()`
- Описание: Получает, читает или вычисляет данные `block name` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::getTab`

- Расположение: `_core/block/base.php:262`
- Сигнатура: `function getTab()`
- Описание: Получает, читает или вычисляет данные `tab` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::getContainer`

- Расположение: `_core/block/base.php:272`
- Сигнатура: `function getContainer()`
- Описание: Получает, читает или вычисляет данные `container` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::getMetaMaker`

- Расположение: `_core/block/base.php:282`
- Сигнатура: `function getMetaMaker()`
- Описание: Получает, читает или вычисляет данные `meta maker` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::getMeta`

- Расположение: `_core/block/base.php:296`
- Сигнатура: `function getMeta(string|array|null $key = null, mixed $default = null, $convToArray = false)`
- Описание: Получает, читает или вычисляет данные `meta` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::getMetaVar`

- Расположение: `_core/block/base.php:309`
- Сигнатура: `function getMetaVar(string|array|null $key = null)`
- Описание: Получает, читает или вычисляет данные `meta var` в рамках этого метода класса `fan\core\block\base`.
- Побочные эффекты: логирует или сообщает об ошибках

### `fan\core\block\base::setMeta`

- Расположение: `_core/block/base.php:323`
- Сигнатура: `function setMeta(string|array $key, mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `meta` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::addMeta`

- Расположение: `_core/block/base.php:336`
- Сигнатура: `function addMeta(array|\fan\core\base\meta\row $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `meta` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::setMetaVar`

- Расположение: `_core/block/base.php:353`
- Сигнатура: `function setMetaVar(mixed $key, mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `meta var` в рамках этого метода класса `fan\core\block\base`.
- Побочные эффекты: логирует или сообщает об ошибках

### `fan\core\block\base::makeDelayedMeta`

- Расположение: `_core/block/base.php:366`
- Сигнатура: `function makeDelayedMeta(\fan\core\base\meta\row $meta)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `delayed meta` для этого метода класса `fan\core\block\base`.

### `fan\core\block\base::getDynamicMeta`

- Расположение: `_core/block/base.php:386`
- Сигнатура: `function getDynamicMeta($meta)`
- Описание: Получает, читает или вычисляет данные `dynamic meta` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::setDynamicMeta`

- Расположение: `_core/block/base.php:396`
- Сигнатура: `function setDynamicMeta()`
- Описание: Устанавливает, добавляет или сохраняет данные `dynamic meta` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::getRoleCondition`

- Расположение: `_core/block/base.php:411`
- Сигнатура: `function getRoleCondition()`
- Описание: Получает, читает или вычисляет данные `role condition` в рамках этого метода класса `fan\core\block\base`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\base::getViewParserName`

- Расположение: `_core/block/base.php:437`
- Сигнатура: `function getViewParserName()`
- Описание: Получает, читает или вычисляет данные `view parser name` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::getViewFormat`

- Расположение: `_core/block/base.php:448`
- Сигнатура: `function getViewFormat()`
- Описание: Получает, читает или вычисляет данные `view format` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::getView`

- Расположение: `_core/block/base.php:458`
- Сигнатура: `function getView()`
- Описание: Получает, читает или вычисляет данные `view` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::getViewData`

- Расположение: `_core/block/base.php:468`
- Сигнатура: `function getViewData()`
- Описание: Получает, читает или вычисляет данные `view data` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::getTemplate`

- Расположение: `_core/block/base.php:479`
- Сигнатура: `function getTemplate()`
- Описание: Получает, читает или вычисляет данные `template` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::setTemplate`

- Расположение: `_core/block/base.php:494`
- Сигнатура: `function setTemplate(string $templatePath, bool $allowException = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `template` в рамках этого метода класса `fan\core\block\base`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\block\base::getRequest`

- Расположение: `_core/block/base.php:510`
- Сигнатура: `function getRequest()`
- Описание: Получает, читает или вычисляет данные `request` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::getNamespace`

- Расположение: `_core/block/base.php:520`
- Сигнатура: `function getNamespace()`
- Описание: Получает, читает или вычисляет данные `namespace` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::getEmbeddedBlocks`

- Расположение: `_core/block/base.php:532`
- Сигнатура: `function getEmbeddedBlocks()`
- Описание: Получает, читает или вычисляет данные `embedded blocks` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::getEmbeddedBlock`

- Расположение: `_core/block/base.php:544`
- Сигнатура: `function getEmbeddedBlock($key)`
- Описание: Получает, читает или вычисляет данные `embedded block` в рамках этого метода класса `fan\core\block\base`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\block\base::getExceptionDbOper`

- Расположение: `_core/block/base.php:557`
- Сигнатура: `function getExceptionDbOper()`
- Описание: Получает, читает или вычисляет данные `exception db oper` в рамках этого метода класса `fan\core\block\base`.

### `fan\core\block\base::getSession`

- Расположение: `_core/block/base.php:567`
- Сигнатура: `function getSession()`
- Описание: Получает, читает или вычисляет данные `session` в рамках этого метода класса `fan\core\block\base`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\base::checkRunInit`

- Расположение: `_core/block/base.php:577`
- Сигнатура: `function checkRunInit()`
- Описание: Проверяет условие или валидирует данные `run init` и возвращает результат либо выбрасывает исключение.

### `fan\core\block\base::getDebugInfo`

- Расположение: `_core/block/base.php:587`
- Сигнатура: `function getDebugInfo()`
- Описание: Получает, читает или вычисляет данные `debug info` в рамках этого метода класса `fan\core\block\base`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\base::_createMetaMaker`

- Расположение: `_core/block/base.php:617`
- Сигнатура: `function _createMetaMaker()`
- Описание: Выполняет логику `create meta maker` и возвращает вычисленный результат.

### `fan\core\block\base::_createViewRouter`

- Расположение: `_core/block/base.php:627`
- Сигнатура: `function _createViewRouter()`
- Описание: Выполняет логику `create view router` и возвращает вычисленный результат.

### `fan\core\block\base::_setViewVar`

- Расположение: `_core/block/base.php:640`
- Сигнатура: `function _setViewVar(string $key, mixed $val)`
- Описание: Выполняет логику `set view var` и возвращает вычисленный результат.

### `fan\core\block\base::_preparseMeta`

- Расположение: `_core/block/base.php:651`
- Сигнатура: `function _preparseMeta()`
- Описание: Выполняет логику `preparse meta` и возвращает вычисленный результат.

### `fan\core\block\base::_makeDynamicMeta`

- Расположение: `_core/block/base.php:676`
- Сигнатура: `function _makeDynamicMeta(bool $force)`
- Описание: Выполняет workflow-логику `make dynamic meta`.

### `fan\core\block\base::_doRoleOperations`

- Расположение: `_core/block/base.php:692`
- Сигнатура: `function _doRoleOperations()`
- Описание: Выполняет workflow-логику `do role operations`.

### `fan\core\block\base::_transferor`

- Расположение: `_core/block/base.php:701`
- Сигнатура: `function _transferor()`
- Описание: Выполняет workflow-логику `transferor`.

### `fan\core\block\base::_postCreate`

- Расположение: `_core/block/base.php:710`
- Сигнатура: `function _postCreate()`
- Описание: Выполняет workflow-логику `post create`.

### `fan\core\block\base::_preOutput`

- Расположение: `_core/block/base.php:719`
- Сигнатура: `function _preOutput()`
- Описание: Выполняет workflow-логику `pre output`.

### `fan\core\block\base::_setRootBlockParameters`

- Расположение: `_core/block/base.php:731`
- Сигнатура: `function _setRootBlockParameters(?\fan\core\block\base $root = null, array $rootKeys = [])`
- Описание: Выполняет логику `set root block parameters` и возвращает вычисленный результат.

### `fan\core\block\base::_setTplVarsByMeta`

- Расположение: `_core/block/base.php:776`
- Сигнатура: `function _setTplVarsByMeta(array|\fan\core\base\meta\row $tplVars)`
- Описание: Выполняет логику `set tpl vars by meta` и возвращает вычисленный результат.

### `fan\core\block\base::_setTemplate`

- Расположение: `_core/block/base.php:793`
- Сигнатура: `function _setTemplate(string $templateName = '')`
- Описание: Выполняет логику `set template` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения

### `fan\core\block\base::_getTplSuffixes`

- Расположение: `_core/block/base.php:835`
- Сигнатура: `function _getTplSuffixes(string $separator = '_')`
- Описание: Выполняет логику `get tpl suffixes` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\base::_checkTemplate`

- Расположение: `_core/block/base.php:857`
- Сигнатура: `function _checkTemplate(string $blockPath, string $templateName, array $suffixes, string $extension = 'tpl')`
- Описание: Выполняет логику `check template` и возвращает вычисленный результат.

### `fan\core\block\base::_setEmbeddedBlocks`

- Расположение: `_core/block/base.php:883`
- Сигнатура: `function _setEmbeddedBlocks()`
- Описание: Выполняет логику `set embedded blocks` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\block\base::_getBlock`

- Расположение: `_core/block/base.php:923`
- Сигнатура: `function _getBlock(string $blockName, bool $allowException = true)`
- Описание: Выполняет логику `get block` и возвращает вычисленный результат.

### `fan\core\block\base::_makeBlockException`

- Расположение: `_core/block/base.php:941`
- Сигнатура: `function _makeBlockException(string $logErrMsg, string $type = 'local', ?string $exceptionDbOper = null, $code = E_USER_NOTICE, ?\Exception $previous = null)`
- Описание: Выполняет workflow-логику `make block exception`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\block\base::_parseClassName`

- Расположение: `_core/block/base.php:961`
- Сигнатура: `function _parseClassName(string $blockPath, bool $allowException = true)`
- Описание: Выполняет логику `parse class name` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\block\base::_setCacheRole`

- Расположение: `_core/block/base.php:977`
- Сигнатура: `function _setCacheRole(mixed $role)`
- Описание: Выполняет логику `set cache role` и возвращает вычисленный результат.

### `fan\core\block\base::_callOrdinaryDelegate`

- Расположение: `_core/block/base.php:1007`
- Сигнатура: `function _callOrdinaryDelegate($object, $method, $args)`
- Описание: Выполняет логику `call ordinary delegate` и возвращает вычисленный результат.

### `fan\core\block\base::_callIdentifiedDelegate`

- Расположение: `_core/block/base.php:1021`
- Сигнатура: `function _callIdentifiedDelegate($object, $method, array $args)`
- Описание: Выполняет логику `call identified delegate` и возвращает вычисленный результат.

### `fan\core\block\base::__get`

- Расположение: `_core/block/base.php:1039`
- Сигнатура: `function __get($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\block\base`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\block\base::__call`

- Расположение: `_core/block/base.php:1056`
- Сигнатура: `function __call($method, $args)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\block\base`.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения

## `_core/block/common/html_nav.php`

### `fan\core\block\common\html_nav::init`

- Расположение: `_core/block/common/html_nav.php:33`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\core\block\common\html_nav`.

### `fan\core\block\common\html_nav::_getNav`

- Расположение: `_core/block/common/html_nav.php:45`
- Сигнатура: `function _getNav($key = 'nav')`
- Описание: Выполняет логику `get nav` и возвращает вычисленный результат.

### `fan\core\block\common\html_nav::_parseNav`

- Расположение: `_core/block/common/html_nav.php:57`
- Сигнатура: `function _parseNav(array $nav)`
- Описание: Выполняет логику `parse nav` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\common\html_nav::_getNavURI`

- Расположение: `_core/block/common/html_nav.php:82`
- Сигнатура: `function _getNavURI(string $url, string $type = 'local', ?string $protocol = null)`
- Описание: Выполняет логику `get nav u r i` и возвращает вычисленный результат.

### `fan\core\block\common\html_nav::_checkCurrentElement`

- Расположение: `_core/block/common/html_nav.php:100`
- Сигнатура: `function _checkCurrentElement(string $key)`
- Описание: Выполняет логику `check current element` и возвращает вычисленный результат.

### `fan\core\block\common\html_nav::_getCurrentRequest`

- Расположение: `_core/block/common/html_nav.php:130`
- Сигнатура: `function _getCurrentRequest(bool $force = false)`
- Описание: Выполняет логику `get current request` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_core/block/common/html_nav_db.php`

### `fan\core\block\common\html_nav_db::_getNav`

- Расположение: `_core/block/common/html_nav_db.php:40`
- Сигнатура: `function _getNav($groupKey = null)`
- Описание: Выполняет логику `get nav` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\common\html_nav_db::_getNavName`

- Расположение: `_core/block/common/html_nav_db.php:89`
- Сигнатура: `function _getNavName(string $key = 'group_key')`
- Описание: Выполняет логику `get nav name` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\common\html_nav_db::_getNavRow`

- Расположение: `_core/block/common/html_nav_db.php:108`
- Сигнатура: `function _getNavRow(int|float $id)`
- Описание: Выполняет логику `get nav row` и возвращает вычисленный результат.

## `_core/block/common/html_pager.php`

### `fan\core\block\common\html_pager::init`

- Расположение: `_core/block/common/html_pager.php:32`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\core\block\common\html_pager`.

### `fan\core\block\common\html_pager::getPageUri`

- Расположение: `_core/block/common/html_pager.php:44`
- Сигнатура: `function getPageUri($page)`
- Описание: Получает, читает или вычисляет данные `page uri` в рамках этого метода класса `fan\core\block\common\html_pager`.

### `fan\core\block\common\html_pager::getEmbeddedForm`

- Расположение: `_core/block/common/html_pager.php:54`
- Сигнатура: `function getEmbeddedForm()`
- Описание: Получает, читает или вычисляет данные `embedded form` в рамках этого метода класса `fan\core\block\common\html_pager`.

### `fan\core\block\common\html_pager::_getPageGroup`

- Расположение: `_core/block/common/html_pager.php:67`
- Сигнатура: `function _getPageGroup($pageQtt, $curPage)`
- Описание: Выполняет логику `get page group` и возвращает вычисленный результат.

### `fan\core\block\common\html_pager::_postCreate`

- Расположение: `_core/block/common/html_pager.php:110`
- Сигнатура: `function _postCreate()`
- Описание: Выполняет workflow-логику `post create`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\common\html_pager::_preOutput`

- Расположение: `_core/block/common/html_pager.php:121`
- Сигнатура: `function _preOutput()`
- Описание: Выполняет workflow-логику `pre output`.

## `_core/block/common/html_pager_quantifier.php`

### `fan\core\block\common\html_pager_quantifier::init`

- Расположение: `_core/block/common/html_pager_quantifier.php:26`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\core\block\common\html_pager_quantifier`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\common\html_pager_quantifier::onSubmit`

- Расположение: `_core/block/common/html_pager_quantifier.php:45`
- Сигнатура: `function onSubmit()`
- Описание: Выполняет workflow-логику `on submit`.

### `fan\core\block\common\html_pager_quantifier::getDynamicMeta`

- Расположение: `_core/block/common/html_pager_quantifier.php:57`
- Сигнатура: `function getDynamicMeta($meta)`
- Описание: Получает, читает или вычисляет данные `dynamic meta` в рамках этого метода класса `fan\core\block\common\html_pager_quantifier`.

### `fan\core\block\common\html_pager_quantifier::getParentMeta`

- Расположение: `_core/block/common/html_pager_quantifier.php:90`
- Сигнатура: `function getParentMeta()`
- Описание: Получает, читает или вычисляет данные `parent meta` в рамках этого метода класса `fan\core\block\common\html_pager_quantifier`.

## `_core/block/error/error403.php`

### `fan\core\block\error\error403::setViewVars`

- Расположение: `_core/block/error/error403.php:31`
- Сигнатура: `function setViewVars(string $error, string $message, string $combiMessage)`
- Описание: Устанавливает, добавляет или сохраняет данные `view vars` в рамках этого метода класса `fan\core\block\error\error403`.

## `_core/block/error/error404.php`

### `fan\core\block\error\error404::setViewVars`

- Расположение: `_core/block/error/error404.php:31`
- Сигнатура: `function setViewVars(string $error, string $message, string $combiMessage)`
- Описание: Устанавливает, добавляет или сохраняет данные `view vars` в рамках этого метода класса `fan\core\block\error\error404`.

## `_core/block/error/error500.php`

### `fan\core\block\error\error500::setViewVars`

- Расположение: `_core/block/error/error500.php:31`
- Сигнатура: `function setViewVars(string $error, string $message, string $combiMessage)`
- Описание: Устанавливает, добавляет или сохраняет данные `view vars` в рамках этого метода класса `fan\core\block\error\error500`.

## `_core/block/form/parser.php`

### `fan\core\block\form\parser::finishConstruct`

- Расположение: `_core/block/form/parser.php:56`
- Сигнатура: `function finishConstruct($container = null, $containerMeta = [], $allowSetEmbedded = true)`
- Описание: Запускает или обрабатывает workflow `construct` для этого метода класса `fan\core\block\form\parser`.

### `fan\core\block\form\parser::checkFormRole`

- Расположение: `_core/block/form/parser.php:70`
- Сигнатура: `function checkFormRole()`
- Описание: Проверяет условие или валидирует данные `form role` и возвращает результат либо выбрасывает исключение.

### `fan\core\block\form\parser::getForm`

- Расположение: `_core/block/form/parser.php:80`
- Сигнатура: `function getForm()`
- Описание: Получает, читает или вычисляет данные `form` в рамках этого метода класса `fan\core\block\form\parser`.

### `fan\core\block\form\parser::getRoleName`

- Расположение: `_core/block/form/parser.php:95`
- Сигнатура: `function getRoleName()`
- Описание: Получает, читает или вычисляет данные `role name` в рамках этого метода класса `fan\core\block\form\parser`.

### `fan\core\block\form\parser::getFormMeta`

- Расположение: `_core/block/form/parser.php:108`
- Сигнатура: `function getFormMeta(?string $key = null, mixed $default = null)`
- Описание: Получает, читает или вычисляет данные `form meta` в рамках этого метода класса `fan\core\block\form\parser`.

### `fan\core\block\form\parser::getFieldsMeta`

- Расположение: `_core/block/form/parser.php:122`
- Сигнатура: `function getFieldsMeta()`
- Описание: Получает, читает или вычисляет данные `fields meta` в рамках этого метода класса `fan\core\block\form\parser`.

### `fan\core\block\form\parser::onSubmitEvent`

- Расположение: `_core/block/form/parser.php:134`
- Сигнатура: `function onSubmitEvent($block)`
- Описание: Выполняет workflow-логику `on submit event`.

### `fan\core\block\form\parser::onErrorEvent`

- Расположение: `_core/block/form/parser.php:154`
- Сигнатура: `function onErrorEvent($block)`
- Описание: Выполняет workflow-логику `on error event`.

### `fan\core\block\form\parser::checkBeforeValidation`

- Расположение: `_core/block/form/parser.php:169`
- Сигнатура: `function checkBeforeValidation()`
- Описание: Проверяет условие или валидирует данные `before validation` и возвращает результат либо выбрасывает исключение.

### `fan\core\block\form\parser::checkAfterValidation`

- Расположение: `_core/block/form/parser.php:179`
- Сигнатура: `function checkAfterValidation()`
- Описание: Проверяет условие или валидирует данные `after validation` и возвращает результат либо выбрасывает исключение.

### `fan\core\block\form\parser::onSubmit`

- Расположение: `_core/block/form/parser.php:189`
- Сигнатура: `function onSubmit()`
- Описание: Выполняет workflow-логику `on submit`.

### `fan\core\block\form\parser::onError`

- Расположение: `_core/block/form/parser.php:198`
- Сигнатура: `function onError()`
- Описание: Выполняет workflow-логику `on error`.

### `fan\core\block\form\parser::_doRoleOperations`

- Расположение: `_core/block/form/parser.php:210`
- Сигнатура: `function _doRoleOperations()`
- Описание: Выполняет workflow-логику `do role operations`.

### `fan\core\block\form\parser::_redefineFieldMeta`

- Расположение: `_core/block/form/parser.php:230`
- Сигнатура: `function _redefineFieldMeta()`
- Описание: Выполняет workflow-логику `redefine field meta`.

### `fan\core\block\form\parser::_correctFieldMeta`

- Расположение: `_core/block/form/parser.php:239`
- Сигнатура: `function _correctFieldMeta()`
- Описание: Выполняет workflow-логику `correct field meta`.

### `fan\core\block\form\parser::_parseForm`

- Расположение: `_core/block/form/parser.php:276`
- Сигнатура: `function _parseForm($parceEmpty = true, $parsingCondition = null, $allowTransfer = null)`
- Описание: Выполняет логику `parse form` и возвращает вычисленный результат.

### `fan\core\block\form\parser::_initFormParts`

- Расположение: `_core/block/form/parser.php:290`
- Сигнатура: `function _initFormParts(?\fan\core\block\form\parser $mainFormBlock = NULL)`
- Описание: Выполняет логику `init form parts` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

## `_core/block/form/part.php`

### `fan\core\block\form\part::partInit`

- Расположение: `_core/block/form/part.php:30`
- Сигнатура: `function partInit(?\fan\core\block\form\parser $mainFormBlock = NULL)`
- Описание: Выполняет workflow-логику `part init`.

### `fan\core\block\form\part::_parseForm`

- Расположение: `_core/block/form/part.php:44`
- Сигнатура: `function _parseForm($parceEmpty = true, $parsingCondition = false, $allowTransfer = false, $showWarning = true)`
- Описание: Выполняет логику `parse form` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

## `_core/block/form/usual.php`

### `fan\core\block\form\usual::finishConstruct`

- Расположение: `_core/block/form/usual.php:38`
- Сигнатура: `function finishConstruct($container = null, $containerMeta = [], $allowSetEmbedded = true)`
- Описание: Запускает или обрабатывает workflow `construct` для этого метода класса `fan\core\block\form\usual`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\form\usual::getCachePermission`

- Расположение: `_core/block/form/usual.php:57`
- Сигнатура: `function getCachePermission()`
- Описание: Получает, читает или вычисляет данные `cache permission` в рамках этого метода класса `fan\core\block\form\usual`.
- Побочные эффекты: читает PHP superglobals

### `fan\core\block\form\usual::getViewData`

- Расположение: `_core/block/form/usual.php:74`
- Сигнатура: `function getViewData()`
- Описание: Получает, читает или вычисляет данные `view data` в рамках этого метода класса `fan\core\block\form\usual`.

## `_core/block/loader/base.php`

### `fan\core\block\loader\base::finishConstruct`

- Расположение: `_core/block/loader/base.php:45`
- Сигнатура: `function finishConstruct($container = null, $containerMeta = [], $allowSetEmbedded = true)`
- Описание: Запускает или обрабатывает workflow `construct` для этого метода класса `fan\core\block\loader\base`.

### `fan\core\block\loader\base::getData`

- Расположение: `_core/block/loader/base.php:66`
- Сигнатура: `function getData()`
- Описание: Получает, читает или вычисляет данные `data` в рамках этого метода класса `fan\core\block\loader\base`.

### `fan\core\block\loader\base::getDataLoader`

- Расположение: `_core/block/loader/base.php:83`
- Сигнатура: `function getDataLoader()`
- Описание: Получает, читает или вычисляет данные `data loader` в рамках этого метода класса `fan\core\block\loader\base`.

### `fan\core\block\loader\base::setJson`

- Расположение: `_core/block/loader/base.php:100`
- Сигнатура: `function setJson($json, $merge = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `json` в рамках этого метода класса `fan\core\block\loader\base`.

### `fan\core\block\loader\base::setHtml`

- Расположение: `_core/block/loader/base.php:118`
- Сигнатура: `function setHtml($html, $merge = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `html` в рамках этого метода класса `fan\core\block\loader\base`.

### `fan\core\block\loader\base::setText`

- Расположение: `_core/block/loader/base.php:132`
- Сигнатура: `function setText(string $text, $merge = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `text` в рамках этого метода класса `fan\core\block\loader\base`.

### `fan\core\block\loader\base::checkRunInit`

- Расположение: `_core/block/loader/base.php:143`
- Сигнатура: `function checkRunInit()`
- Описание: Проверяет условие или валидирует данные `run init` и возвращает результат либо выбрасывает исключение.

### `fan\core\block\loader\base::getOutcome`

- Расположение: `_core/block/loader/base.php:153`
- Сигнатура: `function getOutcome()`
- Описание: Получает, читает или вычисляет данные `outcome` в рамках этого метода класса `fan\core\block\loader\base`.

## `_core/block/loader/loader_form_validation.php`

### `fan\core\block\loader\loader_form_validation::init`

- Расположение: `_core/block/loader/loader_form_validation.php:28`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\core\block\loader\loader_form_validation`.
- Побочные эффекты: может выбрасывать исключения

## `_core/block/root/html.php`

### `fan\core\block\root\html::init`

- Расположение: `_core/block/root/html.php:79`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\core\block\root\html`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\root\html::runAfterInit`

- Расположение: `_core/block/root/html.php:105`
- Сигнатура: `function runAfterInit()`
- Описание: Запускает или обрабатывает workflow `after init` для этого метода класса `fan\core\block\root\html`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\root\html::setTitle`

- Расположение: `_core/block/root/html.php:138`
- Сигнатура: `function setTitle(string $title, bool $checkIsSet = false)`
- Описание: Устанавливает, добавляет или сохраняет данные `title` в рамках этого метода класса `fan\core\block\root\html`.

### `fan\core\block\root\html::getTitle`

- Расположение: `_core/block/root/html.php:151`
- Сигнатура: `function getTitle()`
- Описание: Получает, читает или вычисляет данные `title` в рамках этого метода класса `fan\core\block\root\html`.

### `fan\core\block\root\html::setMetaTag`

- Расположение: `_core/block/root/html.php:163`
- Сигнатура: `function setMetaTag(mixed $meta)`
- Описание: Устанавливает, добавляет или сохраняет данные `meta tag` в рамках этого метода класса `fan\core\block\root\html`.

### `fan\core\block\root\html::getMetaTag`

- Расположение: `_core/block/root/html.php:188`
- Сигнатура: `function getMetaTag()`
- Описание: Получает, читает или вычисляет данные `meta tag` в рамках этого метода класса `fan\core\block\root\html`.

### `fan\core\block\root\html::setLinkTag`

- Расположение: `_core/block/root/html.php:203`
- Сигнатура: `function setLinkTag(string $rel, string $type, string $href, string $title = '')`
- Описание: Устанавливает, добавляет или сохраняет данные `link tag` в рамках этого метода класса `fan\core\block\root\html`.

### `fan\core\block\root\html::setExternalCss`

- Расположение: `_core/block/root/html.php:223`
- Сигнатура: `function setExternalCss(mixed $cssFile, string $type = 'style')`
- Описание: Устанавливает, добавляет или сохраняет данные `external css` в рамках этого метода класса `fan\core\block\root\html`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\block\root\html::setEmbedCss`

- Расположение: `_core/block/root/html.php:266`
- Сигнатура: `function setEmbedCss(mixed $css, string $media = 'all')`
- Описание: Устанавливает, добавляет или сохраняет данные `embed css` в рамках этого метода класса `fan\core\block\root\html`.

### `fan\core\block\root\html::setEmbedCssByMeta`

- Расположение: `_core/block/root/html.php:298`
- Сигнатура: `function setEmbedCssByMeta(mixed $meta)`
- Описание: Устанавливает, добавляет или сохраняет данные `embed css by meta` в рамках этого метода класса `fan\core\block\root\html`.

### `fan\core\block\root\html::setExternalJs`

- Расположение: `_core/block/root/html.php:319`
- Сигнатура: `function setExternalJs(mixed $jsFile, string $pos = 'head')`
- Описание: Устанавливает, добавляет или сохраняет данные `external js` в рамках этого метода класса `fan\core\block\root\html`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\block\root\html::setEmbedJs`

- Расположение: `_core/block/root/html.php:352`
- Сигнатура: `function setEmbedJs(mixed $js, string $pos = 'head', int $ord = 0, $allowDebug = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `embed js` в рамках этого метода класса `fan\core\block\root\html`.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения

### `fan\core\block\root\html::setHeadBefore`

- Расположение: `_core/block/root/html.php:394`
- Сигнатура: `function setHeadBefore(string $htmlCode)`
- Описание: Устанавливает, добавляет или сохраняет данные `head before` в рамках этого метода класса `fan\core\block\root\html`.

### `fan\core\block\root\html::setHeadAfter`

- Расположение: `_core/block/root/html.php:408`
- Сигнатура: `function setHeadAfter(string $htmlCode)`
- Описание: Устанавливает, добавляет или сохраняет данные `head after` в рамках этого метода класса `fan\core\block\root\html`.

### `fan\core\block\root\html::setModalWindow`

- Расположение: `_core/block/root/html.php:425`
- Сигнатура: `function setModalWindow(string $filePath, array $tplVars = [], $cssFile = '/css/modal_win.css', $jsFile = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `modal window` в рамках этого метода класса `fan\core\block\root\html`.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения

### `fan\core\block\root\html::isExtCSS`

- Расположение: `_core/block/root/html.php:461`
- Сигнатура: `function isExtCSS(string $key)`
- Описание: Проверяет условие или валидирует данные `ext c s s` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\block\root\html::_addJsFile`

- Расположение: `_core/block/root/html.php:486`
- Сигнатура: `function _addJsFile(string $uri, string $type)`
- Описание: Выполняет логику `add js file` и возвращает вычисленный результат.

### `fan\core\block\root\html::_preOutput`

- Расположение: `_core/block/root/html.php:509`
- Сигнатура: `function _preOutput()`
- Описание: Выполняет workflow-логику `pre output`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\block\root\html::_compareArray`

- Расположение: `_core/block/root/html.php:532`
- Сигнатура: `function _compareArray(array $array1, array $array2, array $keys)`
- Описание: Выполняет логику `compare array` и возвращает вычисленный результат.

## `_core/bootstrap.php`

### `bootstrap::init`

- Расположение: `_core/bootstrap.php:83`
- Сигнатура: `function init(?string $iniPath = null)`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `bootstrap`.
- Побочные эффекты: читает PHP superglobals

### `bootstrap::run`

- Расположение: `_core/bootstrap.php:150`
- Сигнатура: `function run(?string $iniPath = null, bool $isEcho = true)`
- Описание: Запускает или обрабатывает workflow `выполнение` для этого метода класса `bootstrap`.

### `bootstrap::runCli`

- Расположение: `_core/bootstrap.php:164`
- Сигнатура: `function runCli(string $className, string $methodName = 'init')`
- Описание: Запускает или обрабатывает workflow `cli` для этого метода класса `bootstrap`.

### `bootstrap::getInitializer`

- Расположение: `_core/bootstrap.php:178`
- Сигнатура: `function getInitializer()`
- Описание: Получает, читает или вычисляет данные `initializer` в рамках этого метода класса `bootstrap`.

### `bootstrap::getLoader`

- Расположение: `_core/bootstrap.php:191`
- Сигнатура: `function getLoader()`
- Описание: Получает, читает или вычисляет данные `loader` в рамках этого метода класса `bootstrap`.

### `bootstrap::getRunner`

- Расположение: `_core/bootstrap.php:204`
- Сигнатура: `function getRunner()`
- Описание: Получает, читает или вычисляет данные `runner` в рамках этого метода класса `bootstrap`.

### `bootstrap::getConfigCache`

- Расположение: `_core/bootstrap.php:217`
- Сигнатура: `function getConfigCache()`
- Описание: Получает, читает или вычисляет данные `config cache` в рамках этого метода класса `bootstrap`.

### `bootstrap::loadClass`

- Расположение: `_core/bootstrap.php:230`
- Сигнатура: `function loadClass(string $class, bool $makeAlias = true)`
- Описание: Получает, читает или вычисляет данные `class` в рамках этого метода класса `bootstrap`.

### `bootstrap::loadFile`

- Расположение: `_core/bootstrap.php:244`
- Сигнатура: `function loadFile(string $file, int $handleError = 0, int $way = 0)`
- Описание: Получает, читает или вычисляет данные `file` в рамках этого метода класса `bootstrap`.

### `bootstrap::parsePath`

- Расположение: `_core/bootstrap.php:256`
- Сигнатура: `function parsePath(string $path)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `path` для этого метода класса `bootstrap`.

### `bootstrap::getGlobalPath`

- Расположение: `_core/bootstrap.php:269`
- Сигнатура: `function getGlobalPath($key, $altPath = null)`
- Описание: Получает, читает или вычисляет данные `global path` в рамках этого метода класса `bootstrap`.

### `bootstrap::handleError`

- Расположение: `_core/bootstrap.php:287`
- Сигнатура: `function handleError(int|float $errNo, string $errMsg, ?string $fileName = null, int|float|null $lineNum = null, $errContext = null)`
- Описание: Запускает или обрабатывает workflow `error` для этого метода класса `bootstrap`.

### `bootstrap::logError`

- Расположение: `_core/bootstrap.php:302`
- Сигнатура: `function logError(string $message)`
- Описание: Выполняет логику `log error` и возвращает вычисленный результат.

### `bootstrap::getPid`

- Расположение: `_core/bootstrap.php:323`
- Сигнатура: `function getPid()`
- Описание: Получает, читает или вычисляет данные `pid` в рамках этого метода класса `bootstrap`.

### `bootstrap::isCli`

- Расположение: `_core/bootstrap.php:336`
- Сигнатура: `function isCli()`
- Описание: Проверяет условие или валидирует данные `cli` и возвращает результат либо выбрасывает исключение.

### `bootstrap::_setConfig`

- Расположение: `_core/bootstrap.php:348`
- Сигнатура: `function _setConfig(mixed $iniPath)`
- Описание: Выполняет workflow-логику `set config`.

### `bootstrap::_setErrorHandler`

- Расположение: `_core/bootstrap.php:370`
- Сигнатура: `function _setErrorHandler()`
- Описание: Выполняет workflow-логику `set error handler`.

### `bootstrap::_fillPlaceholder`

- Расположение: `_core/bootstrap.php:388`
- Сигнатура: `function _fillPlaceholder(string $path)`
- Описание: Выполняет логику `fill placeholder` и возвращает вычисленный результат.

### `bootstrap::_defineObj`

- Расположение: `_core/bootstrap.php:405`
- Сигнатура: `function _defineObj(string $key, string $class, string $path)`
- Описание: Выполняет логику `define obj` и возвращает вычисленный результат.

## `_core/bootstrap/initializer.php`

### `fan\core\bootstrap\initializer::__construct`

- Расположение: `_core/bootstrap/initializer.php:33`
- Сигнатура: `function __construct($config)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\bootstrap\initializer`.

### `fan\core\bootstrap\initializer::setConfig`

- Расположение: `_core/bootstrap/initializer.php:47`
- Сигнатура: `function setConfig(array $config)`
- Описание: Устанавливает, добавляет или сохраняет данные `config` в рамках этого метода класса `fan\core\bootstrap\initializer`.

### `fan\core\bootstrap\initializer::initBeforeLoader`

- Расположение: `_core/bootstrap/initializer.php:66`
- Сигнатура: `function initBeforeLoader()`
- Описание: Запускает или обрабатывает workflow `before loader` для этого метода класса `fan\core\bootstrap\initializer`.

### `fan\core\bootstrap\initializer::initAfterLoader`

- Расположение: `_core/bootstrap/initializer.php:79`
- Сигнатура: `function initAfterLoader()`
- Описание: Запускает или обрабатывает workflow `after loader` для этого метода класса `fan\core\bootstrap\initializer`.
- Побочные эффекты: читает PHP superglobals

### `fan\core\bootstrap\initializer::checkRequiredParam`

- Расположение: `_core/bootstrap/initializer.php:98`
- Сигнатура: `function checkRequiredParam()`
- Описание: Проверяет условие или валидирует данные `required param` и возвращает результат либо выбрасывает исключение.

### `fan\core\bootstrap\initializer::checkAdvisedParam`

- Расположение: `_core/bootstrap/initializer.php:108`
- Сигнатура: `function checkAdvisedParam()`
- Описание: Проверяет условие или валидирует данные `advised param` и возвращает результат либо выбрасывает исключение.

### `fan\core\bootstrap\initializer::setMainParam`

- Расположение: `_core/bootstrap/initializer.php:118`
- Сигнатура: `function setMainParam()`
- Описание: Устанавливает, добавляет или сохраняет данные `main param` в рамках этого метода класса `fan\core\bootstrap\initializer`.

### `fan\core\bootstrap\initializer::setAppParam`

- Расположение: `_core/bootstrap/initializer.php:132`
- Сигнатура: `function setAppParam(string $name)`
- Описание: Устанавливает, добавляет или сохраняет данные `app param` в рамках этого метода класса `fan\core\bootstrap\initializer`.

### `fan\core\bootstrap\initializer::setServiceParam`

- Расположение: `_core/bootstrap/initializer.php:144`
- Сигнатура: `function setServiceParam(string $name)`
- Описание: Устанавливает, добавляет или сохраняет данные `service param` в рамках этого метода класса `fan\core\bootstrap\initializer`.

### `fan\core\bootstrap\initializer::_checkPhpConf`

- Расположение: `_core/bootstrap/initializer.php:157`
- Сигнатура: `function _checkPhpConf(string $type, bool $setErr = false)`
- Описание: Выполняет workflow-логику `check php conf`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\bootstrap\initializer::_setPhpConf`

- Расположение: `_core/bootstrap/initializer.php:183`
- Сигнатура: `function _setPhpConf(string $type, string $name)`
- Описание: Выполняет логику `set php conf` и возвращает вычисленный результат.

## `_core/bootstrap/loader.php`

### `fan\core\bootstrap\loader::__construct`

- Расположение: `_core/bootstrap/loader.php:97`
- Сигнатура: `function __construct($config)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\bootstrap\loader`.

### `fan\core\bootstrap\loader::loadFile`

- Расположение: `_core/bootstrap/loader.php:133`
- Сигнатура: `function loadFile(string $path, int $handleError = 0, int $way = 0)`
- Описание: Получает, читает или вычисляет данные `file` в рамках этого метода класса `fan\core\bootstrap\loader`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\bootstrap\loader::registerAutoload`

- Расположение: `_core/bootstrap/loader.php:172`
- Сигнатура: `function registerAutoload(mixed $function, bool $prepend = false)`
- Описание: Выполняет workflow-логику `register autoload`.

### `fan\core\bootstrap\loader::unregisterAutoload`

- Расположение: `_core/bootstrap/loader.php:188`
- Сигнатура: `function unregisterAutoload(mixed $function)`
- Описание: Выполняет workflow-логику `unregister autoload`.

### `fan\core\bootstrap\loader::loadClass`

- Расположение: `_core/bootstrap/loader.php:201`
- Сигнатура: `function loadClass(string $class, bool $makeAlias = true)`
- Описание: Получает, читает или вычисляет данные `class` в рамках этого метода класса `fan\core\bootstrap\loader`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\bootstrap\loader::loadBlockByMR`

- Расположение: `_core/bootstrap/loader.php:259`
- Сигнатура: `function loadBlockByMR(string $appName, array $mainRequest)`
- Описание: Получает, читает или вычисляет данные `block by m r` в рамках этого метода класса `fan\core\bootstrap\loader`.

### `fan\core\bootstrap\loader::loadBlockByClass`

- Расположение: `_core/bootstrap/loader.php:280`
- Сигнатура: `function loadBlockByClass(string $class)`
- Описание: Получает, читает или вычисляет данные `block by class` в рамках этого метода класса `fan\core\bootstrap\loader`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\bootstrap\loader::loadBlockByPath`

- Расположение: `_core/bootstrap/loader.php:315`
- Сигнатура: `function loadBlockByPath($srcPath)`
- Описание: Получает, читает или вычисляет данные `block by path` в рамках этого метода класса `fan\core\bootstrap\loader`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\bootstrap\loader::getPathByNS`

- Расположение: `_core/bootstrap/loader.php:349`
- Сигнатура: `function getPathByNS(string $ns, bool $fullPath = true)`
- Описание: Получает, читает или вычисляет данные `path by n s` в рамках этого метода класса `fan\core\bootstrap\loader`.

### `fan\core\bootstrap\loader::parsePath`

- Расположение: `_core/bootstrap/loader.php:372`
- Сигнатура: `function parsePath(string $path)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `path` для этого метода класса `fan\core\bootstrap\loader`.

### `fan\core\bootstrap\loader::checkPath`

- Расположение: `_core/bootstrap/loader.php:392`
- Сигнатура: `function checkPath(string $path)`
- Описание: Проверяет условие или валидирует данные `path` и возвращает результат либо выбрасывает исключение.

### `fan\core\bootstrap\loader::getRealPath`

- Расположение: `_core/bootstrap/loader.php:406`
- Сигнатура: `function getRealPath(string $path, bool $isFile = true)`
- Описание: Получает, читает или вычисляет данные `real path` в рамках этого метода класса `fan\core\bootstrap\loader`.

### `fan\core\bootstrap\loader::defineNewApp`

- Расположение: `_core/bootstrap/loader.php:420`
- Сигнатура: `function defineNewApp(\fan\core\service\application $app)`
- Описание: Выполняет логику `define new app` и возвращает вычисленный результат.

### `fan\core\bootstrap\loader::isLoading`

- Расположение: `_core/bootstrap/loader.php:451`
- Сигнатура: `function isLoading()`
- Описание: Проверяет условие или валидирует данные `loading` и возвращает результат либо выбрасывает исключение.

### `fan\core\bootstrap\loader::registerZend2`

- Расположение: `_core/bootstrap/loader.php:464`
- Сигнатура: `function registerZend2(mixed $zendPath = null, bool $prepend = false)`
- Описание: Выполняет workflow-логику `register zend2`.

### `fan\core\bootstrap\loader::_setAppDir`

- Расположение: `_core/bootstrap/loader.php:483`
- Сигнатура: `function _setAppDir()`
- Описание: Выполняет логику `set app dir` и возвращает вычисленный результат.

### `fan\core\bootstrap\loader::_setModelDir`

- Расположение: `_core/bootstrap/loader.php:495`
- Сигнатура: `function _setModelDir()`
- Описание: Выполняет логику `set model dir` и возвращает вычисленный результат.

### `fan\core\bootstrap\loader::_setTemporaryDir`

- Расположение: `_core/bootstrap/loader.php:507`
- Сигнатура: `function _setTemporaryDir()`
- Описание: Выполняет логику `set temporary dir` и возвращает вычисленный результат.

### `fan\core\bootstrap\loader::_setBasicLoader`

- Расположение: `_core/bootstrap/loader.php:519`
- Сигнатура: `function _setBasicLoader()`
- Описание: Выполняет логику `set basic loader` и возвращает вычисленный результат.

### `fan\core\bootstrap\loader::_setAdditionalLoader`

- Расположение: `_core/bootstrap/loader.php:530`
- Сигнатура: `function _setAdditionalLoader()`
- Описание: Выполняет логику `set additional loader` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\bootstrap\loader::_getMixedKeys`

- Расположение: `_core/bootstrap/loader.php:549`
- Сигнатура: `function _getMixedKeys()`
- Описание: Выполняет логику `get mixed keys` и возвращает вычисленный результат.

### `fan\core\bootstrap\loader::_findBlock`

- Расположение: `_core/bootstrap/loader.php:562`
- Сигнатура: `function _findBlock(string $dir, string $file)`
- Описание: Выполняет логику `find block` и возвращает вычисленный результат.

### `fan\core\bootstrap\loader::_requireFile`

- Расположение: `_core/bootstrap/loader.php:589`
- Сигнатура: `function _requireFile(string $path)`
- Описание: Выполняет логику `require file` и возвращает вычисленный результат.

### `fan\core\bootstrap\loader::__set`

- Расположение: `_core/bootstrap/loader.php:607`
- Сигнатура: `function __set($key, $value)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\bootstrap\loader`.

### `fan\core\bootstrap\loader::__get`

- Расположение: `_core/bootstrap/loader.php:619`
- Сигнатура: `function __get($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\bootstrap\loader`.

### `fan\core\bootstrap\loader::offsetSet`

- Расположение: `_core/bootstrap/loader.php:634`
- Сигнатура: `function offsetSet($key, mixed $value)`
- Описание: Выполняет workflow-логику `offset set`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\bootstrap\loader::offsetExists`

- Расположение: `_core/bootstrap/loader.php:646`
- Сигнатура: `function offsetExists($key)`
- Описание: Выполняет логику `offset exists` и возвращает вычисленный результат.

### `fan\core\bootstrap\loader::offsetUnset`

- Расположение: `_core/bootstrap/loader.php:659`
- Сигнатура: `function offsetUnset($key)`
- Описание: Выполняет workflow-логику `offset unset`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\bootstrap\loader::offsetGet`

- Расположение: `_core/bootstrap/loader.php:671`
- Сигнатура: `function offsetGet($key)`
- Описание: Выполняет логику `offset get` и возвращает вычисленный результат.

## `_core/bootstrap/runner.php`

### `fan\core\bootstrap\runner::__construct`

- Расположение: `_core/bootstrap/runner.php:36`
- Сигнатура: `function __construct($config)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\bootstrap\runner`.

### `fan\core\bootstrap\runner::run`

- Расположение: `_core/bootstrap/runner.php:50`
- Сигнатура: `function run(bool $isEcho = true, string|array|null $procedure = null, array $parameters = [])`
- Описание: Запускает или обрабатывает workflow `выполнение` для этого метода класса `fan\core\bootstrap\runner`.
- Побочные эффекты: меняет HTTP/session состояние; использует service locator/helpers фреймворка

### `fan\core\bootstrap\runner::runCli`

- Расположение: `_core/bootstrap/runner.php:89`
- Сигнатура: `function runCli($className, $methodName)`
- Описание: Запускает или обрабатывает workflow `cli` для этого метода класса `fan\core\bootstrap\runner`.

### `fan\core\bootstrap\runner::getHandler`

- Расположение: `_core/bootstrap/runner.php:118`
- Сигнатура: `function getHandler()`
- Описание: Получает, читает или вычисляет данные `handler` в рамках этого метода класса `fan\core\bootstrap\runner`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\bootstrap\runner::handleOb`

- Расположение: `_core/bootstrap/runner.php:131`
- Сигнатура: `function handleOb(string $message)`
- Описание: Запускает или обрабатывает workflow `ob` для этого метода класса `fan\core\bootstrap\runner`.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках

### `fan\core\bootstrap\runner::showError`

- Расположение: `_core/bootstrap/runner.php:165`
- Сигнатура: `function showError(mixed $errMsg, string $tplName = 'error_500', bool $isEcho = true)`
- Описание: Выполняет логику `show error` и возвращает вычисленный результат.
- Побочные эффекты: читает PHP superglobals

### `fan\core\bootstrap\runner::_logException`

- Расположение: `_core/bootstrap/runner.php:208`
- Сигнатура: `function _logException(\Exception $e)`
- Описание: Выполняет логику `log exception` и возвращает вычисленный результат.

### `fan\core\bootstrap\runner::_showExceptionError`

- Расположение: `_core/bootstrap/runner.php:236`
- Сигнатура: `function _showExceptionError(\Exception $e, bool $isEcho)`
- Описание: Выполняет workflow-логику `show exception error`.

## `_core/cli/restore_password.php`

### `fan\core\cli\restore_password::init`

- Расположение: `_core/cli/restore_password.php:32`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\core\cli\restore_password`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\cli\restore_password::parceData`

- Расположение: `_core/cli/restore_password.php:60`
- Сигнатура: `function parceData(string $src)`
- Описание: Выполняет логику `parce data` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\cli\restore_password::unserializeLogRow`

- Расположение: `_core/cli/restore_password.php:97`
- Сигнатура: `function unserializeLogRow(string $src): mixed`
- Описание: Выполняет логику `unserialize log row` и возвращает вычисленный результат.
- Побочные эффекты: сериализует или десериализует данные

## `_core/di/container.php`

### `fan\core\di\container::set`

- Расположение: `_core/di/container.php:42`
- Сигнатура: `function set(string $id, object $service): self`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\di\container`.

### `fan\core\di\container::factory`

- Расположение: `_core/di/container.php:59`
- Сигнатура: `function factory(string $id, callable $factory, bool $shared = true): self`
- Описание: Выполняет логику `factory` и возвращает вычисленный результат.

### `fan\core\di\container::alias`

- Расположение: `_core/di/container.php:76`
- Сигнатура: `function alias(string $alias, string $id): self`
- Описание: Выполняет логику `alias` и возвращает вычисленный результат.

### `fan\core\di\container::has`

- Расположение: `_core/di/container.php:90`
- Сигнатура: `function has(string $id): bool`
- Описание: Проверяет условие или валидирует данные `has` и возвращает результат либо выбрасывает исключение.

### `fan\core\di\container::get`

- Расположение: `_core/di/container.php:105`
- Сигнатура: `function get(string $id, mixed ...$arguments): mixed`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\di\container`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\di\container::remove`

- Расположение: `_core/di/container.php:131`
- Сигнатура: `function remove(string $id): self`
- Описание: Удаляет или сбрасывает состояние `элемент` для этого метода класса `fan\core\di\container`.

### `fan\core\di\container::clear`

- Расположение: `_core/di/container.php:144`
- Сигнатура: `function clear(): self`
- Описание: Удаляет или сбрасывает состояние `clear` для этого метода класса `fan\core\di\container`.

### `fan\core\di\container::normalizeId`

- Расположение: `_core/di/container.php:161`
- Сигнатура: `function normalizeId(string $id): string`
- Описание: Выполняет логику `normalize id` и возвращает вычисленный результат.

### `fan\core\di\container::resolveAlias`

- Расположение: `_core/di/container.php:173`
- Сигнатура: `function resolveAlias(string $id): string`
- Описание: Выполняет логику `resolve alias` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

## `_core/di/container_registry.php`

### `fan\core\di\container_registry::get`

- Расположение: `_core/di/container_registry.php:20`
- Сигнатура: `function get(): container`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\di\container_registry`.

### `fan\core\di\container_registry::set`

- Расположение: `_core/di/container_registry.php:36`
- Сигнатура: `function set(container $container): void`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\di\container_registry`.

### `fan\core\di\container_registry::reset`

- Расположение: `_core/di/container_registry.php:46`
- Сигнатура: `function reset(): void`
- Описание: Удаляет или сбрасывает состояние `состояние` для этого метода класса `fan\core\di\container_registry`.

## `_core/error/demonstrator.php`

### `fan\core\error\demonstrator::__construct`

- Расположение: `_core/error/demonstrator.php:87`
- Сигнатура: `function __construct($tplVars = [], $tplName = 'error_500')`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\error\demonstrator`.

### `fan\core\error\demonstrator::setTplVars`

- Расположение: `_core/error/demonstrator.php:101`
- Сигнатура: `function setTplVars(mixed $tplVars)`
- Описание: Устанавливает, добавляет или сохраняет данные `tpl vars` в рамках этого метода класса `fan\core\error\demonstrator`.

### `fan\core\error\demonstrator::getTplVar`

- Расположение: `_core/error/demonstrator.php:117`
- Сигнатура: `function getTplVar(?string $key = null)`
- Описание: Получает, читает или вычисляет данные `tpl var` в рамках этого метода класса `fan\core\error\demonstrator`.

### `fan\core\error\demonstrator::setTplName`

- Расположение: `_core/error/demonstrator.php:129`
- Сигнатура: `function setTplName(mixed $tplFile = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `tpl name` в рамках этого метода класса `fan\core\error\demonstrator`.

### `fan\core\error\demonstrator::setResponseHeader`

- Расположение: `_core/error/demonstrator.php:160`
- Сигнатура: `function setResponseHeader($code)`
- Описание: Устанавливает, добавляет или сохраняет данные `response header` в рамках этого метода класса `fan\core\error\demonstrator`.

### `fan\core\error\demonstrator::setContentType`

- Расположение: `_core/error/demonstrator.php:176`
- Сигнатура: `function setContentType($type)`
- Описание: Устанавливает, добавляет или сохраняет данные `content type` в рамках этого метода класса `fan\core\error\demonstrator`.
- Побочные эффекты: читает PHP superglobals

### `fan\core\error\demonstrator::setOptionalHeader`

- Расположение: `_core/error/demonstrator.php:214`
- Сигнатура: `function setOptionalHeader($header)`
- Описание: Устанавливает, добавляет или сохраняет данные `optional header` в рамках этого метода класса `fan\core\error\demonstrator`.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\error\demonstrator::outputHeaders`

- Расположение: `_core/error/demonstrator.php:227`
- Сигнатура: `function outputHeaders()`
- Описание: Выполняет workflow-логику `output headers`.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\error\demonstrator::getTplContent`

- Расположение: `_core/error/demonstrator.php:248`
- Сигнатура: `function getTplContent()`
- Описание: Получает, читает или вычисляет данные `tpl content` в рамках этого метода класса `fan\core\error\demonstrator`.

### `fan\core\error\demonstrator::showTplContent`

- Расположение: `_core/error/demonstrator.php:267`
- Сигнатура: `function showTplContent()`
- Описание: Выполняет логику `show tpl content` и возвращает вычисленный результат.

### `fan\core\error\demonstrator::setDoctype`

- Расположение: `_core/error/demonstrator.php:280`
- Сигнатура: `function setDoctype()`
- Описание: Устанавливает, добавляет или сохраняет данные `doctype` в рамках этого метода класса `fan\core\error\demonstrator`.
- Побочные эффекты: читает PHP superglobals

### `fan\core\error\demonstrator::convArrayToSting`

- Расположение: `_core/error/demonstrator.php:301`
- Сигнатура: `function convArrayToSting(mixed $src, string $glue = "\n")`
- Описание: Выполняет логику `conv array to sting` и возвращает вычисленный результат.

## `_core/exception/base.php`

### `fan\core\exception\base::__construct`

- Расположение: `_core/exception/base.php:66`
- Сигнатура: `function __construct($logErrMsg, $code = E_USER_ERROR, $previous = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\exception\base`.

### `fan\core\exception\base::getErrorFile`

- Расположение: `_core/exception/base.php:93`
- Сигнатура: `function getErrorFile()`
- Описание: Получает, читает или вычисляет данные `error file` в рамках этого метода класса `fan\core\exception\base`.

### `fan\core\exception\base::getMessageForLog`

- Расположение: `_core/exception/base.php:103`
- Сигнатура: `function getMessageForLog()`
- Описание: Получает, читает или вычисляет данные `message for log` в рамках этого метода класса `fan\core\exception\base`.

### `fan\core\exception\base::getMessageForShow`

- Расположение: `_core/exception/base.php:113`
- Сигнатура: `function getMessageForShow()`
- Описание: Получает, читает или вычисляет данные `message for show` в рамках этого метода класса `fan\core\exception\base`.

### `fan\core\exception\base::getDbOper`

- Расположение: `_core/exception/base.php:123`
- Сигнатура: `function getDbOper()`
- Описание: Получает, читает или вычисляет данные `db oper` в рамках этого метода класса `fan\core\exception\base`.

### `fan\core\exception\base::getLogVars`

- Расположение: `_core/exception/base.php:133`
- Сигнатура: `function getLogVars()`
- Описание: Получает, читает или вычисляет данные `log vars` в рамках этого метода класса `fan\core\exception\base`.

### `fan\core\exception\base::_logByPhp`

- Расположение: `_core/exception/base.php:163`
- Сигнатура: `function _logByPhp(string $errMsg, bool $exceptPos = true)`
- Описание: Выполняет логику `log by php` и возвращает вычисленный результат.

### `fan\core\exception\base::_logByService`

- Расположение: `_core/exception/base.php:181`
- Сигнатура: `function _logByService(string $errMsg, string $errTitle = '', string $note = '')`
- Описание: Выполняет логику `log by service` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках; читает PHP superglobals

### `fan\core\exception\base::_defineDbOper`

- Расположение: `_core/exception/base.php:200`
- Сигнатура: `function _defineDbOper($dbOper = null)`
- Описание: Выполняет логику `define db oper` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

## `_core/exception/block/fatal.php`

### `fan\core\exception\block\fatal::__construct`

- Расположение: `_core/exception/block/fatal.php:29`
- Сигнатура: `function __construct(\fan\core\block\base $block, $logErrMsg, $code = E_USER_ERROR, $previous = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\exception\block\fatal`.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\exception\block\fatal::_defineDbOper`

- Расположение: `_core/exception/block/fatal.php:47`
- Сигнатура: `function _defineDbOper($dbOper = 'rollback')`
- Описание: Выполняет логику `define db oper` и возвращает вычисленный результат.

## `_core/exception/block/form_part.php`

### `fan\core\exception\block\form_part::__construct`

- Расположение: `_core/exception/block/form_part.php:40`
- Сигнатура: `function __construct(\fan\core\block\base $block, $errorMsg, $code = E_USER_WARNING, $previous = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\exception\block\form_part`.

### `fan\core\exception\block\form_part::getBlockName`

- Расположение: `_core/exception/block/form_part.php:52`
- Сигнатура: `function getBlockName()`
- Описание: Получает, читает или вычисляет данные `block name` в рамках этого метода класса `fan\core\exception\block\form_part`.

### `fan\core\exception\block\form_part::getErrorMessages`

- Расположение: `_core/exception/block/form_part.php:62`
- Сигнатура: `function getErrorMessages()`
- Описание: Получает, читает или вычисляет данные `error messages` в рамках этого метода класса `fan\core\exception\block\form_part`.

### `fan\core\exception\block\form_part::_defineDbOper`

- Расположение: `_core/exception/block/form_part.php:74`
- Сигнатура: `function _defineDbOper($dbOper = 'nothing')`
- Описание: Выполняет логику `define db oper` и возвращает вычисленный результат.

## `_core/exception/block/local.php`

### `fan\core\exception\block\local::__construct`

- Расположение: `_core/exception/block/local.php:35`
- Сигнатура: `function __construct(\fan\core\block\base $block, $logErrMsg, $code = E_USER_NOTICE, $previous = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\exception\block\local`.

### `fan\core\exception\block\local::getBlock`

- Расположение: `_core/exception/block/local.php:46`
- Сигнатура: `function getBlock()`
- Описание: Получает, читает или вычисляет данные `block` в рамках этого метода класса `fan\core\exception\block\local`.

### `fan\core\exception\block\local::_defineDbOper`

- Расположение: `_core/exception/block/local.php:58`
- Сигнатура: `function _defineDbOper($dbOper = null)`
- Описание: Выполняет логику `define db oper` и возвращает вычисленный результат.

## `_core/exception/error500.php`

### `fan\core\exception\error500::__construct`

- Расположение: `_core/exception/error500.php:28`
- Сигнатура: `function __construct($logErrMsg, $code = E_USER_ERROR, $previous = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\exception\error500`.
- Побочные эффекты: меняет HTTP/session состояние

## `_core/exception/fatal.php`

### `fan\core\exception\fatal::__construct`

- Расположение: `_core/exception/fatal.php:30`
- Сигнатура: `function __construct($logErrMsg, $showErrMsg = '', $errorFile = '', $code = E_USER_ERROR, $previous = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\exception\fatal`.
- Побочные эффекты: меняет HTTP/session состояние; читает PHP superglobals

## `_core/exception/model/entity/fatal.php`

### `fan\core\exception\model\entity\fatal::__construct`

- Расположение: `_core/exception/model/entity/fatal.php:35`
- Сигнатура: `function __construct(\fan\core\base\model\entity $entity, $logErrMsg, $code = E_USER_ERROR, $previous = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\exception\model\entity\fatal`.

### `fan\core\exception\model\entity\fatal::getEntity`

- Расположение: `_core/exception/model/entity/fatal.php:50`
- Сигнатура: `function getEntity()`
- Описание: Получает, читает или вычисляет данные `entity` в рамках этого метода класса `fan\core\exception\model\entity\fatal`.

### `fan\core\exception\model\entity\fatal::_defineDbOper`

- Расположение: `_core/exception/model/entity/fatal.php:62`
- Сигнатура: `function _defineDbOper($dbOper = 'rollback')`
- Описание: Выполняет логику `define db oper` и возвращает вычисленный результат.

## `_core/exception/model/reverse.php`

### `fan\core\exception\model\reverse::__construct`

- Расположение: `_core/exception/model/reverse.php:34`
- Сигнатура: `function __construct(\fan\core\base\model\entity $entity, $logErrMsg, $code = null, $previous = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\exception\model\reverse`.

### `fan\core\exception\model\reverse::getEntity`

- Расположение: `_core/exception/model/reverse.php:45`
- Сигнатура: `function getEntity()`
- Описание: Получает, читает или вычисляет данные `entity` в рамках этого метода класса `fan\core\exception\model\reverse`.

### `fan\core\exception\model\reverse::_defineDbOper`

- Расположение: `_core/exception/model/reverse.php:57`
- Сигнатура: `function _defineDbOper($dbOper = 'nothing')`
- Описание: Выполняет логику `define db oper` и возвращает вычисленный результат.

## `_core/exception/plain/fatal.php`

### `fan\core\exception\plain\fatal::__construct`

- Расположение: `_core/exception/plain/fatal.php:36`
- Сигнатура: `function __construct($controller, $logMessage, $code = E_USER_ERROR, $previous = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\exception\plain\fatal`.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\exception\plain\fatal::getController`

- Расположение: `_core/exception/plain/fatal.php:55`
- Сигнатура: `function getController()`
- Описание: Получает, читает или вычисляет данные `controller` в рамках этого метода класса `fan\core\exception\plain\fatal`.

## `_core/exception/service/database.php`

### `fan\core\exception\service\database::__construct`

- Расположение: `_core/exception/service/database.php:55`
- Сигнатура: `function __construct(\fan\core\service\database $database, $operCode, $operMessage, $errorCode, $errorMessage, $parsedSql)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\exception\service\database`.

### `fan\core\exception\service\database::getOperationCode`

- Расположение: `_core/exception/service/database.php:72`
- Сигнатура: `function getOperationCode()`
- Описание: Получает, читает или вычисляет данные `operation code` в рамках этого метода класса `fan\core\exception\service\database`.

### `fan\core\exception\service\database::getOperation`

- Расположение: `_core/exception/service/database.php:82`
- Сигнатура: `function getOperation()`
- Описание: Получает, читает или вычисляет данные `operation` в рамках этого метода класса `fan\core\exception\service\database`.

### `fan\core\exception\service\database::getErrorNum`

- Расположение: `_core/exception/service/database.php:92`
- Сигнатура: `function getErrorNum()`
- Описание: Получает, читает или вычисляет данные `error num` в рамках этого метода класса `fan\core\exception\service\database`.

### `fan\core\exception\service\database::getParsedSql`

- Расположение: `_core/exception/service/database.php:102`
- Сигнатура: `function getParsedSql()`
- Описание: Получает, читает или вычисляет данные `parsed sql` в рамках этого метода класса `fan\core\exception\service\database`.

### `fan\core\exception\service\database::_logErrorMessage`

- Расположение: `_core/exception/service/database.php:115`
- Сигнатура: `function _logErrorMessage($logType)`
- Описание: Выполняет логику `log error message` и возвращает вычисленный результат.
- Побочные эффекты: логирует или сообщает об ошибках

## `_core/exception/service/date.php`

### `fan\core\exception\service\date::__construct`

- Расположение: `_core/exception/service/date.php:29`
- Сигнатура: `function __construct($logErrMsg, $code = E_USER_ERROR, $previous = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\exception\service\date`.

## `_core/exception/service/fatal.php`

### `fan\core\exception\service\fatal::__construct`

- Расположение: `_core/exception/service/fatal.php:35`
- Сигнатура: `function __construct(\fan\core\base\service $service, $logErrMsg, $code = E_USER_ERROR, $previous = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\exception\service\fatal`.

### `fan\core\exception\service\fatal::getService`

- Расположение: `_core/exception/service/fatal.php:49`
- Сигнатура: `function getService()`
- Описание: Получает, читает или вычисляет данные `service` в рамках этого метода класса `fan\core\exception\service\fatal`.

### `fan\core\exception\service\fatal::_logErrorMessage`

- Расположение: `_core/exception/service/fatal.php:61`
- Сигнатура: `function _logErrorMessage($logType)`
- Описание: Выполняет логику `log error message` и возвращает вычисленный результат.

### `fan\core\exception\service\fatal::_defineDbOper`

- Расположение: `_core/exception/service/fatal.php:77`
- Сигнатура: `function _defineDbOper($dbOper = null)`
- Описание: Выполняет логику `define db oper` и возвращает вычисленный результат.

## `_core/exception/template/fatal.php`

### `fan\core\exception\template\fatal::__construct`

- Расположение: `_core/exception/template/fatal.php:28`
- Сигнатура: `function __construct($template, $logMessage, $code = E_USER_ERROR)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\exception\template\fatal`.
- Побочные эффекты: меняет HTTP/session состояние

## `_core/functions.php`

### `get_class_alt`

- Расположение: `_core/functions.php:34`
- Сигнатура: `function get_class_alt(mixed $object)`
- Описание: Retrieves class alt used by the framework helper.
- Параметры: mixed $object Input value for the object argument.
- Возвращает: mixed Returns the value produced by the operation.

### `get_class_name`

- Расположение: `_core/functions.php:46`
- Сигнатура: `function get_class_name(string|object $object)`
- Описание: Retrieves class name used by the framework helper.
- Параметры: string|object $object Input value for the object argument.
- Возвращает: mixed Returns the value produced by the operation.

### `get_ns_name`

- Расположение: `_core/functions.php:65`
- Сигнатура: `function get_ns_name(string|object $object, int $depth = 1)`
- Описание: Retrieves ns name used by the framework helper.
- Параметры: string|object $object Input value for the object argument.; int $depth Input value for the depth argument.
- Возвращает: mixed Returns the value produced by the operation.

### `is_array_alt`

- Расположение: `_core/functions.php:92`
- Сигнатура: `function is_array_alt(mixed $arr)`
- Описание: Evaluates array alt and reports the outcome.
- Параметры: mixed $arr Input value for the arr argument.
- Возвращает: mixed Indicates whether the requested condition is satisfied.

### `explode_alt`

- Расположение: `_core/functions.php:106`
- Сигнатура: `function explode_alt(string $delimiter, string $string, int $size)`
- Описание: Runs the explode alt operation and returns its result.
- Параметры: string $delimiter Input value for the delimiter argument.; string $string Input value for the string argument.; int $size Input value for the size argument.
- Возвращает: mixed Returns the value produced by the operation.

### `array_merge_recursive_alt`

- Расположение: `_core/functions.php:120`
- Сигнатура: `function array_merge_recursive_alt(mixed $arrFirst)`
- Описание: Runs the array merge recursive alt operation and returns its result.
- Параметры: mixed $arrFirst Input value for the arr first argument.
- Возвращает: mixed Returns the value produced by the operation.

### `array_val`

- Расположение: `_core/functions.php:153`
- Сигнатура: `function array_val(array|\ArrayAccess $arr, mixed $key, mixed $default = null)`
- Описание: Runs the array val operation and returns its result.
- Параметры: array|\ArrayAccess $arr Input value for the arr argument.; mixed $key Lookup key used to address the target value.; mixed $default Fallback value returned when no explicit value is available.
- Возвращает: mixed Returns the value produced by the operation.
- Побочные эффекты: может выбрасывать исключения

### `array_get_element`

- Расположение: `_core/functions.php:186`
- Сигнатура: `function &array_get_element(&$source, string|array $key, mixed $make = null)`
- Описание: Runs the array get element operation and returns its result.
- Параметры: mixed $source Input value for the source argument.; string|array $key Lookup key used to address the target value.; mixed $make Input value for the make argument.
- Возвращает: mixed Returns the value produced by the operation.

### `adduceToArray`

- Расположение: `_core/functions.php:255`
- Сигнатура: `function adduceToArray(mixed $src)`
- Описание: Applies uce to array to the framework helper.
- Параметры: mixed $src Input value for the src argument.
- Возвращает: mixed Returns the value produced by the operation.

### `increaseNum`

- Расположение: `_core/functions.php:281`
- Сигнатура: `function increaseNum(int|float $number, int|float $qtt = 2, bool $roundIt = true)`
- Описание: Runs the increase num operation and returns its result.
- Параметры: int|float $number Input value for the number argument.; int|float $qtt Input value for the qtt argument.; bool $roundIt Input value for the round it argument.
- Возвращает: mixed Returns the value produced by the operation.

### `decreaseNum`

- Расположение: `_core/functions.php:295`
- Сигнатура: `function decreaseNum(int|float $number, int|float $qtt = 2)`
- Описание: Runs the decrease num operation and returns its result.
- Параметры: int|float $number Input value for the number argument.; int|float $qtt Input value for the qtt argument.
- Возвращает: mixed Returns the value produced by the operation.

### `getCurBlockInfo`

- Расположение: `_core/functions.php:305`
- Сигнатура: `function getCurBlockInfo()`
- Описание: Retrieves cur block info used by the framework helper.
- Возвращает: mixed Returns the value produced by the operation.
- Побочные эффекты: использует service locator/helpers фреймворка

### `service`

- Расположение: `_core/functions.php:334`
- Сигнатура: `function service(string $serviceName, mixed $arguments = [])`
- Описание: Runs the service operation and returns its result.
- Параметры: string $serviceName Input value for the service name argument.; mixed $arguments Input value for the arguments argument.
- Возвращает: mixed Returns the value produced by the operation.

### `service_container`

- Расположение: `_core/functions.php:357`
- Сигнатура: `function service_container(?\fan\core\di\container $container = null): \fan\core\di\container`
- Описание: Runs the service container operation and returns its result.
- Параметры: ?\fan\core\di\container $container Input value for the container argument.
- Возвращает: \fan\core\di\container Returns the value produced by the operation.

### `reset_service_container`

- Расположение: `_core/functions.php:371`
- Сигнатура: `function reset_service_container(): void`
- Описание: Removes or resets service container managed by the framework helper.
- Возвращает: void No value is returned.

### `handleError`

- Расположение: `_core/functions.php:387`
- Сигнатура: `function handleError(int|float $errNo, string $errMsg, ?string $fileName = null, int|float|null $lineNum = null, ?array $errConText = null)`
- Описание: Runs the error workflow for the framework helper.
- Параметры: int|float $errNo Input value for the err no argument.; string $errMsg Input value for the err msg argument.; ?string $fileName Input value for the file name argument.; int|float|null $lineNum Input value for the line num argument.; ?array $errConText Input value for the err con text argument.
- Возвращает: mixed Returns the value produced by the operation.
- Побочные эффекты: использует service locator/helpers фреймворка

### `getUser`

- Расположение: `_core/functions.php:410`
- Сигнатура: `function getUser(mixed $identifyer = null, ?string $userSpace = null)`
- Описание: Retrieves user used by the framework helper.
- Параметры: mixed $identifyer Input value for the identifyer argument.; ?string $userSpace Input value for the user space argument.
- Возвращает: mixed Returns the value produced by the operation.

### `ge`

- Расположение: `_core/functions.php:426`
- Сигнатура: `function ge(string $entityName, $collection = 0, array $param = [])`
- Описание: Runs the ge operation and returns its result.
- Параметры: string $entityName Input value for the entity name argument.; mixed $collection Input value for the collection argument.; array $param Input value for the param argument.
- Возвращает: mixed Returns the value produced by the operation.
- Побочные эффекты: использует service locator/helpers фреймворка

### `gr`

- Расположение: `_core/functions.php:440`
- Сигнатура: `function gr(string $entityName, mixed $rowId = null, bool $idIsEncrypt = false, array $param = [])`
- Описание: Runs the gr operation and returns its result.
- Параметры: string $entityName Input value for the entity name argument.; mixed $rowId Input value for the row id argument.; bool $idIsEncrypt Input value for the id is encrypt argument.; array $param Input value for the param argument.
- Возвращает: mixed Returns the value produced by the operation.
- Побочные эффекты: использует service locator/helpers фреймворка

### `se`

- Расположение: `_core/functions.php:451`
- Сигнатура: `function se(string $entityName)`
- Описание: Runs the se operation and returns its result.
- Параметры: string $entityName Input value for the entity name argument.
- Возвращает: mixed Returns the value produced by the operation.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках

### `le`

- Расположение: `_core/functions.php:465`
- Сигнатура: `function le(string $entityName, mixed $rowId = null, $idIsEncrypt = false)`
- Описание: Runs the le operation and returns its result.
- Параметры: string $entityName Input value for the entity name argument.; mixed $rowId Input value for the row id argument.; mixed $idIsEncrypt Input value for the id is encrypt argument.
- Возвращает: mixed Returns the value produced by the operation.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках

### `dms`

- Расположение: `_core/functions.php:479`
- Сигнатура: `function dms(string $key, mixed $defaultValue = null)`
- Описание: Runs the dms operation and returns its result.
- Параметры: string $key Lookup key used to address the target value.; mixed $defaultValue Input value for the default value argument.
- Возвращает: mixed Returns the value produced by the operation.
- Побочные эффекты: использует service locator/helpers фреймворка

### `dma`

- Расположение: `_core/functions.php:493`
- Сигнатура: `function dma(string $key, $defaultValue = [])`
- Описание: Runs the dma operation and returns its result.
- Параметры: string $key Lookup key used to address the target value.; mixed $defaultValue Input value for the default value argument.
- Возвращает: mixed Returns the value produced by the operation.
- Побочные эффекты: использует service locator/helpers фреймворка

### `role`

- Расположение: `_core/functions.php:506`
- Сигнатура: `function role(string $roleCondition)`
- Описание: Runs the role operation and returns its result.
- Параметры: string $roleCondition Input value for the role condition argument.
- Возвращает: mixed Returns the value produced by the operation.
- Побочные эффекты: использует service locator/helpers фреймворка

### `transfer_out`

- Расположение: `_core/functions.php:520`
- Сигнатура: `function transfer_out(string $newUrl, ?string $newQueryString = null, ?string $dbOper = null)`
- Описание: Runs the transfer out workflow for the framework helper.
- Параметры: string $newUrl Input value for the new url argument.; ?string $newQueryString Input value for the new query string argument.; ?string $dbOper Input value for the db oper argument.
- Возвращает: void No value is returned.
- Побочные эффекты: может выбрасывать исключения

### `transfer_int`

- Расположение: `_core/functions.php:534`
- Сигнатура: `function transfer_int(string $newUrl, ?string $newQueryString = null, ?string $dbOper = null)`
- Описание: Runs the transfer int workflow for the framework helper.
- Параметры: string $newUrl Input value for the new url argument.; ?string $newQueryString Input value for the new query string argument.; ?string $dbOper Input value for the db oper argument.
- Возвращает: void No value is returned.
- Побочные эффекты: может выбрасывать исключения

### `transfer_sham`

- Расположение: `_core/functions.php:548`
- Сигнатура: `function transfer_sham(string $newUrl, ?string $newQueryString = null, ?string $dbOper = null)`
- Описание: Runs the transfer sham workflow for the framework helper.
- Параметры: string $newUrl Input value for the new url argument.; ?string $newQueryString Input value for the new query string argument.; ?string $dbOper Input value for the db oper argument.
- Возвращает: void No value is returned.
- Побочные эффекты: может выбрасывать исключения

### `dateL2M`

- Расположение: `_core/functions.php:561`
- Сигнатура: `function dateL2M(string $date, string $format = 'euro')`
- Описание: Runs the date l2 m operation and returns its result.
- Параметры: string $date Input value for the date argument.; string $format Input value for the format argument.
- Возвращает: mixed Returns the value produced by the operation.
- Побочные эффекты: использует service locator/helpers фреймворка

### `dateM2L`

- Расположение: `_core/functions.php:574`
- Сигнатура: `function dateM2L(string $date, string $format = 'euro')`
- Описание: Runs the date m2 l operation and returns its result.
- Параметры: string $date Input value for the date argument.; string $format Input value for the format argument.
- Возвращает: mixed Returns the value produced by the operation.
- Побочные эффекты: использует service locator/helpers фреймворка

### `msg`

- Расположение: `_core/functions.php:584`
- Сигнатура: `function msg()`
- Описание: Runs the msg operation and returns its result.
- Возвращает: mixed Returns the value produced by the operation.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения

### `msgAlt`

- Расположение: `_core/functions.php:619`
- Сигнатура: `function msgAlt()`
- Описание: Runs the msg alt operation and returns its result.
- Возвращает: mixed Returns the value produced by the operation.
- Побочные эффекты: использует service locator/helpers фреймворка

### `d`

- Расположение: `_core/functions.php:636`
- Сигнатура: `function d(mixed $data, string $title = 'Custom dump', string $note = '', int|float|null $dataDepth = null, bool $isTrace = true)`
- Описание: Runs the d workflow for the framework helper.
- Параметры: mixed $data Structured data consumed by the operation.; string $title Input value for the title argument.; string $note Input value for the note argument.; int|float|null $dataDepth Input value for the data depth argument.; bool $isTrace Input value for the is trace argument.
- Возвращает: void No value is returned.
- Побочные эффекты: использует service locator/helpers фреймворка

### `l`

- Расположение: `_core/functions.php:651`
- Сигнатура: `function l(string $message, string $title = 'Custom message', string $note = '', string $type = 'custom')`
- Описание: Runs the l workflow for the framework helper.
- Параметры: string $message Input value for the message argument.; string $title Input value for the title argument.; string $note Input value for the note argument.; string $type Type discriminator that selects the required behavior.
- Возвращает: void No value is returned.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_core/plain/captcha.php`

### `fan\core\plain\captcha::__construct`

- Расположение: `_core/plain/captcha.php:58`
- Сигнатура: `function __construct(\fan\core\service\plain $handler, $key)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\plain\captcha`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\plain\captcha::getCaptcha`

- Расположение: `_core/plain/captcha.php:75`
- Сигнатура: `function getCaptcha()`
- Описание: Получает, читает или вычисляет данные `captcha` в рамках этого метода класса `fan\core\plain\captcha`.

### `fan\core\plain\captcha::getKey`

- Расположение: `_core/plain/captcha.php:87`
- Сигнатура: `function getKey()`
- Описание: Получает, читает или вычисляет данные `key` в рамках этого метода класса `fan\core\plain\captcha`.

### `fan\core\plain\captcha::_init`

- Расположение: `_core/plain/captcha.php:99`
- Сигнатура: `function _init()`
- Описание: Выполняет логику `init` и возвращает вычисленный результат.

### `fan\core\plain\captcha::_getContent`

- Расположение: `_core/plain/captcha.php:114`
- Сигнатура: `function _getContent()`
- Описание: Выполняет логику `get content` и возвращает вычисленный результат.

### `fan\core\plain\captcha::setConfig`

- Расположение: `_core/plain/captcha.php:129`
- Сигнатура: `function setConfig(\fan\core\service\config\row $config)`
- Описание: Устанавливает, добавляет или сохраняет данные `config` в рамках этого метода класса `fan\core\plain\captcha`.

## `_core/plain/db_file.php`

### `fan\core\plain\db_file::__construct`

- Расположение: `_core/plain/db_file.php:88`
- Сигнатура: `function __construct(\fan\core\service\plain $handler, $key)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\plain\db_file`.

### `fan\core\plain\db_file::outputContent`

- Расположение: `_core/plain/db_file.php:102`
- Сигнатура: `function outputContent()`
- Описание: Выполняет workflow-логику `output content`.

### `fan\core\plain\db_file::getFile`

- Расположение: `_core/plain/db_file.php:123`
- Сигнатура: `function getFile()`
- Описание: Получает, читает или вычисляет данные `file` в рамках этого метода класса `fan\core\plain\db_file`.

### `fan\core\plain\db_file::setConfig`

- Расположение: `_core/plain/db_file.php:135`
- Сигнатура: `function setConfig(\fan\core\service\config\row $config)`
- Описание: Устанавливает, добавляет или сохраняет данные `config` в рамках этого метода класса `fan\core\plain\db_file`.

### `fan\core\plain\db_file::getKey`

- Расположение: `_core/plain/db_file.php:148`
- Сигнатура: `function getKey()`
- Описание: Получает, читает или вычисляет данные `key` в рамках этого метода класса `fan\core\plain\db_file`.

### `fan\core\plain\db_file::_getContent`

- Расположение: `_core/plain/db_file.php:160`
- Сигнатура: `function _getContent()`
- Описание: Выполняет логику `get content` и возвращает вычисленный результат.

### `fan\core\plain\db_file::_prepare`

- Расположение: `_core/plain/db_file.php:170`
- Сигнатура: `function _prepare()`
- Описание: Выполняет логику `prepare` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\plain\db_file::_init`

- Расположение: `_core/plain/db_file.php:192`
- Сигнатура: `function _init()`
- Описание: Выполняет логику `init` и возвращает вычисленный результат.

### `fan\core\plain\db_file::_getFileData`

- Расположение: `_core/plain/db_file.php:228`
- Сигнатура: `function _getFileData($idIsEncrypt = null)`
- Описание: Выполняет логику `get file data` и возвращает вычисленный результат.

### `fan\core\plain\db_file::_getRow`

- Расположение: `_core/plain/db_file.php:281`
- Сигнатура: `function _getRow($idIsEncrypt = null)`
- Описание: Выполняет логику `get row` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_core/plain/image.php`

### `fan\core\plain\image::getImage`

- Расположение: `_core/plain/image.php:53`
- Сигнатура: `function getImage()`
- Описание: Получает, читает или вычисляет данные `image` в рамках этого метода класса `fan\core\plain\image`.

### `fan\core\plain\image::getNail`

- Расположение: `_core/plain/image.php:64`
- Сигнатура: `function getNail()`
- Описание: Получает, читает или вычисляет данные `nail` в рамках этого метода класса `fan\core\plain\image`.

### `fan\core\plain\image::getAdmNail`

- Расположение: `_core/plain/image.php:75`
- Сигнатура: `function getAdmNail()`
- Описание: Получает, читает или вычисляет данные `adm nail` в рамках этого метода класса `fan\core\plain\image`.

### `fan\core\plain\image::_prepareNail`

- Расположение: `_core/plain/image.php:88`
- Сигнатура: `function _prepareNail()`
- Описание: Выполняет логику `prepare nail` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\plain\image::_getNailSize`

- Расположение: `_core/plain/image.php:109`
- Сигнатура: `function _getNailSize()`
- Описание: Выполняет логику `get nail size` и возвращает вычисленный результат.

### `fan\core\plain\image::_getFileData`

- Расположение: `_core/plain/image.php:125`
- Сигнатура: `function _getFileData($idIsEncrypt = null)`
- Описание: Выполняет логику `get file data` и возвращает вычисленный результат.

### `fan\core\plain\image::_getNailFileData`

- Расположение: `_core/plain/image.php:141`
- Сигнатура: `function _getNailFileData(?bool $idIsEncrypt = null)`
- Описание: Выполняет логику `get nail file data` и возвращает вычисленный результат.

### `fan\core\plain\image::_getStubFileData`

- Расположение: `_core/plain/image.php:177`
- Сигнатура: `function _getStubFileData()`
- Описание: Выполняет логику `get stub file data` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\plain\image::_getNailDir`

- Расположение: `_core/plain/image.php:209`
- Сигнатура: `function _getNailDir(string $dirMask, $isException = false)`
- Описание: Выполняет логику `get nail dir` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой; может выбрасывать исключения

### `fan\core\plain\image::_getCacheData`

- Расположение: `_core/plain/image.php:241`
- Сигнатура: `function _getCacheData($mainData)`
- Описание: Выполняет логику `get cache data` и возвращает вычисленный результат.

### `fan\core\plain\image::_getNailData`

- Расположение: `_core/plain/image.php:269`
- Сигнатура: `function _getNailData(array $mainData)`
- Описание: Выполняет логику `get nail data` и возвращает вычисленный результат.

## `_core/plain/obfuscator.php`

### `fan\core\plain\obfuscator::__construct`

- Расположение: `_core/plain/obfuscator.php:39`
- Сигнатура: `function __construct(\fan\core\service\plain $handler, $key)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\plain\obfuscator`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\plain\obfuscator::getCss`

- Расположение: `_core/plain/obfuscator.php:55`
- Сигнатура: `function getCss()`
- Описание: Получает, читает или вычисляет данные `css` в рамках этого метода класса `fan\core\plain\obfuscator`.

### `fan\core\plain\obfuscator::getJs`

- Расположение: `_core/plain/obfuscator.php:64`
- Сигнатура: `function getJs()`
- Описание: Получает, читает или вычисляет данные `js` в рамках этого метода класса `fan\core\plain\obfuscator`.

### `fan\core\plain\obfuscator::_getContent`

- Расположение: `_core/plain/obfuscator.php:76`
- Сигнатура: `function _getContent()`
- Описание: Выполняет логику `get content` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_core/service/application.php`

### `fan\core\service\application::__construct`

- Расположение: `_core/service/application.php:38`
- Сигнатура: `function __construct($allowIni = true)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\application`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\application::setAppName`

- Расположение: `_core/service/application.php:61`
- Сигнатура: `function setAppName($name)`
- Описание: Устанавливает, добавляет или сохраняет данные `app name` в рамках этого метода класса `fan\core\service\application`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\application::getAppName`

- Расположение: `_core/service/application.php:82`
- Сигнатура: `function getAppName()`
- Описание: Получает, читает или вычисляет данные `app name` в рамках этого метода класса `fan\core\service\application`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\application::getDefaultAppName`

- Расположение: `_core/service/application.php:95`
- Сигнатура: `function getDefaultAppName()`
- Описание: Получает, читает или вычисляет данные `default app name` в рамках этого метода класса `fan\core\service\application`.

### `fan\core\service\application::getProjectName`

- Расположение: `_core/service/application.php:106`
- Сигнатура: `function getProjectName()`
- Описание: Получает, читает или вычисляет данные `project name` в рамках этого метода класса `fan\core\service\application`.

### `fan\core\service\application::getCoreVersion`

- Расположение: `_core/service/application.php:116`
- Сигнатура: `function getCoreVersion()`
- Описание: Получает, читает или вычисляет данные `core version` в рамках этого метода класса `fan\core\service\application`.

## `_core/service/cache.php`

### `fan\core\service\cache::__construct`

- Расположение: `_core/service/cache.php:57`
- Сигнатура: `function __construct($type)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\cache`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\cache::configInstance`

- Расположение: `_core/service/cache.php:81`
- Сигнатура: `function configInstance()`
- Описание: Выполняет логику `config instance` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\cache::instance`

- Расположение: `_core/service/cache.php:105`
- Сигнатура: `function instance(mixed $type = null)`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\cache::get`

- Расположение: `_core/service/cache.php:133`
- Сигнатура: `function get(string $key, mixed $default = null)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\service\cache`.

### `fan\core\service\cache::set`

- Расположение: `_core/service/cache.php:147`
- Сигнатура: `function set(string $key, mixed $value, bool $autoSave = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\service\cache`.

### `fan\core\service\cache::getOrDefine`

- Расположение: `_core/service/cache.php:164`
- Сигнатура: `function getOrDefine(string $key, mixed $callBack, bool $autoSave = true)`
- Описание: Получает, читает или вычисляет данные `or define` в рамках этого метода класса `fan\core\service\cache`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\cache::getMeta`

- Расположение: `_core/service/cache.php:186`
- Сигнатура: `function getMeta(string $key, bool $loadMetaOnly = false)`
- Описание: Получает, читает или вычисляет данные `meta` в рамках этого метода класса `fan\core\service\cache`.

### `fan\core\service\cache::getExtraMeta`

- Расположение: `_core/service/cache.php:199`
- Сигнатура: `function getExtraMeta(string $key, string $param)`
- Описание: Получает, читает или вычисляет данные `extra meta` в рамках этого метода класса `fan\core\service\cache`.

### `fan\core\service\cache::setExtraMeta`

- Расположение: `_core/service/cache.php:213`
- Сигнатура: `function setExtraMeta(string $key, string $param, mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `extra meta` в рамках этого метода класса `fan\core\service\cache`.

### `fan\core\service\cache::checkExtraMeta`

- Расположение: `_core/service/cache.php:230`
- Сигнатура: `function checkExtraMeta(string $key, string $param, mixed $value, $method = 'equal', $allowDelete = false)`
- Описание: Проверяет условие или валидирует данные `extra meta` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\cache::isActual`

- Расположение: `_core/service/cache.php:279`
- Сигнатура: `function isActual($key)`
- Описание: Проверяет условие или валидирует данные `actual` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\cache::setLifetime`

- Расположение: `_core/service/cache.php:292`
- Сигнатура: `function setLifetime(string $key, int $time)`
- Описание: Устанавливает, добавляет или сохраняет данные `lifetime` в рамках этого метода класса `fan\core\service\cache`.

### `fan\core\service\cache::setStartLimit`

- Расположение: `_core/service/cache.php:306`
- Сигнатура: `function setStartLimit(string $key, string $dateTime)`
- Описание: Устанавливает, добавляет или сохраняет данные `start limit` в рамках этого метода класса `fan\core\service\cache`.

### `fan\core\service\cache::checkSourceFile`

- Расположение: `_core/service/cache.php:322`
- Сигнатура: `function checkSourceFile(string $key, string $filePath)`
- Описание: Проверяет условие или валидирует данные `source file` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\cache::save`

- Расположение: `_core/service/cache.php:338`
- Сигнатура: `function save(string $key)`
- Описание: Устанавливает, добавляет или сохраняет данные `данные` в рамках этого метода класса `fan\core\service\cache`.

### `fan\core\service\cache::delete`

- Расположение: `_core/service/cache.php:351`
- Сигнатура: `function delete(string $key)`
- Описание: Удаляет или сбрасывает состояние `данные` для этого метода класса `fan\core\service\cache`.

### `fan\core\service\cache::setExtraPath`

- Расположение: `_core/service/cache.php:365`
- Сигнатура: `function setExtraPath($key, $extraPath)`
- Описание: Устанавливает, добавляет или сохраняет данные `extra path` в рамках этого метода класса `fan\core\service\cache`.

### `fan\core\service\cache::isSaved`

- Расположение: `_core/service/cache.php:378`
- Сигнатура: `function isSaved(string $key)`
- Описание: Проверяет условие или валидирует данные `saved` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\cache::getEngine`

- Расположение: `_core/service/cache.php:390`
- Сигнатура: `function getEngine(string $key)`
- Описание: Получает, читает или вычисляет данные `engine` в рамках этого метода класса `fan\core\service\cache`.
- Побочные эффекты: может выбрасывать исключения

## `_core/service/cache/base.php`

### `fan\core\service\cache\base::__construct`

- Расположение: `_core/service/cache/base.php:85`
- Сигнатура: `function __construct(\fan\core\service\cache $facade, $type, $key, $config)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\cache\base`.

### `fan\core\service\cache\base::__destruct`

- Расположение: `_core/service/cache/base.php:96`
- Сигнатура: `function __destruct()`
- Описание: Завершает работу объекта и выполняет отложенную очистку для этого метода класса `fan\core\service\cache\base`.

### `fan\core\service\cache\base::get`

- Расположение: `_core/service/cache/base.php:114`
- Сигнатура: `function get(mixed $default = null)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\service\cache\base`.

### `fan\core\service\cache\base::set`

- Расположение: `_core/service/cache/base.php:133`
- Сигнатура: `function set(mixed $value, bool $autoSave)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\service\cache\base`.

### `fan\core\service\cache\base::addMeta`

- Расположение: `_core/service/cache/base.php:154`
- Сигнатура: `function addMeta(mixed $metaData)`
- Описание: Устанавливает, добавляет или сохраняет данные `meta` в рамках этого метода класса `fan\core\service\cache\base`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\cache\base::getMeta`

- Расположение: `_core/service/cache/base.php:172`
- Сигнатура: `function getMeta(bool $loadMetaOnly)`
- Описание: Получает, читает или вычисляет данные `meta` в рамках этого метода класса `fan\core\service\cache\base`.

### `fan\core\service\cache\base::getExtraMeta`

- Расположение: `_core/service/cache/base.php:190`
- Сигнатура: `function getExtraMeta(string $param)`
- Описание: Получает, читает или вычисляет данные `extra meta` в рамках этого метода класса `fan\core\service\cache\base`.

### `fan\core\service\cache\base::setExtraMeta`

- Расположение: `_core/service/cache/base.php:204`
- Сигнатура: `function setExtraMeta(string $param, mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `extra meta` в рамках этого метода класса `fan\core\service\cache\base`.

### `fan\core\service\cache\base::setLifetime`

- Расположение: `_core/service/cache/base.php:222`
- Сигнатура: `function setLifetime(int $time)`
- Описание: Устанавливает, добавляет или сохраняет данные `lifetime` в рамках этого метода класса `fan\core\service\cache\base`.

### `fan\core\service\cache\base::setStartLimit`

- Расположение: `_core/service/cache/base.php:236`
- Сигнатура: `function setStartLimit(string $dateTime)`
- Описание: Устанавливает, добавляет или сохраняет данные `start limit` в рамках этого метода класса `fan\core\service\cache\base`.

### `fan\core\service\cache\base::isActual`

- Расположение: `_core/service/cache/base.php:252`
- Сигнатура: `function isActual()`
- Описание: Проверяет условие или валидирует данные `actual` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\cache\base::save`

- Расположение: `_core/service/cache/base.php:262`
- Сигнатура: `function save()`
- Описание: Устанавливает, добавляет или сохраняет данные `данные` в рамках этого метода класса `fan\core\service\cache\base`.

### `fan\core\service\cache\base::delete`

- Расположение: `_core/service/cache/base.php:276`
- Сигнатура: `function delete()`
- Описание: Удаляет или сбрасывает состояние `данные` для этого метода класса `fan\core\service\cache\base`.

### `fan\core\service\cache\base::setExtraPath`

- Расположение: `_core/service/cache/base.php:291`
- Сигнатура: `function setExtraPath(string $extraPath)`
- Описание: Устанавливает, добавляет или сохраняет данные `extra path` в рамках этого метода класса `fan\core\service\cache\base`.

### `fan\core\service\cache\base::isLoaded`

- Расположение: `_core/service/cache/base.php:302`
- Сигнатура: `function isLoaded()`
- Описание: Проверяет условие или валидирует данные `loaded` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\cache\base::isSaved`

- Расположение: `_core/service/cache/base.php:312`
- Сигнатура: `function isSaved()`
- Описание: Проверяет условие или валидирует данные `saved` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\cache\base::setFacade`

- Расположение: `_core/service/cache/base.php:324`
- Сигнатура: `function setFacade(\fan\core\base\service $facade)`
- Описание: Устанавливает, добавляет или сохраняет данные `facade` в рамках этого метода класса `fan\core\service\cache\base`.

### `fan\core\service\cache\base::_loadData`

- Расположение: `_core/service/cache/base.php:340`
- Сигнатура: `function _loadData($loadMetaOnly);`
- Описание: Выполняет workflow-логику `load data`.

### `fan\core\service\cache\base::_saveData`

- Расположение: `_core/service/cache/base.php:347`
- Сигнатура: `function _saveData();`
- Описание: Выполняет workflow-логику `save data`.

### `fan\core\service\cache\base::_deleteData`

- Расположение: `_core/service/cache/base.php:355`
- Сигнатура: `function _deleteData()`
- Описание: Выполняет логику `delete data` и возвращает вычисленный результат.

### `fan\core\service\cache\base::_makeNewMeta`

- Расположение: `_core/service/cache/base.php:367`
- Сигнатура: `function _makeNewMeta()`
- Описание: Выполняет логику `make new meta` и возвращает вычисленный результат.

### `fan\core\service\cache\base::_encodePayload`

- Расположение: `_core/service/cache/base.php:384`
- Сигнатура: `function _encodePayload(mixed $value): string`
- Описание: Выполняет логику `encode payload` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения; сериализует или десериализует данные

### `fan\core\service\cache\base::_decodePayload`

- Расположение: `_core/service/cache/base.php:406`
- Сигнатура: `function _decodePayload(string $data, string $errorTitle = 'Cache payload decode error'): mixed`
- Описание: Выполняет логику `decode payload` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках; сериализует или десериализует данные

### `fan\core\service\cache\base::_isJsonPayload`

- Расположение: `_core/service/cache/base.php:427`
- Сигнатура: `function _isJsonPayload(string $data): bool`
- Описание: Выполняет логику `is json payload` и возвращает вычисленный результат.

### `fan\core\service\cache\base::_decodeLegacyPayload`

- Расположение: `_core/service/cache/base.php:440`
- Сигнатура: `function _decodeLegacyPayload(string $data, string $errorTitle): mixed`
- Описание: Выполняет логику `decode legacy payload` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках; сериализует или десериализует данные

### `fan\core\service\cache\base::_hasUnsupportedJsonValue`

- Расположение: `_core/service/cache/base.php:466`
- Сигнатура: `function _hasUnsupportedJsonValue(mixed $value, int $depth = 0): bool`
- Описание: Выполняет логику `has unsupported json value` и возвращает вычисленный результат.

### `fan\core\service\cache\base::_checkActual`

- Расположение: `_core/service/cache/base.php:490`
- Сигнатура: `function _checkActual(array $meta, bool $deleteExired = true)`
- Описание: Выполняет логику `check actual` и возвращает вычисленный результат.

### `fan\core\service\cache\base::_checkDateFormat`

- Расположение: `_core/service/cache/base.php:510`
- Сигнатура: `function _checkDateFormat(string $dateTime)`
- Описание: Выполняет логику `check date format` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

## `_core/service/cache/file.php`

### `fan\core\service\cache\file::_loadData`

- Расположение: `_core/service/cache/file.php:40`
- Сигнатура: `function _loadData($loadMetaOnly)`
- Описание: Выполняет логику `load data` и возвращает вычисленный результат.

### `fan\core\service\cache\file::_saveData`

- Расположение: `_core/service/cache/file.php:85`
- Сигнатура: `function _saveData()`
- Описание: Выполняет логику `save data` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой

### `fan\core\service\cache\file::_deleteData`

- Расположение: `_core/service/cache/file.php:106`
- Сигнатура: `function _deleteData()`
- Описание: Выполняет логику `delete data` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой

### `fan\core\service\cache\file::_getFilePath`

- Расположение: `_core/service/cache/file.php:124`
- Сигнатура: `function _getFilePath()`
- Описание: Выполняет логику `get file path` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой; может выбрасывать исключения

### `fan\core\service\cache\file::_checkWritable`

- Расположение: `_core/service/cache/file.php:171`
- Сигнатура: `function _checkWritable($filePath, $type)`
- Описание: Выполняет workflow-логику `check writable`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\cache\file::_readFile`

- Расположение: `_core/service/cache/file.php:185`
- Сигнатура: `function _readFile($filePath)`
- Описание: Выполняет логику `read file` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

## `_core/service/cache/memcache.php`

### `fan\core\service\cache\memcache::_loadData`

- Расположение: `_core/service/cache/memcache.php:35`
- Сигнатура: `function _loadData($loadMetaOnly)`
- Описание: Выполняет логику `load data` и возвращает вычисленный результат.

### `fan\core\service\cache\memcache::_saveData`

- Расположение: `_core/service/cache/memcache.php:53`
- Сигнатура: `function _saveData()`
- Описание: Выполняет workflow-логику `save data`.

### `fan\core\service\cache\memcache::_deleteData`

- Расположение: `_core/service/cache/memcache.php:65`
- Сигнатура: `function _deleteData()`
- Описание: Выполняет логику `delete data` и возвращает вычисленный результат.

### `fan\core\service\cache\memcache::_getKeeper`

- Расположение: `_core/service/cache/memcache.php:81`
- Сигнатура: `function _getKeeper()`
- Описание: Выполняет логику `get keeper` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\cache\memcache::_getKey`

- Расположение: `_core/service/cache/memcache.php:108`
- Сигнатура: `function _getKey(string $suffix)`
- Описание: Выполняет логику `get key` и возвращает вычисленный результат.

## `_core/service/cache/memcached.php`

### `fan\core\service\cache\memcached::_loadData`

- Расположение: `_core/service/cache/memcached.php:29`
- Сигнатура: `function _loadData($loadMetaOnly)`
- Описание: Выполняет workflow-логику `load data`.

### `fan\core\service\cache\memcached::_saveData`

- Расположение: `_core/service/cache/memcached.php:39`
- Сигнатура: `function _saveData()`
- Описание: Выполняет workflow-логику `save data`.

### `fan\core\service\cache\memcached::_deleteData`

- Расположение: `_core/service/cache/memcached.php:49`
- Сигнатура: `function _deleteData()`
- Описание: Выполняет workflow-логику `delete data`.

## `_core/service/cache/wrapper/file_data.php`

### `fan\core\service\cache\wrapper\file_data::__construct`

- Расположение: `_core/service/cache/wrapper/file_data.php:54`
- Сигнатура: `function __construct($rowData, $idIsEncrypt = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\cache\wrapper\file_data`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\cache\wrapper\file_data::getFileData`

- Расположение: `_core/service/cache/wrapper/file_data.php:75`
- Сигнатура: `function getFileData()`
- Описание: Получает, читает или вычисляет данные `file data` в рамках этого метода класса `fan\core\service\cache\wrapper\file_data`.

### `fan\core\service\cache\wrapper\file_data::reset`

- Расположение: `_core/service/cache/wrapper/file_data.php:93`
- Сигнатура: `function reset()`
- Описание: Удаляет или сбрасывает состояние `состояние` для этого метода класса `fan\core\service\cache\wrapper\file_data`.

### `fan\core\service\cache\wrapper\file_data::_getRow`

- Расположение: `_core/service/cache/wrapper/file_data.php:122`
- Сигнатура: `function _getRow()`
- Описание: Выполняет логику `get row` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\cache\wrapper\file_data::_getCache`

- Расположение: `_core/service/cache/wrapper/file_data.php:143`
- Сигнатура: `function _getCache()`
- Описание: Выполняет логику `get cache` и возвращает вычисленный результат.

## `_core/service/captcha.php`

### `fan\core\service\captcha::__construct`

- Расположение: `_core/service/captcha.php:50`
- Сигнатура: `function __construct($formId)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\captcha`.

### `fan\core\service\captcha::instance`

- Расположение: `_core/service/captcha.php:65`
- Сигнатура: `function instance(string|\fan\core\block\form\usual $formId)`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.

### `fan\core\service\captcha::makeNewText`

- Расположение: `_core/service/captcha.php:87`
- Сигнатура: `function makeNewText(mixed $length = null, mixed $type = null)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `new text` для этого метода класса `fan\core\service\captcha`.

### `fan\core\service\captcha::getText`

- Расположение: `_core/service/captcha.php:105`
- Сигнатура: `function getText()`
- Описание: Получает, читает или вычисляет данные `text` в рамках этого метода класса `fan\core\service\captcha`.

### `fan\core\service\captcha::clearText`

- Расположение: `_core/service/captcha.php:115`
- Сигнатура: `function clearText()`
- Описание: Удаляет или сбрасывает состояние `text` для этого метода класса `fan\core\service\captcha`.

### `fan\core\service\captcha::checkCaptcha`

- Расположение: `_core/service/captcha.php:128`
- Сигнатура: `function checkCaptcha(string $text, bool $del = true)`
- Описание: Проверяет условие или валидирует данные `captcha` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\captcha::getUrn`

- Расположение: `_core/service/captcha.php:142`
- Сигнатура: `function getUrn()`
- Описание: Получает, читает или вычисляет данные `urn` в рамках этого метода класса `fan\core\service\captcha`.

### `fan\core\service\captcha::getHeaders`

- Расположение: `_core/service/captcha.php:152`
- Сигнатура: `function getHeaders()`
- Описание: Получает, читает или вычисляет данные `headers` в рамках этого метода класса `fan\core\service\captcha`.

### `fan\core\service\captcha::getBinaryData`

- Расположение: `_core/service/captcha.php:162`
- Сигнатура: `function getBinaryData()`
- Описание: Получает, читает или вычисляет данные `binary data` в рамках этого метода класса `fan\core\service\captcha`.

### `fan\core\service\captcha::_getTextGenerator`

- Расположение: `_core/service/captcha.php:173`
- Сигнатура: `function _getTextGenerator()`
- Описание: Выполняет логику `get text generator` и возвращает вычисленный результат.

### `fan\core\service\captcha::_getFileMaker`

- Расположение: `_core/service/captcha.php:188`
- Сигнатура: `function _getFileMaker()`
- Описание: Выполняет логику `get file maker` и возвращает вычисленный результат.

### `fan\core\service\captcha::_getSession`

- Расположение: `_core/service/captcha.php:203`
- Сигнатура: `function _getSession()`
- Описание: Выполняет логику `get session` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_core/service/captcha/base.php`

### `fan\core\service\captcha\base::setFacade`

- Расположение: `_core/service/captcha/base.php:45`
- Сигнатура: `function setFacade(\fan\core\service\captcha $facade)`
- Описание: Устанавливает, добавляет или сохраняет данные `facade` в рамках этого метода класса `fan\core\service\captcha\base`.

### `fan\core\service\captcha\base::setConfig`

- Расположение: `_core/service/captcha/base.php:60`
- Сигнатура: `function setConfig(\fan\core\service\config\row $config)`
- Описание: Устанавливает, добавляет или сохраняет данные `config` в рамках этого метода класса `fan\core\service\captcha\base`.
- Побочные эффекты: может выбрасывать исключения

## `_core/service/captcha/file_maker/picture_1.php`

### `fan\core\service\captcha\file_maker\picture_1::getHeaders`

- Расположение: `_core/service/captcha/file_maker/picture_1.php:31`
- Сигнатура: `function getHeaders()`
- Описание: Получает, читает или вычисляет данные `headers` в рамках этого метода класса `fan\core\service\captcha\file_maker\picture_1`.

### `fan\core\service\captcha\file_maker\picture_1::getData`

- Расположение: `_core/service/captcha/file_maker/picture_1.php:44`
- Сигнатура: `function getData()`
- Описание: Получает, читает или вычисляет данные `data` в рамках этого метода класса `fan\core\service\captcha\file_maker\picture_1`.

### `fan\core\service\captcha\file_maker\picture_1::_makeBinaryData`

- Расположение: `_core/service/captcha/file_maker/picture_1.php:59`
- Сигнатура: `function _makeBinaryData()`
- Описание: Выполняет логику `make binary data` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\captcha\file_maker\picture_1::_randomChoice`

- Расположение: `_core/service/captcha/file_maker/picture_1.php:122`
- Сигнатура: `function _randomChoice(mixed $arr)`
- Описание: Выполняет логику `random choice` и возвращает вычисленный результат.

### `fan\core\service\captcha\file_maker\picture_1::_randomValue`

- Расположение: `_core/service/captcha/file_maker/picture_1.php:142`
- Сигнатура: `function _randomValue(mixed $conf, int|float $defMin, int|float $defMax)`
- Описание: Выполняет логику `random value` и возвращает вычисленный результат.

### `fan\core\service\captcha\file_maker\picture_1::_randomColor`

- Расположение: `_core/service/captcha/file_maker/picture_1.php:162`
- Сигнатура: `function _randomColor(\fan\core\service\config\row $conf, string $key)`
- Описание: Выполняет логику `random color` и возвращает вычисленный результат.

### `fan\core\service\captcha\file_maker\picture_1::_drawLines`

- Расположение: `_core/service/captcha/file_maker/picture_1.php:189`
- Сигнатура: `function _drawLines(\fan\core\service\image_draw $img, \fan\core\service\config\row $conf, int|float $qtt, int|float $height)`
- Описание: Выполняет логику `draw lines` и возвращает вычисленный результат.

## `_core/service/captcha/text_generator/simple.php`

### `fan\core\service\captcha\text_generator\simple::makeNewText`

- Расположение: `_core/service/captcha/text_generator/simple.php:29`
- Сигнатура: `function makeNewText(int $length, string $type)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `new text` для этого метода класса `fan\core\service\captcha\text_generator\simple`.

## `_core/service/cli.php`

### `fan\core\service\cli::__construct`

- Расположение: `_core/service/cli.php:27`
- Сигнатура: `function __construct($allowIni = true)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\cli`.

### `fan\core\service\cli::getContent`

- Расположение: `_core/service/cli.php:43`
- Сигнатура: `function getContent($controllerClass, $method)`
- Описание: Получает, читает или вычисляет данные `content` в рамках этого метода класса `fan\core\service\cli`.

### `fan\core\service\cli::_getFinalContent`

- Расположение: `_core/service/cli.php:62`
- Сигнатура: `function _getFinalContent($method)`
- Описание: Выполняет логику `get final content` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\cli::_setController`

- Расположение: `_core/service/cli.php:83`
- Сигнатура: `function _setController(string $controller)`
- Описание: Выполняет логику `set controller` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

## `_core/service/config.php`

### `fan\core\service\config::__construct`

- Расположение: `_core/service/config.php:71`
- Сигнатура: `function __construct($configType, $sourceType)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\config`.

### `fan\core\service\config::instance`

- Расположение: `_core/service/config.php:93`
- Сигнатура: `function instance(string $configType = 'service', string $sourceType = 'ini')`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.

### `fan\core\service\config::mergeByApp`

- Расположение: `_core/service/config.php:108`
- Сигнатура: `function mergeByApp(string $appName)`
- Описание: Устанавливает, добавляет или сохраняет данные `by app` в рамках этого метода класса `fan\core\service\config`.

### `fan\core\service\config::get`

- Расположение: `_core/service/config.php:125`
- Сигнатура: `function get(string $name, string|array|null $key = null)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\service\config`.

### `fan\core\service\config::getSrc`

- Расположение: `_core/service/config.php:139`
- Сигнатура: `function getSrc(string $name, mixed $key = null)`
- Описание: Получает, читает или вычисляет данные `src` в рамках этого метода класса `fan\core\service\config`.

### `fan\core\service\config::set`

- Расположение: `_core/service/config.php:162`
- Сигнатура: `function set(string $name, string $key, string $value, bool $rewriteExisting = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\service\config`.

### `fan\core\service\config::merge`

- Расположение: `_core/service/config.php:180`
- Сигнатура: `function merge(array|\fan\core\service\config\row $data, bool $priority = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `merge` в рамках этого метода класса `fan\core\service\config`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\config::reset`

- Расположение: `_core/service/config.php:199`
- Сигнатура: `function reset(mixed $name = null, mixed $key = null)`
- Описание: Удаляет или сбрасывает состояние `состояние` для этого метода класса `fan\core\service\config`.

### `fan\core\service\config::getConfigType`

- Расположение: `_core/service/config.php:227`
- Сигнатура: `function getConfigType()`
- Описание: Получает, читает или вычисляет данные `config type` в рамках этого метода класса `fan\core\service\config`.

### `fan\core\service\config::getServiceConfig`

- Расположение: `_core/service/config.php:239`
- Сигнатура: `function getServiceConfig(\fan\core\base\service $service)`
- Описание: Получает, читает или вычисляет данные `service config` в рамках этого метода класса `fan\core\service\config`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\config::getControllerConfig`

- Расположение: `_core/service/config.php:266`
- Сигнатура: `function getControllerConfig(mixed $ctrl, string $name)`
- Описание: Получает, читает или вычисляет данные `controller config` в рамках этого метода класса `fan\core\service\config`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\config::getEntityConfig`

- Расположение: `_core/service/config.php:287`
- Сигнатура: `function getEntityConfig(\fan\core\base\model\entity $entity, $name = null)`
- Описание: Получает, читает или вычисляет данные `entity config` в рамках этого метода класса `fan\core\service\config`.

### `fan\core\service\config::_initServiceConfig`

- Расположение: `_core/service/config.php:313`
- Сигнатура: `function _initServiceConfig()`
- Описание: Выполняет логику `init service config` и возвращает вычисленный результат.

### `fan\core\service\config::_initOtherConfig`

- Расположение: `_core/service/config.php:336`
- Сигнатура: `function _initOtherConfig()`
- Описание: Выполняет логику `init other config` и возвращает вычисленный результат.

### `fan\core\service\config::_getData`

- Расположение: `_core/service/config.php:358`
- Сигнатура: `function _getData(string $fileName, bool $checkExist = true)`
- Описание: Выполняет логику `get data` и возвращает вычисленный результат.

### `fan\core\service\config::_setConfig`

- Расположение: `_core/service/config.php:383`
- Сигнатура: `function _setConfig()`
- Описание: Выполняет логику `set config` и возвращает вычисленный результат.

### `fan\core\service\config::_getConfigEngine`

- Расположение: `_core/service/config.php:393`
- Сигнатура: `function _getConfigEngine()`
- Описание: Выполняет логику `get config engine` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\config::_mergeConfig`

- Расположение: `_core/service/config.php:418`
- Сигнатура: `function _mergeConfig(string $fileName, string $resetConf, string $checkExist)`
- Описание: Выполняет логику `merge config` и возвращает вычисленный результат.

### `fan\core\service\config::_isRow`

- Расположение: `_core/service/config.php:434`
- Сигнатура: `function _isRow(mixed $obj)`
- Описание: Выполняет логику `is row` и возвращает вычисленный результат.

## `_core/service/config/base.php`

### `fan\core\service\config\base::setFacade`

- Расположение: `_core/service/config/base.php:45`
- Сигнатура: `function setFacade(\fan\core\service\config $facade)`
- Описание: Устанавливает, добавляет или сохраняет данные `facade` в рамках этого метода класса `fan\core\service\config\base`.

### `fan\core\service\config\base::getFilePath`

- Расположение: `_core/service/config/base.php:61`
- Сигнатура: `function getFilePath($fileName, $checkExist = true)`
- Описание: Получает, читает или вычисляет данные `file path` в рамках этого метода класса `fan\core\service\config\base`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\config\base::loadFile`

- Расположение: `_core/service/config/base.php:80`
- Сигнатура: `function loadFile(string $filePath)`
- Описание: Получает, читает или вычисляет данные `file` в рамках этого метода класса `fan\core\service\config\base`.

### `fan\core\service\config\base::setDirPath`

- Расположение: `_core/service/config/base.php:95`
- Сигнатура: `function setDirPath(string $sourceDir)`
- Описание: Устанавливает, добавляет или сохраняет данные `dir path` в рамках этого метода класса `fan\core\service\config\base`.

### `fan\core\service\config\base::_loadSourceData`

- Расположение: `_core/service/config/base.php:108`
- Сигнатура: `function _loadSourceData($srcFilePath)`
- Описание: Выполняет логику `load source data` и возвращает вычисленный результат.

## `_core/service/config/ini.php`

### `fan\core\service\config\ini::_loadSourceData`

- Расположение: `_core/service/config/ini.php:34`
- Сигнатура: `function _loadSourceData($srcFilePath)`
- Описание: Выполняет логику `load source data` и возвращает вычисленный результат.

### `fan\core\service\config\ini::_separateByDot`

- Расположение: `_core/service/config/ini.php:48`
- Сигнатура: `function _separateByDot(&$branch)`
- Описание: Выполняет workflow-логику `separate by dot`.

### `fan\core\service\config\ini::_checkDotSeparatedElm`

- Расположение: `_core/service/config/ini.php:72`
- Сигнатура: `function &_checkDotSeparatedElm(&$branch, $key, $val)`
- Описание: Выполняет логику `check dot separated elm` и возвращает вычисленный результат.

## `_core/service/config/row.php`

### `fan\core\service\config\row::__construct`

- Расположение: `_core/service/config/row.php:52`
- Сигнатура: `function __construct($data, $key = null, $superior = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\config\row`.

### `fan\core\service\config\row::setFacade`

- Расположение: `_core/service/config/row.php:68`
- Сигнатура: `function setFacade(\fan\core\base\service $facade)`
- Описание: Устанавливает, добавляет или сохраняет данные `facade` в рамках этого метода класса `fan\core\service\config\row`.

### `fan\core\service\config\row::getRootKey`

- Расположение: `_core/service/config/row.php:87`
- Сигнатура: `function getRootKey()`
- Описание: Получает, читает или вычисляет данные `root key` в рамках этого метода класса `fan\core\service\config\row`.

### `fan\core\service\config\row::setServiceOwner`

- Расположение: `_core/service/config/row.php:107`
- Сигнатура: `function setServiceOwner(\fan\core\base\service $service)`
- Описание: Устанавливает, добавляет или сохраняет данные `service owner` в рамках этого метода класса `fan\core\service\config\row`.

### `fan\core\service\config\row::setPlainOwner`

- Расположение: `_core/service/config/row.php:127`
- Сигнатура: `function setPlainOwner(object $ctrl, string $name)`
- Описание: Устанавливает, добавляет или сохраняет данные `plain owner` в рамках этого метода класса `fan\core\service\config\row`.

### `fan\core\service\config\row::setEntityOwner`

- Расположение: `_core/service/config/row.php:147`
- Сигнатура: `function setEntityOwner(\fan\core\base\model\entity $entity, string $name)`
- Описание: Устанавливает, добавляет или сохраняет данные `entity owner` в рамках этого метода класса `fan\core\service\config\row`.

### `fan\core\service\config\row::getOwners`

- Расположение: `_core/service/config/row.php:163`
- Сигнатура: `function getOwners()`
- Описание: Получает, читает или вычисляет данные `owners` в рамках этого метода класса `fan\core\service\config\row`.

### `fan\core\service\config\row::getSources`

- Расположение: `_core/service/config/row.php:173`
- Сигнатура: `function getSources()`
- Описание: Получает, читает или вычисляет данные `sources` в рамках этого метода класса `fan\core\service\config\row`.

### `fan\core\service\config\row::reset`

- Расположение: `_core/service/config/row.php:187`
- Сигнатура: `function reset(mixed $key = null)`
- Описание: Удаляет или сбрасывает состояние `состояние` для этого метода класса `fan\core\service\config\row`.

### `fan\core\service\config\row::mergeData`

- Расположение: `_core/service/config/row.php:214`
- Сигнатура: `function mergeData(array|\fan\core\service\config\row $data, bool $priority = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `data` в рамках этого метода класса `fan\core\service\config\row`.

### `fan\core\service\config\row::_makeSubData`

- Расположение: `_core/service/config/row.php:238`
- Сигнатура: `function _makeSubData($key, $value)`
- Описание: Выполняет логику `make sub data` и возвращает вычисленный результат.

### `fan\core\service\config\row::_checkSetter`

- Расположение: `_core/service/config/row.php:253`
- Сигнатура: `function _checkSetter()`
- Описание: Выполняет логику `check setter` и возвращает вычисленный результат.

### `fan\core\service\config\row::__clone`

- Расположение: `_core/service/config/row.php:264`
- Сигнатура: `function __clone()`
- Описание: Implements PHP magic behavior for this current component.
- Возвращает: void No value is returned.

### `fan\core\service\config\row::__unset`

- Расположение: `_core/service/config/row.php:275`
- Сигнатура: `function __unset($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\config\row`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\config\row::__serialize`

- Расположение: `_core/service/config/row.php:288`
- Сигнатура: `function __serialize(): array`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\config\row`.

### `fan\core\service\config\row::__unserialize`

- Расположение: `_core/service/config/row.php:304`
- Сигнатура: `function __unserialize(array $recover): void`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\config\row`.

### `fan\core\service\config\row::serialize`

- Расположение: `_core/service/config/row.php:317`
- Сигнатура: `function serialize(): string`
- Описание: Выполняет логику `serialize` и возвращает вычисленный результат.
- Побочные эффекты: сериализует или десериализует данные

### `fan\core\service\config\row::unserialize`

- Расположение: `_core/service/config/row.php:329`
- Сигнатура: `function unserialize($recover): void`
- Описание: Выполняет workflow-логику `unserialize`.
- Побочные эффекты: сериализует или десериализует данные

## `_core/service/cookie.php`

### `fan\core\service\cookie::__construct`

- Расположение: `_core/service/cookie.php:62`
- Сигнатура: `function __construct($path, $domain, $secure)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\cookie`.
- Побочные эффекты: читает PHP superglobals

### `fan\core\service\cookie::instance`

- Расположение: `_core/service/cookie.php:83`
- Сигнатура: `function instance(mixed $path = null, mixed $domain = null, bool $secure = false)`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.

### `fan\core\service\cookie::get`

- Расположение: `_core/service/cookie.php:113`
- Сигнатура: `function get(string $name, ?string $defaultVal = null)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\service\cookie`.

### `fan\core\service\cookie::encodeCookieValue`

- Расположение: `_core/service/cookie.php:129`
- Сигнатура: `function encodeCookieValue(mixed $value): string`
- Описание: Создает, разбирает, форматирует или конвертирует данные `cookie value` для этого метода класса `fan\core\service\cookie`.
- Побочные эффекты: может выбрасывать исключения; сериализует или десериализует данные

### `fan\core\service\cookie::decodeCookieValue`

- Расположение: `_core/service/cookie.php:151`
- Сигнатура: `function decodeCookieValue(string $value, mixed $defaultVal = null): mixed`
- Описание: Создает, разбирает, форматирует или конвертирует данные `cookie value` для этого метода класса `fan\core\service\cookie`.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках; сериализует или десериализует данные

### `fan\core\service\cookie::decodeLegacyCookieValue`

- Расположение: `_core/service/cookie.php:172`
- Сигнатура: `function decodeLegacyCookieValue(string $value): mixed`
- Описание: Создает, разбирает, форматирует или конвертирует данные `legacy cookie value` для этого метода класса `fan\core\service\cookie`.
- Побочные эффекты: сериализует или десериализует данные

### `fan\core\service\cookie::hasUnsupportedJsonValue`

- Расположение: `_core/service/cookie.php:192`
- Сигнатура: `function hasUnsupportedJsonValue(mixed $value, int $depth = 0): bool`
- Описание: Проверяет условие или валидирует данные `unsupported json value` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\cookie::getAll`

- Расположение: `_core/service/cookie.php:215`
- Сигнатура: `function getAll(?string $defaultVal = null)`
- Описание: Получает, читает или вычисляет данные `all` в рамках этого метода класса `fan\core\service\cookie`.

### `fan\core\service\cookie::set`

- Расположение: `_core/service/cookie.php:232`
- Сигнатура: `function set(string $name, mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\service\cookie`.

### `fan\core\service\cookie::setByTime`

- Расположение: `_core/service/cookie.php:246`
- Сигнатура: `function setByTime(string $name, mixed $value, int $time)`
- Описание: Устанавливает, добавляет или сохраняет данные `by time` в рамках этого метода класса `fan\core\service\cookie`.
- Побочные эффекты: меняет HTTP/session состояние; читает PHP superglobals

### `fan\core\service\cookie::setByDate`

- Расположение: `_core/service/cookie.php:271`
- Сигнатура: `function setByDate(string $name, mixed $value, string $date)`
- Описание: Устанавливает, добавляет или сохраняет данные `by date` в рамках этого метода класса `fan\core\service\cookie`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\cookie::delete`

- Расположение: `_core/service/cookie.php:304`
- Сигнатура: `function delete(string $name)`
- Описание: Удаляет или сбрасывает состояние `данные` для этого метода класса `fan\core\service\cookie`.

### `fan\core\service\cookie::setHttpOnlyFlag`

- Расположение: `_core/service/cookie.php:316`
- Сигнатура: `function setHttpOnlyFlag(bool $httpOnly)`
- Описание: Устанавливает, добавляет или сохраняет данные `http only flag` в рамках этого метода класса `fan\core\service\cookie`.

## `_core/service/curl.php`

### `fan\core\service\curl::__construct`

- Расположение: `_core/service/curl.php:70`
- Сигнатура: `function __construct($url, $index)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\curl`.

### `fan\core\service\curl::__destruct`

- Расположение: `_core/service/curl.php:94`
- Сигнатура: `function __destruct()`
- Описание: Завершает работу объекта и выполняет отложенную очистку для этого метода класса `fan\core\service\curl`.

### `fan\core\service\curl::instance`

- Расположение: `_core/service/curl.php:107`
- Сигнатура: `function instance($url, $index = 0)`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.

### `fan\core\service\curl::setOption`

- Расположение: `_core/service/curl.php:123`
- Сигнатура: `function setOption(int $key, mixed $val)`
- Описание: Устанавливает, добавляет или сохраняет данные `option` в рамках этого метода класса `fan\core\service\curl`.

### `fan\core\service\curl::setHeaders`

- Расположение: `_core/service/curl.php:136`
- Сигнатура: `function setHeaders(array $headers = [])`
- Описание: Устанавливает, добавляет или сохраняет данные `headers` в рамках этого метода класса `fan\core\service\curl`.

### `fan\core\service\curl::setTimeout`

- Расположение: `_core/service/curl.php:151`
- Сигнатура: `function setTimeout(int $timeout)`
- Описание: Устанавливает, добавляет или сохраняет данные `timeout` в рамках этого метода класса `fan\core\service\curl`.

### `fan\core\service\curl::setCookies`

- Расположение: `_core/service/curl.php:164`
- Сигнатура: `function setCookies(mixed $cookies)`
- Описание: Устанавливает, добавляет или сохраняет данные `cookies` в рамках этого метода класса `fan\core\service\curl`.

### `fan\core\service\curl::getCookies`

- Расположение: `_core/service/curl.php:188`
- Сигнатура: `function getCookies(?string $key = null)`
- Описание: Получает, читает или вычисляет данные `cookies` в рамках этого метода класса `fan\core\service\curl`.

### `fan\core\service\curl::close`

- Расположение: `_core/service/curl.php:206`
- Сигнатура: `function close()`
- Описание: Выполняет логику `close` и возвращает вычисленный результат.

### `fan\core\service\curl::getRequestHeaders`

- Расположение: `_core/service/curl.php:221`
- Сигнатура: `function getRequestHeaders()`
- Описание: Получает, читает или вычисляет данные `request headers` в рамках этого метода класса `fan\core\service\curl`.

### `fan\core\service\curl::getInfo`

- Расположение: `_core/service/curl.php:233`
- Сигнатура: `function getInfo(int|float|null $option = null)`
- Описание: Получает, читает или вычисляет данные `info` в рамках этого метода класса `fan\core\service\curl`.

### `fan\core\service\curl::getError`

- Расположение: `_core/service/curl.php:243`
- Сигнатура: `function getError()`
- Описание: Получает, читает или вычисляет данные `error` в рамках этого метода класса `fan\core\service\curl`.

### `fan\core\service\curl::exec`

- Расположение: `_core/service/curl.php:256`
- Сигнатура: `function exec(mixed $postData = null, $allowExcept = true)`
- Описание: Выполняет логику `exec` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\curl::setSeparateResponse`

- Расположение: `_core/service/curl.php:320`
- Сигнатура: `function setSeparateResponse($separate = false)`
- Описание: Устанавливает, добавляет или сохраняет данные `separate response` в рамках этого метода класса `fan\core\service\curl`.

### `fan\core\service\curl::getResponseHeaders`

- Расположение: `_core/service/curl.php:333`
- Сигнатура: `function getResponseHeaders(?string $key = null)`
- Описание: Получает, читает или вычисляет данные `response headers` в рамках этого метода класса `fan\core\service\curl`.

### `fan\core\service\curl::getContent`

- Расположение: `_core/service/curl.php:343`
- Сигнатура: `function getContent()`
- Описание: Получает, читает или вычисляет данные `content` в рамках этого метода класса `fan\core\service\curl`.

### `fan\core\service\curl::_saveInstance`

- Расположение: `_core/service/curl.php:355`
- Сигнатура: `function _saveInstance()`
- Описание: Выполняет логику `save instance` и возвращает вычисленный результат.

### `fan\core\service\curl::_getSeparator`

- Расположение: `_core/service/curl.php:368`
- Сигнатура: `function _getSeparator(?string $data = null)`
- Описание: Выполняет логику `get separator` и возвращает вычисленный результат.

### `fan\core\service\curl::_convPostArray`

- Расположение: `_core/service/curl.php:388`
- Сигнатура: `function _convPostArray(&$optData, string $key, mixed $data)`
- Описание: Выполняет логику `conv post array` и возвращает вычисленный результат.

## `_core/service/database.php`

### `fan\core\service\database::__construct`

- Расположение: `_core/service/database.php:104`
- Сигнатура: `function __construct($connectionName = null, $extraKey = 0, $param = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\database`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\database::__destruct`

- Расположение: `_core/service/database.php:154`
- Сигнатура: `function __destruct()`
- Описание: Завершает работу объекта и выполняет отложенную очистку для этого метода класса `fan\core\service\database`.

### `fan\core\service\database::instance`

- Расположение: `_core/service/database.php:169`
- Сигнатура: `function instance(?string $connectionName = null, mixed $extraKey = 0)`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.

### `fan\core\service\database::instanceByParam`

- Расположение: `_core/service/database.php:193`
- Сигнатура: `function instanceByParam(mixed $param, $extraKey = 0)`
- Описание: Выполняет логику `instance by param` и возвращает вычисленный результат.

### `fan\core\service\database::close`

- Расположение: `_core/service/database.php:210`
- Сигнатура: `function close()`
- Описание: Выполняет workflow-логику `close`.

### `fan\core\service\database::getCurrentInstance`

- Расположение: `_core/service/database.php:228`
- Сигнатура: `function getCurrentInstance()`
- Описание: Получает, читает или вычисляет данные `current instance` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::getAllInstances`

- Расположение: `_core/service/database.php:238`
- Сигнатура: `function getAllInstances()`
- Описание: Получает, читает или вычисляет данные `all instances` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::commitAll`

- Расположение: `_core/service/database.php:248`
- Сигнатура: `function commitAll()`
- Описание: Выполняет workflow-логику `commit all`.

### `fan\core\service\database::rollbackAll`

- Расположение: `_core/service/database.php:259`
- Сигнатура: `function rollbackAll(bool $setError = true)`
- Описание: Выполняет workflow-логику `rollback all`.

### `fan\core\service\database::fixAll`

- Расположение: `_core/service/database.php:271`
- Сигнатура: `function fixAll(string $oper, bool $setError = true)`
- Описание: Выполняет workflow-логику `fix all`.

### `fan\core\service\database::getConnectionName`

- Расположение: `_core/service/database.php:293`
- Сигнатура: `function getConnectionName()`
- Описание: Получает, читает или вычисляет данные `connection name` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::getConnectionParam`

- Расположение: `_core/service/database.php:302`
- Сигнатура: `function getConnectionParam()`
- Описание: Получает, читает или вычисляет данные `connection param` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::setResultTypes`

- Расположение: `_core/service/database.php:314`
- Сигнатура: `function setResultTypes($resultType = MYSQL_ASSOC)`
- Описание: Устанавливает, добавляет или сохраняет данные `result types` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::getErrorMessage`

- Расположение: `_core/service/database.php:325`
- Сигнатура: `function getErrorMessage()`
- Описание: Получает, читает или вычисляет данные `error message` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::isError`

- Расположение: `_core/service/database.php:339`
- Сигнатура: `function isError()`
- Описание: Проверяет условие или валидирует данные `error` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\database::resetError`

- Расположение: `_core/service/database.php:349`
- Сигнатура: `function resetError()`
- Описание: Удаляет или сбрасывает состояние `error` для этого метода класса `fan\core\service\database`.

### `fan\core\service\database::startTransaction`

- Расположение: `_core/service/database.php:363`
- Сигнатура: `function startTransaction()`
- Описание: Запускает или обрабатывает workflow `transaction` для этого метода класса `fan\core\service\database`.

### `fan\core\service\database::setAutoTransaction`

- Расположение: `_core/service/database.php:379`
- Сигнатура: `function setAutoTransaction($autoTransaction)`
- Описание: Устанавливает, добавляет или сохраняет данные `auto transaction` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::setSavePoint`

- Расположение: `_core/service/database.php:392`
- Сигнатура: `function setSavePoint(string $savePoint)`
- Описание: Устанавливает, добавляет или сохраняет данные `save point` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::commit`

- Расположение: `_core/service/database.php:405`
- Сигнатура: `function commit()`
- Описание: Выполняет логику `commit` и возвращает вычисленный результат.

### `fan\core\service\database::rollback`

- Расположение: `_core/service/database.php:422`
- Сигнатура: `function rollback(?string $savePoint = null, bool $setError = true)`
- Описание: Выполняет логику `rollback` и возвращает вычисленный результат.

### `fan\core\service\database::runScenario`

- Расположение: `_core/service/database.php:445`
- Сигнатура: `function runScenario($scenario, $data = [])`
- Описание: Запускает или обрабатывает workflow `scenario` для этого метода класса `fan\core\service\database`.
- Побочные эффекты: может выбрасывать исключения; выполняет database операции

### `fan\core\service\database::execute`

- Расположение: `_core/service/database.php:474`
- Сигнатура: `function execute(string $sql, ?array $param = null)`
- Описание: Выполняет логику `execute` и возвращает вычисленный результат.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database::getInsertId`

- Расположение: `_core/service/database.php:505`
- Сигнатура: `function getInsertId()`
- Описание: Получает, читает или вычисляет данные `insert id` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::getOne`

- Расположение: `_core/service/database.php:519`
- Сигнатура: `function getOne(string $sql, string $fieldName, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `one` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::getRow`

- Расположение: `_core/service/database.php:533`
- Сигнатура: `function getRow(string $sql, ?array $param = null, ?int $resultType = null)`
- Описание: Получает, читает или вычисляет данные `row` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::getRowAssoc`

- Расположение: `_core/service/database.php:546`
- Сигнатура: `function getRowAssoc(string $sql, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `row assoc` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::getCol`

- Расположение: `_core/service/database.php:560`
- Сигнатура: `function getCol(string $sql, string $colName, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `col` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::getAssoc`

- Расположение: `_core/service/database.php:573`
- Сигнатура: `function getAssoc(string $sql, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `assoc` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::getAll`

- Расположение: `_core/service/database.php:586`
- Сигнатура: `function getAll(string $sql, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `all` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::getVersion`

- Расположение: `_core/service/database.php:596`
- Сигнатура: `function getVersion()`
- Описание: Получает, читает или вычисляет данные `version` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::getAllLimit`

- Расположение: `_core/service/database.php:611`
- Сигнатура: `function getAllLimit(string $sql, ?array $param = null, int|float $qtt = -1, int|float $offset = -1)`
- Описание: Получает, читает или вычисляет данные `all limit` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::getTableStatus`

- Расположение: `_core/service/database.php:623`
- Сигнатура: `function getTableStatus(string $tableName)`
- Описание: Получает, читает или вычисляет данные `table status` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::connectionClose`

- Расположение: `_core/service/database.php:633`
- Сигнатура: `function connectionClose()`
- Описание: Выполняет workflow-логику `connection close`.

### `fan\core\service\database::reconnect`

- Расположение: `_core/service/database.php:645`
- Сигнатура: `function reconnect(bool $makeException = true)`
- Описание: Выполняет логику `reconnect` и возвращает вычисленный результат.

### `fan\core\service\database::parseSql`

- Расположение: `_core/service/database.php:658`
- Сигнатура: `function parseSql(string $sql, array $param)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `sql` для этого метода класса `fan\core\service\database`.

### `fan\core\service\database::getParsedSql`

- Расположение: `_core/service/database.php:668`
- Сигнатура: `function getParsedSql()`
- Описание: Получает, читает или вычисляет данные `parsed sql` в рамках этого метода класса `fan\core\service\database`.

### `fan\core\service\database::fixError`

- Расположение: `_core/service/database.php:683`
- Сигнатура: `function fixError(\fan\core\service\database\base $engine, bool $makeException)`
- Описание: Выполняет логику `fix error` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения; логирует или сообщает об ошибках

### `fan\core\service\database::_getConnectionName`

- Расположение: `_core/service/database.php:717`
- Сигнатура: `function _getConnectionName(&$param)`
- Описание: Выполняет логику `get connection name` и возвращает вычисленный результат.

### `fan\core\service\database::_executeEngine`

- Расположение: `_core/service/database.php:743`
- Сигнатура: `function _executeEngine(string $methodName, string $sql, array $arguments)`
- Описание: Выполняет логику `execute engine` и возвращает вычисленный результат.

### `fan\core\service\database::_languageCorrection`

- Расположение: `_core/service/database.php:763`
- Сигнатура: `function _languageCorrection(string $sql, bool $isCoalesce = true)`
- Описание: Выполняет логику `language correction` и возвращает вычисленный результат.

### `fan\core\service\database::_checkSql`

- Расположение: `_core/service/database.php:793`
- Сигнатура: `function _checkSql(string $sql)`
- Описание: Выполняет логику `check sql` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\database::_setErrorMessage`

- Расположение: `_core/service/database.php:811`
- Сигнатура: `function _setErrorMessage(string $errorMessage)`
- Описание: Выполняет workflow-логику `set error message`.

### `fan\core\service\database::_fixExecuteTime`

- Расположение: `_core/service/database.php:824`
- Сигнатура: `function _fixExecuteTime(int|float $time, string $sql)`
- Описание: Выполняет workflow-логику `fix execute time`.

## `_core/service/database/adodb.php`

### `fan\core\service\database\adodb::__construct`

- Расположение: `_core/service/database/adodb.php:38`
- Сигнатура: `function __construct(\fan\core\base\service $facade, \fan\core\service\config\base $config)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\database\adodb`.

### `fan\core\service\database\adodb::qstr`

- Расположение: `_core/service/database/adodb.php:58`
- Сигнатура: `function qstr(string $s)`
- Описание: Выполняет логику `qstr` и возвращает вычисленный результат.

### `fan\core\service\database\adodb::start_transaction`

- Расположение: `_core/service/database/adodb.php:68`
- Сигнатура: `function start_transaction()`
- Описание: Запускает или обрабатывает workflow `transaction` для этого метода класса `fan\core\service\database\adodb`.

### `fan\core\service\database\adodb::commit`

- Расположение: `_core/service/database/adodb.php:78`
- Сигнатура: `function commit()`
- Описание: Выполняет workflow-логику `commit`.

### `fan\core\service\database\adodb::rollback`

- Расположение: `_core/service/database/adodb.php:88`
- Сигнатура: `function rollback()`
- Описание: Выполняет workflow-логику `rollback`.

### `fan\core\service\database\adodb::connectionClose`

- Расположение: `_core/service/database/adodb.php:98`
- Сигнатура: `function connectionClose()`
- Описание: Выполняет workflow-логику `connection close`.

### `fan\core\service\database\adodb::reconnect`

- Расположение: `_core/service/database/adodb.php:110`
- Сигнатура: `function reconnect($config)`
- Описание: Выполняет workflow-логику `reconnect`.

### `fan\core\service\database\adodb::execute`

- Расположение: `_core/service/database/adodb.php:123`
- Сигнатура: `function execute(string $sql, ?array $params = null)`
- Описание: Выполняет логику `execute` и возвращает вычисленный результат.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\adodb::getInsertId`

- Расположение: `_core/service/database/adodb.php:133`
- Сигнатура: `function getInsertId()`
- Описание: Получает, читает или вычисляет данные `insert id` в рамках этого метода класса `fan\core\service\database\adodb`.

### `fan\core\service\database\adodb::getOne`

- Расположение: `_core/service/database/adodb.php:150`
- Сигнатура: `function getOne(string $sql, ?array $params = null)`
- Описание: Получает, читает или вычисляет данные `one` в рамках этого метода класса `fan\core\service\database\adodb`.

### `fan\core\service\database\adodb::getRow`

- Расположение: `_core/service/database/adodb.php:163`
- Сигнатура: `function getRow(string $sql, ?array $params = null)`
- Описание: Получает, читает или вычисляет данные `row` в рамках этого метода класса `fan\core\service\database\adodb`.

### `fan\core\service\database\adodb::getRowAssoc`

- Расположение: `_core/service/database/adodb.php:176`
- Сигнатура: `function getRowAssoc(string $sql, ?array $params = null)`
- Описание: Получает, читает или вычисляет данные `row assoc` в рамках этого метода класса `fan\core\service\database\adodb`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\adodb::getCol`

- Расположение: `_core/service/database/adodb.php:196`
- Сигнатура: `function getCol(string $sql, ?array $params = null)`
- Описание: Получает, читает или вычисляет данные `col` в рамках этого метода класса `fan\core\service\database\adodb`.

### `fan\core\service\database\adodb::getAssoc`

- Расположение: `_core/service/database/adodb.php:209`
- Сигнатура: `function getAssoc(string $sql, ?array $params = null)`
- Описание: Получает, читает или вычисляет данные `assoc` в рамках этого метода класса `fan\core\service\database\adodb`.

### `fan\core\service\database\adodb::getAll`

- Расположение: `_core/service/database/adodb.php:222`
- Сигнатура: `function getAll(string $sql, ?array $params = null)`
- Описание: Получает, читает или вычисляет данные `all` в рамках этого метода класса `fan\core\service\database\adodb`.

### `fan\core\service\database\adodb::getAllLimit`

- Расположение: `_core/service/database/adodb.php:237`
- Сигнатура: `function getAllLimit(string $sql, ?array $params = null, $qtt = -1, $offset = -1)`
- Описание: Получает, читает или вычисляет данные `all limit` в рамках этого метода класса `fan\core\service\database\adodb`.

### `fan\core\service\database\adodb::getVersion`

- Расположение: `_core/service/database/adodb.php:256`
- Сигнатура: `function getVersion()`
- Описание: Получает, читает или вычисляет данные `version` в рамках этого метода класса `fan\core\service\database\adodb`.

### `fan\core\service\database\adodb::_handleSql`

- Расположение: `_core/service/database/adodb.php:271`
- Сигнатура: `function _handleSql(?string $method = null, ?string $sql = null, ?array $params = null, bool $retArr = true)`
- Описание: Выполняет логику `handle sql` и возвращает вычисленный результат.

### `fan\core\service\database\adodb::_logTime`

- Расположение: `_core/service/database/adodb.php:292`
- Сигнатура: `function _logTime($t, $sql)`
- Описание: Выполняет workflow-логику `log time`.
- Побочные эффекты: читает PHP superglobals

### `fan\core\service\database\adodb_error_handler`

- Расположение: `_core/service/database/adodb.php:314`
- Сигнатура: `function adodb_error_handler(string $dbType, string $operation, int|float $errorNum, string $errMsg, mixed $mainParam, mixed $addParam, object $obj)`
- Описание: Runs the adodb error handler workflow for the framework helper.
- Параметры: string $dbType Input value for the db type argument.; string $operation Input value for the operation argument.; int|float $errorNum Input value for the error num argument.; string $errMsg Input value for the err msg argument.; mixed $mainParam Input value for the main param argument.; mixed $addParam Input value for the add param argument.; object $obj Input value for the obj argument.
- Возвращает: void No value is returned.

## `_core/service/database/base.php`

### `fan\core\service\database\base::__construct`

- Расположение: `_core/service/database/base.php:58`
- Сигнатура: `function __construct(\fan\core\service\database $facade, array $param)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\database\base`.

### `fan\core\service\database\base::setFacade`

- Расположение: `_core/service/database/base.php:72`
- Сигнатура: `function setFacade(\fan\core\base\service $facade)`
- Описание: Устанавливает, добавляет или сохраняет данные `facade` в рамках этого метода класса `fan\core\service\database\base`.

### `fan\core\service\database\base::setResultTypes`

- Расположение: `_core/service/database/base.php:87`
- Сигнатура: `function setResultTypes($resultType = MYSQL_ASSOC)`
- Описание: Устанавливает, добавляет или сохраняет данные `result types` в рамках этого метода класса `fan\core\service\database\base`.

### `fan\core\service\database\base::reconnect`

- Расположение: `_core/service/database/base.php:103`
- Сигнатура: `function reconnect($param, $makeException = true);`
- Описание: Выполняет workflow-логику `reconnect`.

### `fan\core\service\database\base::getErrorData`

- Расположение: `_core/service/database/base.php:110`
- Сигнатура: `function getErrorData()`
- Описание: Получает, читает или вычисляет данные `error data` в рамках этого метода класса `fan\core\service\database\base`.

### `fan\core\service\database\base::resetError`

- Расположение: `_core/service/database/base.php:120`
- Сигнатура: `function resetError()`
- Описание: Удаляет или сбрасывает состояние `error` для этого метода класса `fan\core\service\database\base`.

### `fan\core\service\database\base::_isValidType`

- Расположение: `_core/service/database/base.php:133`
- Сигнатура: `function _isValidType(string $resultType)`
- Описание: Выполняет логику `is valid type` и возвращает вычисленный результат.

### `fan\core\service\database\base::_fixError`

- Расположение: `_core/service/database/base.php:154`
- Сигнатура: `function _fixError(int|float $operCode, string $operMessage, int|float $errorCode, string $errorMessage, bool $makeException = false)`
- Описание: Выполняет workflow-логику `fix error`.

## `_core/service/database/mysql.php`

### `fan\core\service\database\mysql::reconnect`

- Расположение: `_core/service/database/mysql.php:42`
- Сигнатура: `function reconnect(array $param, bool $makeException = true)`
- Описание: Выполняет логику `reconnect` и возвращает вычисленный результат.

### `fan\core\service\database\mysql::connectionClose`

- Расположение: `_core/service/database/mysql.php:110`
- Сигнатура: `function connectionClose()`
- Описание: Выполняет логику `connection close` и возвращает вычисленный результат.

### `fan\core\service\database\mysql::execute`

- Расположение: `_core/service/database/mysql.php:128`
- Сигнатура: `function execute(string $sql, ?array $param = null, mixed $resultType = null)`
- Описание: Выполняет логику `execute` и возвращает вычисленный результат.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysql::startTransaction`

- Расположение: `_core/service/database/mysql.php:183`
- Сигнатура: `function startTransaction()`
- Описание: Запускает или обрабатывает workflow `transaction` для этого метода класса `fan\core\service\database\mysql`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysql::setSavePoint`

- Расположение: `_core/service/database/mysql.php:195`
- Сигнатура: `function setSavePoint(string $savePoint)`
- Описание: Устанавливает, добавляет или сохраняет данные `save point` в рамках этого метода класса `fan\core\service\database\mysql`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysql::commit`

- Расположение: `_core/service/database/mysql.php:205`
- Сигнатура: `function commit()`
- Описание: Выполняет логику `commit` и возвращает вычисленный результат.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysql::rollback`

- Расположение: `_core/service/database/mysql.php:217`
- Сигнатура: `function rollback(?string $savePoint = null)`
- Описание: Выполняет логику `rollback` и возвращает вычисленный результат.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysql::getInsertId`

- Расположение: `_core/service/database/mysql.php:227`
- Сигнатура: `function getInsertId()`
- Описание: Получает, читает или вычисляет данные `insert id` в рамках этого метода класса `fan\core\service\database\mysql`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysql::getOne`

- Расположение: `_core/service/database/mysql.php:245`
- Сигнатура: `function getOne(string $sql, string $fieldName, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `one` в рамках этого метода класса `fan\core\service\database\mysql`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysql::getRow`

- Расположение: `_core/service/database/mysql.php:260`
- Сигнатура: `function getRow(string $sql, ?array $param = null, ?int $resultType = null)`
- Описание: Получает, читает или вычисляет данные `row` в рамках этого метода класса `fan\core\service\database\mysql`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysql::getRowAssoc`

- Расположение: `_core/service/database/mysql.php:274`
- Сигнатура: `function getRowAssoc(string $sql, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `row assoc` в рамках этого метода класса `fan\core\service\database\mysql`.

### `fan\core\service\database\mysql::getCol`

- Расположение: `_core/service/database/mysql.php:288`
- Сигнатура: `function getCol(string $sql, $colName, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `col` в рамках этого метода класса `fan\core\service\database\mysql`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysql::getAssoc`

- Расположение: `_core/service/database/mysql.php:308`
- Сигнатура: `function getAssoc(string $sql, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `assoc` в рамках этого метода класса `fan\core\service\database\mysql`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysql::getAll`

- Расположение: `_core/service/database/mysql.php:331`
- Сигнатура: `function getAll(string $sql, ?array $param = null, $resultType = null)`
- Описание: Получает, читает или вычисляет данные `all` в рамках этого метода класса `fan\core\service\database\mysql`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysql::getAllLimit`

- Расположение: `_core/service/database/mysql.php:348`
- Сигнатура: `function getAllLimit(string $sql, ?array $param = null, $qtt = -1, $offset = -1, $resultType = null)`
- Описание: Получает, читает или вычисляет данные `all limit` в рамках этого метода класса `fan\core\service\database\mysql`.

### `fan\core\service\database\mysql::getVersion`

- Расположение: `_core/service/database/mysql.php:362`
- Сигнатура: `function getVersion()`
- Описание: Получает, читает или вычисляет данные `version` в рамках этого метода класса `fan\core\service\database\mysql`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysql::getTableStatus`

- Расположение: `_core/service/database/mysql.php:378`
- Сигнатура: `function getTableStatus(string $tableName)`
- Описание: Получает, читает или вычисляет данные `table status` в рамках этого метода класса `fan\core\service\database\mysql`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysql::parseSql`

- Расположение: `_core/service/database/mysql.php:392`
- Сигнатура: `function parseSql(string $sql, array $param)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `sql` для этого метода класса `fan\core\service\database\mysql`.

### `fan\core\service\database\mysql::getParsedSql`

- Расположение: `_core/service/database/mysql.php:402`
- Сигнатура: `function getParsedSql()`
- Описание: Получает, читает или вычисляет данные `parsed sql` в рамках этого метода класса `fan\core\service\database\mysql`.

### `fan\core\service\database\mysql::_parseSql`

- Расположение: `_core/service/database/mysql.php:417`
- Сигнатура: `function _parseSql(string $sql, mixed $param, bool $saveResult)`
- Описание: Выполняет логику `parse sql` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

## `_core/service/database/mysqlImproved.php`

### `fan\core\service\database\mysqlImproved::reconnect`

- Расположение: `_core/service/database/mysqlImproved.php:31`
- Сигнатура: `function reconnect(array $param, bool $makeException = true)`
- Описание: Выполняет workflow-логику `reconnect`.

### `fan\core\service\database\mysqlImproved::connectionClose`

- Расположение: `_core/service/database/mysqlImproved.php:40`
- Сигнатура: `function connectionClose()`
- Описание: Выполняет логику `connection close` и возвращает вычисленный результат.

### `fan\core\service\database\mysqlImproved::execute`

- Расположение: `_core/service/database/mysqlImproved.php:54`
- Сигнатура: `function execute(string $sql, ?array $param = null, ?int $resultType = null)`
- Описание: Выполняет логику `execute` и возвращает вычисленный результат.

### `fan\core\service\database\mysqlImproved::startTransaction`

- Расположение: `_core/service/database/mysqlImproved.php:64`
- Сигнатура: `function startTransaction()`
- Описание: Запускает или обрабатывает workflow `transaction` для этого метода класса `fan\core\service\database\mysqlImproved`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlImproved::setSavePoint`

- Расположение: `_core/service/database/mysqlImproved.php:76`
- Сигнатура: `function setSavePoint(string $savePoint)`
- Описание: Устанавливает, добавляет или сохраняет данные `save point` в рамках этого метода класса `fan\core\service\database\mysqlImproved`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlImproved::commit`

- Расположение: `_core/service/database/mysqlImproved.php:86`
- Сигнатура: `function commit()`
- Описание: Выполняет логику `commit` и возвращает вычисленный результат.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlImproved::rollback`

- Расположение: `_core/service/database/mysqlImproved.php:98`
- Сигнатура: `function rollback(?string $savePoint = null)`
- Описание: Выполняет логику `rollback` и возвращает вычисленный результат.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlImproved::getInsertId`

- Расположение: `_core/service/database/mysqlImproved.php:108`
- Сигнатура: `function getInsertId()`
- Описание: Получает, читает или вычисляет данные `insert id` в рамках этого метода класса `fan\core\service\database\mysqlImproved`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlImproved::getOne`

- Расположение: `_core/service/database/mysqlImproved.php:126`
- Сигнатура: `function getOne(string $sql, string $fieldName, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `one` в рамках этого метода класса `fan\core\service\database\mysqlImproved`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlImproved::getRow`

- Расположение: `_core/service/database/mysqlImproved.php:141`
- Сигнатура: `function getRow(string $sql, ?array $param = null, ?int $resultType = null)`
- Описание: Получает, читает или вычисляет данные `row` в рамках этого метода класса `fan\core\service\database\mysqlImproved`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlImproved::getRowAssoc`

- Расположение: `_core/service/database/mysqlImproved.php:155`
- Сигнатура: `function getRowAssoc(string $sql, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `row assoc` в рамках этого метода класса `fan\core\service\database\mysqlImproved`.

### `fan\core\service\database\mysqlImproved::getCol`

- Расположение: `_core/service/database/mysqlImproved.php:169`
- Сигнатура: `function getCol(string $sql, $colName, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `col` в рамках этого метода класса `fan\core\service\database\mysqlImproved`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlImproved::getAssoc`

- Расположение: `_core/service/database/mysqlImproved.php:189`
- Сигнатура: `function getAssoc(string $sql, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `assoc` в рамках этого метода класса `fan\core\service\database\mysqlImproved`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlImproved::getAll`

- Расположение: `_core/service/database/mysqlImproved.php:212`
- Сигнатура: `function getAll(string $sql, ?array $param = null, $resultType = null)`
- Описание: Получает, читает или вычисляет данные `all` в рамках этого метода класса `fan\core\service\database\mysqlImproved`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlImproved::getAllLimit`

- Расположение: `_core/service/database/mysqlImproved.php:229`
- Сигнатура: `function getAllLimit(string $sql, ?array $param = null, $qtt = -1, $offset = -1, $resultType = null)`
- Описание: Получает, читает или вычисляет данные `all limit` в рамках этого метода класса `fan\core\service\database\mysqlImproved`.

### `fan\core\service\database\mysqlImproved::getVersion`

- Расположение: `_core/service/database/mysqlImproved.php:243`
- Сигнатура: `function getVersion()`
- Описание: Получает, читает или вычисляет данные `version` в рамках этого метода класса `fan\core\service\database\mysqlImproved`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlImproved::getTableStatus`

- Расположение: `_core/service/database/mysqlImproved.php:259`
- Сигнатура: `function getTableStatus(string $tableName)`
- Описание: Получает, читает или вычисляет данные `table status` в рамках этого метода класса `fan\core\service\database\mysqlImproved`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlImproved::parseSql`

- Расположение: `_core/service/database/mysqlImproved.php:273`
- Сигнатура: `function parseSql(string $sql, array $param)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `sql` для этого метода класса `fan\core\service\database\mysqlImproved`.

### `fan\core\service\database\mysqlImproved::getParsedSql`

- Расположение: `_core/service/database/mysqlImproved.php:283`
- Сигнатура: `function getParsedSql()`
- Описание: Получает, читает или вычисляет данные `parsed sql` в рамках этого метода класса `fan\core\service\database\mysqlImproved`.

## `_core/service/database/mysqlPdo.php`

### `fan\core\service\database\mysqlPdo::reconnect`

- Расположение: `_core/service/database/mysqlPdo.php:31`
- Сигнатура: `function reconnect(array $param, bool $makeException = true)`
- Описание: Выполняет workflow-логику `reconnect`.

### `fan\core\service\database\mysqlPdo::connectionClose`

- Расположение: `_core/service/database/mysqlPdo.php:40`
- Сигнатура: `function connectionClose()`
- Описание: Выполняет логику `connection close` и возвращает вычисленный результат.

### `fan\core\service\database\mysqlPdo::execute`

- Расположение: `_core/service/database/mysqlPdo.php:54`
- Сигнатура: `function execute(string $sql, ?array $param = null, ?int $resultType = null)`
- Описание: Выполняет логику `execute` и возвращает вычисленный результат.

### `fan\core\service\database\mysqlPdo::startTransaction`

- Расположение: `_core/service/database/mysqlPdo.php:64`
- Сигнатура: `function startTransaction()`
- Описание: Запускает или обрабатывает workflow `transaction` для этого метода класса `fan\core\service\database\mysqlPdo`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlPdo::setSavePoint`

- Расположение: `_core/service/database/mysqlPdo.php:76`
- Сигнатура: `function setSavePoint(string $savePoint)`
- Описание: Устанавливает, добавляет или сохраняет данные `save point` в рамках этого метода класса `fan\core\service\database\mysqlPdo`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlPdo::commit`

- Расположение: `_core/service/database/mysqlPdo.php:86`
- Сигнатура: `function commit()`
- Описание: Выполняет логику `commit` и возвращает вычисленный результат.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlPdo::rollback`

- Расположение: `_core/service/database/mysqlPdo.php:98`
- Сигнатура: `function rollback(?string $savePoint = null)`
- Описание: Выполняет логику `rollback` и возвращает вычисленный результат.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlPdo::getInsertId`

- Расположение: `_core/service/database/mysqlPdo.php:108`
- Сигнатура: `function getInsertId()`
- Описание: Получает, читает или вычисляет данные `insert id` в рамках этого метода класса `fan\core\service\database\mysqlPdo`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlPdo::getOne`

- Расположение: `_core/service/database/mysqlPdo.php:126`
- Сигнатура: `function getOne(string $sql, string $fieldName, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `one` в рамках этого метода класса `fan\core\service\database\mysqlPdo`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlPdo::getRow`

- Расположение: `_core/service/database/mysqlPdo.php:141`
- Сигнатура: `function getRow(string $sql, ?array $param = null, ?int $resultType = null)`
- Описание: Получает, читает или вычисляет данные `row` в рамках этого метода класса `fan\core\service\database\mysqlPdo`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlPdo::getRowAssoc`

- Расположение: `_core/service/database/mysqlPdo.php:155`
- Сигнатура: `function getRowAssoc(string $sql, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `row assoc` в рамках этого метода класса `fan\core\service\database\mysqlPdo`.

### `fan\core\service\database\mysqlPdo::getCol`

- Расположение: `_core/service/database/mysqlPdo.php:169`
- Сигнатура: `function getCol(string $sql, $colName, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `col` в рамках этого метода класса `fan\core\service\database\mysqlPdo`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlPdo::getAssoc`

- Расположение: `_core/service/database/mysqlPdo.php:189`
- Сигнатура: `function getAssoc(string $sql, ?array $param = null)`
- Описание: Получает, читает или вычисляет данные `assoc` в рамках этого метода класса `fan\core\service\database\mysqlPdo`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlPdo::getAll`

- Расположение: `_core/service/database/mysqlPdo.php:212`
- Сигнатура: `function getAll(string $sql, ?array $param = null, $resultType = null)`
- Описание: Получает, читает или вычисляет данные `all` в рамках этого метода класса `fan\core\service\database\mysqlPdo`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlPdo::getAllLimit`

- Расположение: `_core/service/database/mysqlPdo.php:229`
- Сигнатура: `function getAllLimit(string $sql, ?array $param = null, $qtt = -1, $offset = -1, $resultType = null)`
- Описание: Получает, читает или вычисляет данные `all limit` в рамках этого метода класса `fan\core\service\database\mysqlPdo`.

### `fan\core\service\database\mysqlPdo::getVersion`

- Расположение: `_core/service/database/mysqlPdo.php:243`
- Сигнатура: `function getVersion()`
- Описание: Получает, читает или вычисляет данные `version` в рамках этого метода класса `fan\core\service\database\mysqlPdo`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlPdo::getTableStatus`

- Расположение: `_core/service/database/mysqlPdo.php:259`
- Сигнатура: `function getTableStatus(string $tableName)`
- Описание: Получает, читает или вычисляет данные `table status` в рамках этого метода класса `fan\core\service\database\mysqlPdo`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\database\mysqlPdo::parseSql`

- Расположение: `_core/service/database/mysqlPdo.php:273`
- Сигнатура: `function parseSql(string $sql, array $param)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `sql` для этого метода класса `fan\core\service\database\mysqlPdo`.

### `fan\core\service\database\mysqlPdo::getParsedSql`

- Расположение: `_core/service/database/mysqlPdo.php:283`
- Сигнатура: `function getParsedSql()`
- Описание: Получает, читает или вычисляет данные `parsed sql` в рамках этого метода класса `fan\core\service\database\mysqlPdo`.

## `_core/service/date.php`

### `fan\core\service\date::__construct`

- Расположение: `_core/service/date.php:69`
- Сигнатура: `function __construct(\DateTime $date, $format, $isTime, $timezone, $save)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\date`.

### `fan\core\service\date::instance`

- Расположение: `_core/service/date.php:94`
- Сигнатура: `function instance(?string $date = null, mixed $format = null, mixed $timezone = null, bool $save = true)`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\date::_getGlobalConfig`

- Расположение: `_core/service/date.php:134`
- Сигнатура: `function _getGlobalConfig()`
- Описание: Выполняет логику `get global config` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\date::_getDate`

- Расположение: `_core/service/date.php:153`
- Сигнатура: `function _getDate(\fan\core\service\config\row $config, string $date, string $format, string $timezone)`
- Описание: Выполняет логику `get date` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\date::get`

- Расположение: `_core/service/date.php:182`
- Сигнатура: `function get(?string $format = null)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\service\date`.

### `fan\core\service\date::getCustom`

- Расположение: `_core/service/date.php:194`
- Сигнатура: `function getCustom(string $pattern)`
- Описание: Получает, читает или вычисляет данные `custom` в рамках этого метода класса `fan\core\service\date`.

### `fan\core\service\date::setFormat`

- Расположение: `_core/service/date.php:208`
- Сигнатура: `function setFormat(string $format)`
- Описание: Устанавливает, добавляет или сохраняет данные `format` в рамках этого метода класса `fan\core\service\date`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\date::isTime`

- Расположение: `_core/service/date.php:225`
- Сигнатура: `function isTime()`
- Описание: Проверяет условие или валидирует данные `time` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\date::getDateAsArray`

- Расположение: `_core/service/date.php:235`
- Сигнатура: `function getDateAsArray()`
- Описание: Получает, читает или вычисляет данные `date as array` в рамках этого метода класса `fan\core\service\date`.

### `fan\core\service\date::getTimeStamp`

- Расположение: `_core/service/date.php:251`
- Сигнатура: `function getTimeStamp()`
- Описание: Получает, читает или вычисляет данные `time stamp` в рамках этого метода класса `fan\core\service\date`.

### `fan\core\service\date::getDifference`

- Расположение: `_core/service/date.php:264`
- Сигнатура: `function getDifference(string $date2, bool $abs = true)`
- Описание: Получает, читает или вычисляет данные `difference` в рамках этого метода класса `fan\core\service\date`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\date::shiftDate`

- Расположение: `_core/service/date.php:278`
- Сигнатура: `function shiftDate(int|float $shift)`
- Описание: Выполняет логику `shift date` и возвращает вычисленный результат.

### `fan\core\service\date::modify`

- Расположение: `_core/service/date.php:290`
- Сигнатура: `function modify(string $modify)`
- Описание: Выполняет логику `modify` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\date::toArray`

- Расположение: `_core/service/date.php:311`
- Сигнатура: `function toArray()`
- Описание: Выполняет логику `to array` и возвращает вычисленный результат.

### `fan\core\service\date::isValid`

- Расположение: `_core/service/date.php:321`
- Сигнатура: `function isValid()`
- Описание: Проверяет условие или валидирует данные `valid` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\date::_saveInstance`

- Расположение: `_core/service/date.php:333`
- Сигнатура: `function _saveInstance()`
- Описание: Выполняет логику `save instance` и возвращает вычисленный результат.

### `fan\core\service\date::_getPattern`

- Расположение: `_core/service/date.php:350`
- Сигнатура: `function _getPattern(mixed $format = null)`
- Описание: Выполняет логику `get pattern` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\date::__toString`

- Расположение: `_core/service/date.php:366`
- Сигнатура: `function __toString()`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\date`.

## `_core/service/debug.php`

### `fan\core\service\debug::__construct`

- Расположение: `_core/service/debug.php:35`
- Сигнатура: `function __construct($allowIni = true)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\debug`.
- Побочные эффекты: читает PHP superglobals

### `fan\core\service\debug::setExtFiles`

- Расположение: `_core/service/debug.php:51`
- Сигнатура: `function setExtFiles($root, $mode)`
- Описание: Устанавливает, добавляет или сохраняет данные `ext files` в рамках этого метода класса `fan\core\service\debug`.

### `fan\core\service\debug::setBlockCode`

- Расположение: `_core/service/debug.php:81`
- Сигнатура: `function setBlockCode($name, $code)`
- Описание: Устанавливает, добавляет или сохраняет данные `block code` в рамках этого метода класса `fan\core\service\debug`.

### `fan\core\service\debug::wrapHtmlCode`

- Расположение: `_core/service/debug.php:94`
- Сигнатура: `function wrapHtmlCode(string $code, \fan\core\block\base $block)`
- Описание: Выполняет логику `wrap html code` и возвращает вычисленный результат.

### `fan\core\service\debug::getSecondDebugCode`

- Расположение: `_core/service/debug.php:116`
- Сигнатура: `function getSecondDebugCode($blockInfo, $title)`
- Описание: Получает, читает или вычисляет данные `second debug code` в рамках этого метода класса `fan\core\service\debug`.

### `fan\core\service\debug::getSecondDebugRow`

- Расположение: `_core/service/debug.php:155`
- Сигнатура: `function getSecondDebugRow(\fan\core\block\base $block, $incl, $isView)`
- Описание: Получает, читает или вычисляет данные `second debug row` в рамках этого метода класса `fan\core\service\debug`.

### `fan\core\service\debug::_getBlockDetail`

- Расположение: `_core/service/debug.php:199`
- Сигнатура: `function _getBlockDetail(\fan\core\block\base $block)`
- Описание: Выполняет логику `get block detail` и возвращает вычисленный результат.

### `fan\core\service\debug::_reduceMetaArray`

- Расположение: `_core/service/debug.php:241`
- Сигнатура: `function _reduceMetaArray(array $meta)`
- Описание: Выполняет логику `reduce meta array` и возвращает вычисленный результат.

### `fan\core\service\debug::_getFileInfo`

- Расположение: `_core/service/debug.php:260`
- Сигнатура: `function _getFileInfo(string $label, string $file)`
- Описание: Выполняет логику `get file info` и возвращает вычисленный результат.

### `fan\core\service\debug::_getParentInfo`

- Расположение: `_core/service/debug.php:274`
- Сигнатура: `function _getParentInfo(ReflectionClass $refl, $isParent)`
- Описание: Выполняет логику `get parent info` и возвращает вычисленный результат.

### `fan\core\service\debug::_getMetaData`

- Расположение: `_core/service/debug.php:303`
- Сигнатура: `function _getMetaData(string $label, array $meta, mixed $resultMeta)`
- Описание: Выполняет логику `get meta data` и возвращает вычисленный результат.

### `fan\core\service\debug::_showMetaArray`

- Расположение: `_core/service/debug.php:320`
- Сигнатура: `function _showMetaArray(array $meta, mixed $resultMeta, array $keys)`
- Описание: Выполняет логику `show meta array` и возвращает вычисленный результат.

### `fan\core\service\debug::_checkRedefine`

- Расположение: `_core/service/debug.php:359`
- Сигнатура: `function _checkRedefine(mixed $resultMeta, array $keys, $key, $val)`
- Описание: Выполняет логику `check redefine` и возвращает вычисленный результат.

### `fan\core\service\debug::_correctPath`

- Расположение: `_core/service/debug.php:379`
- Сигнатура: `function _correctPath(string $path)`
- Описание: Выполняет логику `correct path` и возвращает вычисленный результат.

## `_core/service/email.php`

### `fan\core\service\email::__construct`

- Расположение: `_core/service/email.php:43`
- Сигнатура: `function __construct($instName)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\email`.

### `fan\core\service\email::instance`

- Расположение: `_core/service/email.php:73`
- Сигнатура: `function instance($instName = 'default')`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\email::setFrom`

- Расположение: `_core/service/email.php:94`
- Сигнатура: `function setFrom(string $emailFrom, string $nameFrom = '')`
- Описание: Устанавливает, добавляет или сохраняет данные `from` в рамках этого метода класса `fan\core\service\email`.

### `fan\core\service\email::clearAllRecipients`

- Расположение: `_core/service/email.php:107`
- Сигнатура: `function clearAllRecipients()`
- Описание: Удаляет или сбрасывает состояние `all recipients` для этого метода класса `fan\core\service\email`.

### `fan\core\service\email::addCc`

- Расположение: `_core/service/email.php:123`
- Сигнатура: `function addCc(string $address, string $name = '')`
- Описание: Applies cc to the current component.
- Параметры: string $address Input value for the address argument.; string $name Logical name of the value or component being addressed.
- Возвращает: static Returns the current instance for fluent chaining.

### `fan\core\service\email::addBcc`

- Расположение: `_core/service/email.php:139`
- Сигнатура: `function addBcc(string $address, string $name = '')`
- Описание: Applies bcc to the current component.
- Параметры: string $address Input value for the address argument.; string $name Logical name of the value or component being addressed.
- Возвращает: static Returns the current instance for fluent chaining.

### `fan\core\service\email::addReplyTo`

- Расположение: `_core/service/email.php:155`
- Сигнатура: `function addReplyTo(string $address, string $name = '')`
- Описание: Applies reply to to the current component.
- Параметры: string $address Input value for the address argument.; string $name Logical name of the value or component being addressed.
- Возвращает: static Returns the current instance for fluent chaining.

### `fan\core\service\email::addAttachment`

- Расположение: `_core/service/email.php:173`
- Сигнатура: `function addAttachment($path, string $name = '', string $encoding = 'base64', string $type = 'application/octet-stream')`
- Описание: Applies attachment to the current component.
- Параметры: mixed $path Filesystem or URL path used by the operation.; string $name Logical name of the value or component being addressed.; string $encoding Input value for the encoding argument.; string $type Type discriminator that selects the required behavior.
- Возвращает: static Returns the current instance for fluent chaining.

### `fan\core\service\email::send`

- Расположение: `_core/service/email.php:192`
- Сигнатура: `function send(string $subj, string $body, string $emailTo, string $nameTo = '', bool $isHtml = false)`
- Описание: Выполняет логику `send` и возвращает вычисленный результат.

### `fan\core\service\email::sendTemplate`

- Расположение: `_core/service/email.php:208`
- Сигнатура: `function sendTemplate(string $templateName, mixed $placeholders, string $emailTo, string $nameTo = '', bool $isHtml = true)`
- Описание: Выполняет логику `send template` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения

### `fan\core\service\email::sendTemplatePlain`

- Расположение: `_core/service/email.php:251`
- Сигнатура: `function sendTemplatePlain(string $templateName, array $placeholders, string $emailTo, string $nameTo = '', bool $isHtml = false)`
- Описание: Выполняет логику `send template plain` и возвращает вычисленный результат.

### `fan\core\service\email::getInstanceName`

- Расположение: `_core/service/email.php:275`
- Сигнатура: `function getInstanceName()`
- Описание: Получает, читает или вычисляет данные `instance name` в рамках этого метода класса `fan\core\service\email`.

### `fan\core\service\email::_checkFilename`

- Расположение: `_core/service/email.php:287`
- Сигнатура: `function _checkFilename(&$templateName)`
- Описание: Выполняет логику `check filename` и возвращает вычисленный результат.

### `fan\core\service\email::_validEmail`

- Расположение: `_core/service/email.php:318`
- Сигнатура: `function _validEmail(string $email)`
- Описание: Выполняет логику `valid email` и возвращает вычисленный результат.

### `fan\core\service\email::_recodingText`

- Расположение: `_core/service/email.php:330`
- Сигнатура: `function _recodingText(string $src, string $code)`
- Описание: Выполняет логику `recoding text` и возвращает вычисленный результат.

## `_core/service/email/phpmailer.php`

### `fan\core\service\email\phpmailer::__construct`

- Расположение: `_core/service/email/phpmailer.php:36`
- Сигнатура: `function __construct()`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\email\phpmailer`.

### `fan\core\service\email\phpmailer::setFacade`

- Расположение: `_core/service/email/phpmailer.php:54`
- Сигнатура: `function setFacade(\fan\core\service\email $facade)`
- Описание: Устанавливает, добавляет или сохраняет данные `facade` в рамках этого метода класса `fan\core\service\email\phpmailer`.

### `fan\core\service\email\phpmailer::autholoadMailer`

- Расположение: `_core/service/email/phpmailer.php:102`
- Сигнатура: `function autholoadMailer(string $className)`
- Описание: Выполняет workflow-логику `authoload mailer`.

### `fan\core\service\email\phpmailer::setFrom`

- Расположение: `_core/service/email/phpmailer.php:120`
- Сигнатура: `function setFrom(string $emailFrom, string $nameFrom = '')`
- Описание: Устанавливает, добавляет или сохраняет данные `from` в рамках этого метода класса `fan\core\service\email\phpmailer`.

### `fan\core\service\email\phpmailer::send`

- Расположение: `_core/service/email/phpmailer.php:139`
- Сигнатура: `function send(string $subj, string $body, string $emailTo, string $nameTo = '', bool $isHtml = false)`
- Описание: Выполняет логику `send` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках

### `fan\core\service\email\phpmailer::__call`

- Расположение: `_core/service/email/phpmailer.php:171`
- Сигнатура: `function __call($method, $args)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\email\phpmailer`.

## `_core/service/entity.php`

### `fan\core\service\entity::__construct`

- Расположение: `_core/service/entity.php:44`
- Сигнатура: `function __construct($collection = 0)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\entity`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\entity::instance`

- Расположение: `_core/service/entity.php:83`
- Сигнатура: `function instance($collection = 0)`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.

### `fan\core\service\entity::__get`

- Расположение: `_core/service/entity.php:99`
- Сигнатура: `function __get($name)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\entity`.

### `fan\core\service\entity::get`

- Расположение: `_core/service/entity.php:115`
- Сигнатура: `function get(string $name, array $param = [])`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\service\entity`.

### `fan\core\service\entity::getAnonymous`

- Расположение: `_core/service/entity.php:139`
- Сигнатура: `function getAnonymous(string $class, array $param = [])`
- Описание: Получает, читает или вычисляет данные `anonymous` в рамках этого метода класса `fan\core\service\entity`.

### `fan\core\service\entity::getEntityByTable`

- Расположение: `_core/service/entity.php:153`
- Сигнатура: `function getEntityByTable(string $tableName, ?string $connectionName = null, bool $force = false)`
- Описание: Получает, читает или вычисляет данные `entity by table` в рамках этого метода класса `fan\core\service\entity`.

### `fan\core\service\entity::getSqlDir`

- Расположение: `_core/service/entity.php:172`
- Сигнатура: `function getSqlDir()`
- Описание: Получает, читает или вычисляет данные `sql dir` в рамках этого метода класса `fan\core\service\entity`.

### `fan\core\service\entity::getNsPrefix`

- Расположение: `_core/service/entity.php:183`
- Сигнатура: `function getNsPrefix()`
- Описание: Получает, читает или вычисляет данные `ns prefix` в рамках этого метода класса `fan\core\service\entity`.

### `fan\core\service\entity::getFileNsSuffix`

- Расположение: `_core/service/entity.php:193`
- Сигнатура: `function getFileNsSuffix()`
- Описание: Получает, читает или вычисляет данные `file ns suffix` в рамках этого метода класса `fan\core\service\entity`.

### `fan\core\service\entity::getCollectionKey`

- Расположение: `_core/service/entity.php:204`
- Сигнатура: `function getCollectionKey()`
- Описание: Получает, читает или вычисляет данные `collection key` в рамках этого метода класса `fan\core\service\entity`.

### `fan\core\service\entity::getDescription`

- Расположение: `_core/service/entity.php:217`
- Сигнатура: `function getDescription(\fan\core\base\model\entity $entity, array $param = [])`
- Описание: Получает, читает или вычисляет данные `description` в рамках этого метода класса `fan\core\service\entity`.

### `fan\core\service\entity::getDesigner`

- Расположение: `_core/service/entity.php:232`
- Сигнатура: `function getDesigner(\fan\core\base\model\entity $entity, string $type = 'select')`
- Описание: Получает, читает или вычисляет данные `designer` в рамках этого метода класса `fan\core\service\entity`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\entity::getSnippet`

- Расположение: `_core/service/entity.php:251`
- Сигнатура: `function getSnippet(\fan\core\service\entity\designer\snippety $snippety, $query, $srcCondition, $callback)`
- Описание: Получает, читает или вычисляет данные `snippet` в рамках этого метода класса `fan\core\service\entity`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\entity::getEncapsulant`

- Расположение: `_core/service/entity.php:263`
- Сигнатура: `function getEncapsulant(?string $class = null)`
- Описание: Получает, читает или вычисляет данные `encapsulant` в рамках этого метода класса `fan\core\service\entity`.

### `fan\core\service\entity::_getEntity`

- Расположение: `_core/service/entity.php:281`
- Сигнатура: `function _getEntity(string $class, array $param, ?string $name = null)`
- Описание: Выполняет логику `get entity` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\entity::_getConfigParam`

- Расположение: `_core/service/entity.php:301`
- Сигнатура: `function _getConfigParam(string $key)`
- Описание: Выполняет логику `get config param` и возвращает вычисленный результат.

### `fan\core\service\entity::_getNameByTable`

- Расположение: `_core/service/entity.php:318`
- Сигнатура: `function _getNameByTable($tableName, $connectionName, $force)`
- Описание: Выполняет логику `get name by table` и возвращает вычисленный результат.

### `fan\core\service\entity::_getDelegate`

- Расположение: `_core/service/entity.php:375`
- Сигнатура: `function _getDelegate($name)`
- Описание: Выполняет логику `get delegate` и возвращает вычисленный результат.

## `_core/service/entity/description.php`

### `fan\core\service\entity\description::__construct`

- Расположение: `_core/service/entity/description.php:89`
- Сигнатура: `function __construct(\fan\core\base\model\entity $entity, $param)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\entity\description`.

### `fan\core\service\entity\description::__set`

- Расположение: `_core/service/entity/description.php:107`
- Сигнатура: `function __set($key, $value)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\entity\description`.

### `fan\core\service\entity\description::__get`

- Расположение: `_core/service/entity/description.php:119`
- Сигнатура: `function __get($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\entity\description`.

### `fan\core\service\entity\description::__call`

- Расположение: `_core/service/entity/description.php:133`
- Сигнатура: `function __call($method, $args)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\entity\description`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\entity\description::get`

- Расположение: `_core/service/entity/description.php:155`
- Сигнатура: `function get(string $key, $force = false)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\service\entity\description`.

### `fan\core\service\entity\description::set`

- Расположение: `_core/service/entity/description.php:174`
- Сигнатура: `function set(string $key, mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\service\entity\description`.

### `fan\core\service\entity\description::setComment`

- Расположение: `_core/service/entity/description.php:194`
- Сигнатура: `function setComment(string $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `comment` в рамках этого метода класса `fan\core\service\entity\description`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\entity\description::isTableExists`

- Расположение: `_core/service/entity/description.php:210`
- Сигнатура: `function isTableExists()`
- Описание: Проверяет условие или валидирует данные `table exists` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\entity\description::getTableName`

- Расположение: `_core/service/entity/description.php:220`
- Сигнатура: `function getTableName()`
- Описание: Получает, читает или вычисляет данные `table name` в рамках этого метода класса `fan\core\service\entity\description`.

### `fan\core\service\entity\description::toArray`

- Расположение: `_core/service/entity/description.php:232`
- Сигнатура: `function toArray()`
- Описание: Выполняет логику `to array` и возвращает вычисленный результат.

### `fan\core\service\entity\description::getEntity`

- Расположение: `_core/service/entity/description.php:242`
- Сигнатура: `function getEntity()`
- Описание: Получает, читает или вычисляет данные `entity` в рамках этого метода класса `fan\core\service\entity\description`.

### `fan\core\service\entity\description::_checkPropertyName`

- Расположение: `_core/service/entity/description.php:258`
- Сигнатура: `function _checkPropertyName(string $propName, bool $allowException = true)`
- Описание: Выполняет логику `check property name` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\entity\description::_getDescriptor`

- Расположение: `_core/service/entity/description.php:274`
- Сигнатура: `function _getDescriptor()`
- Описание: Выполняет логику `get descriptor` и возвращает вычисленный результат.

### `fan\core\service\entity\description::_defineDynamicProperty`

- Расположение: `_core/service/entity/description.php:294`
- Сигнатура: `function _defineDynamicProperty(array $param = [])`
- Описание: Выполняет логику `define dynamic property` и возвращает вычисленный результат.

### `fan\core\service\entity\description::_loadDynamicProperty`

- Расположение: `_core/service/entity/description.php:315`
- Сигнатура: `function _loadDynamicProperty($key = null, $force = false)`
- Описание: Выполняет логику `load dynamic property` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\entity\description::_saveCacheFile`

- Расположение: `_core/service/entity/description.php:346`
- Сигнатура: `function _saveCacheFile()`
- Описание: Выполняет логику `save cache file` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой

### `fan\core\service\entity\description::_getCacheFileName`

- Расположение: `_core/service/entity/description.php:368`
- Сигнатура: `function _getCacheFileName()`
- Описание: Выполняет логику `get cache file name` и возвращает вычисленный результат.

## `_core/service/entity/descriptor.php`

### `fan\core\service\entity\descriptor::__construct`

- Расположение: `_core/service/entity/descriptor.php:43`
- Сигнатура: `function __construct(\fan\core\service\entity\description $description)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\entity\descriptor`.

### `fan\core\service\entity\descriptor::getFields`

- Расположение: `_core/service/entity/descriptor.php:59`
- Сигнатура: `function getFields();`
- Описание: Получает, читает или вычисляет данные `fields` в рамках этого метода класса `fan\core\service\entity\descriptor`.

### `fan\core\service\entity\descriptor::getPrimeryKey`

- Расположение: `_core/service/entity/descriptor.php:65`
- Сигнатура: `function getPrimeryKey();`
- Описание: Получает, читает или вычисляет данные `primery key` в рамках этого метода класса `fan\core\service\entity\descriptor`.

### `fan\core\service\entity\descriptor::getKeys`

- Расположение: `_core/service/entity/descriptor.php:71`
- Сигнатура: `function getKeys();`
- Описание: Получает, читает или вычисляет данные `keys` в рамках этого метода класса `fan\core\service\entity\descriptor`.

### `fan\core\service\entity\descriptor::getRelations`

- Расположение: `_core/service/entity/descriptor.php:77`
- Сигнатура: `function getRelations();`
- Описание: Получает, читает или вычисляет данные `relations` в рамках этого метода класса `fan\core\service\entity\descriptor`.

### `fan\core\service\entity\descriptor::getEngine`

- Расположение: `_core/service/entity/descriptor.php:83`
- Сигнатура: `function getEngine();`
- Описание: Получает, читает или вычисляет данные `engine` в рамках этого метода класса `fan\core\service\entity\descriptor`.

### `fan\core\service\entity\descriptor::getCreateTime`

- Расположение: `_core/service/entity/descriptor.php:89`
- Сигнатура: `function getCreateTime();`
- Описание: Получает, читает или вычисляет данные `create time` в рамках этого метода класса `fan\core\service\entity\descriptor`.

### `fan\core\service\entity\descriptor::getTableCollation`

- Расположение: `_core/service/entity/descriptor.php:95`
- Сигнатура: `function getTableCollation();`
- Описание: Получает, читает или вычисляет данные `table collation` в рамках этого метода класса `fan\core\service\entity\descriptor`.

### `fan\core\service\entity\descriptor::getComment`

- Расположение: `_core/service/entity/descriptor.php:101`
- Сигнатура: `function getComment();`
- Описание: Получает, читает или вычисляет данные `comment` в рамках этого метода класса `fan\core\service\entity\descriptor`.

## `_core/service/entity/descriptor/mysql.php`

### `fan\core\service\entity\descriptor\mysql::getPrimeryKey`

- Расположение: `_core/service/entity/descriptor/mysql.php:41`
- Сигнатура: `function getPrimeryKey()`
- Описание: Получает, читает или вычисляет данные `primery key` в рамках этого метода класса `fan\core\service\entity\descriptor\mysql`.

### `fan\core\service\entity\descriptor\mysql::getKeys`

- Расположение: `_core/service/entity/descriptor/mysql.php:57`
- Сигнатура: `function getKeys()`
- Описание: Получает, читает или вычисляет данные `keys` в рамках этого метода класса `fan\core\service\entity\descriptor\mysql`.

### `fan\core\service\entity\descriptor\mysql::_resetDefaultVal`

- Расположение: `_core/service/entity/descriptor/mysql.php:89`
- Сигнатура: `function _resetDefaultVal(&$field)`
- Описание: Выполняет логику `reset default val` и возвращает вычисленный результат.

### `fan\core\service\entity\descriptor\mysql::_getKeys`

- Расположение: `_core/service/entity/descriptor/mysql.php:106`
- Сигнатура: `function _getKeys()`
- Описание: Выполняет логику `get keys` и возвращает вычисленный результат.
- Побочные эффекты: выполняет database операции

## `_core/service/entity/descriptor/mysql/direct.php`

### `fan\core\service\entity\descriptor\mysql\direct::isTableExists`

- Расположение: `_core/service/entity/descriptor/mysql/direct.php:37`
- Сигнатура: `function isTableExists()`
- Описание: Проверяет условие или валидирует данные `table exists` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: выполняет database операции

### `fan\core\service\entity\descriptor\mysql\direct::getFields`

- Расположение: `_core/service/entity/descriptor/mysql/direct.php:47`
- Сигнатура: `function getFields()`
- Описание: Получает, читает или вычисляет данные `fields` в рамках этого метода класса `fan\core\service\entity\descriptor\mysql\direct`.

### `fan\core\service\entity\descriptor\mysql\direct::getRelations`

- Расположение: `_core/service/entity/descriptor/mysql/direct.php:99`
- Сигнатура: `function getRelations()`
- Описание: Получает, читает или вычисляет данные `relations` в рамках этого метода класса `fan\core\service\entity\descriptor\mysql\direct`.

### `fan\core\service\entity\descriptor\mysql\direct::getEngine`

- Расположение: `_core/service/entity/descriptor/mysql/direct.php:139`
- Сигнатура: `function getEngine()`
- Описание: Получает, читает или вычисляет данные `engine` в рамках этого метода класса `fan\core\service\entity\descriptor\mysql\direct`.

### `fan\core\service\entity\descriptor\mysql\direct::getCreateTime`

- Расположение: `_core/service/entity/descriptor/mysql/direct.php:149`
- Сигнатура: `function getCreateTime()`
- Описание: Получает, читает или вычисляет данные `create time` в рамках этого метода класса `fan\core\service\entity\descriptor\mysql\direct`.

### `fan\core\service\entity\descriptor\mysql\direct::getTableCollation`

- Расположение: `_core/service/entity/descriptor/mysql/direct.php:159`
- Сигнатура: `function getTableCollation()`
- Описание: Получает, читает или вычисляет данные `table collation` в рамках этого метода класса `fan\core\service\entity\descriptor\mysql\direct`.

### `fan\core\service\entity\descriptor\mysql\direct::getComment`

- Расположение: `_core/service/entity/descriptor/mysql/direct.php:169`
- Сигнатура: `function getComment()`
- Описание: Получает, читает или вычисляет данные `comment` в рамках этого метода класса `fan\core\service\entity\descriptor\mysql\direct`.

### `fan\core\service\entity\descriptor\mysql\direct::_getFields`

- Расположение: `_core/service/entity/descriptor/mysql/direct.php:185`
- Сигнатура: `function _getFields()`
- Описание: Выполняет логику `get fields` и возвращает вычисленный результат.
- Побочные эффекты: выполняет database операции

### `fan\core\service\entity\descriptor\mysql\direct::_getCreateTable`

- Расположение: `_core/service/entity/descriptor/mysql/direct.php:198`
- Сигнатура: `function _getCreateTable()`
- Описание: Выполняет логику `get create table` и возвращает вычисленный результат.
- Побочные эффекты: выполняет database операции

## `_core/service/entity/descriptor/mysql/schema.php`

### `fan\core\service\entity\descriptor\mysql\schema::__construct`

- Расположение: `_core/service/entity/descriptor/mysql/schema.php:51`
- Сигнатура: `function __construct(\fan\core\service\entity\description $description)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\entity\descriptor\mysql\schema`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\entity\descriptor\mysql\schema::isTableExists`

- Расположение: `_core/service/entity/descriptor/mysql/schema.php:103`
- Сигнатура: `function isTableExists()`
- Описание: Проверяет условие или валидирует данные `table exists` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\entity\descriptor\mysql\schema::getFields`

- Расположение: `_core/service/entity/descriptor/mysql/schema.php:113`
- Сигнатура: `function getFields()`
- Описание: Получает, читает или вычисляет данные `fields` в рамках этого метода класса `fan\core\service\entity\descriptor\mysql\schema`.

### `fan\core\service\entity\descriptor\mysql\schema::getRelations`

- Расположение: `_core/service/entity/descriptor/mysql/schema.php:150`
- Сигнатура: `function getRelations()`
- Описание: Получает, читает или вычисляет данные `relations` в рамках этого метода класса `fan\core\service\entity\descriptor\mysql\schema`.

### `fan\core\service\entity\descriptor\mysql\schema::getEngine`

- Расположение: `_core/service/entity/descriptor/mysql/schema.php:173`
- Сигнатура: `function getEngine()`
- Описание: Получает, читает или вычисляет данные `engine` в рамках этого метода класса `fan\core\service\entity\descriptor\mysql\schema`.

### `fan\core\service\entity\descriptor\mysql\schema::getCreateTime`

- Расположение: `_core/service/entity/descriptor/mysql/schema.php:183`
- Сигнатура: `function getCreateTime()`
- Описание: Получает, читает или вычисляет данные `create time` в рамках этого метода класса `fan\core\service\entity\descriptor\mysql\schema`.

### `fan\core\service\entity\descriptor\mysql\schema::getTableCollation`

- Расположение: `_core/service/entity/descriptor/mysql/schema.php:193`
- Сигнатура: `function getTableCollation()`
- Описание: Получает, читает или вычисляет данные `table collation` в рамках этого метода класса `fan\core\service\entity\descriptor\mysql\schema`.

### `fan\core\service\entity\descriptor\mysql\schema::getComment`

- Расположение: `_core/service/entity/descriptor/mysql/schema.php:203`
- Сигнатура: `function getComment()`
- Описание: Получает, читает или вычисляет данные `comment` в рамках этого метода класса `fan\core\service\entity\descriptor\mysql\schema`.

### `fan\core\service\entity\descriptor\mysql\schema::_getTableInfo`

- Расположение: `_core/service/entity/descriptor/mysql/schema.php:215`
- Сигнатура: `function _getTableInfo()`
- Описание: Выполняет логику `get table info` и возвращает вычисленный результат.

### `fan\core\service\entity\descriptor\mysql\schema::_getFields`

- Расположение: `_core/service/entity/descriptor/mysql/schema.php:238`
- Сигнатура: `function _getFields()`
- Описание: Выполняет логику `get fields` и возвращает вычисленный результат.

### `fan\core\service\entity\descriptor\mysql\schema::_getConstraints`

- Расположение: `_core/service/entity/descriptor/mysql/schema.php:262`
- Сигнатура: `function _getConstraints()`
- Описание: Выполняет логику `get constraints` и возвращает вычисленный результат.
- Побочные эффекты: выполняет database операции

## `_core/service/entity/designer.php`

### `fan\core\service\entity\designer::__construct`

- Расположение: `_core/service/entity/designer.php:49`
- Сигнатура: `function __construct(?\fan\core\base\model\entity $entity = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\entity\designer`.

### `fan\core\service\entity\designer::__set`

- Расположение: `_core/service/entity/designer.php:65`
- Сигнатура: `function __set($partName, $value)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\entity\designer`.

### `fan\core\service\entity\designer::__get`

- Расположение: `_core/service/entity/designer.php:77`
- Сигнатура: `function __get($partName)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\entity\designer`.

### `fan\core\service\entity\designer::__toString`

- Расположение: `_core/service/entity/designer.php:87`
- Сигнатура: `function __toString()`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\entity\designer`.

### `fan\core\service\entity\designer::set`

- Расположение: `_core/service/entity/designer.php:102`
- Сигнатура: `function set(string $partName, mixed $partValue, $allowException = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\service\entity\designer`.

### `fan\core\service\entity\designer::get`

- Расположение: `_core/service/entity/designer.php:118`
- Сигнатура: `function get(string $partName, $allowException = false)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\service\entity\designer`.

### `fan\core\service\entity\designer::add`

- Расположение: `_core/service/entity/designer.php:133`
- Сигнатура: `function add(string $partName, mixed $newPart, bool $toEnd = true, $allowException = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `элемент` в рамках этого метода класса `fan\core\service\entity\designer`.

### `fan\core\service\entity\designer::makeWhere`

- Расположение: `_core/service/entity/designer.php:163`
- Сигнатура: `function makeWhere(mixed $param, bool $merge = true)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `where` для этого метода класса `fan\core\service\entity\designer`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\entity\designer::assemble`

- Расположение: `_core/service/entity/designer.php:216`
- Сигнатура: `function assemble(mixed $param = null)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `assemble` для этого метода класса `fan\core\service\entity\designer`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\entity\designer::getAdjustedParam`

- Расположение: `_core/service/entity/designer.php:236`
- Сигнатура: `function getAdjustedParam()`
- Описание: Получает, читает или вычисляет данные `adjusted param` в рамках этого метода класса `fan\core\service\entity\designer`.

### `fan\core\service\entity\designer::toArray`

- Расположение: `_core/service/entity/designer.php:246`
- Сигнатура: `function toArray()`
- Описание: Выполняет логику `to array` и возвращает вычисленный результат.

### `fan\core\service\entity\designer::getEntity`

- Расположение: `_core/service/entity/designer.php:255`
- Сигнатура: `function getEntity()`
- Описание: Получает, читает или вычисляет данные `entity` в рамках этого метода класса `fan\core\service\entity\designer`.

### `fan\core\service\entity\designer::_checkPartName`

- Расположение: `_core/service/entity/designer.php:271`
- Сигнатура: `function _checkPartName($partName, $allowException)`
- Описание: Выполняет логику `check part name` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\entity\designer::_mergeParts`

- Расположение: `_core/service/entity/designer.php:289`
- Сигнатура: `function _mergeParts(array $source)`
- Описание: Выполняет логику `merge parts` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\entity\designer::_makeSetupPart`

- Расположение: `_core/service/entity/designer.php:335`
- Сигнатура: `function _makeSetupPart(array $data)`
- Описание: Выполняет логику `make setup part` и возвращает вычисленный результат.

## `_core/service/entity/designer/delete.php`

### `fan\core\service\entity\designer\delete::setDeleteByParam`

- Расположение: `_core/service/entity/designer/delete.php:43`
- Сигнатура: `function setDeleteByParam(array $param)`
- Описание: Устанавливает, добавляет или сохраняет данные `delete by param` в рамках этого метода класса `fan\core\service\entity\designer\delete`.

## `_core/service/entity/designer/insert.php`

### `fan\core\service\entity\designer\insert::setInsertByParam`

- Расположение: `_core/service/entity/designer/insert.php:44`
- Сигнатура: `function setInsertByParam(array $param)`
- Описание: Устанавливает, добавляет или сохраняет данные `insert by param` в рамках этого метода класса `fan\core\service\entity\designer\insert`.

## `_core/service/entity/designer/select.php`

### `fan\core\service\entity\designer\select::setSelectByParam`

- Расположение: `_core/service/entity/designer/select.php:50`
- Сигнатура: `function setSelectByParam(mixed $param, ?string $orderBy = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `select by param` в рамках этого метода класса `fan\core\service\entity\designer\select`.

### `fan\core\service\entity\designer\select::setMainSqlParts`

- Расположение: `_core/service/entity/designer/select.php:64`
- Сигнатура: `function setMainSqlParts()`
- Описание: Устанавливает, добавляет или сохраняет данные `main sql parts` в рамках этого метода класса `fan\core\service\entity\designer\select`.

## `_core/service/entity/designer/snippety.php`

### `fan\core\service\entity\designer\snippety::setSqlRequest`

- Расположение: `_core/service/entity/designer/snippety.php:44`
- Сигнатура: `function setSqlRequest(string $queryKey)`
- Описание: Устанавливает, добавляет или сохраняет данные `sql request` в рамках этого метода класса `fan\core\service\entity\designer\snippety`.

### `fan\core\service\entity\designer\snippety::setRequestSnippet`

- Расположение: `_core/service/entity/designer/snippety.php:64`
- Сигнатура: `function setRequestSnippet(mixed $snippetValue, $allowException = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `request snippet` в рамках этого метода класса `fan\core\service\entity\designer\snippety`.

### `fan\core\service\entity\designer\snippety::addRequestSnippet`

- Расположение: `_core/service/entity/designer/snippety.php:79`
- Сигнатура: `function addRequestSnippet(string|array $snippetValue, $toEnd = true, $allowException = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `request snippet` в рамках этого метода класса `fan\core\service\entity\designer\snippety`.

### `fan\core\service\entity\designer\snippety::setOrderPart`

- Расположение: `_core/service/entity/designer/snippety.php:93`
- Сигнатура: `function setOrderPart(mixed $partValue, bool $allowException = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `order part` в рамках этого метода класса `fan\core\service\entity\designer\snippety`.

### `fan\core\service\entity\designer\snippety::addOrderPart`

- Расположение: `_core/service/entity/designer/snippety.php:110`
- Сигнатура: `function addOrderPart(string|array $partValue, bool $toEnd = true, bool $allowException = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `order part` в рамках этого метода класса `fan\core\service\entity\designer\snippety`.

### `fan\core\service\entity\designer\snippety::_parseSQL`

- Расположение: `_core/service/entity/designer/snippety.php:124`
- Сигнатура: `function _parseSQL($sourceSQL)`
- Описание: Выполняет логику `parse s q l` и возвращает вычисленный результат.

## `_core/service/entity/designer/update.php`

### `fan\core\service\entity\designer\update::setUpdateByParam`

- Расположение: `_core/service/entity/designer/update.php:46`
- Сигнатура: `function setUpdateByParam(array $data, mixed $param)`
- Описание: Устанавливает, добавляет или сохраняет данные `update by param` в рамках этого метода класса `fan\core\service\entity\designer\update`.

## `_core/service/entity/encapsulant/simple.php`

### `fan\core\service\entity\encapsulant\simple::__construct`

- Расположение: `_core/service/entity/encapsulant/simple.php:32`
- Сигнатура: `function __construct(\fan\core\service\entity $service)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\entity\encapsulant\simple`.

### `fan\core\service\entity\encapsulant\simple::encryptId`

- Расположение: `_core/service/entity/encapsulant/simple.php:47`
- Сигнатура: `function encryptId(int|float $id)`
- Описание: Выполняет логику `encrypt id` и возвращает вычисленный результат.

### `fan\core\service\entity\encapsulant\simple::decryptId`

- Расположение: `_core/service/entity/encapsulant/simple.php:77`
- Сигнатура: `function decryptId(string $code)`
- Описание: Выполняет логику `decrypt id` и возвращает вычисленный результат.

### `fan\core\service\entity\encapsulant\simple::_code2Symbol`

- Расположение: `_core/service/entity/encapsulant/simple.php:115`
- Сигнатура: `function _code2Symbol(int $code)`
- Описание: Выполняет логику `code2 symbol` и возвращает вычисленный результат.

### `fan\core\service\entity\encapsulant\simple::_symbol2Code`

- Расположение: `_core/service/entity/encapsulant/simple.php:126`
- Сигнатура: `function _symbol2Code(string $sym)`
- Описание: Выполняет логику `symbol2 code` и возвращает вычисленный результат.

### `fan\core\service\entity\encapsulant\simple::_getCriptKey`

- Расположение: `_core/service/entity/encapsulant/simple.php:140`
- Сигнатура: `function _getCriptKey(string $srt, int $len = 31)`
- Описание: Выполняет логику `get cript key` и возвращает вычисленный результат.

### `fan\core\service\entity\encapsulant\simple::_getCheckSumId`

- Расположение: `_core/service/entity/encapsulant/simple.php:152`
- Сигнатура: `function _getCheckSumId(int $id, int $len = 11)`
- Описание: Выполняет логику `get check sum id` и возвращает вычисленный результат.

### `fan\core\service\entity\encapsulant\simple::_getShiftPos`

- Расположение: `_core/service/entity/encapsulant/simple.php:164`
- Сигнатура: `function _getShiftPos(int $len = 31)`
- Описание: Выполняет логику `get shift pos` и возвращает вычисленный результат.

### `fan\core\service\entity\encapsulant\simple::_getEncryptKey`

- Расположение: `_core/service/entity/encapsulant/simple.php:174`
- Сигнатура: `function _getEncryptKey()`
- Описание: Выполняет логику `get encrypt key` и возвращает вычисленный результат.

## `_core/service/entity/snippet.php`

### `fan\core\service\entity\snippet::__construct`

- Расположение: `_core/service/entity/snippet.php:83`
- Сигнатура: `function __construct(\fan\core\service\entity\designer\snippety $snippety, $query, $srcCondition, $callback)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\entity\snippet`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\entity\snippet::getSnippetQuery`

- Расположение: `_core/service/entity/snippet.php:106`
- Сигнатура: `function getSnippetQuery(array $data)`
- Описание: Получает, читает или вычисляет данные `snippet query` в рамках этого метода класса `fan\core\service\entity\snippet`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\entity\snippet::prepareData`

- Расположение: `_core/service/entity/snippet.php:129`
- Сигнатура: `function prepareData(&$query, mixed $data, mixed $usedKeys = null)`
- Описание: Выполняет логику `prepare data` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения; выполняет database операции

### `fan\core\service\entity\snippet::getSql`

- Расположение: `_core/service/entity/snippet.php:161`
- Сигнатура: `function getSql()`
- Описание: Получает, читает или вычисляет данные `sql` в рамках этого метода класса `fan\core\service\entity\snippet`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\entity\snippet::getSrcCondition`

- Расположение: `_core/service/entity/snippet.php:170`
- Сигнатура: `function getSrcCondition()`
- Описание: Получает, читает или вычисляет данные `src condition` в рамках этого метода класса `fan\core\service\entity\snippet`.

### `fan\core\service\entity\snippet::getCondition`

- Расположение: `_core/service/entity/snippet.php:179`
- Сигнатура: `function getCondition()`
- Описание: Получает, читает или вычисляет данные `condition` в рамках этого метода класса `fan\core\service\entity\snippet`.

### `fan\core\service\entity\snippet::getUsedKeys`

- Расположение: `_core/service/entity/snippet.php:188`
- Сигнатура: `function getUsedKeys()`
- Описание: Получает, читает или вычисляет данные `used keys` в рамках этого метода класса `fan\core\service\entity\snippet`.

### `fan\core\service\entity\snippet::getCallback`

- Расположение: `_core/service/entity/snippet.php:197`
- Сигнатура: `function getCallback()`
- Описание: Получает, читает или вычисляет данные `callback` в рамках этого метода класса `fan\core\service\entity\snippet`.

### `fan\core\service\entity\snippet::getSnippety`

- Расположение: `_core/service/entity/snippet.php:207`
- Сигнатура: `function getSnippety()`
- Описание: Получает, читает или вычисляет данные `snippety` в рамках этого метода класса `fan\core\service\entity\snippet`.

### `fan\core\service\entity\snippet::getEntity`

- Расположение: `_core/service/entity/snippet.php:216`
- Сигнатура: `function getEntity()`
- Описание: Получает, читает или вычисляет данные `entity` в рамках этого метода класса `fan\core\service\entity\snippet`.

### `fan\core\service\entity\snippet::_parseCondition`

- Расположение: `_core/service/entity/snippet.php:231`
- Сигнатура: `function _parseCondition(string $condition)`
- Описание: Выполняет логику `parse condition` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\entity\snippet::_parsePlaceHolders`

- Расположение: `_core/service/entity/snippet.php:279`
- Сигнатура: `function _parsePlaceHolders(string $query)`
- Описание: Выполняет логику `parse place holders` и возвращает вычисленный результат.
- Побочные эффекты: выполняет database операции

### `fan\core\service\entity\snippet::_parseCallback`

- Расположение: `_core/service/entity/snippet.php:303`
- Сигнатура: `function _parseCallback(string $callback)`
- Описание: Выполняет логику `parse callback` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\entity\snippet::_checkCondition`

- Расположение: `_core/service/entity/snippet.php:337`
- Сигнатура: `function _checkCondition(array $data)`
- Описание: Выполняет логику `check condition` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

## `_core/service/error.php`

### `fan\core\service\error::__construct`

- Расположение: `_core/service/error.php:112`
- Сигнатура: `function __construct($allowIni = true)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\error`.
- Побочные эффекты: читает PHP superglobals

### `fan\core\service\error::handleError`

- Расположение: `_core/service/error.php:157`
- Сигнатура: `function handleError(int|float $errNo, string $errMsg, mixed $fileName = null, int|float|null $lineNum = null, mixed $errContext = null)`
- Описание: Запускает или обрабатывает workflow `error` для этого метода класса `fan\core\service\error`.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\service\error::setErrorBuffering`

- Расположение: `_core/service/error.php:241`
- Сигнатура: `function setErrorBuffering(mixed $sysMask = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `error buffering` в рамках этого метода класса `fan\core\service\error`.

### `fan\core\service\error::getErrorBuffering`

- Расположение: `_core/service/error.php:258`
- Сигнатура: `function getErrorBuffering()`
- Описание: Получает, читает или вычисляет данные `error buffering` в рамках этого метода класса `fan\core\service\error`.

### `fan\core\service\error::offErrorBuffering`

- Расположение: `_core/service/error.php:268`
- Сигнатура: `function offErrorBuffering()`
- Описание: Выполняет логику `off error buffering` и возвращает вычисленный результат.

### `fan\core\service\error::addIgnorePath`

- Расположение: `_core/service/error.php:284`
- Сигнатура: `function addIgnorePath(mixed $mask, mixed $path)`
- Описание: Устанавливает, добавляет или сохраняет данные `ignore path` в рамках этого метода класса `fan\core\service\error`.

### `fan\core\service\error::setParseDBerror`

- Расположение: `_core/service/error.php:304`
- Сигнатура: `function setParseDBerror($valj)`
- Описание: Устанавливает, добавляет или сохраняет данные `parse d berror` в рамках этого метода класса `fan\core\service\error`.

### `fan\core\service\error::logDatabaseError`

- Расположение: `_core/service/error.php:320`
- Сигнатура: `function logDatabaseError($connectionName, string $operation, $errorMessage, int|float $errorNum, $parsedSql)`
- Описание: Выполняет логику `log database error` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\service\error::logSoapError`

- Расположение: `_core/service/error.php:340`
- Сигнатура: `function logSoapError(object $soapError)`
- Описание: Выполняет логику `log soap error` и возвращает вычисленный результат.

### `fan\core\service\error::logErrorMessage`

- Расположение: `_core/service/error.php:359`
- Сигнатура: `function logErrorMessage(string $message, string $header = '', string $note = '', bool $isTrace = false, bool $duplicateByEmail = false)`
- Описание: Выполняет workflow-логику `log error message`.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\service\error::logExceptionMessage`

- Расположение: `_core/service/error.php:373`
- Сигнатура: `function logExceptionMessage(string $message, string $header = '', string $note = '')`
- Описание: Выполняет workflow-логику `log exception message`.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\service\error::_logError`

- Расположение: `_core/service/error.php:390`
- Сигнатура: `function _logError(string $type, string $message, string $header, string $note, bool $isTrace, bool $duplicateByEmail)`
- Описание: Выполняет workflow-логику `log error`.
- Побочные эффекты: меняет HTTP/session состояние; читает PHP superglobals

### `fan\core\service\error::makeErrorEmail`

- Расположение: `_core/service/error.php:418`
- Сигнатура: `function makeErrorEmail(string $type, string $subject, string $message)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `error email` для этого метода класса `fan\core\service\error`.
- Побочные эффекты: работает с файловой системой

### `fan\core\service\error::sendPacketEmais`

- Расположение: `_core/service/error.php:462`
- Сигнатура: `function sendPacketEmais()`
- Описание: Выполняет workflow-логику `send packet emais`.

### `fan\core\service\error::removePacketFile`

- Расположение: `_core/service/error.php:496`
- Сигнатура: `function removePacketFile(string $file): void`
- Описание: Удаляет или сбрасывает состояние `packet file` для этого метода класса `fan\core\service\error`.
- Побочные эффекты: работает с файловой системой

### `fan\core\service\error::chmodPacketFile`

- Расположение: `_core/service/error.php:518`
- Сигнатура: `function chmodPacketFile(string $file, int $mode): void`
- Описание: Выполняет логику `chmod packet file` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой

### `fan\core\service\error::_sendErrorEmail`

- Расположение: `_core/service/error.php:536`
- Сигнатура: `function _sendErrorEmail(string $subject, string $message)`
- Описание: Выполняет workflow-логику `send error email`.

## `_core/service/file_system.php`

### `fan\core\service\file_system::__construct`

- Расположение: `_core/service/file_system.php:51`
- Сигнатура: `function __construct($fullPath)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\file_system`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\file_system::instance`

- Расположение: `_core/service/file_system.php:69`
- Сигнатура: `function instance(?string $srcPath = null)`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.

### `fan\core\service\file_system::isFile`

- Расположение: `_core/service/file_system.php:86`
- Сигнатура: `function isFile()`
- Описание: Проверяет условие или валидирует данные `file` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\file_system::isRreadable`

- Расположение: `_core/service/file_system.php:96`
- Сигнатура: `function isRreadable()`
- Описание: Проверяет условие или валидирует данные `rreadable` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\file_system::getFullPath`

- Расположение: `_core/service/file_system.php:106`
- Сигнатура: `function getFullPath()`
- Описание: Получает, читает или вычисляет данные `full path` в рамках этого метода класса `fan\core\service\file_system`.

### `fan\core\service\file_system::setReadByPart`

- Расположение: `_core/service/file_system.php:121`
- Сигнатура: `function setReadByPart(int|float $rowsQtt = 100, string $rowSeparator = "\n", string $colSeparator = "\t", $openFile = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `read by part` в рамках этого метода класса `fan\core\service\file_system`.

### `fan\core\service\file_system::openFile`

- Расположение: `_core/service/file_system.php:140`
- Сигнатура: `function openFile()`
- Описание: Выполняет логику `open file` и возвращает вычисленный результат.

### `fan\core\service\file_system::closeFile`

- Расположение: `_core/service/file_system.php:152`
- Сигнатура: `function closeFile()`
- Описание: Выполняет логику `close file` и возвращает вычисленный результат.

### `fan\core\service\file_system::getPartAsString`

- Расположение: `_core/service/file_system.php:166`
- Сигнатура: `function getPartAsString()`
- Описание: Получает, читает или вычисляет данные `part as string` в рамках этого метода класса `fan\core\service\file_system`.

### `fan\core\service\file_system::getPartAsArray`

- Расположение: `_core/service/file_system.php:214`
- Сигнатура: `function getPartAsArray()`
- Описание: Получает, читает или вычисляет данные `part as array` в рамках этого метода класса `fan\core\service\file_system`.

## `_core/service/form.php`

### `fan\core\service\form::__construct`

- Расположение: `_core/service/form.php:103`
- Сигнатура: `function __construct(\fan\core\block\form\parser $block)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\form`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\form::instance`

- Расположение: `_core/service/form.php:143`
- Сигнатура: `function instance(\fan\core\block\form\parser $block)`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.

### `fan\core\service\form::parseForm`

- Расположение: `_core/service/form.php:163`
- Сигнатура: `function parseForm(bool $parceEmpty = true, ?bool $parsingCondition = null, ?bool $allowTransfer = null)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `form` для этого метода класса `fan\core\service\form`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\form::necessaryFormParsing`

- Расположение: `_core/service/form.php:246`
- Сигнатура: `function necessaryFormParsing(mixed $parsingCondition = null, $chkButton = true)`
- Описание: Выполняет логику `necessary form parsing` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка; читает PHP superglobals

### `fan\core\service\form::strForJsValidation`

- Расположение: `_core/service/form.php:314`
- Сигнатура: `function strForJsValidation()`
- Описание: Выполняет логику `str for js validation` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\form::isMultiLanguage`

- Расположение: `_core/service/form.php:396`
- Сигнатура: `function isMultiLanguage()`
- Описание: Проверяет условие или валидирует данные `multi language` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\form::reduceMessage`

- Расположение: `_core/service/form.php:410`
- Сигнатура: `function reduceMessage($msg, $label)`
- Описание: Выполняет логику `reduce message` и возвращает вычисленный результат.

### `fan\core\service\form::getFieldValue`

- Расположение: `_core/service/form.php:426`
- Сигнатура: `function getFieldValue(mixed $fieldName = null, $useSubform = true)`
- Описание: Получает, читает или вычисляет данные `field value` в рамках этого метода класса `fan\core\service\form`.

### `fan\core\service\form::setFieldValue`

- Расположение: `_core/service/form.php:450`
- Сигнатура: `function setFieldValue(mixed $fieldName, mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `field value` в рамках этого метода класса `fan\core\service\form`.

### `fan\core\service\form::setMassFieldValues`

- Расположение: `_core/service/form.php:470`
- Сигнатура: `function setMassFieldValues(array $values)`
- Описание: Устанавливает, добавляет или сохраняет данные `mass field values` в рамках этого метода класса `fan\core\service\form`.

### `fan\core\service\form::getFieldData`

- Расположение: `_core/service/form.php:487`
- Сигнатура: `function getFieldData(mixed $fieldName)`
- Описание: Получает, читает или вычисляет данные `field data` в рамках этого метода класса `fan\core\service\form`.

### `fan\core\service\form::setFieldData`

- Расположение: `_core/service/form.php:518`
- Сигнатура: `function setFieldData(string $fieldName, mixed $fieldData)`
- Описание: Устанавливает, добавляет или сохраняет данные `field data` в рамках этого метода класса `fan\core\service\form`.

### `fan\core\service\form::setFieldDataByRowset`

- Расположение: `_core/service/form.php:534`
- Сигнатура: `function setFieldDataByRowset(string $fieldName, \fan\core\base\model\rowset $rowset, string $textKey, ?string $valueKey = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `field data by rowset` в рамках этого метода класса `fan\core\service\form`.

### `fan\core\service\form::checkDepth`

- Расположение: `_core/service/form.php:555`
- Сигнатура: `function checkDepth(mixed $val, int|float $depth)`
- Описание: Проверяет условие или валидирует данные `depth` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\form::getErrorMsg`

- Расположение: `_core/service/form.php:578`
- Сигнатура: `function getErrorMsg(mixed $fieldName = null)`
- Описание: Получает, читает или вычисляет данные `error msg` в рамках этого метода класса `fan\core\service\form`.

### `fan\core\service\form::setError`

- Расположение: `_core/service/form.php:588`
- Сигнатура: `function setError()`
- Описание: Устанавливает, добавляет или сохраняет данные `error` в рамках этого метода класса `fan\core\service\form`.

### `fan\core\service\form::isError`

- Расположение: `_core/service/form.php:598`
- Сигнатура: `function isError()`
- Описание: Проверяет условие или валидирует данные `error` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\form::checkFormRole`

- Расположение: `_core/service/form.php:608`
- Сигнатура: `function checkFormRole()`
- Описание: Проверяет условие или валидирует данные `form role` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\form::checkByData`

- Расположение: `_core/service/form.php:622`
- Сигнатура: `function checkByData(mixed $value, array $data)`
- Описание: Проверяет условие или валидирует данные `by data` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\form::_presetFieldValue`

- Расположение: `_core/service/form.php:644`
- Сигнатура: `function _presetFieldValue()`
- Описание: Выполняет логику `preset field value` и возвращает вычисленный результат.

### `fan\core\service\form::_getFormMeta`

- Расположение: `_core/service/form.php:669`
- Сигнатура: `function _getFormMeta(string|array $key, mixed $default = null, $convToArray = false)`
- Описание: Выполняет логику `get form meta` и возвращает вычисленный результат.

### `fan\core\service\form::_defineFieldValue`

- Расположение: `_core/service/form.php:680`
- Сигнатура: `function _defineFieldValue()`
- Описание: Выполняет логику `define field value` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\form::_trimDataRecursive`

- Расположение: `_core/service/form.php:780`
- Сигнатура: `function _trimDataRecursive(mixed $value, $fieldName, $index = [])`
- Описание: Выполняет логику `trim data recursive` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\form::_autoCheckByData`

- Расположение: `_core/service/form.php:826`
- Сигнатура: `function _autoCheckByData($fieldName, $label)`
- Описание: Выполняет логику `auto check by data` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\form::_isMultiVal`

- Расположение: `_core/service/form.php:848`
- Сигнатура: `function _isMultiVal(string $inpType)`
- Описание: Выполняет логику `is multi val` и возвращает вычисленный результат.

### `fan\core\service\form::_validateValueRecursive`

- Расположение: `_core/service/form.php:866`
- Сигнатура: `function _validateValueRecursive(string $fieldName, array $index = [])`
- Описание: Выполняет логику `validate value recursive` и возвращает вычисленный результат.

### `fan\core\service\form::_onSubmitTransfer`

- Расположение: `_core/service/form.php:940`
- Сигнатура: `function _onSubmitTransfer(mixed $allowTransfer, string $dbOper, bool $addQueryStr)`
- Описание: Выполняет логику `on submit transfer` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\form::_getErrorMesage`

- Расположение: `_core/service/form.php:976`
- Сигнатура: `function _getErrorMesage(string $label, string $msg, $altMsg = null)`
- Описание: Выполняет логику `get error mesage` и возвращает вычисленный результат.

### `fan\core\service\form::_getValidator`

- Расположение: `_core/service/form.php:995`
- Сигнатура: `function _getValidator(string $validatorName)`
- Описание: Выполняет логику `get validator` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\form::_checkName`

- Расположение: `_core/service/form.php:1023`
- Сигнатура: `function _checkName($fieldName, $reportErr = true)`
- Описание: Выполняет логику `check name` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\form::_parseFormParts`

- Расположение: `_core/service/form.php:1043`
- Сигнатура: `function _parseFormParts(mixed $parceEmpty)`
- Описание: Выполняет workflow-логику `parse form parts`.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения

### `fan\core\service\form::_getCombiParam`

- Расположение: `_core/service/form.php:1085`
- Сигнатура: `function _getCombiParam(string $fieldName, array $index)`
- Описание: Выполняет логику `get combi param` и возвращает вычисленный результат.

### `fan\core\service\form::_transformFileData`

- Расположение: `_core/service/form.php:1102`
- Сигнатура: `function _transformFileData(&$data, string $key, array $src)`
- Описание: Выполняет логику `transform file data` и возвращает вычисленный результат.

### `fan\core\service\form::__set`

- Расположение: `_core/service/form.php:1125`
- Сигнатура: `function __set($key, $value)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\form`.

### `fan\core\service\form::__get`

- Расположение: `_core/service/form.php:1137`
- Сигнатура: `function __get($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\form`.

### `fan\core\service\form::__isset`

- Расположение: `_core/service/form.php:1149`
- Сигнатура: `function __isset($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\form`.

## `_core/service/form/validator/base.php`

### `fan\core\service\form\validator\base::setFacade`

- Расположение: `_core/service/form/validator/base.php:40`
- Сигнатура: `function setFacade(\fan\core\service\form $facade)`
- Описание: Устанавливает, добавляет или сохраняет данные `facade` в рамках этого метода класса `fan\core\service\form\validator\base`.

## `_core/service/form/validator/common.php`

### `fan\core\service\form\validator\common::isRequired`

- Расположение: `_core/service/form/validator/common.php:30`
- Сигнатура: `function isRequired(mixed $value, array $data)`
- Описание: Проверяет условие или валидирует данные `required` и возвращает результат либо выбрасывает исключение.

## `_core/service/form/validator/date.php`

### `fan\core\service\form\validator\date::isDate`

- Расположение: `_core/service/form/validator/date.php:30`
- Сигнатура: `function isDate(mixed $value, array $data)`
- Описание: Проверяет условие или валидирует данные `date` и возвращает результат либо выбрасывает исключение.

## `_core/service/form/validator/number.php`

### `fan\core\service\form\validator\number::isInt`

- Расположение: `_core/service/form/validator/number.php:30`
- Сигнатура: `function isInt(mixed $value, array $data)`
- Описание: Проверяет условие или валидирует данные `int` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\form\validator\number::isFloat`

- Расположение: `_core/service/form/validator/number.php:52`
- Сигнатура: `function isFloat(mixed $value, array $data)`
- Описание: Проверяет условие или валидирует данные `float` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\form\validator\number::equalTo`

- Расположение: `_core/service/form/validator/number.php:75`
- Сигнатура: `function equalTo(mixed $value, array $data)`
- Описание: Выполняет логику `equal to` и возвращает вычисленный результат.

### `fan\core\service\form\validator\number::notEqualTo`

- Расположение: `_core/service/form/validator/number.php:92`
- Сигнатура: `function notEqualTo(mixed $value, array $data)`
- Описание: Выполняет логику `not equal to` и возвращает вычисленный результат.

### `fan\core\service\form\validator\number::greaterThan`

- Расположение: `_core/service/form/validator/number.php:109`
- Сигнатура: `function greaterThan(mixed $value, array $data)`
- Описание: Выполняет логику `greater than` и возвращает вычисленный результат.

### `fan\core\service\form\validator\number::lesserThan`

- Расположение: `_core/service/form/validator/number.php:130`
- Сигнатура: `function lesserThan(mixed $value, array $data)`
- Описание: Выполняет логику `lesser than` и возвращает вычисленный результат.

### `fan\core\service\form\validator\number::greaterOrEqualTo`

- Расположение: `_core/service/form/validator/number.php:151`
- Сигнатура: `function greaterOrEqualTo(mixed $value, array $data)`
- Описание: Выполняет логику `greater or equal to` и возвращает вычисленный результат.

### `fan\core\service\form\validator\number::lesserOrEqualTo`

- Расположение: `_core/service/form/validator/number.php:168`
- Сигнатура: `function lesserOrEqualTo(mixed $value, array $data)`
- Описание: Выполняет логику `lesser or equal to` и возвращает вычисленный результат.

## `_core/service/form/validator/phone.php`

### `fan\core\service\form\validator\phone::isUkrainianPhone`

- Расположение: `_core/service/form/validator/phone.php:28`
- Сигнатура: `function isUkrainianPhone(mixed $value)`
- Описание: Проверяет условие или валидирует данные `ukrainian phone` и возвращает результат либо выбрасывает исключение.

## `_core/service/form/validator/select.php`

### `fan\core\service\form\validator\select::checkSelect`

- Расположение: `_core/service/form/validator/select.php:30`
- Сигнатура: `function checkSelect(mixed $value, array $data)`
- Описание: Проверяет условие или валидирует данные `select` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\form\validator\select::inArray`

- Расположение: `_core/service/form/validator/select.php:51`
- Сигнатура: `function inArray(mixed $value, array $data)`
- Описание: Выполняет логику `in array` и возвращает вычисленный результат.

## `_core/service/form/validator/string_validator.php`

### `fan\core\service\form\validator\string_validator::strlen`

- Расположение: `_core/service/form/validator/string_validator.php:30`
- Сигнатура: `function strlen(mixed $value, array $data)`
- Описание: Выполняет логику `strlen` и возвращает вычисленный результат.

### `fan\core\service\form\validator\string_validator::isUtf8`

- Расположение: `_core/service/form/validator/string_validator.php:50`
- Сигнатура: `function isUtf8(mixed $value, array $data)`
- Описание: Проверяет условие или валидирует данные `utf8` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\form\validator\string_validator::isAlphalogin`

- Расположение: `_core/service/form/validator/string_validator.php:70`
- Сигнатура: `function isAlphalogin(mixed $value, array $data)`
- Описание: Проверяет условие или валидирует данные `alphalogin` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\form\validator\string_validator::isAlphanumeric`

- Расположение: `_core/service/form/validator/string_validator.php:83`
- Сигнатура: `function isAlphanumeric(mixed $value, array $data)`
- Описание: Проверяет условие или валидирует данные `alphanumeric` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\form\validator\string_validator::matchRegexp`

- Расположение: `_core/service/form/validator/string_validator.php:96`
- Сигнатура: `function matchRegexp(mixed $value, array $data)`
- Описание: Выполняет логику `match regexp` и возвращает вычисленный результат.

## `_core/service/form/validator/upload.php`

### `fan\core\service\form\validator\upload::uploadError`

- Расположение: `_core/service/form/validator/upload.php:29`
- Сигнатура: `function uploadError(mixed $value, $data)`
- Описание: Выполняет логику `upload error` и возвращает вычисленный результат.

### `fan\core\service\form\validator\upload::uploadName`

- Расположение: `_core/service/form/validator/upload.php:42`
- Сигнатура: `function uploadName(mixed $value, mixed $data)`
- Описание: Выполняет логику `upload name` и возвращает вычисленный результат.

### `fan\core\service\form\validator\upload::uploadMime`

- Расположение: `_core/service/form/validator/upload.php:65`
- Сигнатура: `function uploadMime(mixed $value, array $data)`
- Описание: Выполняет логику `upload mime` и возвращает вычисленный результат.

## `_core/service/form/validator/uri.php`

### `fan\core\service\form\validator\uri::isEmail`

- Расположение: `_core/service/form/validator/uri.php:30`
- Сигнатура: `function isEmail(mixed $value, array $data)`
- Описание: Проверяет условие или валидирует данные `email` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\form\validator\uri::isUri`

- Расположение: `_core/service/form/validator/uri.php:43`
- Сигнатура: `function isUri(mixed $value, array $data)`
- Описание: Проверяет условие или валидирует данные `uri` и возвращает результат либо выбрасывает исключение.

## `_core/service/header.php`

### `fan\core\service\header::__construct`

- Расположение: `_core/service/header.php:90`
- Сигнатура: `function __construct($allowIni = true)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\header`.

### `fan\core\service\header::addHeader`

- Расположение: `_core/service/header.php:110`
- Сигнатура: `function addHeader(string $param, string $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `header` в рамках этого метода класса `fan\core\service\header`.

### `fan\core\service\header::setHeaders`

- Расположение: `_core/service/header.php:128`
- Сигнатура: `function setHeaders(array $headers)`
- Описание: Устанавливает, добавляет или сохраняет данные `headers` в рамках этого метода класса `fan\core\service\header`.

### `fan\core\service\header::removeHeader`

- Расположение: `_core/service/header.php:144`
- Сигнатура: `function removeHeader(string $param)`
- Описание: Удаляет или сбрасывает состояние `header` для этого метода класса `fan\core\service\header`.

### `fan\core\service\header::getHeader`

- Расположение: `_core/service/header.php:157`
- Сигнатура: `function getHeader(?string $param = null)`
- Описание: Получает, читает или вычисляет данные `header` в рамках этого метода класса `fan\core\service\header`.

### `fan\core\service\header::sendHeaders`

- Расположение: `_core/service/header.php:167`
- Сигнатура: `function sendHeaders()`
- Описание: Выполняет логику `send headers` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\header::clearHeaders`

- Расположение: `_core/service/header.php:191`
- Сигнатура: `function clearHeaders()`
- Описание: Удаляет или сбрасывает состояние `headers` для этого метода класса `fan\core\service\header`.
- Побочные эффекты: читает PHP superglobals

### `fan\core\service\header::setResponseType`

- Расположение: `_core/service/header.php:209`
- Сигнатура: `function setResponseType(mixed $code = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `response type` в рамках этого метода класса `fan\core\service\header`.

### `fan\core\service\header::getResponseCode`

- Расположение: `_core/service/header.php:226`
- Сигнатура: `function getResponseCode()`
- Описание: Получает, читает или вычисляет данные `response code` в рамках этого метода класса `fan\core\service\header`.

### `fan\core\service\header::getProtocol`

- Расположение: `_core/service/header.php:236`
- Сигнатура: `function getProtocol()`
- Описание: Получает, читает или вычисляет данные `protocol` в рамках этого метода класса `fan\core\service\header`.

### `fan\core\service\header::sendResponseType`

- Расположение: `_core/service/header.php:250`
- Сигнатура: `function sendResponseType(?int $code = null, $protocol = null)`
- Описание: Выполняет workflow-логику `send response type`.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\service\header::sendContentType`

- Расположение: `_core/service/header.php:263`
- Сигнатура: `function sendContentType(?string $value = null, ?string $encoding = null)`
- Описание: Выполняет логику `send content type` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\service\header::sendLength`

- Расположение: `_core/service/header.php:282`
- Сигнатура: `function sendLength(string $len, ?string $ranges = null)`
- Описание: Выполняет логику `send length` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\service\header::sendTime`

- Расположение: `_core/service/header.php:302`
- Сигнатура: `function sendTime(int|float|null $modified = NULL, int|float|null $expired = NULL)`
- Описание: Выполняет логику `send time` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\service\header::sendFilename`

- Расположение: `_core/service/header.php:322`
- Сигнатура: `function sendFilename(string $fileName, bool $isInline = true)`
- Описание: Выполняет логику `send filename` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\service\header::sendCache`

- Расположение: `_core/service/header.php:335`
- Сигнатура: `function sendCache(int|float $timeExpires = 0)`
- Описание: Выполняет логику `send cache` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\service\header::sendLocation`

- Расположение: `_core/service/header.php:363`
- Сигнатура: `function sendLocation(string $url, $continueExec = false)`
- Описание: Выполняет логику `send location` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\service\header::sendLocation301`

- Расположение: `_core/service/header.php:380`
- Сигнатура: `function sendLocation301(string $url, $continueExec = false)`
- Описание: Выполняет логику `send location301` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\service\header::sendArbitrary`

- Расположение: `_core/service/header.php:398`
- Сигнатура: `function sendArbitrary(string $type, string $value, string $extraData = '')`
- Описание: Выполняет логику `send arbitrary` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\service\header::ok200`

- Расположение: `_core/service/header.php:412`
- Сигнатура: `function ok200($send = false)`
- Описание: Выполняет логику `ok200` и возвращает вычисленный результат.

### `fan\core\service\header::error403`

- Расположение: `_core/service/header.php:424`
- Сигнатура: `function error403($send = false)`
- Описание: Выполняет логику `error403` и возвращает вычисленный результат.

### `fan\core\service\header::error404`

- Расположение: `_core/service/header.php:436`
- Сигнатура: `function error404($send = false)`
- Описание: Выполняет логику `error404` и возвращает вычисленный результат.

### `fan\core\service\header::error500`

- Расположение: `_core/service/header.php:448`
- Сигнатура: `function error500($send = false)`
- Описание: Выполняет логику `error500` и возвращает вычисленный результат.

### `fan\core\service\header::_getSendMethodMap`

- Расположение: `_core/service/header.php:459`
- Сигнатура: `function _getSendMethodMap()`
- Описание: Выполняет логику `get send method map` и возвращает вычисленный результат.

### `fan\core\service\header::_setHeadStack`

- Расположение: `_core/service/header.php:472`
- Сигнатура: `function _setHeadStack(string $param, string $value)`
- Описание: Выполняет логику `set head stack` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние; может выбрасывать исключения

### `fan\core\service\header::_prepareFunctions`

- Расположение: `_core/service/header.php:487`
- Сигнатура: `function _prepareFunctions()`
- Описание: Выполняет логику `prepare functions` и возвращает вычисленный результат.

### `fan\core\service\header::_orderArguments`

- Расположение: `_core/service/header.php:505`
- Сигнатура: `function _orderArguments(array $arg)`
- Описание: Выполняет логику `order arguments` и возвращает вычисленный результат.

### `fan\core\service\header::_getResponseText`

- Расположение: `_core/service/header.php:526`
- Сигнатура: `function _getResponseText(int $code, string $protocol)`
- Описание: Выполняет логику `get response text` и возвращает вычисленный результат.

### `fan\core\service\header::_checkResponseCode`

- Расположение: `_core/service/header.php:544`
- Сигнатура: `function _checkResponseCode(int $code)`
- Описание: Выполняет логику `check response code` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\header::_setSpecialType`

- Расположение: `_core/service/header.php:569`
- Сигнатура: `function _setSpecialType(int $code, bool $send)`
- Описание: Выполняет логику `set special type` и возвращает вычисленный результат.

### `fan\core\service\header::__set`

- Расположение: `_core/service/header.php:588`
- Сигнатура: `function __set($key, $value)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\header`.

### `fan\core\service\header::__get`

- Расположение: `_core/service/header.php:600`
- Сигнатура: `function __get($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\header`.

## `_core/service/header/code.php`

### `fan\core\service\header\code::getCodes1`

- Расположение: `_core/service/header/code.php:26`
- Сигнатура: `function getCodes1()`
- Описание: Получает, читает или вычисляет данные `codes1` в рамках этого метода класса `fan\core\service\header\code`.

### `fan\core\service\header\code::getCodes2`

- Расположение: `_core/service/header/code.php:40`
- Сигнатура: `function getCodes2()`
- Описание: Получает, читает или вычисляет данные `codes2` в рамках этого метода класса `fan\core\service\header\code`.

### `fan\core\service\header\code::getCodes3`

- Расположение: `_core/service/header/code.php:60`
- Сигнатура: `function getCodes3()`
- Описание: Получает, читает или вычисляет данные `codes3` в рамках этого метода класса `fan\core\service\header\code`.

### `fan\core\service\header\code::getCodes4`

- Расположение: `_core/service/header/code.php:80`
- Сигнатура: `function getCodes4()`
- Описание: Получает, читает или вычисляет данные `codes4` в рамках этого метода класса `fan\core\service\header\code`.

### `fan\core\service\header\code::getCodes5`

- Расположение: `_core/service/header/code.php:116`
- Сигнатура: `function getCodes5()`
- Описание: Получает, читает или вычисляет данные `codes5` в рамках этого метода класса `fan\core\service\header\code`.

## `_core/service/image_draw.php`

### `fan\core\service\image_draw::setBackground`

- Расположение: `_core/service/image_draw.php:33`
- Сигнатура: `function setBackground(int|string|array $bgrColor = 0xFFFFFF)`
- Описание: Устанавливает, добавляет или сохраняет данные `background` в рамках этого метода класса `fan\core\service\image_draw`.

### `fan\core\service\image_draw::drawText`

- Расположение: `_core/service/image_draw.php:51`
- Сигнатура: `function drawText(string $string, array $coord, int|float $fontNumber = 1, int|string|array $fntColor = 0x000000, string $txtAlign = 'left', string $vertAlign = 'top')`
- Описание: Выполняет логику `draw text` и возвращает вычисленный результат.

### `fan\core\service\image_draw::drawTextTtf`

- Расположение: `_core/service/image_draw.php:87`
- Сигнатура: `function drawTextTtf(string $string, array $coord, string $fontFile = '', int|string|array $fntColor = 0X000000, string $txtAlign = 'left', string $vertAlign = 'top', array $info = ['linespacing' => 1])`
- Описание: Выполняет логику `draw text ttf` и возвращает вычисленный результат.

### `fan\core\service\image_draw::rectangle`

- Расположение: `_core/service/image_draw.php:129`
- Сигнатура: `function rectangle(array $coord, int|string|array|null $brdColor = 0x000000, int|string|array|null $bgrColor = 0xFFFFFF)`
- Описание: Выполняет логику `rectangle` и возвращает вычисленный результат.

### `fan\core\service\image_draw::polygon`

- Расположение: `_core/service/image_draw.php:159`
- Сигнатура: `function polygon(array $coord, int|string|array|null $brdColor = 0x000000, int|string|array|null $bgrColor = 0XFFFFFF)`
- Описание: Выполняет логику `polygon` и возвращает вычисленный результат.

### `fan\core\service\image_draw::ellipse`

- Расположение: `_core/service/image_draw.php:179`
- Сигнатура: `function ellipse(array $coord, int|string|array|null $brdColor = 0x000000, int|string|array|null $bgrColor = 0XFFFFFF)`
- Описание: Выполняет логику `ellipse` и возвращает вычисленный результат.

### `fan\core\service\image_draw::ellipseSector`

- Расположение: `_core/service/image_draw.php:213`
- Сигнатура: `function ellipseSector(array $coord, int|string|array|null $brdColor = 0x000000, int|string|array|null $bgrColor = 0XFFFFFF)`
- Описание: Выполняет логику `ellipse sector` и возвращает вычисленный результат.

### `fan\core\service\image_draw::line`

- Расположение: `_core/service/image_draw.php:253`
- Сигнатура: `function line(array $coord, int|string|array $brdColor = 0X000000)`
- Описание: Выполняет логику `line` и возвращает вычисленный результат.

### `fan\core\service\image_draw::lineVertical`

- Расположение: `_core/service/image_draw.php:274`
- Сигнатура: `function lineVertical(array $coord, int|string|array $brdColor = 0X000000)`
- Описание: Выполняет логику `line vertical` и возвращает вычисленный результат.

### `fan\core\service\image_draw::lineHorizontal`

- Расположение: `_core/service/image_draw.php:291`
- Сигнатура: `function lineHorizontal(array $coord, int|string|array $brdColor = 0X000000)`
- Описание: Выполняет логику `line horizontal` и возвращает вычисленный результат.

### `fan\core\service\image_draw::getFontWidth`

- Расположение: `_core/service/image_draw.php:307`
- Сигнатура: `function getFontWidth(int|float $fontNumber)`
- Описание: Получает, читает или вычисляет данные `font width` в рамках этого метода класса `fan\core\service\image_draw`.

### `fan\core\service\image_draw::getFontHeigth`

- Расположение: `_core/service/image_draw.php:319`
- Сигнатура: `function getFontHeigth(int|float $fontNumber)`
- Описание: Получает, читает или вычисляет данные `font heigth` в рамках этого метода класса `fan\core\service\image_draw`.

## `_core/service/image_modify.php`

### `fan\core\service\image_modify::__construct`

- Расположение: `_core/service/image_modify.php:83`
- Сигнатура: `function __construct($sourcePath, $createParam)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\image_modify`.

### `fan\core\service\image_modify::instance`

- Расположение: `_core/service/image_modify.php:103`
- Сигнатура: `function instance(?string $sourcePath = null, array $createParam = [], $saveInstance = true)`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.

### `fan\core\service\image_modify::setSource`

- Расположение: `_core/service/image_modify.php:127`
- Сигнатура: `function setSource(mixed $sourcePath, array $createParam = [])`
- Описание: Устанавливает, добавляет или сохраняет данные `source` в рамках этого метода класса `fan\core\service\image_modify`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\image_modify::setParam`

- Расположение: `_core/service/image_modify.php:194`
- Сигнатура: `function setParam(array $param)`
- Описание: Устанавливает, добавляет или сохраняет данные `param` в рамках этого метода класса `fan\core\service\image_modify`.

### `fan\core\service\image_modify::setTransparent`

- Расположение: `_core/service/image_modify.php:224`
- Сигнатура: `function setTransparent(int|string|array $color)`
- Описание: Устанавливает, добавляет или сохраняет данные `transparent` в рамках этого метода класса `fan\core\service\image_modify`.

### `fan\core\service\image_modify::getSourceParam`

- Расположение: `_core/service/image_modify.php:235`
- Сигнатура: `function getSourceParam()`
- Описание: Получает, читает или вычисляет данные `source param` в рамках этого метода класса `fan\core\service\image_modify`.

### `fan\core\service\image_modify::getWidth`

- Расположение: `_core/service/image_modify.php:245`
- Сигнатура: `function getWidth()`
- Описание: Получает, читает или вычисляет данные `width` в рамках этого метода класса `fan\core\service\image_modify`.

### `fan\core\service\image_modify::getSourceWidth`

- Расположение: `_core/service/image_modify.php:254`
- Сигнатура: `function getSourceWidth()`
- Описание: Получает, читает или вычисляет данные `source width` в рамках этого метода класса `fan\core\service\image_modify`.

### `fan\core\service\image_modify::getHeigth`

- Расположение: `_core/service/image_modify.php:263`
- Сигнатура: `function getHeigth()`
- Описание: Получает, читает или вычисляет данные `heigth` в рамках этого метода класса `fan\core\service\image_modify`.

### `fan\core\service\image_modify::getSourceHeigth`

- Расположение: `_core/service/image_modify.php:272`
- Сигнатура: `function getSourceHeigth()`
- Описание: Получает, читает или вычисляет данные `source heigth` в рамках этого метода класса `fan\core\service\image_modify`.

### `fan\core\service\image_modify::relocate`

- Расположение: `_core/service/image_modify.php:288`
- Сигнатура: `function relocate(int|float|null $width = null, int|float|null $height = null, $bgrColor = 0XFFFFFF)`
- Описание: Выполняет логику `relocate` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\image_modify::scal`

- Расположение: `_core/service/image_modify.php:327`
- Сигнатура: `function scal(&$width, &$height, int|float $fixRatio = 1, int|string|array $bgrColor = 0xFFFFFF)`
- Описание: Выполняет логику `scal` и возвращает вычисленный результат.

### `fan\core\service\image_modify::crop`

- Расположение: `_core/service/image_modify.php:385`
- Сигнатура: `function crop(int|float $left, int|float $top, int|float $width, int|float $height)`
- Описание: Выполняет логику `crop` и возвращает вычисленный результат.

### `fan\core\service\image_modify::rotate`

- Расположение: `_core/service/image_modify.php:425`
- Сигнатура: `function rotate(int|float $angle, int|string|array $bgrColor = 0xFFFFFF, int|float $fix = 0)`
- Описание: Выполняет логику `rotate` и возвращает вычисленный результат.

### `fan\core\service\image_modify::border`

- Расположение: `_core/service/image_modify.php:465`
- Сигнатура: `function border(int|float $depth, int|string|array $brdColor = 0x000000, bool $inline = false)`
- Описание: Выполняет логику `border` и возвращает вычисленный результат.

### `fan\core\service\image_modify::colorize`

- Расположение: `_core/service/image_modify.php:493`
- Сигнатура: `function colorize(int|string|array $color)`
- Описание: Выполняет логику `colorize` и возвращает вычисленный результат.

### `fan\core\service\image_modify::blur`

- Расположение: `_core/service/image_modify.php:505`
- Сигнатура: `function blur()`
- Описание: Выполняет логику `blur` и возвращает вычисленный результат.

### `fan\core\service\image_modify::grayscale`

- Расположение: `_core/service/image_modify.php:516`
- Сигнатура: `function grayscale()`
- Описание: Выполняет логику `grayscale` и возвращает вычисленный результат.

### `fan\core\service\image_modify::sepia`

- Расположение: `_core/service/image_modify.php:527`
- Сигнатура: `function sepia()`
- Описание: Выполняет логику `sepia` и возвращает вычисленный результат.

### `fan\core\service\image_modify::markering`

- Расположение: `_core/service/image_modify.php:542`
- Сигнатура: `function markering(string $markerMode = 'left_bottom', int|float $opacity = 10)`
- Описание: Выполняет логику `markering` и возвращает вычисленный результат.

### `fan\core\service\image_modify::adaptColor`

- Расположение: `_core/service/image_modify.php:614`
- Сигнатура: `function adaptColor(int|string|array $color)`
- Описание: Выполняет логику `adapt color` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\image_modify::getType`

- Расположение: `_core/service/image_modify.php:656`
- Сигнатура: `function getType()`
- Описание: Получает, читает или вычисляет данные `type` в рамках этого метода класса `fan\core\service\image_modify`.

### `fan\core\service\image_modify::getImageInfo`

- Расположение: `_core/service/image_modify.php:669`
- Сигнатура: `function getImageInfo($timeExpires = 0, $withContent = true)`
- Описание: Получает, читает или вычисляет данные `image info` в рамках этого метода класса `fan\core\service\image_modify`.

### `fan\core\service\image_modify::getImage`

- Расположение: `_core/service/image_modify.php:694`
- Сигнатура: `function getImage()`
- Описание: Получает, читает или вычисляет данные `image` в рамках этого метода класса `fan\core\service\image_modify`.

### `fan\core\service\image_modify::saveAsNew`

- Расположение: `_core/service/image_modify.php:712`
- Сигнатура: `function saveAsNew(string $newFile)`
- Описание: Устанавливает, добавляет или сохраняет данные `as new` в рамках этого метода класса `fan\core\service\image_modify`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\image_modify::saveAndReplace`

- Расположение: `_core/service/image_modify.php:735`
- Сигнатура: `function saveAndReplace(mixed $ext = 'bak')`
- Описание: Устанавливает, добавляет или сохраняет данные `and replace` в рамках этого метода класса `fan\core\service\image_modify`.
- Побочные эффекты: работает с файловой системой

### `fan\core\service\image_modify::_getCoord`

- Расположение: `_core/service/image_modify.php:756`
- Сигнатура: `function _getCoord(array $coord, string $key, int|float $default = 0)`
- Описание: Выполняет логику `get coord` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\image_modify::_getCoordDiff`

- Расположение: `_core/service/image_modify.php:778`
- Сигнатура: `function _getCoordDiff(array $coord, string $key1, string $key2)`
- Описание: Выполняет логику `get coord diff` и возвращает вычисленный результат.

### `fan\core\service\image_modify::_replaceImage`

- Расположение: `_core/service/image_modify.php:794`
- Сигнатура: `function _replaceImage(int|float $width, int|float $height, array $position, $srcImg = null, int|string|array|null $bgrColor = null)`
- Описание: Выполняет логику `replace image` и возвращает вычисленный результат.

### `fan\core\service\image_modify::correctSize`

- Расположение: `_core/service/image_modify.php:819`
- Сигнатура: `function correctSize(&$width, &$height, $oldWidth, $oldHeight)`
- Описание: Выполняет логику `correct size` и возвращает вычисленный результат.

## `_core/service/json.php`

### `fan\core\service\json::__construct`

- Расположение: `_core/service/json.php:45`
- Сигнатура: `function __construct($useBase64)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\json`.

### `fan\core\service\json::instance`

- Расположение: `_core/service/json.php:59`
- Сигнатура: `function instance(bool $useBase64 = false)`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.

### `fan\core\service\json::decode`

- Расположение: `_core/service/json.php:79`
- Сигнатура: `function decode(string $json, bool $array = true, mixed $depth = null, mixed $options = null)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `decode` для этого метода класса `fan\core\service\json`.
- Побочные эффекты: может выбрасывать исключения; сериализует или десериализует данные

### `fan\core\service\json::encode`

- Расположение: `_core/service/json.php:111`
- Сигнатура: `function encode(mixed $sourse, mixed $options = null, $logError = true)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `encode` для этого метода класса `fan\core\service\json`.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения; логирует или сообщает об ошибках; сериализует или десериализует данные

### `fan\core\service\json::fromXml`

- Расположение: `_core/service/json.php:147`
- Сигнатура: `function fromXml($xml, $ignoreXmlAttributes = true)`
- Описание: Выполняет workflow-логику `from xml`.

### `fan\core\service\json::fromYaml`

- Расположение: `_core/service/json.php:159`
- Сигнатура: `function fromYaml($yaml)`
- Описание: Выполняет workflow-логику `from yaml`.

### `fan\core\service\json::isError`

- Расположение: `_core/service/json.php:169`
- Сигнатура: `function isError()`
- Описание: Проверяет условие или валидирует данные `error` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\json::getError`

- Расположение: `_core/service/json.php:179`
- Сигнатура: `function getError()`
- Описание: Получает, читает или вычисляет данные `error` в рамках этого метода класса `fan\core\service\json`.

### `fan\core\service\json::getErrorText`

- Расположение: `_core/service/json.php:189`
- Сигнатура: `function getErrorText()`
- Описание: Получает, читает или вычисляет данные `error text` в рамках этого метода класса `fan\core\service\json`.

### `fan\core\service\json::prettyPrint`

- Расположение: `_core/service/json.php:214`
- Сигнатура: `function prettyPrint($json, $indent = "\t")`
- Описание: Выполняет логику `pretty print` и возвращает вычисленный результат.

### `fan\core\service\json::_code64`

- Расположение: `_core/service/json.php:254`
- Сигнатура: `function _code64(&$v, $k, string $op)`
- Описание: Выполняет workflow-логику `code64`.

## `_core/service/locale.php`

### `fan\core\service\locale::__construct`

- Расположение: `_core/service/locale.php:81`
- Сигнатура: `function __construct($allowIni = true)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\locale`.

### `fan\core\service\locale::getAvailableLanguages`

- Расположение: `_core/service/locale.php:100`
- Сигнатура: `function getAvailableLanguages()`
- Описание: Получает, читает или вычисляет данные `available languages` в рамках этого метода класса `fan\core\service\locale`.

### `fan\core\service\locale::getLanguageShortNames`

- Расположение: `_core/service/locale.php:110`
- Сигнатура: `function getLanguageShortNames()`
- Описание: Получает, читает или вычисляет данные `language short names` в рамках этого метода класса `fan\core\service\locale`.

### `fan\core\service\locale::setLanguage`

- Расположение: `_core/service/locale.php:122`
- Сигнатура: `function setLanguage(string $language)`
- Описание: Устанавливает, добавляет или сохраняет данные `language` в рамках этого метода класса `fan\core\service\locale`.

### `fan\core\service\locale::getLanguage`

- Расположение: `_core/service/locale.php:135`
- Сигнатура: `function getLanguage()`
- Описание: Получает, читает или вычисляет данные `language` в рамках этого метода класса `fan\core\service\locale`.

### `fan\core\service\locale::getLanguageId`

- Расположение: `_core/service/locale.php:145`
- Сигнатура: `function getLanguageId()`
- Описание: Получает, читает или вычисляет данные `language id` в рамках этого метода класса `fan\core\service\locale`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\locale::getDefaultLanguage`

- Расположение: `_core/service/locale.php:158`
- Сигнатура: `function getDefaultLanguage()`
- Описание: Получает, читает или вычисляет данные `default language` в рамках этого метода класса `fan\core\service\locale`.

### `fan\core\service\locale::addLanguage`

- Расположение: `_core/service/locale.php:172`
- Сигнатура: `function addLanguage(string $code, string $name, string $shortName)`
- Описание: Устанавливает, добавляет или сохраняет данные `language` в рамках этого метода класса `fan\core\service\locale`.

### `fan\core\service\locale::removeLanguage`

- Расположение: `_core/service/locale.php:187`
- Сигнатура: `function removeLanguage(string $code)`
- Описание: Удаляет или сбрасывает состояние `language` для этого метода класса `fan\core\service\locale`.

### `fan\core\service\locale::setCharacterSet`

- Расположение: `_core/service/locale.php:199`
- Сигнатура: `function setCharacterSet(string $characterSet)`
- Описание: Устанавливает, добавляет или сохраняет данные `character set` в рамках этого метода класса `fan\core\service\locale`.

### `fan\core\service\locale::getCharacterSet`

- Расположение: `_core/service/locale.php:212`
- Сигнатура: `function getCharacterSet()`
- Описание: Получает, читает или вычисляет данные `character set` в рамках этого метода класса `fan\core\service\locale`.

### `fan\core\service\locale::setTimeZone`

- Расположение: `_core/service/locale.php:224`
- Сигнатура: `function setTimeZone(string $timeZone)`
- Описание: Устанавливает, добавляет или сохраняет данные `time zone` в рамках этого метода класса `fan\core\service\locale`.

### `fan\core\service\locale::getTimeZone`

- Расположение: `_core/service/locale.php:238`
- Сигнатура: `function getTimeZone()`
- Описание: Получает, читает или вычисляет данные `time zone` в рамках этого метода класса `fan\core\service\locale`.

### `fan\core\service\locale::setCurrencyCode`

- Расположение: `_core/service/locale.php:250`
- Сигнатура: `function setCurrencyCode(string $currencyCode)`
- Описание: Устанавливает, добавляет или сохраняет данные `currency code` в рамках этого метода класса `fan\core\service\locale`.

### `fan\core\service\locale::getCurrencyCode`

- Расположение: `_core/service/locale.php:264`
- Сигнатура: `function getCurrencyCode()`
- Описание: Получает, читает или вычисляет данные `currency code` в рамках этого метода класса `fan\core\service\locale`.

### `fan\core\service\locale::setCountry`

- Расположение: `_core/service/locale.php:276`
- Сигнатура: `function setCountry(string $country)`
- Описание: Устанавливает, добавляет или сохраняет данные `country` в рамках этого метода класса `fan\core\service\locale`.

### `fan\core\service\locale::getCountry`

- Расположение: `_core/service/locale.php:290`
- Сигнатура: `function getCountry()`
- Описание: Получает, читает или вычисляет данные `country` в рамках этого метода класса `fan\core\service\locale`.

### `fan\core\service\locale::isUriParsing`

- Расположение: `_core/service/locale.php:300`
- Сигнатура: `function isUriParsing()`
- Описание: Проверяет условие или валидирует данные `uri parsing` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\locale::modifyUrn`

- Расположение: `_core/service/locale.php:313`
- Сигнатура: `function modifyUrn(string $urn, ?string $lng = null)`
- Описание: Выполняет логику `modify urn` и возвращает вычисленный результат.

### `fan\core\service\locale::getSwitcherLinks`

- Расположение: `_core/service/locale.php:335`
- Сигнатура: `function getSwitcherLinks(?string $url = null, $lng = null)`
- Описание: Получает, читает или вычисляет данные `switcher links` в рамках этого метода класса `fan\core\service\locale`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\locale::onSessonStart`

- Расположение: `_core/service/locale.php:365`
- Сигнатура: `function onSessonStart()`
- Описание: Выполняет workflow-логику `on sesson start`.

### `fan\core\service\locale::onSetNewUri`

- Расположение: `_core/service/locale.php:378`
- Сигнатура: `function onSetNewUri(\fan\core\service\matcher $matcher)`
- Описание: Выполняет workflow-логику `on set new uri`.

### `fan\core\service\locale::checkUriLng`

- Расположение: `_core/service/locale.php:395`
- Сигнатура: `function checkUriLng(string $url)`
- Описание: Проверяет условие или валидирует данные `uri lng` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\locale::onAppChange`

- Расположение: `_core/service/locale.php:410`
- Сигнатура: `function onAppChange()`
- Описание: Выполняет workflow-логику `on app change`.

### `fan\core\service\locale::_getSession`

- Расположение: `_core/service/locale.php:427`
- Сигнатура: `function _getSession($forse = true)`
- Описание: Выполняет логику `get session` и возвращает вычисленный результат.

### `fan\core\service\locale::_setBasicProp`

- Расположение: `_core/service/locale.php:440`
- Сигнатура: `function _setBasicProp()`
- Описание: Выполняет логику `set basic prop` и возвращает вычисленный результат.

### `fan\core\service\locale::_defineLocale`

- Расположение: `_core/service/locale.php:462`
- Сигнатура: `function _defineLocale()`
- Описание: Выполняет логику `define locale` и возвращает вычисленный результат.

### `fan\core\service\locale::_defineLanguage`

- Расположение: `_core/service/locale.php:484`
- Сигнатура: `function _defineLanguage($forse = false)`
- Описание: Выполняет логику `define language` и возвращает вычисленный результат.

### `fan\core\service\locale::_setCurrentLanguage`

- Расположение: `_core/service/locale.php:541`
- Сигнатура: `function _setCurrentLanguage(string $language, $forse = false)`
- Описание: Выполняет логику `set current language` и возвращает вычисленный результат.

### `fan\core\service\locale::_getDefultLanguages`

- Расположение: `_core/service/locale.php:573`
- Сигнатура: `function _getDefultLanguages(array $availableLng)`
- Описание: Выполняет логику `get defult languages` и возвращает вычисленный результат.

### `fan\core\service\locale::_defineExtraData`

- Расположение: `_core/service/locale.php:586`
- Сигнатура: `function _defineExtraData()`
- Описание: Выполняет логику `define extra data` и возвращает вычисленный результат.

### `fan\core\service\locale::_getLanguageByMatcher`

- Расположение: `_core/service/locale.php:611`
- Сигнатура: `function _getLanguageByMatcher($matcher = null)`
- Описание: Выполняет логику `get language by matcher` и возвращает вычисленный результат.

## `_core/service/log.php`

### `fan\core\service\log::getLogParser`

- Расположение: `_core/service/log.php:53`
- Сигнатура: `function getLogParser(string $variety, string $file)`
- Описание: Получает, читает или вычисляет данные `log parser` в рамках этого метода класса `fan\core\service\log`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\log::logData`

- Расположение: `_core/service/log.php:81`
- Сигнатура: `function logData(string $type, mixed $data, string $title, string $note = '', int|float|null $dataDepth = null, bool $isTrace = true, ?string $file = null)`
- Описание: Выполняет workflow-логику `log data`.

### `fan\core\service\log::logError`

- Расположение: `_core/service/log.php:107`
- Сигнатура: `function logError(string $type, string $message, string $title, string $note = '', bool $isTrace = true, ?string $file = null)`
- Описание: Выполняет workflow-логику `log error`.

### `fan\core\service\log::logMessage`

- Расположение: `_core/service/log.php:129`
- Сигнатура: `function logMessage(string $type, string $message, string $title, string $note = '', ?string $file = null)`
- Описание: Выполняет workflow-логику `log message`.

### `fan\core\service\log::_setAttribute`

- Расположение: `_core/service/log.php:144`
- Сигнатура: `function _setAttribute($title)`
- Описание: Выполняет логику `set attribute` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние; читает PHP superglobals

### `fan\core\service\log::_setNewData`

- Расположение: `_core/service/log.php:168`
- Сигнатура: `function _setNewData(mixed $data, int|float $dataDepth)`
- Описание: Выполняет логику `set new data` и возвращает вычисленный результат.

### `fan\core\service\log::_setNote`

- Расположение: `_core/service/log.php:213`
- Сигнатура: `function _setNote(&$row, string $note)`
- Описание: Выполняет workflow-логику `set note`.

### `fan\core\service\log::_getTrace`

- Расположение: `_core/service/log.php:225`
- Сигнатура: `function _getTrace()`
- Описание: Выполняет логику `get trace` и возвращает вычисленный результат.

### `fan\core\service\log::_saveLog`

- Расположение: `_core/service/log.php:281`
- Сигнатура: `function _saveLog(string $variety, $type, $row, string $file)`
- Описание: Выполняет workflow-логику `save log`.
- Побочные эффекты: сериализует или десериализует данные

### `fan\core\service\log::_getFullPath`

- Расположение: `_core/service/log.php:299`
- Сигнатура: `function _getFullPath(string $variety, $file)`
- Описание: Выполняет логику `get full path` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\log::_checkIncorrectSymbol`

- Расположение: `_core/service/log.php:344`
- Сигнатура: `function _checkIncorrectSymbol(string $str, $limitKey = null, $limitDefault = null)`
- Описание: Выполняет логику `check incorrect symbol` и возвращает вычисленный результат.

## `_core/service/log/parser_base.php`

### `fan\core\service\log\parser_base::setFilePath`

- Расположение: `_core/service/log/parser_base.php:104`
- Сигнатура: `function setFilePath($variety, $file)`
- Описание: Устанавливает, добавляет или сохраняет данные `file path` в рамках этого метода класса `fan\core\service\log\parser_base`.

### `fan\core\service\log\parser_base::setFacade`

- Расположение: `_core/service/log/parser_base.php:124`
- Сигнатура: `function setFacade(\fan\core\base\service $facade)`
- Описание: Устанавливает, добавляет или сохраняет данные `facade` в рамках этого метода класса `fan\core\service\log\parser_base`.

### `fan\core\service\log\parser_base::checkIndex`

- Расположение: `_core/service/log/parser_base.php:134`
- Сигнатура: `function checkIndex()`
- Описание: Проверяет условие или валидирует данные `index` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\log\parser_base::isData`

- Расположение: `_core/service/log/parser_base.php:166`
- Сигнатура: `function isData($reindex = false)`
- Описание: Проверяет условие или валидирует данные `data` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\log\parser_base::getQtt`

- Расположение: `_core/service/log/parser_base.php:181`
- Сигнатура: `function getQtt(bool $isUnique = false)`
- Описание: Получает, читает или вычисляет данные `qtt` в рамках этого метода класса `fan\core\service\log\parser_base`.

### `fan\core\service\log\parser_base::checkAfterLast`

- Расположение: `_core/service/log/parser_base.php:201`
- Сигнатура: `function checkAfterLast(mixed $lastKey, bool $isUnique = false)`
- Описание: Проверяет условие или валидирует данные `after last` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\log\parser_base::getDataArr`

- Расположение: `_core/service/log/parser_base.php:224`
- Сигнатура: `function getDataArr(int $first, int $qtt, bool $isUnique = false)`
- Описание: Получает, читает или вычисляет данные `data arr` в рамках этого метода класса `fan\core\service\log\parser_base`.
- Побочные эффекты: меняет HTTP/session состояние; сериализует или десериализует данные

### `fan\core\service\log\parser_base::getTrace`

- Расположение: `_core/service/log/parser_base.php:301`
- Сигнатура: `function getTrace(string $key)`
- Описание: Получает, читает или вычисляет данные `trace` в рамках этого метода класса `fan\core\service\log\parser_base`.
- Побочные эффекты: сериализует или десериализует данные

### `fan\core\service\log\parser_base::deleteRows`

- Расположение: `_core/service/log/parser_base.php:328`
- Сигнатура: `function deleteRows($keys, bool $isUnique = false)`
- Описание: Удаляет или сбрасывает состояние `rows` для этого метода класса `fan\core\service\log\parser_base`.
- Побочные эффекты: работает с файловой системой

### `fan\core\service\log\parser_base::_setOffset`

- Расположение: `_core/service/log/parser_base.php:377`
- Сигнатура: `function _setOffset(&$offsets, $k)`
- Описание: Выполняет логику `set offset` и возвращает вычисленный результат.

### `fan\core\service\log\parser_base::_recreateIndex`

- Расположение: `_core/service/log/parser_base.php:395`
- Сигнатура: `function _recreateIndex()`
- Описание: Выполняет логику `recreate index` и возвращает вычисленный результат.

### `fan\core\service\log\parser_base::_parceSting`

- Расположение: `_core/service/log/parser_base.php:435`
- Сигнатура: `function _parceSting(&$data, $str, $l)`
- Описание: Выполняет workflow-логику `parce sting`.

### `fan\core\service\log\parser_base::_setUniqueKeys`

- Расположение: `_core/service/log/parser_base.php:465`
- Сигнатура: `function _setUniqueKeys()`
- Описание: Выполняет workflow-логику `set unique keys`.

### `fan\core\service\log\parser_base::_dataTransfer`

- Расположение: `_core/service/log/parser_base.php:492`
- Сигнатура: `function _dataTransfer($fw, $fr, $start, $end)`
- Описание: Выполняет логику `data transfer` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой

### `fan\core\service\log\parser_base::_removeFile`

- Расположение: `_core/service/log/parser_base.php:511`
- Сигнатура: `function _removeFile()`
- Описание: Выполняет workflow-логику `remove file`.
- Побочные эффекты: работает с файловой системой

### `fan\core\service\log\parser_base::_writeIndexFile`

- Расположение: `_core/service/log/parser_base.php:526`
- Сигнатура: `function _writeIndexFile()`
- Описание: Выполняет логику `write index file` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой

## `_core/service/log/parser_bootstrap.php`

### `fan\core\service\log\parser_bootstrap::setFilePath`

- Расположение: `_core/service/log/parser_bootstrap.php:53`
- Сигнатура: `function setFilePath($variety, $file)`
- Описание: Устанавливает, добавляет или сохраняет данные `file path` в рамках этого метода класса `fan\core\service\log\parser_bootstrap`.
- Побочные эффекты: работает с файловой системой

## `_core/service/matcher.php`

### `fan\core\service\matcher::__construct`

- Расположение: `_core/service/matcher.php:32`
- Сигнатура: `function __construct($allowIni = true)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\matcher`.

### `fan\core\service\matcher::setUri`

- Расположение: `_core/service/matcher.php:47`
- Сигнатура: `function setUri(string $request, ?string $host = null, $shiftCurrent = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `uri` в рамках этого метода класса `fan\core\service\matcher`.

### `fan\core\service\matcher::setCli`

- Расположение: `_core/service/matcher.php:62`
- Сигнатура: `function setCli($file, $path = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `cli` в рамках этого метода класса `fan\core\service\matcher`.

### `fan\core\service\matcher::getLastIndex`

- Расположение: `_core/service/matcher.php:75`
- Сигнатура: `function getLastIndex()`
- Описание: Получает, читает или вычисляет данные `last index` в рамках этого метода класса `fan\core\service\matcher`.

### `fan\core\service\matcher::getCurrentIndex`

- Расположение: `_core/service/matcher.php:85`
- Сигнатура: `function getCurrentIndex()`
- Описание: Получает, читает или вычисляет данные `current index` в рамках этого метода класса `fan\core\service\matcher`.

### `fan\core\service\matcher::getStack`

- Расположение: `_core/service/matcher.php:96`
- Сигнатура: `function getStack()`
- Описание: Получает, читает или вычисляет данные `stack` в рамках этого метода класса `fan\core\service\matcher`.

### `fan\core\service\matcher::getItem`

- Расположение: `_core/service/matcher.php:110`
- Сигнатура: `function getItem(int $number)`
- Описание: Получает, читает или вычисляет данные `item` в рамках этого метода класса `fan\core\service\matcher`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\matcher::getLastItem`

- Расположение: `_core/service/matcher.php:123`
- Сигнатура: `function getLastItem()`
- Описание: Получает, читает или вычисляет данные `last item` в рамках этого метода класса `fan\core\service\matcher`.

### `fan\core\service\matcher::getCurrentItem`

- Расположение: `_core/service/matcher.php:133`
- Сигнатура: `function getCurrentItem()`
- Описание: Получает, читает или вычисляет данные `current item` в рамках этого метода класса `fan\core\service\matcher`.

### `fan\core\service\matcher::getUri`

- Расположение: `_core/service/matcher.php:146`
- Сигнатура: `function getUri(int $number)`
- Описание: Получает, читает или вычисляет данные `uri` в рамках этого метода класса `fan\core\service\matcher`.

### `fan\core\service\matcher::getLastUri`

- Расположение: `_core/service/matcher.php:157`
- Сигнатура: `function getLastUri()`
- Описание: Получает, читает или вычисляет данные `last uri` в рамках этого метода класса `fan\core\service\matcher`.

### `fan\core\service\matcher::getCurrentUri`

- Расположение: `_core/service/matcher.php:167`
- Сигнатура: `function getCurrentUri()`
- Описание: Получает, читает или вычисляет данные `current uri` в рамках этого метода класса `fan\core\service\matcher`.

### `fan\core\service\matcher::getHandler`

- Расположение: `_core/service/matcher.php:181`
- Сигнатура: `function getHandler(int $number, bool $forceDefine = false)`
- Описание: Получает, читает или вычисляет данные `handler` в рамках этого метода класса `fan\core\service\matcher`.

### `fan\core\service\matcher::getCurrentHandler`

- Расположение: `_core/service/matcher.php:194`
- Сигнатура: `function getCurrentHandler(bool $forceDefine = false)`
- Описание: Получает, читает или вычисляет данные `current handler` в рамках этого метода класса `fan\core\service\matcher`.

### `fan\core\service\matcher::getParsedData`

- Расположение: `_core/service/matcher.php:207`
- Сигнатура: `function getParsedData(int $number)`
- Описание: Получает, читает или вычисляет данные `parsed data` в рамках этого метода класса `fan\core\service\matcher`.

### `fan\core\service\matcher::getLastParsedData`

- Расположение: `_core/service/matcher.php:218`
- Сигнатура: `function getLastParsedData()`
- Описание: Получает, читает или вычисляет данные `last parsed data` в рамках этого метода класса `fan\core\service\matcher`.

### `fan\core\service\matcher::getCurrentParsedData`

- Расположение: `_core/service/matcher.php:228`
- Сигнатура: `function getCurrentParsedData()`
- Описание: Получает, читает или вычисляет данные `current parsed data` в рамках этого метода класса `fan\core\service\matcher`.

## `_core/service/matcher/item.php`

### `fan\core\service\matcher\item::__construct`

- Расположение: `_core/service/matcher/item.php:59`
- Сигнатура: `function __construct($index)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\matcher\item`.

### `fan\core\service\matcher\item::initOut`

- Расположение: `_core/service/matcher/item.php:77`
- Сигнатура: `function initOut(string $request, string $host)`
- Описание: Запускает или обрабатывает workflow `out` для этого метода класса `fan\core\service\matcher\item`.

### `fan\core\service\matcher\item::initCli`

- Расположение: `_core/service/matcher/item.php:96`
- Сигнатура: `function initCli(string $file, string $path)`
- Описание: Запускает или обрабатывает workflow `cli` для этого метода класса `fan\core\service\matcher\item`.

### `fan\core\service\matcher\item::setFacade`

- Расположение: `_core/service/matcher/item.php:115`
- Сигнатура: `function setFacade(\fan\core\service\matcher $facade)`
- Описание: Устанавливает, добавляет или сохраняет данные `facade` в рамках этого метода класса `fan\core\service\matcher\item`.

### `fan\core\service\matcher\item::getFacade`

- Расположение: `_core/service/matcher/item.php:129`
- Сигнатура: `function getFacade()`
- Описание: Получает, читает или вычисляет данные `facade` в рамках этого метода класса `fan\core\service\matcher\item`.

### `fan\core\service\matcher\item::getIndex`

- Расположение: `_core/service/matcher/item.php:139`
- Сигнатура: `function getIndex()`
- Описание: Получает, читает или вычисляет данные `index` в рамках этого метода класса `fan\core\service\matcher\item`.

### `fan\core\service\matcher\item::getHandler`

- Расположение: `_core/service/matcher/item.php:151`
- Сигнатура: `function getHandler(bool $forceDefine = false)`
- Описание: Получает, читает или вычисляет данные `handler` в рамках этого метода класса `fan\core\service\matcher\item`.

### `fan\core\service\matcher\item::preParseRequest`

- Расположение: `_core/service/matcher/item.php:167`
- Сигнатура: `function preParseRequest()`
- Описание: Выполняет логику `pre parse request` и возвращает вычисленный результат.
- Побочные эффекты: выполняет database операции

### `fan\core\service\matcher\item::getParsedSrc`

- Расположение: `_core/service/matcher/item.php:246`
- Сигнатура: `function getParsedSrc()`
- Описание: Получает, читает или вычисляет данные `parsed src` в рамках этого метода класса `fan\core\service\matcher\item`.

### `fan\core\service\matcher\item::parseRequest`

- Расположение: `_core/service/matcher/item.php:268`
- Сигнатура: `function parseRequest()`
- Описание: Создает, разбирает, форматирует или конвертирует данные `request` для этого метода класса `fan\core\service\matcher\item`.

### `fan\core\service\matcher\item::toArray`

- Расположение: `_core/service/matcher/item.php:289`
- Сигнатура: `function toArray()`
- Описание: Выполняет логику `to array` и возвращает вычисленный результат.

### `fan\core\service\matcher\item::_parseRequestedUri`

- Расположение: `_core/service/matcher/item.php:305`
- Сигнатура: `function _parseRequestedUri(string $request, string $host)`
- Описание: Выполняет логику `parse requested uri` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения; выполняет database операции; читает PHP superglobals

### `fan\core\service\matcher\item::_parseRequestedCli`

- Расположение: `_core/service/matcher/item.php:374`
- Сигнатура: `function _parseRequestedCli(string $file, string $path)`
- Описание: Выполняет логику `parse requested cli` и возвращает вычисленный результат.
- Побочные эффекты: читает PHP superglobals

### `fan\core\service\matcher\item::_defineHandler`

- Расположение: `_core/service/matcher/item.php:392`
- Сигнатура: `function _defineHandler(bool $forceDefine)`
- Описание: Выполняет логику `define handler` и возвращает вычисленный результат.

### `fan\core\service\matcher\item::_handlerDefinerSapiName`

- Расположение: `_core/service/matcher/item.php:436`
- Сигнатура: `function _handlerDefinerSapiName()`
- Описание: Выполняет логику `handler definer sapi name` и возвращает вычисленный результат.

### `fan\core\service\matcher\item::_handlerDefinerRequest`

- Расположение: `_core/service/matcher/item.php:454`
- Сигнатура: `function _handlerDefinerRequest(string $key, array $data)`
- Описание: Выполняет логику `handler definer request` и возвращает вычисленный результат.

### `fan\core\service\matcher\item::_parseRequestForTab`

- Расположение: `_core/service/matcher/item.php:477`
- Сигнатура: `function _parseRequestForTab(\fan\core\service\matcher\item\parsed $parsed, array $data)`
- Описание: Выполняет логику `parse request for tab` и возвращает вычисленный результат.

### `fan\core\service\matcher\item::_parseRequestForPlain`

- Расположение: `_core/service/matcher/item.php:524`
- Сигнатура: `function _parseRequestForPlain(\fan\core\service\matcher\item\parsed $parsed, array $data)`
- Описание: Выполняет логику `parse request for plain` и возвращает вычисленный результат.

### `fan\core\service\matcher\item::_parseRequestForCli`

- Расположение: `_core/service/matcher/item.php:550`
- Сигнатура: `function _parseRequestForCli(\fan\core\service\matcher\item\parsed $parsed, array $data)`
- Описание: Выполняет логику `parse request for cli` и возвращает вычисленный результат.

### `fan\core\service\matcher\item::_checkKey`

- Расположение: `_core/service/matcher/item.php:562`
- Сигнатура: `function _checkKey(string $key)`
- Описание: Выполняет workflow-логику `check key`.

### `fan\core\service\matcher\item::_makeException`

- Расположение: `_core/service/matcher/item.php:579`
- Сигнатура: `function _makeException(string $errMsg)`
- Описание: Выполняет workflow-логику `make exception`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\matcher\item::_getControllerMethod`

- Расположение: `_core/service/matcher/item.php:595`
- Сигнатура: `function _getControllerMethod(array $matches, string $pattern)`
- Описание: Выполняет логику `get controller method` и возвращает вычисленный результат.

### `fan\core\service\matcher\item::_getConfig`

- Расположение: `_core/service/matcher/item.php:618`
- Сигнатура: `function _getConfig(mixed $key, mixed $default = null)`
- Описание: Выполняет логику `get config` и возвращает вычисленный результат.

### `fan\core\service\matcher\item::offsetSet`

- Расположение: `_core/service/matcher/item.php:632`
- Сигнатура: `function offsetSet(string $key, mixed $value)`
- Описание: Выполняет workflow-логику `offset set`.

### `fan\core\service\matcher\item::offsetExists`

- Расположение: `_core/service/matcher/item.php:645`
- Сигнатура: `function offsetExists($key)`
- Описание: Выполняет логику `offset exists` и возвращает вычисленный результат.

### `fan\core\service\matcher\item::offsetUnset`

- Расположение: `_core/service/matcher/item.php:658`
- Сигнатура: `function offsetUnset($key)`
- Описание: Выполняет workflow-логику `offset unset`.

### `fan\core\service\matcher\item::offsetGet`

- Расположение: `_core/service/matcher/item.php:671`
- Сигнатура: `function offsetGet($key)`
- Описание: Выполняет логику `offset get` и возвращает вычисленный результат.

### `fan\core\service\matcher\item::__set`

- Расположение: `_core/service/matcher/item.php:686`
- Сигнатура: `function __set($key, $value)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\matcher\item`.

### `fan\core\service\matcher\item::__get`

- Расположение: `_core/service/matcher/item.php:698`
- Сигнатура: `function __get($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\matcher\item`.

## `_core/service/matcher/item/base.php`

### `fan\core\service\matcher\item\base::__construct`

- Расположение: `_core/service/matcher/item/base.php:57`
- Сигнатура: `function __construct(\fan\core\service\matcher\item $item)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\matcher\item\base`.

### `fan\core\service\matcher\item\base::__set`

- Расположение: `_core/service/matcher/item/base.php:73`
- Сигнатура: `function __set($key, $value)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\matcher\item\base`.

### `fan\core\service\matcher\item\base::__get`

- Расположение: `_core/service/matcher/item/base.php:85`
- Сигнатура: `function __get($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\matcher\item\base`.

### `fan\core\service\matcher\item\base::offsetSet`

- Расположение: `_core/service/matcher/item/base.php:100`
- Сигнатура: `function offsetSet($key, mixed $value)`
- Описание: Выполняет логику `offset set` и возвращает вычисленный результат.

### `fan\core\service\matcher\item\base::offsetGet`

- Расположение: `_core/service/matcher/item/base.php:112`
- Сигнатура: `function offsetGet($key)`
- Описание: Выполняет логику `offset get` и возвращает вычисленный результат.

### `fan\core\service\matcher\item\base::offsetExists`

- Расположение: `_core/service/matcher/item/base.php:124`
- Сигнатура: `function offsetExists($key)`
- Описание: Выполняет логику `offset exists` и возвращает вычисленный результат.

### `fan\core\service\matcher\item\base::offsetUnset`

- Расположение: `_core/service/matcher/item/base.php:137`
- Сигнатура: `function offsetUnset($key)`
- Описание: Выполняет workflow-логику `offset unset`.

### `fan\core\service\matcher\item\base::rewind`

- Расположение: `_core/service/matcher/item/base.php:148`
- Сигнатура: `function rewind()`
- Описание: Выполняет workflow-логику `rewind`.

### `fan\core\service\matcher\item\base::current`

- Расположение: `_core/service/matcher/item/base.php:158`
- Сигнатура: `function current()`
- Описание: Выполняет логику `current` и возвращает вычисленный результат.

### `fan\core\service\matcher\item\base::key`

- Расположение: `_core/service/matcher/item/base.php:168`
- Сигнатура: `function key()`
- Описание: Выполняет логику `key` и возвращает вычисленный результат.

### `fan\core\service\matcher\item\base::next`

- Расположение: `_core/service/matcher/item/base.php:178`
- Сигнатура: `function next()`
- Описание: Выполняет workflow-логику `next`.

### `fan\core\service\matcher\item\base::valid`

- Расположение: `_core/service/matcher/item/base.php:188`
- Сигнатура: `function valid()`
- Описание: Выполняет логику `valid` и возвращает вычисленный результат.

### `fan\core\service\matcher\item\base::set`

- Расположение: `_core/service/matcher/item/base.php:202`
- Сигнатура: `function set(string $key, mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\service\matcher\item\base`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\matcher\item\base::get`

- Расположение: `_core/service/matcher/item/base.php:222`
- Сигнатура: `function get(string $key)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\service\matcher\item\base`.

### `fan\core\service\matcher\item\base::toArray`

- Расположение: `_core/service/matcher/item/base.php:233`
- Сигнатура: `function toArray()`
- Описание: Выполняет логику `to array` и возвращает вычисленный результат.

### `fan\core\service\matcher\item\base::setFacade`

- Расположение: `_core/service/matcher/item/base.php:245`
- Сигнатура: `function setFacade(\fan\core\service\matcher $facade)`
- Описание: Устанавливает, добавляет или сохраняет данные `facade` в рамках этого метода класса `fan\core\service\matcher\item\base`.

### `fan\core\service\matcher\item\base::_checkKey`

- Расположение: `_core/service/matcher/item/base.php:261`
- Сигнатура: `function _checkKey(string $key, string $method = '')`
- Описание: Выполняет логику `check key` и возвращает вычисленный результат.

### `fan\core\service\matcher\item\base::_getCurrentKey`

- Расположение: `_core/service/matcher/item/base.php:279`
- Сигнатура: `function _getCurrentKey()`
- Описание: Выполняет логику `get current key` и возвращает вычисленный результат.

### `fan\core\service\matcher\item\base::_makeException`

- Расположение: `_core/service/matcher/item/base.php:295`
- Сигнатура: `function _makeException(string $errMsg)`
- Описание: Выполняет workflow-логику `make exception`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\matcher\item\base::_getConfig`

- Расположение: `_core/service/matcher/item/base.php:311`
- Сигнатура: `function _getConfig($key, $default = null)`
- Описание: Выполняет логику `get config` и возвращает вычисленный результат.

## `_core/service/matcher/item/cli.php`

### `fan\core\service\matcher\item\cli::__toString`

- Расположение: `_core/service/matcher/item/cli.php:45`
- Сигнатура: `function __toString()`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\matcher\item\cli`.

## `_core/service/matcher/item/handler.php`

### `fan\core\service\matcher\item\handler::offsetGet`

- Расположение: `_core/service/matcher/item/handler.php:43`
- Сигнатура: `function offsetGet($key)`
- Описание: Выполняет логику `offset get` и возвращает вычисленный результат.

## `_core/service/matcher/item/parsed.php`

### `fan\core\service\matcher\item\parsed::__toString`

- Расположение: `_core/service/matcher/item/parsed.php:59`
- Сигнатура: `function __toString()`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\matcher\item\parsed`.

### `fan\core\service\matcher\item\parsed::getMainRequest`

- Расположение: `_core/service/matcher/item/parsed.php:70`
- Сигнатура: `function getMainRequest()`
- Описание: Получает, читает или вычисляет данные `main request` в рамках этого метода класса `fan\core\service\matcher\item\parsed`.

### `fan\core\service\matcher\item\parsed::getAddRequest`

- Расположение: `_core/service/matcher/item/parsed.php:83`
- Сигнатура: `function getAddRequest()`
- Описание: Получает, читает или вычисляет данные `add request` в рамках этого метода класса `fan\core\service\matcher\item\parsed`.

### `fan\core\service\matcher\item\parsed::getBothRequest`

- Расположение: `_core/service/matcher/item/parsed.php:96`
- Сигнатура: `function getBothRequest()`
- Описание: Получает, читает или вычисляет данные `both request` в рамках этого метода класса `fan\core\service\matcher\item\parsed`.

### `fan\core\service\matcher\item\parsed::getClass`

- Расположение: `_core/service/matcher/item/parsed.php:109`
- Сигнатура: `function getClass()`
- Описание: Получает, читает или вычисляет данные `class` в рамках этого метода класса `fan\core\service\matcher\item\parsed`.

### `fan\core\service\matcher\item\parsed::getFile`

- Расположение: `_core/service/matcher/item/parsed.php:129`
- Сигнатура: `function getFile()`
- Описание: Получает, читает или вычисляет данные `file` в рамках этого метода класса `fan\core\service\matcher\item\parsed`.

### `fan\core\service\matcher\item\parsed::getUrn`

- Расположение: `_core/service/matcher/item/parsed.php:148`
- Сигнатура: `function getUrn()`
- Описание: Получает, читает или вычисляет данные `urn` в рамках этого метода класса `fan\core\service\matcher\item\parsed`.

### `fan\core\service\matcher\item\parsed::toArray`

- Расположение: `_core/service/matcher/item/parsed.php:172`
- Сигнатура: `function toArray()`
- Описание: Выполняет логику `to array` и возвращает вычисленный результат.

## `_core/service/matcher/item/source.php`

### `fan\core\service\matcher\item\source::__toString`

- Расположение: `_core/service/matcher/item/source.php:40`
- Сигнатура: `function __toString()`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\matcher\item\source`.

## `_core/service/matcher/item/uri.php`

### `fan\core\service\matcher\item\uri::__toString`

- Расположение: `_core/service/matcher/item/uri.php:50`
- Сигнатура: `function __toString()`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\matcher\item\uri`.
- Побочные эффекты: выполняет database операции

## `_core/service/matcher/stack.php`

### `fan\core\service\matcher\stack::setFacade`

- Расположение: `_core/service/matcher/stack.php:38`
- Сигнатура: `function setFacade(\fan\core\base\service $facade)`
- Описание: Устанавливает, добавляет или сохраняет данные `facade` в рамках этого метода класса `fan\core\service\matcher\stack`.

### `fan\core\service\matcher\stack::setNewItem`

- Расположение: `_core/service/matcher/stack.php:54`
- Сигнатура: `function setNewItem(string $request, ?string $position = null, $shiftCurrent = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `new item` в рамках этого метода класса `fan\core\service\matcher\stack`.

### `fan\core\service\matcher\stack::getLastIndex`

- Расположение: `_core/service/matcher/stack.php:83`
- Сигнатура: `function getLastIndex()`
- Описание: Получает, читает или вычисляет данные `last index` в рамках этого метода класса `fan\core\service\matcher\stack`.

### `fan\core\service\matcher\stack::getCurrentIndex`

- Расположение: `_core/service/matcher/stack.php:93`
- Сигнатура: `function getCurrentIndex()`
- Описание: Получает, читает или вычисляет данные `current index` в рамках этого метода класса `fan\core\service\matcher\stack`.

## `_core/service/obfuscator.php`

### `fan\core\service\obfuscator::__construct`

- Расположение: `_core/service/obfuscator.php:65`
- Сигнатура: `function __construct($type)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\obfuscator`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\obfuscator::instance`

- Расположение: `_core/service/obfuscator.php:87`
- Сигнатура: `function instance(string $type)`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.

### `fan\core\service\obfuscator::getNewList`

- Расположение: `_core/service/obfuscator.php:105`
- Сигнатура: `function getNewList(array $fileList)`
- Описание: Получает, читает или вычисляет данные `new list` в рамках этого метода класса `fan\core\service\obfuscator`.

### `fan\core\service\obfuscator::obfuscate`

- Расположение: `_core/service/obfuscator.php:122`
- Сигнатура: `function obfuscate(string $text)`
- Описание: Выполняет логику `obfuscate` и возвращает вычисленный результат.

### `fan\core\service\obfuscator::getFileData`

- Расположение: `_core/service/obfuscator.php:135`
- Сигнатура: `function getFileData(string $name)`
- Описание: Получает, читает или вычисляет данные `file data` в рамках этого метода класса `fan\core\service\obfuscator`.

### `fan\core\service\obfuscator::getHeaders`

- Расположение: `_core/service/obfuscator.php:149`
- Сигнатура: `function getHeaders(string $name, ?int $length = null)`
- Описание: Получает, читает или вычисляет данные `headers` в рамках этого метода класса `fan\core\service\obfuscator`.

### `fan\core\service\obfuscator::getConfig`

- Расположение: `_core/service/obfuscator.php:177`
- Сигнатура: `function getConfig($key = null, $default = null)`
- Описание: Получает, читает или вычисляет данные `config` в рамках этого метода класса `fan\core\service\obfuscator`.

### `fan\core\service\obfuscator::isEnabled`

- Расположение: `_core/service/obfuscator.php:187`
- Сигнатура: `function isEnabled()`
- Описание: Проверяет условие или валидирует данные `enabled` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\obfuscator::resetEnabled`

- Расположение: `_core/service/obfuscator.php:197`
- Сигнатура: `function resetEnabled()`
- Описание: Удаляет или сбрасывает состояние `enabled` для этого метода класса `fan\core\service\obfuscator`.

### `fan\core\service\obfuscator::_saveInstance`

- Расположение: `_core/service/obfuscator.php:210`
- Сигнатура: `function _saveInstance()`
- Описание: Выполняет логику `save instance` и возвращает вычисленный результат.

### `fan\core\service\obfuscator::_defineDir`

- Расположение: `_core/service/obfuscator.php:223`
- Сигнатура: `function _defineDir()`
- Описание: Выполняет логику `define dir` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой; может выбрасывать исключения

### `fan\core\service\obfuscator::_makeNewCssList`

- Расположение: `_core/service/obfuscator.php:247`
- Сигнатура: `function _makeNewCssList(array $fileList)`
- Описание: Выполняет логику `make new css list` и возвращает вычисленный результат.

### `fan\core\service\obfuscator::_makeNewJsList`

- Расположение: `_core/service/obfuscator.php:264`
- Сигнатура: `function _makeNewJsList(array $fileList)`
- Описание: Выполняет логику `make new js list` и возвращает вычисленный результат.

### `fan\core\service\obfuscator::_makeNewList`

- Расположение: `_core/service/obfuscator.php:279`
- Сигнатура: `function _makeNewList($list)`
- Описание: Выполняет логику `make new list` и возвращает вычисленный результат.

### `fan\core\service\obfuscator::_makeFile`

- Расположение: `_core/service/obfuscator.php:327`
- Сигнатура: `function _makeFile($list, $name)`
- Описание: Выполняет логику `make file` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой; может выбрасывать исключения

### `fan\core\service\obfuscator::_getDelegate`

- Расположение: `_core/service/obfuscator.php:403`
- Сигнатура: `function _getDelegate($class)`
- Описание: Выполняет логику `get delegate` и возвращает вычисленный результат.

## `_core/service/obfuscator/base.php`

### `fan\core\service\obfuscator\base::__construct`

- Расположение: `_core/service/obfuscator/base.php:47`
- Сигнатура: `function __construct()`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\obfuscator\base`.

### `fan\core\service\obfuscator\base::setFacade`

- Расположение: `_core/service/obfuscator/base.php:62`
- Сигнатура: `function setFacade(\fan\core\service\obfuscator $facade)`
- Описание: Устанавливает, добавляет или сохраняет данные `facade` в рамках этого метода класса `fan\core\service\obfuscator\base`.

### `fan\core\service\obfuscator\base::obfuscate`

- Расположение: `_core/service/obfuscator/base.php:87`
- Сигнатура: `function obfuscate($text);`
- Описание: Выполняет workflow-логику `obfuscate`.

## `_core/service/obfuscator/simple.php`

### `fan\core\service\obfuscator\simple::obfuscate`

- Расположение: `_core/service/obfuscator/simple.php:34`
- Сигнатура: `function obfuscate(string $text)`
- Описание: Выполняет логику `obfuscate` и возвращает вычисленный результат.

## `_core/service/pager.php`

### `fan\core\service\pager::__construct`

- Расположение: `_core/service/pager.php:61`
- Сигнатура: `function __construct(\fan\core\block\base $block)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\pager`.

### `fan\core\service\pager::instance`

- Расположение: `_core/service/pager.php:75`
- Сигнатура: `function instance(string|\fan\core\block\base $block)`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения

### `fan\core\service\pager::setPageNum`

- Расположение: `_core/service/pager.php:100`
- Сигнатура: `function setPageNum(int|float $pageNum, bool $force = false)`
- Описание: Устанавливает, добавляет или сохраняет данные `page num` в рамках этого метода класса `fan\core\service\pager`.

### `fan\core\service\pager::getPageNum`

- Расположение: `_core/service/pager.php:121`
- Сигнатура: `function getPageNum()`
- Описание: Получает, читает или вычисляет данные `page num` в рамках этого метода класса `fan\core\service\pager`.

### `fan\core\service\pager::setPageQtt`

- Расположение: `_core/service/pager.php:134`
- Сигнатура: `function setPageQtt(int|float $pageQtt, bool $force = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `page qtt` в рамках этого метода класса `fan\core\service\pager`.

### `fan\core\service\pager::getPageQtt`

- Расположение: `_core/service/pager.php:155`
- Сигнатура: `function getPageQtt()`
- Описание: Получает, читает или вычисляет данные `page qtt` в рамках этого метода класса `fan\core\service\pager`.

### `fan\core\service\pager::setItemPerPage`

- Расположение: `_core/service/pager.php:167`
- Сигнатура: `function setItemPerPage(int|float $itemPerPage)`
- Описание: Устанавливает, добавляет или сохраняет данные `item per page` в рамках этого метода класса `fan\core\service\pager`.

### `fan\core\service\pager::getItemPerPage`

- Расположение: `_core/service/pager.php:181`
- Сигнатура: `function getItemPerPage()`
- Описание: Получает, читает или вычисляет данные `item per page` в рамках этого метода класса `fan\core\service\pager`.

### `fan\core\service\pager::setItemQtt`

- Расположение: `_core/service/pager.php:194`
- Сигнатура: `function setItemQtt($itemQtt, $definePageNum = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `item qtt` в рамках этого метода класса `fan\core\service\pager`.

### `fan\core\service\pager::getItemQtt`

- Расположение: `_core/service/pager.php:209`
- Сигнатура: `function getItemQtt()`
- Описание: Получает, читает или вычисляет данные `item qtt` в рамках этого метода класса `fan\core\service\pager`.

### `fan\core\service\pager::getOffset`

- Расположение: `_core/service/pager.php:219`
- Сигнатура: `function getOffset()`
- Описание: Получает, читает или вычисляет данные `offset` в рамках этого метода класса `fan\core\service\pager`.

### `fan\core\service\pager::getItemsByParam`

- Расположение: `_core/service/pager.php:234`
- Сигнатура: `function getItemsByParam($param = [], string $orderBy = '')`
- Описание: Получает, читает или вычисляет данные `items by param` в рамках этого метода класса `fan\core\service\pager`.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения

### `fan\core\service\pager::getItemsByKey`

- Расположение: `_core/service/pager.php:255`
- Сигнатура: `function getItemsByKey(string|\fan\core\base\model\entity $ett, string $sqlKey = '', $param = [], string $orderBy = '')`
- Описание: Получает, читает или вычисляет данные `items by key` в рамках этого метода класса `fan\core\service\pager`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\pager::getPageUri`

- Расположение: `_core/service/pager.php:282`
- Сигнатура: `function getPageUri($page, $addExt = true, $addSid = null, $protocol = null)`
- Описание: Получает, читает или вычисляет данные `page uri` в рамках этого метода класса `fan\core\service\pager`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\pager::_countItemByEtt`

- Расположение: `_core/service/pager.php:310`
- Сигнатура: `function _countItemByEtt(mixed $param, string|\fan\core\base\model\entity $ett, ?string $sqlKey = null)`
- Описание: Выполняет логику `count item by ett` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\pager::_definePageNum`

- Расположение: `_core/service/pager.php:326`
- Сигнатура: `function _definePageNum(bool $force = false)`
- Описание: Выполняет логику `define page num` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\pager::_defineItemPerPage`

- Расположение: `_core/service/pager.php:344`
- Сигнатура: `function _defineItemPerPage(bool $force = false)`
- Описание: Выполняет логику `define item per page` и возвращает вычисленный результат.

### `fan\core\service\pager::_definePageQtt`

- Расположение: `_core/service/pager.php:361`
- Сигнатура: `function _definePageQtt($force = false)`
- Описание: Выполняет логику `define page qtt` и возвращает вычисленный результат.

## `_core/service/plain.php`

### `fan\core\service\plain::__construct`

- Расположение: `_core/service/plain.php:66`
- Сигнатура: `function __construct($allowIni = true)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\plain`.

### `fan\core\service\plain::getContent`

- Расположение: `_core/service/plain.php:83`
- Сигнатура: `function getContent($key, string $controllerClass, string $method)`
- Описание: Получает, читает или вычисляет данные `content` в рамках этого метода класса `fan\core\service\plain`.

### `fan\core\service\plain::getHandleData`

- Расположение: `_core/service/plain.php:97`
- Сигнатура: `function getHandleData()`
- Описание: Получает, читает или вычисляет данные `handle data` в рамках этого метода класса `fan\core\service\plain`.

### `fan\core\service\plain::transfer`

- Расположение: `_core/service/plain.php:111`
- Сигнатура: `function transfer(string $request, ?string $host = null, bool $shiftCurrent = true)`
- Описание: Выполняет логику `transfer` и возвращает вычисленный результат.

### `fan\core\service\plain::addHeader`

- Расположение: `_core/service/plain.php:128`
- Сигнатура: `function addHeader(string $key, string $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `header` в рамках этого метода класса `fan\core\service\plain`.
- Побочные эффекты: меняет HTTP/session состояние; может выбрасывать исключения

### `fan\core\service\plain::setHeaders`

- Расположение: `_core/service/plain.php:144`
- Сигнатура: `function setHeaders($headers)`
- Описание: Устанавливает, добавляет или сохраняет данные `headers` в рамках этого метода класса `fan\core\service\plain`.

### `fan\core\service\plain::setErrorMessage`

- Расположение: `_core/service/plain.php:162`
- Сигнатура: `function setErrorMessage(string $errMsg, int|float $errCode = 404)`
- Описание: Устанавливает, добавляет или сохраняет данные `error message` в рамках этого метода класса `fan\core\service\plain`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\plain::isError`

- Расположение: `_core/service/plain.php:182`
- Сигнатура: `function isError()`
- Описание: Проверяет условие или валидирует данные `error` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\plain::_getFinalContent`

- Расположение: `_core/service/plain.php:198`
- Сигнатура: `function _getFinalContent($method)`
- Описание: Выполняет логику `get final content` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\plain::_setController`

- Расположение: `_core/service/plain.php:222`
- Сигнатура: `function _setController($controllerKey, string $controllerClass)`
- Описание: Выполняет логику `set controller` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\plain::_assignHeaders`

- Расположение: `_core/service/plain.php:240`
- Сигнатура: `function _assignHeaders()`
- Описание: Выполняет логику `assign headers` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние; использует service locator/helpers фреймворка

### `fan\core\service\plain::_defineError404`

- Расположение: `_core/service/plain.php:251`
- Сигнатура: `function _defineError404()`
- Описание: Выполняет логику `define error404` и возвращает вычисленный результат.

## `_core/service/reflector.php`

### `fan\core\service\reflector::setReflection`

- Расположение: `_core/service/reflector.php:37`
- Сигнатура: `function setReflection(&$className)`
- Описание: Устанавливает, добавляет или сохраняет данные `reflection` в рамках этого метода класса `fan\core\service\reflector`.

### `fan\core\service\reflector::getReflection`

- Расположение: `_core/service/reflector.php:78`
- Сигнатура: `function getReflection($className)`
- Описание: Получает, читает или вычисляет данные `reflection` в рамках этого метода класса `fan\core\service\reflector`.

### `fan\core\service\reflector::getParentChain`

- Расположение: `_core/service/reflector.php:91`
- Сигнатура: `function getParentChain($className)`
- Описание: Получает, читает или вычисляет данные `parent chain` в рамках этого метода класса `fan\core\service\reflector`.

### `fan\core\service\reflector::getParentPaths`

- Расположение: `_core/service/reflector.php:104`
- Сигнатура: `function getParentPaths($className)`
- Описание: Получает, читает или вычисляет данные `parent paths` в рамках этого метода класса `fan\core\service\reflector`.

## `_core/service/request.php`

### `fan\core\service\request::__construct`

- Расположение: `_core/service/request.php:94`
- Сигнатура: `function __construct()`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\request`.

### `fan\core\service\request::__get`

- Расположение: `_core/service/request.php:124`
- Сигнатура: `function __get($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\request`.

### `fan\core\service\request::__invoke`

- Расположение: `_core/service/request.php:137`
- Сигнатура: `function __invoke($key, $order = null, $default = null)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\request`.

### `fan\core\service\request::get`

- Расположение: `_core/service/request.php:154`
- Сигнатура: `function get(string $key, ?string $order = null, mixed $default = null, $extraAdd = true)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\service\request`.

### `fan\core\service\request::getAll`

- Расположение: `_core/service/request.php:173`
- Сигнатура: `function getAll(?string $order = null, $default = [], $extraAdd = true)`
- Описание: Получает, читает или вычисляет данные `all` в рамках этого метода класса `fan\core\service\request`.

### `fan\core\service\request::getRawPost`

- Расположение: `_core/service/request.php:192`
- Сигнатура: `function getRawPost(string $convFormat = 'json', $useBase64 = false)`
- Описание: Получает, читает или вычисляет данные `raw post` в рамках этого метода класса `fan\core\service\request`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\request::conv`

- Расположение: `_core/service/request.php:208`
- Сигнатура: `function conv(mixed $item)`
- Описание: Runs the conv operation and returns its result.
- Параметры: mixed $item Input value for the item argument.
- Возвращает: mixed Returns the value produced by the operation.

### `fan\core\service\request::set`

- Расположение: `_core/service/request.php:229`
- Сигнатура: `function set(string $key, mixed $value, string $type = 'P')`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\service\request`.

### `fan\core\service\request::remove`

- Расположение: `_core/service/request.php:245`
- Сигнатура: `function remove(string $key, string $type = 'G', $fullUnset = false)`
- Описание: Удаляет или сбрасывает состояние `элемент` для этого метода класса `fan\core\service\request`.

### `fan\core\service\request::getQueryString`

- Расположение: `_core/service/request.php:268`
- Сигнатура: `function getQueryString($byGetData = true, $current = true, $sprtr = null)`
- Описание: Получает, читает или вычисляет данные `query string` в рамках этого метода класса `fan\core\service\request`.
- Побочные эффекты: выполняет database операции; читает PHP superglobals

### `fan\core\service\request::getInfoString`

- Расположение: `_core/service/request.php:284`
- Сигнатура: `function getInfoString()`
- Описание: Получает, читает или вычисляет данные `info string` в рамках этого метода класса `fan\core\service\request`.
- Побочные эффекты: читает PHP superglobals

### `fan\core\service\request::checkIsData`

- Расположение: `_core/service/request.php:304`
- Сигнатура: `function checkIsData(?string $order = null, $extraAdd = true)`
- Описание: Проверяет условие или валидирует данные `is data` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\request::getAddDelimiter`

- Расположение: `_core/service/request.php:319`
- Сигнатура: `function getAddDelimiter()`
- Описание: Получает, читает или вычисляет данные `add delimiter` в рамках этого метода класса `fan\core\service\request`.

### `fan\core\service\request::_separateData`

- Расположение: `_core/service/request.php:336`
- Сигнатура: `function _separateData(string $order, $extraAdd)`
- Описание: Выполняет логику `separate data` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\request::_makeHeaders`

- Расположение: `_core/service/request.php:366`
- Сигнатура: `function _makeHeaders()`
- Описание: Выполняет логику `make headers` и возвращает вычисленный результат.
- Побочные эффекты: читает PHP superglobals

### `fan\core\service\request::_makeAddRequest`

- Расположение: `_core/service/request.php:395`
- Сигнатура: `function _makeAddRequest($extraAdd)`
- Описание: Выполняет логику `make add request` и возвращает вычисленный результат.

### `fan\core\service\request::_makeMainRequest`

- Расположение: `_core/service/request.php:419`
- Сигнатура: `function _makeMainRequest()`
- Описание: Выполняет логику `make main request` и возвращает вычисленный результат.

### `fan\core\service\request::_makeBothRequest`

- Расположение: `_core/service/request.php:430`
- Сигнатура: `function _makeBothRequest()`
- Описание: Выполняет логику `make both request` и возвращает вычисленный результат.

### `fan\core\service\request::_makeGet`

- Расположение: `_core/service/request.php:442`
- Сигнатура: `function _makeGet()`
- Описание: Выполняет логику `make get` и возвращает вычисленный результат.

### `fan\core\service\request::_makeCookies`

- Расположение: `_core/service/request.php:457`
- Сигнатура: `function _makeCookies()`
- Описание: Выполняет логику `make cookies` и возвращает вычисленный результат.

### `fan\core\service\request::_makeOptions`

- Расположение: `_core/service/request.php:467`
- Сигнатура: `function _makeOptions()`
- Описание: Выполняет логику `make options` и возвращает вычисленный результат.

### `fan\core\service\request::_getRequestData`

- Расположение: `_core/service/request.php:482`
- Сигнатура: `function _getRequestData(string $prop)`
- Описание: Выполняет логику `get request data` и возвращает вычисленный результат.

### `fan\core\service\request::_isAllowToSet`

- Расположение: `_core/service/request.php:497`
- Сигнатура: `function _isAllowToSet(string $type)`
- Описание: Выполняет логику `is allow to set` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\request::_getMatcher`

- Расположение: `_core/service/request.php:514`
- Сигнатура: `function _getMatcher()`
- Описание: Выполняет логику `get matcher` и возвращает вычисленный результат.

## `_core/service/rest.php`

### `fan\core\service\rest::__construct`

- Расположение: `_core/service/rest.php:42`
- Сигнатура: `function __construct($connectionName)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\rest`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\rest::__destruct`

- Расположение: `_core/service/rest.php:65`
- Сигнатура: `function __destruct()`
- Описание: Завершает работу объекта и выполняет отложенную очистку для этого метода класса `fan\core\service\rest`.

### `fan\core\service\rest::instance`

- Расположение: `_core/service/rest.php:75`
- Сигнатура: `function instance(?string $connectionName = NULL)`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.

### `fan\core\service\rest::get`

- Расположение: `_core/service/rest.php:99`
- Сигнатура: `function get(string $urlSuffix, mixed $data = null)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\service\rest`.

### `fan\core\service\rest::post`

- Расположение: `_core/service/rest.php:124`
- Сигнатура: `function post(string $urlSuffix, mixed $data, $format = 'json')`
- Описание: Выполняет логику `post` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\rest::delete`

- Расположение: `_core/service/rest.php:146`
- Сигнатура: `function delete(string $urlSuffix, string $data)`
- Описание: Удаляет или сбрасывает состояние `данные` для этого метода класса `fan\core\service\rest`.

### `fan\core\service\rest::_callPutRequest`

- Расположение: `_core/service/rest.php:164`
- Сигнатура: `function _callPutRequest(string $urlSuffix, string $data)`
- Описание: Выполняет логику `call put request` и возвращает вычисленный результат.

### `fan\core\service\rest::getConnectionName`

- Расположение: `_core/service/rest.php:182`
- Сигнатура: `function getConnectionName()`
- Описание: Получает, читает или вычисляет данные `connection name` в рамках этого метода класса `fan\core\service\rest`.

### `fan\core\service\rest::_getCurl`

- Расположение: `_core/service/rest.php:193`
- Сигнатура: `function _getCurl(string $urlSuffix)`
- Описание: Выполняет логику `get curl` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\rest::_getResponse`

- Расположение: `_core/service/rest.php:217`
- Сигнатура: `function _getResponse(\fan\core\service\curl $curl, mixed $post = null)`
- Описание: Выполняет логику `get response` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения; логирует или сообщает об ошибках

## `_core/service/role.php`

### `fan\core\service\role::__construct`

- Расположение: `_core/service/role.php:90`
- Сигнатура: `function __construct()`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\role`.

### `fan\core\service\role::__destruct`

- Расположение: `_core/service/role.php:120`
- Сигнатура: `function __destruct()`
- Описание: Завершает работу объекта и выполняет отложенную очистку для этого метода класса `fan\core\service\role`.

### `fan\core\service\role::getRoles`

- Расположение: `_core/service/role.php:141`
- Сигнатура: `function getRoles()`
- Описание: Получает, читает или вычисляет данные `roles` в рамках этого метода класса `fan\core\service\role`.

### `fan\core\service\role::setSessionRoles`

- Расположение: `_core/service/role.php:155`
- Сигнатура: `function setSessionRoles(mixed $newRoles, int|float|string|null $expiredTime = null, bool $inUserSpace = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `session roles` в рамках этого метода класса `fan\core\service\role`.

### `fan\core\service\role::killSessionRoles`

- Расположение: `_core/service/role.php:188`
- Сигнатура: `function killSessionRoles(string|array|null $killRoles = null, int $destination = 3)`
- Описание: Выполняет логику `kill session roles` и возвращает вычисленный результат.

### `fan\core\service\role::getSessionRoles`

- Расположение: `_core/service/role.php:223`
- Сигнатура: `function getSessionRoles()`
- Описание: Получает, читает или вычисляет данные `session roles` в рамках этого метода класса `fan\core\service\role`.

### `fan\core\service\role::setFixQttRoles`

- Расположение: `_core/service/role.php:238`
- Сигнатура: `function setFixQttRoles(mixed $newRoles, int|float $qtt = 1, $rules = [], int|float|null $expiredTime = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `fix qtt roles` в рамках этого метода класса `fan\core\service\role`.

### `fan\core\service\role::getCurrentUser`

- Расположение: `_core/service/role.php:264`
- Сигнатура: `function getCurrentUser()`
- Описание: Получает, читает или вычисляет данные `current user` в рамках этого метода класса `fan\core\service\role`.

### `fan\core\service\role::setStaticRoles`

- Расположение: `_core/service/role.php:277`
- Сигнатура: `function setStaticRoles(mixed $newRoles, int|float|null $expiredTime = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `static roles` в рамках этого метода класса `fan\core\service\role`.

### `fan\core\service\role::getStaticRoles`

- Расположение: `_core/service/role.php:296`
- Сигнатура: `function getStaticRoles()`
- Описание: Получает, читает или вычисляет данные `static roles` в рамках этого метода класса `fan\core\service\role`.

### `fan\core\service\role::check`

- Расположение: `_core/service/role.php:308`
- Сигнатура: `function check(mixed $rolesRule)`
- Описание: Проверяет условие или валидирует данные `check` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: логирует или сообщает об ошибках

### `fan\core\service\role::isRole`

- Расположение: `_core/service/role.php:333`
- Сигнатура: `function isRole(string $role)`
- Описание: Проверяет условие или валидирует данные `role` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\role::onCurrentUserSet`

- Расположение: `_core/service/role.php:345`
- Сигнатура: `function onCurrentUserSet(\fan\core\service\user $user)`
- Описание: Выполняет workflow-логику `on current user set`.

### `fan\core\service\role::onUserRolesChange`

- Расположение: `_core/service/role.php:361`
- Сигнатура: `function onUserRolesChange(\fan\core\service\user $user)`
- Описание: Выполняет workflow-логику `on user roles change`.

### `fan\core\service\role::onLogoutUser`

- Расположение: `_core/service/role.php:374`
- Сигнатура: `function onLogoutUser()`
- Описание: Выполняет workflow-логику `on logout user`.

### `fan\core\service\role::onAppChange`

- Расположение: `_core/service/role.php:388`
- Сигнатура: `function onAppChange(string $appName)`
- Описание: Выполняет workflow-логику `on app change`.

### `fan\core\service\role::_convValToArray`

- Расположение: `_core/service/role.php:405`
- Сигнатура: `function _convValToArray(mixed $val, ?string $exceptionMessage = null)`
- Описание: Выполняет логику `conv val to array` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\role::_setCurrentRoles`

- Расположение: `_core/service/role.php:437`
- Сигнатура: `function _setCurrentRoles($force = false)`
- Описание: Выполняет логику `set current roles` и возвращает вычисленный результат.

### `fan\core\service\role::_setStaticRoles`

- Расположение: `_core/service/role.php:467`
- Сигнатура: `function _setStaticRoles()`
- Описание: Выполняет логику `set static roles` и возвращает вычисленный результат.

### `fan\core\service\role::_removeStaticExpired`

- Расположение: `_core/service/role.php:483`
- Сигнатура: `function _removeStaticExpired()`
- Описание: Выполняет логику `remove static expired` и возвращает вычисленный результат.

### `fan\core\service\role::_removeSessionExpired`

- Расположение: `_core/service/role.php:499`
- Сигнатура: `function _removeSessionExpired()`
- Описание: Выполняет логику `remove session expired` и возвращает вычисленный результат.

### `fan\core\service\role::_checkRoleDate`

- Расположение: `_core/service/role.php:526`
- Сигнатура: `function _checkRoleDate(&$roles)`
- Описание: Выполняет логику `check role date` и возвращает вычисленный результат.

### `fan\core\service\role::_getUserSpace`

- Расположение: `_core/service/role.php:544`
- Сигнатура: `function _getUserSpace()`
- Описание: Выполняет логику `get user space` и возвращает вычисленный результат.

### `fan\core\service\role::_defineExpiredDate`

- Расположение: `_core/service/role.php:556`
- Сигнатура: `function _defineExpiredDate(int|float|string $expiredTime)`
- Описание: Выполняет логику `define expired date` и возвращает вычисленный результат.

## `_core/service/session.php`

### `fan\core\service\session::__construct`

- Расположение: `_core/service/session.php:68`
- Сигнатура: `function __construct($nameSpace, $group)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\session`.
- Побочные эффекты: читает PHP superglobals

### `fan\core\service\session::instance`

- Расположение: `_core/service/session.php:118`
- Сигнатура: `function instance(mixed $nameSpace = null, mixed $group = 'custom')`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\session::get`

- Расположение: `_core/service/session.php:150`
- Сигнатура: `function get(array|string $key, mixed $defaultValue = null, bool $removeFromSes = false)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\service\session`.

### `fan\core\service\session::getByLink`

- Расположение: `_core/service/session.php:171`
- Сигнатура: `function &getByLink(mixed $key, mixed $defaultValue = null)`
- Описание: Получает, читает или вычисляет данные `by link` в рамках этого метода класса `fan\core\service\session`.

### `fan\core\service\session::getAll`

- Расположение: `_core/service/session.php:189`
- Сигнатура: `function getAll()`
- Описание: Получает, читает или вычисляет данные `all` в рамках этого метода класса `fan\core\service\session`.

### `fan\core\service\session::set`

- Расположение: `_core/service/session.php:202`
- Сигнатура: `function set(mixed $key, mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\service\session`.

### `fan\core\service\session::remove`

- Расположение: `_core/service/session.php:226`
- Сигнатура: `function remove(mixed $key)`
- Описание: Удаляет или сбрасывает состояние `элемент` для этого метода класса `fan\core\service\session`.

### `fan\core\service\session::removeAll`

- Расположение: `_core/service/session.php:254`
- Сигнатура: `function removeAll()`
- Описание: Удаляет или сбрасывает состояние `all` для этого метода класса `fan\core\service\session`.

### `fan\core\service\session::setBufferData`

- Расположение: `_core/service/session.php:272`
- Сигнатура: `function setBufferData(string $key, mixed $val)`
- Описание: Устанавливает, добавляет или сохраняет данные `buffer data` в рамках этого метода класса `fan\core\service\session`.

### `fan\core\service\session::getBufferData`

- Расположение: `_core/service/session.php:286`
- Сигнатура: `function getBufferData(string $key, mixed $default = null)`
- Описание: Получает, читает или вычисляет данные `buffer data` в рамках этого метода класса `fan\core\service\session`.

### `fan\core\service\session::getSessionId`

- Расположение: `_core/service/session.php:296`
- Сигнатура: `function getSessionId()`
- Описание: Получает, читает или вычисляет данные `session id` в рамках этого метода класса `fan\core\service\session`.

### `fan\core\service\session::isByCookies`

- Расположение: `_core/service/session.php:309`
- Сигнатура: `function isByCookies()`
- Описание: Проверяет условие или валидирует данные `by cookies` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\session::setSessionId`

- Расположение: `_core/service/session.php:321`
- Сигнатура: `function setSessionId(string $sid)`
- Описание: Устанавливает, добавляет или сохраняет данные `session id` в рамках этого метода класса `fan\core\service\session`.

### `fan\core\service\session::getSessionName`

- Расположение: `_core/service/session.php:338`
- Сигнатура: `function getSessionName()`
- Описание: Получает, читает или вычисляет данные `session name` в рамках этого метода класса `fan\core\service\session`.

### `fan\core\service\session::getGroup`

- Расположение: `_core/service/session.php:351`
- Сигнатура: `function getGroup()`
- Описание: Получает, читает или вычисляет данные `group` в рамках этого метода класса `fan\core\service\session`.

### `fan\core\service\session::getNameSpace`

- Расположение: `_core/service/session.php:361`
- Сигнатура: `function getNameSpace()`
- Описание: Получает, читает или вычисляет данные `name space` в рамках этого метода класса `fan\core\service\session`.

### `fan\core\service\session::isExpired`

- Расположение: `_core/service/session.php:371`
- Сигнатура: `function isExpired()`
- Описание: Проверяет условие или валидирует данные `expired` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\session::resetExpired`

- Расположение: `_core/service/session.php:383`
- Сигнатура: `function resetExpired($clearAll = true)`
- Описание: Удаляет или сбрасывает состояние `expired` для этого метода класса `fan\core\service\session`.

### `fan\core\service\session::destroy`

- Расположение: `_core/service/session.php:397`
- Сигнатура: `function destroy()`
- Описание: Удаляет или сбрасывает состояние `destroy` для этого метода класса `fan\core\service\session`.

### `fan\core\service\session::_prepareParameters`

- Расположение: `_core/service/session.php:416`
- Сигнатура: `function _prepareParameters()`
- Описание: Выполняет логику `prepare parameters` и возвращает вычисленный результат.

### `fan\core\service\session::_setCookie`

- Расположение: `_core/service/session.php:466`
- Сигнатура: `function _setCookie(string $var, string $val)`
- Описание: Выполняет логику `set cookie` и возвращает вычисленный результат.

### `fan\core\service\session::_getEngineData`

- Расположение: `_core/service/session.php:477`
- Сигнатура: `function &_getEngineData()`
- Описание: Выполняет логику `get engine data` и возвращает вычисленный результат.

### `fan\core\service\session::_checkSessionId`

- Расположение: `_core/service/session.php:490`
- Сигнатура: `function _checkSessionId(&$sid, $sesName = null)`
- Описание: Выполняет логику `check session id` и возвращает вычисленный результат.

### `fan\core\service\session::_compareSystem`

- Расположение: `_core/service/session.php:509`
- Сигнатура: `function _compareSystem()`
- Описание: Выполняет логику `compare system` и возвращает вычисленный результат.

### `fan\core\service\session::_checkSessionTimeout`

- Расположение: `_core/service/session.php:545`
- Сигнатура: `function _checkSessionTimeout()`
- Описание: Выполняет логику `check session timeout` и возвращает вычисленный результат.

### `fan\core\service\session::_killAll`

- Расположение: `_core/service/session.php:571`
- Сигнатура: `function _killAll()`
- Описание: Выполняет workflow-логику `kill all`.

## `_core/service/session/adodb.php`

### `fan\core\service\session\adodb::__construct`

- Расположение: `_core/service/session/adodb.php:32`
- Сигнатура: `function __construct($config)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\session\adodb`.

## `_core/service/session/inbuilt.php`

### `fan\core\service\session\inbuilt::__construct`

- Расположение: `_core/service/session/inbuilt.php:32`
- Сигнатура: `function __construct($sid)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\session\inbuilt`.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\service\session\inbuilt::setFacade`

- Расположение: `_core/service/session/inbuilt.php:47`
- Сигнатура: `function setFacade(\fan\core\service\session $facade)`
- Описание: Устанавливает, добавляет или сохраняет данные `facade` в рамках этого метода класса `fan\core\service\session\inbuilt`.

### `fan\core\service\session\inbuilt::getSessionId`

- Расположение: `_core/service/session/inbuilt.php:60`
- Сигнатура: `function getSessionId()`
- Описание: Получает, читает или вычисляет данные `session id` в рамках этого метода класса `fan\core\service\session\inbuilt`.

### `fan\core\service\session\inbuilt::setSessionId`

- Расположение: `_core/service/session/inbuilt.php:72`
- Сигнатура: `function setSessionId(string $sid)`
- Описание: Устанавливает, добавляет или сохраняет данные `session id` в рамках этого метода класса `fan\core\service\session\inbuilt`.

### `fan\core\service\session\inbuilt::getSessionName`

- Расположение: `_core/service/session/inbuilt.php:83`
- Сигнатура: `function getSessionName()`
- Описание: Получает, читает или вычисляет данные `session name` в рамках этого метода класса `fan\core\service\session\inbuilt`.

### `fan\core\service\session\inbuilt::getData`

- Расположение: `_core/service/session/inbuilt.php:96`
- Сигнатура: `function &getData(string $group, string $sesName)`
- Описание: Получает, читает или вычисляет данные `data` в рамках этого метода класса `fan\core\service\session\inbuilt`.
- Побочные эффекты: читает PHP superglobals

### `fan\core\service\session\inbuilt::getRoot`

- Расположение: `_core/service/session/inbuilt.php:111`
- Сигнатура: `function &getRoot()`
- Описание: Получает, читает или вычисляет данные `root` в рамках этого метода класса `fan\core\service\session\inbuilt`.
- Побочные эффекты: читает PHP superglobals

### `fan\core\service\session\inbuilt::destroy`

- Расположение: `_core/service/session/inbuilt.php:121`
- Сигнатура: `function destroy()`
- Описание: Удаляет или сбрасывает состояние `destroy` для этого метода класса `fan\core\service\session\inbuilt`.
- Побочные эффекты: меняет HTTP/session состояние; использует service locator/helpers фреймворка; логирует или сообщает об ошибках

## `_core/service/session/pear.php`

### `fan\core\service\session\pear::__construct`

- Расположение: `_core/service/session/pear.php:26`
- Сигнатура: `function __construct($config)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\session\pear`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\session\pear::getSessionId`

- Расположение: `_core/service/session/pear.php:51`
- Сигнатура: `function getSessionId()`
- Описание: Получает, читает или вычисляет данные `session id` в рамках этого метода класса `fan\core\service\session\pear`.

### `fan\core\service\session\pear::get`

- Расположение: `_core/service/session/pear.php:64`
- Сигнатура: `function get(string $key, ?string $defaultValue = NULL)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\service\session\pear`.

### `fan\core\service\session\pear::set`

- Расположение: `_core/service/session/pear.php:77`
- Сигнатура: `function set(string $key, string $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\service\session\pear`.

### `fan\core\service\session\pear::remove`

- Расположение: `_core/service/session/pear.php:89`
- Сигнатура: `function remove(string $key)`
- Описание: Удаляет или сбрасывает состояние `элемент` для этого метода класса `fan\core\service\session\pear`.

### `fan\core\service\session\pear::remove_all`

- Расположение: `_core/service/session/pear.php:99`
- Сигнатура: `function remove_all()`
- Описание: Удаляет или сбрасывает состояние `all` для этого метода класса `fan\core\service\session\pear`.

### `fan\core\service\session\pear::destroy`

- Расположение: `_core/service/session/pear.php:109`
- Сигнатура: `function destroy()`
- Описание: Удаляет или сбрасывает состояние `destroy` для этого метода класса `fan\core\service\session\pear`.

## `_core/service/soap.php`

### `fan\core\service\soap::__construct`

- Расположение: `_core/service/soap.php:45`
- Сигнатура: `function __construct($logEnabled)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\soap`.

### `fan\core\service\soap::instance`

- Расположение: `_core/service/soap.php:73`
- Сигнатура: `function instance(string $wsdlFile, ?array $param = null, $logEnabled = true)`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.

### `fan\core\service\soap::call`

- Расположение: `_core/service/soap.php:89`
- Сигнатура: `function call(mixed $funcName, mixed $arguments = [], mixed $options = null)`
- Описание: Выполняет логику `call` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках

### `fan\core\service\soap::setHeader`

- Расположение: `_core/service/soap.php:138`
- Сигнатура: `function setHeader(string $nameSpace, array $name, ?array $data = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `header` в рамках этого метода класса `fan\core\service\soap`.

### `fan\core\service\soap::setSoapVar`

- Расположение: `_core/service/soap.php:153`
- Сигнатура: `function setSoapVar(array $data, array $varParam = [], array $levels = [0])`
- Описание: Устанавливает, добавляет или сохраняет данные `soap var` в рамках этого метода класса `fan\core\service\soap`.

### `fan\core\service\soap::allowErrLogging`

- Расположение: `_core/service/soap.php:166`
- Сигнатура: `function allowErrLogging(bool $logEnabled)`
- Описание: Выполняет workflow-логику `allow err logging`.

### `fan\core\service\soap::isError`

- Расположение: `_core/service/soap.php:176`
- Сигнатура: `function isError()`
- Описание: Проверяет условие или валидирует данные `error` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\soap::getSoapFault`

- Расположение: `_core/service/soap.php:186`
- Сигнатура: `function getSoapFault()`
- Описание: Получает, читает или вычисляет данные `soap fault` в рамках этого метода класса `fan\core\service\soap`.

### `fan\core\service\soap::getDebugInfo`

- Расположение: `_core/service/soap.php:196`
- Сигнатура: `function getDebugInfo()`
- Описание: Получает, читает или вычисляет данные `debug info` в рамках этого метода класса `fan\core\service\soap`.

### `fan\core\service\soap::_initSoapObj`

- Расположение: `_core/service/soap.php:224`
- Сигнатура: `function _initSoapObj(string $wsdlFile, mixed $param = null)`
- Описание: Выполняет логику `init soap obj` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках; использует внешнюю коммуникацию

### `fan\core\service\soap::_setSoapVarRecursive`

- Расположение: `_core/service/soap.php:285`
- Сигнатура: `function _setSoapVarRecursive(mixed $data, array $varParam, array $levels, int $currentLevel)`
- Описание: Выполняет логику `set soap var recursive` и возвращает вычисленный результат.

### `fan\core\service\soap::_format4log`

- Расположение: `_core/service/soap.php:312`
- Сигнатура: `function _format4log(string $xml)`
- Описание: Выполняет логику `format4log` и возвращает вычисленный результат.

## `_core/service/tab.php`

### `fan\core\service\tab::__construct`

- Расположение: `_core/service/tab.php:172`
- Сигнатура: `function __construct($allowIni = true)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\tab`.

### `fan\core\service\tab::getContent`

- Расположение: `_core/service/tab.php:185`
- Сигнатура: `function getContent()`
- Описание: Получает, читает или вычисляет данные `content` в рамках этого метода класса `fan\core\service\tab`.

### `fan\core\service\tab::getMainBlock`

- Расположение: `_core/service/tab.php:197`
- Сигнатура: `function getMainBlock()`
- Описание: Получает, читает или вычисляет данные `main block` в рамках этого метода класса `fan\core\service\tab`.

### `fan\core\service\tab::getAppName`

- Расположение: `_core/service/tab.php:207`
- Сигнатура: `function getAppName()`
- Описание: Получает, читает или вычисляет данные `app name` в рамках этого метода класса `fan\core\service\tab`.

### `fan\core\service\tab::getRootBlock`

- Расположение: `_core/service/tab.php:217`
- Сигнатура: `function getRootBlock()`
- Описание: Получает, читает или вычисляет данные `root block` в рамках этого метода класса `fan\core\service\tab`.

### `fan\core\service\tab::getViewDefiner`

- Расположение: `_core/service/tab.php:227`
- Сигнатура: `function getViewDefiner()`
- Описание: Получает, читает или вычисляет данные `view definer` в рамках этого метода класса `fan\core\service\tab`.

### `fan\core\service\tab::getViewClass`

- Расположение: `_core/service/tab.php:239`
- Сигнатура: `function getViewClass()`
- Описание: Получает, читает или вычисляет данные `view class` в рамках этого метода класса `fan\core\service\tab`.

### `fan\core\service\tab::checkBlockStatus`

- Расположение: `_core/service/tab.php:251`
- Сигнатура: `function checkBlockStatus(\fan\core\block\base $block)`
- Описание: Проверяет условие или валидирует данные `block status` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\tab::getTabMeta`

- Расположение: `_core/service/tab.php:264`
- Сигнатура: `function getTabMeta(mixed $key = null, mixed $defautValue = null)`
- Описание: Получает, читает или вычисляет данные `tab meta` в рамках этого метода класса `fan\core\service\tab`.

### `fan\core\service\tab::getBlocksMetaByMain`

- Расположение: `_core/service/tab.php:280`
- Сигнатура: `function getBlocksMetaByMain($name)`
- Описание: Получает, читает или вычисляет данные `blocks meta by main` в рамках этого метода класса `fan\core\service\tab`.

### `fan\core\service\tab::getDefaultMeta`

- Расположение: `_core/service/tab.php:290`
- Сигнатура: `function getDefaultMeta()`
- Описание: Получает, читает или вычисляет данные `default meta` в рамках этого метода класса `fan\core\service\tab`.

### `fan\core\service\tab::checkTabRoles`

- Расположение: `_core/service/tab.php:303`
- Сигнатура: `function checkTabRoles(?string $dbOper = null, bool $allowTransfer = true)`
- Описание: Проверяет условие или валидирует данные `tab roles` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\tab::loadBlock`

- Расположение: `_core/service/tab.php:346`
- Сигнатура: `function loadBlock($path)`
- Описание: Получает, читает или вычисляет данные `block` в рамках этого метода класса `fan\core\service\tab`.

### `fan\core\service\tab::setCurrentBlock`

- Расположение: `_core/service/tab.php:365`
- Сигнатура: `function setCurrentBlock($block)`
- Описание: Устанавливает, добавляет или сохраняет данные `current block` в рамках этого метода класса `fan\core\service\tab`.

### `fan\core\service\tab::getCurrentBlock`

- Расположение: `_core/service/tab.php:376`
- Сигнатура: `function getCurrentBlock()`
- Описание: Получает, читает или вычисляет данные `current block` в рамках этого метода класса `fan\core\service\tab`.

### `fan\core\service\tab::setTabBlock`

- Расположение: `_core/service/tab.php:391`
- Сигнатура: `function setTabBlock($block, $name)`
- Описание: Устанавливает, добавляет или сохраняет данные `tab block` в рамках этого метода класса `fan\core\service\tab`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\tab::getTabBlock`

- Расположение: `_core/service/tab.php:413`
- Сигнатура: `function getTabBlock($blockName, $allowException = true)`
- Описание: Получает, читает или вычисляет данные `tab block` в рамках этого метода класса `fan\core\service\tab`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\tab::isSetBlock`

- Расположение: `_core/service/tab.php:431`
- Сигнатура: `function isSetBlock(string $blockName)`
- Описание: Проверяет условие или валидирует данные `set block` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\tab::getTabStage`

- Расположение: `_core/service/tab.php:441`
- Сигнатура: `function getTabStage()`
- Описание: Получает, читает или вычисляет данные `tab stage` в рамках этого метода класса `fan\core\service\tab`.

### `fan\core\service\tab::getDefaultInitNum`

- Расположение: `_core/service/tab.php:451`
- Сигнатура: `function getDefaultInitNum()`
- Описание: Получает, читает или вычисляет данные `default init num` в рамках этого метода класса `fan\core\service\tab`.

### `fan\core\service\tab::isDebugAllowed`

- Расположение: `_core/service/tab.php:461`
- Сигнатура: `function isDebugAllowed()`
- Описание: Проверяет условие или валидирует данные `debug allowed` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: читает PHP superglobals

### `fan\core\service\tab::getSubscriber`

- Расположение: `_core/service/tab.php:475`
- Сигнатура: `function getSubscriber()`
- Описание: Получает, читает или вычисляет данные `subscriber` в рамках этого метода класса `fan\core\service\tab`.

### `fan\core\service\tab::setFileTime`

- Расположение: `_core/service/tab.php:492`
- Сигнатура: `function setFileTime(int|float $fileTime, int|float $expireTime)`
- Описание: Устанавливает, добавляет или сохраняет данные `file time` в рамках этого метода класса `fan\core\service\tab`.

### `fan\core\service\tab::isCacheEnabled`

- Расположение: `_core/service/tab.php:506`
- Сигнатура: `function isCacheEnabled()`
- Описание: Проверяет условие или валидирует данные `cache enabled` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\tab::disableCache`

- Расположение: `_core/service/tab.php:515`
- Сигнатура: `function disableCache()`
- Описание: Выполняет логику `disable cache` и возвращает вычисленный результат.

### `fan\core\service\tab::_controlTabTransfer`

- Расположение: `_core/service/tab.php:530`
- Сигнатура: `function _controlTabTransfer()`
- Описание: Выполняет логику `control tab transfer` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние; может выбрасывать исключения

### `fan\core\service\tab::_checkAlias`

- Расположение: `_core/service/tab.php:582`
- Сигнатура: `function _checkAlias()`
- Описание: Выполняет логику `check alias` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\tab::_resetProperty`

- Расположение: `_core/service/tab.php:644`
- Сигнатура: `function _resetProperty()`
- Описание: Выполняет логику `reset property` и возвращает вычисленный результат.

### `fan\core\service\tab::_parseError`

- Расположение: `_core/service/tab.php:668`
- Сигнатура: `function _parseError()`
- Описание: Выполняет логику `parse error` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\tab::_makeContent`

- Расположение: `_core/service/tab.php:705`
- Сигнатура: `function _makeContent()`
- Описание: Выполняет логику `make content` и возвращает вычисленный результат.

### `fan\core\service\tab::_setViewClass`

- Расположение: `_core/service/tab.php:741`
- Сигнатура: `function _setViewClass(string $viewClass)`
- Описание: Выполняет логику `set view class` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\tab::_createRootBlock`

- Расположение: `_core/service/tab.php:759`
- Сигнатура: `function _createRootBlock()`
- Описание: Выполняет логику `create root block` и возвращает вычисленный результат.

### `fan\core\service\tab::_initBlocks`

- Расположение: `_core/service/tab.php:777`
- Сигнатура: `function _initBlocks(array $initOrderBlock)`
- Описание: Выполняет логику `init blocks` и возвращает вычисленный результат.

### `fan\core\service\tab::_runAfterInit`

- Расположение: `_core/service/tab.php:807`
- Сигнатура: `function _runAfterInit()`
- Описание: Выполняет логику `run after init` и возвращает вычисленный результат.

### `fan\core\service\tab::_getFinalContent`

- Расположение: `_core/service/tab.php:827`
- Сигнатура: `function _getFinalContent(\fan\core\block\base $rootBlock)`
- Описание: Выполняет логику `get final content` и возвращает вычисленный результат.

### `fan\core\service\tab::_defineRootBlock`

- Расположение: `_core/service/tab.php:853`
- Сигнатура: `function _defineRootBlock(array $rootMeta)`
- Описание: Выполняет логику `define root block` и возвращает вычисленный результат.

### `fan\core\service\tab::_getRootMeta`

- Расположение: `_core/service/tab.php:897`
- Сигнатура: `function _getRootMeta()`
- Описание: Выполняет логику `get root meta` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\tab::_getInitBlocks`

- Расположение: `_core/service/tab.php:920`
- Сигнатура: `function _getInitBlocks()`
- Описание: Выполняет логику `get init blocks` и возвращает вычисленный результат.

### `fan\core\service\tab::_parseError403`

- Расположение: `_core/service/tab.php:931`
- Сигнатура: `function _parseError403()`
- Описание: Выполняет логику `parse error403` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\service\tab::_parseError404`

- Расположение: `_core/service/tab.php:952`
- Сигнатура: `function _parseError404($forse)`
- Описание: Выполняет логику `parse error404` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\core\service\tab::_parseError500`

- Расположение: `_core/service/tab.php:975`
- Сигнатура: `function _parseError500($errorLog = null)`
- Описание: Выполняет логику `parse error500` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние; логирует или сообщает об ошибках

### `fan\core\service\tab::_getPathWithExt`

- Расположение: `_core/service/tab.php:999`
- Сигнатура: `function _getPathWithExt($urn, $defaultExt = 'html')`
- Описание: Выполняет логику `get path with ext` и возвращает вычисленный результат.

### `fan\core\service\tab::_setMainBlock`

- Расположение: `_core/service/tab.php:1016`
- Сигнатура: `function _setMainBlock()`
- Описание: Выполняет логику `set main block` и возвращает вычисленный результат.

### `fan\core\service\tab::_setTabMeta`

- Расположение: `_core/service/tab.php:1033`
- Сигнатура: `function _setTabMeta()`
- Описание: Выполняет логику `set tab meta` и возвращает вычисленный результат.

### `fan\core\service\tab::_setBlocksMetaByMain`

- Расположение: `_core/service/tab.php:1044`
- Сигнатура: `function _setBlocksMetaByMain()`
- Описание: Выполняет логику `set blocks meta by main` и возвращает вычисленный результат.

### `fan\core\service\tab::_setDefaultMeta`

- Расположение: `_core/service/tab.php:1055`
- Сигнатура: `function _setDefaultMeta()`
- Описание: Выполняет логику `set default meta` и возвращает вычисленный результат.

### `fan\core\service\tab::_getDebugMode`

- Расположение: `_core/service/tab.php:1066`
- Сигнатура: `function _getDebugMode()`
- Описание: Выполняет логику `get debug mode` и возвращает вычисленный результат.

### `fan\core\service\tab::_fixPerformance`

- Расположение: `_core/service/tab.php:1097`
- Сигнатура: `function _fixPerformance($startTime, $initTime)`
- Описание: Выполняет workflow-логику `fix performance`.

## `_core/service/tab/delegate.php`

### `fan\core\service\tab\delegate::setFacade`

- Расположение: `_core/service/tab/delegate.php:38`
- Сигнатура: `function setFacade(\fan\core\base\service $facade)`
- Описание: Устанавливает, добавляет или сохраняет данные `facade` в рамках этого метода класса `fan\core\service\tab\delegate`.

### `fan\core\service\tab\delegate::getConfig`

- Расположение: `_core/service/tab/delegate.php:53`
- Сигнатура: `function getConfig(mixed $key = null, mixed $default = null)`
- Описание: Получает, читает или вычисляет данные `config` в рамках этого метода класса `fan\core\service\tab\delegate`.

## `_core/service/tab/delegate/urlMaker.php`

### `fan\core\service\tab\delegate\urlMaker::__construct`

- Расположение: `_core/service/tab/delegate/urlMaker.php:29`
- Сигнатура: `function __construct()`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\tab\delegate\urlMaker`.

### `fan\core\service\tab\delegate\urlMaker::isUseHttps`

- Расположение: `_core/service/tab/delegate/urlMaker.php:40`
- Сигнатура: `function isUseHttps()`
- Описание: Проверяет условие или валидирует данные `use https` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\tab\delegate\urlMaker::getCurrentURI`

- Расположение: `_core/service/tab/delegate/urlMaker.php:56`
- Сигнатура: `function getCurrentURI(mixed $corLanguage = true, bool $addExt = true, bool $addQueryStr = true, mixed $addSid = null, mixed $sprtr = null)`
- Описание: Получает, читает или вычисляет данные `current u r i` в рамках этого метода класса `fan\core\service\tab\delegate\urlMaker`.
- Побочные эффекты: выполняет database операции

### `fan\core\service\tab\delegate\urlMaker::getModifiedCurrentURI`

- Расположение: `_core/service/tab/delegate/urlMaker.php:145`
- Сигнатура: `function getModifiedCurrentURI(array $modifier, $addExt = true, $addSid = null, $protocol = null)`
- Описание: Получает, читает или вычисляет данные `modified current u r i` в рамках этого метода класса `fan\core\service\tab\delegate\urlMaker`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\tab\delegate\urlMaker::getURI`

- Расположение: `_core/service/tab/delegate/urlMaker.php:225`
- Сигнатура: `function getURI(string $urn = '', string $type = 'link', mixed $addSid = null, mixed $protocol = null)`
- Описание: Получает, читает или вычисляет данные `u r i` в рамках этого метода класса `fan\core\service\tab\delegate\urlMaker`.
- Побочные эффекты: читает PHP superglobals

### `fan\core\service\tab\delegate\urlMaker::addQuery`

- Расположение: `_core/service/tab/delegate/urlMaker.php:271`
- Сигнатура: `function addQuery(string $urn, $key, $val, $sprtr = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `query` в рамках этого метода класса `fan\core\service\tab\delegate\urlMaker`.

### `fan\core\service\tab\delegate\urlMaker::getDefaultExtension`

- Расположение: `_core/service/tab/delegate/urlMaker.php:291`
- Сигнатура: `function getDefaultExtension()`
- Описание: Получает, читает или вычисляет данные `default extension` в рамках этого метода класса `fan\core\service\tab\delegate\urlMaker`.

### `fan\core\service\tab\delegate\urlMaker::reduceExt`

- Расположение: `_core/service/tab/delegate/urlMaker.php:305`
- Сигнатура: `function reduceExt(string $url, $minLen = 2, $maxLen = 4)`
- Описание: Выполняет логику `reduce ext` и возвращает вычисленный результат.

### `fan\core\service\tab\delegate\urlMaker::_addSlashes`

- Расположение: `_core/service/tab/delegate/urlMaker.php:323`
- Сигнатура: `function _addSlashes(string $val)`
- Описание: Выполняет логику `add slashes` и возвращает вычисленный результат.

## `_core/service/tab/engine.php`

### `fan\core\service\tab\engine::setFacade`

- Расположение: `_core/service/tab/engine.php:38`
- Сигнатура: `function setFacade(\fan\core\base\service $facade)`
- Описание: Устанавливает, добавляет или сохраняет данные `facade` в рамках этого метода класса `fan\core\service\tab\engine`.

### `fan\core\service\tab\engine::_makeException`

- Расположение: `_core/service/tab/engine.php:56`
- Сигнатура: `function _makeException($message)`
- Описание: Выполняет workflow-логику `make exception`.
- Побочные эффекты: может выбрасывать исключения

## `_core/service/tab/subscriber.php`

### `fan\core\service\tab\subscriber::subscribeForEvent`

- Расположение: `_core/service/tab/subscriber.php:41`
- Сигнатура: `function subscribeForEvent(\fan\core\block\base $listener, string $eventName, string $listenerMethod = 'eventHandler')`
- Описание: Выполняет логику `subscribe for event` и возвращает вычисленный результат.

### `fan\core\service\tab\subscriber::subscribeByName`

- Расположение: `_core/service/tab/subscriber.php:58`
- Сигнатура: `function subscribeByName(\fan\core\block\base $listener, string $broadcasterName, string $eventName, string $listenerMethod = 'eventHandler')`
- Описание: Выполняет логику `subscribe by name` и возвращает вычисленный результат.

### `fan\core\service\tab\subscriber::subscribeByClass`

- Расположение: `_core/service/tab/subscriber.php:75`
- Сигнатура: `function subscribeByClass(\fan\core\block\base $listener, string $className, string $eventName, string $listenerMethod = 'eventHandler')`
- Описание: Выполняет логику `subscribe by class` и возвращает вычисленный результат.

### `fan\core\service\tab\subscriber::unSubscribeByName`

- Расположение: `_core/service/tab/subscriber.php:90`
- Сигнатура: `function unSubscribeByName(\fan\core\block\base $listener, string $broadcasterName, string $eventName, string $listenerMethod = 'eventHandler')`
- Описание: Выполняет логику `un subscribe by name` и возвращает вычисленный результат.

### `fan\core\service\tab\subscriber::unSubscribeByClass`

- Расположение: `_core/service/tab/subscriber.php:105`
- Сигнатура: `function unSubscribeByClass(\fan\core\block\base $listener, string $className, string $eventName, string $listenerMethod = 'eventHandler')`
- Описание: Выполняет логику `un subscribe by class` и возвращает вычисленный результат.

### `fan\core\service\tab\subscriber::broadcastEvent`

- Расположение: `_core/service/tab/subscriber.php:119`
- Сигнатура: `function broadcastEvent(\fan\core\block\base $broadcaster, string $eventName, array $data = [])`
- Описание: Выполняет логику `broadcast event` и возвращает вычисленный результат.

### `fan\core\service\tab\subscriber::_addSubscriber`

- Расположение: `_core/service/tab/subscriber.php:151`
- Сигнатура: `function _addSubscriber(\fan\core\block\base $listener, string $listenerMethod, string $type, string $key, string $eventName)`
- Описание: Выполняет логику `add subscriber` и возвращает вычисленный результат.

### `fan\core\service\tab\subscriber::_removeSubscriber`

- Расположение: `_core/service/tab/subscriber.php:178`
- Сигнатура: `function _removeSubscriber(\fan\core\block\base $listener, string $listenerMethod, string $type, string $key, string $eventName)`
- Описание: Выполняет логику `remove subscriber` и возвращает вычисленный результат.

## `_core/service/template.php`

### `fan\core\service\template::get`

- Расположение: `_core/service/template.php:86`
- Сигнатура: `function get(string $templatePath, ?string $parent = null, ?\fan\core\block\base $block = null)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\service\template`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\template::getParseData`

- Расположение: `_core/service/template.php:107`
- Сигнатура: `function getParseData()`
- Описание: Получает, читает или вычисляет данные `parse data` в рамках этого метода класса `fan\core\service\template`.

### `fan\core\service\template::disableStrip`

- Расположение: `_core/service/template.php:119`
- Сигнатура: `function disableStrip(bool $allowStrip = false)`
- Описание: Выполняет workflow-логику `disable strip`.

### `fan\core\service\template::parse_literal`

- Расположение: `_core/service/template.php:133`
- Сигнатура: `function parse_literal(string $data)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `literal` для этого метода класса `fan\core\service\template`.

### `fan\core\service\template::parse_plain`

- Расположение: `_core/service/template.php:145`
- Сигнатура: `function parse_plain(string $data)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `plain` для этого метода класса `fan\core\service\template`.

### `fan\core\service\template::_getClassAttributes`

- Расположение: `_core/service/template.php:177`
- Сигнатура: `function _getClassAttributes(string $templatePath, mixed $block)`
- Описание: Выполняет логику `get class attributes` и возвращает вычисленный результат.

### `fan\core\service\template::_makeClass`

- Расположение: `_core/service/template.php:204`
- Сигнатура: `function _makeClass(string $templatePath, string $compilePath, string $nameSpace, string $className, string $type)`
- Описание: Выполняет workflow-логику `make class`.
- Побочные эффекты: работает с файловой системой

### `fan\core\service\template::_addSlashes`

- Расположение: `_core/service/template.php:289`
- Сигнатура: `function _addSlashes(string $data)`
- Описание: Выполняет логику `add slashes` и возвращает вычисленный результат.

## `_core/service/template/parser/base.php`

### `fan\core\service\template\parser\base::setFacade`

- Расположение: `_core/service/template/parser/base.php:45`
- Сигнатура: `function setFacade(\fan\core\service\template $facade)`
- Описание: Устанавливает, добавляет или сохраняет данные `facade` в рамках этого метода класса `fan\core\service\template\parser\base`.

### `fan\core\service\template\parser\base::__call`

- Расположение: `_core/service/template/parser/base.php:58`
- Сигнатура: `function __call($method, $args)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\template\parser\base`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\template\parser\base::getTagList`

- Расположение: `_core/service/template/parser/base.php:76`
- Сигнатура: `function getTagList()`
- Описание: Получает, читает или вычисляет данные `tag list` в рамках этого метода класса `fan\core\service\template\parser\base`.

### `fan\core\service\template\parser\base::setAutoTag`

- Расположение: `_core/service/template/parser/base.php:89`
- Сигнатура: `function setAutoTag($tagName, $data)`
- Описание: Устанавливает, добавляет или сохраняет данные `auto tag` в рамках этого метода класса `fan\core\service\template\parser\base`.

### `fan\core\service\template\parser\base::getSimpleParam`

- Расположение: `_core/service/template/parser/base.php:102`
- Сигнатура: `function getSimpleParam(string $data)`
- Описание: Получает, читает или вычисляет данные `simple param` в рамках этого метода класса `fan\core\service\template\parser\base`.

### `fan\core\service\template\parser\base::getStandardParam`

- Расположение: `_core/service/template/parser/base.php:127`
- Сигнатура: `function getStandardParam(string $data, array $require = [], array $strip = [])`
- Описание: Получает, читает или вычисляет данные `standard param` в рамках этого метода класса `fan\core\service\template\parser\base`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\template\parser\base::getDynamicArray`

- Расположение: `_core/service/template/parser/base.php:166`
- Сигнатура: `function getDynamicArray(string $data, array $require = [])`
- Описание: Получает, читает или вычисляет данные `dynamic array` в рамках этого метода класса `fan\core\service\template\parser\base`.

## `_core/service/template/parser/form.php`

### `fan\core\service\template\parser\form::parse_form_key_field`

- Расположение: `_core/service/template/parser/form.php:31`
- Сигнатура: `function parse_form_key_field()`
- Описание: Создает, разбирает, форматирует или конвертирует данные `form key field` для этого метода класса `fan\core\service\template\parser\form`.

### `fan\core\service\template\parser\form::parse_form_sid`

- Расположение: `_core/service/template/parser/form.php:41`
- Сигнатура: `function parse_form_sid()`
- Описание: Создает, разбирает, форматирует или конвертирует данные `form sid` для этого метода класса `fan\core\service\template\parser\form`.

## `_core/service/template/parser/image.php`

### `fan\core\service\template\parser\image::parse_some_special`

- Расположение: `_core/service/template/parser/image.php:31`
- Сигнатура: `function parse_some_special()`
- Описание: Создает, разбирает, форматирует или конвертирует данные `some special` для этого метода класса `fan\core\service\template\parser\image`.

## `_core/service/template/parser/main.php`

### `fan\core\service\template\parser\main::parse_assign`

- Расположение: `_core/service/template/parser/main.php:33`
- Сигнатура: `function parse_assign(string $data)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `assign` для этого метода класса `fan\core\service\template\parser\main`.

### `fan\core\service\template\parser\main::parse_if`

- Расположение: `_core/service/template/parser/main.php:50`
- Сигнатура: `function parse_if(string $data)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `if` для этого метода класса `fan\core\service\template\parser\main`.

### `fan\core\service\template\parser\main::parse_elseif`

- Расположение: `_core/service/template/parser/main.php:62`
- Сигнатура: `function parse_elseif(string $data)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `elseif` для этого метода класса `fan\core\service\template\parser\main`.

### `fan\core\service\template\parser\main::parse_foreach`

- Расположение: `_core/service/template/parser/main.php:74`
- Сигнатура: `function parse_foreach(string $data)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `foreach` для этого метода класса `fan\core\service\template\parser\main`.

### `fan\core\service\template\parser\main::parse_for`

- Расположение: `_core/service/template/parser/main.php:92`
- Сигнатура: `function parse_for(string $data)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `for` для этого метода класса `fan\core\service\template\parser\main`.

### `fan\core\service\template\parser\main::parse_msg`

- Расположение: `_core/service/template/parser/main.php:112`
- Сигнатура: `function parse_msg(string $data)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `msg` для этого метода класса `fan\core\service\template\parser\main`.

### `fan\core\service\template\parser\main::parse_uri`

- Расположение: `_core/service/template/parser/main.php:124`
- Сигнатура: `function parse_uri(string $data)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `uri` для этого метода класса `fan\core\service\template\parser\main`.

### `fan\core\service\template\parser\main::parse_getURI`

- Расположение: `_core/service/template/parser/main.php:136`
- Сигнатура: `function parse_getURI(string $data)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `get u r i` для этого метода класса `fan\core\service\template\parser\main`.

### `fan\core\service\template\parser\main::parse_get_url`

- Расположение: `_core/service/template/parser/main.php:148`
- Сигнатура: `function parse_get_url(string $data)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `get url` для этого метода класса `fan\core\service\template\parser\main`.

## `_core/service/template/type/base.php`

### `fan\core\service\template\type\base::__construct`

- Расположение: `_core/service/template/type/base.php:55`
- Сигнатура: `function __construct($block = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\template\type\base`.

### `fan\core\service\template\type\base::__set`

- Расположение: `_core/service/template/type/base.php:78`
- Сигнатура: `function __set($key, $value)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\template\type\base`.

### `fan\core\service\template\type\base::__get`

- Расположение: `_core/service/template/type/base.php:90`
- Сигнатура: `function __get($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\template\type\base`.

### `fan\core\service\template\type\base::offsetSet`

- Расположение: `_core/service/template/type/base.php:104`
- Сигнатура: `function offsetSet($key, mixed $value)`
- Описание: Выполняет workflow-логику `offset set`.

### `fan\core\service\template\type\base::offsetExists`

- Расположение: `_core/service/template/type/base.php:124`
- Сигнатура: `function offsetExists($key)`
- Описание: Выполняет логику `offset exists` и возвращает вычисленный результат.

### `fan\core\service\template\type\base::offsetUnset`

- Расположение: `_core/service/template/type/base.php:136`
- Сигнатура: `function offsetUnset($key)`
- Описание: Выполняет workflow-логику `offset unset`.

### `fan\core\service\template\type\base::offsetGet`

- Расположение: `_core/service/template/type/base.php:148`
- Сигнатура: `function offsetGet($key)`
- Описание: Выполняет логику `offset get` и возвращает вычисленный результат.

### `fan\core\service\template\type\base::getEngineList`

- Расположение: `_core/service/template/type/base.php:163`
- Сигнатура: `function getEngineList()`
- Описание: Получает, читает или вычисляет данные `engine list` в рамках этого метода класса `fan\core\service\template\type\base`.

### `fan\core\service\template\type\base::getAutoParseTag`

- Расположение: `_core/service/template/type/base.php:173`
- Сигнатура: `function getAutoParseTag()`
- Описание: Получает, читает или вычисляет данные `auto parse tag` в рамках этого метода класса `fan\core\service\template\type\base`.

### `fan\core\service\template\type\base::assign`

- Расположение: `_core/service/template/type/base.php:186`
- Сигнатура: `function assign(string $key, mixed $value)`
- Описание: Выполняет workflow-логику `assign`.

### `fan\core\service\template\type\base::assignByRef`

- Расположение: `_core/service/template/type/base.php:200`
- Сигнатура: `function assignByRef(string $key, &$value)`
- Описание: Выполняет workflow-логику `assign by ref`.

### `fan\core\service\template\type\base::clearAssign`

- Расположение: `_core/service/template/type/base.php:214`
- Сигнатура: `function clearAssign(string $key)`
- Описание: Удаляет или сбрасывает состояние `assign` для этого метода класса `fan\core\service\template\type\base`.

### `fan\core\service\template\type\base::getVars`

- Расположение: `_core/service/template/type/base.php:227`
- Сигнатура: `function getVars(mixed $key = null)`
- Описание: Получает, читает или вычисляет данные `vars` в рамках этого метода класса `fan\core\service\template\type\base`.

### `fan\core\service\template\type\base::fetch`

- Расположение: `_core/service/template/type/base.php:237`
- Сигнатура: `function fetch()`
- Описание: Получает, читает или вычисляет данные `fetch` в рамках этого метода класса `fan\core\service\template\type\base`.

### `fan\core\service\template\type\base::parseHtml`

- Расположение: `_core/service/template/type/base.php:249`
- Сигнатура: `function parseHtml();`
- Описание: Создает, разбирает, форматирует или конвертирует данные `html` для этого метода класса `fan\core\service\template\type\base`.

### `fan\core\service\template\type\base::linkForAssign`

- Расположение: `_core/service/template/type/base.php:258`
- Сигнатура: `function &linkForAssign(string $key)`
- Описание: Выполняет логику `link for assign` и возвращает вычисленный результат.

### `fan\core\service\template\type\base::getIteration`

- Расположение: `_core/service/template/type/base.php:271`
- Сигнатура: `function getIteration(string $key)`
- Описание: Получает, читает или вычисляет данные `iteration` в рамках этого метода класса `fan\core\service\template\type\base`.

### `fan\core\service\template\type\base::getTotal`

- Расположение: `_core/service/template/type/base.php:283`
- Сигнатура: `function getTotal(string $key)`
- Описание: Получает, читает или вычисляет данные `total` в рамках этого метода класса `fan\core\service\template\type\base`.

### `fan\core\service\template\type\base::isFirst`

- Расположение: `_core/service/template/type/base.php:295`
- Сигнатура: `function isFirst(string $key)`
- Описание: Проверяет условие или валидирует данные `first` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\template\type\base::isLast`

- Расположение: `_core/service/template/type/base.php:307`
- Сигнатура: `function isLast(string $key)`
- Описание: Проверяет условие или валидирует данные `last` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\template\type\base::isEven`

- Расположение: `_core/service/template/type/base.php:320`
- Сигнатура: `function isEven(string $key, $isBoolean = false)`
- Описание: Проверяет условие или валидирует данные `even` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\template\type\base::makeTagAttr`

- Расположение: `_core/service/template/type/base.php:335`
- Сигнатура: `function makeTagAttr($attr, mixed $data, mixed $key = null)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `tag attr` для этого метода класса `fan\core\service\template\type\base`.

### `fan\core\service\template\type\base::setObjectData`

- Расположение: `_core/service/template/type/base.php:356`
- Сигнатура: `function setObjectData($type, $key, &$data = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `object data` в рамках этого метода класса `fan\core\service\template\type\base`.

### `fan\core\service\template\type\base::setIteration`

- Расположение: `_core/service/template/type/base.php:369`
- Сигнатура: `function setIteration($key)`
- Описание: Устанавливает, добавляет или сохраняет данные `iteration` в рамках этого метода класса `fan\core\service\template\type\base`.

### `fan\core\service\template\type\base::checkVars`

- Расположение: `_core/service/template/type/base.php:381`
- Сигнатура: `function checkVars($key)`
- Описание: Проверяет условие или валидирует данные `vars` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: может выбрасывать исключения

## `_core/service/template/type/form.php`

### `fan\core\service\template\type\form::__construct`

- Расположение: `_core/service/template/type/form.php:72`
- Сигнатура: `function __construct(\fan\core\block\form\usual $block)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\template\type\form`.

### `fan\core\service\template\type\form::getEngineList`

- Расположение: `_core/service/template/type/form.php:100`
- Сигнатура: `function getEngineList()`
- Описание: Получает, читает или вычисляет данные `engine list` в рамках этого метода класса `fan\core\service\template\type\form`.

### `fan\core\service\template\type\form::getAutoParseTag`

- Расположение: `_core/service/template/type/form.php:110`
- Сигнатура: `function getAutoParseTag()`
- Описание: Получает, читает или вычисляет данные `auto parse tag` в рамках этого метода класса `fan\core\service\template\type\form`.

### `fan\core\service\template\type\form::getKeyField`

- Расположение: `_core/service/template/type/form.php:127`
- Сигнатура: `function getKeyField()`
- Описание: Получает, читает или вычисляет данные `key field` в рамках этого метода класса `fan\core\service\template\type\form`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\template\type\form::getSidField`

- Расположение: `_core/service/template/type/form.php:144`
- Сигнатура: `function getSidField()`
- Описание: Получает, читает или вычисляет данные `sid field` в рамках этого метода класса `fan\core\service\template\type\form`.

### `fan\core\service\template\type\form::getFormRow`

- Расположение: `_core/service/template/type/form.php:157`
- Сигнатура: `function getFormRow(array $data)`
- Описание: Получает, читает или вычисляет данные `form row` в рамках этого метода класса `fan\core\service\template\type\form`.

### `fan\core\service\template\type\form::getLabel`

- Расположение: `_core/service/template/type/form.php:187`
- Сигнатура: `function getLabel(array $data)`
- Описание: Получает, читает или вычисляет данные `label` в рамках этого метода класса `fan\core\service\template\type\form`.

### `fan\core\service\template\type\form::getErrorMsg`

- Расположение: `_core/service/template/type/form.php:220`
- Сигнатура: `function getErrorMsg(array $data)`
- Описание: Получает, читает или вычисляет данные `error msg` в рамках этого метода класса `fan\core\service\template\type\form`.

### `fan\core\service\template\type\form::getNote`

- Расположение: `_core/service/template/type/form.php:258`
- Сигнатура: `function getNote(array $data)`
- Описание: Получает, читает или вычисляет данные `note` в рамках этого метода класса `fan\core\service\template\type\form`.

### `fan\core\service\template\type\form::getButton`

- Расположение: `_core/service/template/type/form.php:291`
- Сигнатура: `function getButton(array $data)`
- Описание: Получает, читает или вычисляет данные `button` в рамках этого метода класса `fan\core\service\template\type\form`.

### `fan\core\service\template\type\form::getField`

- Расположение: `_core/service/template/type/form.php:320`
- Сигнатура: `function getField(array $data)`
- Описание: Получает, читает или вычисляет данные `field` в рамках этого метода класса `fan\core\service\template\type\form`.

### `fan\core\service\template\type\form::getInput`

- Расположение: `_core/service/template/type/form.php:355`
- Сигнатура: `function getInput(array $data)`
- Описание: Получает, читает или вычисляет данные `input` в рамках этого метода класса `fan\core\service\template\type\form`.

### `fan\core\service\template\type\form::getChecking`

- Расположение: `_core/service/template/type/form.php:388`
- Сигнатура: `function getChecking(array $data)`
- Описание: Получает, читает или вычисляет данные `checking` в рамках этого метода класса `fan\core\service\template\type\form`.

### `fan\core\service\template\type\form::getSelect`

- Расположение: `_core/service/template/type/form.php:414`
- Сигнатура: `function getSelect(array $data)`
- Описание: Получает, читает или вычисляет данные `select` в рамках этого метода класса `fan\core\service\template\type\form`.

### `fan\core\service\template\type\form::getSeparatedSelect`

- Расположение: `_core/service/template/type/form.php:458`
- Сигнатура: `function getSeparatedSelect(array $data)`
- Описание: Получает, читает или вычисляет данные `separated select` в рамках этого метода класса `fan\core\service\template\type\form`.

### `fan\core\service\template\type\form::_getMsgByLng`

- Расположение: `_core/service/template/type/form.php:512`
- Сигнатура: `function _getMsgByLng(string $text, array $data)`
- Описание: Выполняет логику `get msg by lng` и возвращает вычисленный результат.

### `fan\core\service\template\type\form::_parseSubPattern`

- Расположение: `_core/service/template/type/form.php:528`
- Сигнатура: `function _parseSubPattern(string $subPattern, mixed $value, mixed $fdt, array $data, string $fieldType)`
- Описание: Выполняет логику `parse sub pattern` и возвращает вычисленный результат.

### `fan\core\service\template\type\form::_setAttributes`

- Расположение: `_core/service/template/type/form.php:565`
- Сигнатура: `function _setAttributes(string $pattern, array $data, bool $isTabInd = true)`
- Описание: Выполняет логику `set attributes` и возвращает вычисленный результат.

### `fan\core\service\template\type\form::_setTabIndex`

- Расположение: `_core/service/template/type/form.php:590`
- Сигнатура: `function _setTabIndex(string $pattern, array $data)`
- Описание: Выполняет логику `set tab index` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\template\type\form::_getIdByName`

- Расположение: `_core/service/template/type/form.php:611`
- Сигнатура: `function _getIdByName(string $name)`
- Описание: Выполняет логику `get id by name` и возвращает вычисленный результат.

### `fan\core\service\template\type\form::_getFormMeta`

- Расположение: `_core/service/template/type/form.php:624`
- Сигнатура: `function _getFormMeta(mixed $key, mixed $default = null)`
- Описание: Выполняет логику `get form meta` и возвращает вычисленный результат.

### `fan\core\service\template\type\form::_getFieldMeta`

- Расположение: `_core/service/template/type/form.php:642`
- Сигнатура: `function _getFieldMeta(array $data, mixed $key = null, mixed $default = null)`
- Описание: Выполняет логику `get field meta` и возвращает вычисленный результат.

### `fan\core\service\template\type\form::_getFieldValue`

- Расположение: `_core/service/template/type/form.php:668`
- Сигнатура: `function _getFieldValue(array $data, mixed $default = null)`
- Описание: Выполняет логику `get field value` и возвращает вычисленный результат.

### `fan\core\service\template\type\form::_getFieldData`

- Расположение: `_core/service/template/type/form.php:682`
- Сигнатура: `function _getFieldData(array $data, mixed $default = null)`
- Описание: Выполняет логику `get field data` и возвращает вычисленный результат.

### `fan\core\service\template\type\form::_parseCombiName`

- Расположение: `_core/service/template/type/form.php:706`
- Сигнатура: `function _parseCombiName($data)`
- Описание: Выполняет логику `parse combi name` и возвращает вычисленный результат.

## `_core/service/template/type/image.php`

### `fan\core\service\template\type\image::getEngineList`

- Расположение: `_core/service/template/type/image.php:31`
- Сигнатура: `function getEngineList()`
- Описание: Получает, читает или вычисляет данные `engine list` в рамках этого метода класса `fan\core\service\template\type\image`.

### `fan\core\service\template\type\image::getAutoParseTag`

- Расположение: `_core/service/template/type/image.php:41`
- Сигнатура: `function getAutoParseTag()`
- Описание: Получает, читает или вычисляет данные `auto parse tag` в рамках этого метода класса `fan\core\service\template\type\image`.

### `fan\core\service\template\type\image::setBaseParam`

- Расположение: `_core/service/template/type/image.php:57`
- Сигнатура: `function setBaseParam(array $param)`
- Описание: Устанавливает, добавляет или сохраняет данные `base param` в рамках этого метода класса `fan\core\service\template\type\image`.

### `fan\core\service\template\type\image::makeImgTag`

- Расположение: `_core/service/template/type/image.php:71`
- Сигнатура: `function makeImgTag(array $data = [])`
- Описание: Создает, разбирает, форматирует или конвертирует данные `img tag` для этого метода класса `fan\core\service\template\type\image`.

### `fan\core\service\template\type\image::makeTopSign`

- Расположение: `_core/service/template/type/image.php:92`
- Сигнатура: `function makeTopSign(array $data = [])`
- Описание: Создает, разбирает, форматирует или конвертирует данные `top sign` для этого метода класса `fan\core\service\template\type\image`.

### `fan\core\service\template\type\image::makeBotSign`

- Расположение: `_core/service/template/type/image.php:104`
- Сигнатура: `function makeBotSign(array $data = [])`
- Описание: Создает, разбирает, форматирует или конвертирует данные `bot sign` для этого метода класса `fan\core\service\template\type\image`.

### `fan\core\service\template\type\image::makeSignature`

- Расположение: `_core/service/template/type/image.php:116`
- Сигнатура: `function makeSignature(array $data)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `signature` для этого метода класса `fan\core\service\template\type\image`.

## `_core/service/timer.php`

### `fan\core\service\timer::__construct`

- Расположение: `_core/service/timer.php:47`
- Сигнатура: `function __construct()`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\timer`.

### `fan\core\service\timer::chargeProgram`

- Расположение: `_core/service/timer.php:68`
- Сигнатура: `function chargeProgram(mixed $startTime, string $className, string $methodName, array $param, int|float $period = 0, int|float|null $overcall = null, bool $isShell = true)`
- Описание: Выполняет логику `charge program` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\timer::modifyChargedProgram`

- Расположение: `_core/service/timer.php:120`
- Сигнатура: `function modifyChargedProgram(string $className, string $methodName, ?array $param = null, int|float|null $period = null, int|float|null $overcall = null)`
- Описание: Выполняет логику `modify charged program` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\timer::modifyChargedProgramByPID`

- Расположение: `_core/service/timer.php:144`
- Сигнатура: `function modifyChargedProgramByPID(string $pid, ?array $param = null, int|float|null $period = null, int|float|null $overcall = null)`
- Описание: Выполняет логику `modify charged program by p i d` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\timer::runCronProgram`

- Расположение: `_core/service/timer.php:162`
- Сигнатура: `function runCronProgram(?string $pid = null)`
- Описание: Запускает или обрабатывает workflow `cron program` для этого метода класса `fan\core\service\timer`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\timer::execBackPhp`

- Расположение: `_core/service/timer.php:206`
- Сигнатура: `function execBackPhp(string $className, string $methodName, array $param, int|float $overcall = -1)`
- Описание: Выполняет логику `exec back php` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\timer::execBackBin`

- Расположение: `_core/service/timer.php:225`
- Сигнатура: `function execBackBin(string $cmd)`
- Описание: Выполняет логику `exec back bin` и возвращает вычисленный результат.

### `fan\core\service\timer::_runProgram`

- Расположение: `_core/service/timer.php:250`
- Сигнатура: `function _runProgram(\fan\model\timer_program\row $timerRow)`
- Описание: Выполняет логику `run program` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках

### `fan\core\service\timer::_modifyProgram`

- Расположение: `_core/service/timer.php:347`
- Сигнатура: `function _modifyProgram(\fan\core\base\timer_program $row, mixed $param, int|float $period, int|float $overcall)`
- Описание: Выполняет workflow-логику `modify program`.

### `fan\core\service\timer::_runBackground`

- Расположение: `_core/service/timer.php:368`
- Сигнатура: `function _runBackground(string $pid)`
- Описание: Выполняет логику `run background` и возвращает вычисленный результат.

### `fan\core\service\timer::_getCommandLine`

- Расположение: `_core/service/timer.php:380`
- Сигнатура: `function _getCommandLine(string $key)`
- Описание: Выполняет логику `get command line` и возвращает вычисленный результат.

## `_core/service/translation.php`

### `fan\core\service\translation::__construct`

- Расположение: `_core/service/translation.php:70`
- Сигнатура: `function __construct($allowIni = true)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\translation`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\translation::__destruct`

- Расположение: `_core/service/translation.php:80`
- Сигнатура: `function __destruct()`
- Описание: Завершает работу объекта и выполняет отложенную очистку для этого метода класса `fan\core\service\translation`.

### `fan\core\service\translation::getCombiPart`

- Расположение: `_core/service/translation.php:96`
- Сигнатура: `function getCombiPart()`
- Описание: Получает, читает или вычисляет данные `combi part` в рамках этого метода класса `fan\core\service\translation`.

### `fan\core\service\translation::getCombiMessage`

- Расположение: `_core/service/translation.php:113`
- Сигнатура: `function getCombiMessage(string $keyList, ?string $lng = null)`
- Описание: Получает, читает или вычисляет данные `combi message` в рамках этого метода класса `fan\core\service\translation`.

### `fan\core\service\translation::getCombiMessageAlt`

- Расположение: `_core/service/translation.php:131`
- Сигнатура: `function getCombiMessageAlt(string $phrases)`
- Описание: Получает, читает или вычисляет данные `combi message alt` в рамках этого метода класса `fan\core\service\translation`.

### `fan\core\service\translation::setEditableLng`

- Расположение: `_core/service/translation.php:147`
- Сигнатура: `function setEditableLng(string $editableLng)`
- Описание: Устанавливает, добавляет или сохраняет данные `editable lng` в рамках этого метода класса `fan\core\service\translation`.

### `fan\core\service\translation::getMessage`

- Расположение: `_core/service/translation.php:164`
- Сигнатура: `function getMessage(string $key, ?string $language = null, bool $enableML = true)`
- Описание: Получает, читает или вычисляет данные `message` в рамках этого метода класса `fan\core\service\translation`.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения

### `fan\core\service\translation::getAllMessages`

- Расположение: `_core/service/translation.php:229`
- Сигнатура: `function getAllMessages()`
- Описание: Получает, читает или вычисляет данные `all messages` в рамках этого метода класса `fan\core\service\translation`.

### `fan\core\service\translation::getMessageArr`

- Расположение: `_core/service/translation.php:244`
- Сигнатура: `function getMessageArr($lng)`
- Описание: Получает, читает или вычисляет данные `message arr` в рамках этого метода класса `fan\core\service\translation`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\translation::getMsgUseTag`

- Расположение: `_core/service/translation.php:266`
- Сигнатура: `function getMsgUseTag()`
- Описание: Получает, читает или вычисляет данные `msg use tag` в рамках этого метода класса `fan\core\service\translation`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\translation::editMessageArr`

- Расположение: `_core/service/translation.php:288`
- Сигнатура: `function editMessageArr($key, $data, $save = true)`
- Описание: Выполняет workflow-логику `edit message arr`.

### `fan\core\service\translation::deleteMessage`

- Расположение: `_core/service/translation.php:317`
- Сигнатура: `function deleteMessage(string $key)`
- Описание: Удаляет или сбрасывает состояние `message` для этого метода класса `fan\core\service\translation`.

### `fan\core\service\translation::getTagArr`

- Расположение: `_core/service/translation.php:347`
- Сигнатура: `function getTagArr()`
- Описание: Получает, читает или вычисляет данные `tag arr` в рамках этого метода класса `fan\core\service\translation`.

### `fan\core\service\translation::getRefererArr`

- Расположение: `_core/service/translation.php:363`
- Сигнатура: `function getRefererArr($key = NULL)`
- Описание: Получает, читает или вычисляет данные `referer arr` в рамках этого метода класса `fan\core\service\translation`.

### `fan\core\service\translation::editTagArr`

- Расположение: `_core/service/translation.php:391`
- Сигнатура: `function editTagArr($key, $data, $save = true)`
- Описание: Выполняет workflow-логику `edit tag arr`.

### `fan\core\service\translation::checkUrlLng`

- Расположение: `_core/service/translation.php:421`
- Сигнатура: `function checkUrlLng(string $url)`
- Описание: Проверяет условие или валидирует данные `url lng` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\translation::_formatKey`

- Расположение: `_core/service/translation.php:439`
- Сигнатура: `function _formatKey(string $key)`
- Описание: Выполняет логику `format key` и возвращает вычисленный результат.

### `fan\core\service\translation::_getFilePath`

- Расположение: `_core/service/translation.php:476`
- Сигнатура: `function _getFilePath(string $key, $repl = null)`
- Описание: Выполняет логику `get file path` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\translation::_getTag`

- Расположение: `_core/service/translation.php:495`
- Сигнатура: `function _getTag(string $key)`
- Описание: Выполняет логику `get tag` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках

### `fan\core\service\translation::_setReferer`

- Расположение: `_core/service/translation.php:524`
- Сигнатура: `function _setReferer(string $key)`
- Описание: Выполняет workflow-логику `set referer`.
- Побочные эффекты: использует service locator/helpers фреймворка; читает PHP superglobals

### `fan\core\service\translation::_setNewMessage`

- Расположение: `_core/service/translation.php:547`
- Сигнатура: `function _setNewMessage(string $keyF, string $key)`
- Описание: Выполняет workflow-логику `set new message`.

### `fan\core\service\translation::_saveMessageArr`

- Расположение: `_core/service/translation.php:561`
- Сигнатура: `function _saveMessageArr()`
- Описание: Выполняет логику `save message arr` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой; может выбрасывать исключения

### `fan\core\service\translation::_saveMsgUseTag`

- Расположение: `_core/service/translation.php:583`
- Сигнатура: `function _saveMsgUseTag()`
- Описание: Выполняет логику `save msg use tag` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой

### `fan\core\service\translation::_saveTagArr`

- Расположение: `_core/service/translation.php:599`
- Сигнатура: `function _saveTagArr()`
- Описание: Выполняет логику `save tag arr` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой

### `fan\core\service\translation::_saveRefererArr`

- Расположение: `_core/service/translation.php:615`
- Сигнатура: `function _saveRefererArr()`
- Описание: Выполняет логику `save referer arr` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой

## `_core/service/user.php`

### `fan\core\service\user::__construct`

- Расположение: `_core/service/user.php:135`
- Сигнатура: `function __construct($identifyer, $userSpace)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\user`.

### `fan\core\service\user::instance`

- Расположение: `_core/service/user.php:160`
- Сигнатура: `function instance(mixed $identifyer, ?string $reqSpace = null)`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.

### `fan\core\service\user::checkLogout`

- Расположение: `_core/service/user.php:178`
- Сигнатура: `function checkLogout()`
- Описание: Проверяет условие или валидирует данные `logout` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\user::getCurrent`

- Расположение: `_core/service/user.php:208`
- Сигнатура: `function getCurrent(?string $reqSpace = null)`
- Описание: Получает, читает или вычисляет данные `current` в рамках этого метода класса `fan\core\service\user`.

### `fan\core\service\user::getCurrentSpace`

- Расположение: `_core/service/user.php:222`
- Сигнатура: `function getCurrentSpace()`
- Описание: Получает, читает или вычисляет данные `current space` в рамках этого метода класса `fan\core\service\user`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\user::setCurrent`

- Расположение: `_core/service/user.php:271`
- Сигнатура: `function setCurrent()`
- Описание: Устанавливает, добавляет или сохраняет данные `current` в рамках этого метода класса `fan\core\service\user`.

### `fan\core\service\user::isCurrent`

- Расположение: `_core/service/user.php:291`
- Сигнатура: `function isCurrent()`
- Описание: Проверяет условие или валидирует данные `current` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\user::setPrioritySpace`

- Расположение: `_core/service/user.php:304`
- Сигнатура: `function setPrioritySpace($appName = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `priority space` в рамках этого метода класса `fan\core\service\user`.

### `fan\core\service\user::logout`

- Расположение: `_core/service/user.php:322`
- Сигнатура: `function logout()`
- Описание: Выполняет логику `logout` и возвращает вычисленный результат.

### `fan\core\service\user::getRoles`

- Расположение: `_core/service/user.php:348`
- Сигнатура: `function getRoles(bool $force = false)`
- Описание: Получает, читает или вычисляет данные `roles` в рамках этого метода класса `fan\core\service\user`.

### `fan\core\service\user::addRole`

- Расположение: `_core/service/user.php:361`
- Сигнатура: `function addRole(string $role, int|float|null $expiredTime = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `role` в рамках этого метода класса `fan\core\service\user`.

### `fan\core\service\user::removeRole`

- Расположение: `_core/service/user.php:375`
- Сигнатура: `function removeRole(string|array $role)`
- Описание: Удаляет или сбрасывает состояние `role` для этого метода класса `fan\core\service\user`.

### `fan\core\service\user::setRoles`

- Расположение: `_core/service/user.php:391`
- Сигнатура: `function setRoles(array $newRoles)`
- Описание: Устанавливает, добавляет или сохраняет данные `roles` в рамках этого метода класса `fan\core\service\user`.

### `fan\core\service\user::getUserSpace`

- Расположение: `_core/service/user.php:416`
- Сигнатура: `function getUserSpace()`
- Описание: Получает, читает или вычисляет данные `user space` в рамках этого метода класса `fan\core\service\user`.

### `fan\core\service\user::makePasswordHash`

- Расположение: `_core/service/user.php:428`
- Сигнатура: `function makePasswordHash(string $password)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `password hash` для этого метода класса `fan\core\service\user`.

### `fan\core\service\user::setData`

- Расположение: `_core/service/user.php:440`
- Сигнатура: `function setData(array $data)`
- Описание: Устанавливает, добавляет или сохраняет данные `data` в рамках этого метода класса `fan\core\service\user`.

### `fan\core\service\user::getData`

- Расположение: `_core/service/user.php:453`
- Сигнатура: `function getData()`
- Описание: Получает, читает или вычисляет данные `data` в рамках этого метода класса `fan\core\service\user`.

### `fan\core\service\user::toArray`

- Расположение: `_core/service/user.php:463`
- Сигнатура: `function toArray()`
- Описание: Выполняет логику `to array` и возвращает вычисленный результат.

### `fan\core\service\user::getEngine`

- Расположение: `_core/service/user.php:473`
- Сигнатура: `function getEngine()`
- Описание: Получает, читает или вычисляет данные `engine` в рамках этого метода класса `fan\core\service\user`.

### `fan\core\service\user::onSetAppName`

- Расположение: `_core/service/user.php:485`
- Сигнатура: `function onSetAppName(string $appName)`
- Описание: Выполняет workflow-логику `on set app name`.

### `fan\core\service\user::set`

- Расположение: `_core/service/user.php:501`
- Сигнатура: `function set(string $key, mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\service\user`.

### `fan\core\service\user::get`

- Расположение: `_core/service/user.php:516`
- Сигнатура: `function get(string $key)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\service\user`.

### `fan\core\service\user::_saveInstance`

- Расположение: `_core/service/user.php:529`
- Сигнатура: `function _saveInstance()`
- Описание: Выполняет логику `save instance` и возвращает вычисленный результат.

### `fan\core\service\user::_getCurrentUsers`

- Расположение: `_core/service/user.php:540`
- Сигнатура: `function _getCurrentUsers()`
- Описание: Выполняет логику `get current users` и возвращает вычисленный результат.

### `fan\core\service\user::_verifySpace`

- Расположение: `_core/service/user.php:565`
- Сигнатура: `function _verifySpace($userSpace)`
- Описание: Выполняет логику `verify space` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\user::_getSession`

- Расположение: `_core/service/user.php:582`
- Сигнатура: `function _getSession()`
- Описание: Выполняет логику `get session` и возвращает вычисленный результат.

### `fan\core\service\user::_getDelegate`

- Расположение: `_core/service/user.php:597`
- Сигнатура: `function _getDelegate($class)`
- Описание: Выполняет логику `get delegate` и возвращает вычисленный результат.

### `fan\core\service\user::_getSpaceConfig`

- Расположение: `_core/service/user.php:610`
- Сигнатура: `function _getSpaceConfig()`
- Описание: Выполняет логику `get space config` и возвращает вычисленный результат.

### `fan\core\service\user::_isCorrespondApp`

- Расположение: `_core/service/user.php:622`
- Сигнатура: `function _isCorrespondApp($appName = null)`
- Описание: Выполняет логику `is correspond app` и возвращает вычисленный результат.

### `fan\core\service\user::_convertToArray`

- Расположение: `_core/service/user.php:640`
- Сигнатура: `function _convertToArray(mixed $data)`
- Описание: Выполняет логику `convert to array` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\user::_checkKey`

- Расположение: `_core/service/user.php:662`
- Сигнатура: `function _checkKey($type, $key)`
- Описание: Выполняет логику `check key` и возвращает вычисленный результат.

### `fan\core\service\user::__set`

- Расположение: `_core/service/user.php:685`
- Сигнатура: `function __set($key, $value)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\user`.

### `fan\core\service\user::__get`

- Расположение: `_core/service/user.php:696`
- Сигнатура: `function __get($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\user`.

### `fan\core\service\user::serialize`

- Расположение: `_core/service/user.php:708`
- Сигнатура: `function serialize()`
- Описание: Выполняет логику `serialize` и возвращает вычисленный результат.
- Побочные эффекты: сериализует или десериализует данные

### `fan\core\service\user::unserialize`

- Расположение: `_core/service/user.php:724`
- Сигнатура: `function unserialize($data)`
- Описание: Выполняет workflow-логику `unserialize`.
- Побочные эффекты: сериализует или десериализует данные

## `_core/service/user/base.php`

### `fan\core\service\user\base::__construct`

- Расположение: `_core/service/user/base.php:112`
- Сигнатура: `function __construct($identifyer)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\service\user\base`.

### `fan\core\service\user\base::makePasswordHash`

- Расположение: `_core/service/user/base.php:127`
- Сигнатура: `function makePasswordHash($password);`
- Описание: Создает, разбирает, форматирует или конвертирует данные `password hash` для этого метода класса `fan\core\service\user\base`.

### `fan\core\service\user\base::setFacade`

- Расположение: `_core/service/user/base.php:136`
- Сигнатура: `function setFacade(\fan\core\service\user $facade)`
- Описание: Устанавливает, добавляет или сохраняет данные `facade` в рамках этого метода класса `fan\core\service\user\base`.

### `fan\core\service\user\base::setConfig`

- Расположение: `_core/service/user/base.php:151`
- Сигнатура: `function setConfig(\fan\core\service\config\row $config)`
- Описание: Устанавливает, добавляет или сохраняет данные `config` в рамках этого метода класса `fan\core\service\user\base`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\user\base::getId`

- Расположение: `_core/service/user/base.php:177`
- Сигнатура: `function getId()`
- Описание: Получает, читает или вычисляет данные `id` в рамках этого метода класса `fan\core\service\user\base`.

### `fan\core\service\user\base::getFullName`

- Расположение: `_core/service/user/base.php:189`
- Сигнатура: `function getFullName(bool $withTitle = true)`
- Описание: Получает, читает или вычисляет данные `full name` в рамках этого метода класса `fan\core\service\user\base`.

### `fan\core\service\user\base::getRoles`

- Расположение: `_core/service/user/base.php:206`
- Сигнатура: `function getRoles(bool $force = false)`
- Описание: Получает, читает или вычисляет данные `roles` в рамках этого метода класса `fan\core\service\user\base`.

### `fan\core\service\user\base::getAllData`

- Расположение: `_core/service/user/base.php:216`
- Сигнатура: `function getAllData()`
- Описание: Получает, читает или вычисляет данные `all data` в рамках этого метода класса `fan\core\service\user\base`.

### `fan\core\service\user\base::setVisitDate`

- Расположение: `_core/service/user/base.php:235`
- Сигнатура: `function setVisitDate(mixed $date = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `visit date` в рамках этого метода класса `fan\core\service\user\base`.

### `fan\core\service\user\base::setPassword`

- Расположение: `_core/service/user/base.php:254`
- Сигнатура: `function setPassword(string $password)`
- Описание: Устанавливает, добавляет или сохраняет данные `password` в рамках этого метода класса `fan\core\service\user\base`.

### `fan\core\service\user\base::checkPassword`

- Расположение: `_core/service/user/base.php:271`
- Сигнатура: `function checkPassword(string $password)`
- Описание: Проверяет условие или валидирует данные `password` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: использует service locator/helpers фреймворка; логирует или сообщает об ошибках; читает PHP superglobals

### `fan\core\service\user\base::load`

- Расположение: `_core/service/user/base.php:297`
- Сигнатура: `function load()`
- Описание: Получает, читает или вычисляет данные `load` в рамках этого метода класса `fan\core\service\user\base`.

### `fan\core\service\user\base::logout`

- Расположение: `_core/service/user/base.php:311`
- Сигнатура: `function logout()`
- Описание: Выполняет логику `logout` и возвращает вычисленный результат.

### `fan\core\service\user\base::save`

- Расположение: `_core/service/user/base.php:321`
- Сигнатура: `function save()`
- Описание: Устанавливает, добавляет или сохраняет данные `данные` в рамках этого метода класса `fan\core\service\user\base`.

### `fan\core\service\user\base::isValid`

- Расположение: `_core/service/user/base.php:339`
- Сигнатура: `function isValid()`
- Описание: Проверяет условие или валидирует данные `valid` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\user\base::isNew`

- Расположение: `_core/service/user/base.php:348`
- Сигнатура: `function isNew()`
- Описание: Проверяет условие или валидирует данные `new` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\user\base::isChanged`

- Расположение: `_core/service/user/base.php:357`
- Сигнатура: `function isChanged()`
- Описание: Проверяет условие или валидирует данные `changed` и возвращает результат либо выбрасывает исключение.

### `fan\core\service\user\base::_loadData`

- Расположение: `_core/service/user/base.php:369`
- Сигнатура: `function _loadData();`
- Описание: Выполняет workflow-логику `load data`.

### `fan\core\service\user\base::_saveData`

- Расположение: `_core/service/user/base.php:375`
- Сигнатура: `function _saveData();`
- Описание: Выполняет workflow-логику `save data`.

### `fan\core\service\user\base::_validateForSave`

- Расположение: `_core/service/user/base.php:381`
- Сигнатура: `function _validateForSave();`
- Описание: Выполняет workflow-логику `validate for save`.

### `fan\core\service\user\base::_getKeyList`

- Расположение: `_core/service/user/base.php:388`
- Сигнатура: `function _getKeyList()`
- Описание: Выполняет логику `get key list` и возвращает вычисленный результат.

### `fan\core\service\user\base::_set`

- Расположение: `_core/service/user/base.php:419`
- Сигнатура: `function _set(string $key, mixed $val)`
- Описание: Выполняет логику `set` и возвращает вычисленный результат.

### `fan\core\service\user\base::_get`

- Расположение: `_core/service/user/base.php:434`
- Сигнатура: `function _get(string $key)`
- Описание: Выполняет логику `get` и возвращает вычисленный результат.

### `fan\core\service\user\base::_convCamelCase`

- Расположение: `_core/service/user/base.php:446`
- Сигнатура: `function _convCamelCase(string $str)`
- Описание: Выполняет логику `conv camel case` и возвращает вычисленный результат.

### `fan\core\service\user\base::__call`

- Расположение: `_core/service/user/base.php:463`
- Сигнатура: `function __call($method, $args)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\service\user\base`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\user\base::serialize`

- Расположение: `_core/service/user/base.php:481`
- Сигнатура: `function serialize()`
- Описание: Выполняет логику `serialize` и возвращает вычисленный результат.
- Побочные эффекты: сериализует или десериализует данные

### `fan\core\service\user\base::unserialize`

- Расположение: `_core/service/user/base.php:501`
- Сигнатура: `function unserialize($data)`
- Описание: Выполняет workflow-логику `unserialize`.
- Побочные эффекты: сериализует или десериализует данные

## `_core/service/user/config.php`

### `fan\core\service\user\config::makePasswordHash`

- Расположение: `_core/service/user/config.php:36`
- Сигнатура: `function makePasswordHash(string $password)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `password hash` для этого метода класса `fan\core\service\user\config`.

### `fan\core\service\user\config::_loadData`

- Расположение: `_core/service/user/config.php:49`
- Сигнатура: `function _loadData()`
- Описание: Выполняет логику `load data` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\user\config::_getAnonymousData`

- Расположение: `_core/service/user/config.php:88`
- Сигнатура: `function _getAnonymousData(\fan\core\service\config\row $rule)`
- Описание: Выполняет логику `get anonymous data` и возвращает вычисленный результат.

### `fan\core\service\user\config::_getAuthorizedData`

- Расположение: `_core/service/user/config.php:112`
- Сигнатура: `function _getAuthorizedData($rule)`
- Описание: Выполняет логику `get authorized data` и возвращает вычисленный результат.

### `fan\core\service\user\config::_saveData`

- Расположение: `_core/service/user/config.php:141`
- Сигнатура: `function _saveData()`
- Описание: Выполняет логику `save data` и возвращает вычисленный результат.

### `fan\core\service\user\config::_validateForSave`

- Расположение: `_core/service/user/config.php:151`
- Сигнатура: `function _validateForSave()`
- Описание: Выполняет логику `validate for save` и возвращает вычисленный результат.

### `fan\core\service\user\config::_getAccessRule`

- Расположение: `_core/service/user/config.php:161`
- Сигнатура: `function _getAccessRule()`
- Описание: Выполняет логику `get access rule` и возвращает вычисленный результат.
- Побочные эффекты: читает PHP superglobals

### `fan\core\service\user\config::_getAuthentication`

- Расположение: `_core/service/user/config.php:182`
- Сигнатура: `function _getAuthentication()`
- Описание: Выполняет логику `get authentication` и возвращает вычисленный результат.

### `fan\core\service\user\config::_mergeRoles`

- Расположение: `_core/service/user/config.php:202`
- Сигнатура: `function _mergeRoles(&$target, mixed $source)`
- Описание: Выполняет логику `merge roles` и возвращает вычисленный результат.

## `_core/service/user/entity.php`

### `fan\core\service\user\entity::makePasswordHash`

- Расположение: `_core/service/user/entity.php:43`
- Сигнатура: `function makePasswordHash(string $password)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `password hash` для этого метода класса `fan\core\service\user\entity`.

### `fan\core\service\user\entity::_loadData`

- Расположение: `_core/service/user/entity.php:56`
- Сигнатура: `function _loadData()`
- Описание: Выполняет логику `load data` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\service\user\entity::_saveData`

- Расположение: `_core/service/user/entity.php:76`
- Сигнатура: `function _saveData()`
- Описание: Выполняет логику `save data` и возвращает вычисленный результат.

### `fan\core\service\user\entity::_validateForSave`

- Расположение: `_core/service/user/entity.php:100`
- Сигнатура: `function _validateForSave()`
- Описание: Выполняет логику `validate for save` и возвращает вычисленный результат.

### `fan\core\service\user\entity::_getEntityData`

- Расположение: `_core/service/user/entity.php:111`
- Сигнатура: `function _getEntityData()`
- Описание: Выполняет логику `get entity data` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\user\entity::_getMethodList`

- Расположение: `_core/service/user/entity.php:146`
- Сигнатура: `function _getMethodList(string $type)`
- Описание: Выполняет логику `get method list` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\service\user\entity::_getRow`

- Расположение: `_core/service/user/entity.php:181`
- Сигнатура: `function _getRow()`
- Описание: Выполняет логику `get row` и возвращает вычисленный результат.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_core/view/definer.php`

### `fan\core\view\definer::__construct`

- Расположение: `_core/view/definer.php:60`
- Сигнатура: `function __construct(array $config)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\view\definer`.

### `fan\core\view\definer::getViewParserName`

- Расположение: `_core/view/definer.php:76`
- Сигнатура: `function getViewParserName()`
- Описание: Получает, читает или вычисляет данные `view parser name` в рамках этого метода класса `fan\core\view\definer`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\core\view\definer::_getConditions`

- Расположение: `_core/view/definer.php:96`
- Сигнатура: `function _getConditions()`
- Описание: Выполняет логику `get conditions` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения; выполняет database операции

### `fan\core\view\definer::_getConditionValue`

- Расположение: `_core/view/definer.php:168`
- Сигнатура: `function _getConditionValue(mixed $val, $v3, $v2)`
- Описание: Выполняет логику `get condition value` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\view\definer::_getStringExpr`

- Расположение: `_core/view/definer.php:199`
- Сигнатура: `function _getStringExpr(string $key, string $src, mixed $val)`
- Описание: Выполняет логику `get string expr` и возвращает вычисленный результат.

### `fan\core\view\definer::_getIntegerExpr`

- Расположение: `_core/view/definer.php:213`
- Сигнатура: `function _getIntegerExpr(string $key, string $src, mixed $val)`
- Описание: Выполняет логику `get integer expr` и возвращает вычисленный результат.

### `fan\core\view\definer::_getNumericExpr`

- Расположение: `_core/view/definer.php:227`
- Сигнатура: `function _getNumericExpr(string $key, string $src, mixed $val)`
- Описание: Выполняет логику `get numeric expr` и возвращает вычисленный результат.

### `fan\core\view\definer::_getBooleanExpr`

- Расположение: `_core/view/definer.php:255`
- Сигнатура: `function _getBooleanExpr(string $key, string $src, mixed $val)`
- Описание: Выполняет логику `get boolean expr` и возвращает вычисленный результат.

### `fan\core\view\definer::_getRegexpExpr`

- Расположение: `_core/view/definer.php:269`
- Сигнатура: `function _getRegexpExpr(string $key, string $src, mixed $val)`
- Описание: Выполняет логику `get regexp expr` и возвращает вычисленный результат.

## `_core/view/keeper.php`

### `fan\core\view\keeper::__construct`

- Расположение: `_core/view/keeper.php:31`
- Сигнатура: `function __construct(\fan\core\view\router $router)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\view\keeper`.

### `fan\core\view\keeper::get`

- Расположение: `_core/view/keeper.php:50`
- Сигнатура: `function get($key = null, $default = null, $logError = true)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\view\keeper`.

### `fan\core\view\keeper::set`

- Расположение: `_core/view/keeper.php:65`
- Сигнатура: `function set($key, $value, $rewriteExisting = true, $convArray = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\view\keeper`.

### `fan\core\view\keeper::clear`

- Расположение: `_core/view/keeper.php:80`
- Сигнатура: `function clear()`
- Описание: Удаляет или сбрасывает состояние `clear` для этого метода класса `fan\core\view\keeper`.

### `fan\core\view\keeper::getRouter`

- Расположение: `_core/view/keeper.php:93`
- Сигнатура: `function getRouter()`
- Описание: Получает, читает или вычисляет данные `router` в рамках этого метода класса `fan\core\view\keeper`.

### `fan\core\view\keeper::getBlock`

- Расположение: `_core/view/keeper.php:103`
- Сигнатура: `function getBlock()`
- Описание: Получает, читает или вычисляет данные `block` в рамках этого метода класса `fan\core\view\keeper`.

## `_core/view/keeper/loader/json.php`

### `fan\core\view\keeper\loader\json::__construct`

- Расположение: `_core/view/keeper/loader/json.php:26`
- Сигнатура: `function __construct(\fan\core\view\router $router)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\view\keeper\loader\json`.

### `fan\core\view\keeper\loader\json::addRouter`

- Расположение: `_core/view/keeper/loader/json.php:42`
- Сигнатура: `function addRouter(\fan\core\view\router\loader $router)`
- Описание: Устанавливает, добавляет или сохраняет данные `router` в рамках этого метода класса `fan\core\view\keeper\loader\json`.

## `_core/view/keeper/loader/text.php`

### `fan\core\view\keeper\loader\text::__construct`

- Расположение: `_core/view/keeper/loader/text.php:26`
- Сигнатура: `function __construct(\fan\core\view\router $router)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\view\keeper\loader\text`.

### `fan\core\view\keeper\loader\text::get`

- Расположение: `_core/view/keeper/loader/text.php:45`
- Сигнатура: `function get($key = null, $default = null, $logError = true)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\view\keeper\loader\text`.

### `fan\core\view\keeper\loader\text::set`

- Расположение: `_core/view/keeper/loader/text.php:61`
- Сигнатура: `function set($key, $value, $rewriteExisting = true, $convArray = null)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\view\keeper\loader\text`.

### `fan\core\view\keeper\loader\text::addRouter`

- Расположение: `_core/view/keeper/loader/text.php:84`
- Сигнатура: `function addRouter(\fan\core\view\router\loader $router)`
- Описание: Устанавливает, добавляет или сохраняет данные `router` в рамках этого метода класса `fan\core\view\keeper\loader\text`.

### `fan\core\view\keeper\loader\text::__set`

- Расположение: `_core/view/keeper/loader/text.php:103`
- Сигнатура: `function __set($key, $value)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\view\keeper\loader\text`.

### `fan\core\view\keeper\loader\text::__get`

- Расположение: `_core/view/keeper/loader/text.php:115`
- Сигнатура: `function __get($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\view\keeper\loader\text`.

### `fan\core\view\keeper\loader\text::__toString`

- Расположение: `_core/view/keeper/loader/text.php:125`
- Сигнатура: `function __toString()`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\view\keeper\loader\text`.

## `_core/view/parser.php`

### `fan\core\view\parser::__construct`

- Расположение: `_core/view/parser.php:43`
- Сигнатура: `function __construct(\fan\core\block\base $mainBlock)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\view\parser`.

### `fan\core\view\parser::getFormat`

- Расположение: `_core/view/parser.php:54`
- Сигнатура: `function getFormat()`
- Описание: Получает, читает или вычисляет данные `format` в рамках этого метода класса `fan\core\view\parser`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\view\parser::getRouter`

- Расположение: `_core/view/parser.php:65`
- Сигнатура: `function getRouter(\fan\core\block\base $block)`
- Описание: Получает, читает или вычисляет данные `router` в рамках этого метода класса `fan\core\view\parser`.

### `fan\core\view\parser::startParsing`

- Расположение: `_core/view/parser.php:79`
- Сигнатура: `function startParsing(\fan\core\block\base $rootBlock)`
- Описание: Запускает или обрабатывает workflow `parsing` для этого метода класса `fan\core\view\parser`.

### `fan\core\view\parser::getFinalContent`

- Расположение: `_core/view/parser.php:91`
- Сигнатура: `function getFinalContent()`
- Описание: Получает, читает или вычисляет данные `final content` в рамках этого метода класса `fan\core\view\parser`.

### `fan\core\view\parser::getResultData`

- Расположение: `_core/view/parser.php:105`
- Сигнатура: `function getResultData(\fan\core\block\base $block)`
- Описание: Получает, читает или вычисляет данные `result data` в рамках этого метода класса `fan\core\view\parser`.

### `fan\core\view\parser::_assembleToArray`

- Расположение: `_core/view/parser.php:118`
- Сигнатура: `function _assembleToArray(\fan\core\block\base $block)`
- Описание: Выполняет логику `assemble to array` и возвращает вычисленный результат.

### `fan\core\view\parser::_mixEmbededData`

- Расположение: `_core/view/parser.php:140`
- Сигнатура: `function _mixEmbededData(array $blockData, array $embededData)`
- Описание: Выполняет логику `mix embeded data` и возвращает вычисленный результат.

### `fan\core\view\parser::_parseTemplate`

- Расположение: `_core/view/parser.php:157`
- Сигнатура: `function _parseTemplate(\fan\core\block\base $block, $tplVar)`
- Описание: Выполняет логику `parse template` и возвращает вычисленный результат.

### `fan\core\view\parser::_formatResultData`

- Расположение: `_core/view/parser.php:195`
- Сигнатура: `function _formatResultData(\fan\core\block\base $block, array $srcData, string $tplResult)`
- Описание: Выполняет логику `format result data` и возвращает вычисленный результат.

### `fan\core\view\parser::_setHeaders`

- Расположение: `_core/view/parser.php:209`
- Сигнатура: `function _setHeaders($result, $contentType = 'text/plain', $encoding = null)`
- Описание: Выполняет логику `set headers` и возвращает вычисленный результат.
- Побочные эффекты: меняет HTTP/session состояние

## `_core/view/parser/debug1.php`

### `fan\core\view\parser\debug1::__construct`

- Расположение: `_core/view/parser/debug1.php:31`
- Сигнатура: `function __construct(\fan\core\block\base $mainBlock)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\view\parser\debug1`.

### `fan\core\view\parser\debug1::getResultData`

- Расположение: `_core/view/parser/debug1.php:48`
- Сигнатура: `function getResultData(\fan\core\block\base $rootBlock)`
- Описание: Получает, читает или вычисляет данные `result data` в рамках этого метода класса `fan\core\view\parser\debug1`.

### `fan\core\view\parser\debug1::_getInternalResultData`

- Расположение: `_core/view/parser/debug1.php:76`
- Сигнатура: `function _getInternalResultData(\fan\core\block\base $block)`
- Описание: Выполняет логику `get internal result data` и возвращает вычисленный результат.

## `_core/view/parser/debug2.php`

### `fan\core\view\parser\debug2::__construct`

- Расположение: `_core/view/parser/debug2.php:31`
- Сигнатура: `function __construct(\fan\core\block\base $mainBlock)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\view\parser\debug2`.

### `fan\core\view\parser\debug2::getFormat`

- Расположение: `_core/view/parser/debug2.php:45`
- Сигнатура: `function getFormat()`
- Описание: Получает, читает или вычисляет данные `format` в рамках этого метода класса `fan\core\view\parser\debug2`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\view\parser\debug2::getResultData`

- Расположение: `_core/view/parser/debug2.php:59`
- Сигнатура: `function getResultData(\fan\core\block\base $block)`
- Описание: Получает, читает или вычисляет данные `result data` в рамках этого метода класса `fan\core\view\parser\debug2`.

### `fan\core\view\parser\debug2::_getInternalResultData`

- Расположение: `_core/view/parser/debug2.php:80`
- Сигнатура: `function _getInternalResultData(\fan\core\block\base $block, $isView)`
- Описание: Выполняет логику `get internal result data` и возвращает вычисленный результат.

### `fan\core\view\parser\debug2::_setHeaders`

- Расположение: `_core/view/parser/debug2.php:98`
- Сигнатура: `function _setHeaders($result, $contentType = 'text/html', $encoding = null)`
- Описание: Выполняет логику `set headers` и возвращает вычисленный результат.

## `_core/view/parser/html.php`

### `fan\core\view\parser\html::getFormat`

- Расположение: `_core/view/parser/html.php:27`
- Сигнатура: `function getFormat()`
- Описание: Получает, читает или вычисляет данные `format` в рамках этого метода класса `fan\core\view\parser\html`.

### `fan\core\view\parser\html::getRouter`

- Расположение: `_core/view/parser/html.php:38`
- Сигнатура: `function getRouter(\fan\core\block\base $block)`
- Описание: Получает, читает или вычисляет данные `router` в рамках этого метода класса `fan\core\view\parser\html`.

### `fan\core\view\parser\html::getResultData`

- Расположение: `_core/view/parser/html.php:52`
- Сигнатура: `function getResultData(\fan\core\block\base $block)`
- Описание: Получает, читает или вычисляет данные `result data` в рамках этого метода класса `fan\core\view\parser\html`.

### `fan\core\view\parser\html::_setHeaders`

- Расположение: `_core/view/parser/html.php:74`
- Сигнатура: `function _setHeaders($result, $contentType = 'text/html', $encoding = null)`
- Описание: Выполняет логику `set headers` и возвращает вычисленный результат.

## `_core/view/parser/json.php`

### `fan\core\view\parser\json::getFormat`

- Расположение: `_core/view/parser/json.php:28`
- Сигнатура: `function getFormat()`
- Описание: Получает, читает или вычисляет данные `format` в рамках этого метода класса `fan\core\view\parser\json`.

### `fan\core\view\parser\json::getRouter`

- Расположение: `_core/view/parser/json.php:38`
- Сигнатура: `function getRouter(\fan\core\block\base $block)`
- Описание: Получает, читает или вычисляет данные `router` в рамках этого метода класса `fan\core\view\parser\json`.

### `fan\core\view\parser\json::getFinalContent`

- Расположение: `_core/view/parser/json.php:48`
- Сигнатура: `function getFinalContent()`
- Описание: Получает, читает или вычисляет данные `final content` в рамках этого метода класса `fan\core\view\parser\json`.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_core/view/parser/loader.php`

### `fan\core\view\parser\loader::getFormat`

- Расположение: `_core/view/parser/loader.php:27`
- Сигнатура: `function getFormat()`
- Описание: Получает, читает или вычисляет данные `format` в рамках этого метода класса `fan\core\view\parser\loader`.

### `fan\core\view\parser\loader::getRouter`

- Расположение: `_core/view/parser/loader.php:38`
- Сигнатура: `function getRouter(\fan\core\block\base $block)`
- Описание: Получает, читает или вычисляет данные `router` в рамках этого метода класса `fan\core\view\parser\loader`.

### `fan\core\view\parser\loader::getFinalContent`

- Расположение: `_core/view/parser/loader.php:50`
- Сигнатура: `function getFinalContent()`
- Описание: Получает, читает или вычисляет данные `final content` в рамках этого метода класса `fan\core\view\parser\loader`.

### `fan\core\view\parser\loader::getResultData`

- Расположение: `_core/view/parser/loader.php:74`
- Сигнатура: `function getResultData(\fan\core\block\base $block)`
- Описание: Получает, читает или вычисляет данные `result data` в рамках этого метода класса `fan\core\view\parser\loader`.

### `fan\core\view\parser\loader::_getTplResult`

- Расположение: `_core/view/parser/loader.php:93`
- Сигнатура: `function _getTplResult(\fan\core\block\base $block)`
- Описание: Выполняет логику `get tpl result` и возвращает вычисленный результат.

## `_core/view/parser/soap.php`

### `fan\core\view\parser\soap::getFormat`

- Расположение: `_core/view/parser/soap.php:27`
- Сигнатура: `function getFormat()`
- Описание: Получает, читает или вычисляет данные `format` в рамках этого метода класса `fan\core\view\parser\soap`.

## `_core/view/parser/xml.php`

### `fan\core\view\parser\xml::getFormat`

- Расположение: `_core/view/parser/xml.php:27`
- Сигнатура: `function getFormat()`
- Описание: Получает, читает или вычисляет данные `format` в рамках этого метода класса `fan\core\view\parser\xml`.

### `fan\core\view\parser\xml::getFinalContent`

- Расположение: `_core/view/parser/xml.php:39`
- Сигнатура: `function getFinalContent()`
- Описание: Получает, читает или вычисляет данные `final content` в рамках этого метода класса `fan\core\view\parser\xml`.

### `fan\core\view\parser\xml::_makeDomElements`

- Расположение: `_core/view/parser/xml.php:59`
- Сигнатура: `function _makeDomElements(\DOMNode $parent, $data)`
- Описание: Выполняет логику `make dom elements` и возвращает вычисленный результат.

## `_core/view/router.php`

### `fan\core\view\router::__construct`

- Расположение: `_core/view/router.php:42`
- Сигнатура: `function __construct(\fan\core\block\base $block)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\view\router`.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\view\router::__set`

- Расположение: `_core/view/router.php:66`
- Сигнатура: `function __set($key, $value)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\view\router`.

### `fan\core\view\router::__get`

- Расположение: `_core/view/router.php:78`
- Сигнатура: `function __get($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\view\router`.

### `fan\core\view\router::__isset`

- Расположение: `_core/view/router.php:90`
- Сигнатура: `function __isset($key)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\core\view\router`.

### `fan\core\view\router::offsetSet`

- Расположение: `_core/view/router.php:105`
- Сигнатура: `function offsetSet($key, mixed $value)`
- Описание: Выполняет workflow-логику `offset set`.

### `fan\core\view\router::offsetGet`

- Расположение: `_core/view/router.php:117`
- Сигнатура: `function offsetGet($key)`
- Описание: Выполняет логику `offset get` и возвращает вычисленный результат.

### `fan\core\view\router::offsetExists`

- Расположение: `_core/view/router.php:129`
- Сигнатура: `function offsetExists($key)`
- Описание: Выполняет логику `offset exists` и возвращает вычисленный результат.

### `fan\core\view\router::offsetUnset`

- Расположение: `_core/view/router.php:141`
- Сигнатура: `function offsetUnset($key)`
- Описание: Выполняет workflow-логику `offset unset`.

### `fan\core\view\router::set`

- Расположение: `_core/view/router.php:153`
- Сигнатура: `function set($key, $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\view\router`.

### `fan\core\view\router::get`

- Расположение: `_core/view/router.php:180`
- Сигнатура: `function get(string $key, mixed $default = null, bool $logError = true)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `fan\core\view\router`.

### `fan\core\view\router::setSeveral`

- Расположение: `_core/view/router.php:200`
- Сигнатура: `function setSeveral(array $values)`
- Описание: Устанавливает, добавляет или сохраняет данные `several` в рамках этого метода класса `fan\core\view\router`.

### `fan\core\view\router::getBlock`

- Расположение: `_core/view/router.php:213`
- Сигнатура: `function getBlock()`
- Описание: Получает, читает или вычисляет данные `block` в рамках этого метода класса `fan\core\view\router`.

### `fan\core\view\router::toArray`

- Расположение: `_core/view/router.php:223`
- Сигнатура: `function toArray()`
- Описание: Выполняет логику `to array` и возвращает вычисленный результат.

### `fan\core\view\router::getAll`

- Расположение: `_core/view/router.php:233`
- Сигнатура: `function getAll()`
- Описание: Получает, читает или вычисляет данные `all` в рамках этого метода класса `fan\core\view\router`.

### `fan\core\view\router::count`

- Расположение: `_core/view/router.php:250`
- Сигнатура: `function count()`
- Описание: Выполняет логику `count` и возвращает вычисленный результат.

### `fan\core\view\router::_getKeeper`

- Расположение: `_core/view/router.php:265`
- Сигнатура: `function _getKeeper(string $key)`
- Описание: Выполняет логику `get keeper` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\core\view\router::_checkSetter`

- Расположение: `_core/view/router.php:282`
- Сигнатура: `function _checkSetter()`
- Описание: Выполняет логику `check setter` и возвращает вычисленный результат.

## `_core/view/router/json.php`

### `fan\core\view\router\json::useBase64`

- Расположение: `_core/view/router/json.php:35`
- Сигнатура: `function useBase64(bool $useBase64 = true)`
- Описание: Выполняет логику `use base64` и возвращает вычисленный результат.

### `fan\core\view\router\json::isUseBase64`

- Расположение: `_core/view/router/json.php:45`
- Сигнатура: `function isUseBase64()`
- Описание: Проверяет условие или валидирует данные `use base64` и возвращает результат либо выбрасывает исключение.

## `_core/view/router/loader.php`

### `fan\core\view\router\loader::set`

- Расположение: `_core/view/router/loader.php:59`
- Сигнатура: `function set($key, $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `fan\core\view\router\loader`.

### `fan\core\view\router\loader::getJson`

- Расположение: `_core/view/router/loader.php:78`
- Сигнатура: `function getJson(string|array|null $key = null, mixed $default = null, bool $logError = true)`
- Описание: Получает, читает или вычисляет данные `json` в рамках этого метода класса `fan\core\view\router\loader`.

### `fan\core\view\router\loader::setJson`

- Расположение: `_core/view/router/loader.php:92`
- Сигнатура: `function setJson(string|int|float $key, mixed $value, bool $rewriteExisting = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `json` в рамках этого метода класса `fan\core\view\router\loader`.

### `fan\core\view\router\loader::getText`

- Расположение: `_core/view/router/loader.php:102`
- Сигнатура: `function getText()`
- Описание: Получает, читает или вычисляет данные `text` в рамках этого метода класса `fan\core\view\router\loader`.

### `fan\core\view\router\loader::setText`

- Расположение: `_core/view/router/loader.php:115`
- Сигнатура: `function setText(mixed $value, int $position = 1)`
- Описание: Устанавливает, добавляет или сохраняет данные `text` в рамках этого метода класса `fan\core\view\router\loader`.

### `fan\core\view\router\loader::isFullRewrite`

- Расположение: `_core/view/router/loader.php:128`
- Сигнатура: `function isFullRewrite(\fan\core\view\keeper $keeper)`
- Описание: Проверяет условие или валидирует данные `full rewrite` и возвращает результат либо выбрасывает исключение.

### `fan\core\view\router\loader::_getJsonKeeper`

- Расположение: `_core/view/router/loader.php:144`
- Сигнатура: `function _getJsonKeeper()`
- Описание: Выполняет логику `get json keeper` и возвращает вычисленный результат.

### `fan\core\view\router\loader::_getTextKeeper`

- Расположение: `_core/view/router/loader.php:159`
- Сигнатура: `function _getTextKeeper()`
- Описание: Выполняет логику `get text keeper` и возвращает вычисленный результат.

## `_project/app/__log_viewer/common/root.php`

### `fan\app\__log_viewer\common\root::setTitle`

- Расположение: `_project/app/__log_viewer/common/root.php:30`
- Сигнатура: `function setTitle(string $title, bool $checkIsSet = false)`
- Описание: Устанавливает, добавляет или сохраняет данные `title` в рамках этого метода класса `fan\app\__log_viewer\common\root`.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_project/app/__log_viewer/design/header.php`

### `fan\app\__log_viewer\design\header::init`

- Расположение: `_project/app/__log_viewer/design/header.php:26`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__log_viewer\design\header`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\app\__log_viewer\design\header::setFileList`

- Расположение: `_project/app/__log_viewer/design/header.php:71`
- Сигнатура: `function setFileList(&$dt, $path, $regexp)`
- Описание: Устанавливает, добавляет или сохраняет данные `file list` в рамках этого метода класса `fan\app\__log_viewer\design\header`.
- Побочные эффекты: может выбрасывать исключения

## `_project/app/__log_viewer/design/nav.php`

### `fan\app\__log_viewer\design\nav::getNavUrl`

- Расположение: `_project/app/__log_viewer/design/nav.php:29`
- Сигнатура: `function getNavUrl(string $key, string $addUrl = '')`
- Описание: Получает, читает или вычисляет данные `nav url` в рамках этого метода класса `fan\app\__log_viewer\design\nav`.

### `fan\app\__log_viewer\design\nav::getVarieties`

- Расположение: `_project/app/__log_viewer/design/nav.php:39`
- Сигнатура: `function getVarieties()`
- Описание: Получает, читает или вычисляет данные `varieties` в рамках этого метода класса `fan\app\__log_viewer\design\nav`.

## `_project/app/__log_viewer/main/error403.php`

### `fan\app\__log_viewer\main\error403::init`

- Расположение: `_project/app/__log_viewer/main/error403.php:15`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__log_viewer\main\error403`.

## `_project/app/__log_viewer/main/error404.php`

### `fan\app\__log_viewer\main\error404::init`

- Расположение: `_project/app/__log_viewer/main/error404.php:15`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__log_viewer\main\error404`.

## `_project/app/__log_viewer/main/get_log_data.php`

### `fan\app\__log_viewer\main\get_log_data::init`

- Расположение: `_project/app/__log_viewer/main/get_log_data.php:27`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__log_viewer\main\get_log_data`.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_project/app/__log_viewer/main/get_trace.php`

### `fan\app\__log_viewer\main\get_trace::init`

- Расположение: `_project/app/__log_viewer/main/get_trace.php:27`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__log_viewer\main\get_trace`.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_project/app/__log_viewer/main/index.php`

### `fan\app\__log_viewer\main\index::init`

- Расположение: `_project/app/__log_viewer/main/index.php:26`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__log_viewer\main\index`.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_project/app/__log_viewer/main/request_password.php`

### `fan\app\__log_viewer\main\request_password::init`

- Расположение: `_project/app/__log_viewer/main/request_password.php:32`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__log_viewer\main\request_password`.

### `fan\app\__log_viewer\main\request_password::checkPassword`

- Расположение: `_project/app/__log_viewer/main/request_password.php:45`
- Сигнатура: `function checkPassword(mixed $value, array $data)`
- Описание: Проверяет условие или валидирует данные `password` и возвращает результат либо выбрасывает исключение.

### `fan\app\__log_viewer\main\request_password::onSubmit`

- Расположение: `_project/app/__log_viewer/main/request_password.php:56`
- Сигнатура: `function onSubmit()`
- Описание: Выполняет workflow-логику `on submit`.

## `_project/app/__tools/common/root.php`

### `fan\app\__tools\common\root::setTitle`

- Расположение: `_project/app/__tools/common/root.php:30`
- Сигнатура: `function setTitle(string $title, bool $checkIsSet = false)`
- Описание: Устанавливает, добавляет или сохраняет данные `title` в рамках этого метода класса `fan\app\__tools\common\root`.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_project/app/__tools/design/header.php`

### `fan\app\__tools\design\header::init`

- Расположение: `_project/app/__tools/design/header.php:26`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__tools\design\header`.

## `_project/app/__tools/design/nav/application_subnav.php`

### `fan\app\__tools\design\application_subnav::init`

- Расположение: `_project/app/__tools/design/nav/application_subnav.php:25`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__tools\design\application_subnav`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\app\__tools\design\application_subnav::getCurrentName`

- Расположение: `_project/app/__tools/design/nav/application_subnav.php:48`
- Сигнатура: `function getCurrentName()`
- Описание: Получает, читает или вычисляет данные `current name` в рамках этого метода класса `fan\app\__tools\design\application_subnav`.

## `_project/app/__tools/design/nav/counter_subnav.php`

### `fan\app\__tools\design\counter_subnav::init`

- Расположение: `_project/app/__tools/design/nav/counter_subnav.php:19`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__tools\design\counter_subnav`.

### `fan\app\__tools\design\counter_subnav::getNavUrl`

- Расположение: `_project/app/__tools/design/nav/counter_subnav.php:38`
- Сигнатура: `function getNavUrl(string $key, string $addUrl = '')`
- Описание: Получает, читает или вычисляет данные `nav url` в рамках этого метода класса `fan\app\__tools\design\counter_subnav`.

## `_project/app/__tools/design/nav/db_connection_subnav.php`

### `fan\app\__tools\design\db_connection_subnav::init`

- Расположение: `_project/app/__tools/design/nav/db_connection_subnav.php:25`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__tools\design\db_connection_subnav`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\app\__tools\design\db_connection_subnav::getCurrentName`

- Расположение: `_project/app/__tools/design/nav/db_connection_subnav.php:48`
- Сигнатура: `function getCurrentName()`
- Описание: Получает, читает или вычисляет данные `current name` в рамках этого метода класса `fan\app\__tools\design\db_connection_subnav`.

## `_project/app/__tools/form/entity_filter.php`

### `fan\app\__tools\form\entity_filter::init`

- Расположение: `_project/app/__tools/form/entity_filter.php:26`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__tools\form\entity_filter`.

### `fan\app\__tools\form\entity_filter::getDbList`

- Расположение: `_project/app/__tools/form/entity_filter.php:36`
- Сигнатура: `function getDbList()`
- Описание: Получает, читает или вычисляет данные `db list` в рамках этого метода класса `fan\app\__tools\form\entity_filter`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\app\__tools\form\entity_filter::getDirList`

- Расположение: `_project/app/__tools/form/entity_filter.php:54`
- Сигнатура: `function getDirList()`
- Описание: Получает, читает или вычисляет данные `dir list` в рамках этого метода класса `fan\app\__tools\form\entity_filter`.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_project/app/__tools/main/conv_entity.php`

### `fan\app\__tools\main\conv_entity::init`

- Расположение: `_project/app/__tools/main/conv_entity.php:38`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__tools\main\conv_entity`.

### `fan\app\__tools\main\conv_entity::onSubmit`

- Расположение: `_project/app/__tools/main/conv_entity.php:48`
- Сигнатура: `function onSubmit()`
- Описание: Выполняет workflow-логику `on submit`.
- Побочные эффекты: работает с файловой системой; может выбрасывать исключения

### `fan\app\__tools\main\conv_entity::_makeFileList`

- Расположение: `_project/app/__tools/main/conv_entity.php:107`
- Сигнатура: `function _makeFileList($path, $mask)`
- Описание: Выполняет логику `make file list` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\app\__tools\main\conv_entity::_getNameSpace`

- Расположение: `_project/app/__tools/main/conv_entity.php:142`
- Сигнатура: `function _getNameSpace($path)`
- Описание: Выполняет логику `get name space` и возвращает вычисленный результат.

### `fan\app\__tools\main\conv_entity::_makeRowContent`

- Расположение: `_project/app/__tools/main/conv_entity.php:158`
- Сигнатура: `function _makeRowContent(&$srcContent, $srcName)`
- Описание: Выполняет логику `make row content` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\app\__tools\main\conv_entity::_makeEntityContent`

- Расположение: `_project/app/__tools/main/conv_entity.php:205`
- Сигнатура: `function _makeEntityContent(&$srcContent)`
- Описание: Выполняет логику `make entity content` и возвращает вычисленный результат.

### `fan\app\__tools\main\conv_entity::_makeRequestContent`

- Расположение: `_project/app/__tools/main/conv_entity.php:263`
- Сигнатура: `function _makeRequestContent(&$srcContent)`
- Описание: Выполняет логику `make request content` и возвращает вычисленный результат.

### `fan\app\__tools\main\conv_entity::_createFile`

- Расположение: `_project/app/__tools/main/conv_entity.php:294`
- Сигнатура: `function _createFile(string $class, string $tableName, $content)`
- Описание: Выполняет логику `create file` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой

### `fan\app\__tools\main\conv_entity::_addSpace`

- Расположение: `_project/app/__tools/main/conv_entity.php:341`
- Сигнатура: `function _addSpace(&$content, bool $addCond = true)`
- Описание: Выполняет логику `add space` и возвращает вычисленный результат.

### `fan\app\__tools\main\conv_entity::_removeUsed`

- Расположение: `_project/app/__tools/main/conv_entity.php:357`
- Сигнатура: `function _removeUsed(&$content, $texts)`
- Описание: Выполняет логику `remove used` и возвращает вычисленный результат.

## `_project/app/__tools/main/create_entity.php`

### `fan\app\__tools\main\create_entity::init`

- Расположение: `_project/app/__tools/main/create_entity.php:28`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__tools\main\create_entity`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\app\__tools\main\create_entity::onSubmit`

- Расположение: `_project/app/__tools/main/create_entity.php:86`
- Сигнатура: `function onSubmit()`
- Описание: Выполняет workflow-логику `on submit`.
- Побочные эффекты: работает с файловой системой; использует service locator/helpers фреймворка

### `fan\app\__tools\main\create_entity::getMethodList`

- Расположение: `_project/app/__tools/main/create_entity.php:159`
- Сигнатура: `function getMethodList($tableName)`
- Описание: Получает, читает или вычисляет данные `method list` в рамках этого метода класса `fan\app\__tools\main\create_entity`.

### `fan\app\__tools\main\create_entity::getParamByDb`

- Расположение: `_project/app/__tools/main/create_entity.php:185`
- Сигнатура: `function getParamByDb($tableName)`
- Описание: Получает, читает или вычисляет данные `param by db` в рамках этого метода класса `fan\app\__tools\main\create_entity`.

### `fan\app\__tools\main\create_entity::getFields`

- Расположение: `_project/app/__tools/main/create_entity.php:216`
- Сигнатура: `function getFields(string $tableName)`
- Описание: Получает, читает или вычисляет данные `fields` в рамках этого метода класса `fan\app\__tools\main\create_entity`.

## `_project/app/__tools/main/db_up/db_up.php`

### `fan\app\__tools\main\db_up::init`

- Расположение: `_project/app/__tools/main/db_up/db_up.php:15`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__tools\main\db_up`.

## `_project/app/__tools/main/db_up/scenario.php`

### `fan\app\__tools\main\scenario::__construct`

- Расположение: `_project/app/__tools/main/db_up/scenario.php:53`
- Сигнатура: `function __construct($file, $dumpdir = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\app\__tools\main\scenario`.

### `fan\app\__tools\main\scenario::__call`

- Расположение: `_project/app/__tools/main/db_up/scenario.php:72`
- Сигнатура: `function __call($m, $a)`
- Описание: Реализует магическое поведение PHP для этого метода класса `fan\app\__tools\main\scenario`.

### `fan\app\__tools\main\scenario::isSuccess`

- Расположение: `_project/app/__tools/main/db_up/scenario.php:81`
- Сигнатура: `function isSuccess()`
- Описание: Проверяет условие или валидирует данные `success` и возвращает результат либо выбрасывает исключение.

### `fan\app\__tools\main\scenario::get_next`

- Расположение: `_project/app/__tools/main/db_up/scenario.php:90`
- Сигнатура: `function get_next()`
- Описание: Получает, читает или вычисляет данные `next` в рамках этого метода класса `fan\app\__tools\main\scenario`.

### `fan\app\__tools\main\scenario::parse_scenario`

- Расположение: `_project/app/__tools/main/db_up/scenario.php:107`
- Сигнатура: `function parse_scenario()`
- Описание: Создает, разбирает, форматирует или конвертирует данные `scenario` для этого метода класса `fan\app\__tools\main\scenario`.

### `fan\app\__tools\main\scenario::command_description`

- Расположение: `_project/app/__tools/main/db_up/scenario.php:139`
- Сигнатура: `function command_description($name)`
- Описание: Выполняет workflow-логику `command description`.

### `fan\app\__tools\main\scenario::command_connect`

- Расположение: `_project/app/__tools/main/db_up/scenario.php:150`
- Сигнатура: `function command_connect($name)`
- Описание: Выполняет workflow-логику `command connect`.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения

### `fan\app\__tools\main\scenario::command_commit`

- Расположение: `_project/app/__tools/main/db_up/scenario.php:166`
- Сигнатура: `function command_commit()`
- Описание: Выполняет workflow-логику `command commit`.

### `fan\app\__tools\main\scenario::command_clear_tables`

- Расположение: `_project/app/__tools/main/db_up/scenario.php:176`
- Сигнатура: `function command_clear_tables()`
- Описание: Выполняет workflow-логику `command clear tables`.
- Побочные эффекты: выполняет database операции

### `fan\app\__tools\main\scenario::command_clear_fk`

- Расположение: `_project/app/__tools/main/db_up/scenario.php:193`
- Сигнатура: `function command_clear_fk()`
- Описание: Выполняет workflow-логику `command clear fk`.

### `fan\app\__tools\main\scenario::command_sql_file`

- Расположение: `_project/app/__tools/main/db_up/scenario.php:208`
- Сигнатура: `function command_sql_file($file)`
- Описание: Выполняет workflow-логику `command sql file`.
- Побочные эффекты: может выбрасывать исключения; выполняет database операции

### `fan\app\__tools\main\scenario::command_sql_query`

- Расположение: `_project/app/__tools/main/db_up/scenario.php:249`
- Сигнатура: `function command_sql_query($query)`
- Описание: Выполняет workflow-логику `command sql query`.
- Побочные эффекты: выполняет database операции

### `fan\app\__tools\main\scenario::execute`

- Расположение: `_project/app/__tools/main/db_up/scenario.php:263`
- Сигнатура: `function execute($query, $param = [])`
- Описание: Выполняет workflow-логику `execute`.
- Побочные эффекты: выполняет database операции

### `fan\app\__tools\main\scenario::check_sql_error`

- Расположение: `_project/app/__tools/main/db_up/scenario.php:274`
- Сигнатура: `function check_sql_error()`
- Описание: Проверяет условие или валидирует данные `sql error` и возвращает результат либо выбрасывает исключение.
- Побочные эффекты: может выбрасывать исключения

### `fan\app\__tools\main\scenario::get_table_list`

- Расположение: `_project/app/__tools/main/db_up/scenario.php:288`
- Сигнатура: `function get_table_list($show = '')`
- Описание: Получает, читает или вычисляет данные `table list` в рамках этого метода класса `fan\app\__tools\main\scenario`.

### `fan\app\__tools\main\scenario::clear_fk`

- Расположение: `_project/app/__tools/main/db_up/scenario.php:307`
- Сигнатура: `function clear_fk($tableList)`
- Описание: Удаляет или сбрасывает состояние `fk` для этого метода класса `fan\app\__tools\main\scenario`.
- Побочные эффекты: выполняет database операции

## `_project/app/__tools/main/db_up/scenario_processing.php`

### `fan\app\__tools\main\scenario_processing::init`

- Расположение: `_project/app/__tools/main/db_up/scenario_processing.php:20`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__tools\main\scenario_processing`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `fan\app\__tools\main\scenario_processing::perform_db`

- Расположение: `_project/app/__tools/main/db_up/scenario_processing.php:48`
- Сигнатура: `function perform_db()`
- Описание: Выполняет workflow-логику `perform db`.

## `_project/app/__tools/main/error403.php`

### `fan\app\__tools\main\error403::init`

- Расположение: `_project/app/__tools/main/error403.php:15`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__tools\main\error403`.

## `_project/app/__tools/main/error404.php`

### `fan\app\__tools\main\error404::init`

- Расположение: `_project/app/__tools/main/error404.php:15`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__tools\main\error404`.

## `_project/app/__tools/main/index.php`

### `fan\app\__tools\main\index::init`

- Расположение: `_project/app/__tools/main/index.php:26`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__tools\main\index`.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_project/app/__tools/main/request_password.php`

### `fan\app\__tools\main\request_password::init`

- Расположение: `_project/app/__tools/main/request_password.php:32`
- Сигнатура: `function init ()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__tools\main\request_password`.

### `fan\app\__tools\main\request_password::checkPassword`

- Расположение: `_project/app/__tools/main/request_password.php:45`
- Сигнатура: `function checkPassword(mixed $value, array $data)`
- Описание: Проверяет условие или валидирует данные `password` и возвращает результат либо выбрасывает исключение.

### `fan\app\__tools\main\request_password::onSubmit`

- Расположение: `_project/app/__tools/main/request_password.php:56`
- Сигнатура: `function onSubmit()`
- Описание: Выполняет workflow-логику `on submit`.

## `_project/app/__tools/main/upgrade_blocks.php`

### `fan\app\__tools\main\upgrade_blocks::init`

- Расположение: `_project/app/__tools/main/upgrade_blocks.php:96`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\__tools\main\upgrade_blocks`.

### `fan\app\__tools\main\upgrade_blocks::_addNamespase`

- Расположение: `_project/app/__tools/main/upgrade_blocks.php:144`
- Сигнатура: `function _addNamespase(array $data, string $nsPref)`
- Описание: Выполняет workflow-логику `add namespase`.
- Побочные эффекты: может выбрасывать исключения

### `fan\app\__tools\main\upgrade_blocks::_setExtends`

- Расположение: `_project/app/__tools/main/upgrade_blocks.php:181`
- Сигнатура: `function _setExtends()`
- Описание: Выполняет workflow-логику `set extends`.
- Побочные эффекты: может выбрасывать исключения

### `fan\app\__tools\main\upgrade_blocks::_setServiceCalls`

- Расположение: `_project/app/__tools/main/upgrade_blocks.php:213`
- Сигнатура: `function _setServiceCalls()`
- Описание: Выполняет workflow-логику `set service calls`.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения

### `fan\app\__tools\main\upgrade_blocks::_setEntityOperations`

- Расположение: `_project/app/__tools/main/upgrade_blocks.php:244`
- Сигнатура: `function _setEntityOperations()`
- Описание: Выполняет workflow-логику `set entity operations`.
- Побочные эффекты: использует service locator/helpers фреймворка; может выбрасывать исключения

### `fan\app\__tools\main\upgrade_blocks::_directReplacement`

- Расположение: `_project/app/__tools/main/upgrade_blocks.php:327`
- Сигнатура: `function _directReplacement()`
- Описание: Выполняет workflow-логику `direct replacement`.

### `fan\app\__tools\main\upgrade_blocks::_setFinalComent`

- Расположение: `_project/app/__tools/main/upgrade_blocks.php:359`
- Сигнатура: `function _setFinalComent($data, $nsPref)`
- Описание: Выполняет workflow-логику `set final coment`.
- Побочные эффекты: может выбрасывать исключения

### `fan\app\__tools\main\upgrade_blocks::_getFileList`

- Расположение: `_project/app/__tools/main/upgrade_blocks.php:400`
- Сигнатура: `function _getFileList($type)`
- Описание: Выполняет логику `get file list` и возвращает вычисленный результат.

### `fan\app\__tools\main\upgrade_blocks::_getContent`

- Расположение: `_project/app/__tools/main/upgrade_blocks.php:415`
- Сигнатура: `function _getContent($type)`
- Описание: Выполняет логику `get content` и возвращает вычисленный результат.

### `fan\app\__tools\main\upgrade_blocks::_makeFileList`

- Расположение: `_project/app/__tools/main/upgrade_blocks.php:432`
- Сигнатура: `function _makeFileList(&$dest, string $type, string $basePath)`
- Описание: Выполняет логику `make file list` и возвращает вычисленный результат.
- Побочные эффекты: может выбрасывать исключения

### `fan\app\__tools\main\upgrade_blocks::_saveFiles`

- Расположение: `_project/app/__tools/main/upgrade_blocks.php:465`
- Сигнатура: `function _saveFiles()`
- Описание: Выполняет логику `save files` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой

### `fan\app\__tools\main\upgrade_blocks::_checkType`

- Расположение: `_project/app/__tools/main/upgrade_blocks.php:487`
- Сигнатура: `function _checkType(string $name, string $type)`
- Описание: Выполняет логику `check type` и возвращает вычисленный результат.

## `_project/app/frontend/design/footer.php`

### `fan\app\frontend\design\footer::init`

- Расположение: `_project/app/frontend/design/footer.php:15`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\frontend\design\footer`.

## `_project/app/frontend/main/error404.php`

### `fan\app\frontend\main\error404::init`

- Расположение: `_project/app/frontend/main/error404.php:15`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\frontend\main\error404`.

## `_project/app/frontend/main/index.php`

### `fan\app\frontend\main\index::init`

- Расположение: `_project/app/frontend/main/index.php:15`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\frontend\main\index`.

## `_project/app/frontend/main/test.php`

### `fan\app\frontend\main\index::init`

- Расположение: `_project/app/frontend/main/test.php:15`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\app\frontend\main\index`.

## `_project/block/carcass/common.php`

### `fan\project\block\carcass\common::setMessageBefore`

- Расположение: `_project/block/carcass/common.php:32`
- Сигнатура: `function setMessageBefore(string $mess, int|float $position = 1, string $type = 'error')`
- Описание: Устанавливает, добавляет или сохраняет данные `message before` в рамках этого метода класса `fan\project\block\carcass\common`.

### `fan\project\block\carcass\common::setMessageAfter`

- Расположение: `_project/block/carcass/common.php:52`
- Сигнатура: `function setMessageAfter(string $mess, int|float $position = 1, string $type = 'error')`
- Описание: Устанавливает, добавляет или сохраняет данные `message after` в рамках этого метода класса `fan\project\block\carcass\common`.

## `_project/block/common/html_pager_quantifier.php`

### `fan\project\block\common\html_pager_quantifier::init`

- Расположение: `_project/block/common/html_pager_quantifier.php:26`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\project\block\common\html_pager_quantifier`.
- Побочные эффекты: читает PHP superglobals

### `fan\project\block\common\html_pager_quantifier::getFieldValuesFromRequest`

- Расположение: `_project/block/common/html_pager_quantifier.php:45`
- Сигнатура: `function getFieldValuesFromRequest()`
- Описание: Получает, читает или вычисляет данные `field values from request` в рамках этого метода класса `fan\project\block\common\html_pager_quantifier`.

### `fan\project\block\common\html_pager_quantifier::onSubmit`

- Расположение: `_project/block/common/html_pager_quantifier.php:60`
- Сигнатура: `function onSubmit()`
- Описание: Выполняет workflow-логику `on submit`.

### `fan\project\block\common\html_pager_quantifier::getPagerSession`

- Расположение: `_project/block/common/html_pager_quantifier.php:78`
- Сигнатура: `function getPagerSession()`
- Описание: Получает, читает или вычисляет данные `pager session` в рамках этого метода класса `fan\project\block\common\html_pager_quantifier`.

### `fan\project\block\common\html_pager_quantifier::getQuantifier`

- Расположение: `_project/block/common/html_pager_quantifier.php:96`
- Сигнатура: `function getQuantifier($getFromRequest = false)`
- Описание: Получает, читает или вычисляет данные `quantifier` в рамках этого метода класса `fan\project\block\common\html_pager_quantifier`.
- Побочные эффекты: использует service locator/helpers фреймворка

## `_project/block/error/error403.php`

### `fan\project\block\error\error403::init`

- Расположение: `_project/block/error/error403.php:26`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\project\block\error\error403`.

## `_project/block/error/error404.php`

### `fan\project\block\error\error404::init`

- Расположение: `_project/block/error/error404.php:26`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\project\block\error\error404`.

## `_project/block/error/error500.php`

### `fan\project\block\error\error500::init`

- Расположение: `_project/block/error/error500.php:26`
- Сигнатура: `function init()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\project\block\error\error500`.

## `_project/block/form/filter.php`

### `fan\project\block\form\filter::init`

- Расположение: `_project/block/form/filter.php:27`
- Сигнатура: `function init ()`
- Описание: Запускает или обрабатывает workflow `инициализацию` для этого метода класса `fan\project\block\form\filter`.

## `_project/block/form/injector.php`

### `fan\project\block\form\injector::_parseForm`

- Расположение: `_project/block/form/injector.php:31`
- Сигнатура: `function _parseForm($parceEmpty = true, $parsingCondition = null, $allowTransfer = null)`
- Описание: Выполняет логику `parse form` и возвращает вычисленный результат.

## `_project/block/root/html5.php`

### `fan\project\block\root\html5::setMetaByDb`

- Расположение: `_project/block/root/html5.php:17`
- Сигнатура: `function setMetaByDb($idMetaData)`
- Описание: Устанавливает, добавляет или сохраняет данные `meta by db` в рамках этого метода класса `fan\project\block\root\html5`.
- Побочные эффекты: использует service locator/helpers фреймворка

## `cli/install.php`

### `makeReqDir`

- Расположение: `cli/install.php:61`
- Сигнатура: `function makeReqDir(array $struct, $parentDir, int|float $level = 0)`
- Описание: Builds req dir for the framework helper.
- Параметры: array $struct Input value for the struct argument.; mixed $parentDir Input value for the parent dir argument.; int|float $level Input value for the level argument.
- Возвращает: void No value is returned.

### `makeApacheConf`

- Расположение: `cli/install.php:84`
- Сигнатура: `function makeApacheConf($rootDir, string $domain)`
- Описание: Builds apache conf for the framework helper.
- Параметры: mixed $rootDir Input value for the root dir argument.; string $domain Input value for the domain argument.
- Возвращает: mixed Returns the value produced by the operation.
- Побочные эффекты: работает с файловой системой

### `checkDir`

- Расположение: `cli/install.php:140`
- Сигнатура: `function checkDir(string $dir, int|float $mode, bool $wrRequired = true)`
- Описание: Evaluates dir and reports the outcome.
- Параметры: string $dir Input value for the dir argument.; int|float $mode Input value for the mode argument.; bool $wrRequired Input value for the wr required argument.
- Возвращает: mixed Indicates whether the requested condition is satisfied.
- Побочные эффекты: работает с файловой системой

## `cli/timer/error_email.php`

### `fan\project\cli\timer\error_email::sendPacketEmais`

- Расположение: `cli/timer/error_email.php:15`
- Сигнатура: `function sendPacketEmais()`
- Описание: Выполняет workflow-логику `send packet emais`.
- Побочные эффекты: использует service locator/helpers фреймворка

## `cli/timer/send_email.php`

### `fan\project\cli\timer\send_email::sendEmail`

- Расположение: `cli/timer/send_email.php:22`
- Сигнатура: `function sendEmail($subject, $message, $mailTo, $nameTo, $mailCC)`
- Описание: Выполняет workflow-логику `send email`.
- Побочные эффекты: использует service locator/helpers фреймворка

## `htdocs/install/incl/base.php`

### `base::__construct`

- Расположение: `htdocs/install/incl/base.php:37`
- Сигнатура: `function __construct()`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `base`.

### `base::run`

- Расположение: `htdocs/install/incl/base.php:49`
- Сигнатура: `function run(string $method = 'runCheck')`
- Описание: Запускает или обрабатывает workflow `выполнение` для этого метода класса `base`.

### `base::_setLocale`

- Расположение: `htdocs/install/incl/base.php:68`
- Сигнатура: `function _setLocale(bool $forse = false)`
- Описание: Выполняет логику `set locale` и возвращает вычисленный результат.
- Побочные эффекты: читает PHP superglobals

### `base::_parseTemplate`

- Расположение: `htdocs/install/incl/base.php:101`
- Сигнатура: `function _parseTemplate(string $tplName)`
- Описание: Выполняет логику `parse template` и возвращает вычисленный результат.

## `htdocs/install/incl/check_configuration.php`

### `check_configuration::runCheck`

- Расположение: `htdocs/install/incl/check_configuration.php:27`
- Сигнатура: `function runCheck()`
- Описание: Запускает или обрабатывает workflow `check` для этого метода класса `check_configuration`.

### `check_configuration::_checkPhpVersion`

- Расположение: `htdocs/install/incl/check_configuration.php:42`
- Сигнатура: `function _checkPhpVersion()`
- Описание: Выполняет логику `check php version` и возвращает вычисленный результат.

### `check_configuration::_checkPhpModules`

- Расположение: `htdocs/install/incl/check_configuration.php:57`
- Сигнатура: `function _checkPhpModules()`
- Описание: Выполняет логику `check php modules` и возвращает вычисленный результат.

## `htdocs/install/incl/check_directories.php`

### `check_directories::runCheck`

- Расположение: `htdocs/install/incl/check_directories.php:86`
- Сигнатура: `function runCheck()`
- Описание: Запускает или обрабатывает workflow `check` для этого метода класса `check_directories`.

### `check_directories::_checkBaseDirectories`

- Расположение: `htdocs/install/incl/check_directories.php:102`
- Сигнатура: `function _checkBaseDirectories()`
- Описание: Выполняет логику `check base directories` и возвращает вычисленный результат.
- Побочные эффекты: читает PHP superglobals

### `check_directories::_checkLogDirectories`

- Расположение: `htdocs/install/incl/check_directories.php:149`
- Сигнатура: `function _checkLogDirectories()`
- Описание: Выполняет логику `check log directories` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой

### `check_directories::_checkCacheDirectories`

- Расположение: `htdocs/install/incl/check_directories.php:203`
- Сигнатура: `function _checkCacheDirectories()`
- Описание: Выполняет логику `check cache directories` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой

### `check_directories::_findIndexDir`

- Расположение: `htdocs/install/incl/check_directories.php:269`
- Сигнатура: `function _findIndexDir()`
- Описание: Выполняет логику `find index dir` и возвращает вычисленный результат.
- Побочные эффекты: читает PHP superglobals

### `check_directories::_checkIndexFile`

- Расположение: `htdocs/install/incl/check_directories.php:298`
- Сигнатура: `function _checkIndexFile(string $dir)`
- Описание: Выполняет логику `check index file` и возвращает вычисленный результат.

### `check_directories::_isUnderDir`

- Расположение: `htdocs/install/incl/check_directories.php:334`
- Сигнатура: `function _isUnderDir(string $dir)`
- Описание: Выполняет логику `is under dir` и возвращает вычисленный результат.

### `check_directories::_adaptPath`

- Расположение: `htdocs/install/incl/check_directories.php:348`
- Сигнатура: `function _adaptPath(string $path)`
- Описание: Выполняет логику `adapt path` и возвращает вычисленный результат.

### `check_directories::_replacePlaceholder`

- Расположение: `htdocs/install/incl/check_directories.php:360`
- Сигнатура: `function _replacePlaceholder(string $path)`
- Описание: Выполняет логику `replace placeholder` и возвращает вычисленный результат.

### `check_directories::_setConst`

- Расположение: `htdocs/install/incl/check_directories.php:373`
- Сигнатура: `function _setConst()`
- Описание: Выполняет логику `set const` и возвращает вычисленный результат.

## `htdocs/install/incl/fan_version.php`

### `fan_version::runCheck`

- Расположение: `htdocs/install/incl/fan_version.php:27`
- Сигнатура: `function runCheck()`
- Описание: Запускает или обрабатывает workflow `check` для этого метода класса `fan_version`.

### `fan_version::_showFanVersion`

- Расположение: `htdocs/install/incl/fan_version.php:38`
- Сигнатура: `function _showFanVersion()`
- Описание: Выполняет логику `show fan version` и возвращает вычисленный результат.
- Побочные эффекты: читает PHP superglobals

## `tools/generate_function_catalog.php`

### `collectFunctions`

- Расположение: `tools/generate_function_catalog.php:55`
- Сигнатура: `function collectFunctions(string $file, string $root): array`
- Описание: Runs the collect functions operation and returns its result.
- Параметры: string $file File path or file descriptor handled by the operation.; string $root Input value for the root argument.
- Возвращает: array Returns the structured data produced by the operation.

### `renderCatalog`

- Расположение: `tools/generate_function_catalog.php:150`
- Сигнатура: `function renderCatalog(array $entries): string`
- Описание: Runs the render catalog operation and returns its result.
- Параметры: array $entries Input value for the entries argument.
- Возвращает: string Returns the string representation produced by the operation.

### `readNamespace`

- Расположение: `tools/generate_function_catalog.php:203`
- Сигнатура: `function readNamespace(array $tokens, int $offset): string`
- Описание: Retrieves namespace used by the framework helper.
- Параметры: array $tokens Input value for the tokens argument.; int $offset Input value for the offset argument.
- Возвращает: string Returns the string representation produced by the operation.

### `readNamedDeclaration`

- Расположение: `tools/generate_function_catalog.php:226`
- Сигнатура: `function readNamedDeclaration(array $tokens, int $offset): ?string`
- Описание: Retrieves named declaration used by the framework helper.
- Параметры: array $tokens Input value for the tokens argument.; int $offset Input value for the offset argument.
- Возвращает: ?string Returns the string representation produced by the operation.

### `isAnonymousClass`

- Расположение: `tools/generate_function_catalog.php:246`
- Сигнатура: `function isAnonymousClass(array $tokens, int $classIndex): bool`
- Описание: Evaluates anonymous class and reports the outcome.
- Параметры: array $tokens Input value for the tokens argument.; int $classIndex Input value for the class index argument.
- Возвращает: bool Indicates whether the requested condition is satisfied.

### `isClosure`

- Расположение: `tools/generate_function_catalog.php:266`
- Сигнатура: `function isClosure(array $tokens, int $functionIndex): bool`
- Описание: Evaluates closure and reports the outcome.
- Параметры: array $tokens Input value for the tokens argument.; int $functionIndex Input value for the function index argument.
- Возвращает: bool Indicates whether the requested condition is satisfied.

### `readFunctionSignatureAndBody`

- Расположение: `tools/generate_function_catalog.php:280`
- Сигнатура: `function readFunctionSignatureAndBody(array $tokens, int $functionIndex): array`
- Описание: Retrieves function signature and body used by the framework helper.
- Параметры: array $tokens Input value for the tokens argument.; int $functionIndex Input value for the function index argument.
- Возвращает: array Returns the structured data produced by the operation.

### `readPreviousDocBlock`

- Расположение: `tools/generate_function_catalog.php:326`
- Сигнатура: `function readPreviousDocBlock(array $tokens, int $index): string`
- Описание: Retrieves previous doc block used by the framework helper.
- Параметры: array $tokens Input value for the tokens argument.; int $index Input value for the index argument.
- Возвращает: string Returns the string representation produced by the operation.

### `readVisibility`

- Расположение: `tools/generate_function_catalog.php:346`
- Сигнатура: `function readVisibility(array $tokens, int $functionIndex): string`
- Описание: Retrieves visibility used by the framework helper.
- Параметры: array $tokens Input value for the tokens argument.; int $functionIndex Input value for the function index argument.
- Возвращает: string Returns the string representation produced by the operation.

### `parseDocBlock`

- Расположение: `tools/generate_function_catalog.php:370`
- Сигнатура: `function parseDocBlock(string $docBlock): array`
- Описание: Transforms doc block between supported representations.
- Параметры: string $docBlock Input value for the doc block argument.
- Возвращает: array Returns the structured data produced by the operation.

### `inferSummary`

- Расположение: `tools/generate_function_catalog.php:421`
- Сигнатура: `function inferSummary(string $name, ?string $className, string $docBlock, string $body): string`
- Описание: Runs the infer summary operation and returns its result.
- Параметры: string $name Logical name of the value or component being addressed.; ?string $className Input value for the class name argument.; string $docBlock Input value for the doc block argument.; string $body Input value for the body argument.
- Возвращает: string Returns the string representation produced by the operation.

### `inferEffects`

- Расположение: `tools/generate_function_catalog.php:471`
- Сигнатура: `function inferEffects(string $body): array`
- Описание: Runs the infer effects operation and returns its result.
- Параметры: string $body Input value for the body argument.
- Возвращает: array Returns the structured data produced by the operation.
- Побочные эффекты: работает с файловой системой; меняет HTTP/session состояние; может выбрасывать исключения; логирует или сообщает об ошибках; сериализует или десериализует данные; выполняет database операции; использует внешнюю коммуникацию

### `normalizeWhitespace`

- Расположение: `tools/generate_function_catalog.php:502`
- Сигнатура: `function normalizeWhitespace(string $value): string`
- Описание: Runs the normalize whitespace operation and returns its result.
- Параметры: string $value Value that should be applied or transformed.
- Возвращает: string Returns the string representation produced by the operation.

### `readableFunctionTarget`

- Расположение: `tools/generate_function_catalog.php:514`
- Сигнатура: `function readableFunctionTarget(string $name): string`
- Описание: Retrieves able function target used by the framework helper.
- Параметры: string $name Logical name of the value or component being addressed.
- Возвращает: string Returns the string representation produced by the operation.

## `tools/generate_function_docblocks.php`

### `collectPhpFiles`

- Расположение: `tools/generate_function_docblocks.php:55`
- Сигнатура: `function collectPhpFiles(string $root, array $sourceRoots, array $excludedPrefixes): array`
- Описание: Runs the collect php files operation and returns its result.
- Параметры: string $root Input value for the root argument.; array $sourceRoots Input value for the source roots argument.; array $excludedPrefixes Input value for the excluded prefixes argument.
- Возвращает: array Returns the structured data produced by the operation.

### `collectDocblockReplacements`

- Расположение: `tools/generate_function_docblocks.php:91`
- Сигнатура: `function collectDocblockReplacements(string $code): array`
- Описание: Runs the collect docblock replacements operation and returns its result.
- Параметры: string $code Input value for the code argument.
- Возвращает: array Returns the structured data produced by the operation.

### `findDeclarationStartIndex`

- Расположение: `tools/generate_function_docblocks.php:172`
- Сигнатура: `function findDeclarationStartIndex(array $tokens, int $functionIndex): int`
- Описание: Retrieves declaration start index used by the framework helper.
- Параметры: array $tokens Input value for the tokens argument.; int $functionIndex Input value for the function index argument.
- Возвращает: int Returns the numeric result produced by the operation.

### `findExistingDocBlock`

- Расположение: `tools/generate_function_docblocks.php:198`
- Сигнатура: `function findExistingDocBlock(string $code, array $tokens, int $declarationStartIndex): ?array`
- Описание: Retrieves existing doc block used by the framework helper.
- Параметры: string $code Input value for the code argument.; array $tokens Input value for the tokens argument.; int $declarationStartIndex Input value for the declaration start index argument.
- Возвращает: ?array Returns the structured data produced by the operation.

### `buildDocBlock`

- Расположение: `tools/generate_function_docblocks.php:232`
- Сигнатура: `function buildDocBlock(string $name, ?string $className, string $signature, string $body, string $indent, string $oldDocBlock): string`
- Описание: Builds doc block for the framework helper.
- Параметры: string $name Logical name of the value or component being addressed.; ?string $className Input value for the class name argument.; string $signature Input value for the signature argument.; string $body Input value for the body argument.; string $indent Input value for the indent argument.; string $oldDocBlock Input value for the old doc block argument.
- Возвращает: string Returns the string representation produced by the operation.

### `inferSummary`

- Расположение: `tools/generate_function_docblocks.php:282`
- Сигнатура: `function inferSummary(string $name, ?string $className, string $body): string`
- Описание: Runs the infer summary operation and returns its result.
- Параметры: string $name Logical name of the value or component being addressed.; ?string $className Input value for the class name argument.; string $body Input value for the body argument.
- Возвращает: string Returns the string representation produced by the operation.

### `readableFunctionTarget`

- Расположение: `tools/generate_function_docblocks.php:317`
- Сигнатура: `function readableFunctionTarget(string $name): string`
- Описание: Retrieves able function target used by the framework helper.
- Параметры: string $name Logical name of the value or component being addressed.
- Возвращает: string Returns the string representation produced by the operation.

### `extractParameters`

- Расположение: `tools/generate_function_docblocks.php:351`
- Сигнатура: `function extractParameters(string $signature): array`
- Описание: Runs the extract parameters operation and returns its result.
- Параметры: string $signature Input value for the signature argument.
- Возвращает: array Returns the structured data produced by the operation.

### `splitTopLevel`

- Расположение: `tools/generate_function_docblocks.php:382`
- Сигнатура: `function splitTopLevel(string $source): array`
- Описание: Runs the split top level operation and returns its result.
- Параметры: string $source Input value for the source argument.
- Возвращает: array Returns the structured data produced by the operation.

### `inferReturnType`

- Расположение: `tools/generate_function_docblocks.php:416`
- Сигнатура: `function inferReturnType(string $name, string $signature, string $body): ?string`
- Описание: Runs the infer return type operation and returns its result.
- Параметры: string $name Logical name of the value or component being addressed.; string $signature Input value for the signature argument.; string $body Input value for the body argument.
- Возвращает: ?string Returns the string representation produced by the operation.

### `normalizeType`

- Расположение: `tools/generate_function_docblocks.php:440`
- Сигнатура: `function normalizeType(string $type): string`
- Описание: Runs the normalize type operation and returns its result.
- Параметры: string $type Type discriminator that selects the required behavior.
- Возвращает: string Returns the string representation produced by the operation.

### `describeParameter`

- Расположение: `tools/generate_function_docblocks.php:454`
- Сигнатура: `function describeParameter(string $name): string`
- Описание: Runs the describe parameter operation and returns its result.
- Параметры: string $name Logical name of the value or component being addressed.
- Возвращает: string Returns the string representation produced by the operation.

### `describeReturn`

- Расположение: `tools/generate_function_docblocks.php:491`
- Сигнатура: `function describeReturn(string $name, string $type): string`
- Описание: Runs the describe return operation and returns its result.
- Параметры: string $name Logical name of the value or component being addressed.; string $type Type discriminator that selects the required behavior.
- Возвращает: string Returns the string representation produced by the operation.

### `extractPreservedTags`

- Расположение: `tools/generate_function_docblocks.php:522`
- Сигнатура: `function extractPreservedTags(string $docBlock): array`
- Описание: Runs the extract preserved tags operation and returns its result.
- Параметры: string $docBlock Input value for the doc block argument.
- Возвращает: array Returns the structured data produced by the operation.

### `readableIdentifier`

- Расположение: `tools/generate_function_docblocks.php:545`
- Сигнатура: `function readableIdentifier(string $identifier): string`
- Описание: Retrieves able identifier used by the framework helper.
- Параметры: string $identifier Input value for the identifier argument.
- Возвращает: string Returns the string representation produced by the operation.

### `returnsOnlyThis`

- Расположение: `tools/generate_function_docblocks.php:560`
- Сигнатура: `function returnsOnlyThis(string $body): bool`
- Описание: Runs the returns only this operation and returns its result.
- Параметры: string $body Input value for the body argument.
- Возвращает: bool Indicates whether the requested condition is satisfied.

### `isBooleanLikeName`

- Расположение: `tools/generate_function_docblocks.php:582`
- Сигнатура: `function isBooleanLikeName(string $name): bool`
- Описание: Evaluates boolean like name and reports the outcome.
- Параметры: string $name Logical name of the value or component being addressed.
- Возвращает: bool Indicates whether the requested condition is satisfied.

### `readNamespace`

- Расположение: `tools/generate_function_docblocks.php:595`
- Сигнатура: `function readNamespace(array $tokens, int $offset): string`
- Описание: Retrieves namespace used by the framework helper.
- Параметры: array $tokens Input value for the tokens argument.; int $offset Input value for the offset argument.
- Возвращает: string Returns the string representation produced by the operation.

### `readNamedDeclaration`

- Расположение: `tools/generate_function_docblocks.php:618`
- Сигнатура: `function readNamedDeclaration(array $tokens, int $offset): ?string`
- Описание: Retrieves named declaration used by the framework helper.
- Параметры: array $tokens Input value for the tokens argument.; int $offset Input value for the offset argument.
- Возвращает: ?string Returns the string representation produced by the operation.

### `isAnonymousClass`

- Расположение: `tools/generate_function_docblocks.php:638`
- Сигнатура: `function isAnonymousClass(array $tokens, int $classIndex): bool`
- Описание: Evaluates anonymous class and reports the outcome.
- Параметры: array $tokens Input value for the tokens argument.; int $classIndex Input value for the class index argument.
- Возвращает: bool Indicates whether the requested condition is satisfied.

### `isClosure`

- Расположение: `tools/generate_function_docblocks.php:658`
- Сигнатура: `function isClosure(array $tokens, int $functionIndex): bool`
- Описание: Evaluates closure and reports the outcome.
- Параметры: array $tokens Input value for the tokens argument.; int $functionIndex Input value for the function index argument.
- Возвращает: bool Indicates whether the requested condition is satisfied.

### `readFunctionSignatureAndBody`

- Расположение: `tools/generate_function_docblocks.php:671`
- Сигнатура: `function readFunctionSignatureAndBody(array $tokens, int $functionIndex): array`
- Описание: Retrieves function signature and body used by the framework helper.
- Параметры: array $tokens Input value for the tokens argument.; int $functionIndex Input value for the function index argument.
- Возвращает: array Returns the structured data produced by the operation.

### `readLineIndent`

- Расположение: `tools/generate_function_docblocks.php:715`
- Сигнатура: `function readLineIndent(string $code, int $offset): string`
- Описание: Retrieves line indent used by the framework helper.
- Параметры: string $code Input value for the code argument.; int $offset Input value for the offset argument.
- Возвращает: string Returns the string representation produced by the operation.

### `readLineStartOffset`

- Расположение: `tools/generate_function_docblocks.php:731`
- Сигнатура: `function readLineStartOffset(string $code, int $offset): int`
- Описание: Retrieves line start offset used by the framework helper.
- Параметры: string $code Input value for the code argument.; int $offset Input value for the offset argument.
- Возвращает: int Returns the numeric result produced by the operation.

## `unit/_core/CoreSourceInventoryTest.php`

### `CoreSourceInventoryTest::getDiscoveredSourceFiles`

- Расположение: `unit/_core/CoreSourceInventoryTest.php:9`
- Сигнатура: `function getDiscoveredSourceFiles()`
- Описание: Получает, читает или вычисляет данные `discovered source files` в рамках этого метода класса `CoreSourceInventoryTest`.
- Побочные эффекты: меняет HTTP/session состояние

### `CoreSourceInventoryTest::getCreatedTests`

- Расположение: `unit/_core/CoreSourceInventoryTest.php:243`
- Сигнатура: `function getCreatedTests()`
- Описание: Получает, читает или вычисляет данные `created tests` в рамках этого метода класса `CoreSourceInventoryTest`.
- Побочные эффекты: меняет HTTP/session состояние

### `CoreSourceInventoryTest::getSkippedSourceFiles`

- Расположение: `unit/_core/CoreSourceInventoryTest.php:455`
- Сигнатура: `function getSkippedSourceFiles()`
- Описание: Получает, читает или вычисляет данные `skipped source files` в рамках этого метода класса `CoreSourceInventoryTest`.

### `CoreSourceInventoryTest::testEveryCorePhpSourceFileIsClassified`

- Расположение: `unit/_core/CoreSourceInventoryTest.php:488`
- Сигнатура: `function testEveryCorePhpSourceFileIsClassified()`
- Описание: Выполняет workflow-логику `test every core php source file is classified`.

### `CoreSourceInventoryTest::testCreatedTestFilesExistInPreservedUnitTree`

- Расположение: `unit/_core/CoreSourceInventoryTest.php:502`
- Сигнатура: `function testCreatedTestFilesExistInPreservedUnitTree()`
- Описание: Выполняет workflow-логику `test created test files exist in preserved unit tree`.

### `CoreSourceInventoryTest::testSkippedFilesHaveExplicitReasons`

- Расположение: `unit/_core/CoreSourceInventoryTest.php:516`
- Сигнатура: `function testSkippedFilesHaveExplicitReasons()`
- Описание: Выполняет workflow-логику `test skipped files have explicit reasons`.

## `unit/_core/FunctionsServiceContainerTest.php`

### `FunctionsServiceContainerTest::setUp`

- Расположение: `unit/_core/FunctionsServiceContainerTest.php:17`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `FunctionsServiceContainerTest`.

### `FunctionsServiceContainerTest::tearDown`

- Расположение: `unit/_core/FunctionsServiceContainerTest.php:27`
- Сигнатура: `function tearDown(): void`
- Описание: Выполняет workflow-логику `tear down`.

### `FunctionsServiceContainerTest::testDefaultContainerRegistersFirstServiceLayer`

- Расположение: `unit/_core/FunctionsServiceContainerTest.php:37`
- Сигнатура: `function testDefaultContainerRegistersFirstServiceLayer(): void`
- Описание: Выполняет workflow-логику `test default container registers first service layer`.

### `FunctionsServiceContainerTest::testServiceUsesExplicitContainerDependency`

- Расположение: `unit/_core/FunctionsServiceContainerTest.php:51`
- Сигнатура: `function testServiceUsesExplicitContainerDependency(): void`
- Описание: Выполняет workflow-логику `test service uses explicit container dependency`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `FunctionsServiceContainerTest::testServicePassesArgumentsToContainerFactory`

- Расположение: `unit/_core/FunctionsServiceContainerTest.php:67`
- Сигнатура: `function testServicePassesArgumentsToContainerFactory(): void`
- Описание: Выполняет workflow-логику `test service passes arguments to container factory`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `FunctionsServiceContainerTest::testUnknownServiceStillReturnsNull`

- Расположение: `unit/_core/FunctionsServiceContainerTest.php:88`
- Сигнатура: `function testUnknownServiceStillReturnsNull(): void`
- Описание: Выполняет workflow-логику `test unknown service still returns null`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `FunctionsServiceContainerTest::loadCoreFunctions`

- Расположение: `unit/_core/FunctionsServiceContainerTest.php:98`
- Сигнатура: `function loadCoreFunctions(): void`
- Описание: Получает, читает или вычисляет данные `core functions` в рамках этого метода класса `FunctionsServiceContainerTest`.

## `unit/_core/base/DataTest.php`

### `DataTest::setUp`

- Расположение: `unit/_core/base/DataTest.php:16`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `DataTest`.

### `DataTest::testConstructorGetAndToArraySupportNestedData`

- Расположение: `unit/_core/base/DataTest.php:26`
- Сигнатура: `function testConstructorGetAndToArraySupportNestedData()`
- Описание: Выполняет workflow-логику `test constructor get and to array support nested data`.

### `DataTest::testSetSupportsScalarNestedAndNoRewriteBranches`

- Расположение: `unit/_core/base/DataTest.php:54`
- Сигнатура: `function testSetSupportsScalarNestedAndNoRewriteBranches()`
- Описание: Выполняет workflow-логику `test set supports scalar nested and no rewrite branches`.

### `DataTest::testArrayAccessIteratorCountMagicUnsetAndStringConversion`

- Расположение: `unit/_core/base/DataTest.php:75`
- Сигнатура: `function testArrayAccessIteratorCountMagicUnsetAndStringConversion()`
- Описание: Выполняет workflow-логику `test array access iterator count magic unset and string conversion`.

### `DataTest::testSetterRestrictionsAllowOnlyRegisteredSetter`

- Расположение: `unit/_core/base/DataTest.php:103`
- Сигнатура: `function testSetterRestrictionsAllowOnlyRegisteredSetter()`
- Описание: Выполняет workflow-логику `test setter restrictions allow only registered setter`.

### `DataTest::testInvalidKeyTypeLogsAndNonMultilevelArrayPathUsesArrayHelper`

- Расположение: `unit/_core/base/DataTest.php:124`
- Сигнатура: `function testInvalidKeyTypeLogsAndNonMultilevelArrayPathUsesArrayHelper()`
- Описание: Выполняет workflow-логику `test invalid key type logs and non multilevel array path uses array helper`.

### `DataTest::testSerializationRestoresNestedParentPointersAndData`

- Расположение: `unit/_core/base/DataTest.php:142`
- Сигнатура: `function testSerializationRestoresNestedParentPointersAndData()`
- Описание: Выполняет workflow-логику `test serialization restores nested parent pointers and data`.
- Побочные эффекты: сериализует или десериализует данные

## `unit/_core/base/ExpressionEvaluatorTest.php`

### `ExpressionEvaluatorTest::testEvaluatesBooleanExpressionsWithResolvedIdentifiers`

- Расположение: `unit/_core/base/ExpressionEvaluatorTest.php:13`
- Сигнатура: `function testEvaluatesBooleanExpressionsWithResolvedIdentifiers()`
- Описание: Выполняет workflow-логику `test evaluates boolean expressions with resolved identifiers`.

### `ExpressionEvaluatorTest::testEvaluatesLiteralsComparisonsAndArithmetic`

- Расположение: `unit/_core/base/ExpressionEvaluatorTest.php:34`
- Сигнатура: `function testEvaluatesLiteralsComparisonsAndArithmetic()`
- Описание: Выполняет workflow-логику `test evaluates literals comparisons and arithmetic`.

### `ExpressionEvaluatorTest::testEvaluatesBooleanXorWithPhpIndependentPrecedence`

- Расположение: `unit/_core/base/ExpressionEvaluatorTest.php:52`
- Сигнатура: `function testEvaluatesBooleanXorWithPhpIndependentPrecedence()`
- Описание: Выполняет workflow-логику `test evaluates boolean xor with php independent precedence`.

### `ExpressionEvaluatorTest::testRejectsInvalidExpression`

- Расположение: `unit/_core/base/ExpressionEvaluatorTest.php:63`
- Сигнатура: `function testRejectsInvalidExpression()`
- Описание: Выполняет workflow-логику `test rejects invalid expression`.

## `unit/_core/base/TransferTest.php`

### `TransferTest::setUp`

- Расположение: `unit/_core/base/TransferTest.php:15`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `TransferTest`.

### `TransferTest::testInternalTransferStoresTypeUriQueryAndNoticeMessage`

- Расположение: `unit/_core/base/TransferTest.php:25`
- Сигнатура: `function testInternalTransferStoresTypeUriQueryAndNoticeMessage()`
- Описание: Выполняет workflow-логику `test internal transfer stores type uri query and notice message`.

### `TransferTest::testRequestFallsBackToUriWhenQueryStringIsEmpty`

- Расположение: `unit/_core/base/TransferTest.php:43`
- Сигнатура: `function testRequestFallsBackToUriWhenQueryStringIsEmpty()`
- Описание: Выполняет workflow-логику `test request falls back to uri when query string is empty`.

### `TransferTest::testShamTransferDoesNotShiftCurrentMatcher`

- Расположение: `unit/_core/base/TransferTest.php:55`
- Сигнатура: `function testShamTransferDoesNotShiftCurrentMatcher()`
- Описание: Выполняет workflow-логику `test sham transfer does not shift current matcher`.

### `TransferTest::testExplicitDatabaseOperationIsForwardedToDatabaseService`

- Расположение: `unit/_core/base/TransferTest.php:68`
- Сигнатура: `function testExplicitDatabaseOperationIsForwardedToDatabaseService()`
- Описание: Выполняет workflow-логику `test explicit database operation is forwarded to database service`.

## `unit/_core/base/meta/DelayedTest.php`

### `DelayedTest::testCallsObjectMethodWithArrayArguments`

- Расположение: `unit/_core/base/meta/DelayedTest.php:12`
- Сигнатура: `function testCallsObjectMethodWithArrayArguments()`
- Описание: Выполняет логику `test calls object method with array arguments` и возвращает вычисленный результат.

### `DelayedTest::build`

- Расположение: `unit/_core/base/meta/DelayedTest.php:23`
- Сигнатура: `function build($left, $right)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `build` для этого метода класса `DelayedTest`.

### `DelayedTest::testWrapsScalarArgumentIntoSingleArgumentList`

- Расположение: `unit/_core/base/meta/DelayedTest.php:39`
- Сигнатура: `function testWrapsScalarArgumentIntoSingleArgumentList()`
- Описание: Выполняет логику `test wraps scalar argument into single argument list` и возвращает вычисленный результат.

### `DelayedTest::repeat`

- Расположение: `unit/_core/base/meta/DelayedTest.php:49`
- Сигнатура: `function repeat($value)`
- Описание: Выполняет логику `repeat` и возвращает вычисленный результат.

### `DelayedTest::testNullArgumentsProduceEmptyArgumentList`

- Расположение: `unit/_core/base/meta/DelayedTest.php:65`
- Сигнатура: `function testNullArgumentsProduceEmptyArgumentList()`
- Описание: Выполняет логику `test null arguments produce empty argument list` и возвращает вычисленный результат.

### `DelayedTest::value`

- Расположение: `unit/_core/base/meta/DelayedTest.php:73`
- Сигнатура: `function value()`
- Описание: Выполняет логику `value` и возвращает вычисленный результат.

## `unit/_core/base/meta/MakerTest.php`

### `MetaMakerTest::tearDown`

- Расположение: `unit/_core/base/meta/MakerTest.php:18`
- Сигнатура: `function tearDown(): void`
- Описание: Выполняет workflow-логику `tear down`.

### `MetaMakerTest::handleError`

- Расположение: `unit/_core/base/meta/MakerTest.php:35`
- Сигнатура: `function handleError($type, $message)`
- Описание: Запускает или обрабатывает workflow `error` для этого метода класса `MetaMakerTest`.

### `MetaMakerTest::captureErrors`

- Расположение: `unit/_core/base/meta/MakerTest.php:46`
- Сигнатура: `function captureErrors(): void`
- Описание: Выполняет workflow-логику `capture errors`.

### `MetaMakerTest::makerWithSources`

- Расположение: `unit/_core/base/meta/MakerTest.php:58`
- Сигнатура: `function makerWithSources(): TestMetaMaker`
- Описание: Создает, разбирает, форматирует или конвертирует данные `r with sources` для этого метода класса `MetaMakerTest`.

### `MetaMakerTest::testAssembleTabMergesConfiguredSourcesInTabOrder`

- Расположение: `unit/_core/base/meta/MakerTest.php:91`
- Сигнатура: `function testAssembleTabMergesConfiguredSourcesInTabOrder()`
- Описание: Выполняет workflow-логику `test assemble tab merges configured sources in tab order`.

### `MetaMakerTest::testAssembleBlockBuildsRootRowAndLetsMakerReadAndWriteIt`

- Расположение: `unit/_core/base/meta/MakerTest.php:114`
- Сигнатура: `function testAssembleBlockBuildsRootRowAndLetsMakerReadAndWriteIt()`
- Описание: Выполняет workflow-логику `test assemble block builds root row and lets maker read and write it`.

### `MetaMakerTest::testAssembleOtherAndEmbeddedExcludeCurrentBlockAndMergeNamedBlocks`

- Расположение: `unit/_core/base/meta/MakerTest.php:147`
- Сигнатура: `function testAssembleOtherAndEmbeddedExcludeCurrentBlockAndMergeNamedBlocks()`
- Описание: Выполняет workflow-логику `test assemble other and embedded exclude current block and merge named blocks`.

### `MetaMakerTest::testMixSourceMetaCollectsFolderParentBlockAndContainerCommonData`

- Расположение: `unit/_core/base/meta/MakerTest.php:170`
- Сигнатура: `function testMixSourceMetaCollectsFolderParentBlockAndContainerCommonData()`
- Описание: Выполняет workflow-логику `test mix source meta collects folder parent block and container common data`.

### `MetaMakerTest::testMakeActiveMetaSupportsDelayedAndImmediateEvaluation`

- Расположение: `unit/_core/base/meta/MakerTest.php:189`
- Сигнатура: `function testMakeActiveMetaSupportsDelayedAndImmediateEvaluation()`
- Описание: Выполняет workflow-логику `test make active meta supports delayed and immediate evaluation`.

### `MetaMakerTest::testUnknownOrderKeyThrowsException`

- Расположение: `unit/_core/base/meta/MakerTest.php:205`
- Сигнатура: `function testUnknownOrderKeyThrowsException()`
- Описание: Выполняет workflow-логику `test unknown order key throws exception`.

## `unit/_core/base/meta/RowTest.php`

### `MetaRowTest::setUp`

- Расположение: `unit/_core/base/meta/RowTest.php:14`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `MetaRowTest`.

### `MetaRowTest::testConstructorConvertsNestedArraysToProjectMetaRows`

- Расположение: `unit/_core/base/meta/RowTest.php:24`
- Сигнатура: `function testConstructorConvertsNestedArraysToProjectMetaRows()`
- Описание: Выполняет workflow-логику `test constructor converts nested arrays to project meta rows`.

### `MetaRowTest::testMergeDataIsAllowedThroughMakerAndHonorsRewriteFlag`

- Расположение: `unit/_core/base/meta/RowTest.php:53`
- Сигнатура: `function testMergeDataIsAllowedThroughMakerAndHonorsRewriteFlag()`
- Описание: Выполняет workflow-логику `test merge data is allowed through maker and honors rewrite flag`.

### `MetaRowTest::testExternalMutationIsRejectedButMakerCanSetPathValues`

- Расположение: `unit/_core/base/meta/RowTest.php:80`
- Сигнатура: `function testExternalMutationIsRejectedButMakerCanSetPathValues()`
- Описание: Выполняет workflow-логику `test external mutation is rejected but maker can set path values`.

## `unit/_core/base/transfer/IntTest.php`

### `IntTest::testCompatibilityLoaderDefinesTransferIntClass`

- Расположение: `unit/_core/base/transfer/IntTest.php:14`
- Сигнатура: `function testCompatibilityLoaderDefinesTransferIntClass()`
- Описание: Выполняет workflow-логику `test compatibility loader defines transfer int class`.

## `unit/_core/base/transfer/OutTest.php`

### `OutTest::setUp`

- Расположение: `unit/_core/base/transfer/OutTest.php:14`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `OutTest`.

### `OutTest::testOuterTransferCommitsByDefault`

- Расположение: `unit/_core/base/transfer/OutTest.php:24`
- Сигнатура: `function testOuterTransferCommitsByDefault()`
- Описание: Выполняет workflow-логику `test outer transfer commits by default`.

### `OutTest::testOuterTransferPreservesExplicitRollback`

- Расположение: `unit/_core/base/transfer/OutTest.php:39`
- Сигнатура: `function testOuterTransferPreservesExplicitRollback()`
- Описание: Выполняет workflow-логику `test outer transfer preserves explicit rollback`.

## `unit/_core/base/transfer/ShamTest.php`

### `ShamTest::testCreatesShamTransferWithoutCurrentShift`

- Расположение: `unit/_core/base/transfer/ShamTest.php:14`
- Сигнатура: `function testCreatesShamTransferWithoutCurrentShift()`
- Описание: Выполняет workflow-логику `test creates sham transfer without current shift`.

## `unit/_core/base/transfer/TransferIntTest.php`

### `TransferIntTest::testCreatesInternalTransfer`

- Расположение: `unit/_core/base/transfer/TransferIntTest.php:14`
- Сигнатура: `function testCreatesInternalTransfer()`
- Описание: Выполняет workflow-логику `test creates internal transfer`.

## `unit/_core/block/BaseTest.php`

### `BaseTest::setUp`

- Расположение: `unit/_core/block/BaseTest.php:31`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `BaseTest`.

### `BaseTest::tearDown`

- Расположение: `unit/_core/block/BaseTest.php:43`
- Сигнатура: `function tearDown(): void`
- Описание: Выполняет workflow-логику `tear down`.

### `BaseTest::handleError`

- Расположение: `unit/_core/block/BaseTest.php:63`
- Сигнатура: `function handleError($type, $message, $fileName = null, $lineNum = null, $errContext = null)`
- Описание: Запускает или обрабатывает workflow `error` для этого метода класса `BaseTest`.

### `BaseTest::captureErrors`

- Расположение: `unit/_core/block/BaseTest.php:74`
- Сигнатура: `function captureErrors()`
- Описание: Выполняет workflow-логику `capture errors`.

### `BaseTest::fixturePath`

- Расположение: `unit/_core/block/BaseTest.php:91`
- Сигнатура: `function fixturePath($file)`
- Описание: Выполняет логику `fixture path` и возвращает вычисленный результат.

### `BaseTest::makeMetaRow`

- Расположение: `unit/_core/block/BaseTest.php:104`
- Сигнатура: `function makeMetaRow(TestableBaseBlock $block, array $data)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `meta row` для этого метода класса `BaseTest`.

### `BaseTest::testConstructorWiresCoreDependenciesWithoutFullConstruction`

- Расположение: `unit/_core/block/BaseTest.php:116`
- Сигнатура: `function testConstructorWiresCoreDependenciesWithoutFullConstruction()`
- Описание: Выполняет workflow-логику `test constructor wires core dependencies without full construction`.

### `BaseTest::testConstructorDoesNotSetCurrentBlockWhenBlockNameIsEmpty`

- Расположение: `unit/_core/block/BaseTest.php:140`
- Сигнатура: `function testConstructorDoesNotSetCurrentBlockWhenBlockNameIsEmpty()`
- Описание: Выполняет workflow-логику `test constructor does not set current block when block name is empty`.

### `BaseTest::testFinishConstructCompletesHappyPathAndUsesContainerMeta`

- Расположение: `unit/_core/block/BaseTest.php:155`
- Сигнатура: `function testFinishConstructCompletesHappyPathAndUsesContainerMeta()`
- Описание: Выполняет workflow-логику `test finish construct completes happy path and uses container meta`.

### `BaseTest::testFinishConstructStopsAfterRoleFailure`

- Расположение: `unit/_core/block/BaseTest.php:180`
- Сигнатура: `function testFinishConstructStopsAfterRoleFailure()`
- Описание: Выполняет workflow-логику `test finish construct stops after role failure`.

### `BaseTest::testMetaAccessMutationAndDeprecatedAliases`

- Расположение: `unit/_core/block/BaseTest.php:204`
- Сигнатура: `function testMetaAccessMutationAndDeprecatedAliases()`
- Описание: Выполняет workflow-логику `test meta access mutation and deprecated aliases`.

### `BaseTest::testDelayedAndDynamicMetaAreResolvedAndMergedOnce`

- Расположение: `unit/_core/block/BaseTest.php:237`
- Сигнатура: `function testDelayedAndDynamicMetaAreResolvedAndMergedOnce()`
- Описание: Выполняет логику `test delayed and dynamic meta are resolved and merged once` и возвращает вычисленный результат.

### `BaseTest::value`

- Расположение: `unit/_core/block/BaseTest.php:248`
- Сигнатура: `function value($value)`
- Описание: Выполняет логику `value` и возвращает вычисленный результат.

### `BaseTest::testForcedDynamicMetaCanBeEnabledByMetaFlag`

- Расположение: `unit/_core/block/BaseTest.php:286`
- Сигнатура: `function testForcedDynamicMetaCanBeEnabledByMetaFlag()`
- Описание: Выполняет workflow-логику `test forced dynamic meta can be enabled by meta flag`.

### `BaseTest::testRoleConditionReturnsNullForEmptyOrAllowedRolesAndCachesDeniedRole`

- Расположение: `unit/_core/block/BaseTest.php:300`
- Сигнатура: `function testRoleConditionReturnsNullForEmptyOrAllowedRolesAndCachesDeniedRole()`
- Описание: Выполняет workflow-логику `test role condition returns null for empty or allowed roles and caches denied role`.

### `BaseTest::testViewMethodsDelegateToTabAndRouter`

- Расположение: `unit/_core/block/BaseTest.php:324`
- Сигнатура: `function testViewMethodsDelegateToTabAndRouter()`
- Описание: Выполняет workflow-логику `test view methods delegate to tab and router`.

### `BaseTest::testTemplateSelectionValidationAndLocalizedSuffixes`

- Расположение: `unit/_core/block/BaseTest.php:346`
- Сигнатура: `function testTemplateSelectionValidationAndLocalizedSuffixes()`
- Описание: Выполняет workflow-логику `test template selection validation and localized suffixes`.

### `BaseTest::testSetTemplateFindsTemplateByClassParentPath`

- Расположение: `unit/_core/block/BaseTest.php:374`
- Сигнатура: `function testSetTemplateFindsTemplateByClassParentPath()`
- Описание: Выполняет workflow-логику `test set template finds template by class parent path`.

### `BaseTest::testRootParametersAndTemplateVariablesAreCopiedFromMeta`

- Расположение: `unit/_core/block/BaseTest.php:393`
- Сигнатура: `function testRootParametersAndTemplateVariablesAreCopiedFromMeta()`
- Описание: Выполняет workflow-логику `test root parameters and template variables are copied from meta`.

### `BaseTest::testPreparseMetaUpdatesRootAndTplVarsForHtmlView`

- Расположение: `unit/_core/block/BaseTest.php:433`
- Сигнатура: `function testPreparseMetaUpdatesRootAndTplVarsForHtmlView()`
- Описание: Выполняет workflow-логику `test preparse meta updates root and tpl vars for html view`.

### `BaseTest::testEmbeddedBlocksCreateNewBlocksAndReuseMainPlaceholder`

- Расположение: `unit/_core/block/BaseTest.php:458`
- Сигнатура: `function testEmbeddedBlocksCreateNewBlocksAndReuseMainPlaceholder()`
- Описание: Выполняет workflow-логику `test embedded blocks create new blocks and reuse main placeholder`.

### `BaseTest::testEmbeddedMainPlaceholderRequiresExistingMainBlock`

- Расположение: `unit/_core/block/BaseTest.php:488`
- Сигнатура: `function testEmbeddedMainPlaceholderRequiresExistingMainBlock()`
- Описание: Выполняет workflow-логику `test embedded main placeholder requires existing main block`.

### `BaseTest::testEmbeddedBlockLookupThrowsForUnknownKey`

- Расположение: `unit/_core/block/BaseTest.php:508`
- Сигнатура: `function testEmbeddedBlockLookupThrowsForUnknownKey()`
- Описание: Выполняет workflow-логику `test embedded block lookup throws for unknown key`.

### `BaseTest::testBlockLookupClassParsingAndBlockExceptions`

- Расположение: `unit/_core/block/BaseTest.php:526`
- Сигнатура: `function testBlockLookupClassParsingAndBlockExceptions()`
- Описание: Выполняет workflow-логику `test block lookup class parsing and block exceptions`.

### `BaseTest::testMagicGetAndDelegatedCalls`

- Расположение: `unit/_core/block/BaseTest.php:571`
- Сигнатура: `function testMagicGetAndDelegatedCalls()`
- Описание: Выполняет workflow-логику `test magic get and delegated calls`.
- Побочные эффекты: использует service locator/helpers фреймворка

### `BaseTest::testDelegateHelpersCacheRoleCheckRunInitAndDebugInfo`

- Расположение: `unit/_core/block/BaseTest.php:602`
- Сигнатура: `function testDelegateHelpersCacheRoleCheckRunInitAndDebugInfo()`
- Описание: Выполняет workflow-логику `test delegate helpers cache role check run init and debug info`.

## `unit/_core/block/MetaFilesTest.php`

### `role`

- Расположение: `unit/_core/block/MetaFilesTest.php:13`
- Сигнатура: `function role($condition)`
- Описание: Runs the role operation and returns its result.
- Параметры: mixed $condition Input value for the condition argument.
- Возвращает: mixed Returns the value produced by the operation.

### `MetaFilesTest::metaFileProvider`

- Расположение: `unit/_core/block/MetaFilesTest.php:26`
- Сигнатура: `function metaFileProvider(): array`
- Описание: Выполняет логику `meta file provider` и возвращает вычисленный результат.

### `MetaFilesTest::testMetaFileReturnsOwnConfigurationArray`

- Расположение: `unit/_core/block/MetaFilesTest.php:53`
- Сигнатура: `function testMetaFileReturnsOwnConfigurationArray(string $file)`
- Описание: Выполняет workflow-логику `test meta file returns own configuration array`.

### `MetaFilesTest::testAdminDataMetaListsEditableInputTypes`

- Расположение: `unit/_core/block/MetaFilesTest.php:68`
- Сигнатура: `function testAdminDataMetaListsEditableInputTypes()`
- Описание: Выполняет workflow-логику `test admin data meta lists editable input types`.

### `MetaFilesTest::testAdminIndexMetaBuildsRuntimeEmbedScriptFromRole`

- Расположение: `unit/_core/block/MetaFilesTest.php:84`
- Сигнатура: `function testAdminIndexMetaBuildsRuntimeEmbedScriptFromRole()`
- Описание: Выполняет workflow-логику `test admin index meta builds runtime embed script from role`.

### `MetaFilesTest::testFormUsualMetaContainsDefaultDesignAndValidatorSettings`

- Расположение: `unit/_core/block/MetaFilesTest.php:98`
- Сигнатура: `function testFormUsualMetaContainsDefaultDesignAndValidatorSettings()`
- Описание: Выполняет workflow-логику `test form usual meta contains default design and validator settings`.

### `MetaFilesTest::testPagerQuantifierMetaDescribesGetFormField`

- Расположение: `unit/_core/block/MetaFilesTest.php:114`
- Сигнатура: `function testPagerQuantifierMetaDescribesGetFormField()`
- Описание: Выполняет workflow-логику `test pager quantifier meta describes get form field`.

## `unit/_core/di/ContainerTest.php`

### `ContainerTest::testFactoryReceivesContainerAndRuntimeArguments`

- Расположение: `unit/_core/di/ContainerTest.php:13`
- Сигнатура: `function testFactoryReceivesContainerAndRuntimeArguments(): void`
- Описание: Выполняет workflow-логику `test factory receives container and runtime arguments`.

### `ContainerTest::testSharedFactoryCachesArgumentLessService`

- Расположение: `unit/_core/di/ContainerTest.php:36`
- Сигнатура: `function testSharedFactoryCachesArgumentLessService(): void`
- Описание: Выполняет workflow-логику `test shared factory caches argument less service`.

### `ContainerTest::testExplicitInstanceOverridesFactory`

- Расположение: `unit/_core/di/ContainerTest.php:49`
- Сигнатура: `function testExplicitInstanceOverridesFactory(): void`
- Описание: Выполняет workflow-логику `test explicit instance overrides factory`.

### `ContainerTest::testAliasResolvesRegisteredService`

- Расположение: `unit/_core/di/ContainerTest.php:65`
- Сигнатура: `function testAliasResolvesRegisteredService(): void`
- Описание: Выполняет workflow-логику `test alias resolves registered service`.

### `ContainerTest::testUnknownServiceThrowsClearException`

- Расположение: `unit/_core/di/ContainerTest.php:82`
- Сигнатура: `function testUnknownServiceThrowsClearException(): void`
- Описание: Выполняет workflow-логику `test unknown service throws clear exception`.

## `unit/_core/error/TemplateFilesTest.php`

### `TemplateFilesTest::testDefaultTemplateRendersTextResponseFromTplVars`

- Расположение: `unit/_core/error/TemplateFilesTest.php:10`
- Сигнатура: `function testDefaultTemplateRendersTextResponseFromTplVars()`
- Описание: Выполняет workflow-логику `test default template renders text response from tpl vars`.
- Побочные эффекты: меняет HTTP/session состояние

### `TemplateFilesTest::testError403TemplateSetsForbiddenHtmlResponse`

- Расположение: `unit/_core/error/TemplateFilesTest.php:25`
- Сигнатура: `function testError403TemplateSetsForbiddenHtmlResponse()`
- Описание: Выполняет workflow-логику `test error403 template sets forbidden html response`.
- Побочные эффекты: меняет HTTP/session состояние

### `TemplateFilesTest::testError404TemplateSetsNotFoundHtmlResponse`

- Расположение: `unit/_core/error/TemplateFilesTest.php:41`
- Сигнатура: `function testError404TemplateSetsNotFoundHtmlResponse()`
- Описание: Выполняет workflow-логику `test error404 template sets not found html response`.
- Побочные эффекты: меняет HTTP/session состояние

### `TemplateFilesTest::testError500TemplateRendersXhtmlTextParagraphs`

- Расположение: `unit/_core/error/TemplateFilesTest.php:56`
- Сигнатура: `function testError500TemplateRendersXhtmlTextParagraphs()`
- Описание: Выполняет workflow-логику `test error500 template renders xhtml text paragraphs`.
- Побочные эффекты: меняет HTTP/session состояние

### `ErrorTemplateRenderer::__construct`

- Расположение: `unit/_core/error/TemplateFilesTest.php:75`
- Сигнатура: `function __construct(private array $tplVars = [])`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `ErrorTemplateRenderer`.

### `ErrorTemplateRenderer::render`

- Расположение: `unit/_core/error/TemplateFilesTest.php:86`
- Сигнатура: `function render(string $file): array`
- Описание: Выполняет логику `render` и возвращает вычисленный результат.

### `ErrorTemplateRenderer::setResponseHeader`

- Расположение: `unit/_core/error/TemplateFilesTest.php:98`
- Сигнатура: `function setResponseHeader(int $code): string`
- Описание: Устанавливает, добавляет или сохраняет данные `response header` в рамках этого метода класса `ErrorTemplateRenderer`.
- Побочные эффекты: меняет HTTP/session состояние

### `ErrorTemplateRenderer::setContentType`

- Расположение: `unit/_core/error/TemplateFilesTest.php:110`
- Сигнатура: `function setContentType(string $type): string`
- Описание: Устанавливает, добавляет или сохраняет данные `content type` в рамках этого метода класса `ErrorTemplateRenderer`.

### `ErrorTemplateRenderer::setDoctype`

- Расположение: `unit/_core/error/TemplateFilesTest.php:120`
- Сигнатура: `function setDoctype(): string`
- Описание: Устанавливает, добавляет или сохраняет данные `doctype` в рамках этого метода класса `ErrorTemplateRenderer`.

### `ErrorTemplateRenderer::getTplVar`

- Расположение: `unit/_core/error/TemplateFilesTest.php:130`
- Сигнатура: `function getTplVar(): array`
- Описание: Получает, читает или вычисляет данные `tpl var` в рамках этого метода класса `ErrorTemplateRenderer`.

### `ErrorTemplateRenderer::convArrayToSting`

- Расположение: `unit/_core/error/TemplateFilesTest.php:143`
- Сигнатура: `function convArrayToSting(array $data, string $separator = "\n"): string`
- Описание: Выполняет логику `conv array to sting` и возвращает вычисленный результат.

## `unit/_core/exception/BaseTest.php`

### `ExceptionBaseTest::tearDown`

- Расположение: `unit/_core/exception/BaseTest.php:17`
- Сигнатура: `function tearDown(): void`
- Описание: Выполняет workflow-логику `tear down`.

### `ExceptionBaseTest::handleError`

- Расположение: `unit/_core/exception/BaseTest.php:35`
- Сигнатура: `function handleError($type, $message, $fileName = null, $lineNum = null, $errContext = null)`
- Описание: Запускает или обрабатывает workflow `error` для этого метода класса `ExceptionBaseTest`.

### `ExceptionBaseTest::captureErrors`

- Расположение: `unit/_core/exception/BaseTest.php:46`
- Сигнатура: `function captureErrors()`
- Описание: Выполняет workflow-логику `capture errors`.

### `ExceptionBaseTest::testConstructorSetsDefaultPublicMessageFileAndLogMessage`

- Расположение: `unit/_core/exception/BaseTest.php:58`
- Сигнатура: `function testConstructorSetsDefaultPublicMessageFileAndLogMessage()`
- Описание: Выполняет workflow-логику `test constructor sets default public message file and log message`.

### `ExceptionBaseTest::testDbOperationAllowsRollbackCommitAndNothing`

- Расположение: `unit/_core/exception/BaseTest.php:77`
- Сигнатура: `function testDbOperationAllowsRollbackCommitAndNothing()`
- Описание: Выполняет workflow-логику `test db operation allows rollback commit and nothing`.

### `ExceptionBaseTest::testInvalidDbOperationThrowsException`

- Расположение: `unit/_core/exception/BaseTest.php:90`
- Сигнатура: `function testInvalidDbOperationThrowsException()`
- Описание: Выполняет workflow-логику `test invalid db operation throws exception`.

### `ExceptionBaseTest::testGetLogVarsFormatsScalarsNullArraysAndObjects`

- Расположение: `unit/_core/exception/BaseTest.php:105`
- Сигнатура: `function testGetLogVarsFormatsScalarsNullArraysAndObjects()`
- Описание: Выполняет workflow-логику `test get log vars formats scalars null arrays and objects`.

## `unit/_core/exception/Error500Test.php`

### `ExceptionError500Test::setUp`

- Расположение: `unit/_core/exception/Error500Test.php:20`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `ExceptionError500Test`.

### `ExceptionError500Test::testError500KeepsInternalMessageAndLogsThroughErrorService`

- Расположение: `unit/_core/exception/Error500Test.php:35`
- Сигнатура: `function testError500KeepsInternalMessageAndLogsThroughErrorService()`
- Описание: Выполняет workflow-логику `test error500 keeps internal message and logs through error service`.

## `unit/_core/exception/FatalTest.php`

### `ExceptionFatalTest::setUp`

- Расположение: `unit/_core/exception/FatalTest.php:14`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `ExceptionFatalTest`.

### `ExceptionFatalTest::testFatalExceptionUsesPublicMessageFileAndLogsOriginalMessage`

- Расположение: `unit/_core/exception/FatalTest.php:24`
- Сигнатура: `function testFatalExceptionUsesPublicMessageFileAndLogsOriginalMessage()`
- Описание: Выполняет workflow-логику `test fatal exception uses public message file and logs original message`.
- Побочные эффекты: читает PHP superglobals

## `unit/_core/exception/block/FatalTest.php`

### `ExceptionBlockFatalTest::setUp`

- Расположение: `unit/_core/exception/block/FatalTest.php:26`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `ExceptionBlockFatalTest`.

### `ExceptionBlockFatalTest::testBlockFatalKeepsBlockRollsBackAndLogsBlockClass`

- Расположение: `unit/_core/exception/block/FatalTest.php:42`
- Сигнатура: `function testBlockFatalKeepsBlockRollsBackAndLogsBlockClass()`
- Описание: Выполняет workflow-логику `test block fatal keeps block rolls back and logs block class`.

## `unit/_core/exception/block/FormPartTest.php`

### `ExceptionBlockFormPartTest::setUp`

- Расположение: `unit/_core/exception/block/FormPartTest.php:22`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `ExceptionBlockFormPartTest`.

### `ExceptionBlockFormPartTest::testFormPartExceptionExposesBlockNameAndValidationMessages`

- Расположение: `unit/_core/exception/block/FormPartTest.php:33`
- Сигнатура: `function testFormPartExceptionExposesBlockNameAndValidationMessages()`
- Описание: Выполняет workflow-логику `test form part exception exposes block name and validation messages`.

## `unit/_core/exception/block/LocalTest.php`

### `ExceptionBlockLocalTest::setUp`

- Расположение: `unit/_core/exception/block/LocalTest.php:21`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `ExceptionBlockLocalTest`.

### `ExceptionBlockLocalTest::testLocalExceptionKeepsBlockMessageAndNoticeCode`

- Расположение: `unit/_core/exception/block/LocalTest.php:32`
- Сигнатура: `function testLocalExceptionKeepsBlockMessageAndNoticeCode()`
- Описание: Выполняет workflow-логику `test local exception keeps block message and notice code`.

## `unit/_core/exception/model/ReverseTest.php`

### `ExceptionModelReverseTest::setUp`

- Расположение: `unit/_core/exception/model/ReverseTest.php:16`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `ExceptionModelReverseTest`.

### `ExceptionModelReverseTest::testReverseExceptionKeepsEntityAndDoesNotOpenDatabaseTransactionHandling`

- Расположение: `unit/_core/exception/model/ReverseTest.php:26`
- Сигнатура: `function testReverseExceptionKeepsEntityAndDoesNotOpenDatabaseTransactionHandling()`
- Описание: Выполняет workflow-логику `test reverse exception keeps entity and does not open database transaction handling`.

## `unit/_core/exception/model/entity/FatalTest.php`

### `ExceptionModelEntityFatalTest::setUp`

- Расположение: `unit/_core/exception/model/entity/FatalTest.php:21`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `ExceptionModelEntityFatalTest`.

### `ExceptionModelEntityFatalTest::testEntityFatalKeepsEntityRollsBackAndLogsEntitySnapshot`

- Расположение: `unit/_core/exception/model/entity/FatalTest.php:36`
- Сигнатура: `function testEntityFatalKeepsEntityRollsBackAndLogsEntitySnapshot()`
- Описание: Выполняет workflow-логику `test entity fatal keeps entity rolls back and logs entity snapshot`.

## `unit/_core/exception/plain/FatalTest.php`

### `ExceptionPlainFatalTest::setUp`

- Расположение: `unit/_core/exception/plain/FatalTest.php:20`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `ExceptionPlainFatalTest`.

### `ExceptionPlainFatalTest::testPlainFatalKeepsControllerAndLogsControllerClass`

- Расположение: `unit/_core/exception/plain/FatalTest.php:35`
- Сигнатура: `function testPlainFatalKeepsControllerAndLogsControllerClass()`
- Описание: Выполняет workflow-логику `test plain fatal keeps controller and logs controller class`.

## `unit/_core/exception/service/DatabaseTest.php`

### `ExceptionServiceDatabaseTest::setUp`

- Расположение: `unit/_core/exception/service/DatabaseTest.php:22`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `ExceptionServiceDatabaseTest`.

### `ExceptionServiceDatabaseTest::testDatabaseExceptionExposesOperationErrorAndSql`

- Расположение: `unit/_core/exception/service/DatabaseTest.php:40`
- Сигнатура: `function testDatabaseExceptionExposesOperationErrorAndSql()`
- Описание: Выполняет workflow-логику `test database exception exposes operation error and sql`.

### `ExceptionServiceDatabaseTest::testLowLevelOperationUsesGenericServiceFatalLogging`

- Расположение: `unit/_core/exception/service/DatabaseTest.php:68`
- Сигнатура: `function testLowLevelOperationUsesGenericServiceFatalLogging()`
- Описание: Выполняет workflow-логику `test low level operation uses generic service fatal logging`.

### `ExceptionServiceDatabaseTest::testQueryOperationIsLoggedAsDatabaseError`

- Расположение: `unit/_core/exception/service/DatabaseTest.php:84`
- Сигнатура: `function testQueryOperationIsLoggedAsDatabaseError()`
- Описание: Выполняет workflow-логику `test query operation is logged as database error`.

## `unit/_core/exception/service/DateTest.php`

### `ExceptionServiceDateTest::setUp`

- Расположение: `unit/_core/exception/service/DateTest.php:14`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `ExceptionServiceDateTest`.

### `ExceptionServiceDateTest::testDateExceptionKeepsMessageCodeAndLogsThroughBootstrap`

- Расположение: `unit/_core/exception/service/DateTest.php:24`
- Сигнатура: `function testDateExceptionKeepsMessageCodeAndLogsThroughBootstrap()`
- Описание: Выполняет workflow-логику `test date exception keeps message code and logs through bootstrap`.

## `unit/_core/exception/service/FatalTest.php`

### `ExceptionServiceFatalTest::setUp`

- Расположение: `unit/_core/exception/service/FatalTest.php:21`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `ExceptionServiceFatalTest`.

### `ExceptionServiceFatalTest::testServiceFatalKeepsServiceRollsBackAndLogsToErrorService`

- Расположение: `unit/_core/exception/service/FatalTest.php:37`
- Сигнатура: `function testServiceFatalKeepsServiceRollsBackAndLogsToErrorService()`
- Описание: Выполняет workflow-логику `test service fatal keeps service rolls back and logs to error service`.

### `ExceptionServiceFatalTest::testServiceFatalCanLogToPhpOrStaySilent`

- Расположение: `unit/_core/exception/service/FatalTest.php:59`
- Сигнатура: `function testServiceFatalCanLogToPhpOrStaySilent()`
- Описание: Выполняет workflow-логику `test service fatal can log to php or stay silent`.

## `unit/_core/exception/template/FatalTest.php`

### `ExceptionTemplateFatalTest::setUp`

- Расположение: `unit/_core/exception/template/FatalTest.php:20`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `ExceptionTemplateFatalTest`.

### `ExceptionTemplateFatalTest::testTemplateFatalLogsTemplateClassAndUsesDefaultPublicError`

- Расположение: `unit/_core/exception/template/FatalTest.php:35`
- Сигнатура: `function testTemplateFatalLogsTemplateClassAndUsesDefaultPublicError()`
- Описание: Выполняет workflow-логику `test template fatal logs template class and uses default public error`.

## `unit/_core/service/RoleTest.php`

### `RoleTest::setUp`

- Расположение: `unit/_core/service/RoleTest.php:15`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `RoleTest`.

### `RoleTest::testCheckEvaluatesRoleExpressionWithoutEval`

- Расположение: `unit/_core/service/RoleTest.php:25`
- Сигнатура: `function testCheckEvaluatesRoleExpressionWithoutEval()`
- Описание: Выполняет workflow-логику `test check evaluates role expression without eval`.

### `RoleTest::testCheckReportsInvalidRoleExpression`

- Расположение: `unit/_core/service/RoleTest.php:41`
- Сигнатура: `function testCheckReportsInvalidRoleExpression()`
- Описание: Выполняет workflow-логику `test check reports invalid role expression`.

### `RoleTest::makeRole`

- Расположение: `unit/_core/service/RoleTest.php:57`
- Сигнатура: `function makeRole(array $roles): \fan\core\service\role`
- Описание: Создает, разбирает, форматирует или конвертирует данные `role` для этого метода класса `RoleTest`.

## `unit/_core/service/config/ArrTest.php`

### `ServiceConfigArrTest::testPhpExtensionIsAppliedWhenResolvingConfigFile`

- Расположение: `unit/_core/service/config/ArrTest.php:13`
- Сигнатура: `function testPhpExtensionIsAppliedWhenResolvingConfigFile()`
- Описание: Выполняет логику `test php extension is applied when resolving config file` и возвращает вычисленный результат.
- Побочные эффекты: работает с файловой системой

## `unit/_core/service/config/BaseTest.php`

### `ServiceConfigBaseTest::setUp`

- Расположение: `unit/_core/service/config/BaseTest.php:14`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `ServiceConfigBaseTest`.
- Побочные эффекты: работает с файловой системой

### `ServiceConfigBaseTest::tearDown`

- Расположение: `unit/_core/service/config/BaseTest.php:25`
- Сигнатура: `function tearDown(): void`
- Описание: Выполняет workflow-логику `tear down`.
- Побочные эффекты: работает с файловой системой

### `ServiceConfigBaseTest::testDirectoryPathIsNormalizedAndExistingFilePathIsResolved`

- Расположение: `unit/_core/service/config/BaseTest.php:40`
- Сигнатура: `function testDirectoryPathIsNormalizedAndExistingFilePathIsResolved()`
- Описание: Выполняет workflow-логику `test directory path is normalized and existing file path is resolved`.
- Побочные эффекты: работает с файловой системой

### `ServiceConfigBaseTest::testBaseLoaderReturnsEmptyArrayForExistingFileWithoutSpecialParser`

- Расположение: `unit/_core/service/config/BaseTest.php:57`
- Сигнатура: `function testBaseLoaderReturnsEmptyArrayForExistingFileWithoutSpecialParser()`
- Описание: Выполняет workflow-логику `test base loader returns empty array for existing file without special parser`.
- Побочные эффекты: работает с файловой системой

## `unit/_core/service/config/IniTest.php`

### `ServiceConfigIniTest::setUp`

- Расположение: `unit/_core/service/config/IniTest.php:15`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `ServiceConfigIniTest`.

### `ServiceConfigIniTest::tearDown`

- Расположение: `unit/_core/service/config/IniTest.php:25`
- Сигнатура: `function tearDown(): void`
- Описание: Выполняет workflow-логику `tear down`.
- Побочные эффекты: работает с файловой системой

### `ServiceConfigIniTest::testIniLoaderSeparatesDotKeysAndConvertsBracketLists`

- Расположение: `unit/_core/service/config/IniTest.php:37`
- Сигнатура: `function testIniLoaderSeparatesDotKeysAndConvertsBracketLists()`
- Описание: Выполняет workflow-логику `test ini loader separates dot keys and converts bracket lists`.
- Побочные эффекты: работает с файловой системой

### `ServiceConfigIniTest::testIniExtensionIsUsedByFilePathResolver`

- Расположение: `unit/_core/service/config/IniTest.php:68`
- Сигнатура: `function testIniExtensionIsUsedByFilePathResolver()`
- Описание: Выполняет workflow-логику `test ini extension is used by file path resolver`.
- Побочные эффекты: работает с файловой системой

## `unit/_core/service/config/RowTest.php`

### `ServiceConfigRowTest::setUp`

- Расположение: `unit/_core/service/config/RowTest.php:17`
- Сигнатура: `function setUp(): void`
- Описание: Устанавливает, добавляет или сохраняет данные `up` в рамках этого метода класса `ServiceConfigRowTest`.

### `ServiceConfigRowTest::testRootKeysOwnersSourcesAndNestedRowsArePreserved`

- Расположение: `unit/_core/service/config/RowTest.php:27`
- Сигнатура: `function testRootKeysOwnersSourcesAndNestedRowsArePreserved()`
- Описание: Выполняет workflow-логику `test root keys owners sources and nested rows are preserved`.

### `ServiceConfigRowTest::testFacadeControlsMutationResetAndMergePriority`

- Расположение: `unit/_core/service/config/RowTest.php:57`
- Сигнатура: `function testFacadeControlsMutationResetAndMergePriority()`
- Описание: Выполняет workflow-логику `test facade controls mutation reset and merge priority`.

### `ServiceConfigRowTest::testSerializationKeepsSourceDataAndRootKey`

- Расположение: `unit/_core/service/config/RowTest.php:96`
- Сигнатура: `function testSerializationKeepsSourceDataAndRootKey()`
- Описание: Выполняет workflow-логику `test serialization keeps source data and root key`.
- Побочные эффекты: сериализует или десериализует данные

## `unit/_core/service/config/XmlTest.php`

### `ServiceConfigXmlTest::testXmlExtensionIsAppliedWhenResolvingConfigFile`

- Расположение: `unit/_core/service/config/XmlTest.php:13`
- Сигнатура: `function testXmlExtensionIsAppliedWhenResolvingConfigFile()`
- Описание: Выполняет workflow-логику `test xml extension is applied when resolving config file`.
- Побочные эффекты: работает с файловой системой

## `unit/_core/service/config/YamlTest.php`

### `ServiceConfigYamlTest::testYamlExtensionIsAppliedWhenResolvingConfigFile`

- Расположение: `unit/_core/service/config/YamlTest.php:13`
- Сигнатура: `function testYamlExtensionIsAppliedWhenResolvingConfigFile()`
- Описание: Выполняет workflow-логику `test yaml extension is applied when resolving config file`.
- Побочные эффекты: работает с файловой системой

## `unit/_core/service/entity/SnippetTest.php`

### `SnippetTest::testSnippetConditionFiltersQueryAndPreparesData`

- Расположение: `unit/_core/service/entity/SnippetTest.php:12`
- Сигнатура: `function testSnippetConditionFiltersQueryAndPreparesData()`
- Описание: Выполняет workflow-логику `test snippet condition filters query and prepares data`.

### `SnippetTest::testSnippetSupportsStringConstants`

- Расположение: `unit/_core/service/entity/SnippetTest.php:35`
- Сигнатура: `function testSnippetSupportsStringConstants()`
- Описание: Выполняет workflow-логику `test snippet supports string constants`.

### `SnippetTest::testEmptyConditionIsTreatedAsUnconditional`

- Расположение: `unit/_core/service/entity/SnippetTest.php:51`
- Сигнатура: `function testEmptyConditionIsTreatedAsUnconditional()`
- Описание: Выполняет workflow-логику `test empty condition is treated as unconditional`.

### `SnippetTest::makeSnippet`

- Расположение: `unit/_core/service/entity/SnippetTest.php:66`
- Сигнатура: `function makeSnippet(string $query, string $condition): \fan\core\service\entity\snippet`
- Описание: Создает, разбирает, форматирует или конвертирует данные `snippet` для этого метода класса `SnippetTest`.
- Побочные эффекты: выполняет database операции

## `unit/_core/service/header/CodeTest.php`

### `ServiceHeaderCodeTest::testCodeGroupsExposeExpectedStatusTexts`

- Расположение: `unit/_core/service/header/CodeTest.php:12`
- Сигнатура: `function testCodeGroupsExposeExpectedStatusTexts()`
- Описание: Выполняет workflow-логику `test code groups expose expected status texts`.
- Побочные эффекты: меняет HTTP/session состояние

### `ServiceHeaderCodeTest::testCodeGroupsRemainSeparatedByStatusClass`

- Расположение: `unit/_core/service/header/CodeTest.php:26`
- Сигнатура: `function testCodeGroupsRemainSeparatedByStatusClass()`
- Описание: Выполняет workflow-логику `test code groups remain separated by status class`.
- Побочные эффекты: меняет HTTP/session состояние

## `unit/_core/view/DefinerTest.php`

### `DefinerTest::testGetViewParserNameEvaluatesConfiguredRulesWithoutEval`

- Расположение: `unit/_core/view/DefinerTest.php:12`
- Сигнатура: `function testGetViewParserNameEvaluatesConfiguredRulesWithoutEval()`
- Описание: Выполняет workflow-логику `test get view parser name evaluates configured rules without eval`.

### `DefinerTest::testGetViewParserNameSupportsNumericIntegerAndRegexpRules`

- Расположение: `unit/_core/view/DefinerTest.php:46`
- Сигнатура: `function testGetViewParserNameSupportsNumericIntegerAndRegexpRules()`
- Описание: Выполняет workflow-логику `test get view parser name supports numeric integer and regexp rules`.

### `FanTestViewDefiner::setRequest`

- Расположение: `unit/_core/view/DefinerTest.php:81`
- Сигнатура: `function setRequest(object $request): void`
- Описание: Устанавливает, добавляет или сохраняет данные `request` в рамках этого метода класса `FanTestViewDefiner`.

### `FanTestDefinerRequest::__construct`

- Расположение: `unit/_core/view/DefinerTest.php:94`
- Сигнатура: `function __construct(private array $data)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `FanTestDefinerRequest`.

### `FanTestDefinerRequest::get`

- Расположение: `unit/_core/view/DefinerTest.php:107`
- Сигнатура: `function get(string $key, string $source, mixed $default = null): mixed`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `FanTestDefinerRequest`.

## `unit/mock/_core/SourceFileContractTestCase.php`

### `FanTest\_core\SourceFileContractTestCase::testSourceFileExists`

- Расположение: `unit/mock/_core/SourceFileContractTestCase.php:14`
- Сигнатура: `function testSourceFileExists(): void`
- Описание: Выполняет workflow-логику `test source file exists`.

### `FanTest\_core\SourceFileContractTestCase::testSourceFileIsValidPhpCode`

- Расположение: `unit/mock/_core/SourceFileContractTestCase.php:24`
- Сигнатура: `function testSourceFileIsValidPhpCode(): void`
- Описание: Выполняет workflow-логику `test source file is valid php code`.

### `FanTest\_core\SourceFileContractTestCase::testSourceFileDeclaresContract`

- Расположение: `unit/mock/_core/SourceFileContractTestCase.php:37`
- Сигнатура: `function testSourceFileDeclaresContract(): void`
- Описание: Выполняет логику `test source file declares contract` и возвращает вычисленный результат.

### `FanTest\_core\SourceFileContractTestCase::testTestCaseIsNoLongerPending`

- Расположение: `unit/mock/_core/SourceFileContractTestCase.php:59`
- Сигнатура: `function testTestCaseIsNoLongerPending(): void`
- Описание: Выполняет workflow-логику `test test case is no longer pending`.

### `FanTest\_core\SourceFileContractTestCase::sourcePath`

- Расположение: `unit/mock/_core/SourceFileContractTestCase.php:72`
- Сигнатура: `function sourcePath(): string`
- Описание: Выполняет логику `source path` и возвращает вычисленный результат.

### `FanTest\_core\SourceFileContractTestCase::sourceCode`

- Расположение: `unit/mock/_core/SourceFileContractTestCase.php:85`
- Сигнатура: `function sourceCode(): string`
- Описание: Выполняет логику `source code` и возвращает вычисленный результат.

### `FanTest\_core\SourceFileContractTestCase::readDeclarations`

- Расположение: `unit/mock/_core/SourceFileContractTestCase.php:98`
- Сигнатура: `function readDeclarations(): array`
- Описание: Получает, читает или вычисляет данные `declarations` в рамках этого метода класса `FanTest\_core\SourceFileContractTestCase`.

### `FanTest\_core\SourceFileContractTestCase::readNamespace`

- Расположение: `unit/mock/_core/SourceFileContractTestCase.php:146`
- Сигнатура: `function readNamespace(array $tokens, int $offset): string`
- Описание: Получает, читает или вычисляет данные `namespace` в рамках этого метода класса `FanTest\_core\SourceFileContractTestCase`.

### `FanTest\_core\SourceFileContractTestCase::readNamedDeclaration`

- Расположение: `unit/mock/_core/SourceFileContractTestCase.php:169`
- Сигнатура: `function readNamedDeclaration(array $tokens, int $offset): ?string`
- Описание: Получает, читает или вычисляет данные `named declaration` в рамках этого метода класса `FanTest\_core\SourceFileContractTestCase`.

### `FanTest\_core\SourceFileContractTestCase::isAnonymousClass`

- Расположение: `unit/mock/_core/SourceFileContractTestCase.php:189`
- Сигнатура: `function isAnonymousClass(array $tokens, int $classIndex): bool`
- Описание: Проверяет условие или валидирует данные `anonymous class` и возвращает результат либо выбрасывает исключение.

## `unit/mock/_core/base/DataFunctions.php`

### `get_class_alt`

- Расположение: `unit/mock/_core/base/DataFunctions.php:11`
- Сигнатура: `function get_class_alt(mixed $value)`
- Описание: Retrieves class alt used by the framework helper.
- Параметры: mixed $value Value that should be applied or transformed.
- Возвращает: mixed Returns the value produced by the operation.

### `array_get_element`

- Расположение: `unit/mock/_core/base/DataFunctions.php:27`
- Сигнатура: `function &array_get_element(&$data, $path, $create = false)`
- Описание: Runs the array get element operation and returns its result.
- Параметры: mixed $data Structured data consumed by the operation.; mixed $path Filesystem or URL path used by the operation.; mixed $create Input value for the create argument.
- Возвращает: mixed Returns the value produced by the operation.

### `is_array_alt`

- Расположение: `unit/mock/_core/base/DataFunctions.php:62`
- Сигнатура: `function is_array_alt(mixed $arr)`
- Описание: Evaluates array alt and reports the outcome.
- Параметры: mixed $arr Input value for the arr argument.
- Возвращает: mixed Indicates whether the requested condition is satisfied.

### `array_merge_recursive_alt`

- Расположение: `unit/mock/_core/base/DataFunctions.php:76`
- Сигнатура: `function array_merge_recursive_alt(mixed $arrFirst)`
- Описание: Runs the array merge recursive alt operation and returns its result.
- Параметры: mixed $arrFirst Input value for the arr first argument.
- Возвращает: mixed Returns the value produced by the operation.

### `adduceToArray`

- Расположение: `unit/mock/_core/base/DataFunctions.php:104`
- Сигнатура: `function adduceToArray(mixed $src)`
- Описание: Applies uce to array to the framework helper.
- Параметры: mixed $src Input value for the src argument.
- Возвращает: mixed Returns the value produced by the operation.

### `fan\project\service\error::instance`

- Расположение: `unit/mock/_core/base/DataFunctions.php:132`
- Сигнатура: `function instance()`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.

### `fan\project\service\error::reset`

- Расположение: `unit/mock/_core/base/DataFunctions.php:145`
- Сигнатура: `function reset()`
- Описание: Удаляет или сбрасывает состояние `состояние` для этого метода класса `fan\project\service\error`.

### `fan\project\service\error::logErrorMessage`

- Расположение: `unit/mock/_core/base/DataFunctions.php:160`
- Сигнатура: `function logErrorMessage($message, $title = '', $note = '', $fixPosition = false)`
- Описание: Выполняет workflow-логику `log error message`.

### `fan\project\service\error::logExceptionMessage`

- Расположение: `unit/mock/_core/base/DataFunctions.php:174`
- Сигнатура: `function logExceptionMessage(string $message, string $header = '', string $note = '')`
- Описание: Выполняет workflow-логику `log exception message`.
- Побочные эффекты: меняет HTTP/session состояние

### `fan\project\service\error::logDatabaseError`

- Расположение: `unit/mock/_core/base/DataFunctions.php:190`
- Сигнатура: `function logDatabaseError($connectionName, string $operation, $errorMessage, int|float $errorNum, $parsedSql)`
- Описание: Выполняет workflow-логику `log database error`.

## `unit/mock/_core/base/TestData.php`

### `FanTest\_core\base\TestData::allowSetter`

- Расположение: `unit/mock/_core/base/TestData.php:13`
- Сигнатура: `function allowSetter(mixed $setter)`
- Описание: Выполняет логику `allow setter` и возвращает вычисленный результат.

### `FanTest\_core\base\TestData::setMultiLevel`

- Расположение: `unit/mock/_core/base/TestData.php:25`
- Сигнатура: `function setMultiLevel($multiLevel)`
- Описание: Устанавливает, добавляет или сохраняет данные `multi level` в рамках этого метода класса `FanTest\_core\base\TestData`.

### `FanTest\_core\base\TestData::exposeSubData`

- Расположение: `unit/mock/_core/base/TestData.php:35`
- Сигнатура: `function exposeSubData()`
- Описание: Выполняет логику `expose sub data` и возвращает вычисленный результат.

### `FanTest\_core\base\TestDataSetter::setValue`

- Расположение: `unit/mock/_core/base/TestData.php:52`
- Сигнатура: `function setValue(TestData $data, $key, mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `value` в рамках этого метода класса `FanTest\_core\base\TestDataSetter`.

### `FanTest\_core\base\TestDataSetter::unsetValue`

- Расположение: `unit/mock/_core/base/TestData.php:65`
- Сигнатура: `function unsetValue(TestData $data, $key)`
- Описание: Выполняет workflow-логику `unset value`.

## `unit/mock/_core/base/TransferStubs.php`

### `fan\project\service\database::reset`

- Расположение: `unit/mock/_core/base/TransferStubs.php:13`
- Сигнатура: `function reset(): void`
- Описание: Удаляет или сбрасывает состояние `состояние` для этого метода класса `fan\project\service\database`.

### `fan\project\service\database::fixAll`

- Расположение: `unit/mock/_core/base/TransferStubs.php:26`
- Сигнатура: `function fixAll($dbOper, $makeException = true): void`
- Описание: Выполняет workflow-логику `fix all`.

## `unit/mock/_core/base/meta/MetaDoubles.php`

### `FanTest\_core\base\meta\TestMetaTab::getBlocksMetaByMain`

- Расположение: `unit/mock/_core/base/meta/MetaDoubles.php:37`
- Сигнатура: `function getBlocksMetaByMain($blockName)`
- Описание: Получает, читает или вычисляет данные `blocks meta by main` в рамках этого метода класса `FanTest\_core\base\meta\TestMetaTab`.

### `FanTest\_core\base\meta\TestMetaBlock::__construct`

- Расположение: `unit/mock/_core/base/meta/MetaDoubles.php:54`
- Сигнатура: `function __construct(string $blockName = 'content')`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `FanTest\_core\base\meta\TestMetaBlock`.

### `FanTest\_core\base\meta\TestMetaBlock::getBlockName`

- Расположение: `unit/mock/_core/base/meta/MetaDoubles.php:65`
- Сигнатура: `function getBlockName()`
- Описание: Получает, читает или вычисляет данные `block name` в рамках этого метода класса `FanTest\_core\base\meta\TestMetaBlock`.

### `FanTest\_core\base\meta\TestMetaBlock::getTab`

- Расположение: `unit/mock/_core/base/meta/MetaDoubles.php:75`
- Сигнатура: `function getTab()`
- Описание: Получает, читает или вычисляет данные `tab` в рамках этого метода класса `FanTest\_core\base\meta\TestMetaBlock`.

### `FanTest\_core\base\meta\TestMetaBlock::buildValue`

- Расположение: `unit/mock/_core/base/meta/MetaDoubles.php:88`
- Сигнатура: `function buildValue($left, $right = null)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `value` для этого метода класса `FanTest\_core\base\meta\TestMetaBlock`.

### `FanTest\_core\base\meta\TestMetaMaker::__construct`

- Расположение: `unit/mock/_core/base/meta/MetaDoubles.php:101`
- Сигнатура: `function __construct(?TestMetaBlock $block = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `FanTest\_core\base\meta\TestMetaMaker`.

### `FanTest\_core\base\meta\TestMetaMaker::replaceSource`

- Расположение: `unit/mock/_core/base/meta/MetaDoubles.php:115`
- Сигнатура: `function replaceSource(string $type, array $data)`
- Описание: Выполняет логику `replace source` и возвращает вычисленный результат.

### `FanTest\_core\base\meta\TestMetaMaker::mergeRow`

- Расположение: `unit/mock/_core/base/meta/MetaDoubles.php:129`
- Сигнатура: `function mergeRow(\fan\core\base\meta\row $row, array $data, $rewriteExisting = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `row` в рамках этого метода класса `FanTest\_core\base\meta\TestMetaMaker`.

### `FanTest\_core\base\meta\TestMetaMaker::exposeMakeActiveMeta`

- Расположение: `unit/mock/_core/base/meta/MetaDoubles.php:144`
- Сигнатура: `function exposeMakeActiveMeta(string $method, $arguments = [], string|object|null $obj = null, bool $delayed = true)`
- Описание: Выполняет логику `expose make active meta` и возвращает вычисленный результат.

## `unit/mock/_core/block/FakeServices.php`

### `FanTest\_core\block\FakeServiceRegistry::reset`

- Расположение: `unit/mock/_core/block/FakeServices.php:15`
- Сигнатура: `function reset()`
- Описание: Удаляет или сбрасывает состояние `состояние` для этого метода класса `FanTest\_core\block\FakeServiceRegistry`.

### `FanTest\_core\block\FakeServiceRegistry::set`

- Расположение: `unit/mock/_core/block/FakeServices.php:31`
- Сигнатура: `function set($name, $service)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `FanTest\_core\block\FakeServiceRegistry`.

### `FanTest\_core\block\FakeServiceRegistry::get`

- Расположение: `unit/mock/_core/block/FakeServices.php:44`
- Сигнатура: `function get($name, $args = [])`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `FanTest\_core\block\FakeServiceRegistry`.

### `FanTest\_core\block\FakeRoleRegistry::reset`

- Расположение: `unit/mock/_core/block/FakeServices.php:75`
- Сигнатура: `function reset()`
- Описание: Удаляет или сбрасывает состояние `состояние` для этого метода класса `FanTest\_core\block\FakeRoleRegistry`.

### `FanTest\_core\block\FakeRoleRegistry::set`

- Расположение: `unit/mock/_core/block/FakeServices.php:89`
- Сигнатура: `function set($condition, $result)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `FanTest\_core\block\FakeRoleRegistry`.

### `FanTest\_core\block\FakeRoleRegistry::check`

- Расположение: `unit/mock/_core/block/FakeServices.php:101`
- Сигнатура: `function check($condition)`
- Описание: Проверяет условие или валидирует данные `check` и возвращает результат либо выбрасывает исключение.

### `FanTest\_core\block\FakeGenericService::__construct`

- Расположение: `unit/mock/_core/block/FakeServices.php:117`
- Сигнатура: `function __construct($name)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `FanTest\_core\block\FakeGenericService`.

### `FanTest\_core\block\FakeSession::__construct`

- Расположение: `unit/mock/_core/block/FakeServices.php:138`
- Сигнатура: `function __construct($args = [])`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `FanTest\_core\block\FakeSession`.

### `FanTest\_core\block\FakeSession::get`

- Расположение: `unit/mock/_core/block/FakeServices.php:152`
- Сигнатура: `function get(mixed $key = null, mixed $default = null, $remove = false)`
- Описание: Получает, читает или вычисляет данные `значение` в рамках этого метода класса `FanTest\_core\block\FakeSession`.

### `FanTest\_core\block\FakeSession::set`

- Расположение: `unit/mock/_core/block/FakeServices.php:173`
- Сигнатура: `function set(mixed $key, mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `FanTest\_core\block\FakeSession`.

### `FanTest\_core\block\FakeSession::remove`

- Расположение: `unit/mock/_core/block/FakeServices.php:187`
- Сигнатура: `function remove(mixed $key)`
- Описание: Удаляет или сбрасывает состояние `элемент` для этого метода класса `FanTest\_core\block\FakeSession`.

### `FanTest\_core\block\FakeSubscriber::subscribeForEvent`

- Расположение: `unit/mock/_core/block/FakeServices.php:208`
- Сигнатура: `function subscribeForEvent($block, $eventName, $listenerMethod = 'eventHandler')`
- Описание: Выполняет логику `subscribe for event` и возвращает вычисленный результат.

### `FanTest\_core\block\FakeSubscriber::subscribeByName`

- Расположение: `unit/mock/_core/block/FakeServices.php:224`
- Сигнатура: `function subscribeByName($block, $broadcasterName, $eventName, $listenerMethod = 'eventHandler')`
- Описание: Выполняет логику `subscribe by name` и возвращает вычисленный результат.

### `FanTest\_core\block\FakeSubscriber::subscribeByClass`

- Расположение: `unit/mock/_core/block/FakeServices.php:240`
- Сигнатура: `function subscribeByClass($block, $className, $eventName, $listenerMethod = 'eventHandler')`
- Описание: Выполняет логику `subscribe by class` и возвращает вычисленный результат.

### `FanTest\_core\block\FakeSubscriber::unSubscribeForEvent`

- Расположение: `unit/mock/_core/block/FakeServices.php:255`
- Сигнатура: `function unSubscribeForEvent($block, $eventName, $listenerMethod = 'eventHandler')`
- Описание: Выполняет логику `un subscribe for event` и возвращает вычисленный результат.

### `FanTest\_core\block\FakeSubscriber::unSubscribeByName`

- Расположение: `unit/mock/_core/block/FakeServices.php:271`
- Сигнатура: `function unSubscribeByName($block, $broadcasterName, $eventName, $listenerMethod = 'eventHandler')`
- Описание: Выполняет логику `un subscribe by name` и возвращает вычисленный результат.

### `FanTest\_core\block\FakeSubscriber::unSubscribeByClass`

- Расположение: `unit/mock/_core/block/FakeServices.php:287`
- Сигнатура: `function unSubscribeByClass($block, $className, $eventName, $listenerMethod = 'eventHandler')`
- Описание: Выполняет логику `un subscribe by class` и возвращает вычисленный результат.

### `FanTest\_core\block\FakeSubscriber::broadcastEvent`

- Расположение: `unit/mock/_core/block/FakeServices.php:302`
- Сигнатура: `function broadcastEvent($block, $eventName, $data = [])`
- Описание: Выполняет логику `broadcast event` и возвращает вычисленный результат.

### `FanTest\_core\block\FakeViewDefiner::getViewParserName`

- Расположение: `unit/mock/_core/block/FakeServices.php:318`
- Сигнатура: `function getViewParserName()`
- Описание: Получает, читает или вычисляет данные `view parser name` в рамках этого метода класса `FanTest\_core\block\FakeViewDefiner`.

### `FanTest\_core\block\FakeViewClass::getFormat`

- Расположение: `unit/mock/_core/block/FakeServices.php:334`
- Сигнатура: `function getFormat()`
- Описание: Получает, читает или вычисляет данные `format` в рамках этого метода класса `FanTest\_core\block\FakeViewClass`.

### `FanTest\_core\block\FakeViewClass::getRouter`

- Расположение: `unit/mock/_core/block/FakeServices.php:346`
- Сигнатура: `function getRouter($block)`
- Описание: Получает, читает или вычисляет данные `router` в рамках этого метода класса `FanTest\_core\block\FakeViewClass`.

### `FanTest\_core\block\FakeViewRouter::__construct`

- Расположение: `unit/mock/_core/block/FakeServices.php:364`
- Сигнатура: `function __construct($block = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `FanTest\_core\block\FakeViewRouter`.

### `FanTest\_core\block\FakeViewRouter::set`

- Расположение: `unit/mock/_core/block/FakeServices.php:377`
- Сигнатура: `function set($key, mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `значение` в рамках этого метода класса `FanTest\_core\block\FakeViewRouter`.

### `FanTest\_core\block\FakeViewRouter::getAll`

- Расположение: `unit/mock/_core/block/FakeServices.php:388`
- Сигнатура: `function getAll()`
- Описание: Получает, читает или вычисляет данные `all` в рамках этого метода класса `FanTest\_core\block\FakeViewRouter`.

### `FanTest\_core\block\FakeTab::__construct`

- Расположение: `unit/mock/_core/block/FakeServices.php:410`
- Сигнатура: `function __construct()`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `FanTest\_core\block\FakeTab`.

### `FanTest\_core\block\FakeTab::setCurrentBlock`

- Расположение: `unit/mock/_core/block/FakeServices.php:423`
- Сигнатура: `function setCurrentBlock($block)`
- Описание: Устанавливает, добавляет или сохраняет данные `current block` в рамках этого метода класса `FanTest\_core\block\FakeTab`.

### `FanTest\_core\block\FakeTab::setTabBlock`

- Расположение: `unit/mock/_core/block/FakeServices.php:438`
- Сигнатура: `function setTabBlock($block, $blockName)`
- Описание: Устанавливает, добавляет или сохраняет данные `tab block` в рамках этого метода класса `FanTest\_core\block\FakeTab`.

### `FanTest\_core\block\FakeTab::checkBlockStatus`

- Расположение: `unit/mock/_core/block/FakeServices.php:451`
- Сигнатура: `function checkBlockStatus(\fan\core\block\base $block)`
- Описание: Проверяет условие или валидирует данные `block status` и возвращает результат либо выбрасывает исключение.

### `FanTest\_core\block\FakeTab::getBlocksMetaByMain`

- Расположение: `unit/mock/_core/block/FakeServices.php:463`
- Сигнатура: `function getBlocksMetaByMain($blockName)`
- Описание: Получает, читает или вычисляет данные `blocks meta by main` в рамках этого метода класса `FanTest\_core\block\FakeTab`.

### `FanTest\_core\block\FakeTab::getViewClass`

- Расположение: `unit/mock/_core/block/FakeServices.php:473`
- Сигнатура: `function getViewClass()`
- Описание: Получает, читает или вычисляет данные `view class` в рамках этого метода класса `FanTest\_core\block\FakeTab`.

### `FanTest\_core\block\FakeTab::getViewDefiner`

- Расположение: `unit/mock/_core/block/FakeServices.php:483`
- Сигнатура: `function getViewDefiner()`
- Описание: Получает, читает или вычисляет данные `view definer` в рамках этого метода класса `FanTest\_core\block\FakeTab`.

### `FanTest\_core\block\FakeTab::getMainBlock`

- Расположение: `unit/mock/_core/block/FakeServices.php:493`
- Сигнатура: `function getMainBlock()`
- Описание: Получает, читает или вычисляет данные `main block` в рамках этого метода класса `FanTest\_core\block\FakeTab`.

### `FanTest\_core\block\FakeTab::setMainBlock`

- Расположение: `unit/mock/_core/block/FakeServices.php:505`
- Сигнатура: `function setMainBlock($block)`
- Описание: Устанавливает, добавляет или сохраняет данные `main block` в рамках этого метода класса `FanTest\_core\block\FakeTab`.

### `FanTest\_core\block\FakeTab::getTabBlock`

- Расположение: `unit/mock/_core/block/FakeServices.php:518`
- Сигнатура: `function getTabBlock($blockName, $allowException = true)`
- Описание: Получает, читает или вычисляет данные `tab block` в рамках этого метода класса `FanTest\_core\block\FakeTab`.

### `FanTest\_core\block\FakeTab::loadBlock`

- Расположение: `unit/mock/_core/block/FakeServices.php:530`
- Сигнатура: `function loadBlock($blockPath)`
- Описание: Получает, читает или вычисляет данные `block` в рамках этого метода класса `FanTest\_core\block\FakeTab`.

### `FanTest\_core\block\FakeTab::getSubscriber`

- Расположение: `unit/mock/_core/block/FakeServices.php:540`
- Сигнатура: `function getSubscriber()`
- Описание: Получает, читает или вычисляет данные `subscriber` в рамках этого метода класса `FanTest\_core\block\FakeTab`.

### `FanTest\_core\block\FakeReflector::__construct`

- Расположение: `unit/mock/_core/block/FakeServices.php:553`
- Сигнатура: `function __construct()`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `FanTest\_core\block\FakeReflector`.

### `FanTest\_core\block\FakeReflector::getParentPaths`

- Расположение: `unit/mock/_core/block/FakeServices.php:567`
- Сигнатура: `function getParentPaths($block)`
- Описание: Получает, читает или вычисляет данные `parent paths` в рамках этого метода класса `FanTest\_core\block\FakeReflector`.

### `FanTest\_core\block\FakeLocale::getLanguage`

- Расположение: `unit/mock/_core/block/FakeServices.php:582`
- Сигнатура: `function getLanguage()`
- Описание: Получает, читает или вычисляет данные `language` в рамках этого метода класса `FanTest\_core\block\FakeLocale`.

### `FanTest\_core\block\FakeMetaMaker::__construct`

- Расположение: `unit/mock/_core/block/FakeServices.php:609`
- Сигнатура: `function __construct($block, $data = [])`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `FanTest\_core\block\FakeMetaMaker`.

### `FanTest\_core\block\FakeMetaMaker::setContainerMeta`

- Расположение: `unit/mock/_core/block/FakeServices.php:624`
- Сигнатура: `function setContainerMeta($containerMeta)`
- Описание: Устанавливает, добавляет или сохраняет данные `container meta` в рамках этого метода класса `FanTest\_core\block\FakeMetaMaker`.

### `FanTest\_core\block\FakeMetaMaker::setMainBlockMeta`

- Расположение: `unit/mock/_core/block/FakeServices.php:635`
- Сигнатура: `function setMainBlockMeta()`
- Описание: Устанавливает, добавляет или сохраняет данные `main block meta` в рамках этого метода класса `FanTest\_core\block\FakeMetaMaker`.

### `FanTest\_core\block\FakeMetaMaker::assembleBlock`

- Расположение: `unit/mock/_core/block/FakeServices.php:646`
- Сигнатура: `function assembleBlock()`
- Описание: Создает, разбирает, форматирует или конвертирует данные `block` для этого метода класса `FanTest\_core\block\FakeMetaMaker`.

### `FanTest\_core\block\FakeMetaMaker::getMeta`

- Расположение: `unit/mock/_core/block/FakeServices.php:660`
- Сигнатура: `function getMeta(mixed $key = null, mixed $default = null)`
- Описание: Получает, читает или вычисляет данные `meta` в рамках этого метода класса `FanTest\_core\block\FakeMetaMaker`.

### `FanTest\_core\block\FakeMetaMaker::setMeta`

- Расположение: `unit/mock/_core/block/FakeServices.php:687`
- Сигнатура: `function setMeta(mixed $key, mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `meta` в рамках этого метода класса `FanTest\_core\block\FakeMetaMaker`.

### `FanTest\_core\block\FakeMetaMaker::getSource`

- Расположение: `unit/mock/_core/block/FakeServices.php:714`
- Сигнатура: `function getSource(mixed $key = null)`
- Описание: Получает, читает или вычисляет данные `source` в рамках этого метода класса `FanTest\_core\block\FakeMetaMaker`.

### `FanTest\_core\block\FakeMetaMaker::getMixSrcMeta`

- Расположение: `unit/mock/_core/block/FakeServices.php:737`
- Сигнатура: `function getMixSrcMeta()`
- Описание: Получает, читает или вычисляет данные `mix src meta` в рамках этого метода класса `FanTest\_core\block\FakeMetaMaker`.

## `unit/mock/_core/block/FrameworkStubs.php`

### `fan\core\base\meta\delayed::__construct`

- Расположение: `unit/mock/_core/block/FrameworkStubs.php:19`
- Сигнатура: `function __construct($value)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\base\meta\delayed`.

### `fan\core\base\meta\delayed::getValue`

- Расположение: `unit/mock/_core/block/FrameworkStubs.php:29`
- Сигнатура: `function getValue()`
- Описание: Получает, читает или вычисляет данные `value` в рамках этого метода класса `fan\core\base\meta\delayed`.

### `fan\core\base\meta\row::__construct`

- Расположение: `unit/mock/_core/block/FrameworkStubs.php:46`
- Сигнатура: `function __construct($data = [])`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\core\base\meta\row`.

### `fan\core\base\meta\row::offsetExists`

- Расположение: `unit/mock/_core/block/FrameworkStubs.php:60`
- Сигнатура: `function offsetExists(mixed $key): bool`
- Описание: Выполняет логику `offset exists` и возвращает вычисленный результат.

### `fan\core\base\meta\row::offsetGet`

- Расположение: `unit/mock/_core/block/FrameworkStubs.php:72`
- Сигнатура: `function offsetGet(mixed $key): mixed`
- Описание: Выполняет логику `offset get` и возвращает вычисленный результат.

### `fan\core\base\meta\row::offsetSet`

- Расположение: `unit/mock/_core/block/FrameworkStubs.php:85`
- Сигнатура: `function offsetSet(mixed $key, mixed $value): void`
- Описание: Выполняет workflow-логику `offset set`.

### `fan\core\base\meta\row::offsetUnset`

- Расположение: `unit/mock/_core/block/FrameworkStubs.php:101`
- Сигнатура: `function offsetUnset(mixed $key): void`
- Описание: Выполняет workflow-логику `offset unset`.

### `fan\core\base\meta\row::getIterator`

- Расположение: `unit/mock/_core/block/FrameworkStubs.php:111`
- Сигнатура: `function getIterator(): \Traversable`
- Описание: Получает, читает или вычисляет данные `iterator` в рамках этого метода класса `fan\core\base\meta\row`.

### `fan\core\base\meta\row::count`

- Расположение: `unit/mock/_core/block/FrameworkStubs.php:121`
- Сигнатура: `function count(): int`
- Описание: Выполняет логику `count` и возвращает вычисленный результат.

### `fan\core\base\meta\row::mergeData`

- Расположение: `unit/mock/_core/block/FrameworkStubs.php:133`
- Сигнатура: `function mergeData(array $data)`
- Описание: Устанавливает, добавляет или сохраняет данные `data` в рамках этого метода класса `fan\core\base\meta\row`.

### `fan\core\base\meta\row::toArray`

- Расположение: `unit/mock/_core/block/FrameworkStubs.php:146`
- Сигнатура: `function toArray()`
- Описание: Выполняет логику `to array` и возвращает вычисленный результат.

### `fan\project\exception\block\fatal::__construct`

- Расположение: `unit/mock/_core/block/FrameworkStubs.php:171`
- Сигнатура: `function __construct($block, $message = '', $code = 0, ?\Exception $previous = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\project\exception\block\fatal`.

### `fan\project\exception\block\fatal::getBlock`

- Расположение: `unit/mock/_core/block/FrameworkStubs.php:182`
- Сигнатура: `function getBlock()`
- Описание: Получает, читает или вычисляет данные `block` в рамках этого метода класса `fan\project\exception\block\fatal`.

### `fan\project\exception\block\local::__construct`

- Расположение: `unit/mock/_core/block/FrameworkStubs.php:200`
- Сигнатура: `function __construct($block, $message = '', $code = 0, ?\Exception $previous = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `fan\project\exception\block\local`.

### `fan\project\exception\block\local::getBlock`

- Расположение: `unit/mock/_core/block/FrameworkStubs.php:211`
- Сигнатура: `function getBlock()`
- Описание: Получает, читает или вычисляет данные `block` в рамках этого метода класса `fan\project\exception\block\local`.

## `unit/mock/_core/block/GlobalFunctions.php`

### `service`

- Расположение: `unit/mock/_core/block/GlobalFunctions.php:11`
- Сигнатура: `function service($name, $args = [])`
- Описание: Runs the service operation and returns its result.
- Параметры: mixed $name Logical name of the value or component being addressed.; mixed $args Input value for the args argument.
- Возвращает: mixed Returns the value produced by the operation.

### `role`

- Расположение: `unit/mock/_core/block/GlobalFunctions.php:25`
- Сигнатура: `function role($condition)`
- Описание: Runs the role operation and returns its result.
- Параметры: mixed $condition Input value for the condition argument.
- Возвращает: mixed Returns the value produced by the operation.

### `get_class_name`

- Расположение: `unit/mock/_core/block/GlobalFunctions.php:39`
- Сигнатура: `function get_class_name($class)`
- Описание: Retrieves class name used by the framework helper.
- Параметры: mixed $class Input value for the class argument.
- Возвращает: mixed Returns the value produced by the operation.

### `get_class_alt`

- Расположение: `unit/mock/_core/block/GlobalFunctions.php:57`
- Сигнатура: `function get_class_alt(mixed $value)`
- Описание: Retrieves class alt used by the framework helper.
- Параметры: mixed $value Value that should be applied or transformed.
- Возвращает: mixed Returns the value produced by the operation.

### `bootstrap::parsePath`

- Расположение: `unit/mock/_core/block/GlobalFunctions.php:76`
- Сигнатура: `function parsePath($path)`
- Описание: Создает, разбирает, форматирует или конвертирует данные `path` для этого метода класса `bootstrap`.

### `bootstrap::getInitializer`

- Расположение: `unit/mock/_core/block/GlobalFunctions.php:86`
- Сигнатура: `function getInitializer()`
- Описание: Получает, читает или вычисляет данные `initializer` в рамках этого метода класса `bootstrap`.

### `bootstrap::setServiceParam`

- Расположение: `unit/mock/_core/block/GlobalFunctions.php:99`
- Сигнатура: `function setServiceParam($class)`
- Описание: Устанавливает, добавляет или сохраняет данные `service param` в рамках этого метода класса `bootstrap`.

### `bootstrap::logError`

- Расположение: `unit/mock/_core/block/GlobalFunctions.php:116`
- Сигнатура: `function logError($message)`
- Описание: Выполняет workflow-логику `log error`.

## `unit/mock/_core/block/TestableBaseBlock.php`

### `FanTest\_core\block\TestableBaseBlock::useMeta`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:24`
- Сигнатура: `function useMeta(array $meta)`
- Описание: Выполняет workflow-логику `use meta`.

### `FanTest\_core\block\TestableBaseBlock::_createMetaMaker`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:34`
- Сигнатура: `function _createMetaMaker()`
- Описание: Выполняет логику `create meta maker` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::getDynamicMeta`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:47`
- Сигнатура: `function getDynamicMeta($meta)`
- Описание: Получает, читает или вычисляет данные `dynamic meta` в рамках этого метода класса `FanTest\_core\block\TestableBaseBlock`.

### `FanTest\_core\block\TestableBaseBlock::_transferor`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:59`
- Сигнатура: `function _transferor()`
- Описание: Выполняет workflow-логику `transferor`.

### `FanTest\_core\block\TestableBaseBlock::_postCreate`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:69`
- Сигнатура: `function _postCreate()`
- Описание: Выполняет workflow-логику `post create`.

### `FanTest\_core\block\TestableBaseBlock::_preOutput`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:79`
- Сигнатура: `function _preOutput()`
- Описание: Выполняет workflow-логику `pre output`.

### `FanTest\_core\block\TestableBaseBlock::_doRoleOperations`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:89`
- Сигнатура: `function _doRoleOperations()`
- Описание: Выполняет workflow-логику `do role operations`.

### `FanTest\_core\block\TestableBaseBlock::exposeSetMeta`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:102`
- Сигнатура: `function exposeSetMeta(mixed $key, mixed $value)`
- Описание: Выполняет логику `expose set meta` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::exposeAddMeta`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:114`
- Сигнатура: `function exposeAddMeta(mixed $value)`
- Описание: Выполняет логику `expose add meta` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::exposeSetMetaVar`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:127`
- Сигнатура: `function exposeSetMetaVar(mixed $key, mixed $value)`
- Описание: Выполняет логику `expose set meta var` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::exposeMakeDynamicMeta`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:139`
- Сигнатура: `function exposeMakeDynamicMeta($force)`
- Описание: Выполняет логику `expose make dynamic meta` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::exposeSetRootBlockParameters`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:152`
- Сигнатура: `function exposeSetRootBlockParameters(?\fan\core\block\base $root = null, $rootKeys = [])`
- Описание: Выполняет логику `expose set root block parameters` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::exposeSetTplVarsByMeta`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:164`
- Сигнатура: `function exposeSetTplVarsByMeta($tplVars)`
- Описание: Выполняет логику `expose set tpl vars by meta` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::exposePreparseMeta`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:174`
- Сигнатура: `function exposePreparseMeta()`
- Описание: Выполняет логику `expose preparse meta` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::exposeSetTemplateInternal`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:186`
- Сигнатура: `function exposeSetTemplateInternal($templateName = '')`
- Описание: Выполняет логику `expose set template internal` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::exposeGetTplSuffixes`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:198`
- Сигнатура: `function exposeGetTplSuffixes($separator = '_')`
- Описание: Выполняет логику `expose get tpl suffixes` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::exposeCheckTemplate`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:213`
- Сигнатура: `function exposeCheckTemplate($blockPath, $templateName, $suffixes, $extension = 'tpl')`
- Описание: Выполняет логику `expose check template` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::exposeSetEmbeddedBlocks`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:223`
- Сигнатура: `function exposeSetEmbeddedBlocks()`
- Описание: Выполняет логику `expose set embedded blocks` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::exposeGetBlock`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:236`
- Сигнатура: `function exposeGetBlock($blockName, $allowException = true)`
- Описание: Выполняет логику `expose get block` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::exposeMakeBlockException`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:252`
- Сигнатура: `function exposeMakeBlockException($logErrMsg, $type = 'local', $exceptionDbOper = null, $code = E_USER_NOTICE, $previous = null)`
- Описание: Выполняет логику `expose make block exception` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::exposeParseClassName`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:265`
- Сигнатура: `function exposeParseClassName($blockPath, $allowException = true)`
- Описание: Выполняет логику `expose parse class name` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::exposeSetCacheRole`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:277`
- Сигнатура: `function exposeSetCacheRole(mixed $role)`
- Описание: Выполняет логику `expose set cache role` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::exposeCallOrdinaryDelegate`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:291`
- Сигнатура: `function exposeCallOrdinaryDelegate($object, $method, $args)`
- Описание: Выполняет логику `expose call ordinary delegate` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::exposeCallIdentifiedDelegate`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:305`
- Сигнатура: `function exposeCallIdentifiedDelegate($object, $method, $args)`
- Описание: Выполняет логику `expose call identified delegate` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::exposeSetViewVar`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:318`
- Сигнатура: `function exposeSetViewVar($key, mixed $value)`
- Описание: Выполняет логику `expose set view var` и возвращает вычисленный результат.

### `FanTest\_core\block\TestableBaseBlock::setEmbeddedBlocksForTest`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:330`
- Сигнатура: `function setEmbeddedBlocksForTest(array $blocks)`
- Описание: Устанавливает, добавляет или сохраняет данные `embedded blocks for test` в рамках этого метода класса `FanTest\_core\block\TestableBaseBlock`.

### `FanTest\_core\block\TestableBaseBlock::setViewForTest`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:343`
- Сигнатура: `function setViewForTest($view)`
- Описание: Устанавливает, добавляет или сохраняет данные `view for test` в рамках этого метода класса `FanTest\_core\block\TestableBaseBlock`.

### `FanTest\_core\block\TestableBaseBlock::setMetaRowForTest`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:356`
- Сигнатура: `function setMetaRowForTest(\fan\core\base\meta\row $meta)`
- Описание: Устанавливает, добавляет или сохраняет данные `meta row for test` в рамках этого метода класса `FanTest\_core\block\TestableBaseBlock`.

### `FanTest\_core\block\FakeRootBlock::setMetaTag`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:375`
- Сигнатура: `function setMetaTag(mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `meta tag` в рамках этого метода класса `FanTest\_core\block\FakeRootBlock`.

### `FanTest\_core\block\FakeRootBlock::setExternalCss`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:387`
- Сигнатура: `function setExternalCss(mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `external css` в рамках этого метода класса `FanTest\_core\block\FakeRootBlock`.

### `FanTest\_core\block\FakeRootBlock::setEmbedCssByMeta`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:399`
- Сигнатура: `function setEmbedCssByMeta(mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `embed css by meta` в рамках этого метода класса `FanTest\_core\block\FakeRootBlock`.

### `FanTest\_core\block\FakeRootBlock::setExternalJs`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:411`
- Сигнатура: `function setExternalJs(mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `external js` в рамках этого метода класса `FanTest\_core\block\FakeRootBlock`.

### `FanTest\_core\block\FakeRootBlock::setEmbedJs`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:424`
- Сигнатура: `function setEmbedJs(mixed $value, $position)`
- Описание: Устанавливает, добавляет или сохраняет данные `embed js` в рамках этого метода класса `FanTest\_core\block\FakeRootBlock`.

### `FanTest\_core\block\ObjectWithToArray::__construct`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:443`
- Сигнатура: `function __construct(array $data)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `FanTest\_core\block\ObjectWithToArray`.

### `FanTest\_core\block\ObjectWithToArray::toArray`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:453`
- Сигнатура: `function toArray()`
- Описание: Выполняет логику `to array` и возвращает вычисленный результат.

### `FanTest\_core\block\DelegateTarget::callMe`

- Расположение: `unit/mock/_core/block/TestableBaseBlock.php:468`
- Сигнатура: `function callMe()`
- Описание: Выполняет логику `call me` и возвращает вычисленный результат.

## `unit/mock/_core/exception/ExceptionDoubles.php`

### `FanTest\_core\exception\FakeRequestService::getInfoString`

- Расположение: `unit/mock/_core/exception/ExceptionDoubles.php:24`
- Сигнатура: `function getInfoString()`
- Описание: Получает, читает или вычисляет данные `info string` в рамках этого метода класса `FanTest\_core\exception\FakeRequestService`.

### `FanTest\_core\exception\FakeErrorService::logExceptionMessage`

- Расположение: `unit/mock/_core/exception/ExceptionDoubles.php:44`
- Сигнатура: `function logExceptionMessage(string $message, string $header = '', string $note = '')`
- Описание: Выполняет workflow-логику `log exception message`.
- Побочные эффекты: меняет HTTP/session состояние

### `FanTest\_core\exception\FakeErrorService::logDatabaseError`

- Расположение: `unit/mock/_core/exception/ExceptionDoubles.php:60`
- Сигнатура: `function logDatabaseError($connectionName, string $operation, $errorMessage, int|float $errorNum, $parsedSql)`
- Описание: Выполняет workflow-логику `log database error`.

### `FanTest\_core\exception\TestService::__construct`

- Расположение: `unit/mock/_core/exception/ExceptionDoubles.php:76`
- Сигнатура: `function __construct(?string $logType = 'service', ?string $dbOper = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `FanTest\_core\exception\TestService`.

### `FanTest\_core\exception\TestService::isSingleton`

- Расположение: `unit/mock/_core/exception/ExceptionDoubles.php:87`
- Сигнатура: `function isSingleton()`
- Описание: Проверяет условие или валидирует данные `singleton` и возвращает результат либо выбрасывает исключение.

### `FanTest\_core\exception\TestService::getConfigType`

- Расположение: `unit/mock/_core/exception/ExceptionDoubles.php:97`
- Сигнатура: `function getConfigType()`
- Описание: Получает, читает или вычисляет данные `config type` в рамках этого метода класса `FanTest\_core\exception\TestService`.

### `FanTest\_core\exception\TestService::setConfigValue`

- Расположение: `unit/mock/_core/exception/ExceptionDoubles.php:111`
- Сигнатура: `function setConfigValue(\fan\core\service\config\row $row, mixed $key, mixed $value)`
- Описание: Устанавливает, добавляет или сохраняет данные `config value` в рамках этого метода класса `FanTest\_core\exception\TestService`.

### `FanTest\_core\exception\TestService::resetConfigValue`

- Расположение: `unit/mock/_core/exception/ExceptionDoubles.php:124`
- Сигнатура: `function resetConfigValue(\fan\core\service\config\row $row, mixed $key)`
- Описание: Удаляет или сбрасывает состояние `config value` для этого метода класса `FanTest\_core\exception\TestService`.

### `FanTest\_core\exception\TestService::mergeConfigData`

- Расположение: `unit/mock/_core/exception/ExceptionDoubles.php:138`
- Сигнатура: `function mergeConfigData(\fan\core\service\config\row $row, array|\fan\core\service\config\row $data, bool $priority = true)`
- Описание: Устанавливает, добавляет или сохраняет данные `config data` в рамках этого метода класса `FanTest\_core\exception\TestService`.

### `FanTest\_core\exception\TestDatabaseService::__construct`

- Расположение: `unit/mock/_core/exception/ExceptionDoubles.php:152`
- Сигнатура: `function __construct(?string $connectionName = 'main', ?string $logType = 'nothing')`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `FanTest\_core\exception\TestDatabaseService`.

### `FanTest\_core\exception\TestEntity::__construct`

- Расположение: `unit/mock/_core/exception/ExceptionDoubles.php:164`
- Сигнатура: `function __construct()`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `FanTest\_core\exception\TestEntity`.

### `FanTest\_core\exception\TestEntity::__toString`

- Расположение: `unit/mock/_core/exception/ExceptionDoubles.php:173`
- Сигнатура: `function __toString()`
- Описание: Реализует магическое поведение PHP для этого метода класса `FanTest\_core\exception\TestEntity`.

### `fan\project\service\error::instance`

- Расположение: `unit/mock/_core/exception/ExceptionDoubles.php:191`
- Сигнатура: `function instance()`
- Описание: Выполняет логику `instance` и возвращает вычисленный результат.

### `fan\project\service\error::reset`

- Расположение: `unit/mock/_core/exception/ExceptionDoubles.php:204`
- Сигнатура: `function reset()`
- Описание: Удаляет или сбрасывает состояние `состояние` для этого метода класса `fan\project\service\error`.

## `unit/mock/_core/exception/RuntimeStubs.php`

### `bootstrap::getInitializer`

- Расположение: `unit/mock/_core/exception/RuntimeStubs.php:14`
- Сигнатура: `function getInitializer()`
- Описание: Получает, читает или вычисляет данные `initializer` в рамках этого метода класса `bootstrap`.

### `bootstrap::setServiceParam`

- Расположение: `unit/mock/_core/exception/RuntimeStubs.php:27`
- Сигнатура: `function setServiceParam($class)`
- Описание: Устанавливает, добавляет или сохраняет данные `service param` в рамках этого метода класса `bootstrap`.

### `bootstrap::logError`

- Расположение: `unit/mock/_core/exception/RuntimeStubs.php:44`
- Сигнатура: `function logError($message): void`
- Описание: Выполняет workflow-логику `log error`.

## `unit/mock/_core/exception/TestException.php`

### `FanTest\_core\exception\TestException::__construct`

- Расположение: `unit/mock/_core/exception/TestException.php:20`
- Сигнатура: `function __construct($message, $nextDbOper = null, $code = E_USER_ERROR, ?\Exception $previous = null)`
- Описание: Инициализирует состояние объекта и его зависимости для этого метода класса `FanTest\_core\exception\TestException`.

### `FanTest\_core\exception\TestException::_defineDbOper`

- Расположение: `unit/mock/_core/exception/TestException.php:34`
- Сигнатура: `function _defineDbOper($dbOper = null)`
- Описание: Выполняет логику `define db oper` и возвращает вычисленный результат.

### `FanTest\_core\exception\TestException::exposeDefineDbOper`

- Расположение: `unit/mock/_core/exception/TestException.php:46`
- Сигнатура: `function exposeDefineDbOper($dbOper)`
- Описание: Выполняет логику `expose define db oper` и возвращает вычисленный результат.
