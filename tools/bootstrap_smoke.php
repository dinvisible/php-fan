<?php

declare(strict_types=1);

/**
 * Verifies that the web entrypoint can load the current bootstrap factory.
 *
 * Usage:
 *   php tools/bootstrap_smoke.php
 *   php tools/bootstrap_smoke.php --json
 */

function php_fan_bootstrap_smoke(string $root): array
{
    $checks = [];
    $checks[] = php_fan_bootstrap_smoke_index_source($root);
    $checks[] = php_fan_bootstrap_smoke_load_file($root, 'vendor_autoload', 'vendor/autoload.php');
    $checks[] = php_fan_bootstrap_smoke_load_file($root, 'composer_autoload_helper', 'tools/composer_autoload.php');
    $checks[] = php_fan_bootstrap_smoke_function('array_val');
    $checks[] = php_fan_bootstrap_smoke_class(
        $root,
        'web_initializer_factory_class',
        'fan\\core\\di\\web_application_initializer_defaults_factory',
        'core/factory/web_application_initializer_defaults_factory.php'
    );
    $checks[] = php_fan_bootstrap_smoke_class(
        $root,
        'context_factory_class',
        'fan\\core\\bootstrap\\context_factory',
        'core/factory/context_factory.php'
    );
    $checks[] = php_fan_bootstrap_smoke_class(
        $root,
        'service_listener_state_class',
        'fan\\core\\service\\service_listener_state',
        'core/service/service_listener_state.php'
    );

    $ok = array_reduce(
        $checks,
        static fn(bool $carry, array $check): bool => $carry && $check['status'] === 'pass',
        true
    );

    return [
        'status' => $ok ? 'pass' : 'fail',
        'checks' => $checks,
    ];
}

function php_fan_bootstrap_smoke_index_source(string $root): array
{
    $path = $root . '/htdocs/index.php';
    if (!is_file($path)) {
        return php_fan_bootstrap_smoke_check('index_source', 'fail', 'htdocs/index.php is missing.');
    }

    $source = file_get_contents($path);
    if (!is_string($source)) {
        return php_fan_bootstrap_smoke_check('index_source', 'fail', 'htdocs/index.php is not readable.');
    }

    $required = [
        "require_once __DIR__ . '/../vendor/autoload.php';",
        "require_once __DIR__ . '/../tools/composer_autoload.php';",
        'use fan\core\di\web_application_initializer_defaults_factory;',
        '((new web_application_initializer_defaults_factory())())->run();',
    ];
    $missing = array_values(array_filter(
        $required,
        static fn(string $needle): bool => !str_contains($source, $needle)
    ));

    if ($missing !== []) {
        return php_fan_bootstrap_smoke_check('index_source', 'fail', 'htdocs/index.php is stale.', [
            'missing' => $missing,
        ]);
    }

    return php_fan_bootstrap_smoke_check('index_source', 'pass', 'htdocs/index.php contains the current bootstrap fallback.');
}

function php_fan_bootstrap_smoke_load_file(string $root, string $name, string $relativePath): array
{
    $path = $root . '/' . $relativePath;
    if (!is_file($path)) {
        return php_fan_bootstrap_smoke_check($name, 'fail', $relativePath . ' is missing.');
    }

    require_once $path;

    return php_fan_bootstrap_smoke_check($name, 'pass', $relativePath . ' loaded.');
}

function php_fan_bootstrap_smoke_function(string $function): array
{
    if (!function_exists($function)) {
        return php_fan_bootstrap_smoke_check($function . '_function', 'fail', $function . '() is not loaded.');
    }

    return php_fan_bootstrap_smoke_check($function . '_function', 'pass', $function . '() is loaded.');
}

function php_fan_bootstrap_smoke_class(string $root, string $name, string $class, string $relativePath): array
{
    if (!function_exists('php_fan_composer_autoload_path_for') || !function_exists('php_fan_composer_autoload_symbol_exists')) {
        return php_fan_bootstrap_smoke_check($name, 'fail', 'tools/composer_autoload.php does not expose fallback helpers.');
    }

    $path = php_fan_composer_autoload_path_for($class);
    $expected = $root . '/' . $relativePath;
    if ($path !== $expected) {
        return php_fan_bootstrap_smoke_check($name, 'fail', $class . ' fallback path is wrong.', [
            'expected' => $expected,
            'actual' => $path,
        ]);
    }

    if (!php_fan_composer_autoload_symbol_exists($class, true)) {
        return php_fan_bootstrap_smoke_check($name, 'fail', $class . ' is not autoloadable.', [
            'class' => $class,
            'path' => $path,
        ]);
    }

    return php_fan_bootstrap_smoke_check($name, 'pass', $class . ' is autoloadable.', [
        'class' => $class,
        'path' => $path,
    ]);
}

function php_fan_bootstrap_smoke_check(string $name, string $status, string $message, array $details = []): array
{
    return [
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'details' => $details,
    ];
}

function php_fan_bootstrap_smoke_render_text(array $result): string
{
    $lines = ['Bootstrap smoke: ' . strtoupper($result['status'])];
    foreach ($result['checks'] as $check) {
        $lines[] = sprintf('[%s] %s - %s', strtoupper($check['status']), $check['name'], $check['message']);
    }

    return implode("\n", $lines) . "\n";
}

function php_fan_bootstrap_smoke_main(array $argv): int
{
    $result = php_fan_bootstrap_smoke(dirname(__DIR__));

    if (in_array('--json', $argv, true)) {
        fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    } else {
        fwrite(STDOUT, php_fan_bootstrap_smoke_render_text($result));
    }

    return $result['status'] === 'pass' ? 0 : 1;
}

if (PHP_SAPI === 'cli' && realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    exit(php_fan_bootstrap_smoke_main($argv));
}
