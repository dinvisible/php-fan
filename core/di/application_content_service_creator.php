<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_content_service_creator
{
    public function createTranslationService(
        container_interface $container,
        callable $translationServiceFactory
    ): mixed
    {
        $className = self::getProjectServiceClassName('translation');
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Service "translation" does not expose a project class.');
        }

        return $translationServiceFactory(
            $className,
            true,
            $container->get(service_id::LOCALE),
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            static fn(): mixed => $container->get(service_id::TAB),
            [],
            $container->get(service_id::ERROR),
            $container->get(service_id::BLOCK_CONTEXT),
            $container->get(service_id::MATCHER),
            $container->get(service_id::REQUEST_INPUT),
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            $container->get(service_id::CONFIG),
            static fn(string $type): mixed => $container->get(service_id::CACHE, $type),
            $container->get(service_id::PHP_ARRAY_FILE_LOADER),
            $container->get(service_id::TRANSLATION_FILE_STORAGE)
        );
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }
}
