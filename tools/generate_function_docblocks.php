<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$sourceRoots = ['_core', '_project', 'htdocs', 'cli', 'tools', 'unit'];
$excludedPrefixes = [
    $root . '/libraries/',
    $root . '/vendor/',
    $root . '/legacy_assets/',
    $root . '/.git/',
];

$files = collectPhpFiles($root, $sourceRoots, $excludedPrefixes);
$updatedFiles = 0;
$updatedFunctions = 0;

foreach ($files as $file) {
    $code = file_get_contents($file);
    if (!is_string($code)) {
        continue;
    }

    $replacements = collectDocblockReplacements($code);
    if ($replacements === []) {
        continue;
    }

    usort($replacements, static fn(array $a, array $b): int => $b['start'] <=> $a['start']);
    foreach ($replacements as $replacement) {
        $code = substr_replace(
            $code,
            $replacement['content'],
            $replacement['start'],
            $replacement['end'] - $replacement['start']
        );
    }

    file_put_contents($file, $code);
    $updatedFiles++;
    $updatedFunctions += count($replacements);
}

printf("updated_files=%d documented_functions=%d\n", $updatedFiles, $updatedFunctions);

function collectPhpFiles(string $root, array $sourceRoots, array $excludedPrefixes): array
{
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
    return $files;
}

function collectDocblockReplacements(string $code): array
{
    $tokens = PhpToken::tokenize($code, TOKEN_PARSE);
    $namespace = '';
    $classStack = [];
    $pendingClass = null;
    $braceDepth = 0;
    $replacements = [];
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
                $classStack[] = [
                    'name' => $pendingClass,
                    'depth' => $braceDepth,
                ];
                $pendingClass = null;
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

        $declarationStartIndex = findDeclarationStartIndex($tokens, $i);
        $declarationStart = $tokens[$declarationStartIndex]->pos;
        $lineStart = readLineStartOffset($code, $declarationStart);
        $indent = readLineIndent($code, $declarationStart);
        $existingDoc = findExistingDocBlock($code, $tokens, $declarationStartIndex);
        [$signature, $body] = readFunctionSignatureAndBody($tokens, $i);
        $className = $classStack === [] ? null : end($classStack)['name'];
        $docBlock = buildDocBlock($name, $className, $signature, $body, $indent, $existingDoc['text'] ?? '');

        $replacements[] = [
            'start' => $existingDoc['start'] ?? $lineStart,
            'end' => $existingDoc['end'] ?? $lineStart,
            'content' => $docBlock . "\n" . $indent,
        ];
    }

    return $replacements;
}

/**
 * Retrieves declaration start index used by the framework helper.
 *
 * @param array $tokens Input value for the tokens argument.
 * @param int $functionIndex Input value for the function index argument.
 *
 * @return int Returns the numeric result produced by the operation.
 */
function findDeclarationStartIndex(array $tokens, int $functionIndex): int
{
    $start = $functionIndex;
    for ($i = $functionIndex - 1; $i >= 0; $i--) {
        $token = $tokens[$i];
        if ($token->isIgnorable()) {
            continue;
        }
        if (in_array($token->id, [T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_ABSTRACT, T_FINAL], true)) {
            $start = $i;
            continue;
        }
        break;
    }
    return $start;
}

/**
 * Retrieves existing doc block used by the framework helper.
 *
 * @param string $code Input value for the code argument.
 * @param array $tokens Input value for the tokens argument.
 * @param int $declarationStartIndex Input value for the declaration start index argument.
 *
 * @return ?array Returns the structured data produced by the operation.
 */
function findExistingDocBlock(string $code, array $tokens, int $declarationStartIndex): ?array
{
    for ($i = $declarationStartIndex - 1; $i >= 0; $i--) {
        $token = $tokens[$i];
        if ($token->id === T_WHITESPACE) {
            continue;
        }
        if ($token->id !== T_DOC_COMMENT) {
            return null;
        }

        $end = $tokens[$declarationStartIndex]->pos;
        return [
            'start' => readLineStartOffset($code, $token->pos),
            'end' => $end,
            'text' => $token->text,
        ];
    }

    return null;
}

/**
 * Builds doc block for the framework helper.
 *
 * @param string $name Logical name of the value or component being addressed.
 * @param ?string $className Input value for the class name argument.
 * @param string $signature Input value for the signature argument.
 * @param string $body Input value for the body argument.
 * @param string $indent Input value for the indent argument.
 * @param string $oldDocBlock Input value for the old doc block argument.
 *
 * @return string Returns the string representation produced by the operation.
 */
function buildDocBlock(string $name, ?string $className, string $signature, string $body, string $indent, string $oldDocBlock): string
{
    $summary = inferSummary($name, $className, $body);
    $params = extractParameters($signature);
    $returnType = inferReturnType($name, $signature, $body);
    $preservedTags = extractPreservedTags($oldDocBlock);

    $lines = [
        $indent . '/**',
        $indent . ' * ' . $summary,
    ];

    if ($params !== []) {
        $lines[] = $indent . ' *';
        foreach ($params as $param) {
            $lines[] = sprintf(
                '%s * @param %s $%s %s',
                $indent,
                $param['type'],
                $param['name'],
                describeParameter($param['name'])
            );
        }
    }

    if ($returnType !== null) {
        $lines[] = $indent . ' *';
        $lines[] = sprintf('%s * @return %s %s', $indent, $returnType, describeReturn($name, $returnType));
    }

    if ($preservedTags !== []) {
        $lines[] = $indent . ' *';
        foreach ($preservedTags as $tag) {
            $lines[] = $indent . ' * ' . $tag;
        }
    }

    $lines[] = $indent . ' */';
    return implode("\n", $lines);
}

/**
 * Runs the infer summary operation and returns its result.
 *
 * @param string $name Logical name of the value or component being addressed.
 * @param ?string $className Input value for the class name argument.
 * @param string $body Input value for the body argument.
 *
 * @return string Returns the string representation produced by the operation.
 */
function inferSummary(string $name, ?string $className, string $body): string
{
    $target = readableFunctionTarget($name);
    $context = $className === null ? 'framework helper' : 'current component';

    return match (true) {
        $name === '__construct' => 'Initializes the object state and required collaborators.',
        $name === '__destruct' => 'Finalizes the object and performs deferred cleanup.',
        $name === '__get' => 'Handles dynamic property reads for this ' . $context . '.',
        $name === '__set' => 'Handles dynamic property writes for this ' . $context . '.',
        $name === '__isset' => 'Checks whether a dynamic property is available.',
        $name === '__unset' => 'Handles dynamic property removal for this ' . $context . '.',
        $name === '__call' => 'Routes dynamic method calls to the appropriate delegate.',
        $name === '__serialize' => 'Exports object state for PHP serialization.',
        $name === '__unserialize' => 'Restores object state from PHP serialization data.',
        str_starts_with($name, '__') => 'Implements PHP magic behavior for this ' . $context . '.',
        preg_match('/^(get|read|load|fetch|find)/i', $name) === 1 => 'Retrieves ' . $target . ' used by the ' . $context . '.',
        preg_match('/^(set|add|put|save|write|merge)/i', $name) === 1 => 'Applies ' . $target . ' to the ' . $context . '.',
        preg_match('/^(is|has|check|validate|can)/i', $name) === 1 => 'Evaluates ' . $target . ' and reports the outcome.',
        preg_match('/^(make|create|build|assemble)/i', $name) === 1 => 'Builds ' . $target . ' for the ' . $context . '.',
        preg_match('/^(parse|format|convert|encode|decode)/i', $name) === 1 => 'Transforms ' . $target . ' between supported representations.',
        preg_match('/^(delete|remove|reset|clear|destroy)/i', $name) === 1 => 'Removes or resets ' . $target . ' managed by the ' . $context . '.',
        preg_match('/^(init|run|start|finish|handle|process)/i', $name) === 1 => 'Runs the ' . $target . ' workflow for the ' . $context . '.',
        str_contains($body, 'return') => 'Runs the ' . $target . ' operation and returns its result.',
        default => 'Runs the ' . $target . ' workflow for the ' . $context . '.',
    };
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
        'get' => 'the requested value',
        'set' => 'the supplied value',
        'add' => 'the supplied item',
        'save' => 'the current state',
        'delete' => 'the current state',
        'reset' => 'the current state',
        'init' => 'the initialization sequence',
        'run' => 'the runtime sequence',
    ];
    $lowerName = strtolower($name);
    if (isset($exactTargets[$lowerName])) {
        return $exactTargets[$lowerName];
    }

    $target = preg_replace(
        '/^_?(get|read|load|fetch|find|set|add|put|save|write|merge|is|has|check|validate|can|make|create|build|assemble|parse|format|convert|encode|decode|delete|remove|reset|clear|destroy|init|run|start|finish|handle|process)_?/i',
        '',
        $name
    );
    $target = $target === '' ? $name : $target;
    $target = readableIdentifier($target);
    return strtolower($target === '' ? $name : $target);
}

function extractParameters(string $signature): array
{
    if (!preg_match('/\\((.*)\\)/s', $signature, $matches)) {
        return [];
    }

    $params = [];
    foreach (splitTopLevel($matches[1]) as $rawParam) {
        $rawParam = trim($rawParam);
        if ($rawParam === '' || !preg_match('/\\$([A-Za-z_][A-Za-z0-9_]*)/', $rawParam, $nameMatch)) {
            continue;
        }

        $beforeVariable = substr($rawParam, 0, strpos($rawParam, '$' . $nameMatch[1]));
        $beforeVariable = trim(str_replace(['&', '...'], '', $beforeVariable));
        $params[] = [
            'name' => $nameMatch[1],
            'type' => $beforeVariable === '' ? 'mixed' : normalizeType($beforeVariable),
        ];
    }

    return $params;
}

function splitTopLevel(string $source): array
{
    $parts = [];
    $current = '';
    $depth = 0;
    $length = strlen($source);

    for ($i = 0; $i < $length; $i++) {
        $char = $source[$i];
        if (in_array($char, ['(', '['], true)) {
            $depth++;
        } elseif (in_array($char, [')', ']'], true)) {
            $depth--;
        } elseif ($char === ',' && $depth === 0) {
            $parts[] = $current;
            $current = '';
            continue;
        }
        $current .= $char;
    }

    $parts[] = $current;
    return $parts;
}

/**
 * Runs the infer return type operation and returns its result.
 *
 * @param string $name Logical name of the value or component being addressed.
 * @param string $signature Input value for the signature argument.
 * @param string $body Input value for the body argument.
 *
 * @return ?string Returns the string representation produced by the operation.
 */
function inferReturnType(string $name, string $signature, string $body): ?string
{
    if ($name === '__construct' || $name === '__destruct') {
        return null;
    }
    if (preg_match('/\\)\\s*:\\s*([^\\{;]+)/', $signature, $matches)) {
        return normalizeType(trim($matches[1]));
    }
    if (returnsOnlyThis($body)) {
        return 'static';
    }
    if (preg_match('/\\breturn\\b/', $body)) {
        return 'mixed';
    }
    return 'void';
}

/**
 * Runs the normalize type operation and returns its result.
 *
 * @param string $type Type discriminator that selects the required behavior.
 *
 * @return string Returns the string representation produced by the operation.
 */
function normalizeType(string $type): string
{
    $type = preg_replace('/\\s*=.*$/s', '', trim($type));
    $type = preg_replace('/\\s+/', '', $type);
    return $type === '' ? 'mixed' : $type;
}

/**
 * Runs the describe parameter operation and returns its result.
 *
 * @param string $name Logical name of the value or component being addressed.
 *
 * @return string Returns the string representation produced by the operation.
 */
function describeParameter(string $name): string
{
    $lowerName = strtolower($name);
    $label = strtolower(readableIdentifier($name));
    $descriptions = [
        'id' => 'Unique identifier used to locate the target item.',
        'key' => 'Lookup key used to address the target value.',
        'name' => 'Logical name of the value or component being addressed.',
        'type' => 'Type discriminator that selects the required behavior.',
        'value' => 'Value that should be applied or transformed.',
        'data' => 'Structured data consumed by the operation.',
        'config' => 'Configuration data used to control the operation.',
        'path' => 'Filesystem or URL path used by the operation.',
        'file' => 'File path or file descriptor handled by the operation.',
        'url' => 'URL used as the external request target.',
        'uri' => 'URI used as the routing or request target.',
        'request' => 'Request object or payload handled by the operation.',
        'response' => 'Response object or payload handled by the operation.',
        'default' => 'Fallback value returned when no explicit value is available.',
        'defaultval' => 'Fallback value returned when no explicit value is available.',
        'callback' => 'Callable invoked to complete the delegated operation.',
        'options' => 'Optional settings that refine the operation behavior.',
        'params' => 'Parameter set passed into the operation.',
        'parameters' => 'Parameter set passed into the operation.',
    ];

    return $descriptions[$lowerName] ?? 'Input value for the ' . $label . ' argument.';
}

/**
 * Runs the describe return operation and returns its result.
 *
 * @param string $name Logical name of the value or component being addressed.
 * @param string $type Type discriminator that selects the required behavior.
 *
 * @return string Returns the string representation produced by the operation.
 */
function describeReturn(string $name, string $type): string
{
    $lowerType = strtolower(ltrim($type, '?'));
    if ($type === 'void') {
        return 'No value is returned.';
    }
    if ($type === 'static' || $type === 'self') {
        return 'Returns the current instance for fluent chaining.';
    }
    if ($lowerType === 'bool' || isBooleanLikeName($name)) {
        return 'Indicates whether the requested condition is satisfied.';
    }
    if ($lowerType === 'array') {
        return 'Returns the structured data produced by the operation.';
    }
    if ($lowerType === 'string') {
        return 'Returns the string representation produced by the operation.';
    }
    if (in_array($lowerType, ['int', 'float'], true)) {
        return 'Returns the numeric result produced by the operation.';
    }
    return 'Returns the value produced by the operation.';
}

function extractPreservedTags(string $docBlock): array
{
    if ($docBlock === '') {
        return [];
    }

    $tags = [];
    foreach (preg_split('/\\R/', $docBlock) as $line) {
        $line = trim((string)preg_replace('/^\\s*\\*\\s?/', '', trim($line, " \t/*")));
        if (preg_match('/^@(throws|deprecated|see|since)\\b/', $line)) {
            $tags[] = $line;
        }
    }
    return array_values(array_unique($tags));
}

/**
 * Retrieves able identifier used by the framework helper.
 *
 * @param string $identifier Input value for the identifier argument.
 *
 * @return string Returns the string representation produced by the operation.
 */
function readableIdentifier(string $identifier): string
{
    $identifier = str_replace('_', ' ', $identifier);
    $identifier = (string)preg_replace('/(?<!^)[A-Z]/', ' $0', $identifier);
    $identifier = (string)preg_replace('/\s+/', ' ', $identifier);
    return trim($identifier);
}

function returnsOnlyThis(string $body): bool
{
    if (!preg_match_all('/\breturn\s+([^;]+);/', $body, $matches)) {
        return false;
    }

    foreach ($matches[1] as $expression) {
        if (trim($expression) !== '$this') {
            return false;
        }
    }

    return true;
}

function isBooleanLikeName(string $name): bool
{
    return preg_match('/^(is|has|can|should|validate|check)/i', $name) === 1;
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
    return readNamedDeclaration($tokens, $functionIndex + 1) === null;
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

    for ($i = $functionIndex, $count = count($tokens); $i < $count; $i++) {
        $token = $tokens[$i];
        if (!$insideBody) {
            if ($token->text === ';') {
                return [$signature . ';', ''];
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
                return [$signature, $body];
            }
        }
        $body .= $token->text;
    }

    return [$signature, $body];
}

/**
 * Retrieves line indent used by the framework helper.
 *
 * @param string $code Input value for the code argument.
 * @param int $offset Input value for the offset argument.
 *
 * @return string Returns the string representation produced by the operation.
 */
function readLineIndent(string $code, int $offset): string
{
    $lineStart = readLineStartOffset($code, $offset);
    $line = substr($code, $lineStart, $offset - $lineStart);
    preg_match('/^[ \\t]*/', $line, $matches);
    return $matches[0] ?? '';
}

/**
 * Retrieves line start offset used by the framework helper.
 *
 * @param string $code Input value for the code argument.
 * @param int $offset Input value for the offset argument.
 *
 * @return int Returns the numeric result produced by the operation.
 */
function readLineStartOffset(string $code, int $offset): int
{
    $lineStart = strrpos(substr($code, 0, $offset), "\n");
    return $lineStart === false ? 0 : $lineStart + 1;
}
