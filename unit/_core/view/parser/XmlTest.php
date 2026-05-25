<?php

declare(strict_types=1);

use fan\core\view\parser\xml;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\block\base;
use fan\core\service\header;
use fan\core\view\parser;


class ViewParserXmlTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/view/parser/xml.php';

    public function testFormatIsXml(): void
    {
        $this->assertSame('xml', xml::getFormat());
    }

    public function testMakeDomElementsSkipsNumericKeysAndNestsArrays(): void
    {
        $parser = new ViewParserXmlProbe(new ViewParserXmlBlockDouble('main'));
        $dom = new DOMDocument('1.0', 'iso-8859-1');
        $root = new DOMElement('root');
        $dom->appendChild($root);

        $this->assertSame($parser, $parser->exposeMakeDomElements($root, [
            'title' => 'Hello',
            'nested' => ['item' => 'Value'],
            0 => 'ignored',
        ]));

        $this->assertStringContainsString('<title>Hello</title>', $dom->saveXML());
        $this->assertStringContainsString('<nested><item>Value</item></nested>', $dom->saveXML());
        $this->assertStringNotContainsString('ignored', $dom->saveXML());
    }

    public function testFinalContentBuildsXmlDocumentAndSetsHeaders(): void
    {
        $parser = new ViewParserXmlProbe(new ViewParserXmlBlockDouble('main'));
        $parser->setRootBlock(new ViewParserXmlBlockDouble('root'));
        $parser->setResult(['title' => 'Hello']);

        $xml = $parser->getFinalContent();

        $this->assertIsString($xml);
        $this->assertStringContainsString('<root><title>Hello</title></root>', $xml);
        $this->assertSame([
            ['length', strlen($xml)],
            ['contentType', 'text/xml'],
        ], $parser->header->headers);
    }
}

final class ViewParserXmlProbe extends xml
{
    public ViewParserXmlHeaderDouble $header;

    public function __construct(base $mainBlock)
    {
        $header = new ViewParserXmlHeaderDouble();
        parent::__construct($mainBlock, null, null, $header, new ViewParserXmlLocaleDouble());
        $this->header = $header;
    }

    public function exposeMakeDomElements(DOMNode $parent, mixed $data): static
    {
        return $this->_makeDomElements($parent, $data);
    }

    public function setRootBlock(object $rootBlock): void
    {
        $property = new ReflectionProperty(parser::class, 'rootBlock');
        $property->setValue($this, $rootBlock);
    }

    public function setResult(array $result): void
    {
        $property = new ReflectionProperty(parser::class, 'result');
        $property->setValue($this, $result);
    }

}

final class ViewParserXmlBlockDouble extends base
{
    public function __construct(private string $name)
    {
    }

    public function getBlockName(): string
    {
        return $this->name;
    }
}

final class ViewParserXmlHeaderDouble extends header
{
    public array $headers = [];

    public function __construct()
    {
    }

    public function addHeader(string $key, mixed $value): static
    {
        $this->headers[] = [$key, $value];

        return $this;
    }
}

final class ViewParserXmlLocaleDouble
{
    public function getCharacterSet(): string
    {
        return 'utf-8';
    }
}
