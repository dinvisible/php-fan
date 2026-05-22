<?php

declare(strict_types=1);

use FanTest\_core\SourceFileContractTestCase;

class GeneratedPendingServiceTemplateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/template.php';

    public function testCompiledTemplatesDeclareCompatibleParseHtmlReturnType(): void
    {
        $this->assertStringContainsString(
            'protected function parseHtml(): mixed',
            $this->sourceCode()
        );
    }

    public function testLegacyCompiledTemplatesAreRegenerated(): void
    {
        $this->assertStringContainsString('!$this->isCompiledTemplateCurrent($compilePath)', $this->sourceCode());
        $this->assertStringContainsString('function parseHtml(): mixed', $this->sourceCode());
    }
}
