<?php

declare(strict_types=1);

use fan\core\adapter\cache_source_file_metadata;
use PHPUnit\Framework\TestCase;

final class CacheSourceFileMetadataTest extends TestCase
{
    public function testMetadataWrapsSourceFileStatOperations(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'fan_cache_source_');
        $this->assertIsString($file);
        file_put_contents($file, 'payload');
        $metadata = new cache_source_file_metadata();

        try {
            $this->assertTrue($metadata->isFile($file));
            $this->assertSame(7, $metadata->size($file));
            $this->assertIsInt($metadata->modifiedTime($file));
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
