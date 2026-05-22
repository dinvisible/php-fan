<?php

declare(strict_types=1);

class AppBlockClassNamingTest extends \PHPUnit\Framework\TestCase
{
    public function testProjectAppBlockClassNamesMatchFileNames(): void
    {
        $projectRoot = dirname(__DIR__, 2);
        $appRoot = $projectRoot . '/_project/app';
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($appRoot, \FilesystemIterator::SKIP_DOTS)
        );
        $mismatches = [];

        foreach ($files as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php' || str_ends_with($file->getBasename(), '.meta.php')) {
                continue;
            }

            $relative = substr($file->getPathname(), strlen($appRoot) + 1);
            $parts = explode(DIRECTORY_SEPARATOR, $relative);
            if (count($parts) < 3) {
                continue;
            }

            $expectedClass = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $declaredClass = $this->firstDeclaredClass((string)file_get_contents($file->getPathname()));
            if ($declaredClass === null) {
                continue;
            }

            if ($declaredClass !== $expectedClass) {
                $mismatches[] = sprintf('%s declares class %s, expected %s', $relative, $declaredClass, $expectedClass);
            }
        }

        $this->assertSame([], $mismatches);
    }

    private function firstDeclaredClass(string $source): ?string
    {
        $tokens = token_get_all($source);
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_CLASS) {
                continue;
            }

            for ($j = $i + 1; $j < $count; $j++) {
                if (is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                    return $tokens[$j][1];
                }
            }
        }

        return null;
    }
}
