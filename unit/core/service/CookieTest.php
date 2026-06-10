<?php

declare(strict_types=1);

use fan\core\adapter\safe_serializer;
use fan\core\service\cookie;
use fan\core\service\cookie_state;
use FanTest\core\SourceFileContractTestCase;
use fan\core\adapter\warning_capture;


class ServiceCookieTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/cookie.php';

    private ?cookie_state $cookieState = null;

    protected function tearDown(): void
    {
        $this->cookieState = null;
    }

    public function testGetReturnsDecodedJsonCookieValue(): void
    {
        $this->setCookieData([
            'prefs' => safe_serializer::encodeJson(['theme' => 'dark', 'compact' => true]),
        ]);

        $this->assertSame(['theme' => 'dark', 'compact' => true], $this->cookie()->get('prefs'));
    }

    public function testGetReturnsDefaultForMissingCookie(): void
    {
        $this->setCookieData([]);

        $this->assertSame('fallback', $this->cookie()->get('missing', 'fallback'));
    }

    public function testGetAllDecodesEachCookie(): void
    {
        $this->setCookieData([
            'json' => safe_serializer::encodeJson(['ok' => true]),
            'legacy' => safe_serializer::encodePhpSnapshot(['mode' => 'old']),
        ]);

        $this->assertSame([
            'json' => ['ok' => true],
            'legacy' => ['mode' => 'old'],
        ], $this->cookie()->getAll());
    }

    public function testRawCookieValueReturnsOriginalValueWithoutDecodeError(): void
    {
        $error = new ServiceCookieErrorDouble();

        $cookie = $this->cookie($error);
        $this->setCookieData(['broken' => 'not serialized']);

        $this->assertSame('not serialized', $cookie->get('broken', 'fallback'));
        $this->assertSame([], $error->messages);
    }

    public function testMalformedLegacyPayloadReturnsOriginalValueAndLogsDecodeError(): void
    {
        $error = new ServiceCookieErrorDouble();

        $cookie = $this->cookie($error);
        $this->setCookieData(['broken' => 'a:1:{broken']);

        $this->assertSame('a:1:{broken', $cookie->get('broken', 'fallback'));
        $this->assertSame([
            ['unserialize(): Error at offset 5 of 11 bytes', 'Cookie JSON decode error', '', true, false],
        ], $error->messages);
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseFunctionAliases();

        $input = new ServiceCookieInputDouble([
            'prefs' => safe_serializer::encodeJson(['theme' => 'dark']),
        ]);
        $runtime = new ServiceCookieRuntimeDouble();
        $config = new ServiceCookieConfigDouble();
        $configurator = new ServiceCookieConfiguratorDouble($config);
        $state = new cookie_state();
        $cookieWriter = new ServiceCookieWriterDouble();
        $cacheFactoryCalls = [];

        $cookie = new ServiceCookieConstructorProbe(
            '/app',
            'example.test',
            true,
            $input,
            static fn(): object => new ServiceCookieErrorDouble(),
            static fn(mixed $value): string => safe_serializer::encodeJson($value),
            static fn(
                string $payload,
                mixed $default = null,
                ?callable $onError = null,
                bool $returnOriginalOnLegacyFailure = false
            ): mixed => safe_serializer::decodeExternalPayload(
                $payload,
                $default,
                $onError,
                $returnOriginalOnLegacyFailure,
                new warning_capture()
            ),
            $this->defaultCookieChecker(),
            $cookieWriter,
            $state,
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            }
        );

        $this->assertSame([], $runtime->initializer->serviceParams);
        $this->assertSame([$cookie], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceCookieConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame(['theme' => 'dark'], $cookie->get('prefs'));
        $this->assertSame([], $cacheFactoryCalls);
        $this->assertSame([], $cookieWriter->writes);
    }

    public function testConstructorRequiresInjectedState(): void
    {
        $this->ensureBaseFunctionAliases();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cookie state is not configured for cookie service.');

        new cookie(
            '/app',
            'example.test',
            true,
            new ServiceCookieInputDouble(),
            static fn(): object => new ServiceCookieErrorDouble(),
            $this->defaultCookieEncoder(),
            $this->defaultCookieDecoder(),
            $this->defaultCookieChecker(),
            new ServiceCookieWriterDouble(),
            null,
            new ServiceCookieRuntimeDouble(),
            new ServiceCookieConfiguratorDouble(new ServiceCookieConfigDouble()),
            static fn(string $type): object => (object)['type' => $type]
        );
    }

    public function testSetByDateUsesInjectedServiceExceptionFactoryForEmptyDate(): void
    {
        $cookie = $this->cookie();
        $factoryCalls = [];
        $cookie->setServiceDependencies(
            serviceExceptionFactory: static function (
                string $exceptionClass,
                object $service,
                string $message,
                int $code,
                ?Throwable $previous
            ) use (&$factoryCalls): Throwable {
                $factoryCalls[] = [$exceptionClass, $service, $message, $code, $previous];

                return new RuntimeException($message, $code, $previous);
            }
        );

        try {
            $cookie->setByDate('prefs', 'value', '   ');
            $this->fail('Expected injected service exception factory to create the empty date failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Date isn\'t set', $exception->getMessage());
        }

        $this->assertCount(1, $factoryCalls);
        $this->assertSame('\fan\project\exception\service\fatal', $factoryCalls[0][0]);
        $this->assertSame($cookie, $factoryCalls[0][1]);
        $this->assertSame('Date isn\'t set', $factoryCalls[0][2]);
        $this->assertSame(E_USER_ERROR, $factoryCalls[0][3]);
        $this->assertNull($factoryCalls[0][4]);
    }

    public function testUsesInjectedCookieDecoder(): void
    {
        $decoderCalls = [];
        $cookie = $this->cookie(
            decoder: static function (
                string $payload,
                mixed $default = null,
                ?callable $onError = null,
                bool $returnOriginalOnLegacyFailure = false
            ) use (&$decoderCalls): string {
                $decoderCalls[] = [$payload, $default, $returnOriginalOnLegacyFailure, $onError !== null];

                return 'decoded:' . $payload;
            }
        );
        $this->setCookieData(['token' => safe_serializer::encodeJson('raw')]);

        $this->assertSame('decoded:' . safe_serializer::encodeJson('raw'), $cookie->get('token', 'fallback'));
        $this->assertSame([[safe_serializer::encodeJson('raw'), 'fallback', true, true]], $decoderCalls);
    }

    public function testUsesInjectedCookieEncoder(): void
    {
        $input = new ServiceCookieInputDouble([], ['HTTPS' => 'on']);
        $state = new cookie_state();
        $encoded = [];
        $cookieWriter = new ServiceCookieWriterDouble();
        $cookie = new ServiceCookieConstructorProbe(
            '/app',
            'example.test',
            true,
            $input,
            static fn(): object => new ServiceCookieErrorDouble(),
            static function (mixed $value) use (&$encoded): string {
                $encoded[] = $value;

                return 'encoded-cookie';
            },
            $this->defaultCookieDecoder(),
            $this->defaultCookieChecker(),
            $cookieWriter,
            $state,
            new ServiceCookieRuntimeDouble(),
            new ServiceCookieConfiguratorDouble(new ServiceCookieConfigDouble()),
            static fn(string $type): object => (object)['type' => $type]
        );

        $this->assertTrue($cookie->set('prefs', ['compact' => true]));
        $this->assertSame([['compact' => true]], $encoded);
        $this->assertSame('encoded-cookie', $state->getData('prefs'));
        $this->assertSame([
            ['prefs', 'encoded-cookie', 0, '/app', 'example.test', true, false],
        ], $cookieWriter->writes);
    }

    public function testMissingCookieEncoderFailsAtUseTime(): void
    {
        $cookie = $this->cookieWithDefaultValueCodecs();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cookie value encoder is not configured for cookie service.');

        $cookie->set('prefs', ['compact' => true]);
    }

    public function testMissingCookieDecoderFailsAtUseTime(): void
    {
        $cookie = $this->cookieWithDefaultValueCodecs();
        $this->setCookieData(['prefs' => safe_serializer::encodeJson('raw')]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cookie value decoder is not configured for cookie service.');

        $cookie->get('prefs');
    }

    private function cookie(
        ?ServiceCookieErrorDouble $error = null,
        ?callable $encoder = null,
        ?callable $decoder = null
    ): cookie
    {
        $cookie = (new ReflectionClass(cookie::class))->newInstanceWithoutConstructor();
        $property = new ReflectionProperty(cookie::class, 'errorFactory');
        $property->setValue($cookie, $error === null ? null : fn(): ServiceCookieErrorDouble => $error);
        $property = new ReflectionProperty(cookie::class, 'state');
        $this->cookieState ??= new cookie_state();
        $property->setValue($cookie, $this->cookieState);
        $property = new ReflectionProperty(cookie::class, 'cookieValueEncoder');
        $property->setValue($cookie, Closure::fromCallable($encoder ?? $this->defaultCookieEncoder()));
        $property = new ReflectionProperty(cookie::class, 'cookieValueDecoder');
        $property->setValue($cookie, Closure::fromCallable($decoder ?? $this->defaultCookieDecoder()));
        $property = new ReflectionProperty(cookie::class, 'cookieValueChecker');
        $property->setValue($cookie, Closure::fromCallable($this->defaultCookieChecker()));
        $property = new ReflectionProperty(cookie::class, 'cookieWriter');
        $property->setValue($cookie, new ServiceCookieWriterDouble());

        return $cookie;
    }

    private function cookieWithDefaultValueCodecs(): cookie
    {
        $this->ensureBaseFunctionAliases();

        return new ServiceCookieConstructorProbe(
            '/app',
            'example.test',
            true,
            new ServiceCookieInputDouble(),
            static fn(): object => new ServiceCookieErrorDouble(),
            null,
            null,
            $this->defaultCookieChecker(),
            new ServiceCookieWriterDouble(),
            $this->cookieState ??= new cookie_state(),
            new ServiceCookieRuntimeDouble(),
            new ServiceCookieConfiguratorDouble(new ServiceCookieConfigDouble()),
            static fn(string $type): object => (object)['type' => $type]
        );
    }

    private function defaultCookieEncoder(): callable
    {
        return static fn(mixed $value): string => safe_serializer::encodeJson($value);
    }

    private function defaultCookieDecoder(): callable
    {
        return static fn(
            string $payload,
            mixed $default = null,
            ?callable $onError = null,
            bool $returnOriginalOnLegacyFailure = false
        ): mixed => safe_serializer::decodeExternalPayload(
            $payload,
            $default,
            $onError,
            $returnOriginalOnLegacyFailure,
            new warning_capture()
        );
    }

    private function defaultCookieChecker(): callable
    {
        return static fn(string $payload): bool => safe_serializer::isJsonPayload($payload) || safe_serializer::looksLikeLegacyPhpPayload($payload);
    }

    private function setCookieData(?array $data): void
    {
        $this->cookieState ??= new cookie_state();
        $this->cookieState->clear();
        $this->cookieState->initializeData($data ?? []);
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

final class ServiceCookieConstructorProbe extends cookie
{
    public function __construct(
        mixed $path,
        mixed $domain,
        bool $secure,
        ?object $input,
        ?callable $errorFactory,
        ?callable $cookieValueEncoder,
        ?callable $cookieValueDecoder,
        ?callable $cookieValueChecker,
        ?object $cookieWriter,
        ?object $state,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory
    )
    {
        parent::__construct(
            $path,
            $domain,
            $secure,
            $input,
            $errorFactory,
            $cookieValueEncoder,
            $cookieValueDecoder,
            $cookieValueChecker,
            $cookieWriter,
            $state,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory
        );
    }
}

final class ServiceCookieWriterDouble
{
    public array $writes = [];

    public function __construct(private bool $result = true)
    {
    }

    public function write(
        string $name,
        string $value,
        int $expires,
        string $path,
        string $domain,
        bool $secure,
        bool $httpOnly
    ): bool {
        $this->writes[] = [$name, $value, $expires, $path, $domain, $secure, $httpOnly];

        return $this->result;
    }
}

final class ServiceCookieInputDouble
{
    public function __construct(private array $cookie = [], private array $server = [])
    {
    }

    public function globalArray(string $name): array
    {
        return $name === '_COOKIE' ? $this->cookie : [];
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }
}

final class ServiceCookieConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}

final class ServiceCookieRuntimeDouble
{
    public ServiceCookieInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ServiceCookieInitializerDouble();
    }

    public function getInitializer(): ServiceCookieInitializerDouble
    {
        return $this->initializer;
    }

    public function classNameResolver(): callable
    {
        return static fn(object $object): string => get_class($object);
    }
}

final class ServiceCookieInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceCookieConfiguratorDouble
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

final class ServiceCookieErrorDouble
{
    public array $messages = [];

    public function logErrorMessage(
        string $message,
        string $title = '',
        string $note = '',
        bool $fixPosition = false,
        bool $debugBacktrace = true,
    ): void {
        $this->messages[] = [$message, $title, $note, $fixPosition, $debugBacktrace];
    }
}
