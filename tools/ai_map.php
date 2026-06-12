<?php

declare(strict_types=1);

/**
 * Builds a machine-readable project map for AI coding agents.
 *
 * Usage:
 *   php tools/ai_map.php --json
 *   php tools/ai_map.php --write
 *   php tools/ai_map.php --validate
 */

require_once __DIR__ . '/../core/di/service_descriptor.php';

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
    $metaFiles = php_fan_ai_meta_files($root);
    $templateFiles = php_fan_ai_template_files($root);

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
            'ai_map_validate' => 'php tools/ai_map.php --validate',
            'ai_explain' => 'php tools/ai_explain.php <file> --json',
            'ai_verify' => 'php tools/ai_verify.php',
            'ai_static' => 'php tools/ai_static_check.php',
            'fan_ai_map' => 'php fan ai:map --json',
            'fan_ai_map_validate' => 'php fan ai:map --validate',
            'fan_ai_services' => 'php fan ai:services --json',
            'fan_ai_explain' => 'php fan ai:explain <file> --json',
            'fan_ai_verify' => 'php fan ai:verify',
            'fan_ai_doctor' => 'php fan ai:doctor',
        ],
        'counts' => [
            'php_files' => count($phpFiles),
            'production_php_files' => count($productionPhpFiles),
            'strict_production_php_files' => php_fan_ai_count_strict_files($root, $productionPhpFiles),
            'meta_files' => count($metaFiles),
            'template_files' => count($templateFiles),
            'registered_service_ids' => count($serviceMap['registered']),
            'referenced_service_ids' => count($serviceMap['referenced']),
            'service_descriptors' => count($serviceMap['descriptors']),
        ],
        'files' => [
            'meta' => $metaFiles,
            'templates' => $templateFiles,
        ],
        'services' => $serviceMap,
        'metadata' => php_fan_ai_metadata_map($root, $metaFiles, $templateFiles),
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

function php_fan_ai_metadata_map(string $root, array $metaFiles, array $templateFiles): array
{
    $metaEntries = [];
    $topLevelKeyUsage = [];
    $ownKeyUsage = [];
    $commonKeyUsage = [];

    foreach ($metaFiles as $relativeFile) {
        $data = php_fan_ai_meta_file_data($root, $relativeFile);
        $topLevelKeys = array_keys($data);
        $ownKeys = isset($data['own']) && is_array($data['own']) ? array_keys($data['own']) : [];
        $commonKeys = isset($data['common']) && is_array($data['common']) ? array_keys($data['common']) : [];

        php_fan_ai_increment_usage($topLevelKeyUsage, $topLevelKeys);
        php_fan_ai_increment_usage($ownKeyUsage, $ownKeys);
        php_fan_ai_increment_usage($commonKeyUsage, $commonKeys);

        $metaEntries[$relativeFile] = [
            'top_level_keys' => $topLevelKeys,
            'own_keys' => $ownKeys,
            'common_keys' => $commonKeys,
            'paired_template' => php_fan_ai_paired_template($relativeFile, $templateFiles),
        ];
    }

    $templateEntries = [];
    $placeholderUsage = [];
    foreach ($templateFiles as $relativeFile) {
        $placeholders = php_fan_ai_template_placeholders($root, $relativeFile);
        php_fan_ai_increment_usage($placeholderUsage, $placeholders);
        $templateEntries[$relativeFile] = [
            'placeholders' => $placeholders,
            'paired_meta' => php_fan_ai_paired_meta_file($relativeFile, $metaFiles),
        ];
    }

    ksort($metaEntries);
    ksort($templateEntries);
    ksort($topLevelKeyUsage);
    ksort($ownKeyUsage);
    ksort($commonKeyUsage);
    ksort($placeholderUsage);

    return [
        'meta_schema' => '.ai/meta.schema.json',
        'meta' => [
            'files' => $metaEntries,
            'top_level_key_usage' => $topLevelKeyUsage,
            'own_key_usage' => $ownKeyUsage,
            'common_key_usage' => $commonKeyUsage,
        ],
        'templates' => [
            'files' => $templateEntries,
            'placeholder_usage' => $placeholderUsage,
        ],
    ];
}

function php_fan_ai_meta_file_data(string $root, string $relativeFile): array
{
    $absoluteFile = $root . '/' . $relativeFile;
    if (!is_file($absoluteFile)) {
        return [];
    }

    try {
        $data = (static fn(string $file): mixed => include $file)($absoluteFile);
    } catch (\Throwable) {
        return [];
    }

    return is_array($data) ? $data : [];
}

function php_fan_ai_increment_usage(array &$usage, array $keys): void
{
    foreach ($keys as $key) {
        if (!is_string($key) && !is_int($key)) {
            continue;
        }
        $key = (string)$key;
        $usage[$key] = ($usage[$key] ?? 0) + 1;
    }
}

function php_fan_ai_paired_template(string $metaFile, array $templateFiles): ?string
{
    $candidate = substr($metaFile, 0, -strlen('.meta.php')) . '.tpl';

    return in_array($candidate, $templateFiles, true) ? $candidate : null;
}

function php_fan_ai_paired_meta_file(string $templateFile, array $metaFiles): ?string
{
    $candidate = substr($templateFile, 0, -strlen('.tpl')) . '.meta.php';

    return in_array($candidate, $metaFiles, true) ? $candidate : null;
}

function php_fan_ai_template_placeholders(string $root, string $relativeFile): array
{
    $source = file_get_contents($root . '/' . $relativeFile);
    if (!is_string($source)) {
        return [];
    }

    $placeholders = [];
    if (preg_match_all('/\{[A-Za-z_][A-Za-z0-9_]*\}/', $source, $matches) > 0) {
        $placeholders = array_merge($placeholders, $matches[0]);
    }

    if (preg_match_all('/\{\{([^}]*)\}\}|\{%([^%]*)%\}/', $source, $matches, PREG_SET_ORDER) > 0) {
        $ignored = [
            'and' => true,
            'default' => true,
            'else' => true,
            'elseif' => true,
            'endfor' => true,
            'endif' => true,
            'false' => true,
            'for' => true,
            'if' => true,
            'in' => true,
            'last' => true,
            'loop' => true,
            'not' => true,
            'null' => true,
            'or' => true,
            'true' => true,
        ];
        foreach ($matches as $match) {
            $expression = $match[1] !== '' ? $match[1] : $match[2];
            if (preg_match_all('/\b[A-Za-z_][A-Za-z0-9_]*\b/', $expression, $tokens) === 0) {
                continue;
            }
            foreach ($tokens[0] as $token) {
                if (!isset($ignored[$token])) {
                    $placeholders[] = $token;
                }
            }
        }
    }

    $placeholders = array_values(array_unique($placeholders));
    sort($placeholders);

    return $placeholders;
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

    $registered = php_fan_ai_sorted_occurrences($registered);

    return [
        'registered' => $registered,
        'aliases' => $aliases,
        'referenced' => php_fan_ai_sorted_occurrences($referenced),
        'constants' => $constants,
        'descriptors' => php_fan_ai_service_descriptors($root, $relativePhpFiles, $registered, $aliases, $constants),
    ];
}

function php_fan_ai_service_descriptors(string $root, array $relativePhpFiles, array $registered, array $aliases, array $constants): array
{
    $registrations = php_fan_ai_service_registrations($root, $relativePhpFiles, $constants);
    $creatorMethods = php_fan_ai_service_creator_methods($root, $relativePhpFiles, $constants);
    $descriptors = [];

    foreach ($registered as $id => $registrarFiles) {
        $registration = $registrations[$id] ?? [
            'creator_methods' => [],
            'dependencies' => [],
            'runtime_arguments' => [],
            'shared' => null,
        ];
        $methods = $registration['creator_methods'];
        $dependencies = $registration['dependencies'];
        $runtimeArguments = $registration['runtime_arguments'];

        foreach ($methods as $method) {
            if (!isset($creatorMethods[$method])) {
                continue;
            }
            $dependencies = array_merge($dependencies, $creatorMethods[$method]['dependencies']);
        }

        $dependencies = array_values(array_diff(array_unique($dependencies), [$id]));
        sort($dependencies);
        $runtimeArguments = array_values(array_unique($runtimeArguments));
        sort($runtimeArguments);
        $aliasIds = php_fan_ai_aliases_for_service($id, $aliases);

        $descriptor = new \fan\core\di\service_descriptor(
            $id,
            $registrarFiles,
            array_values(array_unique($methods)),
            $registration['shared'],
            $dependencies,
            [
                'registrar_files' => $registrarFiles,
                'creator_methods' => array_values(array_unique($methods)),
            ],
            [
                'container_dependencies' => $dependencies,
                'runtime_arguments' => $runtimeArguments,
            ],
            $aliasIds
        );
        $descriptors[$id] = $descriptor->toArray();
    }

    ksort($descriptors);

    return $descriptors;
}

function php_fan_ai_service_registrations(string $root, array $relativePhpFiles, array $constants): array
{
    $registrations = [];

    foreach ($relativePhpFiles as $relativeFile) {
        if (!str_starts_with($relativeFile, 'core/di/')) {
            continue;
        }

        $source = file_get_contents($root . '/' . $relativeFile);
        if (!is_string($source)) {
            continue;
        }

        foreach (php_fan_ai_extract_fluent_calls($source, 'factory') as $call) {
            $id = php_fan_ai_service_id_from_registration_call($call, $constants);
            if ($id === null) {
                continue;
            }

            $registrations[$id]['creator_methods'] ??= [];
            $registrations[$id]['dependencies'] ??= [];
            $registrations[$id]['runtime_arguments'] ??= [];
            $registrations[$id]['shared'] ??= true;
            $registrations[$id]['creator_methods'] = array_merge(
                $registrations[$id]['creator_methods'],
                php_fan_ai_creator_methods_from_source($call)
            );
            $registrations[$id]['dependencies'] = array_merge(
                $registrations[$id]['dependencies'],
                php_fan_ai_container_dependencies_from_source($call, $constants)
            );
            $registrations[$id]['runtime_arguments'] = array_merge(
                $registrations[$id]['runtime_arguments'],
                php_fan_ai_runtime_arguments_from_source($call)
            );
            if (preg_match('/,\s*false\s*\)\s*$/s', $call) === 1) {
                $registrations[$id]['shared'] = false;
            }
        }
    }

    foreach ($registrations as $id => $registration) {
        $registration['creator_methods'] = array_values(array_unique($registration['creator_methods']));
        sort($registration['creator_methods']);
        $registration['dependencies'] = array_values(array_unique($registration['dependencies']));
        sort($registration['dependencies']);
        $registration['runtime_arguments'] = array_values(array_unique($registration['runtime_arguments']));
        sort($registration['runtime_arguments']);
        $registrations[$id] = $registration;
    }

    return $registrations;
}

function php_fan_ai_aliases_for_service(string $id, array $aliases): array
{
    $result = [];
    foreach ($aliases as $alias => $entry) {
        if (($entry['target'] ?? null) === $id) {
            $result[] = $alias;
        }
    }

    sort($result);

    return $result;
}

function php_fan_ai_service_creator_methods(string $root, array $relativePhpFiles, array $constants): array
{
    $methods = [];

    foreach ($relativePhpFiles as $relativeFile) {
        if (!str_starts_with($relativeFile, 'core/di/') || !str_ends_with($relativeFile, '_service_creator.php')) {
            continue;
        }

        $source = file_get_contents($root . '/' . $relativeFile);
        if (!is_string($source)) {
            continue;
        }

        foreach (php_fan_ai_extract_public_creator_methods($source) as $methodName => $body) {
            $methods[$methodName] = [
                'file' => $relativeFile,
                'dependencies' => php_fan_ai_container_dependencies_from_source($body, $constants),
            ];
        }
    }

    ksort($methods);

    return $methods;
}

function php_fan_ai_extract_fluent_calls(string $source, string $method): array
{
    $calls = [];
    $offset = 0;
    $needle = '->' . $method . '(';

    while (($start = strpos($source, $needle, $offset)) !== false) {
        $open = strpos($source, '(', $start);
        if ($open === false) {
            break;
        }

        $close = php_fan_ai_find_matching_paren($source, $open);
        if ($close === null) {
            break;
        }

        $calls[] = substr($source, $start, $close - $start + 1);
        $offset = $close + 1;
    }

    return $calls;
}

function php_fan_ai_find_matching_paren(string $source, int $open): ?int
{
    $length = strlen($source);
    $depth = 0;
    $stringQuote = null;
    $escaped = false;

    for ($i = $open; $i < $length; $i++) {
        $char = $source[$i];

        if ($stringQuote !== null) {
            if ($escaped) {
                $escaped = false;
                continue;
            }
            if ($char === '\\') {
                $escaped = true;
                continue;
            }
            if ($char === $stringQuote) {
                $stringQuote = null;
            }
            continue;
        }

        if ($char === '\'' || $char === '"') {
            $stringQuote = $char;
            continue;
        }

        if ($char === '(') {
            $depth++;
            continue;
        }

        if ($char === ')') {
            $depth--;
            if ($depth === 0) {
                return $i;
            }
        }
    }

    return null;
}

function php_fan_ai_service_id_from_registration_call(string $call, array $constants): ?string
{
    if (preg_match('/^->factory\s*\(\s*[\'"]([^\'"]+)[\'"]/', $call, $match) === 1) {
        return $match[1];
    }

    if (preg_match('/^->factory\s*\(\s*service_id::([A-Z0-9_]+)/', $call, $match) === 1) {
        return $constants[$match[1]] ?? null;
    }

    return null;
}

function php_fan_ai_creator_methods_from_source(string $source): array
{
    if (preg_match_all('/->\s*(create[A-Za-z0-9_]+)\s*\(/', $source, $matches) === 0) {
        return [];
    }

    $methods = array_values(array_unique($matches[1]));
    sort($methods);

    return $methods;
}

function php_fan_ai_runtime_arguments_from_source(string $source): array
{
    if (preg_match('/(?:static\s+)?(?:function|fn)\s*\(([^)]*)\)/s', $source, $match) !== 1) {
        return [];
    }

    $arguments = [];
    foreach (explode(',', $match[1]) as $parameter) {
        if (preg_match('/\$([A-Za-z_][A-Za-z0-9_]*)/', $parameter, $parameterMatch) !== 1) {
            continue;
        }
        $name = $parameterMatch[1];
        if ($name === 'container') {
            continue;
        }
        $arguments[] = $name;
    }

    $arguments = array_values(array_unique($arguments));
    sort($arguments);

    return $arguments;
}

function php_fan_ai_extract_public_creator_methods(string $source): array
{
    $methods = [];
    $offset = 0;

    while (preg_match('/public\s+function\s+(create[A-Za-z0-9_]+)\s*\(/', $source, $match, PREG_OFFSET_CAPTURE, $offset) === 1) {
        $methodName = $match[1][0];
        $methodStart = $match[0][1];
        $openBrace = strpos($source, '{', $methodStart);
        if ($openBrace === false) {
            break;
        }

        $closeBrace = php_fan_ai_find_matching_brace($source, $openBrace);
        if ($closeBrace === null) {
            break;
        }

        $methods[$methodName] = substr($source, $openBrace + 1, $closeBrace - $openBrace - 1);
        $offset = $closeBrace + 1;
    }

    return $methods;
}

function php_fan_ai_find_matching_brace(string $source, int $open): ?int
{
    $length = strlen($source);
    $depth = 0;
    $stringQuote = null;
    $escaped = false;

    for ($i = $open; $i < $length; $i++) {
        $char = $source[$i];

        if ($stringQuote !== null) {
            if ($escaped) {
                $escaped = false;
                continue;
            }
            if ($char === '\\') {
                $escaped = true;
                continue;
            }
            if ($char === $stringQuote) {
                $stringQuote = null;
            }
            continue;
        }

        if ($char === '\'' || $char === '"') {
            $stringQuote = $char;
            continue;
        }

        if ($char === '{') {
            $depth++;
            continue;
        }

        if ($char === '}') {
            $depth--;
            if ($depth === 0) {
                return $i;
            }
        }
    }

    return null;
}

function php_fan_ai_container_dependencies_from_source(string $source, array $constants): array
{
    $dependencies = [];

    if (preg_match_all('/(?:\$container|\$this->container\(\)|\$this->context\(\)->container\(\))\s*->\s*get\s*\(\s*[\'"]([^\'"]+)[\'"]/', $source, $matches) > 0) {
        foreach ($matches[1] as $id) {
            $dependencies[] = $id;
        }
    }

    if (preg_match_all('/(?:\$container|\$this->container\(\)|\$this->context\(\)->container\(\))\s*->\s*get\s*\(\s*service_id::([A-Z0-9_]+)/', $source, $matches) > 0) {
        foreach ($matches[1] as $constant) {
            if (isset($constants[$constant])) {
                $dependencies[] = $constants[$constant];
            }
        }
    }

    $dependencies = array_values(array_unique($dependencies));
    sort($dependencies);

    return $dependencies;
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

function php_fan_ai_validate_map_contract(string $root, array $map): array
{
    $errors = [];

    if (!is_file($root . '/.ai/map.schema.json')) {
        $errors[] = '.ai/map.schema.json is missing.';
    } else {
        $schema = json_decode((string)file_get_contents($root . '/.ai/map.schema.json'), true);
        if (!is_array($schema)) {
            $errors[] = '.ai/map.schema.json is not valid JSON.';
        }
    }

    foreach (['schema_version', 'generated_at', 'entrypoints', 'source_roots', 'test_roots', 'autoload', 'ai_docs', 'commands', 'counts', 'files', 'services', 'metadata', 'dynamic_boundaries', 'verification'] as $key) {
        if (!array_key_exists($key, $map)) {
            $errors[] = 'AI map is missing required key: ' . $key;
        }
    }

    if (($map['schema_version'] ?? null) !== 1) {
        $errors[] = 'AI map schema_version must be 1.';
    }
    if (!is_string($map['generated_at'] ?? null) || strtotime($map['generated_at']) === false) {
        $errors[] = 'AI map generated_at must be an ISO-like date string.';
    }

    foreach (['entrypoints', 'autoload', 'commands', 'counts', 'files', 'services', 'metadata', 'dynamic_boundaries', 'verification'] as $key) {
        if (isset($map[$key]) && !is_array($map[$key])) {
            $errors[] = 'AI map key must be an array: ' . $key;
        }
    }
    foreach (['source_roots', 'test_roots', 'ai_docs'] as $key) {
        if (isset($map[$key]) && !php_fan_ai_is_string_list($map[$key])) {
            $errors[] = 'AI map key must be a list of strings: ' . $key;
        }
    }
    foreach (($map['commands'] ?? []) as $name => $command) {
        if (!is_string($name) || !is_string($command) || $command === '') {
            $errors[] = 'AI map command entries must be non-empty strings.';
            break;
        }
    }

    foreach (['registered', 'aliases', 'referenced', 'constants', 'descriptors'] as $key) {
        if (!isset($map['services'][$key]) || !is_array($map['services'][$key])) {
            $errors[] = 'AI map services section is missing array key: ' . $key;
        }
    }
    foreach (($map['services']['descriptors'] ?? []) as $id => $descriptor) {
        if (!is_string($id) || !is_array($descriptor)) {
            $errors[] = 'AI map service descriptor entries must be arrays keyed by service id.';
            continue;
        }
        if (($descriptor['id'] ?? null) !== $id) {
            $errors[] = 'AI map descriptor id mismatch for service: ' . $id;
        }
        foreach (['registrar_files', 'creator_methods', 'dependencies', 'aliases'] as $listKey) {
            if (!isset($descriptor[$listKey]) || !php_fan_ai_is_string_list($descriptor[$listKey])) {
                $errors[] = 'AI map descriptor "' . $id . '" must have string-list key: ' . $listKey;
            }
        }
        if (array_key_exists('shared', $descriptor) && !is_bool($descriptor['shared']) && $descriptor['shared'] !== null) {
            $errors[] = 'AI map descriptor "' . $id . '" shared must be boolean or null.';
        }
        if (!isset($descriptor['factory_origin']) || !is_array($descriptor['factory_origin'])) {
            $errors[] = 'AI map descriptor "' . $id . '" must have factory_origin.';
        } else {
            foreach (['registrar_files', 'creator_methods'] as $listKey) {
                if (!isset($descriptor['factory_origin'][$listKey]) || !php_fan_ai_is_string_list($descriptor['factory_origin'][$listKey])) {
                    $errors[] = 'AI map descriptor "' . $id . '" factory_origin must have string-list key: ' . $listKey;
                }
            }
        }
        if (!isset($descriptor['factory_arguments']) || !is_array($descriptor['factory_arguments'])) {
            $errors[] = 'AI map descriptor "' . $id . '" must have factory_arguments.';
        } else {
            foreach (['container_dependencies', 'runtime_arguments'] as $listKey) {
                if (!isset($descriptor['factory_arguments'][$listKey]) || !php_fan_ai_is_string_list($descriptor['factory_arguments'][$listKey])) {
                    $errors[] = 'AI map descriptor "' . $id . '" factory_arguments must have string-list key: ' . $listKey;
                }
            }
        }
    }

    if (($map['metadata']['meta_schema'] ?? null) !== '.ai/meta.schema.json') {
        $errors[] = 'AI map metadata.meta_schema must point to .ai/meta.schema.json.';
    }
    foreach (['meta', 'templates'] as $key) {
        if (!isset($map['metadata'][$key]['files']) || !is_array($map['metadata'][$key]['files'])) {
            $errors[] = 'AI map metadata.' . $key . '.files must be an array.';
        }
    }

    $metaFiles = $map['files']['meta'] ?? [];
    $templateFiles = $map['files']['templates'] ?? [];
    if (!php_fan_ai_is_string_list($metaFiles)) {
        $errors[] = 'AI map files.meta must be a list of strings.';
        $metaFiles = [];
    }
    if (!php_fan_ai_is_string_list($templateFiles)) {
        $errors[] = 'AI map files.templates must be a list of strings.';
        $templateFiles = [];
    }

    $metadataMetaFiles = array_keys($map['metadata']['meta']['files'] ?? []);
    sort($metadataMetaFiles);
    $sortedMetaFiles = $metaFiles;
    sort($sortedMetaFiles);
    if ($metadataMetaFiles !== $sortedMetaFiles) {
        $errors[] = 'AI map metadata.meta.files keys must match files.meta.';
    }

    $metadataTemplateFiles = array_keys($map['metadata']['templates']['files'] ?? []);
    sort($metadataTemplateFiles);
    $sortedTemplateFiles = $templateFiles;
    sort($sortedTemplateFiles);
    if ($metadataTemplateFiles !== $sortedTemplateFiles) {
        $errors[] = 'AI map metadata.templates.files keys must match files.templates.';
    }

    foreach (($map['metadata']['meta']['files'] ?? []) as $metaFile => $entry) {
        if (!is_array($entry)) {
            $errors[] = 'AI map metadata meta entry must be an array: ' . $metaFile;
            continue;
        }
        foreach (['top_level_keys', 'own_keys', 'common_keys'] as $listKey) {
            if (!isset($entry[$listKey]) || !php_fan_ai_is_string_list($entry[$listKey])) {
                $errors[] = 'AI map metadata meta entry "' . $metaFile . '" must have string-list key: ' . $listKey;
            }
        }
        $pairedTemplate = $entry['paired_template'] ?? null;
        if ($pairedTemplate !== null && (!is_string($pairedTemplate) || !in_array($pairedTemplate, $templateFiles, true) || !is_file($root . '/' . $pairedTemplate))) {
            $errors[] = 'AI map paired template is invalid for meta file: ' . $metaFile;
        }
    }

    return $errors;
}

function php_fan_ai_is_string_list(mixed $value): bool
{
    if (!is_array($value) || !array_is_list($value)) {
        return false;
    }

    foreach ($value as $item) {
        if (!is_string($item)) {
            return false;
        }
    }

    return true;
}

function php_fan_ai_main(array $argv): int
{
    $root = dirname(__DIR__);
    $map = php_fan_ai_build_map($root);

    if (in_array('--validate', $argv, true)) {
        $errors = php_fan_ai_validate_map_contract($root, $map);
        $result = [
            'status' => $errors === [] ? 'pass' : 'fail',
            'errors' => $errors,
        ];
        if (in_array('--json', $argv, true)) {
            fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        } else {
            fwrite(STDOUT, 'AI map validation: ' . strtoupper($result['status']) . "\n");
            foreach ($errors as $error) {
                fwrite(STDOUT, '- ' . $error . "\n");
            }
        }

        return $errors === [] ? 0 : 1;
    }

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
