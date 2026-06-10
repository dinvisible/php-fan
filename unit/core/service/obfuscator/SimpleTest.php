<?php

declare(strict_types=1);

use fan\core\service\obfuscator;
use fan\core\service\obfuscator\simple;
use FanTest\core\SourceFileContractTestCase;

class ServiceObfuscatorSimpleTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/obfuscator/simple.php';

    public function testSimpleObfuscatorDropsCommentsEndRowsAndExtraSpacesByDefault(): void
    {
        $engine = new simple();

        $source = "  body   {  color: red;  }  \n// remove me\n/* block */\n  .link   { display:  block; }";

        $this->assertSame('body { color: red; } .link { display: block; }', $engine->obfuscate($source));
    }

    public function testSimpleObfuscatorHonorsFacadeOptions(): void
    {
        $engine = new simple();
        $engine->setFacade(new ServiceObfuscatorSimpleFacadeDouble([
            'DROP_COMMENTS' => false,
            'DROP_END_ROW' => false,
            'SPACES_TO_ONE' => true,
        ]));

        $source = "  body   {  color: red;  }\n\n  .link   {  color: blue;  }";

        $this->assertSame("body { color: red; }\n.link { color: blue; }", $engine->obfuscate($source));
    }

    public function testSimpleObfuscatorCanPreserveWhitespaceAndComments(): void
    {
        $engine = new simple();
        $engine->setFacade(new ServiceObfuscatorSimpleFacadeDouble([
            'DROP_COMMENTS' => false,
            'DROP_END_ROW' => false,
            'SPACES_TO_ONE' => false,
        ]));
        $source = "  body   {  color: red;  }\n// keep me";

        $this->assertSame($source, $engine->obfuscate($source));
    }
}

final class ServiceObfuscatorSimpleFacadeDouble extends obfuscator
{
    public function __construct(private array $options)
    {
    }

    public function getConfig(mixed $key = null, mixed $default = null): mixed
    {
        return $key === 'option' ? $this->options : $default;
    }
}
