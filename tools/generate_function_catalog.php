<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$sourceRoots = ['_core', '_project', 'htdocs', 'tools', 'unit'];
$excludedPrefixes = [
    $root . '/libraries/',
    $root . '/vendor/',
    $root . '/legacy_assets/',
    $root . '/.git/',
];

$files = [];
foreach ($sourceRoots as $sourceRoot) {
    $path = $root . '/' . $sourceRoot;
    if (!is_dir($path)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $filePath = $file->getPathname();
        foreach ($excludedPrefixes as $prefix) {
            if (str_starts_with($filePath, $prefix)) {
                continue 2;
            }
        }
        $files[] = $filePath;
    }
}
sort($files);

$entries = [];
foreach ($files as $file) {
    foreach (collectFunctions($file, $root) as $entry) {
        $entries[] = $entry;
    }
}

$target = $root . '/doc/function-catalog.md';
file_put_contents($target, renderCatalog($entries));
printf("documented_functions=%d\n%s\n", count($entries), $target);

function collectFunctions(string $file, string $root): array
{
    $code = file_get_contents($file);
    if (!is_string($code)) {
        return [];
    }

    $tokens = PhpToken::tokenize($code, TOKEN_PARSE);
    $namespace = '';
    $classStack = [];
    $pendingClass = null;
    $pendingClassDepth = null;
    $braceDepth = 0;
    $entries = [];
    $tokenCount = count($tokens);

    for ($i = 0; $i < $tokenCount; $i++) {
        $token = $tokens[$i];
        if ($token->id === T_NAMESPACE) {
            $namespace = readNamespace($tokens, $i + 1);
            continue;
        }

        if (in_array($token->id, [T_CLASS, T_INTERFACE, T_TRAIT], true) && !isAnonymousClass($tokens, $i)) {
            $className = readNamedDeclaration($tokens, $i + 1);
            if ($className !== null) {
                $pendingClass = ltrim($namespace . '\\' . $className, '\\');
            }
            continue;
        }

        if ($token->text === '{') {
            $braceDepth++;
            if ($pendingClass !== null) {
                $pendingClassDepth = $braceDepth;
                $classStack[] = [
                    'name' => $pendingClass,
                    'depth' => $pendingClassDepth,
                ];
                $pendingClass = null;
                $pendingClassDepth = null;
            }
            continue;
        }

        if ($token->text === '}') {
            while ($classStack !== [] && end($classStack)['depth'] === $braceDepth) {
                array_pop($classStack);
            }
            $braceDepth--;
            continue;
        }

        if ($token->id !== T_FUNCTION || isClosure($tokens, $i)) {
            continue;
        }

        $name = readNamedDeclaration($tokens, $i + 1);
        if ($name === null) {
            continue;
        }

        $className = $classStack === [] ? null : end($classStack)['name'];
        $functionNamespace = $className === null ? $namespace : '';
        [$signature, $body, $endLine] = readFunctionSignatureAndBody($tokens, $i);
        $docBlock = readPreviousDocBlock($tokens, $i);

        $entries[] = [
            'file' => ltrim(str_replace($root . '/', '', $file), '/'),
            'line' => $token->line,
            'endLine' => $endLine,
            'namespace' => $namespace,
            'class' => $className,
            'name' => $name,
            'fqName' => $className === null
                ? ltrim($functionNamespace . '\\' . $name, '\\')
                : $className . '::' . $name,
            'signature' => normalizeWhitespace($signature),
            'visibility' => readVisibility($tokens, $i),
            'doc' => parseDocBlock($docBlock),
            'summary' => inferSummary($name, $className, $docBlock, $body),
            'effects' => inferEffects($body),
        ];
    }

    return $entries;
}

/**
 * Runs the render catalog operation and returns its result.
 *
 * @param array $entries Input value for the entries argument.
 *
 * @return string Returns the string representation produced by the operation.
 */
function renderCatalog(array $entries): string
{
    $generatedAt = gmdate('Y-m-d H:i:s') . ' UTC';
    $byFile = [];
    foreach ($entries as $entry) {
        $byFile[$entry['file']][] = $entry;
    }

    $lines = [
        '# Каталог функций',
        '',
        'Документ сгенерирован по PHP-токенам. Он описывает функции и методы production-кода, tools, htdocs и unit-тестов, исключая vendor, libraries и legacy assets.',
        '',
        '- Сгенерировано: ' . $generatedAt,
        '- Описано функций и методов: ' . count($entries),
        '- Файлов с функциями: ' . count($byFile),
        '',
    ];

    foreach ($byFile as $file => $fileEntries) {
        $lines[] = '## `' . $file . '`';
        $lines[] = '';

        foreach ($fileEntries as $entry) {
            $lines[] = '### `' . $entry['fqName'] . '`';
            $lines[] = '';
            $lines[] = '- Расположение: `' . $entry['file'] . ':' . $entry['line'] . '`';
            $lines[] = '- Сигнатура: `' . $entry['signature'] . '`';
            $lines[] = '- Описание: ' . $entry['summary'];
            if ($entry['doc']['params'] !== []) {
                $lines[] = '- Параметры: ' . implode('; ', $entry['doc']['params']);
            }
            if ($entry['doc']['return'] !== '') {
                $lines[] = '- Возвращает: ' . $entry['doc']['return'];
            }
            if ($entry['effects'] !== []) {
                $lines[] = '- Побочные эффекты: ' . implode('; ', $entry['effects']);
            }
            $lines[] = '';
        }
    }

    return implode("\n", $lines);
}

/**
 * Retrieves namespace used by the framework helper.
 *
 * @param array $tokens Input value for the tokens argument.
 * @param int $offset Input value for the offset argument.
 *
 * @return string Returns the string representation produced by the operation.
 */
function readNamespace(array $tokens, int $offset): string
{
    $parts = [];
    for ($i = $offset, $count = count($tokens); $i < $count; $i++) {
        $token = $tokens[$i];
        if ($token->text === ';' || $token->text === '{') {
            break;
        }
        if ($token->id === T_STRING || $token->id === T_NAME_QUALIFIED || $token->text === '\\') {
            $parts[] = $token->text;
        }
    }
    return implode('', $parts);
}

/**
 * Retrieves named declaration used by the framework helper.
 *
 * @param array $tokens Input value for the tokens argument.
 * @param int $offset Input value for the offset argument.
 *
 * @return ?string Returns the string representation produced by the operation.
 */
function readNamedDeclaration(array $tokens, int $offset): ?string
{
    for ($i = $offset, $count = count($tokens); $i < $count; $i++) {
        $token = $tokens[$i];
        if ($token->isIgnorable() || $token->text === '&') {
            continue;
        }
        return $token->id === T_STRING ? $token->text : null;
    }
    return null;
}

function isAnonymousClass(array $tokens, int $classIndex): bool
{
    for ($i = $classIndex - 1; $i >= 0; $i--) {
        $token = $tokens[$i];
        if ($token->isIgnorable()) {
            continue;
        }
        return $token->id === T_NEW;
    }
    return false;
}

function isClosure(array $tokens, int $functionIndex): bool
{
    $next = readNamedDeclaration($tokens, $functionIndex + 1);
    return $next === null;
}

/**
 * Retrieves function signature and body used by the framework helper.
 *
 * @param array $tokens Input value for the tokens argument.
 * @param int $functionIndex Input value for the function index argument.
 *
 * @return array Returns the structured data produced by the operation.
 */
function readFunctionSignatureAndBody(array $tokens, int $functionIndex): array
{
    $signature = '';
    $body = '';
    $braceDepth = 0;
    $insideBody = false;
    $endLine = $tokens[$functionIndex]->line;

    for ($i = $functionIndex, $count = count($tokens); $i < $count; $i++) {
        $token = $tokens[$i];
        $endLine = $token->line;
        if (!$insideBody) {
            if ($token->text === ';') {
                return [$signature . ';', '', $endLine];
            }
            if ($token->text === '{') {
                $insideBody = true;
                $braceDepth = 1;
                continue;
            }
            $signature .= $token->text;
            continue;
        }

        if ($token->text === '{') {
            $braceDepth++;
        } elseif ($token->text === '}') {
            $braceDepth--;
            if ($braceDepth === 0) {
                return [$signature, $body, $endLine];
            }
        }
        $body .= $token->text;
    }

    return [$signature, $body, $endLine];
}

/**
 * Retrieves previous doc block used by the framework helper.
 *
 * @param array $tokens Input value for the tokens argument.
 * @param int $index Input value for the index argument.
 *
 * @return string Returns the string representation produced by the operation.
 */
function readPreviousDocBlock(array $tokens, int $index): string
{
    for ($i = $index - 1; $i >= 0; $i--) {
        $token = $tokens[$i];
        if ($token->isIgnorable() && $token->id !== T_DOC_COMMENT) {
            continue;
        }
        return $token->id === T_DOC_COMMENT ? $token->text : '';
    }
    return '';
}

/**
 * Retrieves visibility used by the framework helper.
 *
 * @param array $tokens Input value for the tokens argument.
 * @param int $functionIndex Input value for the function index argument.
 *
 * @return string Returns the string representation produced by the operation.
 */
function readVisibility(array $tokens, int $functionIndex): string
{
    $parts = [];
    for ($i = $functionIndex - 1; $i >= 0; $i--) {
        $token = $tokens[$i];
        if ($token->isIgnorable() || $token->id === T_DOC_COMMENT) {
            continue;
        }
        if (in_array($token->id, [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_ABSTRACT, T_FINAL], true)) {
            $parts[] = strtolower($token->text);
            continue;
        }
        break;
    }
    return implode(' ', array_reverse($parts));
}

function parseDocBlock(string $docBlock): array
{
    if ($docBlock === '') {
        return [
            'summary' => '',
            'params' => [],
            'return' => '',
        ];
    }

    $clean = preg_replace('/^\\s*\\/\\*\\*|\\*\\/\\s*$/', '', $docBlock);
    $lines = preg_split('/\\R/', (string)$clean);
    $summary = '';
    $params = [];
    $return = '';

    foreach ($lines as $line) {
        $line = trim(preg_replace('/^\\s*\\*\\s?/', '', $line));
        if ($line === '') {
            continue;
        }
        if (str_starts_with($line, '@param')) {
            $params[] = normalizeWhitespace(substr($line, 6));
            continue;
        }
        if (str_starts_with($line, '@return')) {
            $return = normalizeWhitespace(substr($line, 7));
            continue;
        }
        if ($summary === '' && !str_starts_with($line, '@')) {
            $summary = $line;
        }
    }

    return [
        'summary' => $summary,
        'params' => $params,
        'return' => $return,
    ];
}

/**
 * Runs the infer summary operation and returns its result.
 *
 * @param string $name Logical name of the value or component being addressed.
 * @param ?string $className Input value for the class name argument.
 * @param string $docBlock Input value for the doc block argument.
 * @param string $body Input value for the body argument.
 *
 * @return string Returns the string representation produced by the operation.
 */
function inferSummary(string $name, ?string $className, string $docBlock, string $body): string
{
    $doc = parseDocBlock($docBlock);
    if ($doc['summary'] !== '' && !preg_match('/^(Description of|Method for|Get|Set)$/i', $doc['summary'])) {
        return rtrim($doc['summary'], '.') . '.';
    }

    $context = $className === null ? 'глобальной функции' : 'метода класса `' . $className . '`';
    $readableName = readableFunctionTarget($name);

    if ($name === '__construct') {
        return 'Инициализирует состояние объекта и его зависимости для этого ' . $context . '.';
    }
    if ($name === '__destruct') {
        return 'Завершает работу объекта и выполняет отложенную очистку для этого ' . $context . '.';
    }
    if (str_starts_with($name, '__')) {
        return 'Реализует магическое поведение PHP для этого ' . $context . '.';
    }
    if (preg_match('/^(get|read|load|fetch|find)/i', $name)) {
        return 'Получает, читает или вычисляет данные `' . $readableName . '` в рамках этого ' . $context . '.';
    }
    if (preg_match('/^(set|add|put|save|write|merge)/i', $name)) {
        return 'Устанавливает, добавляет или сохраняет данные `' . $readableName . '` в рамках этого ' . $context . '.';
    }
    if (preg_match('/^(is|has|check|validate|can)/i', $name)) {
        return 'Проверяет условие или валидирует данные `' . $readableName . '` и возвращает результат либо выбрасывает исключение.';
    }
    if (preg_match('/^(make|create|build|assemble|parse|format|convert|encode|decode)/i', $name)) {
        return 'Создает, разбирает, форматирует или конвертирует данные `' . $readableName . '` для этого ' . $context . '.';
    }
    if (preg_match('/^(delete|remove|reset|clear|destroy)/i', $name)) {
        return 'Удаляет или сбрасывает состояние `' . $readableName . '` для этого ' . $context . '.';
    }
    if (preg_match('/^(init|run|start|finish|handle|process)/i', $name)) {
        return 'Запускает или обрабатывает workflow `' . $readableName . '` для этого ' . $context . '.';
    }
    if (str_contains($body, 'return')) {
        return 'Выполняет логику `' . $readableName . '` и возвращает вычисленный результат.';
    }
    return 'Выполняет workflow-логику `' . $readableName . '`.';
}

function inferEffects(string $body): array
{
    $effects = [];
    $checks = [
        '/\\b(?:file_put_contents|fwrite|unlink|mkdir|rename|copy|chmod)\\b/' => 'работает с файловой системой',
        '/\\b(?:header|setcookie|session_start|session_destroy)\\b/' => 'меняет HTTP/session состояние',
        '/\\b(?:service|role|ge|gr)\\s*\\(/' => 'использует service locator/helpers фреймворка',
        '/\\b(?:throw new|throw\\s+)/' => 'может выбрасывать исключения',
        '/\\b(?:trigger_error|logErrorMessage|logExceptionMessage|logDatabaseError)\\b/' => 'логирует или сообщает об ошибках',
        '/\\b(?:json_encode|json_decode|serialize|unserialize)\\b/' => 'сериализует или десериализует данные',
        '/\\b(?:mysqli_|mysql_|PDO|Execute|query)\\b/i' => 'выполняет database операции',
        '/\\b(?:curl_|SoapClient|mail\\s*\\()\\b/i' => 'использует внешнюю коммуникацию',
        '/\\$_(?:GET|POST|COOKIE|SESSION|SERVER|FILES|REQUEST)\\b/' => 'читает PHP superglobals',
    ];

    foreach ($checks as $pattern => $label) {
        if (preg_match($pattern, $body)) {
            $effects[] = $label;
        }
    }

    return $effects;
}

/**
 * Runs the normalize whitespace operation and returns its result.
 *
 * @param string $value Value that should be applied or transformed.
 *
 * @return string Returns the string representation produced by the operation.
 */
function normalizeWhitespace(string $value): string
{
    return trim(preg_replace('/\\s+/', ' ', $value));
}

/**
 * Retrieves able function target used by the framework helper.
 *
 * @param string $name Logical name of the value or component being addressed.
 *
 * @return string Returns the string representation produced by the operation.
 */
function readableFunctionTarget(string $name): string
{
    $exactTargets = [
        'get' => 'значение',
        'set' => 'значение',
        'add' => 'элемент',
        'put' => 'значение',
        'save' => 'данные',
        'delete' => 'данные',
        'remove' => 'элемент',
        'reset' => 'состояние',
        'init' => 'инициализацию',
        'run' => 'выполнение',
        'parse' => 'данные',
    ];
    if (isset($exactTargets[strtolower($name)])) {
        return $exactTargets[strtolower($name)];
    }

    $target = preg_replace(
        '/^(get|read|load|fetch|find|set|add|put|save|write|merge|is|has|check|validate|can|make|create|build|assemble|parse|format|convert|encode|decode|delete|remove|reset|clear|destroy|init|run|start|finish|handle|process)_?/i',
        '',
        $name
    );
    $target = $target === '' ? $name : $target;
    $target = trim(preg_replace('/(?<!^)[A-Z]/', ' $0', str_replace('_', ' ', $target)));
    return strtolower($target === '' ? $name : $target);
}
