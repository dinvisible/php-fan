<?php

declare(strict_types=1);

use fan\core\service\session;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\base\service;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;
use fan\core\service\session_state;


if (!function_exists('array_val')) {
    function array_val(array|\ArrayAccess $arr, mixed $key, mixed $default = null): mixed
    {
        if ($key === null) {
            return $default;
        }

        return isset($arr[$key]) ? $arr[$key] : $default;
    }
}

if (!function_exists('array_get_element')) {
    function &array_get_element(&$data, $path, $create = false): mixed
    {
        $null = null;
        if (is_scalar($path)) {
            if (!is_array($data) || !array_key_exists($path, $data)) {
                return $null;
            }
            return $data[$path];
        }
        $current =& $data;
        foreach ($path as $key) {
            if (!is_array($current)) {
                if (!$create) {
                    return $null;
                }
                $current = [];
            }
            if (!array_key_exists($key, $current)) {
                if (!$create) {
                    return $null;
                }
                $current[$key] = null;
            }
            $current =& $current[$key];
        }

        return $current;
    }
}

class ServiceSessionTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/session.php';

    protected function tearDown(): void
    {
    }

    public function testGetSetRemoveAndRemoveAllWorkThroughEngineNamespace(): void
    {
        $engine = new ServiceSessionEngineDouble();
        $session = $this->session($engine, 'user', 'app');

        $this->assertTrue($session->set('id', 7));
        $this->assertSame(7, $session->get('id'));
        $this->assertSame('fallback', $session->get('missing', 'fallback'));

        $link =& $session->getByLink(['profile', 'name'], 'Ada');
        $link = 'Grace';
        $this->assertSame('Grace', $session->get(['profile', 'name']));

        $this->assertTrue($session->remove('id'));
        $this->assertSame('fallback', $session->get('id', 'fallback'));

        $this->assertTrue($session->removeAll());
        $this->assertNull($session->getAll());
    }

    public function testSessionIdNameExpiredAndBufferHelpers(): void
    {
        $engine = new ServiceSessionEngineDouble('SID', 'abcdef12345678901');
        $session = $this->session($engine, 'user', 'app');
        $session->getState()->setByCookie(true);
        $session->getState()->setExpired(true);

        $this->assertSame('abcdef12345678901', $session->getSessionId());
        $this->assertSame('SID', $session->getSessionName());
        $this->assertTrue($session->isByCookies());
        $this->assertSame('app', $session->getGroup());
        $this->assertSame('user', $session->getNameSpace());
        $this->assertTrue($session->isExpired());

        $this->assertSame($session, $session->setBufferData('flash', 'message'));
        $this->assertSame('message', $session->getBufferData('flash'));
        $this->assertSame('fallback', $session->getBufferData('missing', 'fallback'));
    }

    public function testSetSessionIdValidatesAndUpdatesEngine(): void
    {
        $engine = new ServiceSessionEngineDouble('SID', 'oldsessionidentifier');
        $session = $this->session($engine, 'user', 'app');

        $this->assertTrue($session->setSessionId('newsessionidentifier12345'));
        $this->assertSame('newsessionidentifier12345', $engine->sessionId);
        $this->assertSame([['SID', 'newsessionidentifier12345']], $session->cookies);

        $this->assertFalse($session->setSessionId('bad id!'));
    }

    public function testSetCookieUsesInjectedCookieFactory(): void
    {
        $cookieFactory = new ServiceSessionCookieFactoryDouble();
        $session = $this->session(new ServiceSessionEngineDouble('SID', 'oldsessionidentifier'), 'user', 'app');
        $session->setBaseConfig(new ServiceSessionConfigDouble([
            'COOKIE_DOMAIN' => 'example.test',
        ]));
        $session->setSessionDependencies(null, null, null, null, $cookieFactory);

        $this->assertSame($session, $session->exposeSetCookie('SID', 'newsessionidentifier12345'));
        $this->assertSame([['/', 'example.test']], $cookieFactory->calls);
        $this->assertSame([['SID', 'newsessionidentifier12345']], $cookieFactory->cookies[0]->setCalls);
    }

    public function testTimeoutUsesInjectedSessionAndDateFactories(): void
    {
        $timeSession = new ServiceSessionStorageDouble([
            'isKilled' => false,
            'reload' => '2026-05-31 10:00:00',
        ]);
        $sessionFactory = new ServiceSessionFactoryDouble([
            'time:session' => $timeSession,
        ]);
        $dateFactory = new ServiceSessionDateFactoryDouble(15);
        $session = $this->session(new ServiceSessionEngineDouble(), 'user', 'app');
        $session->setBaseConfig(new ServiceSessionConfigDouble([
            'KILL_BY_TIMEOUT' => true,
            'MAXLIFETIME' => 60,
        ]));
        $session->setSessionDependencies(null, null, $sessionFactory, $dateFactory);

        $this->assertTrue($session->exposeCheckSessionTimeout());
        $this->assertSame([['time', 'session']], $sessionFactory->calls);
        $this->assertCount(1, $dateFactory->dates);
        $this->assertFalse($timeSession->data['isKilled']);
        $this->assertArrayHasKey('reload', $timeSession->data);
    }

    public function testCompareSystemUsesInjectedArrayValueReader(): void
    {
        $dataSession = new ServiceSessionStorageDouble([
            'is_fill' => true,
            'param' => [
                'HTTP_USER_AGENT' => 'old-agent',
            ],
        ]);
        $sessionFactory = new ServiceSessionFactoryDouble([
            'data:session' => $dataSession,
        ]);
        $session = $this->session(new ServiceSessionEngineDouble(), 'user', 'app');
        $session->setBaseConfig(new ServiceSessionConfigDouble([
            'CHECK_SYSTEM' => ['HTTP_USER_AGENT'],
        ]));
        $session->getState()->setRequestService(new ServiceSessionSystemRequestDouble([
            'HTTP_USER_AGENT' => 'new-agent',
        ]));
        $session->setSessionDependencies(null, null, $sessionFactory);
        $readerCalls = [];
        $session->setArrayValueReader(
            static function (array|\ArrayAccess $array, mixed $key, mixed $default = null) use (&$readerCalls): mixed {
                $readerCalls[] = [$array, $key, $default];

                return $array[$key] ?? $default;
            }
        );

        $this->assertSame([
            'key' => 'HTTP_USER_AGENT',
            'old' => 'old-agent',
            'new' => 'new-agent',
        ], $session->exposeCompareSystem());
        $this->assertSame([['data', 'session']], $sessionFactory->calls);
        $this->assertCount(4, $readerCalls);
        $this->assertSame('new-agent', $dataSession->data['param']['HTTP_USER_AGENT']);
        $this->assertTrue($dataSession->data['is_fill']);
    }

    public function testSessionServiceNoLongerFallsBackToServiceLocator(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('getContainerService(', $source);
        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('private static array $instances', $source);
        $this->assertStringNotContainsString('private static ?object $engine', $source);
        $this->assertStringContainsString('$this->phpRuntimeSettings()->set(', $source);
        $this->assertStringContainsString('$this->nativeSession()', $source);
        $this->assertStringContainsString('$arrayValueReader = $this->arrayValueReader();', $source);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertStringNotContainsString('defaultPhpRuntimeSettings', $source);
        $this->assertStringNotContainsString('new \\fan\\core\\runtime\\php_runtime_settings()', $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/runtime/php_runtime_settings.php';", $source);
        $this->assertStringNotContainsString('adodbEnvironment', $source);
        $this->assertStringContainsString('$this->pearSessionSupportLoader', $source);
        $this->assertStringNotContainsString('new $class(', $source);
        $this->assertStringNotContainsString('ini_set(', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:session_set_cookie_params|session_cache_limiter|session_name)\s*\(/',
            $source
        );
    }

    public function testConstructorUsesInjectedStateAndBaseServiceDependencies(): void
    {
        $this->ensureBaseFunctionAliases();

        $runtime = new ServiceSessionRuntimeDouble();
        $settings = new ServiceSessionRuntimeSettingsDouble();
        $configurator = new ServiceSessionConfiguratorDouble(new ServiceSessionConfigDouble([
            'ENABLED' => false,
        ]));
        $state = self::sessionState();
        $cacheFactoryCalls = [];

        $session = new ServiceSessionConstructorProbe(
            'profile',
            'custom',
            null,
            new stdClass(),
            static fn(): object => new stdClass(),
            new stdClass(),
            new stdClass(),
            null,
            null,
            null,
            null,
            null,
            $state,
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            },
            $settings,
            new ServiceSessionServiceNativeSessionDouble(),
            static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default
        );

        $this->assertSame([ServiceSessionConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$session], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceSessionConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame($session, $state->getInstance('custom', 'profile'));
        $this->assertSame([], $cacheFactoryCalls);
        $this->assertSame([], $settings->sets);
    }

    public function testConstructorUsesInjectedSessionEngineFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $engine = new ServiceSessionEngineDouble('SID', 'factorysessionidentifier');
        $engineFactoryCalls = [];
        $request = new ServiceSessionRequestDouble();
        $input = new ServiceSessionRequestInputDouble();
        $databaseConfig = new stdClass();
        $errorFactory = static fn(): object => new stdClass();
        $pearLoader = new stdClass();
        $state = self::sessionState();
        $runtime = new ServiceSessionRuntimeDouble();
        $settings = new ServiceSessionRuntimeSettingsDouble();
        $nativeSession = new ServiceSessionServiceNativeSessionDouble();
        $configurator = new ServiceSessionConfiguratorDouble(new ServiceSessionConfigDouble([
            'ENABLED' => true,
            'ENGINE' => 'inbuilt',
            'MAXLIFETIME' => 0,
            'COOKIE_SECURE' => false,
            'COOKIE_HTTPONLY' => true,
            'COOKIE_DOMAIN' => '',
            'CACHE_LIMITER' => '',
            'SESSION_NAME' => 'SID',
            'IS_GET_PRIORITY' => false,
            'CHECK_SYSTEM' => [],
            'KILL_BY_TIMEOUT' => false,
        ]));

        $session = new ServiceSessionEngineFactoryProbe(
            'profile',
            'custom',
            $databaseConfig,
            $input,
            $errorFactory,
            $request,
            new ServiceSessionLogDouble(),
            null,
            null,
            null,
            $pearLoader,
            static function (
                string $class,
                ?string $sid,
                object $config,
                ?object $databaseConfig,
                ?object $requestInput,
                mixed $errorFactory,
                ?object $requestService,
                ?object $pearSessionSupportLoader
            ) use (&$engineFactoryCalls, $engine): object {
                $engineFactoryCalls[] = [
                    $class,
                    $sid,
                    $config,
                    $databaseConfig,
                    $requestInput,
                    $errorFactory,
                    $requestService,
                    $pearSessionSupportLoader,
                ];

                return $engine;
            },
            $state,
            $runtime,
            $configurator,
            static fn(string $type): object => (object)['type' => $type],
            $settings,
            $nativeSession
        );

        $this->assertSame($engine, $state->getEngine());
        $this->assertSame($session, $engine->facade);
        $this->assertSame([[
            '\\' . ServiceSessionEngineDouble::class,
            null,
            $configurator->config,
            $databaseConfig,
            $input,
            $errorFactory,
            $request,
            $pearLoader,
        ]], $engineFactoryCalls);
        $this->assertSame([
            ['session.cookie_secure', '0'],
            ['session.cookie_httponly', '1'],
        ], $settings->sets);
        $this->assertSame([
            ['setCookieParams', 0, '/', null],
            ['cacheLimiter', ''],
            ['name', 'SID'],
        ], $nativeSession->calls);
    }

    public function testDestroyClearsEngineAndCookieMode(): void
    {
        $engine = new ServiceSessionEngineDouble();
        $session = $this->session($engine, 'user', 'app');
        $session->getState()->setByCookie(true);

        $this->assertSame($session, $session->destroy());
        $this->assertSame(1, $engine->destroyCalls);
        $this->assertNull($session->getSessionId());
        $this->assertNull($session->isByCookies());
    }

    private function session(ServiceSessionEngineDouble $engine, string $nameSpace, string $group): ServiceSessionProbe
    {
        $session = new ServiceSessionProbe();
        $state = self::sessionState();
        $state->setEngine($engine);
        $session->setState($state);

        foreach (['nameSpace' => $nameSpace, 'group' => $group] as $propertyName => $value) {
            $property = new ReflectionProperty(session::class, $propertyName);
            $property->setValue($session, $value);
        }

        return $session;
    }

    private static function sessionState(): session_state
    {
        return new session_state(
            static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default
        );
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

final class ServiceSessionProbe extends session
{
    public array $cookies = [];

    public function __construct()
    {
    }

    public function setBaseConfig(object $config): void
    {
        $property = new ReflectionProperty(service::class, 'config');
        $property->setValue($this, $config);
    }

    public function setState(object $state): void
    {
        $property = new ReflectionProperty(session::class, 'sessionState');
        $property->setValue($this, $state);
    }

    public function getState(): session_state
    {
        $property = new ReflectionProperty(session::class, 'sessionState');

        return $property->getValue($this);
    }

    public function exposeSetCookie(string $var, string $val): static
    {
        return parent::_setCookie($var, $val);
    }

    public function exposeCheckSessionTimeout(): bool
    {
        return parent::_checkSessionTimeout();
    }

    public function exposeCompareSystem(): ?array
    {
        return parent::_compareSystem();
    }

    public function setArrayValueReader(callable $arrayValueReader): void
    {
        $property = new ReflectionProperty(service::class, 'arrayValueReader');
        $property->setValue($this, $arrayValueReader);
    }

    protected function _setCookie(string $var, string $val): static
    {
        $this->cookies[] = [$var, $val];

        return $this;
    }
}

final class ServiceSessionConstructorProbe extends session
{
    public function __construct(
        string $nameSpace,
        string $group,
        ?object $databaseConfig,
        ?object $requestInput,
        ?callable $errorFactory,
        ?object $requestService,
        ?object $logService,
        ?callable $sessionFactory,
        ?callable $dateFactory,
        ?callable $cookieFactory,
        ?object $pearSessionSupportLoader,
        ?callable $sessionEngineFactory,
        ?object $sessionState,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory,
        ?object $phpRuntimeSettings = null,
        ?object $nativeSession = null,
        ?callable $arrayValueReader = null
    )
    {
        parent::__construct(
            $nameSpace,
            $group,
            $databaseConfig,
            $requestInput,
            $errorFactory,
            $requestService,
            $logService,
            $sessionFactory,
            $dateFactory,
            $cookieFactory,
            $pearSessionSupportLoader,
            $sessionEngineFactory,
            $sessionState,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $phpRuntimeSettings,
            $nativeSession,
            $arrayValueReader
        );
    }
}

final class ServiceSessionEngineDouble
{
    public array $root = [];

    public int $destroyCalls = 0;

    public function __construct(public string $sessionName = 'SID', public string $sessionId = 'sessionidentifier123')
    {
    }

    public ?object $facade = null;

    public function setFacade(object $facade): void
    {
        $this->facade = $facade;
    }

    public function &getData(string $group, string $sesName): mixed
    {
        if (!isset($this->root[$group])) {
            $this->root[$group] = [];
        }
        if (!array_key_exists($sesName, $this->root[$group])) {
            $this->root[$group][$sesName] = [];
        }

        return $this->root[$group][$sesName];
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sid): void
    {
        $this->sessionId = $sid;
    }

    public function getSessionName(): string
    {
        return $this->sessionName;
    }

    public function destroy(): void
    {
        $this->destroyCalls++;
    }

    public function &getRoot(): array
    {
        return $this->root;
    }
}

final class ServiceSessionRuntimeDouble
{
    public ServiceSessionInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ServiceSessionInitializerDouble();
    }

    public function getInitializer(): ServiceSessionInitializerDouble
    {
        return $this->initializer;
    }

    private ?service_listener_state $baseServiceListenerState = null;

    private ?service_single_state $baseServiceSingleState = null;

    public function serviceListenerState(): service_listener_state
    {
        return $this->baseServiceListenerState ??= new service_listener_state();
    }

    public function serviceSingleState(): service_single_state
    {
        return $this->baseServiceSingleState ??= new service_single_state();
    }

    public function classNameResolver(): callable
    {
        return static function (object $object): string {
            $parts = explode('\\', get_class($object));

            return end($parts);
        };
    }

    public function arrayValueReader(): callable
    {
        return static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
    }
}

final class ServiceSessionInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceSessionConfiguratorDouble
{
    public array $getServiceConfigCalls = [];
    public array $resetCalls = [];

    public function __construct(public object $config)
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

final class ServiceSessionConfigDouble extends ArrayObject
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this;
        }

        return array_key_exists($key, $this->getArrayCopy()) ? $this[$key] : $default;
    }

    public function toArray(): array
    {
        return $this->getArrayCopy();
    }
}

final class ServiceSessionEngineFactoryProbe extends session
{
    protected function _getEngine($name, $object = true): mixed
    {
        return $object ? null : '\\' . ServiceSessionEngineDouble::class;
    }
}

final class ServiceSessionRequestDouble
{
    public array $removed = [];

    public function get(string $key, string $source = '', mixed $default = null): mixed
    {
        return $default;
    }

    public function getAll(string $source, array $default = []): array
    {
        return $default;
    }

    public function remove(string $key, string $source, bool $recursive = false): void
    {
        $this->removed[] = [$key, $source, $recursive];
    }
}

final class ServiceSessionSystemRequestDouble
{
    public function __construct(private array $server)
    {
    }

    public function getAll(string $source, array $default = []): array
    {
        return $source === 'S' ? $this->server : $default;
    }
}

final class ServiceSessionRequestInputDouble
{
    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $default;
    }
}

final class ServiceSessionLogDouble
{
    public array $messages = [];

    public function logMessage(...$arguments): void
    {
        $this->messages[] = $arguments;
    }
}

final class ServiceSessionCookieFactoryDouble
{
    public array $calls = [];
    public array $cookies = [];

    public function __invoke(mixed $path, mixed $domain): ServiceSessionCookieDouble
    {
        $this->calls[] = [$path, $domain];
        $cookie = new ServiceSessionCookieDouble();
        $this->cookies[] = $cookie;

        return $cookie;
    }
}

final class ServiceSessionCookieDouble
{
    public array $setCalls = [];

    public function set(string $var, string $val): void
    {
        $this->setCalls[] = [$var, $val];
    }
}

final class ServiceSessionFactoryDouble
{
    public array $calls = [];

    public function __construct(private array $sessions)
    {
    }

    public function __invoke(string $nameSpace, string $group): ServiceSessionStorageDouble
    {
        $this->calls[] = [$nameSpace, $group];

        return $this->sessions[$nameSpace . ':' . $group];
    }
}

final class ServiceSessionStorageDouble
{
    public function __construct(public array $data = [])
    {
    }

    public function &getByLink(mixed $key, mixed $defaultValue = null): mixed
    {
        if (!array_key_exists($key, $this->data)) {
            $this->data[$key] = $defaultValue;
        }

        return $this->data[$key];
    }

    public function get(mixed $key, mixed $defaultValue = null): mixed
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : $defaultValue;
    }

    public function set(mixed $key, mixed $value): bool
    {
        $this->data[$key] = $value;

        return true;
    }
}

final class ServiceSessionDateFactoryDouble
{
    public array $dates = [];

    public function __construct(private int|float $difference)
    {
    }

    public function __invoke(string $date): ServiceSessionDateDouble
    {
        $date = new ServiceSessionDateDouble($date, $this->difference);
        $this->dates[] = $date;

        return $date;
    }
}

final class ServiceSessionDateDouble
{
    public function __construct(public string $date, private int|float $difference)
    {
    }

    public function getDifference(string $date): int|float
    {
        return $this->difference;
    }
}

final class ServiceSessionRuntimeSettingsDouble
{
    public array $sets = [];

    public function set(string $name, string $value): string|false
    {
        $this->sets[] = [$name, $value];

        return false;
    }
}

final class ServiceSessionServiceNativeSessionDouble
{
    public array $calls = [];

    public function setCookieParams(int $lifetime, string $path, ?string $domain = null): bool
    {
        $this->calls[] = ['setCookieParams', $lifetime, $path, $domain];

        return true;
    }

    public function cacheLimiter(string $value): string|false
    {
        $this->calls[] = ['cacheLimiter', $value];

        return $value;
    }

    public function name(?string $name = null): string|false
    {
        $this->calls[] = ['name', $name];

        return $name ?? 'SID';
    }
}
