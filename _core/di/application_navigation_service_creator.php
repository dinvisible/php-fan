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
            $container->get('matcher'),
            $container->get('request'),
            $container->get('locale'),
            static fn(mixed ...$arguments): mixed => $container->get('session', ...$arguments),
            $container->get('request_input'),
            $container->get('bootstrap_runtime'),
            static fn(): mixed => $container->get('role'),
            static fn(): mixed => $container->get('transfer'),
            static fn(string $configType = 'service', string $sourceType = 'arr'): mixed => $container->get('config', $configType, $sourceType),
            static fn(): mixed => $container->get('header'),
            static fn(): mixed => $container->get('application'),
            static fn(): mixed => $container->get('debug'),
            static fn(bool $useBase64 = false): mixed => $container->get('json', $useBase64),
            static fn(): mixed => $container->get('data_loader'),
            static fn(): mixed => throw new \RuntimeException('Template service has been removed.'),
            static fn(): mixed => $container->get('error'),
            null,
            static fn(): mixed => $container->get('cookie'),
            static fn(): mixed => $container->get('reflector'),
            static fn(mixed ...$arguments): mixed => $container->get('entity', ...$arguments),
            null,
            static fn(mixed ...$arguments): mixed => $container->get('pager', ...$arguments),
            static fn(mixed ...$arguments): mixed => $container->get('obfuscator', ...$arguments),
            static fn(mixed ...$arguments): mixed => $container->get('image_modify', ...$arguments),
            static fn(mixed ...$arguments): mixed => throw new \RuntimeException('Database service is not configured.'),
            static fn(mixed ...$arguments): mixed => $container->get('user', ...$arguments),
            static fn(mixed ...$arguments): mixed => $container->get('date', ...$arguments),
            $container->get('tab_state'),
            $container->get('bootstrap_runtime'),
            $container->get('config'),
            static fn(string $type): mixed => $container->get('cache', $type),
            $container->get('php_array_file_loader'),
            $tabDelegateFactory,
            $tabViewParserFactory,
            $container->get('block_factory'),
            $container->get('block_exception_factory'),
            $container->get('meta_row_factory'),
            $container->get('tab_alias_file_storage'),
            $container->get('array_adducer'),
            $container->get('recursive_merger'),
            $container->get('array_value_reader'),
            $container->get('class_name_resolver'),
            $container->get('array_like_checker'),
            $container->get('short_class_name_resolver'),
            $container->get('image_metadata_reader'),
            $container->get('error_log_writer'),
            $container->get('block_file_storage'),
            $container->get('meta_file_storage'),
            $container->get('project_tool_file_storage'),
            $container->get('root_html_file_storage')
        );
        if (method_exists($tab, 'setUploadSizeLimitProvider')) {
            $tab->setUploadSizeLimitProvider($container->get('upload_size_limit_provider'));
        }

        return $tab;
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }
}
