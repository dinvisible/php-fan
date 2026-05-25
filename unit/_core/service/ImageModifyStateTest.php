<?php

declare(strict_types=1);

use fan\core\service\image_modify_state;
use FanTest\_core\SourceFileContractTestCase;

class ServiceImageModifyStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/image_modify_state.php';

    public function testStoresImageInstancesByClassName(): void
    {
        $state = new image_modify_state();
        $image = new stdClass();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance('fan\project\service\image_modify'));

        $state->setInstance('fan\project\service\image_modify', $image);

        $this->assertTrue($state->hasInstances());
        $this->assertSame($image, $state->getInstance('fan\project\service\image_modify'));
    }

    public function testClearRemovesStoredInstances(): void
    {
        $state = new image_modify_state();
        $state->setInstance('fan\project\service\image_modify', new stdClass());

        $state->clear();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance('fan\project\service\image_modify'));
    }
}
