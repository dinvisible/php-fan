<?php

declare(strict_types=1);

use fan\core\base\data;
use fan\core\view\keeper\loader\text;
use FanTest\_core\SourceFileContractTestCase;

class ViewKeeperLoaderTextTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/view/keeper/loader/text.php';

    public function testSetRewritesOrAppendsTextByPosition(): void
    {
        $keeper = $this->keeper();

        $this->assertSame($keeper, $keeper->set(0, 'first'));
        $this->assertSame('first', (string)$keeper);

        $keeper->set(1, ' second');
        $this->assertSame('first second', (string)$keeper);

        $keeper->set(-1, 'zero ');
        $this->assertSame('zero first second', (string)$keeper);
    }

    public function testNonNumericKeysBecomeAppendAndEmptyKeyBecomesRewrite(): void
    {
        $keeper = $this->keeper();

        $keeper->set('title', 'first');
        $keeper->set('', 'rewrite');

        $this->assertSame('rewrite', $keeper->get());
    }

    public function testGetReturnsDefaultWhenTextIsEmpty(): void
    {
        $keeper = $this->keeper();

        $this->assertSame('fallback', $keeper->get(null, 'fallback'));
        $this->assertNull($keeper->missing);
    }

    private function keeper(): text
    {
        $keeper = (new ReflectionClass(text::class))->newInstanceWithoutConstructor();

        foreach ([
            'data' => [],
            'fullRewrite' => true,
            'multiLevel' => false,
        ] as $name => $value) {
            $property = new ReflectionProperty(data::class, $name);
            $property->setValue($keeper, $value);
        }

        return $keeper;
    }
}
