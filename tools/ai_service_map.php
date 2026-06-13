<?php

declare(strict_types=1);

final class php_fan_ai_service_map_builder
{
    /**
     * @param list<string> $relativePhpFiles
     */
    public function __construct(
        private readonly string $root,
        private readonly array $relativePhpFiles
    )
    {
    }

    public function build(): array
    {
        $registered = [];
        $aliases = [];
        $referenced = [];
        $referencedLocations = [];
        $constants = php_fan_ai_service_id_constants($this->root);

        foreach ($this->relativePhpFiles as $relativeFile) {
            $source = file_get_contents($this->root . '/' . $relativeFile);
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
                && preg_match_all('/->\s*alias\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]/', $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) > 0
            ) {
                foreach ($matches as $match) {
                    $aliases[$match[1][0]] = [
                        'target' => $match[2][0],
                        'file' => $relativeFile,
                        'line' => php_fan_ai_source_line_number($source, (int)$match[0][1]),
                    ];
                }
            }

            if (preg_match_all('/(?:\$container|\$this->container|\$this->container\(\)|\$this->context\(\)->container\(\))\s*->\s*get\s*\(\s*[\'"]([^\'"]+)[\'"]/', $source, $matches, PREG_OFFSET_CAPTURE) > 0) {
                foreach ($matches[1] as $match) {
                    $id = $match[0];
                    $referenced[$id][] = $relativeFile;
                    $referencedLocations[$id][] = php_fan_ai_source_location(
                        $relativeFile,
                        php_fan_ai_source_line_number($source, (int)$match[1]),
                        value: $id
                    );
                }
            }

            if (preg_match_all('/(?:\$container|\$this->container|\$this->container\(\)|\$this->context\(\)->container\(\))\s*->\s*get\s*\(\s*service_id::([A-Z0-9_]+)/', $source, $matches, PREG_OFFSET_CAPTURE) > 0) {
                foreach ($matches[1] as $match) {
                    $constant = $match[0];
                    if (isset($constants[$constant])) {
                        $id = $constants[$constant];
                        $referenced[$id][] = $relativeFile;
                        $referencedLocations[$id][] = php_fan_ai_source_location(
                            $relativeFile,
                            php_fan_ai_source_line_number($source, (int)$match[1]),
                            value: $id
                        );
                    }
                }
            }

            if (preg_match_all('/\$(common|coreDependencies|utilityDependencies|clientDependencies|contentDependencies|controllerDependencies|tabDependencies|userDependencies|infrastructureDependencies|pagerDependencies|sessionDependencies)\s*->\s*([A-Za-z0-9_]+)\s*\(/', $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) > 0) {
                foreach ($matches as $match) {
                    $id = php_fan_ai_named_dependency_service_id((string)$match[1][0], (string)$match[2][0]);
                    if ($id === null) {
                        continue;
                    }
                    $referenced[$id][] = $relativeFile;
                    $referencedLocations[$id][] = php_fan_ai_source_location(
                        $relativeFile,
                        php_fan_ai_source_line_number($source, (int)$match[2][1]),
                        value: $id
                    );
                }
            }
        }

        $registered = php_fan_ai_sorted_occurrences($registered);
        $referenced = php_fan_ai_sorted_occurrences($referenced);
        $referencedLocations = php_fan_ai_sorted_source_location_occurrences($referencedLocations);

        return [
            'registered' => $registered,
            'aliases' => $aliases,
            'referenced' => $referenced,
            'referenced_locations' => $referencedLocations,
            'constants' => $constants,
            'descriptors' => php_fan_ai_service_descriptors($this->root, $this->relativePhpFiles, $registered, $aliases, $constants, $referenced, $referencedLocations),
        ];
    }
}

function php_fan_ai_service_descriptors(string $root, array $relativePhpFiles, array $registered, array $aliases, array $constants, array $referenced = [], array $referencedLocations = []): array
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
        $creatorMethodArguments = [];
        $classes = [];
        $factories = [];
        $configKeys = [];
        $sourceLocations = [
            'registrations' => $registration['source_locations']['registrations'] ?? [],
            'creator_methods' => [],
            'classes' => [],
            'dependencies' => $registration['source_locations']['dependencies'] ?? [],
            'factories' => [],
            'config_keys' => [],
            'runtime_arguments' => $registration['source_locations']['runtime_arguments'] ?? [],
            'aliases' => [],
        ];

        foreach ($methods as $method) {
            if (!isset($creatorMethods[$method])) {
                continue;
            }
            $dependencies = array_merge($dependencies, $creatorMethods[$method]['dependencies']);
            $classes = array_merge($classes, $creatorMethods[$method]['classes']);
            $factories = array_merge($factories, $creatorMethods[$method]['factories']);
            $configKeys = array_merge($configKeys, $creatorMethods[$method]['config_keys']);
            $creatorMethodArguments[$method] = [
                'parameters' => $creatorMethods[$method]['parameters'],
                'runtime_arguments' => $creatorMethods[$method]['runtime_arguments'],
                'container_dependencies' => $creatorMethods[$method]['container_dependencies'],
                'optional_arguments' => $creatorMethods[$method]['optional_arguments'],
            ];
            $sourceLocations['creator_methods'][] = php_fan_ai_source_location(
                $creatorMethods[$method]['file'],
                (int)$creatorMethods[$method]['line'],
                $method
            );
            foreach ($creatorMethods[$method]['class_locations'] as $location) {
                $sourceLocations['classes'][] = $location;
            }
            foreach ($creatorMethods[$method]['dependency_locations'] as $location) {
                $sourceLocations['dependencies'][] = $location;
            }
            foreach ($creatorMethods[$method]['factory_locations'] as $location) {
                $sourceLocations['factories'][] = $location;
            }
            foreach ($creatorMethods[$method]['config_key_locations'] as $location) {
                $sourceLocations['config_keys'][] = $location;
            }
        }

        $dependencies = array_values(array_diff(array_unique($dependencies), [$id]));
        sort($dependencies);
        $sourceLocations['dependencies'] = array_values(array_filter(
            $sourceLocations['dependencies'],
            static fn(array $location): bool => ($location['value'] ?? null) !== $id
        ));
        $runtimeArguments = array_values(array_unique($runtimeArguments));
        sort($runtimeArguments);
        ksort($creatorMethodArguments);
        $classes = array_values(array_unique($classes));
        sort($classes);
        $factories = array_values(array_unique($factories));
        sort($factories);
        $configKeys = array_values(array_unique($configKeys));
        sort($configKeys);
        $aliasIds = php_fan_ai_aliases_for_service($id, $aliases);
        $sourceLocations['aliases'] = php_fan_ai_alias_locations_for_service($id, $aliases);
        $uniqueMethods = array_values(array_unique($methods));
        $lifetimeReason = php_fan_ai_service_lifetime_reason($registration['shared']);
        $sourceEdges = [
            'aliases' => $aliasIds,
            'classes' => $classes,
            'dependencies' => $dependencies,
            'factories' => $factories,
            'config_keys' => $configKeys,
            'runtime_arguments' => $runtimeArguments,
            'registrar_files' => $registrarFiles,
            'creator_methods' => $uniqueMethods,
        ];

        $descriptor = new \fan\core\di\service_descriptor(
            $id,
            $registrarFiles,
            $uniqueMethods,
            $registration['shared'],
            $dependencies,
            [
                'registrar_files' => $registrarFiles,
                'creator_methods' => $uniqueMethods,
            ],
            [
                'container_dependencies' => $dependencies,
                'runtime_arguments' => $runtimeArguments,
            ],
            $creatorMethodArguments,
            $aliasIds,
            [
                'files' => $referenced[$id] ?? [],
                'locations' => $referencedLocations[$id] ?? [],
            ],
            $classes[0] ?? null,
            $factories[0] ?? null,
            $configKeys[0] ?? null,
            $lifetimeReason,
            $sourceEdges,
            php_fan_ai_unique_source_locations($sourceLocations)
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

        $callOffset = 0;
        foreach (php_fan_ai_extract_fluent_calls($source, 'factory') as $call) {
            $id = php_fan_ai_service_id_from_registration_call($call, $constants);
            if ($id === null) {
                continue;
            }
            $start = strpos($source, $call, $callOffset);
            $line = $start === false ? 1 : php_fan_ai_source_line_number($source, $start);
            if ($start !== false) {
                $callOffset = $start + strlen($call);
            }

            $registrations[$id]['creator_methods'] ??= [];
            $registrations[$id]['dependencies'] ??= [];
            $registrations[$id]['runtime_arguments'] ??= [];
            $registrations[$id]['shared'] ??= true;
            $registrations[$id]['source_locations']['registrations'] ??= [];
            $registrations[$id]['source_locations']['dependencies'] ??= [];
            $registrations[$id]['source_locations']['runtime_arguments'] ??= [];
            $registrations[$id]['source_locations']['registrations'][] = php_fan_ai_source_location($relativeFile, $line, value: $id);
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
            $registrations[$id]['source_locations']['dependencies'] = array_merge(
                $registrations[$id]['source_locations']['dependencies'],
                php_fan_ai_container_dependency_locations_from_source($relativeFile, null, $call, $constants, $line)
            );
            $registrations[$id]['source_locations']['runtime_arguments'] = array_merge(
                $registrations[$id]['source_locations']['runtime_arguments'],
                php_fan_ai_runtime_argument_locations_from_source($relativeFile, $call, $line)
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
        $registration['source_locations']['registrations'] = php_fan_ai_unique_source_location_list($registration['source_locations']['registrations'] ?? []);
        $registration['source_locations']['dependencies'] = php_fan_ai_unique_source_location_list($registration['source_locations']['dependencies'] ?? []);
        $registration['source_locations']['runtime_arguments'] = php_fan_ai_unique_source_location_list($registration['source_locations']['runtime_arguments'] ?? []);
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

function php_fan_ai_alias_locations_for_service(string $id, array $aliases): array
{
    $locations = [];
    foreach ($aliases as $alias => $entry) {
        if (($entry['target'] ?? null) !== $id) {
            continue;
        }
        if (!isset($entry['file'], $entry['line']) || !is_string($entry['file']) || !is_int($entry['line'])) {
            continue;
        }
        $locations[] = php_fan_ai_source_location($entry['file'], $entry['line'], value: (string)$alias);
    }

    return php_fan_ai_unique_source_location_list($locations);
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

        foreach (php_fan_ai_extract_public_creator_methods($source) as $methodName => $method) {
            $arguments = php_fan_ai_creator_method_arguments_from_parameters($method['parameters']);
            $methods[$methodName] = [
                'file' => $relativeFile,
                'line' => $method['line'],
                'dependencies' => php_fan_ai_container_dependencies_from_source($method['body'], $constants),
                'parameters' => $arguments['parameters'],
                'runtime_arguments' => $arguments['runtime_arguments'],
                'container_dependencies' => $arguments['container_dependencies'],
                'optional_arguments' => $arguments['optional_arguments'],
                'classes' => php_fan_ai_project_service_classes_from_source($method['body']),
                'factories' => php_fan_ai_factory_parameters_from_arguments($arguments['parameters']),
                'config_keys' => php_fan_ai_config_keys_from_source($method['body']),
                'class_locations' => php_fan_ai_project_service_class_locations_from_source($relativeFile, $methodName, $method),
                'dependency_locations' => php_fan_ai_container_dependency_locations_from_source($relativeFile, $methodName, $method['body'], $constants, (int)($method['body_line'] ?? $method['line'])),
                'factory_locations' => php_fan_ai_factory_parameter_locations_from_arguments($relativeFile, $methodName, $method, $arguments['parameters']),
                'config_key_locations' => php_fan_ai_config_key_locations_from_source($relativeFile, $methodName, $method),
            ];
        }
    }

    ksort($methods);

    return $methods;
}

function php_fan_ai_service_lifetime_reason(?bool $shared): string
{
    if ($shared === false) {
        return 'factory registration explicitly disables sharing';
    }
    if ($shared === true) {
        return 'factory registration defaults to shared service';
    }

    return 'service is registered outside fluent factory metadata';
}

function php_fan_ai_project_service_classes_from_source(string $source): array
{
    $classes = [];
    if (preg_match_all('/getProjectServiceClassName\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $source, $matches) > 0) {
        foreach ($matches[1] as $serviceName) {
            $classes[] = '\fan\project\service\\' . trim((string)$serviceName, " \t\n\r\0\x0B\\");
        }
    }

    $classes = array_values(array_unique($classes));
    sort($classes);

    return $classes;
}

function php_fan_ai_project_service_class_locations_from_source(string $file, string $methodName, array $method): array
{
    $locations = [];
    if (preg_match_all('/getProjectServiceClassName\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $method['body'], $matches, PREG_OFFSET_CAPTURE) > 0) {
        foreach ($matches[1] as $match) {
            $serviceName = trim((string)$match[0], " \t\n\r\0\x0B\\");
            $locations[] = php_fan_ai_source_location(
                $file,
                php_fan_ai_source_line_number($method['body'], (int)$match[1]) + ((int)($method['body_line'] ?? $method['line']) - 1),
                $methodName,
                '\fan\project\service\\' . $serviceName
            );
        }
    }

    return php_fan_ai_unique_source_location_list($locations);
}

function php_fan_ai_factory_parameters_from_arguments(array $parameters): array
{
    $factories = [];
    foreach ($parameters as $parameter) {
        if (is_string($parameter) && str_ends_with($parameter, 'Factory')) {
            $factories[] = $parameter;
        }
    }

    $factories = array_values(array_unique($factories));
    sort($factories);

    return $factories;
}

function php_fan_ai_factory_parameter_locations_from_arguments(string $file, string $methodName, array $method, array $parameters): array
{
    $locations = [];
    $parameterOffsets = php_fan_ai_parameter_name_offsets($method['parameters'] ?? '');
    foreach ($parameters as $parameter) {
        if (is_string($parameter) && str_ends_with($parameter, 'Factory')) {
            $line = (int)$method['line'];
            if (isset($parameterOffsets[$parameter])) {
                $line = php_fan_ai_source_line_number((string)$method['parameters'], $parameterOffsets[$parameter])
                    + ((int)($method['parameters_line'] ?? $method['line']) - 1);
            }
            $locations[] = php_fan_ai_source_location($file, $line, $methodName, $parameter);
        }
    }

    return php_fan_ai_unique_source_location_list($locations);
}

function php_fan_ai_parameter_name_offsets(string $parameters): array
{
    $offsets = [];
    if (preg_match_all('/\$([A-Za-z_][A-Za-z0-9_]*)/', $parameters, $matches, PREG_OFFSET_CAPTURE) === 0) {
        return $offsets;
    }

    foreach ($matches[1] as $match) {
        $offsets[$match[0]] = $match[1];
    }

    return $offsets;
}

function php_fan_ai_config_keys_from_source(string $source): array
{
    $keys = [];
    if (preg_match_all('/->\s*get\s*\(\s*service_id::CONFIG\s*\)\s*->\s*get\s*\(\s*[\'"]([^\'"]+)[\'"]/', $source, $matches) > 0) {
        $keys = array_merge($keys, $matches[1]);
    }
    if (preg_match_all('/\$config\s*->\s*get\s*\(\s*[\'"]([^\'"]+)[\'"]/', $source, $matches) > 0) {
        $keys = array_merge($keys, $matches[1]);
    }

    $keys = array_values(array_unique(array_map('strval', $keys)));
    sort($keys);

    return $keys;
}

function php_fan_ai_config_key_locations_from_source(string $file, string $methodName, array $method): array
{
    $locations = [];
    foreach ([
        '/->\s*get\s*\(\s*service_id::CONFIG\s*\)\s*->\s*get\s*\(\s*[\'"]([^\'"]+)[\'"]/' => 1,
        '/\$config\s*->\s*get\s*\(\s*[\'"]([^\'"]+)[\'"]/' => 1,
    ] as $pattern => $group) {
        if (preg_match_all($pattern, $method['body'], $matches, PREG_OFFSET_CAPTURE) === 0) {
            continue;
        }
        foreach ($matches[$group] as $match) {
            $locations[] = php_fan_ai_source_location(
                $file,
                php_fan_ai_source_line_number($method['body'], (int)$match[1]) + ((int)($method['body_line'] ?? $method['line']) - 1),
                $methodName,
                (string)$match[0]
            );
        }
    }

    return php_fan_ai_unique_source_location_list($locations);
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

function php_fan_ai_runtime_argument_locations_from_source(string $file, string $source, int $baseLine = 1): array
{
    if (preg_match('/(?:static\s+)?(?:function|fn)\s*\(([^)]*)\)/s', $source, $match, PREG_OFFSET_CAPTURE) !== 1) {
        return [];
    }

    $parameters = $match[1][0];
    $parameterBaseOffset = $match[1][1];
    $locations = [];
    foreach (php_fan_ai_parameter_name_offsets($parameters) as $name => $offset) {
        if ($name === 'container') {
            continue;
        }
        $locations[] = php_fan_ai_source_location(
            $file,
            php_fan_ai_source_line_number($source, $parameterBaseOffset + $offset) + ($baseLine - 1),
            value: $name
        );
    }

    return php_fan_ai_unique_source_location_list($locations);
}

function php_fan_ai_extract_public_creator_methods(string $source): array
{
    $methods = [];
    $offset = 0;

    while (preg_match('/public\s+function\s+(create[A-Za-z0-9_]+)\s*\(/', $source, $match, PREG_OFFSET_CAPTURE, $offset) === 1) {
        $methodName = $match[1][0];
        $methodStart = $match[0][1];
        $openParen = strpos($source, '(', $methodStart);
        if ($openParen === false) {
            break;
        }

        $closeParen = php_fan_ai_find_matching_paren($source, $openParen);
        if ($closeParen === null) {
            break;
        }

        $openBrace = strpos($source, '{', $closeParen);
        if ($openBrace === false) {
            break;
        }

        $closeBrace = php_fan_ai_find_matching_brace($source, $openBrace);
        if ($closeBrace === null) {
            break;
        }

        $methods[$methodName] = [
            'parameters' => substr($source, $openParen + 1, $closeParen - $openParen - 1),
            'parameters_line' => php_fan_ai_source_line_number($source, $openParen + 1),
            'body' => substr($source, $openBrace + 1, $closeBrace - $openBrace - 1),
            'line' => php_fan_ai_source_line_number($source, $methodStart),
            'body_line' => php_fan_ai_source_line_number($source, $openBrace + 1),
        ];
        $offset = $closeBrace + 1;
    }

    return $methods;
}

function php_fan_ai_source_line_number(string $source, int $offset): int
{
    return substr_count(substr($source, 0, max(0, $offset)), "\n") + 1;
}

function php_fan_ai_source_location(string $file, int $line, ?string $method = null, ?string $value = null): array
{
    $location = [
        'file' => $file,
        'line' => max(1, $line),
    ];
    if ($method !== null) {
        $location['method'] = $method;
    }
    if ($value !== null) {
        $location['value'] = $value;
    }

    return $location;
}

function php_fan_ai_unique_source_locations(array $locations): array
{
    foreach ($locations as $key => $list) {
        $locations[$key] = php_fan_ai_unique_source_location_list(is_array($list) ? $list : []);
    }

    return $locations;
}

function php_fan_ai_unique_source_location_list(array $locations): array
{
    $unique = [];
    foreach ($locations as $location) {
        if (!is_array($location)) {
            continue;
        }
        $key = json_encode($location, JSON_UNESCAPED_SLASHES);
        if (is_string($key)) {
            $unique[$key] = $location;
        }
    }

    return array_values($unique);
}

function php_fan_ai_creator_method_arguments_from_parameters(string $parameters): array
{
    $all = [];
    $runtime = [];
    $container = [];
    $optional = [];

    foreach (php_fan_ai_split_parameter_list($parameters) as $parameter) {
        $descriptor = php_fan_ai_parameter_descriptor($parameter);
        if ($descriptor === null) {
            continue;
        }

        $name = $descriptor['name'];
        $all[] = $name;
        if ($descriptor['optional']) {
            $optional[] = $name;
        }
        if (php_fan_ai_parameter_is_container_dependency($name, $descriptor['type'])) {
            $container[] = $name;
            continue;
        }
        if (php_fan_ai_parameter_is_runtime_argument($descriptor['type'])) {
            $runtime[] = $name;
        }
    }

    $all = array_values(array_unique($all));
    $runtime = array_values(array_unique($runtime));
    sort($runtime);
    $container = array_values(array_unique($container));
    sort($container);
    $optional = array_values(array_unique($optional));
    sort($optional);

    return [
        'parameters' => $all,
        'runtime_arguments' => $runtime,
        'container_dependencies' => $container,
        'optional_arguments' => $optional,
    ];
}

function php_fan_ai_split_parameter_list(string $parameters): array
{
    $items = [];
    $start = 0;
    $depth = 0;
    $stringQuote = null;
    $escaped = false;
    $length = strlen($parameters);

    for ($i = 0; $i < $length; $i++) {
        $char = $parameters[$i];

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

        if ($char === '(' || $char === '[') {
            $depth++;
            continue;
        }

        if ($char === ')' || $char === ']') {
            $depth--;
            continue;
        }

        if ($char === ',' && $depth === 0) {
            $items[] = trim(substr($parameters, $start, $i - $start));
            $start = $i + 1;
        }
    }

    $tail = trim(substr($parameters, $start));
    if ($tail !== '') {
        $items[] = $tail;
    }

    return $items;
}

function php_fan_ai_parameter_descriptor(string $parameter): ?array
{
    if (preg_match('/\$([A-Za-z_][A-Za-z0-9_]*)/', $parameter, $match, PREG_OFFSET_CAPTURE) !== 1) {
        return null;
    }

    $name = $match[1][0];
    $type = trim(substr($parameter, 0, $match[0][1]));
    $type = trim(str_replace(['&', '...'], '', $type));
    $type = preg_replace('/\s+/', ' ', $type);

    return [
        'name' => $name,
        'type' => is_string($type) ? $type : '',
        'optional' => str_contains(substr($parameter, $match[0][1] + strlen($match[0][0])), '='),
    ];
}

function php_fan_ai_parameter_is_container_dependency(string $name, string $type): bool
{
    if ($name === 'container') {
        return true;
    }

    foreach (php_fan_ai_parameter_type_names($type) as $typeName) {
        if ($typeName === 'container_interface' || str_ends_with($typeName, '\\container_interface')) {
            return true;
        }
    }

    return false;
}

function php_fan_ai_parameter_is_runtime_argument(string $type): bool
{
    $runtimeTypes = [
        'array' => true,
        'bool' => true,
        'float' => true,
        'int' => true,
        'mixed' => true,
        'string' => true,
    ];

    foreach (php_fan_ai_parameter_type_names($type) as $typeName) {
        if (isset($runtimeTypes[$typeName])) {
            return true;
        }
    }

    return $type === '';
}

function php_fan_ai_parameter_type_names(string $type): array
{
    $type = trim($type);
    if ($type === '') {
        return [];
    }

    $type = str_replace('?', '', $type);
    $type = preg_replace('/\s+/', '', $type);
    if (!is_string($type) || $type === '') {
        return [];
    }

    $names = [];
    foreach (preg_split('/[|&]/', $type) ?: [] as $typeName) {
        $typeName = strtolower(ltrim($typeName, '\\'));
        if ($typeName !== '') {
            $names[] = $typeName;
        }
    }

    return $names;
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

    if (preg_match_all('/(?:\$container|\$this->container|\$this->container\(\)|\$this->context\(\)->container\(\))\s*->\s*get\s*\(\s*[\'"]([^\'"]+)[\'"]/', $source, $matches) > 0) {
        foreach ($matches[1] as $id) {
            $dependencies[] = $id;
        }
    }

    if (preg_match_all('/(?:\$container|\$this->container|\$this->container\(\)|\$this->context\(\)->container\(\))\s*->\s*get\s*\(\s*service_id::([A-Z0-9_]+)/', $source, $matches) > 0) {
        foreach ($matches[1] as $constant) {
            if (isset($constants[$constant])) {
                $dependencies[] = $constants[$constant];
            }
        }
    }

    if (preg_match_all('/\$(common|coreDependencies|utilityDependencies|clientDependencies|contentDependencies|controllerDependencies|tabDependencies|userDependencies|infrastructureDependencies|pagerDependencies|sessionDependencies)\s*->\s*([A-Za-z0-9_]+)\s*\(/', $source, $matches, PREG_SET_ORDER) > 0) {
        foreach ($matches as $match) {
            $dependency = php_fan_ai_named_dependency_service_id($match[1], $match[2]);
            if ($dependency !== null) {
                $dependencies[] = $dependency;
            }
        }
    }

    $dependencies = array_values(array_unique($dependencies));
    sort($dependencies);

    return $dependencies;
}

function php_fan_ai_container_dependency_locations_from_source(
    string $file,
    ?string $methodName,
    string $source,
    array $constants,
    int $baseLine = 1
): array {
    $locations = [];
    foreach ([
        '/(?:\$container|\$this->container|\$this->container\(\)|\$this->context\(\)->container\(\))\s*->\s*get\s*\(\s*[\'"]([^\'"]+)[\'"]/' => null,
        '/(?:\$container|\$this->container|\$this->container\(\)|\$this->context\(\)->container\(\))\s*->\s*get\s*\(\s*service_id::([A-Z0-9_]+)/' => $constants,
    ] as $pattern => $constantMap) {
        if (preg_match_all($pattern, $source, $matches, PREG_OFFSET_CAPTURE) === 0) {
            continue;
        }
        foreach ($matches[1] as $match) {
            $value = (string)$match[0];
            if (is_array($constantMap)) {
                $value = $constantMap[$value] ?? $value;
            }
            $locations[] = php_fan_ai_source_location(
                $file,
                php_fan_ai_source_line_number($source, (int)$match[1]) + ($baseLine - 1),
                $methodName,
                $value
            );
        }
    }

    if (preg_match_all('/\$(common|coreDependencies|utilityDependencies|clientDependencies|contentDependencies|controllerDependencies|tabDependencies|userDependencies|infrastructureDependencies|pagerDependencies|sessionDependencies)\s*->\s*([A-Za-z0-9_]+)\s*\(/', $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) > 0) {
        foreach ($matches as $match) {
            $value = php_fan_ai_named_dependency_service_id((string)$match[1][0], (string)$match[2][0]);
            if ($value === null) {
                continue;
            }
            $locations[] = php_fan_ai_source_location(
                $file,
                php_fan_ai_source_line_number($source, (int)$match[2][1]) + ($baseLine - 1),
                $methodName,
                $value
            );
        }
    }

    return php_fan_ai_unique_source_location_list($locations);
}

function php_fan_ai_common_dependency_service_id(string $methodName): ?string
{
    return [
        'bootstrapRuntime' => 'bootstrap_runtime',
        'config' => 'config',
        'cacheFactory' => 'cache',
    ][$methodName] ?? null;
}

function php_fan_ai_named_dependency_service_id(string $variableName, string $methodName): ?string
{
    if ($variableName === 'common') {
        return php_fan_ai_common_dependency_service_id($methodName);
    }
    if ($variableName === 'coreDependencies') {
        return [
            'requestInput' => 'request_input',
            'jsonFactory' => 'json',
            'cookieFactory' => 'cookie',
            'matcherFactory' => 'matcher',
            'requestFactory' => 'request',
            'arrayAdducer' => 'array_adducer',
            'recursiveMerger' => 'recursive_merger',
            'arrayValueReader' => 'array_value_reader',
            'classNameResolver' => 'class_name_resolver',
            'currentUserFactory' => 'current_user',
            'sessionFactory' => 'session',
            'error' => 'error',
            'errorFactory' => 'error',
            'currentUserSpaceFactory' => 'current_user_space',
            'dateFactory' => 'date',
            'reflectionClassFactory' => 'reflection_class_factory',
            'tab' => 'tab',
            'tabFactory' => 'tab',
            'metaFileStorage' => 'meta_file_storage',
            'headerWriter' => 'header_writer',
            'phpArrayFileLoader' => 'php_array_file_loader',
            'errorLogWriter' => 'error_log_writer',
            'errorFileStorage' => 'error_file_storage',
            'locale' => 'locale',
            'application' => 'application',
            'matcherRouteFileStorage' => 'matcher_route_file_storage',
            'entityFactory' => 'entity',
        ][$methodName] ?? null;
    }
    if ($variableName === 'utilityDependencies') {
        return [
            'bootstrapRuntime' => 'bootstrap_runtime',
            'config' => 'config',
            'cacheFactory' => 'cache',
            'phpArrayFileLoader' => 'php_array_file_loader',
            'obfuscatorFileStorage' => 'obfuscator_file_storage',
            'imageMetadataReader' => 'image_metadata_reader',
            'imageResourceFactory' => 'image_resource_factory',
            'imageCanvasOperations' => 'image_canvas_operations',
            'imageOutputWriter' => 'image_output_writer',
            'imageSourceFileStorage' => 'image_source_file_storage',
            'arrayValueReader' => 'array_value_reader',
            'errorFactory' => 'error',
            'phpRuntimeSettings' => 'php_runtime_settings',
            'soapWsdlFileStorage' => 'soap_wsdl_file_storage',
            'classNameResolver' => 'class_name_resolver',
        ][$methodName] ?? null;
    }
    if ($variableName === 'clientDependencies') {
        return [
            'bootstrapRuntime' => 'bootstrap_runtime',
            'config' => 'config',
            'cacheFactory' => 'cache',
            'curlAdapter' => 'curl_adapter',
            'arrayAdducer' => 'array_adducer',
            'arrayValueReader' => 'array_value_reader',
            'jsonFactory' => 'json',
            'curlFactory' => 'curl',
            'errorFactory' => 'error',
            'requestInput' => 'request_input',
            'serializerOperations' => 'serializer_operations',
            'cookieWriter' => 'cookie_writer',
        ][$methodName] ?? null;
    }
    if ($variableName === 'contentDependencies') {
        return [
            'locale' => 'locale',
            'bootstrapRuntime' => 'bootstrap_runtime',
            'tabFactory' => 'tab',
            'error' => 'error',
            'blockContext' => 'block_context',
            'matcher' => 'matcher',
            'requestInput' => 'request_input',
            'config' => 'config',
            'cacheFactory' => 'cache',
            'phpArrayFileLoader' => 'php_array_file_loader',
            'translationFileStorage' => 'translation_file_storage',
        ][$methodName] ?? null;
    }
    if ($variableName === 'controllerDependencies') {
        return [
            'matcher' => 'matcher',
            'plainConfigFactory' => 'config',
            'header' => 'header',
            'obfuscatorFactory' => 'obfuscator',
            'request' => 'request',
            'plainFileContext' => 'plain_file_context',
            'bootstrapRuntime' => 'bootstrap_runtime',
            'config' => 'config',
            'cacheFactory' => 'cache',
        ][$methodName] ?? null;
    }
    if ($variableName === 'userDependencies') {
        return [
            'serializerOperations' => 'serializer_operations',
            'config' => 'config',
            'request' => 'request',
            'application' => 'application',
            'error500ExceptionFactory' => 'error500_exception_factory',
            'session' => 'session',
            'configFactory' => 'config',
            'sessionFactory' => 'session',
            'currentUserFactory' => 'current_user',
            'applicationFactory' => 'application',
            'errorFactory' => 'error',
            'requestInputFactory' => 'request_input',
            'entityFactory' => 'entity',
            'bootstrapRuntime' => 'bootstrap_runtime',
            'cacheFactory' => 'cache',
            'arrayAdducer' => 'array_adducer',
        ][$methodName] ?? null;
    }
    if ($variableName === 'infrastructureDependencies') {
        return [
            'bootstrapRuntime' => 'bootstrap_runtime',
            'config' => 'config',
            'configFactory' => 'config',
            'configCacheFactory' => 'config_cache',
            'cacheFactory' => 'cache',
            'errorFactory' => 'error',
            'phpArrayFileLoader' => 'php_array_file_loader',
            'serializerOperations' => 'serializer_operations',
            'shortClassNameResolver' => 'short_class_name_resolver',
            'cacheSourceFileMetadata' => 'cache_source_file_metadata',
            'configSourceFileStorage' => 'config_source_file_storage',
            'fileSystemStorage' => 'file_system_storage',
            'coreFatalExceptionFactory' => 'core_fatal_exception_factory',
            'requestInput' => 'request_input',
            'headerWriter' => 'header_writer',
            'error500ExceptionFactory' => 'error500_exception_factory',
        ][$methodName] ?? null;
    }
    if ($variableName === 'pagerDependencies') {
        return [
            'entityFactory' => 'entity',
            'tab' => 'tab',
            'tabFactory' => 'tab',
            'requestFactory' => 'request',
            'bootstrapRuntime' => 'bootstrap_runtime',
            'config' => 'config',
            'cacheFactory' => 'cache',
            'error500ExceptionFactory' => 'error500_exception_factory',
        ][$methodName] ?? null;
    }
    if ($variableName === 'sessionDependencies') {
        return [
            'config' => 'config',
            'application' => 'application',
            'requestInput' => 'request_input',
            'headerWriter' => 'header_writer',
            'errorFactory' => 'error',
            'request' => 'request',
            'sessionFactory' => 'session',
            'dateFactory' => 'date',
            'cookieFactory' => 'cookie',
            'pearHttpSessionLoader' => 'pear_http_session_loader',
            'bootstrapRuntime' => 'bootstrap_runtime',
            'cacheFactory' => 'cache',
            'phpRuntimeSettings' => 'php_runtime_settings',
            'nativeSession' => 'native_session',
            'arrayValueReader' => 'array_value_reader',
        ][$methodName] ?? null;
    }
    if ($variableName !== 'tabDependencies') {
        return null;
    }

    return [
        'matcher' => 'matcher',
        'request' => 'request',
        'locale' => 'locale',
        'sessionFactory' => 'session',
        'requestInput' => 'request_input',
        'roleFactory' => 'role',
        'transferFactory' => 'transfer',
        'configFactory' => 'config',
        'headerFactory' => 'header',
        'applicationFactory' => 'application',
        'debugFactory' => 'debug',
        'jsonFactory' => 'json',
        'dataLoaderFactory' => 'data_loader',
        'errorFactory' => 'error',
        'cookieFactory' => 'cookie',
        'reflectorFactory' => 'reflector',
        'entityFactory' => 'entity',
        'pagerFactory' => 'pager',
        'obfuscatorFactory' => 'obfuscator',
        'imageModifyFactory' => 'image_modify',
        'userFactory' => 'user',
        'dateFactory' => 'date',
        'tabState' => 'tab_state',
        'phpArrayFileLoader' => 'php_array_file_loader',
        'blockFactory' => 'block_factory',
        'blockExceptionFactory' => 'block_exception_factory',
        'metaRowFactory' => 'meta_row_factory',
        'tabAliasFileStorage' => 'tab_alias_file_storage',
        'arrayAdducer' => 'array_adducer',
        'recursiveMerger' => 'recursive_merger',
        'arrayValueReader' => 'array_value_reader',
        'classNameResolver' => 'class_name_resolver',
        'arrayLikeChecker' => 'array_like_checker',
        'shortClassNameResolver' => 'short_class_name_resolver',
        'imageMetadataReader' => 'image_metadata_reader',
        'errorLogWriter' => 'error_log_writer',
        'blockFileStorage' => 'block_file_storage',
        'metaFileStorage' => 'meta_file_storage',
        'projectToolFileStorage' => 'project_tool_file_storage',
        'rootHtmlFileStorage' => 'root_html_file_storage',
        'uploadSizeLimitProvider' => 'upload_size_limit_provider',
    ][$methodName] ?? null;
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

function php_fan_ai_sorted_source_location_occurrences(array $occurrences): array
{
    ksort($occurrences);
    foreach ($occurrences as $id => $locations) {
        $locations = php_fan_ai_unique_source_location_list(is_array($locations) ? $locations : []);
        usort(
            $locations,
            static fn(array $left, array $right): int => [
                (string)($left['file'] ?? ''),
                (int)($left['line'] ?? 0),
                (string)($left['value'] ?? ''),
            ] <=> [
                (string)($right['file'] ?? ''),
                (int)($right['line'] ?? 0),
                (string)($right['value'] ?? ''),
            ]
        );
        $occurrences[$id] = $locations;
    }

    return $occurrences;
}
