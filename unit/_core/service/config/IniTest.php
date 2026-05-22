<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../_core/service/config/base.php';
require_once __DIR__ . '/../../../../_core/service/config/ini.php';

class ServiceConfigIniTest extends \PHPUnit\Framework\TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'php-fan-ini-');
    }

    protected function tearDown(): void
    {
        if (is_file($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }

    public function testIniLoaderSeparatesDotKeysAndConvertsBracketLists(): void
    {
        file_put_contents($this->tmpFile, implode("\n", [
            'plain = value',
            'db.host = localhost',
            'db.port = 3306',
            'list = "[alpha; beta;gamma]"',
            '[service]',
            'cache.enabled = 1',
        ]));

        $loader = new \fan\core\service\config\ini();

        $this->assertSame([
            'plain' => 'value',
            'list' => ['alpha', 'beta', 'gamma'],
            'service' => [
                'cache' => ['enabled' => '1'],
            ],
            'db' => [
                'host' => 'localhost',
                'port' => '3306',
            ],
        ], $loader->loadFile($this->tmpFile));
    }

    public function testIniExtensionIsUsedByFilePathResolver(): void
    {
        $dir = dirname($this->tmpFile);
        $name = basename($this->tmpFile, '.tmp');
        $path = $dir . '/' . $name . '.ini';
        file_put_contents($path, 'x = y');

        try {
            $loader = (new \fan\core\service\config\ini())->setDirPath($dir);
            $this->assertSame($path, $loader->getFilePath($name));
        } finally {
            unlink($path);
        }
    }
}
