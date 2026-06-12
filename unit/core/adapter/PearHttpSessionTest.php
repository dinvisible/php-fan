<?php

declare(strict_types=1);

if (!class_exists('HTTP_Session', false)) {
    final class HTTP_Session
    {
        public static array $data = [];

        public static string $id = 'pear-session-id';

        public static array $calls = [];

        public static function setContainer(string $type, array $param): void
        {
            self::$calls[] = ['setContainer', $type, $param];
        }

        public static function useCookies(bool $useCookies): void
        {
            self::$calls[] = ['useCookies', $useCookies];
        }

        public static function start(string $sessionName, mixed $sid = null): void
        {
            self::$calls[] = ['start', $sessionName, $sid];
        }

        public static function id(): string
        {
            return self::$id;
        }

        public static function get(string $key, ?string $defaultValue = null): mixed
        {
            return self::$data[$key] ?? $defaultValue;
        }

        public static function set(string $key, mixed $value): void
        {
            self::$data[$key] = $value;
        }

        public static function clear(): void
        {
            self::$calls[] = ['clear'];
            self::$data = [];
        }

        public static function destroy(): void
        {
            self::$calls[] = ['destroy'];
        }

        public static function reset(): void
        {
            self::$data = [];
            self::$id = 'pear-session-id';
            self::$calls = [];
        }
    }
}

use fan\core\adapter\pear_http_session;
use FanTest\core\SourceFileContractTestCase;

final class AdapterPearHttpSessionTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/adapter/pear_http_session.php';

    protected function setUp(): void
    {
        HTTP_Session::reset();
    }

    public function testAdapterDelegatesToPearHttpSessionStatics(): void
    {
        $adapter = new pear_http_session();

        $adapter->setContainer('DB', ['table' => 'sessions']);
        $adapter->useCookies(true);
        $adapter->start('SID', 'abc123');
        $adapter->set('token', 'secret');
        HTTP_Session::$id = 'id-1';

        $this->assertSame('id-1', $adapter->id());
        $this->assertSame('secret', $adapter->get('token'));
        $this->assertSame('fallback', $adapter->get('missing', 'fallback'));

        $adapter->clear();
        $adapter->destroy();

        $this->assertSame([], HTTP_Session::$data);
        $this->assertSame([
            ['setContainer', 'DB', ['table' => 'sessions']],
            ['useCookies', true],
            ['start', 'SID', 'abc123'],
            ['clear'],
            ['destroy'],
        ], HTTP_Session::$calls);
    }

    public function testSourceKeepsPearStaticAccessInsideAdapterBoundary(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('private \Closure $staticCall;', $source);
        $this->assertStringContainsString('$className::$method(...$arguments)', $source);
        $this->assertStringNotContainsString('HTTP_Session::', $source);
    }
}
