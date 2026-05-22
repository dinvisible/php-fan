<?php

declare(strict_types=1);

use FanTest\_core\SourceFileContractTestCase;

class GeneratedPendingServiceUserTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/user.php';

    public function testDelegateResolverSignatureRemainsCompatibleWithBaseService(): void
    {
        $this->assertStringContainsString(
            'protected function _getDelegate(mixed $class): mixed',
            $this->sourceCode()
        );
    }
}
