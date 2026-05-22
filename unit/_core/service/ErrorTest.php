<?php

declare(strict_types=1);

use FanTest\_core\SourceFileContractTestCase;

class GeneratedPendingServiceErrorTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/error.php';

    public function testNumericErrorMasksAreAccepted(): void
    {
        $service = $this->makeErrorService();

        $this->assertSame(30719, $service->readMask(30719));
        $this->assertSame(3754, $service->readMask('3754'));
        $this->assertSame(1034, $service->readMask(1034.0));
    }

    public function testSymbolicErrorMaskExpressionsAreRejected(): void
    {
        $service = $this->makeErrorService();

        $this->expectException(\UnexpectedValueException::class);
        $service->readMask('E_NOTICE | E_USER_NOTICE');
    }

    private function makeErrorService(): object
    {
        require_once dirname(__DIR__, 3) . '/_core/di/container_interface.php';
        require_once dirname(__DIR__, 3) . '/_core/di/container_aware_trait.php';
        require_once dirname(__DIR__, 3) . '/_core/base/service.php';
        require_once dirname(__DIR__, 3) . '/_core/base/service/single.php';
        require_once dirname(__DIR__, 3) . '/_core/service/error.php';

        return new class extends \fan\core\service\error {
            public function __construct()
            {
            }

            public function readMask(mixed $mask): int
            {
                return $this->readErrorMask($mask);
            }
        };
    }
}
