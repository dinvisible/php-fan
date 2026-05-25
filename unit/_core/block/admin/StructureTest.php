<?php

declare(strict_types=1);

use fan\core\block\admin\structure;
use FanTest\_core\SourceFileContractTestCase;

class BlockAdminStructureTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/block/admin/structure.php';

    public function testStructureAccessorsReadConfiguredMetaBuckets(): void
    {
        $block = new BlockAdminStructureProbe([
            'addParam' => ['page' => 2],
            'extra' => ['mode' => 'edit'],
            'cond' => ['active' => 1],
        ]);

        $this->assertSame(['page' => 2], $block->getAddParam());
        $this->assertSame(['mode' => 'edit'], $block->getExtraData());
        $this->assertSame(['active' => 1], $block->getCondition());
    }

    public function testInitUsesInjectedRoleServiceAndBuildsLoaderPayload(): void
    {
        $role = new BlockAdminStructureRoleDouble();
        $block = new BlockAdminStructureProbe([
            'login_timeout' => 600,
            'addParam' => ['page' => 2],
            'extra' => ['mode' => 'edit'],
            'cond' => ['active' => 1],
        ], $role, ['id' => 7], '<p>Template</p>');

        $block->init();

        $this->assertSame([['admin', 600]], $role->sessionRoles);
        $this->assertSame([
            'condition' => [
                'code' => '<p>Template</p>',
                'param' => ['page' => 2],
                'extra' => ['mode' => 'edit'],
                'cond' => ['active' => 1],
            ],
        ], $block->jsonPayload);
        $this->assertSame('ok', $block->textPayload);
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $this->assertStringNotContainsString('containerService(', $this->sourceCode());
    }
}

final class BlockAdminStructureProbe extends structure
{
    public array $jsonPayload = [];

    public string $textPayload = '';

    public function __construct(
        private array $metaData = [],
        private ?BlockAdminStructureRoleDouble $role = null,
        private array $dataPayload = [],
        private string $templateCode = '',
    ) {
    }

    public function getMeta(string|array|null $key = null, mixed $default = null, bool $convToArray = false): mixed
    {
        return is_string($key) && array_key_exists($key, $this->metaData) ? $this->metaData[$key] : $default;
    }

    public function getData(): array
    {
        return $this->dataPayload;
    }

    protected function getTemplateCode(array $addVars = []): string
    {
        return $this->templateCode;
    }

    public function setJson(mixed $json, bool $merge = true): static
    {
        $this->jsonPayload = (array)$json;

        return $this;
    }

    public function setText(string $text, bool $merge = true): static
    {
        $this->textPayload = $text;

        return $this;
    }

    protected function roleService(): object
    {
        return $this->role ??= new BlockAdminStructureRoleDouble();
    }
}

final class BlockAdminStructureRoleDouble
{
    public array $sessionRoles = [];

    public function setSessionRoles(string $role, mixed $timeout): void
    {
        $this->sessionRoles[] = [$role, $timeout];
    }
}
