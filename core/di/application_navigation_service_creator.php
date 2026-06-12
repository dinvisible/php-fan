<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_service_creator
{
    public function createTabService(
        container_interface $container,
        callable $tabDelegateFactory,
        callable $tabViewParserFactory,
        callable $tabServiceFactory
    ): mixed {
        $className = self::getProjectServiceClassName('tab');
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Service "tab" does not expose a project class.');
        }

        $tab = $tabServiceFactory(
            $className,
            true,
            $container->get(service_id::MATCHER),
            $container->get(service_id::REQUEST),
            $container->get(service_id::LOCALE),
            static fn(mixed ...$arguments): mixed => $container->get(service_id::SESSION, ...$arguments),
            $container->get(service_id::REQUEST_INPUT),
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            static fn(): mixed => $container->get(service_id::ROLE),
            static fn(): mixed => $container->get(service_id::TRANSFER),
            static fn(string $configType = 'service', string $sourceType = 'arr'): mixed => $container->get(service_id::CONFIG, $configType, $sourceType),
            static fn(): mixed => $container->get(service_id::HEADER),
            static fn(): mixed => $container->get(service_id::APPLICATION),
            static fn(): mixed => $container->get(service_id::DEBUG),
            static fn(bool $useBase64 = false): mixed => $container->get(service_id::JSON, $useBase64),
            static fn(): mixed => $container->get(service_id::DATA_LOADER),
            static fn(): mixed => throw new \RuntimeException('Template service has been removed.'),
            static fn(): mixed => $container->get(service_id::ERROR),
            null,
            static fn(): mixed => $container->get(service_id::COOKIE),
            static fn(): mixed => $container->get(service_id::REFLECTOR),
            static fn(mixed ...$arguments): mixed => $container->get(service_id::ENTITY, ...$arguments),
            null,
            static fn(mixed ...$arguments): mixed => $container->get(service_id::PAGER, ...$arguments),
            static fn(mixed ...$arguments): mixed => $container->get(service_id::OBFUSCATOR, ...$arguments),
            static fn(mixed ...$arguments): mixed => $container->get(service_id::IMAGE_MODIFY, ...$arguments),
            static fn(mixed ...$arguments): mixed => throw new \RuntimeException('Database service is not configured.'),
            static fn(mixed ...$arguments): mixed => $container->get(service_id::USER, ...$arguments),
            static fn(mixed ...$arguments): mixed => $container->get(service_id::DATE, ...$arguments),
            $container->get(service_id::TAB_STATE),
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            $container->get(service_id::CONFIG),
            static fn(string $type): mixed => $container->get(service_id::CACHE, $type),
            $container->get(service_id::PHP_ARRAY_FILE_LOADER),
            $tabDelegateFactory,
            $tabViewParserFactory,
            $container->get(service_id::BLOCK_FACTORY),
            $container->get(service_id::BLOCK_EXCEPTION_FACTORY),
            $container->get(service_id::META_ROW_FACTORY),
            $container->get(service_id::TAB_ALIAS_FILE_STORAGE),
            $container->get(service_id::ARRAY_ADDUCER),
            $container->get(service_id::RECURSIVE_MERGER),
            $container->get(service_id::ARRAY_VALUE_READER),
            $container->get(service_id::CLASS_NAME_RESOLVER),
            $container->get(service_id::ARRAY_LIKE_CHECKER),
            $container->get(service_id::SHORT_CLASS_NAME_RESOLVER),
            $container->get(service_id::IMAGE_METADATA_READER),
            $container->get(service_id::ERROR_LOG_WRITER),
            $container->get(service_id::BLOCK_FILE_STORAGE),
            $container->get(service_id::META_FILE_STORAGE),
            $container->get(service_id::PROJECT_TOOL_FILE_STORAGE),
            $container->get(service_id::ROOT_HTML_FILE_STORAGE)
        );
        if (method_exists($tab, 'setUploadSizeLimitProvider')) {
            $tab->setUploadSizeLimitProvider($container->get(service_id::UPLOAD_SIZE_LIMIT_PROVIDER));
        }

        return $tab;
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }
}
