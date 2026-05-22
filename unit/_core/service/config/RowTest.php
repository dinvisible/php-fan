<?php

declare(strict_types=1);

use FanTest\_core\exception\TestService;

require_once __DIR__ . '/../../../mock/_core/base/DataFunctions.php';
require_once __DIR__ . '/../../../mock/_core/exception/ExceptionDoubles.php';
require_once __DIR__ . '/../../../../_core/base/data.php';
require_once __DIR__ . '/../../../../_core/service/config/row.php';

class ServiceConfigRowTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        \fan\project\service\error::reset();
    }

    public function testRootKeysOwnersSourcesAndNestedRowsArePreserved(): void
    {
        $row = new \fan\core\service\config\row([
            'TestService' => [
                'ENABLED' => true,
                'nested' => ['value' => 'source'],
            ],
        ]);
        $service = new TestService();

        $this->assertSame('', $row->getRootKey());
        $this->assertSame('TestService', $row->get('TestService')->getRootKey());
        $this->assertSame('TestService', $row->get(['TestService', 'nested'])->getRootKey());

        $this->assertSame($row->get('TestService'), $row->get('TestService')->setServiceOwner($service));
        $this->assertSame([$service], $row->get('TestService')->getOwners());
        $this->assertSame([$service], $row->get(['TestService', 'nested'])->getOwners());
        $this->assertSame([
            'TestService' => [
                'ENABLED' => true,
                'nested' => ['value' => 'source'],
            ],
        ], $row->getSources());
    }

    public function testFacadeControlsMutationResetAndMergePriority(): void
    {
        $service = new TestService();
        $row = new \fan\core\service\config\row([
            'TestService' => [
                'ENABLED' => true,
                'mode' => 'source',
            ],
        ]);
        $branch = $row->get('TestService');
        $branch->setFacade($service);

        $branch->set('mode', 'external');
        $this->assertSame('source', $branch->get('mode'));
        $this->assertCount(1, \fan\project\service\error::instance()->messages);

        $service->setConfigValue($branch, 'mode', 'runtime');
        $this->assertSame('runtime', $branch->get('mode'));

        $service->resetConfigValue($branch, 'mode');
        $this->assertSame('source', $branch->get('mode'));

        $service->setConfigValue($branch, 'newKey', 'runtime');
        $service->resetConfigValue($branch, 'newKey');
        $this->assertNull($branch->get('newKey'));

        $service->mergeConfigData($branch, ['mode' => 'blocked', 'added' => 'no-priority'], false);
        $this->assertSame('source', $branch->get('mode'));
        $this->assertSame('no-priority', $branch->get('added'));

        $service->mergeConfigData($branch, ['mode' => 'priority'], true);
        $this->assertSame('priority', $branch->get('mode'));
    }

    public function testSerializationKeepsSourceDataAndRootKey(): void
    {
        $row = new \fan\core\service\config\row(['alpha' => ['x' => 1]]);
        $row->get('alpha')->getRootKey();

        $restored = unserialize(serialize($row));

        $this->assertInstanceOf(\fan\core\service\config\row::class, $restored);
        $this->assertSame(['alpha' => ['x' => 1]], $restored->toArray());
        $this->assertSame('alpha', $restored->get('alpha')->getRootKey());
        $this->assertSame(['alpha' => ['x' => 1]], $restored->getSources());
    }
}
