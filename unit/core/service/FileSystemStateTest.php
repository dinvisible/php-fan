<?php

declare(strict_types=1);

use fan\core\service\file_system_state;
use FanTest\core\SourceFileContractTestCase;

class ServiceFileSystemStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/file_system_state.php';

    public function testStoresFileSystemInstancesByFullPath(): void
    {
        $state = new file_system_state();
        $fileSystem = new stdClass();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance('/tmp/source.csv'));

        $state->setInstance('/tmp/source.csv', $fileSystem);

        $this->assertTrue($state->hasInstances());
        $this->assertSame($fileSystem, $state->getInstance('/tmp/source.csv'));
    }

    public function testClearRemovesStoredInstances(): void
    {
        $state = new file_system_state();
        $state->setInstance('/tmp/source.csv', new stdClass());

        $state->clear();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance('/tmp/source.csv'));
    }
}
