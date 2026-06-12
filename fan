<?php

declare(strict_types=1);

function php_fan_cli_usage(): string
{
    return implode("\n", [
        'Usage:',
        '  php fan ai:map [--json|--write|--validate]',
        '  php fan ai:services [--json]',
        '  php fan ai:explain <file> [--json]',
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
