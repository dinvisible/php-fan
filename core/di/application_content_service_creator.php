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
            $container->get('locale'),
            $container->get('bootstrap_runtime'),
            static fn(): mixed => $container->get('tab'),
            [],
            $container->get('error'),
            $container->get('block_context'),
            $container->get('matcher'),
            $container->get('request_input'),
            $container->get('bootstrap_runtime'),
            $container->get('config'),
            static fn(string $type): mixed => $container->get('cache', $type),
            $container->get('php_array_file_loader'),
            $container->get('translation_file_storage')
        );
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }
}
