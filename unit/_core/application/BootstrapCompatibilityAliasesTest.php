<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class BootstrapCompatibilityAliasesTest extends TestCase
{
    public function testMovedBootstrapClassesKeepDeprecatedAliases(): void
    {
        $aliases = self::compatibilityAliases();

        $this->assertNotEmpty($aliases);

        foreach ($aliases as $alias => $target) {
            $this->assertTrue(class_exists($target), $target);
            $this->assertTrue(class_exists($alias), $alias);
            $this->assertTrue(is_a($alias, $target, true), $alias . ' should alias ' . $target);
        }
    }

    /**
     * @return array<string, class-string>
     */
    private static function compatibilityAliases(): array
    {
        $aliases = [];
        foreach (glob(dirname(__DIR__, 3) . '/_core/application/*.php') ?: [] as $path) {
            $source = file_get_contents($path);
            if (!is_string($source) || !str_contains($source, '\\class_alias(')) {
                continue;
            }
            if (
                !preg_match('/^use (fan\\\\core\\\\(?:di|runtime|adapter)\\\\([a-z_]+));$/m', $source, $useMatches)
                || !preg_match('/\\\\class_alias\\(([a-z_]+)::class, __NAMESPACE__ \\. \'\\\\\\\\([a-z_]+)\'\\);/', $source, $aliasMatches)
            ) {
                continue;
            }

            if ($useMatches[2] !== $aliasMatches[1]) {
                continue;
            }

            $aliases['fan\\core\\bootstrap\\' . $aliasMatches[2]] = $useMatches[1];
        }

        ksort($aliases);

        return $aliases;
    }
}
