<?php

declare(strict_types=1);

use fan\core\di\service_id;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/tools/ai_map.php';
require_once dirname(__DIR__, 2) . '/tools/ai_explain.php';
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
        foreach ([service_id::REQUEST, service_id::MATCHER, service_id::BOOTSTRAP_RUNTIME, service_id::CONFIG, service_id::CACHE, service_id::JSON, service_id::TAB, service_id::SESSION, service_id::USER] as $serviceId) {
            $this->assertArrayHasKey($serviceId, $map['services']['descriptors']);
            $this->assertSame($serviceId, $map['services']['descriptors'][$serviceId]['id']);
            $this->assertNotSame([], $map['services']['descriptors'][$serviceId]['registrar_files']);
            $this->assertSame(
                $map['services']['descriptors'][$serviceId]['dependencies'],
                $map['services']['descriptors'][$serviceId]['factory_arguments']['container_dependencies']
            );
            $this->assertSame(
                $map['services']['descriptors'][$serviceId]['registrar_files'],
                $map['services']['descriptors'][$serviceId]['factory_origin']['registrar_files']
            );
        }
        $this->assertContains('createRequestService', $map['services']['descriptors'][service_id::REQUEST]['creator_methods']);
        $this->assertContains('createRequestService', $map['services']['descriptors'][service_id::REQUEST]['factory_origin']['creator_methods']);
        $this->assertContains(service_id::CONFIG, $map['services']['descriptors'][service_id::REQUEST]['dependencies']);
        $this->assertContains('type', $map['services']['descriptors'][service_id::CACHE]['factory_arguments']['runtime_arguments']);
        $this->assertFalse($map['services']['descriptors'][service_id::CACHE]['shared']);
        $this->assertIsArray($map['services']['descriptors'][service_id::CACHE]['aliases']);
        $this->assertSame('.ai/meta.schema.json', $map['metadata']['meta_schema']);
        $this->assertArrayHasKey('own', $map['metadata']['meta']['top_level_key_usage']);
        $this->assertArrayHasKey('json', $map['metadata']['meta']['own_key_usage']);
        $this->assertSame(
            'core/block/admin/data_form.tpl',
            $map['metadata']['meta']['files']['core/block/admin/data_form.meta.php']['paired_template']
        );
        $this->assertContains(
            'tplType',
            $map['metadata']['templates']['files']['project/block/common/html_pager.tpl']['placeholders']
        );
    }

    public function testAiMapCanBeEncodedAsJson(): void
    {
        $json = json_encode(php_fan_ai_build_map(dirname(__DIR__, 2)), JSON_THROW_ON_ERROR);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('php tools/ai_verify.php', $decoded['commands']['ai_verify']);
        $this->assertSame('php tools/ai_explain.php <file> --json', $decoded['commands']['ai_explain']);
        $this->assertSame('php tools/ai_static_check.php', $decoded['commands']['ai_static']);
        $this->assertSame('php fan ai:map --json', $decoded['commands']['fan_ai_map']);
        $this->assertSame('php fan ai:map --validate', $decoded['commands']['fan_ai_map_validate']);
    }

    public function testAiMapSchemaAndContractValidationPass(): void
    {
        $root = dirname(__DIR__, 2);
        $schema = json_decode((string)file_get_contents($root . '/.ai/map.schema.json'), true, 512, JSON_THROW_ON_ERROR);
        $map = php_fan_ai_build_map($root);

        $this->assertSame('PHP-FAN AI map', $schema['title']);
        $this->assertContains('services', $schema['required']);
        $this->assertContains('metadata', $schema['required']);
        $this->assertSame([], php_fan_ai_validate_map_contract($root, $map));
    }

    public function testAiExplainSummarizesSourceFile(): void
    {
        $explanation = php_fan_ai_explain_file(dirname(__DIR__, 2), 'core/base/model/request.php');

        $this->assertSame('core/base/model/request.php', $explanation['file']);
        $this->assertSame('php', $explanation['type']);
        $this->assertSame('fan\core\base\model', $explanation['namespace']);
        $this->assertSame('request', $explanation['classes'][0]['name']);
        $this->assertContains('unit/core/base/model/RequestTest.php', $explanation['related_tests']);
        $this->assertFalse($explanation['dynamic_boundaries']['container_get']);
    }

    public function testAiVerifyFastChecksPassForCurrentWorkspace(): void
    {
        $result = php_fan_ai_verify(dirname(__DIR__, 2), ['tools/ai_verify.php', '--skip-phpunit']);

        $this->assertSame('pass', $result['status']);
        $this->assertSame(
            ['tracked_noise', 'git_diff_check', 'php_lint_changed', 'ai_map_build', 'static_baseline'],
            array_column($result['checks'], 'name')
        );
    }

    public function testAiVerifyNoPhpunitAliasMatchesSkipPhpunit(): void
    {
        $result = php_fan_ai_verify(dirname(__DIR__, 2), ['tools/ai_verify.php', '--no-phpunit']);

        $this->assertSame('pass', $result['status']);
        $this->assertSame(
            ['tracked_noise', 'git_diff_check', 'php_lint_changed', 'ai_map_build', 'static_baseline'],
            array_column($result['checks'], 'name')
        );
    }

    public function testFanCliBridgeRunsAiCommands(): void
    {
        $root = dirname(__DIR__, 2);

        $mapResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:map', '--json'], $root);
        $this->assertSame(0, $mapResult['exit_code'], $mapResult['stderr']);
        $map = json_decode($mapResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(1, $map['schema_version']);

        $mapValidationResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:map', '--validate', '--json'], $root);
        $this->assertSame(0, $mapValidationResult['exit_code'], $mapValidationResult['stderr']);
        $mapValidation = json_decode($mapValidationResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('pass', $mapValidation['status']);
        $this->assertSame([], $mapValidation['errors']);

        $servicesResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:services', '--json'], $root);
        $this->assertSame(0, $servicesResult['exit_code'], $servicesResult['stderr']);
        $services = json_decode($servicesResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey(service_id::REQUEST, $services['descriptors']);

        $explainResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:explain', 'core/base/model/request.php', '--json'], $root);
        $this->assertSame(0, $explainResult['exit_code'], $explainResult['stderr']);
        $explanation = json_decode($explainResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('core/base/model/request.php', $explanation['file']);

        $doctorResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:doctor', '--no-phpunit'], $root);
        $this->assertSame(0, $doctorResult['exit_code'], $doctorResult['stdout'] . $doctorResult['stderr']);
    }
}
