<?php

declare(strict_types=1);

use FanTest\_core\SourceFileContractTestCase;
use fan\core\service\tab;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use FanTest\_core\ConfigRowFactory;
use fan\core\base\meta\maker;
use fan\core\base\meta\row;
use fan\core\base\service;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;
use fan\project\base\meta\row as meta_row;


#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class GeneratedPendingServiceTabTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/tab.php';

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseHelper();
        $runtime = new ServiceTabRuntimeDouble();
        $configurator = new ServiceTabConfiguratorDouble(new ServiceTabConfigDouble([]));
        $cacheFactoryCalls = [];
        $loadedPhpArrayFiles = [];

        $tab = new ServiceTabConstructorProbe(
            true,
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            },
            static function (string $path, mixed $default = null) use (&$loadedPhpArrayFiles): mixed {
                $loadedPhpArrayFiles[] = [$path, $default];

                return $default;
            }
        );

        $this->assertSame([ServiceTabConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$tab], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceTabConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame([], $cacheFactoryCalls);
        $this->assertSame([], $loadedPhpArrayFiles);
    }

    public function testParsedMatcherItemsAreStoredAsObjects(): void
    {
        $code = $this->sourceCode();

        $this->assertStringContainsString('protected ?object $currentData = null;', $code);
        $this->assertStringContainsString('protected ?object $lastData = null;', $code);
        $this->assertStringContainsString('$this->currentData = $this->matcher->getCurrentParsedData();', $code);
        $this->assertStringContainsString('$this->lastData    = $this->matcher->getLastParsedData();', $code);
    }

    public function testMainBlockReceivesArrayContainerMeta(): void
    {
        $this->assertStringContainsString(
            '$this->mainBlock = $this->createBlock($class, \'main\', null, [], false);',
            $this->sourceCode()
        );
    }

    public function testDebugExternalFilesUseBooleanMode(): void
    {
        $this->assertStringContainsString('$debug->setExtFiles($rootBlock, false);', $this->sourceCode());
    }

    public function testBooleanFlagsAcceptOnlyNumericValues(): void
    {
        $tab = $this->makeTabService();

        $this->assertFalse($tab->readFlag('0'));
        $this->assertTrue($tab->readFlag('1'));
        $this->assertFalse($tab->readFlag(0));
        $this->assertTrue($tab->readFlag(1));
    }

    public function testBooleanFlagWordsAreRejected(): void
    {
        $tab = $this->makeTabService();

        $this->expectException(\UnexpectedValueException::class);
        $tab->readFlag('false');
    }

    public function testArrayConfigRowsAreConvertedToArrays(): void
    {
        require_once dirname(__DIR__, 3) . '/_core/base/data.php';
        require_once dirname(__DIR__, 3) . '/_core/service/config/row.php';

        $tab = $this->makeTabService();
        $row = ConfigRowFactory::row(['common' => ['tplVars' => ['foo' => 'bar']]]);

        $this->assertSame(
            ['common' => ['tplVars' => ['foo' => 'bar']]],
            $tab->readArrayConfig($row)
        );
    }

    public function testArrayConfigRejectsScalarValues(): void
    {
        $tab = $this->makeTabService();

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Configuration "TEST_ARRAY" must be an array, string given.');

        $tab->readArrayConfig('legacy');
    }

    public function testCheckTabRolesUsesInjectedRoleFactory(): void
    {
        $role = new ServiceTabRoleDouble(false);
        $tab = $this->makeTabService([
            'tabMeta' => [
                'roles' => [
                    ['condition' => 'admin'],
                ],
            ],
        ]);

        $tab->setTabDependencies(null, fn(): ServiceTabRoleDouble => $role);

        $this->assertFalse($tab->checkTabRoles(null, false));
        $this->assertSame(['admin'], $role->checks);
    }

    public function testDebugModeUsesInjectedRequestAndCookieFactory(): void
    {
        $request = new ServiceTabRequestDouble([
            'debug|PGC|0' => 10,
            'debug|PG|0' => 10,
        ]);
        $cookie = new ServiceTabCookieDouble();
        $tab = $this->makeTabService([
            'allowDebug' => true,
            'request' => $request,
            'config' => new ServiceTabConfigDouble(['debug_key' => 'debug']),
        ]);

        $tab->setTabDependencies(
            cookieFactory: fn(): ServiceTabCookieDouble => $cookie
        );

        $this->assertSame(1, $tab->_getDebugMode());
        $this->assertSame([['debug', 1]], $cookie->sets);
        $this->assertSame([], $cookie->deletes);
    }

    public function testBlockDependenciesUseInjectedFactoriesWithoutResolver(): void
    {
        $request = new stdClass();
        $locale = new stdClass();
        $runtime = new stdClass();
        $role = new stdClass();
        $reflector = new stdClass();
        $session = new stdClass();
        $entity = new stdClass();
        $json = new stdClass();
        $dataLoader = new stdClass();
        $phpArrayFileLoader = static fn(string $path, mixed $default = null): mixed => $default;
        $metaMakerFactory = static fn(): object => new stdClass();
        $viewRouterFactory = static fn(): object => new stdClass();
        $viewLoaderState = new stdClass();
        $viewLoaderStateFactory = static fn(): object => $viewLoaderState;
        $metaMakerState = new stdClass();
        $metaMakerStateFactory = static fn(): object => $metaMakerState;
        $uploadSizeLimitProvider = static fn(): string => '32M';
        $metaRowFactory = static fn(
            maker $maker,
            array $data,
            ?row $parent = null,
            int|string|null $keyName = null,
            ?callable $rowFactory = null
        ): object => new meta_row($maker, $data, $parent, $keyName, $rowFactory);
        $entityCalls = [];
        $jsonCalls = [];
        $dataLoaderCalls = 0;
        $arrayAdducer = static fn(mixed $value): array => is_array($value) ? $value : [$value];
        $recursiveMerger = static fn(mixed ...$values): array => array_replace_recursive(...$values);
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $arrayLikeChecker = static fn(mixed $value): bool => is_array($value) || $value instanceof ArrayAccess;
        $shortClassNameResolver = static function (object|string $object): string {
            $className = is_object($object) ? get_class($object) : $object;
            $position = strrpos($className, '\\');

            return $position === false ? $className : substr($className, $position + 1);
        };
        $imageMetadataReader = new stdClass();
        $errorLogWriter = new stdClass();
        $blockFileStorage = new stdClass();
        $metaFileStorage = new stdClass();
        $projectToolFileStorage = new stdClass();
        $rootHtmlFileStorage = new stdClass();

        $tab = $this->makeTabService([
            'request' => $request,
            'locale' => $locale,
            'matcher' => new stdClass(),
            'input' => new stdClass(),
            'sessionFactory' => static fn(string $namespace, string $group): object => $session,
        ]);
        $tab->setTabDependencies(
            runtime: $runtime,
            roleFactory: static fn(): object => $role,
            reflectorFactory: static fn(): object => $reflector,
            jsonFactory: static function (bool $useBase64 = false) use (&$jsonCalls, $json): object {
                $jsonCalls[] = [$useBase64];
                return $json;
            },
            dataLoaderFactory: static function () use (&$dataLoaderCalls, $dataLoader): object {
                $dataLoaderCalls++;
                return $dataLoader;
            },
            arrayAdducer: $arrayAdducer,
            recursiveMerger: $recursiveMerger,
            arrayValueReader: $arrayValueReader,
            arrayLikeChecker: $arrayLikeChecker,
            shortClassNameResolver: $shortClassNameResolver,
            entityFactory: static function (mixed ...$arguments) use (&$entityCalls, $entity): object {
                $entityCalls[] = $arguments;
                return $entity;
            },
            phpArrayFileLoader: $phpArrayFileLoader,
            metaMakerFactory: $metaMakerFactory,
            metaRowFactory: $metaRowFactory,
            viewRouterFactory: $viewRouterFactory,
            viewLoaderStateFactory: $viewLoaderStateFactory,
            metaMakerStateFactory: $metaMakerStateFactory,
            uploadSizeLimitProvider: $uploadSizeLimitProvider,
            imageMetadataReader: $imageMetadataReader,
            errorLogWriter: $errorLogWriter,
            blockFileStorage: $blockFileStorage,
            metaFileStorage: $metaFileStorage,
            projectToolFileStorage: $projectToolFileStorage,
            rootHtmlFileStorage: $rootHtmlFileStorage
        );

        $dependencies = $tab->getBlockDependencies();

        $this->assertSame($tab, $dependencies['tab']);
        $this->assertSame($request, ($dependencies['requestFactory'])());
        $this->assertSame($role, ($dependencies['roleFactory'])());
        $this->assertSame($session, ($dependencies['sessionFactory'])('block-name', 'block'));
        $this->assertSame($reflector, ($dependencies['reflectorFactory'])());
        $this->assertSame($runtime, $dependencies['runtime']);
        $this->assertSame($locale, ($dependencies['localeFactory'])());
        $this->assertArrayNotHasKey('serviceFactory', $dependencies);
        $this->assertSame($json, ($dependencies['jsonFactory'])(true));
        $this->assertSame($dataLoader, ($dependencies['dataLoaderFactory'])());
        $this->assertSame(['value'], ($dependencies['arrayAdducer'])('value'));
        $this->assertSame(['a' => 1, 'b' => 2], ($dependencies['recursiveMerger'])(['a' => 1], ['b' => 2]));
        $this->assertSame('fallback', ($dependencies['arrayValueReader'])([], 'missing', 'fallback'));
        $this->assertTrue(($dependencies['arrayLikeChecker'])(new ArrayObject()));
        $this->assertSame((new ReflectionClass($this))->getShortName(), ($dependencies['shortClassNameResolver'])($this));
        $this->assertSame($entity, ($dependencies['entityFactory'])(1));
        $this->assertSame($phpArrayFileLoader, $dependencies['phpArrayFileLoader']);
        $this->assertSame($metaMakerFactory, $dependencies['metaMakerFactory']);
        $this->assertSame($metaRowFactory, $dependencies['metaRowFactory']);
        $this->assertSame($viewRouterFactory, $dependencies['viewRouterFactory']);
        $this->assertSame($viewLoaderState, $dependencies['viewLoaderState']);
        $this->assertSame($metaMakerState, $dependencies['metaMakerState']);
        $this->assertSame($imageMetadataReader, $dependencies['imageMetadataReader']);
        $this->assertSame($errorLogWriter, $dependencies['errorLogWriter']);
        $this->assertSame($blockFileStorage, $dependencies['blockFileStorage']);
        $this->assertSame($metaFileStorage, $dependencies['metaFileStorage']);
        $this->assertSame($projectToolFileStorage, $dependencies['projectToolFileStorage']);
        $this->assertSame($rootHtmlFileStorage, $dependencies['rootHtmlFileStorage']);
        $this->assertSame($uploadSizeLimitProvider, $dependencies['uploadSizeLimitProvider']);
        $this->assertSame('32M', ($dependencies['uploadSizeLimitProvider'])());
        $this->assertSame([[true]], $jsonCalls);
        $this->assertSame(1, $dataLoaderCalls);
        $this->assertSame([[1]], $entityCalls);
    }

    public function testBlockDependenciesRequireSpecificFactoriesForOptionalServices(): void
    {
        $tab = $this->makeTabService([
            'request' => new stdClass(),
            'locale' => new stdClass(),
            'matcher' => new stdClass(),
            'input' => new stdClass(),
            'runtime' => new stdClass(),
            'sessionFactory' => static fn(string $namespace, string $group): object => new stdClass(),
        ]);
        $tab->setTabDependencies(
            runtime: new stdClass(),
            roleFactory: static fn(): object => new stdClass(),
            reflectorFactory: static fn(): object => new stdClass(),
            phpArrayFileLoader: static fn(string $path, mixed $default = null): mixed => $default,
            metaMakerFactory: static fn(): object => new stdClass(),
            viewRouterFactory: static fn(): object => new stdClass(),
            viewLoaderStateFactory: static fn(): object => new stdClass(),
            metaMakerStateFactory: static fn(): object => new stdClass(),
            arrayAdducer: static fn(mixed $value): array => is_array($value) ? $value : [$value],
            recursiveMerger: static fn(mixed ...$values): array => array_replace_recursive(...$values),
            arrayValueReader: static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default,
            arrayLikeChecker: static fn(mixed $value): bool => is_array($value) || $value instanceof ArrayAccess,
            shortClassNameResolver: static fn(object|string $object): string => is_object($object) ? (new ReflectionClass($object))->getShortName() : basename(str_replace('\\', '/', $object)),
            metaRowFactory: static fn(
                maker $maker,
                array $data,
                ?row $parent = null,
                int|string|null $keyName = null,
                ?callable $rowFactory = null
            ): object => new meta_row($maker, $data, $parent, $keyName, $rowFactory)
        );

        $dependencies = $tab->getBlockDependencies();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Entity service is not configured for tab service.');

        ($dependencies['entityFactory'])();
    }

    public function testRootMetaUsesInjectedArrayValueReader(): void
    {
        $calls = [];
        $tab = $this->makeTabService([
            'defaultMeta' => [
                'root' => ['template' => 'root.tpl'],
                'common' => ['tplVars' => ['title' => 'Root']],
            ],
            'locale' => new ServiceTabLocaleDouble(false),
            'arrayValueReader' => static function (array|\ArrayAccess $array, mixed $key, mixed $default = null) use (&$calls): mixed {
                $calls[] = [$key, $default];

                return $array[$key] ?? $default;
            },
        ]);

        $this->assertSame(
            [
                'own' => ['template' => 'root.tpl'],
                'common' => ['tplVars' => ['title' => 'Root']],
            ],
            $tab->exposeGetRootMeta()
        );
        $this->assertSame([
            ['root', []],
            ['common', []],
        ], $calls);
    }

    public function testAliasFileUsesInjectedPhpArrayLoader(): void
    {
        $aliasFile = '/tmp/fan-tab-alias.php';
        $loadedPhpArrayFiles = [];
        $aliasFileStorage = new ServiceTabAliasFileStorageDouble();
        $tab = $this->makeTabService([
            'matcher' => new ServiceTabAliasMatcherDouble(['catalog', 'item']),
            'runtime' => new ServiceTabRuntimeDouble(),
            'aliasFileStorage' => $aliasFileStorage,
            'phpArrayFileLoader' => static function (string $path, mixed $default = null) use (&$loadedPhpArrayFiles): mixed {
                $loadedPhpArrayFiles[] = [$path, $default];

                return [];
            },
        ]);
        $configProperty = new ReflectionProperty(service::class, 'config');
        $configProperty->setValue($tab, new ServiceTabConfigDouble(['ALIAS_FILE_PATH' => $aliasFile]));

        $this->assertSame($tab, $tab->exposeCheckAlias());
        $this->assertSame([$aliasFile], $aliasFileStorage->readablePaths);
        $this->assertSame([
            [$aliasFile, []],
        ], $loadedPhpArrayFiles);
    }

    public function testViewDefinerUsesInjectedFactory(): void
    {
        $request = new stdClass();
        $viewDefiner = new stdClass();
        $calls = [];
        $tab = $this->makeTabService([
            'request' => $request,
            'config' => new ServiceTabConfigDouble([
                'VIEW_DEFINER' => new ServiceTabConfigRowDouble(['default_format' => 'json']),
            ]),
            'viewDefinerFactory' => static function (array $config, object $requestArgument, object $tabArgument) use (&$calls, $request, $viewDefiner): object {
                $calls[] = [$config, $requestArgument, $tabArgument];

                return $viewDefiner;
            },
        ]);

        $this->assertSame($viewDefiner, $tab->getViewDefiner());
        $this->assertSame($viewDefiner, $tab->getViewDefiner());
        $this->assertCount(1, $calls);
        $this->assertSame(['default_format' => 'json'], $calls[0][0]);
        $this->assertSame($request, $calls[0][1]);
        $this->assertSame($tab, $calls[0][2]);
    }

    public function testViewDefinerFactoryIsRequired(): void
    {
        $tab = $this->makeTabService();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('View definer factory is not configured for tab service.');

        $tab->getViewDefiner();
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('resolveService(', $source);
        $this->assertStringNotContainsString('serviceFactory', $source);
    }

    public function testErrorTransferStateIsInjectedInsteadOfStaticStorage(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('private ?object $tabState = null;', $source);
        $this->assertStringContainsString('$this->tabState()', $source);
        $this->assertStringNotContainsString('protected static array $errTransfer', $source);
        $this->assertStringNotContainsString('self::$errTransfer', $source);
    }

    private function ensureBaseHelper(): void
    {
        if (function_exists('fan\core\base\get_class_name')) {
            return;
        }

        eval('
            namespace fan\core\base;

            function get_class_name(string|object $object): ?string
            {
                if (is_object($object)) {
                    $object = get_class($object);
                }
                $parts = explode("\\\\", $object);

                return end($parts);
            }
        ');
    }

    private function makeTabService(array $state = []): object
    {
        require_once dirname(__DIR__, 3) . '/_core/di/container_interface.php';
        require_once dirname(__DIR__, 3) . '/_core/base/service.php';
        require_once dirname(__DIR__, 3) . '/_core/base/service/single.php';

        if (!class_exists('\fan\core\service\tab', false)) {
            require_once dirname(__DIR__, 3) . '/_core/service/tab.php';
        }

        $tab = new class extends tab {
            public function __construct()
            {
            }

            public function readFlag(mixed $value): bool
            {
                return $this->readBooleanFlag($value, 'TEST_FLAG');
            }

            public function readArrayConfig(mixed $value): array
            {
                return $this->readArrayConfigValue($value, 'TEST_ARRAY');
            }

            public function exposeCheckAlias(): static
            {
                return $this->_checkAlias();
            }

            public function exposeGetRootMeta(): array
            {
                return $this->_getRootMeta();
            }
        };

        foreach ($state as $propertyName => $value) {
            $property = new ReflectionProperty(tab::class, $propertyName);
            $property->setValue($tab, $value);
        }

        return $tab;
    }
}

final class ServiceTabConstructorProbe extends tab
{
    public function __construct(
        bool $allowIni,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        callable $phpArrayFileLoader,
        ?callable $metaRowFactory = null,
        ?object $aliasFileStorage = null
    )
    {
        parent::__construct(
            allowIni: $allowIni,
            serviceBootstrapRuntime: $serviceBootstrapRuntime,
            serviceConfigurator: $serviceConfigurator,
            serviceCacheFactory: $serviceCacheFactory,
            phpArrayFileLoader: $phpArrayFileLoader,
            metaRowFactory: $metaRowFactory,
            aliasFileStorage: $aliasFileStorage,
            classNameResolver: static fn(object $object): string => get_class($object)
        );
    }
}

final class ServiceTabRoleDouble
{
    public array $checks = [];

    public function __construct(private bool $result)
    {
    }

    public function check(string $condition): bool
    {
        $this->checks[] = $condition;
        return $this->result;
    }
}

final class ServiceTabRequestDouble
{
    public function __construct(private array $values)
    {
    }

    public function get(string $key, string $source, mixed $default = null): mixed
    {
        return $this->values[$key . '|' . $source . '|' . (string)$default] ?? $default;
    }
}

final class ServiceTabCookieDouble
{
    public array $sets = [];
    public array $deletes = [];

    public function set(string $key, mixed $value): void
    {
        $this->sets[] = [$key, $value];
    }

    public function delete(string $key): void
    {
        $this->deletes[] = $key;
    }
}

final class ServiceTabLocaleDouble
{
    public function __construct(
        private bool $enabled,
        private string $language = 'en'
    )
    {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
}

final class ServiceTabConfigDouble
{
    public function __construct(private array $values)
    {
    }

    public function get(string|array|null $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->values : ($this->values[$key] ?? $default);
    }
}

final class ServiceTabConfigRowDouble
{
    public function __construct(private array $values)
    {
    }

    public function toArray(): array
    {
        return $this->values;
    }
}

final class ServiceTabRuntimeDouble
{
    public ServiceTabInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ServiceTabInitializerDouble();
    }

    public function getInitializer(): ServiceTabInitializerDouble
    {
        return $this->initializer;
    }

    public function parsePath(string $path): string
    {
        return $path;
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
}

final class ServiceTabInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceTabConfiguratorDouble
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

final class ServiceTabAliasMatcherDouble
{
    public function __construct(private array $parsedSource)
    {
    }

    public function getCurrentIndex(): int
    {
        return 0;
    }

    public function getLastItem(): ServiceTabAliasMatcherItemDouble
    {
        return new ServiceTabAliasMatcherItemDouble($this->parsedSource);
    }
}

final class ServiceTabAliasMatcherItemDouble
{
    public function __construct(private array $parsedSource)
    {
    }

    public function getParsedSrc(): array
    {
        return $this->parsedSource;
    }
}

final class ServiceTabAliasFileStorageDouble
{
    public array $readablePaths = [];

    public function isReadable(string $path): bool
    {
        $this->readablePaths[] = $path;

        return true;
    }
}
