<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;


final class ProjectBlockRootHtml5Test extends TestCase
{
    private const SOURCE_FILE = '_project/block/root/html5.php';

    public function testSourceDoesNotUseContainerServiceLocator(): void
    {
        $this->assertStringNotContainsString('containerService(', $this->sourceCode());
    }

    private function sourceCode(): string
    {
        $code = file_get_contents(dirname(__DIR__, 2) . '/' . self::SOURCE_FILE);

        $this->assertIsString($code);
        return $code;
    }
}
