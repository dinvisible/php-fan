<?php

declare(strict_types=1);

use fan\core\service\session_state;
use FanTest\core\SourceFileContractTestCase;

class ServiceSessionStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/session_state.php';

    public function testStoresSessionInstancesWithoutStaticSessionServiceState(): void
    {
        $state = self::sessionState();
        $session = new stdClass();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance('app', 'frontend'));

        $state->setInstance('app', 'frontend', $session);

        $this->assertTrue($state->hasInstances());
        $this->assertSame($session, $state->getInstance('app', 'frontend'));
    }

    public function testStoresEngineRequestFlagsAndBufferData(): void
    {
        $state = self::sessionState();
        $engine = new stdClass();
        $request = new stdClass();

        $state->setEngine($engine);
        $state->setRequestService($request);
        $state->setByCookie(true);
        $state->setExpired(true);
        $state->setBufferData('flash', 'message');

        $this->assertSame($engine, $state->getEngine());
        $this->assertSame($request, $state->getRequestService());
        $this->assertTrue($state->isByCookie());
        $this->assertTrue($state->isExpired());
        $this->assertSame('message', $state->getBufferData('flash'));
        $this->assertSame('fallback', $state->getBufferData('missing', 'fallback'));
    }

    public function testClearResetsRequestScopedState(): void
    {
        $state = self::sessionState();
        $state->setInstance('app', 'frontend', new stdClass());
        $state->setEngine(new stdClass());
        $state->setRequestService(new stdClass());
        $state->setByCookie(true);
        $state->setExpired(true);
        $state->setBufferData('flash', 'message');

        $state->clear();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getEngine());
        $this->assertNull($state->getRequestService());
        $this->assertNull($state->isByCookie());
        $this->assertFalse($state->isExpired());
        $this->assertNull($state->getBufferData('flash'));
    }

    public function testExpiredReferencePreservesSessionStorageLink(): void
    {
        $state = self::sessionState();
        $stored = false;

        $state->setExpiredReference($stored);
        $state->setExpired(true);

        $this->assertTrue($stored);
        $this->assertTrue($state->isExpired());
    }

    public function testSourceUsesInjectedArrayValueReader(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('public function __construct(callable $arrayValueReader)', $source);
        $this->assertStringContainsString('return ($this->arrayValueReader)($this->bufferData, $key, $default);', $source);
        $this->assertStringNotContainsString('array_val(', $source);
    }

    private static function sessionState(): session_state
    {
        return new session_state(static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default);
    }
}
