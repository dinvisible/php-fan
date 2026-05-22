<?php

declare(strict_types=1);

use FanTest\_core\SourceFileContractTestCase;

class GeneratedPendingServiceObfuscatorTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/obfuscator.php';

    public function testGetConfigSignatureRemainsCompatibleWithBaseService(): void
    {
        $this->assertStringContainsString(
            'public function getConfig(mixed $key = null, mixed $default = null): mixed',
            $this->sourceCode()
        );
    }

    public function testDelegateResolverSignatureRemainsCompatibleWithBaseService(): void
    {
        $this->assertStringContainsString(
            'protected function _getDelegate(mixed $class): mixed',
            $this->sourceCode()
        );
    }
}
