<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../_core/service/config/base.php';
require_once __DIR__ . '/../../../../_core/service/config/yaml.php';

class ServiceConfigYamlTest extends \PHPUnit\Framework\TestCase
{
    public function testYamlExtensionIsAppliedWhenResolvingConfigFile(): void
    {
        $dir = sys_get_temp_dir() . '/php-fan-yaml-' . uniqid('', true);
        mkdir($dir);
        file_put_contents($dir . '/service.yaml', 'enabled: true');

        try {
            $loader = (new \fan\core\service\config\yaml())->setDirPath($dir);

            $this->assertSame($dir . '/service.yaml', $loader->getFilePath('service'));
            $this->assertSame([], $loader->loadFile($dir . '/service.yaml'));
        } finally {
            unlink($dir . '/service.yaml');
            rmdir($dir);
        }
    }
}
