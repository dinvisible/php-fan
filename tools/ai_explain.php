<?php

declare(strict_types=1);

/**
 * Explains one PHP-FAN source file for AI coding agents.
 *
 * Usage:
 *   php tools/ai_explain.php core/base/model/request.php
 *   php tools/ai_explain.php core/base/model/request.php --json
 */

require_once __DIR__ . '/ai_map.php';

function php_fan_ai_explain_file(string $root, string $path): array
{
    $root = rtrim($root, DIRECTORY_SEPARATOR);
    $relativeFile = php_fan_ai_explain_relative_path($root, $path);
    $absoluteFile = $root . '/' . $relativeFile;

    if (!is_file($absoluteFile)) {
        throw new InvalidArgumentException('File is not inside the project or does not exist: ' . $path);
    }

    $source = (string)file_get_contents($absoluteFile);
    $constants = php_fan_ai_service_id_constants($root);

    return [
        'file' => $relativeFile,
        'type' => php_fan_ai_explain_file_type($relativeFile),
        'strict_types' => str_contains($source, 'declare(strict_types=1)'),
        'namespace' => php_fan_ai_explain_namespace($source),
        'classes' => php_fan_ai_explain_classes($source),
        'functions' => php_fan_ai_explain_functions($source),
        'service_dependencies' => php_fan_ai_container_dependencies_from_source($source, $constants),
        'related_tests' => php_fan_ai_explain_related_tests($root, $relativeFile),
        'metadata' => str_ends_with($relativeFile, '.meta.php')
            ? php_fan_ai_explain_meta($root, $relativeFile)
            : null,
        'template' => str_ends_with($relativeFile, '.tpl')
            ? ['placeholders' => php_fan_ai_template_placeholders($root, $relativeFile)]
            : null,
        'dynamic_boundaries' => php_fan_ai_explain_dynamic_boundaries($source),
    ];
}

function php_fan_ai_explain_relative_path(string $root, string $path): string
{
    $candidate = str_starts_with($path, DIRECTORY_SEPARATOR) ? $path : $root . '/' . $path;
    $realRoot = realpath($root);
    $realFile = realpath($candidate);

    if (!is_string($realRoot) || !is_string($realFile) || !str_starts_with($realFile, $realRoot . DIRECTORY_SEPARATOR)) {
        throw new InvalidArgumentException('File is not inside the project or does not exist: ' . $path);
    }

    return str_replace($realRoot . DIRECTORY_SEPARATOR, '', $realFile);
}

function php_fan_ai_explain_file_type(string $relativeFile): string
{
    return match (true) {
        str_ends_with($relativeFile, '.meta.php') => 'meta',
        str_ends_with($relativeFile, '.tpl') => 'template',
        str_starts_with($relativeFile, 'unit/') => 'test',
        default => 'php',
    };
}

function php_fan_ai_explain_namespace(string $source): ?string
{
    return preg_match('/^\s*namespace\s+([^;]+);/m', $source, $match) === 1 ? trim($match[1]) : null;
}

function php_fan_ai_explain_classes(string $source): array
{
    if (preg_match_all('/\b(?:(final|abstract)\s+)?class\s+([A-Za-z_][A-Za-z0-9_]*)/m', $source, $matches, PREG_SET_ORDER) === 0) {
        return [];
    }

    $classes = [];
    foreach ($matches as $match) {
        $classes[] = [
            'name' => $match[2],
            'modifier' => $match[1] !== '' ? $match[1] : null,
            'methods' => php_fan_ai_explain_methods($source),
        ];
    }

    return $classes;
}

function php_fan_ai_explain_functions(string $source): array
{
    if (preg_match_all('/(?<!->)(?<!::)\bfunction\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/m', $source, $matches, PREG_OFFSET_CAPTURE) === 0) {
        return [];
    }

    $functions = [];
    foreach ($matches[1] as $index => $match) {
        $offset = $matches[0][$index][1];
        $prefix = substr($source, max(0, $offset - 40), min(40, $offset));
        if (preg_match('/\b(?:public|protected|private)\s+(?:static\s+)?$/', $prefix) === 1) {
            continue;
        }
        $functions[] = $match[0];
    }

    $functions = array_values(array_unique($functions));
    sort($functions);

    return $functions;
}

function php_fan_ai_explain_methods(string $source): array
{
    if (preg_match_all('/\b(public|protected|private)\s+(?:static\s+)?function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/m', $source, $matches, PREG_SET_ORDER) === 0) {
        return [];
    }

    $methods = [];
    foreach ($matches as $match) {
        $methods[] = [
            'visibility' => $match[1],
            'name' => $match[2],
        ];
    }

    return $methods;
}

function php_fan_ai_explain_related_tests(string $root, string $relativeFile): array
{
    if (str_starts_with($relativeFile, 'unit/')) {
        return [$relativeFile];
    }

    $stem = strtolower(pathinfo($relativeFile, PATHINFO_FILENAME));
    $expectedBasename = $stem . 'test.php';
    $tests = [];

    foreach (php_fan_ai_php_files($root, ['unit']) as $testFile) {
        if (strtolower(basename($testFile)) === $expectedBasename) {
            $tests[] = $testFile;
        }
    }
    sort($tests);

    return $tests;
}

function php_fan_ai_explain_meta(string $root, string $relativeFile): array
{
    $data = php_fan_ai_meta_file_data($root, $relativeFile);

    return [
        'top_level_keys' => array_keys($data),
        'own_keys' => isset($data['own']) && is_array($data['own']) ? array_keys($data['own']) : [],
        'common_keys' => isset($data['common']) && is_array($data['common']) ? array_keys($data['common']) : [],
    ];
}

function php_fan_ai_explain_dynamic_boundaries(string $source): array
{
    return [
        'container_get' => preg_match('/(?:\$container|\$this->container\(\)|\$this->context\(\)->container\(\))\s*->\s*get\s*\(/', $source) === 1,
        'dynamic_new' => preg_match('/new\s+\$[A-Za-z_][A-Za-z0-9_]*\s*\(/', $source) === 1,
        'file_include' => preg_match('/\b(?:include|include_once|require|require_once)\b/', $source) === 1,
        'callback_dispatch' => preg_match('/\bcall_user_func(?:_array)?\s*\(/', $source) === 1,
        'eval' => preg_match('/\beval\s*\(/', $source) === 1,
    ];
}

function php_fan_ai_explain_render_markdown(array $explanation): string
{
    $lines = [
        '# AI Explain: ' . $explanation['file'],
        '',
        '- Type: ' . $explanation['type'],
        '- Namespace: ' . ($explanation['namespace'] ?? '(none)'),
        '- Strict types: ' . ($explanation['strict_types'] ? 'yes' : 'no'),
        '- Service dependencies: ' . (empty($explanation['service_dependencies']) ? '(none)' : implode(', ', $explanation['service_dependencies'])),
        '- Related tests: ' . (empty($explanation['related_tests']) ? '(none)' : implode(', ', $explanation['related_tests'])),
        '',
        '## Classes',
        '',
    ];

    foreach ($explanation['classes'] as $class) {
        $lines[] = '- ' . $class['name'] . ' (' . count($class['methods']) . ' methods)';
    }
    if ($explanation['classes'] === []) {
        $lines[] = '- (none)';
    }

    return implode("\n", $lines) . "\n";
}

function php_fan_ai_explain_main(array $argv): int
{
    $root = dirname(__DIR__);
    $args = array_values(array_filter(array_slice($argv, 1), static fn(string $arg): bool => $arg !== '--json'));
    if ($args === []) {
        fwrite(STDERR, "Usage: php tools/ai_explain.php <file> [--json]\n");
        return 2;
    }

    try {
        $explanation = php_fan_ai_explain_file($root, $args[0]);
    } catch (\Throwable $exception) {
        fwrite(STDERR, $exception->getMessage() . "\n");
        return 1;
    }

    if (in_array('--json', $argv, true)) {
        fwrite(STDOUT, json_encode($explanation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        return 0;
    }

    fwrite(STDOUT, php_fan_ai_explain_render_markdown($explanation));

    return 0;
}

if (PHP_SAPI === 'cli' && realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    exit(php_fan_ai_explain_main($argv));
}
