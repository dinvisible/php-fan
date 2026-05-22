<?php

declare(strict_types=1);

namespace FanTest\_core;

abstract class SourceFileContractTestCase extends \PHPUnit\Framework\TestCase
{
    protected const SOURCE_FILE = '';

    public function testSourceFileExists(): void
    {
        $this->assertFileExists($this->sourcePath());
    }

    public function testSourceFileIsValidPhpCode(): void
    {
        $code = $this->sourceCode();

        $this->assertStringStartsWith('<?php', $code);
        $this->assertNotEmpty(\PhpToken::tokenize($code, TOKEN_PARSE));
    }

    public function testSourceFileDeclaresContract(): void
    {
        $declarations = $this->readDeclarations();

        if ($declarations['classLike'] === [] && $declarations['functions'] === []) {
            $this->assertMatchesRegularExpression(
                '/\\b(?:require|require_once|include|include_once|class_exists)\\b/',
                $this->sourceCode()
            );
            return;
        }

        foreach ($declarations['classLike'] as $declaration) {
            $this->assertStringStartsWith('fan\\core\\', $declaration);
        }
    }

    public function testTestCaseIsNoLongerPending(): void
    {
        $testCode = file_get_contents((new \ReflectionClass($this))->getFileName());

        $this->assertIsString($testCode);
        $this->assertStringNotContainsString('markTest' . 'Skipped', $testCode);
    }

    protected function sourcePath(): string
    {
        $sourceFile = static::SOURCE_FILE;
        $this->assertNotSame('', $sourceFile);

        return dirname(__DIR__, 3) . '/' . $sourceFile;
    }

    protected function sourceCode(): string
    {
        $code = file_get_contents($this->sourcePath());

        $this->assertIsString($code);
        return $code;
    }

    protected function readDeclarations(): array
    {
        $tokens = \PhpToken::tokenize($this->sourceCode(), TOKEN_PARSE);
        $namespace = '';
        $classLike = [];
        $functions = [];
        $tokenCount = count($tokens);

        for ($i = 0; $i < $tokenCount; $i++) {
            $token = $tokens[$i];
            if ($token->id === T_NAMESPACE) {
                $namespace = $this->readNamespace($tokens, $i + 1);
                continue;
            }

            if (in_array($token->id, [T_CLASS, T_INTERFACE, T_TRAIT], true)) {
                if ($this->isAnonymousClass($tokens, $i)) {
                    continue;
                }
                $name = $this->readNamedDeclaration($tokens, $i + 1);
                if ($name !== null) {
                    $classLike[] = ltrim($namespace . '\\' . $name, '\\');
                }
                continue;
            }

            if ($token->id === T_FUNCTION) {
                $name = $this->readNamedDeclaration($tokens, $i + 1);
                if ($name !== null) {
                    $functions[] = ltrim($namespace . '\\' . $name, '\\');
                }
            }
        }

        return [
            'classLike' => $classLike,
            'functions' => $functions,
        ];
    }

    private function readNamespace(array $tokens, int $offset): string
    {
        $parts = [];
        for ($i = $offset, $count = count($tokens); $i < $count; $i++) {
            $token = $tokens[$i];
            if ($token->text === ';' || $token->text === '{') {
                break;
            }
            if ($token->id === T_STRING || $token->id === T_NAME_QUALIFIED || $token->text === '\\') {
                $parts[] = $token->text;
            }
        }
        return implode('', $parts);
    }

    private function readNamedDeclaration(array $tokens, int $offset): ?string
    {
        for ($i = $offset, $count = count($tokens); $i < $count; $i++) {
            $token = $tokens[$i];
            if ($token->isIgnorable() || $token->text === '&') {
                continue;
            }
            return $token->id === T_STRING ? $token->text : null;
        }
        return null;
    }

    private function isAnonymousClass(array $tokens, int $classIndex): bool
    {
        for ($i = $classIndex - 1; $i >= 0; $i--) {
            $token = $tokens[$i];
            if ($token->isIgnorable()) {
                continue;
            }
            return $token->id === T_NEW;
        }
        return false;
    }
}
