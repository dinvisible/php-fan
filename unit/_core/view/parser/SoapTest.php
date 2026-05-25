<?php

declare(strict_types=1);

use fan\core\view\parser;
use fan\core\view\parser\soap;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\block\base;


class ViewParserSoapTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/view/parser/soap.php';

    public function testFormatIsSoap(): void
    {
        $this->assertSame('soap', soap::getFormat());
    }

    public function testSoapParserExtendsBaseParser(): void
    {
        $this->assertInstanceOf(parser::class, new soap(new ViewParserSoapBlockDouble()));
    }
}

final class ViewParserSoapBlockDouble extends base
{
    public function __construct()
    {
    }
}
