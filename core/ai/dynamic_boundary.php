<?php

declare(strict_types=1);

namespace fan\core\ai;

final class dynamic_boundary
{
    public const METHOD_BODY_OR_RUNTIME = 'method_body_or_runtime_boundary';
    public const NAMED_DEFAULT_CLOSURE = 'named_default_closure_boundary';
    public const TERMINAL_COMPOSITION_LEAF = 'terminal_composition_leaf';
    public const ACTIONABLE_COMPOSITION_ROOT = 'actionable_composition_root';
    public const ACTIONABLE_NAMED_MIGRATION_DEBT = 'actionable_named_migration_debt';
    public const INTENTIONAL_NAMED_COMPATIBILITY = 'intentional_named_compatibility_boundary';

    public static function kindForFile(string $relativeFile): string
    {
        return in_array($relativeFile, [
            'core/adapter/bootstrap_loader_file_storage.php',
            'core/adapter/compiled_template_loader.php',
            'core/adapter/project_tool_loader.php',
            'core/adapter/zend_autoloader.php',
        ], true)
            ? self::NAMED_DEFAULT_CLOSURE
            : self::METHOD_BODY_OR_RUNTIME;
    }

    public static function kindForLocation(string $relativeFile, string $pattern, string $lineText): string
    {
        if (self::kindForFile($relativeFile) === self::NAMED_DEFAULT_CLOSURE) {
            return self::NAMED_DEFAULT_CLOSURE;
        }

        if (
            $pattern === 'class_exists'
            && str_contains($lineText, 'static fn')
            && str_contains($lineText, '=> class_exists(')
        ) {
            return self::NAMED_DEFAULT_CLOSURE;
        }

        return self::METHOD_BODY_OR_RUNTIME;
    }

    public static function kindForLocations(string $relativeFile, array $locations): string
    {
        $hasNamedBoundary = false;
        foreach ($locations as $location) {
            $locationKind = is_array($location) && is_string($location['boundary_kind'] ?? null)
                ? $location['boundary_kind']
                : self::kindForFile($relativeFile);
            if ($locationKind === self::METHOD_BODY_OR_RUNTIME) {
                return self::METHOD_BODY_OR_RUNTIME;
            }
            if ($locationKind === self::NAMED_DEFAULT_CLOSURE) {
                $hasNamedBoundary = true;
            }
        }

        return $hasNamedBoundary ? self::NAMED_DEFAULT_CLOSURE : self::kindForFile($relativeFile);
    }

    public static function filterLocations(array $locations, ?string $boundaryKind): array
    {
        if ($boundaryKind === null) {
            return array_values($locations);
        }

        return array_values(array_filter(
            $locations,
            static fn(mixed $location): bool => is_array($location)
                && ($location['boundary_kind'] ?? null) === $boundaryKind
        ));
    }

    public static function compositionLeafKind(?string $category, int $count): ?string
    {
        if ($category !== 'composition_roots') {
            return null;
        }

        return $count <= 1 ? self::TERMINAL_COMPOSITION_LEAF : self::ACTIONABLE_COMPOSITION_ROOT;
    }

    public static function namedMigrationDebtKindForLocation(string $relativeFile, int $line): ?string
    {
        if (in_array($relativeFile, [
            'core/base/model/entity_dependencies.php',
            'core/base/transfer/int.php',
            'core/block/base_dependency_defaults.php',
            'core/service/block_context.php',
        ], true)) {
            return self::INTENTIONAL_NAMED_COMPATIBILITY;
        }

        if ($relativeFile === 'core/block/base.php' && $line < 900) {
            return self::INTENTIONAL_NAMED_COMPATIBILITY;
        }

        return self::ACTIONABLE_NAMED_MIGRATION_DEBT;
    }
}
