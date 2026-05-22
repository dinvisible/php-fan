<?php

declare(strict_types=1);

class ParserBaseSerializationTest extends \PHPUnit\Framework\TestCase
{
    public function testParserReadsJsonLogPayloads(): void
    {
        $parser = new class extends \fan\core\service\log\parser_base {
            public function decodeForTest(string $payload): array
            {
                return $this->decodeLogRowPayload($payload);
            }
        };

        $row = [
            'header' => 'Title',
            'main_msg' => 'Message',
            'trace' => [['func' => 'test']],
        ];

        $this->assertSame(
            $row + ['method' => '', 'request' => ''],
            $parser->decodeForTest(\fan\core\adapter\safe_serializer::encodeJson($row))
        );
    }

    public function testParserReadsLegacyPhpLogPayloads(): void
    {
        $parser = new class extends \fan\core\service\log\parser_base {
            public function decodeForTest(string $payload): array
            {
                return $this->decodeLogRowPayload($payload);
            }
        };

        $row = [
            'method' => 'CLI',
            'request' => 'script.php',
            'header' => 'Title',
            'main_msg' => 'Message',
        ];

        $this->assertSame(
            $row,
            $parser->decodeForTest(\fan\core\adapter\safe_serializer::encodePhpSnapshot($row))
        );
    }
}
