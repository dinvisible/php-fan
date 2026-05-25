<?php

declare(strict_types=1);

use fan\core\service\pager;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\base\service;
use fan\core\block\base;


class ServicePagerTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/pager.php';

    public function testPageNumberIsClampedToValidRange(): void
    {
        $pager = $this->pager();

        $this->assertSame($pager, $pager->setPageQtt(5));
        $pager->setPageNum(-10);
        $this->assertSame(1, $pager->getPageNum());

        $pager->setPageNum(8);
        $this->assertSame(5.0, $pager->getPageNum());

        $pager->setPageNum(8, true);
        $this->assertSame(8.0, $pager->getPageNum());
        $this->assertSame(8.0, $pager->getPageQtt());
    }

    public function testPageQuantityCanForceOrPreserveCurrentPage(): void
    {
        $pager = $this->pager();
        $pager->setPageNum(7);

        $pager->setPageQtt(3, false);
        $this->assertSame(7.0, $pager->getPageNum());
        $this->assertSame(7.0, $pager->getPageQtt());

        $pager->setPageQtt(3, true);
        $this->assertSame(3.0, $pager->getPageNum());
        $this->assertSame(3.0, $pager->getPageQtt());
    }

    public function testItemPerPageFallsBackToConfiguredDefault(): void
    {
        $pager = $this->pager(['DEFAULT_ITEM_PER_PAGE' => 25]);

        $this->assertSame(25, $pager->getItemPerPage());
        $pager->setItemPerPage(0);
        $this->assertSame(25, $pager->getItemPerPage());

        $pager->setItemPerPage(12.6);
        $this->assertSame(13.0, $pager->getItemPerPage());
    }

    public function testSetItemQuantityCanDeferPageCalculations(): void
    {
        $pager = $this->pager();

        $pager->setItemQtt('42', false);

        $this->assertSame(42.0, $pager->getItemQtt());
        $this->assertNull($pager->getPageNum());
        $this->assertNull($pager->getPageQtt());
    }

    public function testOffsetUsesCurrentPageAndItemPerPage(): void
    {
        $pager = $this->pager();

        $pager->setPageNum(4);
        $pager->setItemPerPage(15);

        $this->assertSame(45.0, $pager->getOffset());
    }

    public function testSetItemQuantityUsesInjectedRequestAndBlockMetadata(): void
    {
        $request = new ServicePagerRequestDouble(4);
        $pager = $this->pager([
            'PAGE_REQUEST_KEY' => 'page',
            'PAGE_REQUEST_SRC' => 'G',
        ], new ServicePagerBlockDouble([
            'pager' => [
                'item_per_page' => 20,
            ],
        ]));

        $pager->setPagerDependencies(null, null, fn(): ServicePagerRequestDouble => $request);
        $pager->setItemQtt(95);

        $this->assertSame(4.0, $pager->getPageNum());
        $this->assertSame(20.0, $pager->getItemPerPage());
        $this->assertSame(5.0, $pager->getPageQtt());
        $this->assertSame(['page', 'G', 1], $request->lastGet);
    }

    public function testPageUriUsesInjectedTabFactory(): void
    {
        $tab = new ServicePagerTabDouble('/page/3');
        $pager = $this->pager([
            'PAGE_REQUEST_KEY' => 'p',
            'PAGING_BY' => 'GET',
        ], new ServicePagerBlockDouble());

        $pager->setPagerDependencies(null, fn(): ServicePagerTabDouble => $tab);

        $this->assertSame('/page/3', $pager->getPageUri(3, false, 'sid', 'https'));
        $this->assertSame([
            'exclude' => [
                'A' => ['p'],
                'G' => ['p'],
            ],
            'include' => [
                'G' => [
                    'p' => 3,
                ],
            ],
        ], $tab->modifier);
        $this->assertSame([false, 'sid', 'https'], $tab->arguments);
    }

    public function testSetItemQuantityRequiresInjectedRequestFactory(): void
    {
        $pager = $this->pager();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Request service is not configured for pager service.');

        $pager->setItemQtt(10);
    }

    public function testPageUriRequiresInjectedTabFactory(): void
    {
        $pager = $this->pager();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Tab service is not configured for pager service.');

        $pager->getPageUri(2);
    }

    public function testItemsByKeyRequiresInjectedEntityFactoryForStringEntity(): void
    {
        $request = new ServicePagerRequestDouble(1);
        $pager = $this->pager([], new ServicePagerBlockDouble([
            'pager' => [
                'item_per_page' => 10,
            ],
        ]));
        $pager->setPagerDependencies(null, null, fn(): ServicePagerRequestDouble => $request);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Entity service is not configured for pager service.');

        $pager->getItemsByKey('article');
    }

    public function testItemsByParamRequiresEntityKeyThroughInjectedServiceExceptionFactory(): void
    {
        $runtime = new ServicePagerRuntimeDouble();
        $pager = $this->pager(runtime: $runtime);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Entity key is not set.');

        try {
            $pager->getItemsByParam();
        } finally {
            $this->assertSame([
                ['\fan\project\exception\service\fatal', $pager, 'Entity key is not set.', E_USER_ERROR, null],
            ], $runtime->serviceExceptionFactoryCalls);
        }
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseFunctionAliases();

        $runtime = new ServicePagerRuntimeDouble();
        $config = new ServicePagerConfigDouble(['DEFAULT_ITEM_PER_PAGE' => 15]);
        $configurator = new ServicePagerConfiguratorDouble($config);
        $cacheFactoryCalls = [];
        $block = new ServicePagerBlockDouble();

        $pager = new ServicePagerConstructorProbe(
            $block,
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new ServicePagerRequestDouble(1),
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            }
        );

        $this->assertSame([ServicePagerConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$pager], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServicePagerConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame(15, $pager->getItemPerPage());
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testSourceUsesInjectedFactoriesInsteadOfContainerLookup(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('setPagerDependencies', $source);
        $this->assertStringNotContainsString('getContainerService(', $source);
        $this->assertStringNotContainsString('containerService(', $source);
    }

    private function pager(array $config = [], ?ServicePagerBlockDouble $block = null, ?object $runtime = null): pager
    {
        $pager = (new ReflectionClass(pager::class))->newInstanceWithoutConstructor();
        $property = new ReflectionProperty(service::class, 'config');
        $property->setValue($pager, new ServicePagerConfigDouble($config));
        $property = new ReflectionProperty(pager::class, 'block');
        $property->setValue($pager, $block ?? new ServicePagerBlockDouble());
        if ($runtime !== null) {
            $pager->setServiceDependencies(serviceBootstrapRuntime: $runtime);
        }

        return $pager;
    }

    private function ensureBaseFunctionAliases(): void
    {
        if (!function_exists('get_class_name')) {
            eval('function get_class_name(string|object $object): ?string { if (is_object($object)) { $object = get_class($object); } $parts = explode("\\\\\\\\", $object); return end($parts); }');
        }
        if (!function_exists('fan\core\base\get_class_name')) {
            eval('namespace fan\core\base { function get_class_name(string|object $object): ?string { return \get_class_name($object); } }');
        }
    }
}

final class ServicePagerConstructorProbe extends pager
{
    public function __construct(
        ServicePagerBlockDouble $block,
        ?callable $entityFactory,
        ?callable $tabFactory,
        ?callable $requestFactory,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory
    )
    {
        parent::__construct(
            $block,
            $entityFactory,
            $tabFactory,
            $requestFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory
        );
    }
}

final class ServicePagerRuntimeDouble
{
    public ServicePagerInitializerDouble $initializer;
    public array $serviceExceptionFactoryCalls = [];

    public function __construct()
    {
        $this->initializer = new ServicePagerInitializerDouble();
    }

    public function getInitializer(): ServicePagerInitializerDouble
    {
        return $this->initializer;
    }

    public function serviceExceptionFactory(): callable
    {
        return function (
            string $exceptionClass,
            service $service,
            string $message,
            int $code = E_USER_ERROR,
            ?\Throwable $previous = null
        ): \Throwable {
            $this->serviceExceptionFactoryCalls[] = [$exceptionClass, $service, $message, $code, $previous];

            return new \RuntimeException($message, $code, $previous);
        };
    }
}

final class ServicePagerInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServicePagerConfiguratorDouble
{
    public array $getServiceConfigCalls = [];
    public array $resetCalls = [];

    public function __construct(private object $config)
    {
    }

    public function getServiceConfig(object $service): object
    {
        $this->getServiceConfigCalls[] = $service;

        return $this->config;
    }

    public function reset(string $className, string $key): void
    {
        $this->resetCalls[] = [$className, $key];
    }
}

final class ServicePagerBlockDouble extends base
{
    public function __construct(private array $metaData = [])
    {
    }

    public function getBlockName(): string
    {
        return 'service_pager_test_block';
    }

    public function getMeta(string|array|null $key = null, mixed $default = null, bool $convToArray = false): mixed
    {
        if ($key === null) {
            return $this->metaData;
        }
        if (is_array($key)) {
            $value = $this->metaData;
            foreach ($key as $part) {
                if (!is_array($value) || !array_key_exists($part, $value)) {
                    return null;
                }
                $value = $value[$part];
            }

            return $value;
        }

        return $this->metaData[$key] ?? null;
    }
}

final class ServicePagerRequestDouble
{
    public ?array $lastGet = null;

    public function __construct(private int $page)
    {
    }

    public function get(string $key, string $source, mixed $default = null): int
    {
        $this->lastGet = [$key, $source, $default];

        return $this->page;
    }
}

final class ServicePagerTabDouble
{
    public ?array $modifier = null;
    public ?array $arguments = null;

    public function __construct(private string $uri)
    {
    }

    public function getModifiedCurrentURI(array $modifier, mixed $addExt = true, mixed $addSid = null, mixed $protocol = null): string
    {
        $this->modifier = $modifier;
        $this->arguments = [$addExt, $addSid, $protocol];

        return $this->uri;
    }
}

final class ServicePagerConfigDouble
{
    public function __construct(private array $data)
    {
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}
