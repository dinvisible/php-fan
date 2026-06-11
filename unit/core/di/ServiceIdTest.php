<?php

declare(strict_types=1);

use fan\core\di\service_id;
use PHPUnit\Framework\TestCase;

final class ServiceIdTest extends TestCase
{
    public function testServiceIdConstantsExposeLegacyWireValues(): void
    {
        $this->assertSame('application', service_id::APPLICATION);
        $this->assertSame('matcher', service_id::MATCHER);
        $this->assertSame('request_input', service_id::REQUEST_INPUT);
        $this->assertSame('bootstrap_runtime', service_id::BOOTSTRAP_RUNTIME);
    }

    public function testServiceIdConstantsAreUnique(): void
    {
        $reflection = new ReflectionClass(service_id::class);
        $values = array_values($reflection->getConstants());

        $this->assertSame($values, array_values(array_unique($values)));
    }
}
