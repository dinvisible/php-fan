<?php

declare(strict_types=1);

use FanTest\_core\SourceFileContractTestCase;
use fan\core\service\error;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;


class GeneratedPendingServiceErrorTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/error.php';

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseHelper();
        $runtime = new ServiceErrorRuntimeDouble();
        $configurator = new ServiceErrorConfiguratorDouble(new ServiceErrorConfigDouble());
        $cacheFactoryCalls = [];
        $input = new ServiceErrorInputDouble(['SERVER_NAME' => 'example.test']);
        $errorLogWriter = new ServiceErrorLogWriterDouble();

        $service = new ServiceErrorConstructorProbe(
            true,
            $input,
            $runtime,
            static fn(): object => new ServiceErrorLogDouble(),
            static fn(): object => new ServiceErrorEmailDouble(),
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            },
            static fn(string $path, mixed $default = null): mixed => $default,
            $errorLogWriter,
            new ServiceErrorFileStorageDouble()
        );

        $this->assertSame([ServiceErrorConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$service], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceErrorConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame(E_ALL, $service->readMask(E_ALL));
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testSourceNoLongerUsesFilesystemFunctionsDirectly(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_dir|realpath|file_exists|file_put_contents|scandir|is_file|is_writable|unlink|chmod)\s*\(/',
            $this->sourceCode()
        );
    }

    public function testNumericErrorMasksAreAccepted(): void
    {
        $service = $this->makeErrorService();

        $this->assertSame(30719, $service->readMask(30719));
        $this->assertSame(3754, $service->readMask('3754'));
        $this->assertSame(1034, $service->readMask(1034.0));
    }

    public function testSymbolicErrorMaskExpressionsAreRejected(): void
    {
        $service = $this->makeErrorService();

        $this->expectException(\UnexpectedValueException::class);
        $service->readMask('E_NOTICE | E_USER_NOTICE');
    }

    public function testLogErrorAddsServerDumpFromInjectedInputForNonHttpMethods(): void
    {
        $log = new ServiceErrorLogDouble();
        $service = $this->makeErrorService(
            input: new ServiceErrorInputDouble([
                'REQUEST_METHOD' => 'PUT',
                'REQUEST_URI' => '/api/item',
            ]),
            logFactory: static fn(): object => $log
        );

        $service->writeLog('custom', 'broken', 'Header', 'note', false, false);

        $this->assertSame('custom', $log->errors[0][0]);
        $this->assertSame('broken', $log->errors[0][1]);
        $this->assertSame('Header', $log->errors[0][2]);
        $this->assertStringContainsString('note<br />$_SERVER = ', $log->errors[0][3]);
        $this->assertStringContainsString("'REQUEST_URI' => '/api/item'", $log->errors[0][3]);
        $this->assertFalse($log->errors[0][4]);
    }

    public function testLogErrorFallsBackToErrorLogWriterWhenLogServiceIsRemoved(): void
    {
        $writer = new ServiceErrorLogWriterDouble();
        $service = $this->makeErrorService(
            input: new ServiceErrorInputDouble(['REQUEST_METHOD' => 'GET']),
            logFactory: null,
            errorLogWriter: $writer
        );

        $service->writeLog('custom', 'broken', 'Header', 'note', false, false);

        $this->assertSame([["custom\tHeader\tbroken\tnote", 0, null]], $writer->writes);
    }

    public function testMakeErrorEmailLoadsExistingPacketThroughInjectedLoader(): void
    {
        $dir = sys_get_temp_dir() . '/fan_error_packet_' . bin2hex(random_bytes(4));
        mkdir($dir, 0777, true);
        $prefix = $dir . '/packet_';
        $file = $prefix . 'custom.log.php';
        file_put_contents($file, "<?php\nreturn [];\n");

        $message = 'broken packet message';
        $key = md5($message);
        $loaded = [
            'start' => time(),
            $key => [
                'subject' => 'Custom',
                'message' => $message,
                'qtt' => 2,
            ],
        ];
        $loaderCalls = [];
        $config = new ServiceErrorConfigDouble([
            'MAIL_TO' => 'ops@example.test',
            'MAIL_FILE' => $prefix,
            'SENT_TIME_LIMIT' => 3600,
        ]);

        $service = $this->makeErrorService(
            runtime: new ServiceErrorRuntimeDouble(),
            config: $config,
            phpArrayFileLoader: static function (string $path, mixed $default = null) use (&$loaderCalls, $loaded): array {
                $loaderCalls[] = [$path, $default];

                return $loaded;
            }
        );

        try {
            $service->makeErrorEmail('custom', 'Custom', $message);

            $saved = require $file;
            $this->assertSame([[$file, ['start' => $loaderCalls[0][1]['start']]]], $loaderCalls);
            $this->assertSame(3, $saved[$key]['qtt']);
            $this->assertSame('Custom', $saved[$key]['subject']);
        } finally {
            if (file_exists($file)) {
                unlink($file);
            }
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }

    public function testPacketFileRemoveFailureIsReportedThroughInjectedErrorLogWriter(): void
    {
        $file = sys_get_temp_dir() . '/fan_error_packet_remove_' . bin2hex(random_bytes(4)) . '.log.php';
        file_put_contents($file, 'packet');
        chmod($file, 0444);
        $writer = new ServiceErrorLogWriterDouble();
        $config = new ServiceErrorConfigDouble([
            'MAIL_TO' => 'ops@example.test',
            'MAIL_FILE' => $file,
            'SENT_TIME_LIMIT' => 3600,
        ]);

        $service = $this->makeErrorService(config: $config, errorLogWriter: $writer);

        try {
            $method = new ReflectionMethod($service, 'removePacketFile');
            $method->invoke($service, $file);
        } finally {
            chmod($file, 0666);
            unlink($file);
        }

        $this->assertSame([
            ['Cannot remove error packet file "' . $file . '": file is not writable.', 0, null],
        ], $writer->writes);
    }

    private function makeErrorService(
        ?object $input = null,
        ?object $runtime = null,
        ?callable $logFactory = null,
        ?callable $emailFactory = null,
        ?object $config = null,
        ?callable $phpArrayFileLoader = null,
        ?ServiceErrorLogWriterDouble $errorLogWriter = null,
        ?ServiceErrorFileStorageDouble $fileStorage = null
    ): object
    {
        require_once dirname(__DIR__, 3) . '/_core/base/service.php';
        require_once dirname(__DIR__, 3) . '/_core/base/service/single.php';
        require_once dirname(__DIR__, 3) . '/_core/service/error.php';

        return new class($input, $runtime, $logFactory, $emailFactory, $config, $phpArrayFileLoader, $errorLogWriter, $fileStorage) extends error {
            public function __construct(
                ?object $input = null,
                ?object $runtime = null,
                ?callable $logFactory = null,
                ?callable $emailFactory = null,
                ?object $config = null,
                ?callable $phpArrayFileLoader = null,
                ?object $errorLogWriter = null,
                ?object $fileStorage = null
            )
            {
                $this->input = $input;
                $this->runtime = $runtime;
                $this->logFactory = $logFactory;
                $this->emailFactory = $emailFactory;
                $this->config = $config;
                $this->phpArrayFileLoader = $phpArrayFileLoader;
                $this->errorLogWriter = $errorLogWriter;
                $this->fileStorage = $fileStorage ?? new ServiceErrorFileStorageDouble();
            }

            public function readMask(mixed $mask): int
            {
                return $this->readErrorMask($mask);
            }

            public function writeLog(string $type, string $message, string $header, string $note, bool $isTrace, bool $duplicateByEmail): void
            {
                $this->_logError($type, $message, $header, $note, $isTrace, $duplicateByEmail);
            }
        };
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
}

final class ServiceErrorConstructorProbe extends error
{
    public function __construct(
        bool $allowIni,
        ?object $input,
        ?object $runtime,
        ?callable $logFactory,
        ?callable $emailFactory,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory,
        ?callable $phpArrayFileLoader,
        ?object $errorLogWriter,
        ?object $fileStorage
    )
    {
        parent::__construct(
            $allowIni,
            $input,
            $runtime,
            $logFactory,
            $emailFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $phpArrayFileLoader,
            $errorLogWriter,
            $fileStorage
        );
    }

    public function readMask(mixed $mask): int
    {
        return $this->readErrorMask($mask);
    }
}

final class ServiceErrorInputDouble
{
    public function __construct(private array $server)
    {
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function server(): array
    {
        return $this->server;
    }
}

final class ServiceErrorLogDouble
{
    public array $errors = [];

    public function logError(string $type, string $message, string $title, string $note = '', bool $isTrace = true, ?string $file = null): void
    {
        $this->errors[] = [$type, $message, $title, $note, $isTrace, $file];
    }
}

final class ServiceErrorEmailDouble
{
}

final class ServiceErrorLogWriterDouble
{
    public array $writes = [];

    public function write(string $message, int $messageType = 0, ?string $destination = null): bool
    {
        $this->writes[] = [$message, $messageType, $destination];

        return true;
    }
}

final class ServiceErrorFileStorageDouble
{
    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public function realPath(string $path): string|false
    {
        return realpath($path);
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function write(string $path, string $content): int|false
    {
        return file_put_contents($path, $content);
    }

    public function scanDirectory(string $path): array|false
    {
        return scandir($path);
    }

    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    public function delete(string $path): bool
    {
        return unlink($path);
    }

    public function changeMode(string $path, int $mode): bool
    {
        return chmod($path, $mode);
    }
}

final class ServiceErrorRuntimeDouble
{
    public ServiceErrorInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ServiceErrorInitializerDouble();
    }

    public function getInitializer(): ServiceErrorInitializerDouble
    {
        return $this->initializer;
    }

    public function isCli(): bool
    {
        return false;
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

final class ServiceErrorInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceErrorConfiguratorDouble
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

final class ServiceErrorConfigDouble implements ArrayAccess
{
    private array $values = [
        'DUPLICATE_BY_EMAIL' => [],
        'SYS_MASK' => E_ALL,
        'SYS_ERR' => [],
        'IGNORE_PATH' => [],
        'MAIL_TO' => null,
        'MAIL_FILE' => null,
        'SENT_TIME_LIMIT' => 0,
        'NAME_TO' => '',
    ];

    public function __construct(array $values = [])
    {
        $this->values = array_replace($this->values, $values);
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->values;
        }

        return $this->values[$key] ?? $default;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->values);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->values[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->values[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->values[$offset]);
    }
}
