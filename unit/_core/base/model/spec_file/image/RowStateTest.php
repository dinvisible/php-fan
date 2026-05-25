<?php

declare(strict_types=1);

use fan\core\base\model\spec_file\image\row_state;
use FanTest\_core\SourceFileContractTestCase;

final class BaseModelSpecFileImageRowStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/base/model/spec_file/image/row_state.php';

    public function testStoresTemplatesByEntityName(): void
    {
        $state = new row_state();
        $template = new stdClass();

        $this->assertFalse($state->hasTemplate('gallery'));
        $this->assertNull($state->getTemplate('gallery'));

        $state->setTemplate('gallery', $template);

        $this->assertTrue($state->hasTemplate('gallery'));
        $this->assertSame($template, $state->getTemplate('gallery'));
        $this->assertSame(['gallery' => $template], $state->templates());
    }

    public function testClearRemovesCachedTemplates(): void
    {
        $state = new row_state();
        $state->setTemplate('gallery', new stdClass());

        $state->clear();

        $this->assertFalse($state->hasTemplate('gallery'));
        $this->assertSame([], $state->templates());
    }
}
