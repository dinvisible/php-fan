<?php

declare(strict_types=1);

namespace FanTest\_core;
use PHPUnit\Framework\TestCase;


abstract class SourceFileContractTestCase extends TestCase
{
    protected const SOURCE_FILE = '';

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
}
