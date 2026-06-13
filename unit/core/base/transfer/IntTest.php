<?php

declare(strict_types=1);
use fan\core\base\transfer\transfer_int;
use PHPUnit\Framework\TestCase;
use function fan\core\base\transfer\ensure_transfer_int_class_loaded;


require_once __DIR__ . '/../../../mock/core/base/TransferStubs.php';
require_once __DIR__ . '/../../../../core/base/transfer.php';
require_once __DIR__ . '/../../../../core/base/transfer/int.php';

class IntTest extends TestCase
{
    public function testCompatibilityLoaderDefinesTransferIntClass(): void
    {
        $this->assertTrue(class_exists('\fan\core\base\transfer\transfer_int'));
        $this->assertInstanceOf(
            '\fan\core\base\transfer\transfer_int',
            new transfer_int('/internal')
        );
    }

    public function testCompatibilityLoaderUsesNamedAvailabilityBoundary(): void
    {
        $checkedClasses = [];

        $this->assertTrue(ensure_transfer_int_class_loaded(
            static function (string $className) use (&$checkedClasses): bool {
                $checkedClasses[] = $className;

                return true;
            }
        ));
        $this->assertSame([transfer_int::class], $checkedClasses);

        $source = file_get_contents(__DIR__ . '/../../../../core/base/transfer/int.php');
        $this->assertIsString($source);
        $this->assertStringContainsString('function ensure_transfer_int_class_loaded(?callable $classExists = null): bool', $source);
        $this->assertStringContainsString('ensure_transfer_int_class_loaded();', $source);
        $this->assertStringNotContainsString('class_exists(transfer_int::class);', $source);
    }
}
