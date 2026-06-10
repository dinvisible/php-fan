<?php

declare(strict_types=1);

use fan\core\bootstrap\bootstrap_config_loader;
use fan\core\bootstrap\context;
use fan\core\bootstrap\state;
use PHPUnit\Framework\TestCase;
use fan\core\di\context_defaults_factory;


final class BootstrapConfigLoaderTest extends TestCase
{
    public function testLoaderLoadsPhpArrayConfigThroughInjectedContextLoader(): void
    {
        $state = new state();
        $loaderCalls = [];
        $fileStorage = new BootstrapConfigLoaderFileStorageDouble();
        $configPath = sys_get_temp_dir() . '/php-fan-bootstrap-' . bin2hex(random_bytes(4)) . '.php';
        file_put_contents($configPath, '<?php return ["legacy" => "ignored"];');
        $fileStorage->existingPaths[$configPath] = true;

        try {
            $context = new context(
                $state,
                bootstrapLoaderFileStorageFactory: static fn(): object => $fileStorage,
                phpArrayFileLoader: static function (string $path, mixed $default = null) use (&$loaderCalls): array {
                    $loaderCalls[] = [$path, $default];

                    return ['bootstrap' => ['admin_email' => 'admin@example.test']];
                },
                defaultFactoriesFactory: self::defaultFactoriesFactory()
            );

            (new bootstrap_config_loader())($context, $configPath);

            $this->assertSame([$configPath], $fileStorage->checkedPaths);
            $this->assertSame([[$configPath, []]], $loaderCalls);
            $this->assertSame('admin@example.test', $state->config()['bootstrap']['admin_email']);
            $this->assertSame('{PROJECT_DIR}/conf', $state->config()['bootstrap']['global_path']['config_source']);
            $this->assertSame('{PROJECT_DIR}/conf/runner.php', $state->config()['runner']['config']);
        } finally {
            unlink($configPath);
        }
    }

    public function testLoaderStoresDefaultConfigForUnsupportedPath(): void
    {
        $state = new state();
        $fileStorage = new BootstrapConfigLoaderFileStorageDouble();
        $context = new context(
            $state,
            bootstrapLoaderFileStorageFactory: static fn(): object => $fileStorage,
            defaultFactoriesFactory: self::defaultFactoriesFactory()
        );
        $path = sys_get_temp_dir() . '/missing-bootstrap.ini';

        (new bootstrap_config_loader())($context, $path);

        $this->assertSame([$path], $fileStorage->checkedPaths);
        $this->assertSame('admin_email@domain.com', $state->config()['bootstrap']['admin_email']);
        $this->assertSame('{CORE_DIR}/../logs/bootstrap_log', $state->config()['bootstrap']['global_path']['bootstrap_log']);
    }

    public function testLoaderStoresDefaultConfigWithoutPath(): void
    {
        $state = new state();
        $fileStorage = new BootstrapConfigLoaderFileStorageDouble();
        $context = new context(
            $state,
            bootstrapLoaderFileStorageFactory: static fn(): object => $fileStorage,
            defaultFactoriesFactory: self::defaultFactoriesFactory()
        );

        (new bootstrap_config_loader())($context);

        $this->assertSame([], $fileStorage->checkedPaths);
        $this->assertSame('{PROJECT_DIR}/../temp_data/cache/config', $state->config()['config_cache']['BASE_DIR']);
    }

    public function testLoaderHasNoStaticBootstrapDependency(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/application/bootstrap_config_loader.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_config_loader', $source);
        $this->assertStringContainsString('public function __invoke(context $context, ?string $configPath = null): void', $source);
        $this->assertStringContainsString('$configPath !== null && $context->bootstrapLoaderFileStorage()->exists((string)$configPath)', $source);
        $this->assertStringContainsString('$context->loadPhpArrayFile((string)$configPath, [])', $source);
        $this->assertStringContainsString('array_replace_recursive($this->defaultConfig(), is_array($config) ? $config : [])', $source);
        $this->assertStringNotContainsString('\bootstrap::', $source);
        $this->assertStringNotContainsString('file_exists(', $source);
        $this->assertStringNotContainsString('parse_ini_file(', $source);
    }

    private static function defaultFactoriesFactory(): callable
    {
        return new context_defaults_factory();
    }
}

final class BootstrapConfigLoaderFileStorageDouble
{
    public array $checkedPaths = [];
    public array $existingPaths = [];

    public function exists(string $path): bool
    {
        $this->checkedPaths[] = $path;

        return $this->existingPaths[$path] ?? false;
    }
}
