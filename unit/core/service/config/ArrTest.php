<?php

declare(strict_types=1);
use fan\core\service\config\arr;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../../core/service/config/base.php';
require_once __DIR__ . '/../../../../core/service/config/arr.php';

class ServiceConfigArrTest extends TestCase
{
    public function testPhpExtensionIsAppliedWhenResolvingConfigFile(): void
    {
        $dir = sys_get_temp_dir() . '/php-fan-arr-' . uniqid('', true);
        mkdir($dir);
        file_put_contents($dir . '/service.php', '<?php return ["legacy" => "ignored"];');
        $loaderCalls = [];

        try {
            $loader = (new arr())
                ->setPhpArrayFileLoader(static function (string $path, mixed $default = null) use (&$loaderCalls): array {
                    $loaderCalls[] = [$path, $default];

                    return ['service' => ['enabled' => true]];
                })
                ->setFileStorage(new class {
                    public function exists(string $path): bool
                    {
                        return is_file($path);
                    }
                })
                ->setDirPath($dir);

            $this->assertSame($dir . '/service.php', $loader->getFilePath('service'));
            $this->assertSame(['service' => ['enabled' => true]], $loader->loadFile($dir . '/service.php'));
            $this->assertSame([[$dir . '/service.php', []]], $loaderCalls);
        } finally {
            unlink($dir . '/service.php');
            rmdir($dir);
        }
    }

    public function testNonArrayLoaderResultFallsBackToEmptyArray(): void
    {
        $loader = (new arr())
            ->setFileStorage(new class {
                public function exists(string $path): bool
                {
                    return is_file($path);
                }
            })
            ->setPhpArrayFileLoader(static fn(string $path, mixed $default = null): string => 'not-array');

        $this->assertSame([], $loader->loadFile(__FILE__));
    }

    public function testSourceUsesInjectedPhpArrayLoader(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../../core/service/config/arr.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('setPhpArrayFileLoader(callable $phpArrayFileLoader)', $source);
        $this->assertStringContainsString('private function phpArrayFileLoader(): callable', $source);
        $this->assertStringNotContainsString('require $srcFilePath', $source);
    }
}
