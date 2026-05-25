<?php

declare(strict_types=1);

use fan\core\service\session\inbuilt;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\service\session;


class ServiceSessionInbuiltTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/session/inbuilt.php';

    public function testSetFacadeStoresOnlyFirstFacade(): void
    {
        $engine = (new ReflectionClass(inbuilt::class))->newInstanceWithoutConstructor();
        $firstFacade = new ServiceSessionFacadeDouble();
        $secondFacade = new ServiceSessionFacadeDouble();

        $this->assertSame($engine, $engine->setFacade($firstFacade));
        $this->assertSame($engine, $engine->setFacade($secondFacade));

        $property = new ReflectionProperty(inbuilt::class, 'facade');
        $this->assertSame($firstFacade, $property->getValue($engine));
        $this->assertSame([], $firstFacade->requestedServices);
        $this->assertSame([], $secondFacade->requestedServices);
    }

    public function testGetDataCreatesGroupAndReturnsWritableReference(): void
    {
        $input = new ServiceSessionInputDouble();
        $engine = $this->engine($input);

        $value =& $engine->getData('group', 'token');
        $this->assertNull($value);

        $value = 'abc';

        $this->assertSame(['group' => ['token' => 'abc']], $input->session);
        $this->assertSame('abc', $engine->getData('group', 'token'));
    }

    public function testGetRootReturnsWritableSessionReference(): void
    {
        $input = new ServiceSessionInputDouble(['existing' => true]);
        $engine = $this->engine($input);

        $root =& $engine->getRoot();
        $root['added'] = 5;

        $this->assertSame(['existing' => true, 'added' => 5], $input->session);
    }

    public function testConstructorAndSessionHelpersUseInjectedNativeSession(): void
    {
        $nativeSession = new ServiceSessionNativeSessionDouble('SID', PHP_SESSION_ACTIVE, false);
        $errors = new ServiceSessionErrorLoggerDouble();
        $engine = new inbuilt(
            'initial-session-id',
            new ServiceSessionInputDouble(),
            static fn(): ServiceSessionErrorLoggerDouble => $errors,
            $nativeSession
        );

        $this->assertSame([['id', 'initial-session-id'], ['start']], $nativeSession->calls);
        $this->assertSame('initial-session-id', $engine->getSessionId());
        $this->assertSame('SID', $engine->getSessionName());

        $this->assertSame($engine, $engine->setSessionId('next-session-id'));
        $this->assertSame('next-session-id', $engine->getSessionId());

        $this->assertSame($engine, $engine->destroy());
        $this->assertSame([['Failed to destroy native PHP session.', 'Session error', '', true, false]], $errors->messages);
    }

    public function testSourceUsesInjectedErrorFactoryDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('private mixed $errorFactory = null;', $source);
        $this->assertStringContainsString('private ?object $nativeSession = null;', $source);
        $this->assertStringContainsString('return ($this->errorFactory)();', $source);
        $this->assertStringContainsString('$this->nativeSession()', $source);
        $this->assertStringNotContainsString('call_user_func', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:session_start|session_id|session_name|session_status|session_destroy)\s*\(/',
            $source
        );
    }

    private function engine(ServiceSessionInputDouble $input): inbuilt
    {
        $engine = (new ReflectionClass(inbuilt::class))->newInstanceWithoutConstructor();
        $property = new ReflectionProperty(inbuilt::class, 'input');
        $property->setValue($engine, $input);

        return $engine;
    }
}

class ServiceSessionFacadeDouble extends session
{
    public array $requestedServices = [];

    private object $requestInput;

    public function __construct()
    {
        $this->requestInput = new ServiceSessionInputDouble();
    }

    public function getContainerService(string $serviceName, mixed ...$arguments): mixed
    {
        $this->requestedServices[$serviceName] = ($this->requestedServices[$serviceName] ?? 0) + 1;

        return $serviceName === 'request_input' ? $this->requestInput : null;
    }
}

class ServiceSessionInputDouble
{
    public function __construct(public array $session = [])
    {
    }

    public function &sessionValue(string $group, string $name): mixed
    {
        if (!isset($this->session[$group]) || !is_array($this->session[$group])) {
            $this->session[$group] = [$name => null];
        } elseif (!array_key_exists($name, $this->session[$group])) {
            $this->session[$group][$name] = null;
        }

        return $this->session[$group][$name];
    }

    public function &sessionRoot(): array
    {
        return $this->session;
    }
}

final class ServiceSessionNativeSessionDouble
{
    public array $calls = [];

    public function __construct(
        private string|false $name = 'PHPSESSID',
        private int $status = PHP_SESSION_NONE,
        private bool $destroyResult = true,
        private string|false $id = ''
    ) {
    }

    public function start(): bool
    {
        $this->calls[] = ['start'];

        return true;
    }

    public function id(?string $id = null): string|false
    {
        if ($id !== null) {
            $this->calls[] = ['id', $id];
            $this->id = $id;
        }

        return $this->id;
    }

    public function name(): string|false
    {
        $this->calls[] = ['name'];

        return $this->name;
    }

    public function status(): int
    {
        $this->calls[] = ['status'];

        return $this->status;
    }

    public function destroy(): bool
    {
        $this->calls[] = ['destroy'];

        return $this->destroyResult;
    }
}

final class ServiceSessionErrorLoggerDouble
{
    public array $messages = [];

    public function logErrorMessage(
        string $message,
        string $title = '',
        string $note = '',
        bool $fixPosition = false,
        bool $allowDebug = true
    ): void {
        $this->messages[] = [$message, $title, $note, $fixPosition, $allowDebug];
    }
}
