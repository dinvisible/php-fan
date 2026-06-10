<?php

declare(strict_types=1);
use fan\core\service\config\xml;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../../core/service/config/base.php';
require_once __DIR__ . '/../../../../core/service/config/xml.php';

class ServiceConfigXmlTest extends TestCase
{
    public function testXmlExtensionIsAppliedWhenResolvingConfigFile(): void
    {
        $dir = sys_get_temp_dir() . '/php-fan-xml-' . uniqid('', true);
        mkdir($dir);
        file_put_contents($dir . '/service.xml', '<config/>');

        try {
            $loader = (new xml())
                ->setFileStorage(new class {
                    public function exists(string $path): bool
                    {
                        return is_file($path);
                    }
                })
                ->setDirPath($dir);

            $this->assertSame($dir . '/service.xml', $loader->getFilePath('service'));
            $this->assertSame([], $loader->loadFile($dir . '/service.xml'));
        } finally {
            unlink($dir . '/service.xml');
            rmdir($dir);
        }
    }
}
