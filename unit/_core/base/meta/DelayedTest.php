<?php

declare(strict_types=1);
use fan\core\base\meta\delayed;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../../_core/base/meta/delayed.php';

class DelayedTest extends TestCase
{
    public function testCallsObjectMethodWithArrayArguments(): void
    {
        $target = new class {
            public function build($left, $right): string
            {
                return $left . ':' . $right;
            }
        };

        $delayed = new delayed($target, 'build', ['alpha', 'beta']);

        $this->assertSame('alpha:beta', $delayed->getValue());
    }

    public function testWrapsScalarArgumentIntoSingleArgumentList(): void
    {
        $target = new class {
            /**
             * @param mixed $value Value that should be applied or transformed.
             */
            public function repeat($value): string
            {
                return $value . $value;
            }
        };

        $delayed = new delayed($target, 'repeat', 'x');

        $this->assertSame('xx', $delayed->getValue());
    }

    public function testNullArgumentsProduceEmptyArgumentList(): void
    {
        $target = new class {
            public function value(): int
            {
                return 42;
            }
        };

        $delayed = new delayed($target, 'value', null);

        $this->assertSame(42, $delayed->getValue());
    }
}
