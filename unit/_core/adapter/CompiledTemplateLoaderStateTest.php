<?php

declare(strict_types=1);

use fan\core\adapter\compiled_template_loader_state;
use PHPUnit\Framework\TestCase;

final class CompiledTemplateLoaderStateTest extends TestCase
{
    public function testStoresPathsByNormalizedClassName(): void
    {
        $state = new compiled_template_loader_state();

        $state->setPath('\\Example\\CompiledTemplate', '/tmp/template.php');

        $this->assertSame('/tmp/template.php', $state->getPath('Example\\CompiledTemplate'));
        $this->assertSame('/tmp/template.php', $state->getPath('\\Example\\CompiledTemplate'));
    }

    public function testTracksAutoloadRegistration(): void
    {
        $state = new compiled_template_loader_state();

        $this->assertFalse($state->isRegistered());

        $state->markRegistered();

        $this->assertTrue($state->isRegistered());
    }
}
