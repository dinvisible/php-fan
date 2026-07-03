<?php

declare(strict_types=1);

use fan\core\service\timer;
use FanTest\core\SourceFileContractTestCase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use fan\core\base\service;
use fan\core\base\timer_program;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;
use fan\model\timer_program\row;
use fan\project\timer\ServiceTimerInjectedProgram;


if (!class_exists('bootstrap', false)) {
    class bootstrap
    {
        public static function parsePath(string $path): string
        {
            return strtr($path, ['{PROJECT}' => '/project']);
        }
    }
}

if (!class_exists('fan\model\timer_program\row', false)) {
    eval('
        namespace fan\model\timer_program {
            class row extends \fan\core\base\timer_program
            {
                public array $setFieldsCalls = [];
                public array $startTimeCalls = [];
                public int $saveCalls = 0;
                public int $deleteCalls = 0;
                private object $entity;

                public function __construct(
                    private string $className = "ServiceTimerInjectedProgram",
                    private string $methodName = "run",
                    private array $parameters = []
                ) {
                    $this->entity = new \ServiceTimerRowEntityDouble();
                }

                public function get_class_name(): string
                {
                    return $this->className;
                }

                public function get_period(mixed $default = null, bool $raw = false): int
                {
                    return 0;
                }

                public function get_is_active(): bool
                {
                    return false;
                }

                public function setFields(array $fields, bool $strict = false): void
                {
                    $this->setFieldsCalls[] = [$fields, $strict];
                }

                public function get_start_time(): string
                {
                    return "2026-06-01 00:00:00";
                }

                public function save(): void
                {
                    $this->saveCalls++;
                }

                public function getEntity(): object
                {
                    return $this->entity;
                }

                public function get_method_name(): string
                {
                    return $this->methodName;
                }

                public function get_parameters(): array
                {
                    return $this->parameters;
                }

                public function checkIsLoad(): bool
                {
                    return true;
                }

                public function delete(): void
                {
                    $this->deleteCalls++;
                }

                public function set_start_time(mixed $startTime): void
                {
                    $this->startTimeCalls[] = $startTime;
                }
            }
        }
    ');
}

if (!class_exists('fan\project\timer\ServiceTimerInjectedProgram', false)) {
    eval('
        namespace fan\project\timer {
            class ServiceTimerInjectedProgram extends \fan\core\base\timer_program
            {
                public array $runCalls = [];
                public array $dependencyCalls = [];

                public function setTimerDependencies(object $error, object $email): void
                {
                    $this->dependencyCalls[] = [$error, $email];
                }

                public function run(mixed $payload): void
                {
                    $this->runCalls[] = $payload;
                }
            }
        }
    ');
}

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ServiceTimerTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/timer.php';

    public function testExecBackBinReturnsFalseWhenExecutionIsDisabled(): void
    {
        $timer = $this->timer([
            'ENABLE_EXEC' => false,
        ]);

        $this->assertFalse($timer->execBackBin('php worker.php'));
        $this->assertSame([], $timer->executedCommands);
    }

    public function testBackgroundExecutionEscapesEveryArgument(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString("array_map('escapeshellarg', \$arguments)", $source);
        $this->assertStringContainsString("getConfig('EXECUTABLE_ALLOWLIST', [])", $source);
        $this->assertStringNotContainsString("exec(\$cmd . ' > /dev/null &')", $source);
    }

    public function testExecBackPhpChargesProgramWithoutBackgroundWhenExecutionIsDisabled(): void
    {
        $timer = $this->timer([
            'ENABLE_EXEC' => false,
        ]);

        $this->assertSame('pid-1', $timer->execBackPhp('Cleanup', 'run', ['force' => true], 3));
        $this->assertSame([
            [
                'startTime' => 0,
                'className' => 'Cleanup',
                'methodName' => 'run',
                'param' => ['force' => true],
                'period' => 0,
                'overcall' => 3,
                'isShell' => false,
            ],
        ], $timer->chargedPrograms);
        $this->assertSame([], $timer->executedCommands);
    }

    public function testCommandLineUsesConfiguredInterpreterAndParsedScriptPath(): void
    {
        $timer = $this->timer([
            'PHP_INTERPRETER' => '/usr/bin/php',
            'CRON_FILE' => '{PROJECT}/timer/cron.php',
        ]);

        $this->assertSame('/usr/bin/php /project/timer/cron.php ', $timer->exposeGetCommandLine('CRON_FILE'));
    }

    public function testRunBackgroundDelegatesToExecBackBinWithPid(): void
    {
        $timer = $this->timer([
            'ENABLE_EXEC' => true,
            'PHP_INTERPRETER' => '/usr/bin/php',
            'BGR_FILE' => '{PROJECT}/timer/background.php ',
        ]);

        $this->assertTrue($timer->exposeRunBackground('42'));
        $this->assertSame(['/usr/bin/php /project/timer/background.php  42'], $timer->executedCommands);
    }

    public function testModifyProgramUpdatesOnlyProvidedFieldsAndSavesRow(): void
    {
        $timer = $this->timer();
        $row = new ServiceTimerProgramRowDouble();

        $timer->exposeModifyProgram($row, ['a' => 1], null, 5);

        $this->assertSame([['a' => 1]], $row->parametersCalls);
        $this->assertSame([], $row->periodCalls);
        $this->assertSame([5], $row->overcallLimitCalls);
        $this->assertSame(1, $row->saveCalls);
    }

    public function testRunProgramUsesInjectedTimerProgramFactory(): void
    {
        $program = new ServiceTimerInjectedProgram();
        $factoryCalls = [];
        $error = new ServiceTimerErrorDouble();
        $email = (object)['name' => 'timer_email'];
        $row = new row('ServiceTimerInjectedProgram', 'run', ['payload']);
        $timer = $this->timer();
        $timer->setBaseNamespace('\fan\project\timer');
        $timer->setTimerDependencies(
            new ServiceTimerRuntimeDouble(),
            static fn(?string $date = null, mixed $format = null): object => new ServiceTimerDateDouble(),
            static fn(): object => new stdClass(),
            static fn(): object => $error,
            static fn(): object => new ServiceTimerLogDouble(),
            static fn(string $name): object => $email,
            static function (string $className) use (&$factoryCalls, $program): object {
                $factoryCalls[] = $className;

                return $program;
            }
        );

        $timer->exposeRunProgram($row);

        $this->assertSame(['\fan\project\timer\ServiceTimerInjectedProgram'], $factoryCalls);
        $this->assertSame($row, $program->getTimerRow());
        $this->assertSame(['payload'], $program->runCalls);
        $this->assertSame([[$error, $email]], $program->dependencyCalls);
        $this->assertSame(1, $row->saveCalls);
        $this->assertSame(1, $row->deleteCalls);
        $this->assertSame(2, $row->getEntity()->getConnection()->commitCalls);
        $this->assertSame([], $error->messages);
    }

    public function testRunProgramUsesInjectedClassAvailabilityCheck(): void
    {
        $checkedClasses = [];
        $factoryCalls = [];
        $error = new ServiceTimerErrorDouble();
        $row = new row('UnavailableProgram', 'run', []);
        $timer = $this->timer();
        $timer->setBaseNamespace('\fan\project\timer');
        $timer->setTimerDependencies(
            new ServiceTimerRuntimeDouble(),
            static fn(?string $date = null, mixed $format = null): object => new ServiceTimerDateDouble(),
            static fn(): object => new stdClass(),
            static fn(): object => $error,
            static fn(): object => new ServiceTimerLogDouble(),
            static fn(string $name): object => (object)['name' => $name],
            static function (string $className) use (&$factoryCalls): object {
                $factoryCalls[] = $className;

                return new stdClass();
            },
            static function (string $className) use (&$checkedClasses): bool {
                $checkedClasses[] = $className;

                return false;
            }
        );

        $timer->exposeRunProgram($row);

        $this->assertSame(['\fan\project\timer\UnavailableProgram'], $checkedClasses);
        $this->assertSame([], $factoryCalls);
        $this->assertSame([
            [
                'Class "\fan\project\timer\UnavailableProgram" for timer doesn\'t exists.',
                'Error run timer proggamm',
            ],
        ], $error->messages);
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseFunctionAliases();

        $runtime = new ServiceTimerRuntimeDouble();
        $config = new ServiceTimerConfigDouble([
            'ENTITY' => 'timer_program',
            'TIMER_DIR' => '{PROJECT}/timer/',
            'BASE_NS' => '\fan\project\timer',
        ]);
        $configurator = new ServiceTimerConfiguratorDouble($config);
        $cacheFactoryCalls = [];
        $programFactoryCalls = [];

        $timer = new ServiceTimerConstructorProbe(
            $runtime,
            fn(?string $date = null, mixed $format = null): object => new stdClass(),
            fn(): object => new stdClass(),
            fn(): object => new stdClass(),
            fn(): object => new stdClass(),
            fn(string $name): object => (object)['name' => $name],
            static function (string $className) use (&$programFactoryCalls): object {
                $programFactoryCalls[] = $className;

                return new stdClass();
            },
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            }
        );

        $this->assertSame([ServiceTimerConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$timer], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceTimerConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame(['{PROJECT}/timer/'], $runtime->paths);
        $this->assertSame([], $cacheFactoryCalls);
        $this->assertSame([], $programFactoryCalls);
    }

    private function timer(array $config = []): ServiceTimerProbe
    {
        $timer = new ServiceTimerProbe();
        $property = new ReflectionProperty(service::class, 'config');
        $property->setValue($timer, new ServiceTimerConfigDouble($config));
        $timer->setTimerDependencies(new ServiceTimerRuntimeDouble());

        return $timer;
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $this->assertStringNotContainsString('getContainerService(', $this->sourceCode());
        $this->assertStringNotContainsString('containerService(', $this->sourceCode());
        $this->assertStringContainsString('public function __construct(', $this->sourceCode());
        $this->assertStringContainsString('parent::__construct(true, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);', $this->sourceCode());
        $this->assertStringNotContainsString('parent::__construct();', $this->sourceCode());
        $this->assertStringContainsString('$timerProgramFactory', $this->sourceCode());
        $this->assertStringContainsString('private ?\Closure $timerClassExists = null;', $this->sourceCode());
        $this->assertStringContainsString('$this->timerClassExists($className)', $this->sourceCode());
        $this->assertStringNotContainsString('new $className()', $this->sourceCode());
        $this->assertStringNotContainsString('if (!class_exists($className))', $this->sourceCode());
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

final class ServiceTimerProbe extends timer
{
    public array $chargedPrograms = [];
    public array $executedCommands = [];

    public function __construct()
    {
    }

    public function chargeProgram(
        mixed $startTime,
        string $className,
        string $methodName,
        array $param,
        int|float $period = 0,
        int|float|null $overcall = null,
        bool $isShell = true
    ): mixed {
        $this->chargedPrograms[] = [
            'startTime' => $startTime,
            'className' => $className,
            'methodName' => $methodName,
            'param' => $param,
            'period' => $period,
            'overcall' => $overcall,
            'isShell' => $isShell,
        ];

        return 'pid-' . count($this->chargedPrograms);
    }

    public function execBackBin(string $cmd): bool
    {
        if (!$this->getConfig('ENABLE_EXEC')) {
            return parent::execBackBin($cmd);
        }

        $this->executedCommands[] = $cmd;

        return true;
    }

    public function exposeGetCommandLine(string $key): string
    {
        return $this->_getCommandLine($key);
    }

    public function exposeRunBackground(string $pid): bool
    {
        return $this->_runBackground($pid);
    }

    public function exposeModifyProgram(
        timer_program $row,
        mixed $param,
        int|float|null $period,
        int|float|null $overcall
    ): void {
        $this->_modifyProgram($row, $param, $period, $overcall);
    }

    public function exposeRunProgram(object $row): void
    {
        $this->_runProgram($row);
    }

    public function setBaseNamespace(string $baseNS): void
    {
        $this->baseNS = $baseNS;
    }
}

final class ServiceTimerRuntimeDouble
{
    public array $paths = [];
    public ServiceTimerInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ServiceTimerInitializerDouble();
    }

    public function parsePath(string $path): string
    {
        $this->paths[] = $path;

        return strtr($path, ['{PROJECT}' => '/project']);
    }

    public function getInitializer(): ServiceTimerInitializerDouble
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
}

final class ServiceTimerInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceTimerConfiguratorDouble
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

final class ServiceTimerConstructorProbe extends timer
{
    public function __construct(
        ?object $runtime,
        ?callable $dateFactory,
        ?callable $entityFactory,
        ?callable $errorFactory,
        ?callable $logFactory,
        ?callable $emailFactory,
        ?callable $programFactory,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory
    )
    {
        parent::__construct(
            $runtime,
            $dateFactory,
            $entityFactory,
            $errorFactory,
            $logFactory,
            $emailFactory,
            $programFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory
        );
    }
}

final class ServiceTimerProgramRowDouble extends timer_program
{
    public array $parametersCalls = [];
    public array $periodCalls = [];
    public array $overcallLimitCalls = [];
    public int $saveCalls = 0;

    public function set_parameters(array $parameters): void
    {
        $this->parametersCalls[] = $parameters;
    }

    public function set_period(int|float $period): void
    {
        $this->periodCalls[] = $period;
    }

    public function set_overcall_limit(int|float $overcall): void
    {
        $this->overcallLimitCalls[] = $overcall;
    }

    public function save(): void
    {
        $this->saveCalls++;
    }
}

final class ServiceTimerConfigDouble
{
    public function __construct(private array $data = [])
    {
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}

final class ServiceTimerDateDouble
{
    public function getDifference(string $date): int
    {
        return 0;
    }

    public function shiftDate(int|float $shift): string
    {
        return '2026-06-01 00:00:00';
    }
}

final class ServiceTimerLogDouble
{
    public array $messages = [];

    public function logMessage(...$arguments): void
    {
        $this->messages[] = $arguments;
    }
}

final class ServiceTimerErrorDouble
{
    public array $messages = [];
    public array $emails = [];

    public function logErrorMessage(...$arguments): void
    {
        $this->messages[] = $arguments;
    }

    public function makeErrorEmail(...$arguments): void
    {
        $this->emails[] = $arguments;
    }
}

final class ServiceTimerRowEntityDouble
{
    private ServiceTimerConnectionDouble $connection;

    public function __construct()
    {
        $this->connection = new ServiceTimerConnectionDouble();
    }

    public function getConnection(): ServiceTimerConnectionDouble
    {
        return $this->connection;
    }
}

final class ServiceTimerConnectionDouble
{
    public int $commitCalls = 0;

    public function commit(): void
    {
        $this->commitCalls++;
    }
}
