<?php

declare(strict_types=1);

use fan\core\di\service_id;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/tools/ai_map.php';
require_once dirname(__DIR__, 2) . '/tools/ai_verify.php';

final class AiToolingTest extends TestCase
{
    public function testAiMapBuildsRequiredProjectSections(): void
    {
        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);

        $this->assertSame(1, $map['schema_version']);
        $this->assertSame('htdocs/index.php', $map['entrypoints']['web']);
        $this->assertContains('core', $map['source_roots']);
        $this->assertContains('unit', $map['test_roots']);
        $this->assertArrayHasKey('fan\\core\\di\\', $map['autoload']['psr-4']);
        $this->assertContains('.ai/project.md', $map['ai_docs']);
        $this->assertSame(service_id::MATCHER, $map['services']['constants']['MATCHER']);
        $this->assertArrayHasKey(service_id::REQUEST, $map['services']['registered']);
        $this->assertArrayHasKey(service_id::MATCHER, $map['services']['registered']);
    }

    public function testAiMapCanBeEncodedAsJson(): void
    {
        $json = json_encode(php_fan_ai_build_map(dirname(__DIR__, 2)), JSON_THROW_ON_ERROR);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('php tools/ai_verify.php', $decoded['commands']['ai_verify']);
    }

    public function testAiVerifyFastChecksPassForCurrentWorkspace(): void
    {
        $result = php_fan_ai_verify(dirname(__DIR__, 2), ['tools/ai_verify.php', '--skip-phpunit']);

        $this->assertSame('pass', $result['status']);
        $this->assertSame(
            ['tracked_noise', 'git_diff_check', 'php_lint_changed'],
            array_column($result['checks'], 'name')
        );
    }
}
