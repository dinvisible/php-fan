<?php

declare(strict_types=1);

namespace {
    use fan\core\service\session\pear;
    use FanTest\_core\SourceFileContractTestCase;

    class ServiceSessionPearTest extends SourceFileContractTestCase
    {
        protected const SOURCE_FILE = '_core/service/session/pear.php';

        public function testGetSessionIdDelegatesToHttpSession(): void
        {
            $httpSession = new ServiceSessionPearHttpSessionDouble();
            $httpSession->id = 'custom-id';
            $engine = $this->engine($httpSession);

            $this->assertSame('custom-id', $engine->getSessionId());
        }

        public function testGetSetAndRemoveDelegateToHttpSessionStore(): void
        {
            $httpSession = new ServiceSessionPearHttpSessionDouble();
            $engine = $this->engine($httpSession);

            $this->assertSame('fallback', $engine->get('missing', 'fallback'));

            $engine->set('token', 'abc');
            $this->assertSame('abc', $engine->get('token'));

            $engine->remove('token');
            $this->assertNull($engine->get('token', 'fallback'));
            $this->assertSame(['token' => null], $httpSession->data);
        }

        public function testRemoveAllAndDestroyDelegateToHttpSession(): void
        {
            $httpSession = new ServiceSessionPearHttpSessionDouble();
            $httpSession->data = ['token' => 'abc'];
            $engine = $this->engine($httpSession);

            $engine->remove_all();
            $engine->destroy();

            $this->assertSame([], $httpSession->data);
            $this->assertSame(1, $httpSession->clearCalls);
            $this->assertSame(1, $httpSession->destroyCalls);
        }

        public function testConstructorUsesInjectedDatabaseConfigAndRequest(): void
        {
            $loader = new ServiceSessionPearLoaderDouble();
            $httpSession = new ServiceSessionPearHttpSessionDouble();

            new pear(
                [
                    'IS_DATABASE' => true,
                    'CONNECTION' => 'main',
                    'TABLE' => 'session_store',
                    'SESSION_NAME' => 'SID',
                ],
                new ServiceSessionPearDatabaseConfigDouble(),
                new ServiceSessionPearRequestDouble(['SID' => 'abc123']),
                $loader,
                $httpSession
            );

            $this->assertSame(1, $loader->loadCalls);
            $this->assertSame([
                [
                    'DB',
                    [
                        'dsn' => 'mysqli://user:secret@localhost/fan',
                        'table' => 'session_store',
                    ],
                ],
            ], $httpSession->containerCalls);
            $this->assertSame([true], $httpSession->useCookiesCalls);
            $this->assertSame([['SID', 'abc123']], $httpSession->startCalls);
        }

        public function testSourceUsesInjectedLoaderInsteadOfDirectAdapterCall(): void
        {
            $source = $this->sourceCode();

            $this->assertStringContainsString('$sessionSupportLoader', $source);
            $this->assertStringContainsString('$this->httpSession()', $source);
            $this->assertStringNotContainsString('pear_http_session::ensureAvailable', $source);
            $this->assertStringNotContainsString('pear_http_session_loader', $source);
            $this->assertStringNotContainsString('HTTP_Session::', $source);
            $this->assertStringNotContainsString('fan\\project\\adapter', $source);
        }

        public function testConstructorRequiresInjectedLoader(): void
        {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('PEAR HTTP session loader is not configured.');

            new pear([], null, new ServiceSessionPearRequestDouble([]));
        }

        public function testConstructorRequiresInjectedHttpSessionAdapter(): void
        {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('HTTP session adapter is not configured for PEAR session engine.');

            new pear([], null, new ServiceSessionPearRequestDouble([]), new ServiceSessionPearLoaderDouble());
        }

        private function engine(?ServiceSessionPearHttpSessionDouble $httpSession = null): pear
        {
            return new pear(
                [],
                null,
                new ServiceSessionPearRequestDouble([]),
                new ServiceSessionPearLoaderDouble(),
                $httpSession ?? new ServiceSessionPearHttpSessionDouble()
            );
        }
    }

    final class ServiceSessionPearLoaderDouble
    {
        public int $loadCalls = 0;

        public function load(): void
        {
            $this->loadCalls++;
        }
    }

    final class ServiceSessionPearDatabaseConfigDouble implements ArrayAccess
    {
        public function offsetExists(mixed $offset): bool
        {
            return $offset === 'DATABASES';
        }

        public function offsetGet(mixed $offset): mixed
        {
            return [
                'main' => [
                    'DRIVER' => 'mysqli',
                    'USER' => 'user',
                    'PASSWORD' => 'secret',
                    'HOST' => 'localhost',
                    'DATABASE' => 'fan',
                ],
            ];
        }

        public function offsetSet(mixed $offset, mixed $value): void
        {
        }

        public function offsetUnset(mixed $offset): void
        {
        }
    }

    final class ServiceSessionPearRequestDouble
    {
        public function __construct(private array $data)
        {
        }

        public function get(string $key): mixed
        {
            return $this->data[$key] ?? null;
        }
    }

    final class ServiceSessionPearHttpSessionDouble
    {
        public array $data = [];

        public int $clearCalls = 0;

        public int $destroyCalls = 0;

        public string $id = 'pear-session-id';

        public array $containerCalls = [];

        public array $startCalls = [];

        public array $useCookiesCalls = [];

        public function setContainer(string $type, array $param): void
        {
            $this->containerCalls[] = [$type, $param];
        }

        public function useCookies(bool $useCookies): void
        {
            $this->useCookiesCalls[] = $useCookies;
        }

        public function start(string $sessionName, mixed $sid = null): void
        {
            $this->startCalls[] = [$sessionName, $sid];
        }

        public function id(): string
        {
            return $this->id;
        }

        public function get(string $key, ?string $defaultValue = null): mixed
        {
            return array_key_exists($key, $this->data) ? $this->data[$key] : $defaultValue;
        }

        public function set(string $key, mixed $value): void
        {
            $this->data[$key] = $value;
        }

        public function clear(): void
        {
            $this->clearCalls++;
            $this->data = [];
        }

        public function destroy(): void
        {
            $this->destroyCalls++;
        }
    }
}
