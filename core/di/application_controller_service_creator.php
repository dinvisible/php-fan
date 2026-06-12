<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\plain\db_file;
use fan\core\plain\obfuscator;


final class application_controller_service_creator
{
    public function createPlainService(
        container_interface $container,
        callable $plainControllerFactory,
        callable $plainServiceFactory
    ): mixed {
        $className = self::getProjectServiceClassName('plain');
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Service "plain" does not expose a project class.');
        }

        return $plainServiceFactory(
            $className,
            true,
            $container->get(service_id::MATCHER),
            static fn(): mixed => $container->get(service_id::CONFIG, 'plain'),
            $container->get(service_id::HEADER),
            static function (string $controllerClass, int|string $controllerKey, object $handler) use ($container): array {
                if (is_a($controllerClass, obfuscator::class, true)) {
                    return [
                        static fn(string $type): mixed => $container->get(service_id::OBFUSCATOR, $type),
                        $container->get(service_id::REQUEST),
                    ];
                }
                if (is_a($controllerClass, db_file::class, true)) {
                    return [$container->get(service_id::PLAIN_FILE_CONTEXT)];
                }
                return [];
            },
            $plainControllerFactory,
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            $container->get(service_id::CONFIG),
            static fn(string $type): mixed => $container->get(service_id::CACHE, $type)
        );
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }
}
