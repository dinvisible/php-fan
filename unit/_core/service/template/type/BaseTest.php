<?php

declare(strict_types=1);

use FanTest\_core\SourceFileContractTestCase;

class GeneratedPendingServiceTemplateTypeBaseTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/template/type/base.php';

    public function testConstructorProvidesLegacyBlockAliasForTemplates(): void
    {
        $this->assertStringContainsString('$this->tplVar[\'oBlock\'] = $block;', $this->sourceCode());
    }
}
