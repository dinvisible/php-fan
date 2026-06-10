<?php

declare(strict_types=1);
use fan\core\service\config\yaml;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../../core/service/config/base.php';
require_once __DIR__ . '/../../../../core/service/config/yaml.php';

class ServiceConfigYamlTest extends TestCase
{
    public function testYamlExtensionIsAppliedWhenResolvingConfigFile(): void
    {
        $dir = sys_get_temp_dir() . '/php-fan-yaml-' . uniqid('', true);
        mkdir($dir);
        file_put_contents($dir . '/service.yaml', 'enabled: true');

        try {
            $loader = (new yaml())
                ->setFileStorage(new class {
                    public function exists(string $path): bool
                    {
                        return is_file($path);
                    }
                })
                ->setDirPath($dir);

            $this->assertSame($dir . '/service.yaml', $loader->getFilePath('service'));
            $this->assertSame([], $loader->loadFile($dir . '/service.yaml'));
        } finally {
            unlink($dir . '/service.yaml');
            rmdir($dir);
        }
    }
}
