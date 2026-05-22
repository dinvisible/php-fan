<?php

declare(strict_types=1);

use FanTest\_core\SourceFileContractTestCase;

class GeneratedPendingServiceEntityTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/entity.php';

    public function testDelegateResolverSignatureRemainsCompatibleWithBaseService(): void
    {
        $this->assertStringContainsString(
            'protected function _getDelegate(mixed $name): mixed',
            $this->sourceCode()
        );
    }
}
