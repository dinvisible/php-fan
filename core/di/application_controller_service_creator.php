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
            $container->get('matcher'),
            static fn(): mixed => $container->get('config', 'plain'),
            $container->get('header'),
            static function (string $controllerClass, int|string $controllerKey, object $handler) use ($container): array {
                if (is_a($controllerClass, obfuscator::class, true)) {
                    return [
                        static fn(string $type): mixed => $container->get('obfuscator', $type),
                        $container->get('request'),
                    ];
                }
                if (is_a($controllerClass, db_file::class, true)) {
                    return [$container->get('plain_file_context')];
                }
                return [];
            },
            $plainControllerFactory,
            $container->get('bootstrap_runtime'),
            $container->get('config'),
            static fn(string $type): mixed => $container->get('cache', $type)
        );
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }
}
