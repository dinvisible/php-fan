<?php

declare(strict_types=1);

/**
 * Runs the verification checks an AI coding agent should use before finishing.
 *
 * Usage:
 *   php tools/ai_verify.php
 *   php tools/ai_verify.php --json
 *   php tools/ai_verify.php --changed
 *   php tools/ai_verify.php --no-phpunit
 *   php tools/ai_verify.php --skip-phpunit
 */

require_once __DIR__ . '/ai_map.php';

function php_fan_ai_verify(string $root, array $argv): array
{
    $changedOnly = in_array('--changed', $argv, true);
    $skipPhpunit = in_array('--skip-phpunit', $argv, true) || in_array('--no-phpunit', $argv, true);
    $checks = [];
    $checks[] = php_fan_ai_verify_tracked_noise($root);
    $checks[] = php_fan_ai_verify_diff_check($root);
    $checks[] = php_fan_ai_verify_changed_php_lint($root);
    $checks[] = php_fan_ai_verify_ai_map($root);
    $checks[] = php_fan_ai_verify_static_baseline($root);

    if ($changedOnly && !$skipPhpunit) {
        $checks[] = php_fan_ai_verify_focused_phpunit($root);
    } elseif (!$skipPhpunit) {
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
        $result = php_fan_ai_verify_run([PHP_BINARY, '-l', $file], $root);
        if ($result['exit_code'] !== 0) {
            return php_fan_ai_verify_check('php_lint_changed', 'fail', 'PHP lint failed for ' . $file, $result);
        }
    }

    return php_fan_ai_verify_check('php_lint_changed', 'pass', 'PHP lint passed for ' . count($files) . ' changed PHP file(s).');
}

function php_fan_ai_verify_ai_map(string $root): array
{
    try {
        $map = php_fan_ai_build_map($root);
        json_encode($map, JSON_THROW_ON_ERROR);
    } catch (\Throwable $exception) {
        return php_fan_ai_verify_check('ai_map_build', 'fail', 'AI map failed to build.', [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
        ]);
    }

    foreach (['schema_version', 'services', 'metadata', 'commands'] as $requiredKey) {
        if (!array_key_exists($requiredKey, $map)) {
            return php_fan_ai_verify_check('ai_map_build', 'fail', 'AI map is missing key: ' . $requiredKey);
        }
    }
    $contractErrors = php_fan_ai_validate_map_contract($root, $map);
    if ($contractErrors !== []) {
        return php_fan_ai_verify_check('ai_map_build', 'fail', 'AI map contract validation failed.', [
            'errors' => $contractErrors,
        ]);
    }

    return php_fan_ai_verify_check(
        'ai_map_build',
        'pass',
        'AI map built and encoded with ' . count($map['services']['descriptors']) . ' service descriptor(s).'
    );
}

function php_fan_ai_verify_static_baseline(string $root): array
{
    $files = ['phpstan.neon.dist', 'phpstan-baseline.neon', '.php-cs-fixer.dist.php'];
    $missing = array_values(array_filter($files, static fn(string $file): bool => !is_file($root . '/' . $file)));

    if ($missing !== []) {
        return php_fan_ai_verify_check('static_baseline', 'fail', 'Missing static tooling baseline: ' . implode(', ', $missing));
    }

    return php_fan_ai_verify_check('static_baseline', 'pass', 'Static analysis and formatter baseline config is present.', [
        'files' => $files,
    ]);
}

function php_fan_ai_verify_phpunit(string $root): array
{
    $phpunit = $root . '/vendor/bin/phpunit';
    if (!is_file($phpunit)) {
        return php_fan_ai_verify_check('phpunit', 'fail', 'Missing vendor/bin/phpunit. Run Composer install first.');
    }

    $result = php_fan_ai_verify_run([PHP_BINARY, 'vendor/bin/phpunit', '--configuration', 'phpunit.xml'], $root);
    if ($result['exit_code'] !== 0) {
        return php_fan_ai_verify_check('phpunit', 'fail', 'PHPUnit failed.', $result);
    }

    return php_fan_ai_verify_check('phpunit', 'pass', 'PHPUnit passed.', $result);
}

function php_fan_ai_verify_focused_phpunit(string $root): array
{
    $phpunit = $root . '/vendor/bin/phpunit';
    if (!is_file($phpunit)) {
        return php_fan_ai_verify_check('phpunit_changed', 'fail', 'Missing vendor/bin/phpunit. Run Composer install first.');
    }

    $testFiles = [
        'unit/core/AiToolingTest.php' => true,
        'unit/core/LegacyDiSourceInventoryTest.php' => true,
    ];
    foreach (php_fan_ai_verify_changed_php_files($root) as $file) {
        foreach (php_fan_ai_verify_related_test_files($root, $file) as $testFile) {
            $testFiles[$testFile] = true;
        }
    }

    $testFiles = array_keys($testFiles);
    sort($testFiles);
    $result = php_fan_ai_verify_run(array_merge([PHP_BINARY, 'vendor/bin/phpunit', '--configuration', 'phpunit.xml'], $testFiles), $root);
    if ($result['exit_code'] !== 0) {
        return php_fan_ai_verify_check('phpunit_changed', 'fail', 'Focused PHPUnit failed.', $result);
    }

    return php_fan_ai_verify_check('phpunit_changed', 'pass', 'Focused PHPUnit passed for ' . count($testFiles) . ' test file(s).', [
        'test_files' => $testFiles,
    ]);
}

function php_fan_ai_verify_related_test_files(string $root, string $relativeFile): array
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
        'details' => php_fan_ai_verify_details($details),
    ];
}

function php_fan_ai_verify_details(array $details): array
{
    if (!isset($details['command'], $details['exit_code'], $details['stdout'], $details['stderr'])) {
        return $details;
    }

    return [
        'command' => $details['command'],
        'exit_code' => $details['exit_code'],
        'stdout_tail' => php_fan_ai_verify_tail((string)$details['stdout']),
        'stderr_tail' => php_fan_ai_verify_tail((string)$details['stderr']),
    ];
}

function php_fan_ai_verify_tail(string $output, int $lines = 40): string
{
    $output = trim($output);
    if ($output === '') {
        return '';
    }

    $parts = explode("\n", $output);
    if (count($parts) <= $lines) {
        return $output;
    }

    return implode("\n", array_slice($parts, -$lines));
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
