<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../_core/service/config/base.php';
require_once __DIR__ . '/../../../../_core/service/config/xml.php';

class ServiceConfigXmlTest extends \PHPUnit\Framework\TestCase
{
    public function testXmlExtensionIsAppliedWhenResolvingConfigFile(): void
    {
        $dir = sys_get_temp_dir() . '/php-fan-xml-' . uniqid('', true);
        mkdir($dir);
        file_put_contents($dir . '/service.xml', '<config/>');

        try {
            $loader = (new \fan\core\service\config\xml())->setDirPath($dir);

            $this->assertSame($dir . '/service.xml', $loader->getFilePath('service'));
            $this->assertSame([], $loader->loadFile($dir . '/service.xml'));
        } finally {
            unlink($dir . '/service.xml');
            rmdir($dir);
        }
    }
}
