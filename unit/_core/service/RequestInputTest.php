<?php

declare(strict_types=1);

use fan\core\service\request_input;
use fan\core\service\request_input_source;
use fan\core\adapter\request_input_globals;
use fan\core\adapter\request_input_native_environment;
use FanTest\_core\SourceFileContractTestCase;

class ServiceRequestInputTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/request_input.php';

    private array $serverBackup = [];
    private array $getBackup = [];
    private array $requestBackup = [];
    private bool $testGlobalWasSet = false;
    private mixed $testGlobalBackup = null;
    private bool $sessionWasSet = false;
    private mixed $sessionBackup = null;

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
        $this->getBackup = $_GET;
        $this->requestBackup = $_REQUEST;
        $this->testGlobalWasSet = array_key_exists('TEST_GLOBAL_VALUE', $GLOBALS);
        $this->testGlobalBackup = $this->testGlobalWasSet ? $GLOBALS['TEST_GLOBAL_VALUE'] : null;
        $this->sessionWasSet = array_key_exists('_SESSION', $GLOBALS);
        $this->sessionBackup = $this->sessionWasSet ? $_SESSION : null;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
        $_GET = $this->getBackup;
        $_REQUEST = $this->requestBackup;
        if ($this->testGlobalWasSet) {
            $GLOBALS['TEST_GLOBAL_VALUE'] = $this->testGlobalBackup;
        } else {
            unset($GLOBALS['TEST_GLOBAL_VALUE']);
        }
        if ($this->sessionWasSet) {
            $_SESSION = $this->sessionBackup;
        } else {
            unset($_SESSION);
        }
    }

    public function testGlobalArrayAndGetExposeSuperglobalArrays(): void
    {
        $_GET = ['page' => '2'];

        $input = $this->input();

        $this->assertSame(['page' => '2'], $input->globalArray('_GET'));
        $this->assertSame(['page' => '2'], $input->get());
    }

    public function testRequestAndGlobalValueExposeSelectedGlobalState(): void
    {
        $_REQUEST = ['id' => '42'];
        $GLOBALS['TEST_GLOBAL_VALUE'] = '5.22';

        $input = $this->input();

        $this->assertSame(['id' => '42'], $input->request());
        $this->assertSame('42', $input->requestValue('id'));
        $this->assertSame('fallback', $input->requestValue('missing', 'fallback'));
        $this->assertSame('5.22', $input->globalValue('TEST_GLOBAL_VALUE'));
        $this->assertSame('fallback', $input->globalValue('MISSING_GLOBAL', 'fallback'));
    }

    public function testUnsetGlobalValueMutatesSelectedGlobalArray(): void
    {
        $_GET = ['remove' => 'yes', 'keep' => 'ok'];

        $this->input()->unsetGlobalValue('_GET', 'remove');

        $this->assertSame(['keep' => 'ok'], $_GET);
    }

    public function testServerValuesAndHeadersAreBuiltFromServerData(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'example.test',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ];

        $input = $this->input();

        $this->assertSame('example.test', $input->serverValue('HTTP_HOST'));
        $this->assertSame('fallback', $input->serverValue('MISSING', 'fallback'));
        $this->assertSame([
            'Host' => 'example.test',
            'X-Requested-With' => 'XMLHttpRequest',
        ], $input->headers());
    }

    public function testSessionRootAndValueExposeWritableReferences(): void
    {
        unset($_SESSION);
        $input = $this->input();

        $root =& $input->sessionRoot();
        $root['existing'] = true;

        $value =& $input->sessionValue('group', 'token');
        $this->assertNull($value);

        $value = 'abc';

        $this->assertSame([
            'existing' => true,
            'group' => ['token' => 'abc'],
        ], $_SESSION);
        $this->assertSame('abc', $input->sessionValue('group', 'token'));
    }

    public function testSessionValueNormalizesMissingOrNonArrayGroups(): void
    {
        $_SESSION = ['group' => 'legacy-scalar'];

        $value =& $this->input()->sessionValue('group', 'token');
        $value = 'abc';

        $this->assertSame(['group' => ['token' => 'abc']], $_SESSION);
    }

    public function testRawPostReadsFromInjectedSource(): void
    {
        $input = new request_input(new class {
            public function rawPost(): string
            {
                return 'payload';
            }
        });

        $this->assertSame('payload', $input->rawPost());
    }

    private function input(): request_input
    {
        return new request_input(new request_input_source(new request_input_globals(new request_input_native_environment())));
    }
}
