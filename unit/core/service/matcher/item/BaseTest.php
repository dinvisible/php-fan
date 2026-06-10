<?php

declare(strict_types=1);

use fan\core\service\matcher\item;
use fan\core\service\matcher\item\base;
use FanTest\core\SourceFileContractTestCase;
use fan\core\service\matcher;


class ServiceMatcherItemBaseTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/matcher/item/base.php';

    public function testBaseSupportsMagicAndArrayAccessForDeclaredKeys(): void
    {
        $item = new ServiceMatcherItemBaseComponentProbe($this->item());

        $item->name = 'catalog';
        $item['count'] = 3;

        $this->assertSame('catalog', $item->name);
        $this->assertSame(3, $item['count']);
        $this->assertTrue(isset($item['name']));
        $this->assertSame([
            'name' => 'catalog',
            'count' => 3,
            'slug_value' => null,
            'mutable' => null,
        ], $item->toArray());
    }

    public function testBaseDispatchesSpecificSettersAndGetters(): void
    {
        $item = new ServiceMatcherItemBaseComponentProbe($this->item());

        $item->slug_value = 'news';

        $this->assertSame('slug:NEWS', $item->slug_value);
        $this->assertSame([['set', 'slug_value', 'news']], $item->methodCalls);
    }

    public function testBaseAllowsVariableKeysToBeChanged(): void
    {
        $item = new ServiceMatcherItemBaseComponentProbe($this->item());

        $item->mutable = 'first';
        $item->mutable = 'second';

        $this->assertSame('second', $item->mutable);
    }

    public function testBaseRejectsChangingAlreadySetNonVariableKeys(): void
    {
        $item = new ServiceMatcherItemBaseComponentProbe($this->item());
        $item->name = 'first';

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Error. Try to change existing property.');

        $item->name = 'second';
    }

    public function testBaseIteratesDeclaredValuesInOrder(): void
    {
        $item = new ServiceMatcherItemBaseComponentProbe($this->item());
        $item->name = 'catalog';
        $item->count = 3;
        $item->slug_value = 'news';

        $this->assertSame(['catalog', 3, 'NEWS', null], iterator_to_array($item, false));
    }

    public function testBaseUsesMatcherItemServiceExceptionFactoryWhenFacadeIsAvailable(): void
    {
        $calls = [];
        $factory = static function (
            string $className,
            object $service,
            string $message,
            int $code,
            ?Throwable $previous
        ) use (&$calls): Throwable {
            $calls[] = [$className, $service, $message, $code, $previous];

            return new RuntimeException('factory: ' . $message);
        };
        $facade = new ServiceMatcherItemBaseFacadeDouble();
        $item = new item(0, componentFactory: static fn(): object => new ServiceMatcherItemBaseComponentDouble(), serviceExceptionFactory: $factory);
        $item->setFacade($facade);
        $component = new ServiceMatcherItemBaseComponentProbe($item);
        $component->setFacade($facade);

        try {
            $component->get('unknown');
            $this->fail('Expected injected service exception factory to provide the throwable.');
        } catch (RuntimeException $exception) {
            $this->assertSame('factory: Invalid key "unknown" while accessing the item property of matcher.', $exception->getMessage());
        }

        $this->assertSame('\fan\project\exception\service\fatal', $calls[0][0]);
        $this->assertSame($facade, $calls[0][1]);
        $this->assertSame('Invalid key "unknown" while accessing the item property of matcher.', $calls[0][2]);
        $this->assertSame(E_USER_ERROR, $calls[0][3]);
        $this->assertNull($calls[0][4]);
    }

    public function testSourceUsesMatcherItemFactoryInsteadOfDirectServiceFatalConstruction(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('createServiceFatalException($errMsg)', $source);
        $this->assertStringNotContainsString('new \fan\project\exception\service\fatal', $source);
    }

    private function item(): item
    {
        return new class extends item {
            public function __construct()
            {
            }
        };
    }
}

final class ServiceMatcherItemBaseComponentProbe extends base
{
    public array $methodCalls = [];

    protected array $data = [
        'name' => null,
        'count' => null,
        'slug_value' => null,
        'mutable' => null,
    ];

    protected array $variable = [
        'mutable',
    ];

    protected function setSlugValue(string $key, mixed $value): void
    {
        $this->methodCalls[] = ['set', $key, $value];
        $this->data[$key] = strtoupper((string)$value);
    }

    protected function getSlugValue(): string
    {
        return 'slug:' . $this->data['slug_value'];
    }
}

final class ServiceMatcherItemBaseFacadeDouble extends matcher
{
    public function __construct()
    {
    }
}

final class ServiceMatcherItemBaseComponentDouble
{
    public function setFacade(object $facade): static
    {
        return $this;
    }
}
