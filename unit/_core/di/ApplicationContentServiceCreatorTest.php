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
