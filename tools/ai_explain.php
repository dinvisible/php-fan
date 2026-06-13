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
    $map = php_fan_ai_build_map($root);
    $relatedServiceIds = php_fan_ai_explain_related_service_ids_from_map($map, $relativeFile);

    return [
        'file' => $relativeFile,
        'type' => php_fan_ai_explain_file_type($relativeFile),
        'strict_types' => str_contains($source, 'declare(strict_types=1)'),
        'namespace' => php_fan_ai_explain_namespace($source),
        'classes' => php_fan_ai_explain_classes($source),
        'functions' => php_fan_ai_explain_functions($source),
        'service_dependencies' => php_fan_ai_container_dependencies_from_source($source, $constants),
        'service_reference_locations' => php_fan_ai_explain_service_reference_locations($map, $relativeFile),
        'related_service_ids' => $relatedServiceIds,
        'related_service_descriptors' => php_fan_ai_explain_related_service_descriptors($map, $relatedServiceIds),
        'related_tests' => php_fan_ai_explain_related_tests($root, $relativeFile),
        'metadata' => str_ends_with($relativeFile, '.meta.php')
            ? php_fan_ai_explain_meta($root, $relativeFile)
            : null,
        'template' => str_ends_with($relativeFile, '.tpl')
            ? ['placeholders' => php_fan_ai_template_placeholders($root, $relativeFile)]
            : null,
        'dynamic_boundaries' => php_fan_ai_explain_dynamic_boundaries($source),
        'dynamic_boundary_category' => php_fan_ai_dynamic_boundary_category_for_file($relativeFile),
        'dynamic_boundary_details' => php_fan_ai_explain_dynamic_boundary_details($map, $relativeFile, $source),
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

function php_fan_ai_explain_related_service_ids(string $root, string $relativeFile): array
{
    return php_fan_ai_explain_related_service_ids_from_map(php_fan_ai_build_map($root), $relativeFile);
}

function php_fan_ai_explain_related_service_ids_from_map(array $map, string $relativeFile): array
{
    $ids = [];

    foreach ($map['services']['descriptors'] as $id => $descriptor) {
        if (in_array($relativeFile, $descriptor['registrar_files'] ?? [], true)) {
            $ids[] = $id;
            continue;
        }
        foreach (($descriptor['source_locations']['creator_methods'] ?? []) as $location) {
            if (($location['file'] ?? null) === $relativeFile) {
                $ids[] = $id;
                break;
            }
        }
    }

    $ids = array_values(array_unique(array_map('strval', $ids)));
    sort($ids);

    return $ids;
}

function php_fan_ai_explain_related_service_descriptors(array $map, array $ids): array
{
    $summaries = [];
    foreach ($ids as $id) {
        $descriptor = $map['services']['descriptors'][$id] ?? null;
        if (!is_array($descriptor)) {
            continue;
        }
        $summaries[$id] = [
            'id' => $id,
            'class' => $descriptor['class'] ?? null,
            'factory' => $descriptor['factory'] ?? null,
            'config_key' => $descriptor['config_key'] ?? null,
            'dependencies' => $descriptor['dependencies'] ?? [],
        ];
    }

    ksort($summaries);

    return $summaries;
}

function php_fan_ai_explain_service_reference_locations(array $map, string $relativeFile): array
{
    $references = [];
    foreach (($map['services']['referenced_locations'] ?? []) as $id => $locations) {
        if (!is_array($locations)) {
            continue;
        }
        foreach ($locations as $location) {
            if (($location['file'] ?? null) !== $relativeFile) {
                continue;
            }
            $references[(string)$id][] = $location;
        }
    }

    foreach ($references as $id => $locations) {
        usort(
            $locations,
            static fn(array $left, array $right): int => [
                (int)($left['line'] ?? 0),
                (string)($left['value'] ?? ''),
            ] <=> [
                (int)($right['line'] ?? 0),
                (string)($right['value'] ?? ''),
            ]
        );
        $references[$id] = $locations;
    }
    ksort($references);

    return $references;
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

function php_fan_ai_explain_dynamic_boundary_details(array $map, string $relativeFile, string $source): array
{
    $categoryDetails = php_fan_ai_dynamic_boundary_category_details_for_file($relativeFile);
    $locations = $map['dynamic_boundaries']['locations'][$relativeFile]
        ?? php_fan_ai_dynamic_boundary_locations_from_source($relativeFile, $source);
    $patterns = array_values(array_unique(array_map(
        static fn(array $location): string => (string)($location['pattern'] ?? ''),
        $locations
    )));
    $patterns = array_values(array_filter($patterns, static fn(string $pattern): bool => $pattern !== ''));
    sort($patterns);

    if ($categoryDetails === null && $patterns === []) {
        return [];
    }

    return [
        'category' => $categoryDetails['category'] ?? null,
        'matched_entry' => $categoryDetails['matched_entry'] ?? null,
        'boundary_kind' => php_fan_ai_dynamic_boundary_kind_for_locations($relativeFile, $locations),
        'reason' => $categoryDetails['reason'] ?? null,
        'patterns' => $patterns,
        'locations' => $locations,
    ];
}

function php_fan_ai_explain_dynamic_boundary_patterns(string $source): array
{
    $patterns = array_values(array_unique(array_map(
        static fn(array $location): string => (string)$location['pattern'],
        php_fan_ai_dynamic_boundary_locations_from_source('(inline)', $source)
    )));

    sort($patterns);

    return $patterns;
}

function php_fan_ai_explain_render_markdown(array $explanation): string
{
    $dynamicDetails = $explanation['dynamic_boundary_details'] ?? [];
    $dynamicPatterns = is_array($dynamicDetails) && isset($dynamicDetails['patterns']) && is_array($dynamicDetails['patterns'])
        ? $dynamicDetails['patterns']
        : [];
    $dynamicLocations = is_array($dynamicDetails) && isset($dynamicDetails['locations']) && is_array($dynamicDetails['locations'])
        ? $dynamicDetails['locations']
        : [];
    $serviceReferenceCount = 0;
    foreach (($explanation['service_reference_locations'] ?? []) as $locations) {
        $serviceReferenceCount += is_array($locations) ? count($locations) : 0;
    }
    $lines = [
        '# AI Explain: ' . $explanation['file'],
        '',
        '- Type: ' . $explanation['type'],
        '- Namespace: ' . ($explanation['namespace'] ?? '(none)'),
        '- Strict types: ' . ($explanation['strict_types'] ? 'yes' : 'no'),
        '- Service dependencies: ' . (empty($explanation['service_dependencies']) ? '(none)' : implode(', ', $explanation['service_dependencies'])),
        '- Service reference locations: ' . ($serviceReferenceCount === 0 ? '(none)' : (string)$serviceReferenceCount),
        '- Related service ids: ' . (empty($explanation['related_service_ids']) ? '(none)' : implode(', ', $explanation['related_service_ids'])),
        '- Related service summaries: ' . (empty($explanation['related_service_descriptors']) ? '(none)' : count($explanation['related_service_descriptors'])),
        '- Related tests: ' . (empty($explanation['related_tests']) ? '(none)' : implode(', ', $explanation['related_tests'])),
        '- Dynamic boundary category: ' . ($explanation['dynamic_boundary_category'] ?? '(none)'),
        '- Dynamic boundary patterns: ' . ($dynamicPatterns === [] ? '(none)' : implode(', ', $dynamicPatterns)),
        '- Dynamic boundary locations: ' . ($dynamicLocations === [] ? '(none)' : (string)count($dynamicLocations)),
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
