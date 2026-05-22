<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../_core/service/config/base.php';
require_once __DIR__ . '/../../../../_core/service/config/arr.php';

class ServiceConfigArrTest extends \PHPUnit\Framework\TestCase
{
    public function testPhpExtensionIsAppliedWhenResolvingConfigFile(): void
    {
        $dir = sys_get_temp_dir() . '/php-fan-arr-' . uniqid('', true);
        mkdir($dir);
        file_put_contents($dir . '/service.php', '<?php return [];');

        try {
            $loader = (new \fan\core\service\config\arr())->setDirPath($dir);

            $this->assertSame($dir . '/service.php', $loader->getFilePath('service'));
            $this->assertSame([], $loader->loadFile($dir . '/service.php'));
        } finally {
            unlink($dir . '/service.php');
            rmdir($dir);
        }
    }
}
