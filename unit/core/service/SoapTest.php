<?php

declare(strict_types=1);

use fan\core\service\soap;
use FanTest\core\SourceFileContractTestCase;
use FanTest\core\ConfigRowFactory;
use fan\core\base\service;


if (!function_exists('get_class_alt')) {
    function get_class_alt(mixed $value): ?string
    {
        return is_object($value) ? get_class($value) : null;
    }
}

if (!function_exists('fan\core\base\get_class_alt')) {
    eval('namespace fan\core\base { function get_class_alt(mixed $value): ?string { return \get_class_alt($value); } }');
}

class ServiceSoapTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/soap.php';

    public function testCallNormalizesInvalidArgumentsAndOptionsAndClearsHeaders(): void
    {
        $client = new ServiceSoapClientDouble('result');
        $error = new ServiceSoapErrorDouble();
        $soap = new ServiceSoapProbe($error);
        $soap->setSoapClient($client);
        $soap->setConfigData(['TRACE_ENABLED' => false]);
        $soap->setSoapHeaders([new SoapHeader('urn:test', 'Auth', ['token' => 'abc'])]);

        $this->assertSame('result', $soap->call('Ping', 'bad-args', 'bad-options'));

        $this->assertSame([
            ['Error! Arguments is not array there: (string) "bad-args"', 'SOAP: incorrect arguments.', null, true],
            ['Error! Options is not array there: (string) "bad-options"', 'SOAP: incorrect options.', null, true],
        ], $error->messages);
        $this->assertSame('Ping', $client->calls[0]['funcName']);
        $this->assertSame([], $client->calls[0]['arguments']);
        $this->assertSame([], $client->calls[0]['options']);
        $this->assertCount(1, $client->calls[0]['headers']);
    }

    public function testCallStoresSoapFaultAndLogsWhenEnabled(): void
    {
        $fault = new SoapFault('Server', 'Boom');
        $error = new ServiceSoapErrorDouble();
        $soap = new ServiceSoapProbe($error);
        $soap->setSoapClient(new ServiceSoapClientDouble($fault));
        $soap->allowErrLogging(true);

        $this->assertNull($soap->call('Ping'));
        $this->assertTrue($soap->isError());
        $this->assertSame($fault, $soap->getSoapFault());
        $this->assertSame([$fault], $error->soapErrors);
    }

    public function testSetSoapVarWrapsConfiguredLevels(): void
    {
        $soap = new ServiceSoapProbe(new ServiceSoapErrorDouble());
        $result = $soap->setSoapVar(['node' => ['child' => 'value']], ['node_name' => 'root'], [0]);

        $this->assertInstanceOf(SoapVar::class, $result);
    }

    public function testSetHeaderUsesInjectedSoapHeaderFactory(): void
    {
        $soap = new ServiceSoapProbe(new ServiceSoapErrorDouble());
        $header = new SoapHeader('urn:test', 'Auth', ['token' => 'abc']);
        $soap->setFactory(
            'soapHeaderFactory',
            static function (string $nameSpace, array $name, ?array $data = null) use ($header): SoapHeader {
                return $header;
            }
        );

        $soap->setHeader('urn:test', ['Auth'], ['token' => 'abc']);

        $this->assertSame([$header], $soap->getSoapHeaders());
    }

    public function testInitSoapObjectUsesInjectedSoapClientFactory(): void
    {
        $client = new ServiceSoapRealClientDouble();
        $error = new ServiceSoapErrorDouble();
        $storage = new ServiceSoapWsdlFileStorageDouble(true);
        $soap = new ServiceSoapProbe($error);
        $soap->setWsdlFileStorage($storage);
        $soap->setRuntime(new ServiceSoapRuntimeDouble('/tmp/php-fan-wsdl/'));
        $soap->setConfigData([
            'TRACE_ENABLED' => false,
            'WSDL_DIR' => '{PROJECT}/wsdl/',
            'PARAM' => ['trace' => '1'],
            'BLOCK_SSL_VERIFY' => false,
        ]);
        $calls = [];
        $soap->setFactory(
            'soapClientFactory',
            static function (string $wsdlFile, ?array $param = null) use (&$calls, $client): SoapClient {
                $calls[] = [$wsdlFile, $param];

                return $client;
            }
        );

        $this->assertSame($client, $soap->exposedInitSoapObj('service.wsdl'));
        $this->assertSame([
            ['/tmp/php-fan-wsdl/service.wsdl', ['trace' => '1']],
        ], $calls);
    }

    public function testInitSoapObjectUsesInjectedStreamContextFactoryForBlockedSslVerification(): void
    {
        $client = new ServiceSoapRealClientDouble();
        $context = stream_context_create();
        $error = new ServiceSoapErrorDouble();
        $soap = new ServiceSoapProbe($error);
        $soap->setWsdlFileStorage(new ServiceSoapWsdlFileStorageDouble(true));
        $soap->setRuntime(new ServiceSoapRuntimeDouble('/tmp/php-fan-wsdl/'));
        $soap->setConfigData([
            'TRACE_ENABLED' => false,
            'WSDL_DIR' => '{PROJECT}/wsdl/',
            'PARAM' => [],
            'BLOCK_SSL_VERIFY' => true,
        ]);
        $contextCalls = [];
        $clientCalls = [];
        $soap->setFactory(
            'streamContextFactory',
            static function (array $options) use (&$contextCalls, $context): mixed {
                $contextCalls[] = $options;

                return $context;
            }
        );
        $soap->setFactory(
            'soapClientFactory',
            static function (string $wsdlFile, ?array $param = null) use (&$clientCalls, $client): SoapClient {
                $clientCalls[] = [$wsdlFile, $param];

                return $client;
            }
        );

        $this->assertSame($client, $soap->exposedInitSoapObj('service.wsdl'));
        $this->assertSame([
            [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ],
        ], $contextCalls);
        $this->assertSame($context, $clientCalls[0][1]['stream_context'] ?? null);
    }

    public function testDebugInfoReturnsFormattedTraceWhenTraceEnabled(): void
    {
        $client = new ServiceSoapClientDouble('result');
        $soap = new ServiceSoapProbe(new ServiceSoapErrorDouble());
        $soap->setSoapClient($client);
        $soap->setConfigData(['TRACE_ENABLED' => true]);

        $debug = $soap->getDebugInfo();

        $this->assertIsString($debug);
        $this->assertStringContainsString('Sent Request DATA', $debug);
        $this->assertStringContainsString('&lt;request&gt;', $debug);
        $this->assertStringContainsString('&lt;response&gt;', $debug);
    }

    public function testMissingSoapHeaderFactoryFailsAtUseSite(): void
    {
        $soap = new ServiceSoapProbe(new ServiceSoapErrorDouble());
        $soap->unsetFactory('soapHeaderFactory');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SOAP header factory is not configured for SOAP service.');

        $soap->setHeader('urn:test', ['Auth'], ['token' => 'abc']);
    }

    public function testMissingSoapClientFactoryFailsAtUseSite(): void
    {
        $soap = new ServiceSoapProbe(new ServiceSoapErrorDouble());
        $soap->setWsdlFileStorage(new ServiceSoapWsdlFileStorageDouble(true));
        $soap->setRuntime(new ServiceSoapRuntimeDouble('/tmp/php-fan-wsdl/'));
        $soap->setConfigData([
            'TRACE_ENABLED' => false,
            'WSDL_DIR' => '{PROJECT}/wsdl/',
            'PARAM' => [],
            'BLOCK_SSL_VERIFY' => false,
        ]);
        $soap->unsetFactory('soapClientFactory');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SOAP client factory is not configured for SOAP service.');

        $soap->exposedInitSoapObj('service.wsdl');
    }

    public function testMissingSoapVarFactoryFailsAtUseSite(): void
    {
        $soap = new ServiceSoapProbe(new ServiceSoapErrorDouble());
        $soap->unsetFactory('soapVarFactory');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SOAP var factory is not configured for SOAP service.');

        $soap->setSoapVar(['node' => ['child' => 'value']], ['node_name' => 'root'], [0]);
    }

    public function testMissingDomDocumentFactoryFailsAtUseSite(): void
    {
        $client = new ServiceSoapClientDouble('result');
        $soap = new ServiceSoapProbe(new ServiceSoapErrorDouble());
        $soap->setSoapClient($client);
        $soap->setConfigData(['TRACE_ENABLED' => true]);
        $soap->unsetFactory('domDocumentFactory');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DOM document factory is not configured for SOAP service.');

        $soap->getDebugInfo();
    }

    public function testMissingStreamContextFactoryFailsAtUseSite(): void
    {
        $soap = new ServiceSoapProbe(new ServiceSoapErrorDouble());
        $soap->setWsdlFileStorage(new ServiceSoapWsdlFileStorageDouble(true));
        $soap->setRuntime(new ServiceSoapRuntimeDouble('/tmp/php-fan-wsdl/'));
        $soap->setConfigData([
            'TRACE_ENABLED' => false,
            'WSDL_DIR' => '{PROJECT}/wsdl/',
            'PARAM' => [],
            'BLOCK_SSL_VERIFY' => true,
        ]);
        $soap->unsetFactory('streamContextFactory');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream context factory is not configured for SOAP service.');

        $soap->exposedInitSoapObj('service.wsdl');
    }

    public function testInitSoapObjectLogsMissingWsdlThroughInjectedDependencies(): void
    {
        $error = new ServiceSoapErrorDouble();
        $soap = new ServiceSoapProbe($error);
        $soap->setConfigData([
            'TRACE_ENABLED' => false,
            'WSDL_DIR' => '{PROJECT}/missing/',
            'PARAM' => [],
            'BLOCK_SSL_VERIFY' => false,
        ]);
        $soap->setRuntime(new ServiceSoapRuntimeDouble('/tmp/php-fan-wsdl/'));

        $this->assertNull($soap->exposedInitSoapObj('missing.wsdl'));
        $this->assertSame([
            ['Error. WSDL-file "/tmp/php-fan-wsdl/missing.wsdl" isn\'t exist.', '', null, false],
        ], $error->messages);
    }

    public function testPublicInitializerDelegatesToSoapObjectSetup(): void
    {
        $error = new ServiceSoapErrorDouble();
        $soap = new ServiceSoapProbe($error);
        $soap->setConfigData([
            'TRACE_ENABLED' => false,
            'WSDL_DIR' => '{PROJECT}/missing/',
            'PARAM' => [],
            'BLOCK_SSL_VERIFY' => false,
        ]);
        $soap->setRuntime(new ServiceSoapRuntimeDouble('/tmp/php-fan-wsdl/'));

        $this->assertNull($soap->initializeSoapObject('missing.wsdl'));
        $this->assertSame([
            ['Error. WSDL-file "/tmp/php-fan-wsdl/missing.wsdl" isn\'t exist.', '', null, false],
        ], $error->messages);
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseFunctionAliases();

        $runtime = new ServiceSoapRuntimeDouble('/tmp/php-fan-wsdl/');
        $config = new ServiceSoapConfigDouble([
            'CACHE_ENABLED' => false,
            'CACHE_DIR' => '',
            'CACHE_TTL' => '',
            'TRACE_ENABLED' => false,
            'PARAM' => [],
        ]);
        $configurator = new ServiceSoapConfiguratorDouble($config);
        $cacheFactoryCalls = [];
        $settings = new ServiceSoapRuntimeSettingsDouble();

        $soap = new ServiceSoapConstructorProbe(
            true,
            static fn(): object => new ServiceSoapErrorDouble(),
            $runtime,
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            },
            $settings
        );

        $this->assertSame([], $runtime->initializer->serviceParams);
        $this->assertSame([$soap], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceSoapConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame([], $cacheFactoryCalls);
        $this->assertSame([['soap.wsdl_cache_enabled', '0']], $settings->sets);
    }

    private function ensureBaseFunctionAliases(): void
    {
        if (!function_exists('get_class_name')) {
            eval('function get_class_name(string|object $object): ?string { if (is_object($object)) { $object = get_class($object); } $parts = explode(chr(92), $object); return end($parts); }');
        }
        if (!function_exists('fan\core\base\get_class_name')) {
            eval('namespace fan\core\base { function get_class_name(string|object $object): ?string { return \get_class_name($object); } }');
        }
    }
}

final class ServiceSoapProbe extends soap
{
    public function __construct(private ServiceSoapErrorDouble $error)
    {
        $this->setFactory('errorFactory', fn(): ServiceSoapErrorDouble => $this->error);
        $this->setWsdlFileStorage(new ServiceSoapWsdlFileStorageDouble(false));
        $this->setArrayValueReader(static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default);
        $this->setRuntimeFactories();
    }

    public function setSoapClient(object $client): void
    {
        $property = new ReflectionProperty(soap::class, 'soapObj');
        $property->setValue($this, $client);
    }

    public function setSoapHeaders(array $headers): void
    {
        $property = new ReflectionProperty(soap::class, 'soapHeaders');
        $property->setValue($this, $headers);
    }

    public function getSoapHeaders(): array
    {
        $property = new ReflectionProperty(soap::class, 'soapHeaders');

        return $property->getValue($this);
    }

    public function setConfigData(array $config): void
    {
        $property = new ReflectionProperty(service::class, 'config');
        $property->setValue($this, ConfigRowFactory::row($config + [
            'TRACE_ENABLED' => false,
        ]));
    }

    public function setRuntime(object $runtime): void
    {
        $property = new ReflectionProperty(soap::class, 'runtime');
        $property->setValue($this, $runtime);
    }

    public function setWsdlFileStorage(object $storage): void
    {
        $property = new ReflectionProperty(soap::class, 'wsdlFileStorage');
        $property->setValue($this, $storage);
    }

    public function exposedInitSoapObj(string $wsdlFile, mixed $param = null): ?SoapClient
    {
        return $this->_initSoapObj($wsdlFile, $param);
    }

    public function setFactory(string $propertyName, callable $factory): void
    {
        $property = new ReflectionProperty(soap::class, $propertyName);
        $property->setValue($this, Closure::fromCallable($factory));
    }

    public function unsetFactory(string $propertyName): void
    {
        (Closure::bind(
            function (string $propertyName): void {
                unset($this->{$propertyName});
            },
            $this,
            soap::class
        ))($propertyName);
    }

    private function setArrayValueReader(callable $arrayValueReader): void
    {
        $property = new ReflectionProperty(soap::class, 'arrayValueReader');
        $property->setValue($this, Closure::fromCallable($arrayValueReader));
    }

    private function setRuntimeFactories(): void
    {
        $this->setFactory(
            'soapHeaderFactory',
            static fn(string $nameSpace, array $name, ?array $data = null): SoapHeader => new SoapHeader($nameSpace, (string)($name[0] ?? ''), $data)
        );
        $this->setFactory(
            'soapClientFactory',
            static fn(string $wsdlFile, ?array $param = null): SoapClient => new ServiceSoapRealClientDouble()
        );
        $this->setFactory(
            'soapVarFactory',
            static fn(
                mixed $data,
                int $encoding,
                ?string $typeName = null,
                ?string $typeNamespace = null,
                ?string $nodeName = null,
                ?string $nodeNamespace = null
            ): SoapVar => new SoapVar($data, $encoding, $typeName, $typeNamespace, $nodeName, $nodeNamespace)
        );
        $this->setFactory(
            'domDocumentFactory',
            static fn(): DOMDocument => new DOMDocument()
        );
        $this->setFactory(
            'streamContextFactory',
            static fn(array $options): mixed => stream_context_create($options)
        );
    }
}

final class ServiceSoapConstructorProbe extends soap
{
    public function __construct(
        bool $logEnabled,
        callable $errorFactory,
        object $runtime,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        object $phpRuntimeSettings,
        ?object $wsdlFileStorage = null
    )
    {
        parent::__construct(
            $logEnabled,
            $errorFactory,
            $runtime,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $phpRuntimeSettings,
            $wsdlFileStorage ?? new ServiceSoapWsdlFileStorageDouble(false),
            static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default,
            static fn(object $object): string => get_class($object)
        );
    }
}

final class ServiceSoapWsdlFileStorageDouble
{
    public array $existsPaths = [];

    public function __construct(private bool $exists)
    {
    }

    public function exists(string $path): bool
    {
        $this->existsPaths[] = $path;

        return $this->exists;
    }
}

final class ServiceSoapRuntimeDouble
{
    public ServiceSoapInitializerDouble $initializer;

    public function __construct(private string $resolvedPath)
    {
        $this->initializer = new ServiceSoapInitializerDouble();
    }

    public function getInitializer(): ServiceSoapInitializerDouble
    {
        return $this->initializer;
    }

    public function parsePath(string $path): string
    {
        return $this->resolvedPath;
    }
}

final class ServiceSoapInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceSoapConfiguratorDouble
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

final class ServiceSoapConfigDouble extends ArrayObject
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this : ($this[$key] ?? $default);
    }
}

final class ServiceSoapClientDouble
{
    public array $calls = [];

    public function __construct(private mixed $result)
    {
    }

    public function __soapCall(string $funcName, array $arguments, ?array $options = null, mixed $headers = null): mixed
    {
        $this->calls[] = [
            'funcName' => $funcName,
            'arguments' => $arguments,
            'options' => $options,
            'headers' => $headers,
        ];

        if ($this->result instanceof SoapFault) {
            throw $this->result;
        }

        return $this->result;
    }

    public function __getLastRequestHeaders(): string
    {
        return 'Request-Headers';
    }

    public function __getLastRequest(): string
    {
        return '<request><id>1</id></request>';
    }

    public function __getLastResponseHeaders(): string
    {
        return 'Response-Headers';
    }

    public function __getLastResponse(): string
    {
        return '<response><ok>1</ok></response>';
    }
}

final class ServiceSoapRealClientDouble extends SoapClient
{
    public function __construct()
    {
    }
}

final class ServiceSoapRuntimeSettingsDouble
{
    public array $sets = [];

    public function set(string $name, string $value): string|false
    {
        $this->sets[] = [$name, $value];

        return false;
    }
}

final class ServiceSoapErrorDouble
{
    public array $messages = [];

    public array $soapErrors = [];

    public function logErrorMessage(string $message, string $title = '', mixed $note = null, bool $fixPosition = false): void
    {
        $this->messages[] = [$message, $title, $note, $fixPosition];
    }

    public function setErrorBuffering(): void
    {
    }

    public function offErrorBuffering(): array
    {
        return [];
    }

    public function logSoapError(SoapFault $fault): void
    {
        $this->soapErrors[] = $fault;
    }
}
