<?php

declare(strict_types=1);

use FanTest\_core\SourceFileContractTestCase;

class GeneratedPendingBaseServiceTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/base/service.php';

    public function testDelegateResolverKeepsMixedParameterContract(): void
    {
        $this->assertStringContainsString(
            'protected function _getDelegate(mixed $class): mixed',
            $this->sourceCode()
        );
    }
}
