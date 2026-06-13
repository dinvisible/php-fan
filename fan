<?php

declare(strict_types=1);

function php_fan_cli_usage(): string
{
    return implode("\n", [
        'Usage:',
        '  php fan ai:map [--json|--write|--validate]',
        '  php fan ai:services [service-id] [--json]',
        '  php fan ai:explain <file> [--json]',
        '  php fan ai:dynamic-boundaries [file] [--json] [--direct|--next|--composition|--composition-open|--debt|--kind=boundary_kind]',
        '  php fan ai:source-inventory [queue-id] [--json|--next]',
        '  php fan ai:verify [--json|--changed|--no-phpunit]',
        '  php fan ai:doctor [--json|--no-phpunit]',
        '  php fan ai:static [--json]',
        '',
    ]);
}

function php_fan_cli_main(array $argv): int
{
    $command = $argv[1] ?? null;
    $args = array_slice($argv, 2);

    return match ($command) {
        'ai:map' => php_fan_cli_ai_map($args),
        'ai:services' => php_fan_cli_ai_services($args),
        'ai:explain' => php_fan_cli_ai_explain($args),
        'ai:dynamic-boundaries' => php_fan_cli_ai_dynamic_boundaries($args),
        'ai:source-inventory' => php_fan_cli_ai_source_inventory($args),
        'ai:verify' => php_fan_cli_ai_verify($args),
        'ai:doctor' => php_fan_cli_ai_doctor($args),
        'ai:static' => php_fan_cli_ai_static($args),
        default => php_fan_cli_unknown($command),
    };
}

function php_fan_cli_unknown(?string $command): int
{
    if ($command !== null) {
        fwrite(STDERR, 'Unknown command: ' . $command . "\n");
    }
    fwrite(STDERR, php_fan_cli_usage());

    return 2;
}

function php_fan_cli_ai_map(array $args): int
{
    require_once __DIR__ . '/tools/ai_map.php';

    return php_fan_ai_main(array_merge(['tools/ai_map.php'], $args));
}

function php_fan_cli_ai_services(array $args): int
{
    require_once __DIR__ . '/tools/ai_map.php';

    $serviceId = null;
    foreach ($args as $arg) {
        if (str_starts_with($arg, '--')) {
            continue;
        }
        $serviceId = $arg;
        break;
    }

    if ($serviceId !== null) {
        $descriptor = php_fan_ai_service_descriptor(__DIR__, $serviceId);
        if ($descriptor === null) {
            fwrite(STDERR, 'Unknown service id: ' . $serviceId . "\n");

            return 1;
        }
        if (in_array('--json', $args, true)) {
            fwrite(STDOUT, json_encode($descriptor, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

            return 0;
        }

        $shared = ($descriptor['shared'] ?? null) === false ? 'non-shared' : 'shared';
        $dependencies = empty($descriptor['dependencies']) ? 'none' : implode(', ', $descriptor['dependencies']);
        fwrite(STDOUT, $serviceId . ' [' . $shared . '] deps: ' . $dependencies . "\n");
        fwrite(STDOUT, 'class: ' . ($descriptor['class'] ?? 'unknown') . "\n");
        fwrite(STDOUT, 'factory: ' . ($descriptor['factory'] ?? 'unknown') . "\n");

        return 0;
    }

    $map = php_fan_ai_build_map(__DIR__);
    if (in_array('--json', $args, true)) {
        fwrite(STDOUT, json_encode($map['services'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

        return 0;
    }

    foreach ($map['services']['descriptors'] as $id => $descriptor) {
        $shared = ($descriptor['shared'] ?? null) === false ? 'non-shared' : 'shared';
        $dependencies = empty($descriptor['dependencies']) ? 'none' : implode(', ', $descriptor['dependencies']);
        fwrite(STDOUT, $id . ' [' . $shared . '] deps: ' . $dependencies . "\n");
    }

    return 0;
}

function php_fan_cli_ai_explain(array $args): int
{
    require_once __DIR__ . '/tools/ai_explain.php';

    return php_fan_ai_explain_main(array_merge(['tools/ai_explain.php'], $args));
}

function php_fan_cli_ai_dynamic_boundaries(array $args): int
{
    require_once __DIR__ . '/tools/ai_map.php';

    $file = null;
    try {
        $boundaryKind = php_fan_cli_dynamic_boundary_kind_filter($args);
        $excludeCategories = php_fan_cli_dynamic_boundary_excluded_categories($args);
        $includeCategories = php_fan_cli_dynamic_boundary_included_categories($args);
        $rankByCount = php_fan_cli_dynamic_boundary_rank_by_count($args);
        $minimumCount = php_fan_cli_dynamic_boundary_minimum_count($args);
    } catch (\Throwable $exception) {
        fwrite(STDERR, $exception->getMessage() . "\n");

        return 1;
    }
    foreach ($args as $arg) {
        if (str_starts_with($arg, '--')) {
            continue;
        }
        $file = $arg;
        break;
    }

    $map = php_fan_ai_build_map(__DIR__);
    $debtOnly = php_fan_cli_dynamic_boundary_debt_only($args);
    if ($file !== null) {
        try {
            $relativeFile = php_fan_cli_relative_project_file(__DIR__, $file);
        } catch (\Throwable $exception) {
            fwrite(STDERR, $exception->getMessage() . "\n");

            return 1;
        }

        $report = $debtOnly
            ? php_fan_ai_dynamic_boundary_named_migration_debt_summary_for_file($map, $relativeFile)
            : php_fan_ai_dynamic_boundary_summary_for_file($map, $relativeFile, $boundaryKind);
        if (in_array('--json', $args, true)) {
            fwrite(STDOUT, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

            return 0;
        }

        php_fan_cli_write_dynamic_boundary_report($report);

        return 0;
    }

    $summary = $debtOnly
        ? php_fan_ai_dynamic_boundary_named_migration_debt_summary($map)
        : php_fan_ai_dynamic_boundary_summary($map, $boundaryKind, $excludeCategories, $includeCategories, $rankByCount, $minimumCount);
    if (in_array('--json', $args, true)) {
        fwrite(STDOUT, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

        return 0;
    }

    if ($summary === []) {
        fwrite(STDOUT, "No dynamic boundary locations.\n");

        return 0;
    }

    foreach ($summary as $entry) {
        $patterns = $entry['patterns'] === [] ? 'none' : implode(', ', $entry['patterns']);
        fwrite(
            STDOUT,
            $entry['file'] . ' [' . ($entry['category'] ?? 'uncategorized') . '] '
            . '(' . $entry['boundary_kind'] . ') '
            . $entry['count'] . ' location(s): ' . $patterns . "\n"
        );
    }

    return 0;
}

function php_fan_cli_dynamic_boundary_kind_filter(array $args): ?string
{
    if (in_array('--debt', $args, true)) {
        return 'named_default_closure_boundary';
    }

    if (in_array('--direct', $args, true) || in_array('--next', $args, true)) {
        return 'method_body_or_runtime_boundary';
    }

    foreach ($args as $arg) {
        if (!str_starts_with($arg, '--kind=')) {
            continue;
        }
        $kind = substr($arg, strlen('--kind='));
        if (in_array($kind, ['method_body_or_runtime_boundary', 'named_default_closure_boundary'], true)) {
            return $kind;
        }
        throw new InvalidArgumentException('Unknown dynamic boundary kind: ' . $kind);
    }

    return null;
}

function php_fan_cli_dynamic_boundary_excluded_categories(array $args): array
{
    if (in_array('--next', $args, true)) {
        return ['composition_roots'];
    }

    return [];
}

function php_fan_cli_dynamic_boundary_included_categories(array $args): array
{
    if (in_array('--composition', $args, true) || in_array('--composition-open', $args, true)) {
        return ['composition_roots'];
    }

    return [];
}

function php_fan_cli_dynamic_boundary_rank_by_count(array $args): bool
{
    return in_array('--composition', $args, true) || in_array('--composition-open', $args, true);
}

function php_fan_cli_dynamic_boundary_minimum_count(array $args): int
{
    return in_array('--composition-open', $args, true) ? 2 : 0;
}

function php_fan_cli_dynamic_boundary_debt_only(array $args): bool
{
    return in_array('--debt', $args, true);
}

function php_fan_cli_relative_project_file(string $root, string $path): string
{
    $candidate = str_starts_with($path, DIRECTORY_SEPARATOR) ? $path : rtrim($root, DIRECTORY_SEPARATOR) . '/' . $path;
    $realRoot = realpath($root);
    $realFile = realpath($candidate);

    if (!is_string($realRoot) || !is_string($realFile) || !str_starts_with($realFile, $realRoot . DIRECTORY_SEPARATOR)) {
        throw new InvalidArgumentException('File is not inside the project or does not exist: ' . $path);
    }

    return str_replace($realRoot . DIRECTORY_SEPARATOR, '', $realFile);
}

function php_fan_cli_write_dynamic_boundary_report(array $report): void
{
    $patterns = $report['patterns'] === [] ? 'none' : implode(', ', $report['patterns']);
    fwrite(
        STDOUT,
        $report['file'] . ' [' . ($report['category'] ?? 'uncategorized') . '] '
        . '(' . $report['boundary_kind'] . ') '
        . $report['count'] . ' location(s): ' . $patterns . "\n"
    );

    foreach ($report['locations'] as $location) {
        $value = preg_replace('/\s+/', ' ', (string)($location['value'] ?? ''));
        fwrite(
            STDOUT,
            '  line ' . (string)($location['line'] ?? '?')
            . ' ' . (string)($location['pattern'] ?? 'unknown')
            . ' (' . (string)($location['boundary_kind'] ?? 'unknown') . ')'
            . ': ' . trim((string)$value) . "\n"
        );
    }
}

function php_fan_cli_ai_source_inventory(array $args): int
{
    require_once __DIR__ . '/tools/ai_map.php';

    $queueId = null;
    foreach ($args as $arg) {
        if (str_starts_with($arg, '--')) {
            continue;
        }
        $queueId = $arg;
        break;
    }

    $map = php_fan_ai_build_map(__DIR__);
    $inventory = $map['source_inventory'];
    if (in_array('--next', $args, true)) {
        $report = $inventory['next'];
    } elseif ($queueId !== null) {
        $report = php_fan_ai_source_inventory_queue($map, $queueId);
        if ($report === null) {
            fwrite(STDERR, 'Unknown source-inventory queue: ' . $queueId . "\n");

            return 1;
        }
    } else {
        $report = $inventory;
    }

    if (in_array('--json', $args, true)) {
        fwrite(STDOUT, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

        return 0;
    }

    if ($queueId !== null && is_array($report) && isset($report['id'])) {
        php_fan_cli_write_source_inventory_queue($report);

        return 0;
    }

    $queues = in_array('--next', $args, true) ? $report : ($inventory['queues'] ?? []);
    if ($queues === []) {
        fwrite(STDOUT, "No actionable source-inventory queues.\n");

        return 0;
    }
    foreach ($queues as $queue) {
        if (is_array($queue)) {
            php_fan_cli_write_source_inventory_queue($queue, false);
        }
    }

    return 0;
}

function php_fan_cli_write_source_inventory_queue(array $queue, bool $includeLocations = true): void
{
    fwrite(
        STDOUT,
        $queue['id'] . ' [' . $queue['classification'] . '] '
        . '(' . $queue['guard_kind'] . ') '
        . $queue['count'] . ' location(s), '
        . count($queue['files']) . ' file(s)' . "\n"
    );

    if (!$includeLocations) {
        return;
    }

    foreach ($queue['locations'] as $location) {
        $value = preg_replace('/\s+/', ' ', (string)($location['value'] ?? ''));
        fwrite(
            STDOUT,
            '  ' . (string)($location['file'] ?? '?')
            . ':' . (string)($location['line'] ?? '?')
            . ' ' . (string)($location['pattern'] ?? 'unknown')
            . ': ' . trim((string)$value) . "\n"
        );
    }
}

function php_fan_cli_ai_verify(array $args): int
{
    require_once __DIR__ . '/tools/ai_verify.php';

    return php_fan_ai_verify_main(array_merge(['tools/ai_verify.php'], $args));
}

function php_fan_cli_ai_doctor(array $args): int
{
    if (!in_array('--changed', $args, true)) {
        $args[] = '--changed';
    }

    return php_fan_cli_ai_verify($args);
}

function php_fan_cli_ai_static(array $args): int
{
    require_once __DIR__ . '/tools/ai_static_check.php';

    return php_fan_ai_static_check_main(array_merge(['tools/ai_static_check.php'], $args));
}

exit(php_fan_cli_main($argv));
