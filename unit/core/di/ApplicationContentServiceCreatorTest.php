<?php

declare(strict_types=1);

use fan\core\di\application_content_service_creator;
use fan\core\di\container;
use PHPUnit\Framework\TestCase;
use fan\project\service\translation;


final class ApplicationContentServiceCreatorTest extends TestCase
{
    public function testTranslationCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = $this->containerWithSharedContentDependencies();
        $received = [];

        $service = (new application_content_service_creator())->createTranslationService(
            $container,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'translation'];
            }
        );

        $this->assertSame('translation', $service->service);
        $this->assertSame('\\' . translation::class, $received[0] ?? null);
        $this->assertSame($container->get('locale'), $received[2] ?? null);
        $this->assertSame($container->get('translation_file_storage'), $received[14] ?? null);
    }

    public function testProjectServiceClassAvailabilityCheckIsInjected(): void
    {
        $checkedClasses = [];
        $creator = new application_content_service_creator(
            static function (string $className) use (&$checkedClasses): bool {
                $checkedClasses[] = $className;

                return false;
            }
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service "translation" does not expose a project class.');

        try {
            $creator->createTranslationService(
                $this->containerWithSharedContentDependencies(),
                static fn(): object => (object)['service' => 'translation']
            );
        } finally {
            $this->assertSame(['\\' . translation::class], $checkedClasses);
        }
    }

    public function testContentCreatorUsesDependencyBundle(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_service_creator.php');
        $bundleSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_service_dependencies.php');
        $contextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_context_dependencies.php');
        $localizationContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_localization_context_dependencies.php');
        $localeContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_locale_context_dependencies.php');
        $tabFactoryContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_tab_factory_context_dependencies.php');
        $errorBlockContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_error_block_context_dependencies.php');
        $errorContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_error_context_dependencies.php');
        $blockContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_block_context_dependencies.php');
        $requestMatcherContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_request_matcher_context_dependencies.php');
        $matcherContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_matcher_context_dependencies.php');
        $requestInputContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_request_input_context_dependencies.php');
        $runtimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_runtime_dependencies.php');
        $bootstrapRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_bootstrap_runtime_dependencies.php');
        $configCacheRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_config_cache_runtime_dependencies.php');
        $configRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_config_runtime_dependencies.php');
        $cacheRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_cache_runtime_dependencies.php');
        $storageSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_storage_dependencies.php');
        $phpArrayFileLoaderStorageSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_php_array_file_loader_storage_dependencies.php');
        $translationFileStorageSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_translation_file_storage_dependencies.php');

        $this->assertIsString($source);
        $this->assertIsString($bundleSource);
        $this->assertIsString($contextSource);
        $this->assertIsString($localizationContextSource);
        $this->assertIsString($localeContextSource);
        $this->assertIsString($tabFactoryContextSource);
        $this->assertIsString($errorBlockContextSource);
        $this->assertIsString($errorContextSource);
        $this->assertIsString($blockContextSource);
        $this->assertIsString($requestMatcherContextSource);
        $this->assertIsString($matcherContextSource);
        $this->assertIsString($requestInputContextSource);
        $this->assertIsString($runtimeSource);
        $this->assertIsString($bootstrapRuntimeSource);
        $this->assertIsString($configCacheRuntimeSource);
        $this->assertIsString($configRuntimeSource);
        $this->assertIsString($cacheRuntimeSource);
        $this->assertIsString($storageSource);
        $this->assertIsString($phpArrayFileLoaderStorageSource);
        $this->assertIsString($translationFileStorageSource);
        $this->assertStringContainsString('private function contentDependencies(container_interface $container): application_content_service_dependencies', $source);
        $this->assertStringContainsString('return new application_content_service_dependencies($container);', $source);
        $this->assertStringContainsString('$contentDependencies = $this->contentDependencies($container);', $source);
        $this->assertStringContainsString('$contentDependencies->locale()', $source);
        $this->assertStringContainsString('$contentDependencies->bootstrapRuntime()', $source);
        $this->assertStringContainsString('$contentDependencies->translationFileStorage()', $source);
        $this->assertStringContainsString('final class application_content_service_dependencies', $bundleSource);
        $this->assertStringContainsString('new application_content_context_dependencies($container)', $bundleSource);
        $this->assertStringContainsString('new application_content_runtime_dependencies($container)', $bundleSource);
        $this->assertStringContainsString('new application_content_storage_dependencies($container)', $bundleSource);
        $this->assertStringContainsString('$this->localization = new application_content_localization_context_dependencies($container);', $contextSource);
        $this->assertStringContainsString('$this->errorBlock = new application_content_error_block_context_dependencies($container);', $contextSource);
        $this->assertStringContainsString('$this->requestMatcher = new application_content_request_matcher_context_dependencies($container);', $contextSource);
        $this->assertStringContainsString('new application_content_locale_context_dependencies($container)', $localizationContextSource);
        $this->assertStringContainsString('new application_content_tab_factory_context_dependencies($container)', $localizationContextSource);
        $this->assertStringContainsString('return $this->locale->locale();', $localizationContextSource);
        $this->assertStringContainsString('return $this->tabFactory->tabFactory();', $localizationContextSource);
        $this->assertStringContainsString('new application_content_error_context_dependencies($container)', $errorBlockContextSource);
        $this->assertStringContainsString('new application_content_block_context_dependencies($container)', $errorBlockContextSource);
        $this->assertStringContainsString('return $this->error->error();', $errorBlockContextSource);
        $this->assertStringContainsString('return $this->blockContext->blockContext();', $errorBlockContextSource);
        $this->assertStringContainsString('new application_content_matcher_context_dependencies($container)', $requestMatcherContextSource);
        $this->assertStringContainsString('new application_content_request_input_context_dependencies($container)', $requestMatcherContextSource);
        $this->assertStringContainsString('return $this->matcher->matcher();', $requestMatcherContextSource);
        $this->assertStringContainsString('return $this->requestInput->requestInput();', $requestMatcherContextSource);
        $this->assertStringContainsString('return $this->container->get(service_id::LOCALE);', $localeContextSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::TAB);', $tabFactoryContextSource);
        $this->assertStringContainsString('return $this->container->get(service_id::ERROR);', $errorContextSource);
        $this->assertStringContainsString('return $this->container->get(service_id::BLOCK_CONTEXT);', $blockContextSource);
        $this->assertStringContainsString('return $this->container->get(service_id::MATCHER);', $matcherContextSource);
        $this->assertStringContainsString('return $this->container->get(service_id::REQUEST_INPUT);', $requestInputContextSource);
        $this->assertStringContainsString('new application_content_bootstrap_runtime_dependencies($container)', $runtimeSource);
        $this->assertStringContainsString('new application_content_config_cache_runtime_dependencies($container)', $runtimeSource);
        $this->assertStringContainsString('return $this->bootstrap->bootstrapRuntime();', $runtimeSource);
        $this->assertStringContainsString('return $this->configCache->cacheFactory();', $runtimeSource);
        $this->assertStringContainsString('return $this->container->get(service_id::BOOTSTRAP_RUNTIME);', $bootstrapRuntimeSource);
        $this->assertStringContainsString('new application_content_config_runtime_dependencies($container)', $configCacheRuntimeSource);
        $this->assertStringContainsString('new application_content_cache_runtime_dependencies($container)', $configCacheRuntimeSource);
        $this->assertStringContainsString('return $this->config->config();', $configCacheRuntimeSource);
        $this->assertStringContainsString('return $this->cache->cacheFactory();', $configCacheRuntimeSource);
        $this->assertStringContainsString('return $this->container->get(service_id::CONFIG);', $configRuntimeSource);
        $this->assertStringContainsString('return fn(string $type): mixed => $this->container->get(service_id::CACHE, $type);', $cacheRuntimeSource);
        $this->assertStringContainsString('new application_content_php_array_file_loader_storage_dependencies($container)', $storageSource);
        $this->assertStringContainsString('new application_content_translation_file_storage_dependencies($container)', $storageSource);
        $this->assertStringContainsString('return $this->phpArrayFileLoader->phpArrayFileLoader();', $storageSource);
        $this->assertStringContainsString('return $this->translationFileStorage->translationFileStorage();', $storageSource);
        $this->assertStringContainsString('return $this->container->get(service_id::PHP_ARRAY_FILE_LOADER);', $phpArrayFileLoaderStorageSource);
        $this->assertStringContainsString('return $this->container->get(service_id::TRANSLATION_FILE_STORAGE);', $translationFileStorageSource);
    }

    private function containerWithSharedContentDependencies(): container
    {
        $container = new container();
        $container
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('translation', static fn(): object => (object)['name' => 'translation'])
            ->factory('tab', static fn(): object => (object)['name' => 'tab'])
            ->factory('session', static fn(): object => (object)['name' => 'session'], false)
            ->factory('array_value_reader', static fn(): callable => static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default)
            ->factory('array_adducer', static fn(): callable => static fn(mixed $value): array => is_array($value) ? $value : [$value])
            ->factory('class_name_resolver', static fn(): callable => static fn(object $object): string => get_class($object))
            ->factory('short_class_name_resolver', static fn(): callable => static function (object|string $object): string {
                $class = is_object($object) ? get_class($object) : $object;
                $parts = explode('\\', $class);

                return end($parts);
            })
            ->factory('config', static fn(): object => (object)['name' => 'config'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('locale', static fn(): object => (object)['name' => 'locale'])
            ->factory('error', static fn(): object => (object)['name' => 'error'])
            ->factory('block_context', static fn(): object => (object)['name' => 'block_context'])
            ->factory('matcher', static fn(): object => (object)['name' => 'matcher'])
            ->factory('request_input', static fn(): object => (object)['name' => 'request_input'])
            ->factory('php_array_file_loader', static fn(): object => (object)['name' => 'php_array_file_loader'])
            ->factory('translation_file_storage', static fn(): object => (object)['name' => 'translation_file_storage']);

        return $container;
    }
}
