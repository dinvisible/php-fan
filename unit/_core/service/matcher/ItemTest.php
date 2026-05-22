<?php

declare(strict_types=1);

use FanTest\_core\SourceFileContractTestCase;

class GeneratedPendingServiceMatcherItemTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/matcher/item.php';

    public function testHandlerConfigRowsAreConvertedToArrays(): void
    {
        $item = $this->makeMatcherItem();
        $row = new \fan\core\service\config\row([
            'definer' => 'request',
            'regexp' => '/^\\/file/',
            'class' => 'file',
            'method' => 'show',
        ]);

        $this->assertSame(
            [
                'definer' => 'request',
                'regexp' => '/^\\/file/',
                'class' => 'file',
                'method' => 'show',
            ],
            $item->readConfig($row)
        );
    }

    public function testInvalidHandlerConfigTypeIsRejected(): void
    {
        $item = $this->makeMatcherItem();

        $this->expectException(\UnexpectedValueException::class);
        $item->readConfig(new \stdClass());
    }

    private function makeMatcherItem(): object
    {
        require_once dirname(__DIR__, 4) . '/unit/mock/_core/base/DataFunctions.php';
        require_once dirname(__DIR__, 4) . '/_core/di/container_interface.php';
        require_once dirname(__DIR__, 4) . '/_core/di/container_aware_trait.php';
        require_once dirname(__DIR__, 4) . '/_core/base/data.php';
        require_once dirname(__DIR__, 4) . '/_core/service/config/row.php';
        require_once dirname(__DIR__, 4) . '/_core/service/matcher/item.php';

        return new class extends \fan\core\service\matcher\item {
            public function __construct()
            {
            }

            public function readConfig(mixed $data): array
            {
                return $this->readHandlerConfig($data);
            }
        };
    }
}
