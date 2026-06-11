<?php

declare(strict_types=1);

/**
 * Runs the verification checks an AI coding agent should use before finishing.
 *
 * Usage:
 *   php tools/ai_verify.php
 *   php tools/ai_verify.php --json
 *   php tools/ai_verify.php --skip-phpunit
 */

function php_fan_ai_verify(string $root, array $argv): array
{
    $checks = [];
    $checks[] = php_fan_ai_verify_tracked_noise($root);
    $checks[] = php_fan_ai_verify_diff_check($root);
    $checks[] = php_fan_ai_verify_changed_php_lint($root);

    if (!in_array('--skip-phpunit', $argv, true)) {
        $checks[] = php_fan_ai_verify_phpunit($root);
    }

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

function php_fan_ai_verify_tracked_noise(string $root): array
{
    $result = php_fan_ai_verify_run(['git', 'ls-files'], $root);
    if ($result['exit_code'] !== 0) {
        return php_fan_ai_verify_check('tracked_noise', 'fail', 'Unable to inspect tracked files.', $result);
    }

    $tracked = array_filter(explode("\n", trim($result['stdout'])));
    $forbidden = array_values(array_filter(
        $tracked,
        static fn(string $file): bool => str_starts_with($file, 'node_modules/')
            || preg_match('#(^|/)\\.venv/#', $file) === 1
            || $file === '.DS_Store'
            || str_ends_with($file, '.log')
    ));

    if ($forbidden !== []) {
        return php_fan_ai_verify_check(
            'tracked_noise',
            'fail',
            'Dependency/runtime files are tracked: ' . implode(', ', array_slice($forbidden, 0, 10)),
            ['forbidden' => $forbidden]
        );
    }

    return php_fan_ai_verify_check('tracked_noise', 'pass', 'No tracked dependency or runtime-output noise found.');
}

function php_fan_ai_verify_diff_check(string $root): array
{
    $result = php_fan_ai_verify_run(['git', 'diff', '--check'], $root);
    if ($result['exit_code'] !== 0) {
        return php_fan_ai_verify_check('git_diff_check', 'fail', 'git diff --check failed.', $result);
    }

    return php_fan_ai_verify_check('git_diff_check', 'pass', 'git diff --check passed.');
}

function php_fan_ai_verify_changed_php_lint(string $root): array
{
    $files = php_fan_ai_verify_changed_php_files($root);
    foreach ($files as $file) {
        $result = php_fan_ai_verify_run(['php', '-l', $file], $root);
        if ($result['exit_code'] !== 0) {
            return php_fan_ai_verify_check('php_lint_changed', 'fail', 'PHP lint failed for ' . $file, $result);
        }
    }

    return php_fan_ai_verify_check('php_lint_changed', 'pass', 'PHP lint passed for ' . count($files) . ' changed PHP file(s).');
}

function php_fan_ai_verify_phpunit(string $root): array
{
    $phpunit = $root . '/vendor/bin/phpunit';
    if (!is_file($phpunit)) {
        return php_fan_ai_verify_check('phpunit', 'fail', 'Missing vendor/bin/phpunit. Run Composer install first.');
    }

    $result = php_fan_ai_verify_run(['php', 'vendor/bin/phpunit', '--configuration', 'phpunit.xml'], $root);
    if ($result['exit_code'] !== 0) {
        return php_fan_ai_verify_check('phpunit', 'fail', 'PHPUnit failed.', $result);
    }

    return php_fan_ai_verify_check('phpunit', 'pass', 'PHPUnit passed.', $result);
}

function php_fan_ai_verify_changed_php_files(string $root): array
{
    $files = [];
    foreach ([
        ['git', 'diff', '--name-only', 'HEAD'],
        ['git', 'ls-files', '--others', '--exclude-standard'],
    ] as $command) {
        $result = php_fan_ai_verify_run($command, $root);
        if ($result['exit_code'] !== 0) {
            continue;
        }
        foreach (explode("\n", trim($result['stdout'])) as $file) {
            if ($file !== '' && str_ends_with($file, '.php') && is_file($root . '/' . $file)) {
                $files[$file] = true;
            }
        }
    }

    $files = array_keys($files);
    sort($files);

    return $files;
}

function php_fan_ai_verify_check(string $name, string $status, string $message, array $details = []): array
{
    return [
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'details' => $details,
    ];
}

function php_fan_ai_verify_run(array $command, string $cwd): array
{
    $descriptor = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptor, $pipes, $cwd);
    if (!is_resource($process)) {
        return [
            'command' => $command,
            'exit_code' => 1,
            'stdout' => '',
            'stderr' => 'Unable to start process.',
        ];
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    return [
        'command' => $command,
        'exit_code' => $exitCode,
        'stdout' => is_string($stdout) ? $stdout : '',
        'stderr' => is_string($stderr) ? $stderr : '',
    ];
}

function php_fan_ai_verify_render_text(array $result): string
{
    $lines = ['AI verification: ' . strtoupper($result['status'])];
    foreach ($result['checks'] as $check) {
        $lines[] = sprintf('[%s] %s - %s', strtoupper($check['status']), $check['name'], $check['message']);
    }

    return implode("\n", $lines) . "\n";
}

function php_fan_ai_verify_main(array $argv): int
{
    $root = dirname(__DIR__);
    $result = php_fan_ai_verify($root, $argv);

    if (in_array('--json', $argv, true)) {
        fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    } else {
        fwrite(STDOUT, php_fan_ai_verify_render_text($result));
    }

    return $result['status'] === 'pass' ? 0 : 1;
}

if (PHP_SAPI === 'cli' && realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    exit(php_fan_ai_verify_main($argv));
}
