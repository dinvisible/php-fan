<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\plain\db_file;
use fan\core\plain\obfuscator;


final class application_controller_service_creator
{
    private \Closure $projectServiceClassExists;

    public function __construct(?callable $projectServiceClassExists = null)
    {
        $this->projectServiceClassExists = \Closure::fromCallable(
            $projectServiceClassExists ?? static fn(string $className): bool => class_exists($className)
        );
    }

    public function createPlainService(
        container_interface $container,
        callable $plainControllerFactory,
        callable $plainServiceFactory
    ): mixed {
        $className = self::getProjectServiceClassName('plain');
        if (!$this->projectServiceClassExists($className)) {
            throw new \InvalidArgumentException('Service "plain" does not expose a project class.');
        }
        $controllerDependencies = $this->controllerDependencies($container);

        return $plainServiceFactory(
            $className,
            true,
            $controllerDependencies->matcher(),
            $controllerDependencies->plainConfigFactory(),
            $controllerDependencies->header(),
            static function (string $controllerClass, int|string $controllerKey, object $handler) use ($controllerDependencies): array {
                if (is_a($controllerClass, obfuscator::class, true)) {
                    return [
                        $controllerDependencies->obfuscatorFactory(),
                        $controllerDependencies->request(),
                    ];
                }
                if (is_a($controllerClass, db_file::class, true)) {
                    return [$controllerDependencies->plainFileContext()];
                }
                return [];
            },
            $plainControllerFactory,
            $controllerDependencies->bootstrapRuntime(),
            $controllerDependencies->config(),
            $controllerDependencies->cacheFactory()
        );
    }

    private function controllerDependencies(container_interface $container): application_controller_service_dependencies
    {
        return new application_controller_service_dependencies($container);
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }

    private function projectServiceClassExists(string $className): bool
    {
        return (bool)($this->projectServiceClassExists)($className);
    }
}
