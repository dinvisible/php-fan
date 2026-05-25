<?php

declare(strict_types=1);

use fan\core\adapter\request_input_native_environment;
use FanTest\_core\SourceFileContractTestCase;

final class AdapterRequestInputNativeEnvironmentTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/adapter/request_input_native_environment.php';

    private array $serverBackup = [];
    private array $getBackup = [];
    private bool $sessionWasSet = false;
    private mixed $sessionBackup = null;

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
        $this->getBackup = $_GET;
        $this->sessionWasSet = array_key_exists('_SESSION', $GLOBALS);
        $this->sessionBackup = $this->sessionWasSet ? $_SESSION : null;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
        $_GET = $this->getBackup;
        if ($this->sessionWasSet) {
            $_SESSION = $this->sessionBackup;
        } else {
            unset($_SESSION);
        }
    }

    public function testReadsGlobalArraysAndServerValues(): void
    {
        $_GET = ['page' => '2'];
        $_SERVER = ['HTTP_HOST' => 'example.test'];

        $environment = new request_input_native_environment();

        $this->assertSame(['page' => '2'], $environment->globalArray('_GET'));
        $this->assertSame('example.test', $environment->serverValue('HTTP_HOST'));
        $this->assertSame('fallback', $environment->serverValue('MISSING', 'fallback'));
    }

    public function testMutatesGlobalArraysAndSessionRoot(): void
    {
        $_GET = ['remove' => 'yes', 'keep' => 'ok'];
        unset($_SESSION);

        $environment = new request_input_native_environment();
        $environment->unsetGlobalValue('_GET', 'remove');
        $session =& $environment->sessionRoot();
        $session['token'] = 'abc';

        $this->assertSame(['keep' => 'ok'], $_GET);
        $this->assertSame(['token' => 'abc'], $_SESSION);
    }

    public function testSourceOwnsRawEnvironmentCalls(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/adapter/request_input_native_environment.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('$GLOBALS[$name]', $source);
        $this->assertStringContainsString('$_SERVER[$key]', $source);
        $this->assertStringContainsString('apache_request_headers()', $source);
        $this->assertStringContainsString('file_get_contents(\'php://input\')', $source);
    }
}
