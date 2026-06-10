<?php

declare(strict_types=1);

use fan\core\adapter\email_template_file_storage;
use PHPUnit\Framework\TestCase;

final class EmailTemplateFileStorageTest extends TestCase
{
    public function testStorageWrapsPlainEmailTemplateFileOperations(): void
    {
        $file = sys_get_temp_dir() . '/fan_email_template_file_storage_' . bin2hex(random_bytes(4)) . '.tpl';
        $storage = new email_template_file_storage();

        try {
            $this->assertFalse($storage->isFile($file));
            $this->assertFalse($storage->isReadable($file));
            file_put_contents($file, 'Subject' . "\n" . 'Body');
            $this->assertTrue($storage->isFile($file));
            $this->assertTrue($storage->isReadable($file));
            $this->assertSame('Subject' . "\n" . 'Body', $storage->read($file));
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
