<?php

declare(strict_types=1);

/**
 * Runs optional static-analysis and formatter checks when local tools exist.
 *
 * Usage:
 *   php tools/ai_static_check.php
 *   php tools/ai_static_check.php --json
 */

require_once __DIR__ . '/ai_verify.php';

function php_fan_ai_static_check(string $root): array
{
    $checks = [];
    $checks[] = php_fan_ai_static_config_files($root);
    $checks[] = php_fan_ai_static_optional_tool(
        $root,
        'phpstan',
        'vendor/bin/phpstan',
        [PHP_BINARY, 'vendor/bin/phpstan', 'analyse', '--configuration', 'phpstan.neon.dist', '--memory-limit=1G', '--no-progress', '--debug']
    );
    $checks[] = php_fan_ai_static_optional_tool(
        $root,
        'php_cs_fixer',
        'vendor/bin/php-cs-fixer',
        [PHP_BINARY, 'vendor/bin/php-cs-fixer', 'fix', '--dry-run', '--diff', '--config=.php-cs-fixer.dist.php', '--using-cache=no', '--sequential', '--allow-unsupported-php-version=yes']
    );

    $ok = array_reduce(
        $checks,
        static fn(bool $carry, array $check): bool => $carry && in_array($check['status'], ['pass', 'skip'], true),
        true
    );

    return [
        'status' => $ok ? 'pass' : 'fail',
        'checks' => $checks,
    ];
}

function php_fan_ai_static_config_files(string $root): array
{
    $files = ['phpstan.neon.dist', 'phpstan-baseline.neon', '.php-cs-fixer.dist.php'];
    $missing = array_values(array_filter($files, static fn(string $file): bool => !is_file($root . '/' . $file)));

    if ($missing !== []) {
        return php_fan_ai_verify_check('static_config', 'fail', 'Missing static tooling config: ' . implode(', ', $missing));
    }

    return php_fan_ai_verify_check('static_config', 'pass', 'Static tooling config files are present.', [
        'files' => $files,
    ]);
}

function php_fan_ai_static_optional_tool(string $root, string $name, string $binary, array $command): array
{
    if (!is_file($root . '/' . $binary)) {
        return php_fan_ai_verify_check($name, 'skip', $binary . ' is not installed.');
    }

    $result = php_fan_ai_verify_run($command, $root);
    if ($result['exit_code'] !== 0) {
        return php_fan_ai_verify_check($name, 'fail', $name . ' failed.', $result);
    }

    return php_fan_ai_verify_check($name, 'pass', $name . ' passed.', $result);
}

function php_fan_ai_static_check_render_text(array $result): string
{
    $lines = ['AI static check: ' . strtoupper($result['status'])];
    foreach ($result['checks'] as $check) {
        $lines[] = sprintf('[%s] %s - %s', strtoupper($check['status']), $check['name'], $check['message']);
    }

    return implode("\n", $lines) . "\n";
}

function php_fan_ai_static_check_main(array $argv): int
{
    $result = php_fan_ai_static_check(dirname(__DIR__));

    if (in_array('--json', $argv, true)) {
        fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    } else {
        fwrite(STDOUT, php_fan_ai_static_check_render_text($result));
    }

    return $result['status'] === 'pass' ? 0 : 1;
}

if (PHP_SAPI === 'cli' && realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    exit(php_fan_ai_static_check_main($argv));
}
