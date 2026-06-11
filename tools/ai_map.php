<?php

declare(strict_types=1);

/**
 * Builds a machine-readable project map for AI coding agents.
 *
 * Usage:
 *   php tools/ai_map.php --json
 *   php tools/ai_map.php --write
 */

function php_fan_ai_build_map(string $root): array
{
    $root = rtrim($root, DIRECTORY_SEPARATOR);
    $sourceRoots = ['core', 'project', 'htdocs', 'tools'];
    $testRoots = ['unit'];
    $phpFiles = php_fan_ai_php_files($root, array_merge($sourceRoots, $testRoots));
    $productionPhpFiles = array_values(array_filter(
        $phpFiles,
        static fn(string $file): bool => !str_starts_with($file, 'unit/')
    ));
    $serviceMap = php_fan_ai_service_map($root, $productionPhpFiles);

    return [
        'schema_version' => 1,
        'generated_at' => gmdate(DATE_ATOM),
        'entrypoints' => [
            'web' => 'htdocs/index.php',
        ],
        'source_roots' => $sourceRoots,
        'test_roots' => $testRoots,
        'autoload' => php_fan_ai_composer_autoload($root),
        'ai_docs' => php_fan_ai_files($root, ['.ai'], static fn(string $file): bool => str_ends_with($file, '.md')),
        'commands' => [
            'test' => 'php vendor/bin/phpunit --configuration phpunit.xml',
            'ai_map_json' => 'php tools/ai_map.php --json',
            'ai_verify' => 'php tools/ai_verify.php',
        ],
        'counts' => [
            'php_files' => count($phpFiles),
            'production_php_files' => count($productionPhpFiles),
            'strict_production_php_files' => php_fan_ai_count_strict_files($root, $productionPhpFiles),
            'meta_files' => count(php_fan_ai_meta_files($root)),
            'template_files' => count(php_fan_ai_template_files($root)),
            'registered_service_ids' => count($serviceMap['registered']),
            'referenced_service_ids' => count($serviceMap['referenced']),
        ],
        'files' => [
            'meta' => php_fan_ai_meta_files($root),
            'templates' => php_fan_ai_template_files($root),
        ],
        'services' => $serviceMap,
        'dynamic_boundaries' => [
            'manual_loading_adapters' => [
                'core/adapter/php_array_file.php',
                'core/adapter/php_template_file.php',
                'core/adapter/bootstrap_loader_file_storage.php',
                'core/adapter/compiled_template_loader.php',
                'core/adapter/project_tool_loader.php',
                'core/adapter/error_demonstrator_loader.php',
                'core/adapter/zend_autoloader.php',
            ],
            'legacy_metadata' => '*.meta.php',
            'legacy_templates' => '*.tpl',
            'request_globals_adapter' => 'core/adapter/request_input_native_environment.php',
        ],
        'verification' => [
            'phpunit' => 'php vendor/bin/phpunit --configuration phpunit.xml',
            'diff_check' => 'git diff --check',
        ],
    ];
}

function php_fan_ai_composer_autoload(string $root): array
{
    $composerFile = $root . '/composer.json';
    if (!is_file($composerFile)) {
        return [];
    }

    $composer = json_decode((string)file_get_contents($composerFile), true);
    if (!is_array($composer)) {
        return [];
    }

    return [
        'psr-4' => $composer['autoload']['psr-4'] ?? [],
        'files' => $composer['autoload']['files'] ?? [],
        'dev_psr-4' => $composer['autoload-dev']['psr-4'] ?? [],
    ];
}

function php_fan_ai_service_map(string $root, array $relativePhpFiles): array
{
    $registered = [];
    $aliases = [];
    $referenced = [];
    $constants = php_fan_ai_service_id_constants($root);

    foreach ($relativePhpFiles as $relativeFile) {
        $source = file_get_contents($root . '/' . $relativeFile);
        if (!is_string($source)) {
            continue;
        }

        if (
            str_starts_with($relativeFile, 'core/di/')
            && preg_match_all('/->\s*factory\s*\(\s*[\'"]([^\'"]+)[\'"]/', $source, $matches) > 0
        ) {
            foreach ($matches[1] as $id) {
                $registered[$id][] = $relativeFile;
            }
        }

        if (
            str_starts_with($relativeFile, 'core/di/')
            && preg_match_all('/->\s*factory\s*\(\s*service_id::([A-Z0-9_]+)/', $source, $matches) > 0
        ) {
            foreach ($matches[1] as $constant) {
                if (isset($constants[$constant])) {
                    $registered[$constants[$constant]][] = $relativeFile;
                }
            }
        }

        if (preg_match_all('/\$container\s*->\s*set\s*\(\s*[\'"]([^\'"]+)[\'"]/', $source, $matches) > 0) {
            foreach ($matches[1] as $id) {
                $registered[$id][] = $relativeFile;
            }
        }

        if (preg_match_all('/\$container\s*->\s*set\s*\(\s*service_id::([A-Z0-9_]+)/', $source, $matches) > 0) {
            foreach ($matches[1] as $constant) {
                if (isset($constants[$constant])) {
                    $registered[$constants[$constant]][] = $relativeFile;
                }
            }
        }

        if (
            str_starts_with($relativeFile, 'core/di/')
            && preg_match_all('/->\s*alias\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]/', $source, $matches, PREG_SET_ORDER) > 0
        ) {
            foreach ($matches as $match) {
                $aliases[$match[1]] = [
                    'target' => $match[2],
                    'file' => $relativeFile,
                ];
            }
        }

        if (preg_match_all('/(?:\$container|\$this->container\(\)|\$this->context\(\)->container\(\))\s*->\s*get\s*\(\s*[\'"]([^\'"]+)[\'"]/', $source, $matches) > 0) {
            foreach ($matches[1] as $id) {
                $referenced[$id][] = $relativeFile;
            }
        }

        if (preg_match_all('/(?:\$container|\$this->container\(\)|\$this->context\(\)->container\(\))\s*->\s*get\s*\(\s*service_id::([A-Z0-9_]+)/', $source, $matches) > 0) {
            foreach ($matches[1] as $constant) {
                if (isset($constants[$constant])) {
                    $referenced[$constants[$constant]][] = $relativeFile;
                }
            }
        }
    }

    return [
        'registered' => php_fan_ai_sorted_occurrences($registered),
        'aliases' => $aliases,
        'referenced' => php_fan_ai_sorted_occurrences($referenced),
        'constants' => $constants,
    ];
}

function php_fan_ai_service_id_constants(string $root): array
{
    $file = $root . '/core/di/service_id.php';
    if (!is_file($file)) {
        return [];
    }

    $source = (string)file_get_contents($file);
    if (preg_match_all('/public\s+const\s+([A-Z0-9_]+)\s*=\s*[\'"]([^\'"]+)[\'"]/', $source, $matches, PREG_SET_ORDER) === 0) {
        return [];
    }

    $constants = [];
    foreach ($matches as $match) {
        $constants[$match[1]] = $match[2];
    }
    ksort($constants);

    return $constants;
}

function php_fan_ai_sorted_occurrences(array $occurrences): array
{
    ksort($occurrences);
    foreach ($occurrences as $id => $files) {
        $files = array_values(array_unique($files));
        sort($files);
        $occurrences[$id] = $files;
    }

    return $occurrences;
}

function php_fan_ai_count_strict_files(string $root, array $relativePhpFiles): int
{
    $count = 0;
    foreach ($relativePhpFiles as $relativeFile) {
        $source = file_get_contents($root . '/' . $relativeFile);
        if (is_string($source) && str_contains($source, 'declare(strict_types=1)')) {
            $count++;
        }
    }

    return $count;
}

function php_fan_ai_meta_files(string $root): array
{
    return php_fan_ai_files(
        $root,
        ['core', 'project', 'htdocs'],
        static fn(string $file): bool => str_ends_with($file, '.meta.php')
    );
}

function php_fan_ai_template_files(string $root): array
{
    return php_fan_ai_files(
        $root,
        ['core', 'project', 'htdocs'],
        static fn(string $file): bool => str_ends_with($file, '.tpl')
    );
}

function php_fan_ai_php_files(string $root, array $roots): array
{
    return php_fan_ai_files(
        $root,
        $roots,
        static fn(string $file): bool => str_ends_with($file, '.php')
    );
}

function php_fan_ai_files(string $root, array $roots, callable $filter): array
{
    $files = [];
    foreach ($roots as $relativeRoot) {
        $absoluteRoot = $root . '/' . $relativeRoot;
        if (!is_dir($absoluteRoot)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absoluteRoot, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $relativePath = str_replace($root . '/', '', $file->getPathname());
            if (php_fan_ai_is_excluded_path($relativePath)) {
                continue;
            }

            if ($filter($relativePath)) {
                $files[] = $relativePath;
            }
        }
    }

    sort($files);

    return $files;
}

function php_fan_ai_is_excluded_path(string $relativePath): bool
{
    foreach (['vendor/', 'node_modules/', 'legacy_assets/', '.git/'] as $prefix) {
        if (str_starts_with($relativePath, $prefix)) {
            return true;
        }
    }

    return str_contains($relativePath, '/.venv/');
}

function php_fan_ai_render_markdown(array $map): string
{
    $lines = [
        '# PHP-FAN AI Map',
        '',
        '- Generated at: ' . $map['generated_at'],
        '- Production PHP files: ' . $map['counts']['production_php_files'],
        '- Strict production PHP files: ' . $map['counts']['strict_production_php_files'],
        '- Registered service ids: ' . $map['counts']['registered_service_ids'],
        '- Referenced service ids: ' . $map['counts']['referenced_service_ids'],
        '- Meta files: ' . $map['counts']['meta_files'],
        '- Template files: ' . $map['counts']['template_files'],
        '',
        '## Commands',
        '',
    ];

    foreach ($map['commands'] as $name => $command) {
        $lines[] = '- `' . $name . '`: `' . $command . '`';
    }

    return implode("\n", $lines) . "\n";
}

function php_fan_ai_write_map(string $root, array $map): string
{
    $target = $root . '/.ai/map.json';
    $encoded = json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (!is_string($encoded)) {
        throw new RuntimeException('Unable to encode AI map.');
    }
    file_put_contents($target, $encoded . "\n");

    return $target;
}

function php_fan_ai_main(array $argv): int
{
    $root = dirname(__DIR__);
    $map = php_fan_ai_build_map($root);

    if (in_array('--write', $argv, true)) {
        $target = php_fan_ai_write_map($root, $map);
        fwrite(STDOUT, "wrote={$target}\n");

        return 0;
    }

    if (in_array('--json', $argv, true)) {
        fwrite(STDOUT, json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

        return 0;
    }

    fwrite(STDOUT, php_fan_ai_render_markdown($map));

    return 0;
}

if (PHP_SAPI === 'cli' && realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    exit(php_fan_ai_main($argv));
}
