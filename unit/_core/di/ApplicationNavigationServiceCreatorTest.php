<?php

declare(strict_types=1);

use fan\core\di\application_navigation_service_creator;
use fan\core\di\container;
use PHPUnit\Framework\TestCase;
use fan\project\service\tab;


final class ApplicationNavigationServiceCreatorTest extends TestCase
{    public function testTabCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = $this->containerWithTabDependencies();
        $tabDelegateFactory = static fn(): object => (object)['name' => 'tab_delegate_factory'];
        $tabViewParserFactory = static fn(): object => (object)['name' => 'tab_view_parser_factory'];
        $received = [];

        $tab = (new application_navigation_service_creator())->createTabService(
            $container,
            $tabDelegateFactory,
            $tabViewParserFactory,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return new ApplicationNavigationServiceCreatorTabProbe();
            }
        );

        $this->assertInstanceOf(ApplicationNavigationServiceCreatorTabProbe::class, $tab);
        $this->assertSame($container->get('upload_size_limit_provider'), $tab->uploadSizeLimitProvider);
        $this->assertSame('\\' . tab::class, $received[0] ?? null);
        $this->assertTrue($received[1] ?? null);
        $this->assertSame($container->get('matcher'), $received[2] ?? null);
        $this->assertSame($container->get('request'), $received[3] ?? null);
        $this->assertSame('member', ($received[5])('member', 'custom')->namespace);
        $this->assertSame($container->get('request_input'), $received[6] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[7] ?? null);
        $this->assertSame($container->get('role'), ($received[8])());
        $this->assertSame($container->get('transfer'), ($received[9])());
        $this->assertSame('service', ($received[10])('service', 'arr')->configType);
        $this->assertNull($received[18] ?? null);
        $this->assertSame('safe', ($received[14])(true)->mode);
        $this->assertSame($container->get('tab_state'), $received[29] ?? null);
        $this->assertSame('cache-key', ($received[32])('cache-key')->type);
        $this->assertSame($tabDelegateFactory, $received[34] ?? null);
        $this->assertSame($tabViewParserFactory, $received[35] ?? null);
        $this->assertSame($container->get('tab_alias_file_storage'), $received[39] ?? null);
        $this->assertSame(['value'], ($received[40])('value'));
        $this->assertSame(['a' => 1, 'b' => 2], ($received[41])(['a' => 1], ['b' => 2]));
        $this->assertSame('fallback', ($received[42])([], 'missing', 'fallback'));
        $this->assertSame($container->get('class_name_resolver'), $received[43] ?? null);
        $this->assertTrue(($received[44] ?? static fn(): bool => false)(new ArrayObject()));
        $this->assertSame('ApplicationNavigationServiceCreatorTest', ($received[45] ?? static fn(): string => '')($this));
        $this->assertSame($container->get('image_metadata_reader'), $received[46] ?? null);
        $this->assertSame($container->get('error_log_writer'), $received[47] ?? null);
        $this->assertSame($container->get('block_file_storage'), $received[48] ?? null);
        $this->assertSame($container->get('meta_file_storage'), $received[49] ?? null);
        $this->assertSame($container->get('project_tool_file_storage'), $received[50] ?? null);
        $this->assertSame($container->get('root_html_file_storage'), $received[51] ?? null);
    }

    private function containerWithTabDependencies(): container
    {
        $container = new container();
        $container
            ->factory('matcher', static fn(): object => (object)['name' => 'matcher'])
            ->factory('request', static fn(): object => (object)['name' => 'request'])
            ->factory('locale', static fn(): object => (object)['name' => 'locale'])
            ->factory('session', static fn(container $container, string $namespace, string $group): object => (object)['namespace' => $namespace, 'group' => $group], false)
            ->factory('request_input', static fn(): object => (object)['name' => 'request_input'])
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('role', static fn(): object => (object)['name' => 'role'])
            ->factory('transfer', static fn(): object => (object)['name' => 'transfer'])
            ->factory('config', static fn(container $container, string $configType = 'service', string $sourceType = 'arr'): object => (object)['configType' => $configType, 'sourceType' => $sourceType], false)
            ->factory('header', static fn(): object => (object)['name' => 'header'])
            ->factory('application', static fn(): object => (object)['name' => 'application'])
            ->factory('debug', static fn(): object => (object)['name' => 'debug'])
            ->factory('json', static fn(container $container, bool $useBase64 = false): object => (object)['mode' => $useBase64 ? 'safe' : 'plain'], false)
            ->factory('data_loader', static fn(): object => (object)['name' => 'data_loader'])
            ->factory('array_adducer', static fn(): callable => static fn(mixed $value): array => is_array($value) ? $value : [$value])
            ->factory('recursive_merger', static fn(): callable => static fn(mixed ...$values): array => array_replace_recursive(...$values))
            ->factory('array_value_reader', static fn(): callable => static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default)
            ->factory('class_name_resolver', static fn(): callable => static fn(object $object): string => get_class($object))
            ->factory('array_like_checker', static fn(): callable => static fn(mixed $value): bool => is_array($value) || $value instanceof \ArrayAccess)
            ->factory('short_class_name_resolver', static fn(): callable => static function (object|string $object): string {
                $className = is_object($object) ? get_class($object) : $object;
                $position = strrpos($className, '\\');

                return $position === false ? $className : substr($className, $position + 1);
            })
            ->factory('error', static fn(): object => (object)['name' => 'error'])
            ->factory('cookie', static fn(): object => (object)['name' => 'cookie'])
            ->factory('reflector', static fn(): object => (object)['name' => 'reflector'])
            ->factory('entity', static fn(): object => (object)['name' => 'entity'], false)
            ->factory('form', static fn(): object => (object)['name' => 'form'], false)
            ->factory('pager', static fn(): object => (object)['name' => 'pager'], false)
            ->factory('obfuscator', static fn(): object => (object)['name' => 'obfuscator'], false)
            ->factory('image_modify', static fn(): object => (object)['name' => 'image_modify'], false)
            ->factory('database', static fn(): object => (object)['name' => 'database'], false)
            ->factory('user', static fn(): object => (object)['name' => 'user'], false)
            ->factory('date', static fn(): object => (object)['name' => 'date'], false)
            ->factory('tab_state', static fn(): object => (object)['name' => 'tab_state'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('php_array_file_loader', static fn(): object => (object)['name' => 'php_array_file_loader'])
            ->factory('block_factory', static fn(): object => (object)['name' => 'block_factory'])
            ->factory('block_exception_factory', static fn(): object => (object)['name' => 'block_exception_factory'])
            ->factory('meta_row_factory', static fn(): object => (object)['name' => 'meta_row_factory'])
            ->factory('tab_alias_file_storage', static fn(): object => (object)['name' => 'tab_alias_file_storage'])
            ->factory('image_metadata_reader', static fn(): object => (object)['name' => 'image_metadata_reader'])
            ->factory('error_log_writer', static fn(): object => (object)['name' => 'error_log_writer'])
            ->factory('block_file_storage', static fn(): object => (object)['name' => 'block_file_storage'])
            ->factory('meta_file_storage', static fn(): object => (object)['name' => 'meta_file_storage'])
            ->factory('project_tool_file_storage', static fn(): object => (object)['name' => 'project_tool_file_storage'])
            ->factory('root_html_file_storage', static fn(): object => (object)['name' => 'root_html_file_storage'])
            ->factory('upload_size_limit_provider', static fn(): object => (object)['name' => 'upload_size_limit_provider']);

        return $container;
    }
}

final class ApplicationNavigationServiceCreatorTabProbe
{
    public ?object $uploadSizeLimitProvider = null;

    public function setUploadSizeLimitProvider(object $uploadSizeLimitProvider): void
    {
        $this->uploadSizeLimitProvider = $uploadSizeLimitProvider;
    }
}
