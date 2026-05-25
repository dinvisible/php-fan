<?php

declare(strict_types=1);

use FanTest\_core\SourceFileContractTestCase;
use fan\core\service\config\row;
use fan\core\service\matcher;
use fan\core\service\matcher\item;
use fan\core\service\matcher\item\base;
use fan\core\service\matcher\item\uri;


class GeneratedPendingServiceMatcherItemTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/matcher/item.php';

    public function testHandlerConfigRowsAreConvertedToArrays(): void
    {
        $item = $this->makeMatcherItem();
        $row = new row([
            'definer' => 'request',
            'regexp' => '/^\\/file/',
            'class' => 'file',
            'method' => 'show',
        ]);

        $this->assertSame(
            [
                'definer' => 'request',
                'regexp' => '/^\\/file/',
                'class' => 'file',
                'method' => 'show',
            ],
            $item->readConfig($row)
        );
    }

    public function testInvalidHandlerConfigTypeIsRejected(): void
    {
        $item = $this->makeMatcherItem();

        $this->expectException(\UnexpectedValueException::class);
        $item->readConfig(new \stdClass());
    }

    public function testRequestedUriUsesInjectedServerInputForFirstItem(): void
    {
        $item = $this->makeMatcherItem(new ServiceMatcherItemInputDouble([
            'HTTPS' => 'on',
            'HTTP_HOST' => 'example.test',
        ]));

        $uri = $item->parseUri('/docs?page=1', '');

        $this->assertSame('https', $uri['scheme']);
        $this->assertSame('example.test', $uri['host']);
        $this->assertSame('https://example.test/docs?page=1', $uri['full']);
    }

    public function testRequestedCliUsesInjectedArgv(): void
    {
        $item = $this->makeMatcherItem(new ServiceMatcherItemInputDouble(argv: ['command.php', '--dry-run']));

        $this->assertSame([
            'file' => 'command.php',
            'path' => '/tools',
            'argv' => ['command.php', '--dry-run'],
        ], $item->parseCli('command.php', '/tools'));
    }

    public function testConstructorUsesInjectedComponentFactory(): void
    {
        require_once dirname(__DIR__, 4) . '/_core/service/matcher/item.php';

        $calls = [];
        $item = new item(
            3,
            new ServiceMatcherItemInputDouble(),
            new ServiceMatcherItemRuntimeDouble(),
            null,
            null,
            new ServiceMatcherItemRouteFileStorageDouble(),
            static function (string $className, item $item, string $key) use (&$calls): object {
                $component = new ServiceMatcherItemComponentDouble($key, $item);
                $calls[] = [$className, $item, $key, $component];

                return $component;
            }
        );

        $this->assertSame(3, $item->getIndex());
        $data = (new ReflectionProperty(item::class, 'data'))->getValue($item);
        foreach (['source', 'uri', 'handler', 'parsed'] as $position => $key) {
            $this->assertSame('\fan\project\service\matcher\item\\' . $key, $calls[$position][0]);
            $this->assertSame($item, $calls[$position][1]);
            $this->assertSame($key, $calls[$position][2]);
            $this->assertSame($calls[$position][3], $data[$key]);
        }
    }

    public function testInvalidMatcherItemKeyUsesInjectedServiceExceptionFactory(): void
    {
        $calls = [];
        $factory = static function (
            string $className,
            object $service,
            string $message,
            int $code,
            ?Throwable $previous
        ) use (&$calls): Throwable {
            $calls[] = [$className, $service, $message, $code, $previous];

            return new RuntimeException('factory: ' . $message);
        };
        $facade = new ServiceMatcherItemFacadeDouble();
        $item = $this->makeRealMatcherItem(serviceExceptionFactory: $factory);
        $item->setFacade($facade);

        try {
            $item['unknown'];
            $this->fail('Expected injected service exception factory to provide the throwable.');
        } catch (RuntimeException $exception) {
            $this->assertSame('factory: Invalid key "unknown" while accessing the item of matcher.', $exception->getMessage());
        }

        $this->assertSame('\fan\project\exception\service\fatal', $calls[0][0]);
        $this->assertSame($facade, $calls[0][1]);
        $this->assertSame('Invalid key "unknown" while accessing the item of matcher.', $calls[0][2]);
        $this->assertSame(E_USER_ERROR, $calls[0][3]);
        $this->assertNull($calls[0][4]);
    }

    public function testHostSwitchingUsesInjectedServiceExceptionFactory(): void
    {
        $calls = [];
        $factory = static function (
            string $className,
            object $service,
            string $message,
            int $code,
            ?Throwable $previous
        ) use (&$calls): Throwable {
            $calls[] = [$className, $service, $message, $code, $previous];

            return new RuntimeException('factory: ' . $message);
        };
        $facade = new ServiceMatcherItemFacadeDouble();
        $item = $this->makeRealMatcherItem(1, serviceExceptionFactory: $factory);
        $item->setFacade($facade);

        try {
            $item->parseUri('/docs', 'other.test');
            $this->fail('Expected injected service exception factory to provide the throwable.');
        } catch (RuntimeException $exception) {
            $this->assertSame('factory: Host switching isn\'t allowed there', $exception->getMessage());
        }

        $this->assertSame('\fan\project\exception\service\fatal', $calls[0][0]);
        $this->assertSame($facade, $calls[0][1]);
        $this->assertSame('Host switching isn\'t allowed there', $calls[0][2]);
    }

    public function testInvalidMatcherItemKeyWithoutFacadeUsesInjectedFatalExceptionFactory(): void
    {
        $calls = [];
        $factory = static function (
            string $message,
            int $code,
            ?Throwable $previous,
            ?object $input
        ) use (&$calls): Throwable {
            $calls[] = [$message, $code, $previous, $input];

            return new RuntimeException('fatal: ' . $message, $code, $previous);
        };
        $input = new ServiceMatcherItemInputDouble();
        $item = $this->makeRealMatcherItem(input: $input, fatalExceptionFactory: $factory);

        try {
            $item['unknown'];
            $this->fail('Expected injected fatal exception factory to provide the throwable.');
        } catch (RuntimeException $exception) {
            $this->assertSame('fatal: Invalid key "unknown" while accessing the item of matcher.', $exception->getMessage());
        }

        $this->assertSame('Invalid key "unknown" while accessing the item of matcher.', $calls[0][0]);
        $this->assertSame(E_USER_ERROR, $calls[0][1]);
        $this->assertNull($calls[0][2]);
        $this->assertSame($input, $calls[0][3]);
    }

    public function testInvalidMatcherItemComponentKeyWithoutFacadeUsesInjectedFatalExceptionFactory(): void
    {
        require_once dirname(__DIR__, 4) . '/_core/service/matcher/item/base.php';

        $calls = [];
        $factory = static function (
            string $message,
            int $code,
            ?Throwable $previous,
            ?object $input
        ) use (&$calls): Throwable {
            $calls[] = [$message, $code, $previous, $input];

            return new RuntimeException('fatal: ' . $message, $code, $previous);
        };
        $input = new ServiceMatcherItemInputDouble();
        $item = $this->makeRealMatcherItem(input: $input, fatalExceptionFactory: $factory);
        $component = new ServiceMatcherItemBaseProbe($item);

        try {
            $component['unknown'];
            $this->fail('Expected injected fatal exception factory to provide the throwable.');
        } catch (RuntimeException $exception) {
            $this->assertSame('fatal: Invalid key "unknown" while accessing the item property of matcher.', $exception->getMessage());
        }

        $this->assertSame('Invalid key "unknown" while accessing the item property of matcher.', $calls[0][0]);
        $this->assertSame(E_USER_ERROR, $calls[0][1]);
        $this->assertNull($calls[0][2]);
        $this->assertSame($input, $calls[0][3]);
    }

    public function testSourceNoLongerConstructsComponentsDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('$componentFactory', $source);
        $this->assertStringContainsString('createServiceFatalException(', $source);
        $this->assertStringContainsString('createMatcherFatalException(', $source);
        $this->assertStringContainsString('$this->routeFileStorage()->isFile($path . \'/\' . $v . \'.php\')', $source);
        $this->assertStringContainsString('$this->routeFileStorage()->isDirectory($path . \'/\' . $v)', $source);
        $this->assertStringNotContainsString('new $class($this)', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('new \fan\project\exception\fatal', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_file|is_dir)\s*\(/',
            $source
        );
    }

    public function testMatcherItemBaseUsesInjectedFatalExceptionFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/_core/service/matcher/item/base.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('$this->item->createMatcherFatalException($errMsg)', $source);
        $this->assertStringNotContainsString('new \fan\project\exception\fatal', $source);
    }

    private function makeMatcherItem(?ServiceMatcherItemInputDouble $input = null): object
    {
        require_once dirname(__DIR__, 4) . '/unit/mock/_core/base/DataFunctions.php';
        require_once dirname(__DIR__, 4) . '/_core/base/data.php';
        require_once dirname(__DIR__, 4) . '/_core/service/config/row.php';
        require_once dirname(__DIR__, 4) . '/_core/service/matcher/item.php';

        $item = new class extends item {
            public function __construct()
            {
            }

            public function readConfig(mixed $data): array
            {
                return $this->readHandlerConfig($data);
            }

            public function parseUri(string $request, string $host): array
            {
                $this->index = 0;

                return $this->_parseRequestedUri($request, $host);
            }

            public function parseCli(string $file, string $path): array
            {
                return $this->_parseRequestedCli($file, $path);
            }
        };
        $item->setDependencies($input ?? new ServiceMatcherItemInputDouble(), new ServiceMatcherItemRuntimeDouble(), null, null, new ServiceMatcherItemRouteFileStorageDouble());

        return $item;
    }

    private function makeRealMatcherItem(
        int $index = 0,
        ?ServiceMatcherItemInputDouble $input = null,
        ?callable $serviceExceptionFactory = null,
        ?callable $fatalExceptionFactory = null
    ): object
    {
        require_once dirname(__DIR__, 4) . '/_core/service/matcher/item.php';

        return new class(
            $index,
            $input ?? new ServiceMatcherItemInputDouble([
                'HTTPS' => 'off',
                'HTTP_HOST' => 'example.test',
            ]),
            new ServiceMatcherItemRuntimeDouble(),
            null,
            null,
            new ServiceMatcherItemRouteFileStorageDouble(),
            static fn(): object => new ServiceMatcherItemComponentDouble('component', new stdClass()),
            $serviceExceptionFactory,
            $fatalExceptionFactory
        ) extends item {
            public function parseUri(string $request, string $host): array
            {
                return $this->_parseRequestedUri($request, $host);
            }
        };
    }
}

final class ServiceMatcherItemRouteFileStorageDouble
{
    public function isFile(string $path): bool
    {
        return str_ends_with($path, '.php');
    }

    public function isDirectory(string $path): bool
    {
        return !str_ends_with($path, '.php');
    }
}

final class ServiceMatcherItemRuntimeDouble
{
    public function isCli(): bool
    {
        return false;
    }
}

final class ServiceMatcherItemInputDouble
{
    public function __construct(private array $server = [], private array $argv = [])
    {
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function argv(): array
    {
        return $this->argv;
    }
}

final class ServiceMatcherItemComponentDouble
{
    public function __construct(public string $key, public object $item)
    {
    }

    public function setFacade(object $facade): static
    {
        return $this;
    }
}

final class ServiceMatcherItemBaseProbe extends base
{
}

final class ServiceMatcherItemFacadeDouble extends matcher
{
    public function __construct()
    {
    }

    public function getUri(int $number): uri
    {
        $uri = new uri(new class extends item {
            public function __construct()
            {
            }
        });
        foreach ([
            'scheme' => 'https',
            'user' => null,
            'pass' => null,
            'fragment' => null,
            'host' => 'example.test',
            'query' => null,
        ] as $key => $value) {
            $uri[$key] = $value;
        }

        return $uri;
    }

    public function getConfig($key = null, $default = null): mixed
    {
        return $default;
    }
}
