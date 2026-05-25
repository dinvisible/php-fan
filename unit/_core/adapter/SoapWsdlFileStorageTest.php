<?php

declare(strict_types=1);

use fan\core\adapter\soap_wsdl_file_storage;
use PHPUnit\Framework\TestCase;

final class SoapWsdlFileStorageTest extends TestCase
{
    public function testStorageWrapsWsdlFileExistence(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'fan_soap_wsdl_');
        $this->assertIsString($file);
        $storage = new soap_wsdl_file_storage();

        try {
            $this->assertTrue($storage->exists($file));
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $this->assertFalse($storage->exists($file));
    }
}
