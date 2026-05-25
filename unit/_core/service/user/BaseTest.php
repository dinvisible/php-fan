<?php

declare(strict_types=1);

use fan\core\service\user\base;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\service\config\row;
use fan\core\service\user;


if (!function_exists('array_val')) {
    function array_val(array|\ArrayAccess $arr, mixed $key, mixed $default = null): mixed
    {
        if ($key === null) {
            return $default;
        }

        return isset($arr[$key]) ? $arr[$key] : $default;
    }
}

class ServiceUserBaseTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/user/base.php';

    public function testFacadeAndConfigAreStoredOnlyOnce(): void
    {
        $engine = new ServiceUserBaseProbe('alice');
        $firstFacade = new ServiceUserFacadeDouble();
        $secondFacade = new ServiceUserFacadeDouble();
        $firstConfig = new row(['LOG_ERR_AUTH' => false]);
        $secondConfig = new row(['LOG_ERR_AUTH' => true]);

        $this->assertSame($engine, $engine->setFacade($firstFacade));
        $this->assertSame($engine, $engine->setFacade($secondFacade));
        $this->assertSame($engine, $engine->setConfig($firstConfig));
        $this->assertSame($engine, $engine->setConfig($secondConfig));

        $this->assertSame($firstFacade, $engine->facade());
        $this->assertSame($firstConfig, $engine->config());
    }

    public function testMagicSettersAndGettersUseSnakeCaseDataKeys(): void
    {
        $facade = new ServiceUserFacadeDouble();
        $engine = (new ServiceUserBaseProbe('alice'))->setFacade($facade);

        $this->assertSame($facade, $engine->setFirstName('Ada'));
        $this->assertSame($facade, $engine->setLastName('Lovelace'));

        $this->assertSame('Ada', $engine->getFirstName());
        $this->assertSame('Lovelace', $engine->getLastName());
        $this->assertSame([
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ], $engine->changed());
    }

    public function testFullNameRolesAndAllDataReflectCurrentState(): void
    {
        $engine = new ServiceUserBaseProbe('alice');
        $engine->setData([
            'title' => 'Dr.',
            'first_name' => 'Ada',
            'patronymic' => 'Byron',
            'last_name' => 'Lovelace',
            'roles' => ['admin' => null],
        ]);

        $this->assertSame('Dr. Ada Byron Lovelace', $engine->getFullName());
        $this->assertSame('Ada Byron Lovelace', $engine->getFullName(false));
        $this->assertSame([], $engine->getRoles());
        $this->assertSame(['admin' => null], $engine->getRoles(true));

        $allData = $engine->getAllData();
        $this->assertSame('Ada', $allData['first_name']);
        $this->assertArrayHasKey('visit_date', $allData);
        $this->assertNull($allData['visit_date']);
    }

    public function testPasswordAndVisitDateMarkEngineChangedAndValid(): void
    {
        $facade = new ServiceUserFacadeDouble();
        $engine = (new ServiceUserBaseProbe('alice'))
            ->setFacade($facade)
            ->setConfig(new row(['LOG_ERR_AUTH' => false]));

        $this->assertSame($facade, $engine->setPassword('secret'));
        $this->assertTrue($engine->isValid());
        $this->assertTrue($engine->checkPassword('secret'));
        $this->assertFalse($engine->checkPassword('wrong'));

        $this->assertSame($facade, $engine->setVisitDate('2026-05-26'));
        $this->assertSame('2026-05-26', $engine->getVisitDate());
        $this->assertSame([
            'password' => 'hash:secret',
            'visit_date' => '2026-05-26',
        ], $engine->changed());
    }

    public function testFailedPasswordLogUsesRequestInputRemoteAddress(): void
    {
        $facade = new ServiceUserFacadeDouble();
        $errorLogger = new ServiceUserErrorLoggerDouble();
        $engine = (new ServiceUserBaseProbe('alice'))
            ->setEngineDependencies(
                $errorLogger,
                new ServiceUserRequestInputDouble(['REMOTE_ADDR' => '203.0.113.9'])
            )
            ->setFacade($facade)
            ->setConfig(new row(['LOG_ERR_AUTH' => true]));
        $engine->setData([
            'password' => 'hash:secret',
        ]);

        $this->assertFalse($engine->checkPassword('wrong'));

        $this->assertCount(1, $errorLogger->loggedErrors);
        $this->assertStringContainsString('Client IP: 203.0.113.9', $errorLogger->loggedErrors[0][0]);
    }

    public function testFailedPasswordLogRequiresInjectedErrorLogger(): void
    {
        $engine = (new ServiceUserBaseProbe('alice'))
            ->setEngineDependencies(null, new ServiceUserRequestInputDouble(['REMOTE_ADDR' => '203.0.113.9']))
            ->setFacade(new ServiceUserFacadeDouble())
            ->setConfig(new row(['LOG_ERR_AUTH' => true]));
        $engine->setData([
            'password' => 'hash:secret',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error service is not configured for user engine.');

        $engine->checkPassword('wrong');
    }

    public function testInvalidMagicCallCreatesExceptionThroughFacade(): void
    {
        $facade = new ServiceUserFacadeDouble();
        $engine = (new ServiceUserBaseProbe('alice'))->setFacade($facade);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Incorrect call of User Engine!');

        try {
            $engine->resetEverything();
        } finally {
            $this->assertSame([
                ['Incorrect call of User Engine!', E_USER_ERROR, null],
            ], $facade->fatalExceptionCalls);
        }
    }

    public function testSourceUsesInjectedDependenciesInsteadOfFacadeLookup(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('setEngineDependencies', $source);
        $this->assertStringContainsString('protected function createUserFatalException(', $source);
        $this->assertStringContainsString('$this->facade->createUserFatalException($message, $code, $previous)', $source);
        $this->assertStringNotContainsString('getContainerService(', $source);
        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('safe_serializer::', $source);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
    }

    public function testSerializePayloadCanBeRestored(): void
    {
        $engine = new ServiceUserBaseProbe('alice');
        $engine->setData(['login' => 'alice']);
        $payload = $engine->__serialize();

        $restored = new ServiceUserBaseProbe('placeholder');
        $restored->__unserialize($payload);

        $this->assertSame('alice', $restored->getId());
        $this->assertSame('alice', $restored->getLogin());
        $this->assertTrue($restored->isNew());
    }

    public function testManualSnapshotSerializationUsesInjectedCodec(): void
    {
        $encodedState = null;
        $calls = [];
        $encoder = static function (mixed $state) use (&$encodedState, &$calls): string {
            $encodedState = $state;
            $calls[] = ['encode', array_keys($state)];

            return 'engine-snapshot';
        };
        $decoder = static function (string $payload, mixed $default = null) use (&$encodedState, &$calls): mixed {
            $calls[] = ['decode', $payload, $default];

            return $encodedState;
        };

        $engine = new ServiceUserBaseProbe('alice', $encoder, $decoder);
        $engine->setData(['login' => 'alice']);

        $this->assertSame('engine-snapshot', $engine->serialize());

        $restored = new ServiceUserBaseProbe('placeholder', $encoder, $decoder);
        $restored->unserialize('engine-snapshot');

        $this->assertSame('alice', $restored->getId());
        $this->assertSame('alice', $restored->getLogin());
        $this->assertSame([
            ['encode', ['flags', 'identifyer', 'data']],
            ['decode', 'engine-snapshot', []],
        ], $calls);
    }

    public function testMissingSnapshotEncoderFailsAtUseSite(): void
    {
        $engine = new ServiceUserBaseProbe('alice');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Snapshot encoder is not configured for user engine.');

        $engine->serialize();
    }

    public function testMissingSnapshotDecoderFailsAtUseSite(): void
    {
        $engine = new ServiceUserBaseProbe('alice');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Snapshot decoder is not configured for user engine.');

        $engine->unserialize('payload');
    }

    public function testMissingArrayValueReaderFailsAtUseSite(): void
    {
        $engine = new ServiceUserBaseBareProbe('alice');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Array value reader is not configured for user engine.');

        $engine->getId();
    }

    public function testMissingArrayAdducerFailsAtUseSite(): void
    {
        $engine = new ServiceUserBaseBareProbe('alice');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Array adducer is not configured for user engine.');

        $engine->adduceValue('role');
    }

    public function testMissingClassNameResolverFailsAtUseSite(): void
    {
        $engine = new ServiceUserBaseBareProbe('alice');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Class name resolver is not configured for user engine.');

        $engine->resolveClassName(new stdClass());
    }
}

final class ServiceUserBaseProbe extends base
{
    public function __construct(
        mixed $identifyer,
        ?callable $snapshotEncoder = null,
        ?callable $snapshotDecoder = null,
        ?callable $arrayValueReader = null,
        ?callable $arrayAdducer = null,
        ?callable $classNameResolver = null
    ) {
        parent::__construct(
            $identifyer,
            $snapshotEncoder,
            $snapshotDecoder,
            $arrayValueReader ?? static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default,
            $arrayAdducer ?? static fn(mixed $value): array => is_array($value) ? $value : (array)$value,
            $classNameResolver ?? static fn(object $object): string => get_class($object)
        );
    }

    public function makePasswordHash(string $password): string
    {
        return 'hash:' . $password;
    }

    public function facade(): ?object
    {
        return $this->facade;
    }

    public function config(): ?object
    {
        return $this->config;
    }

    public function changed(): array
    {
        return $this->changed;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    protected function _loadData(): bool
    {
        return false;
    }

    protected function _saveData(): bool
    {
        return true;
    }

    protected function _validateForSave(): bool
    {
        return true;
    }
}

final class ServiceUserBaseBareProbe extends base
{
    public function makePasswordHash(string $password): string
    {
        return 'hash:' . $password;
    }

    public function adduceValue(mixed $value): array
    {
        return ($this->arrayAdducer())($value);
    }

    public function resolveClassName(object $object): string
    {
        return $this->className($object);
    }

    protected function _loadData(): bool
    {
        return false;
    }

    protected function _saveData(): bool
    {
        return true;
    }

    protected function _validateForSave(): bool
    {
        return true;
    }
}

class ServiceUserFacadeDouble extends user
{
    public array $fatalExceptionCalls = [];

    public function __construct(private string $space = 'default')
    {
    }

    public function getUserSpace(): ?string
    {
        return $this->space;
    }

    public function createUserFatalException(string $message, int $code = E_USER_ERROR, ?Throwable $previous = null): Throwable
    {
        $this->fatalExceptionCalls[] = [$message, $code, $previous];

        return new RuntimeException($message, $code, $previous);
    }
}

final class ServiceUserRequestInputDouble
{
    public function __construct(private array $server)
    {
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }
}

final class ServiceUserErrorLoggerDouble
{
    public array $loggedErrors = [];

    public function logErrorMessage(string $message, string $title, string $note = ''): void
    {
        $this->loggedErrors[] = [$message, $title, $note];
    }
}
