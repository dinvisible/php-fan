<?php

declare(strict_types=1);

use fan\core\service\obfuscator;
use fan\core\service\obfuscator\base;
use FanTest\core\SourceFileContractTestCase;

class ServiceObfuscatorBaseTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/obfuscator/base.php';

    public function testSetFacadeAppliesConfiguredOptions(): void
    {
        $engine = new ServiceObfuscatorBaseProbe();
        $facade = new ServiceObfuscatorBaseFacadeDouble([
            'DROP_COMMENTS' => false,
            'DROP_END_ROW' => true,
            'SPACES_TO_ONE' => false,
        ]);

        $this->assertSame($engine, $engine->setFacade($facade));

        $this->assertSame([
            'dropComments' => false,
            'dropEndRow' => true,
            'spacesToOne' => false,
        ], $engine->options());
    }

    public function testSetFacadeKeepsFirstFacadeAndOptions(): void
    {
        $engine = new ServiceObfuscatorBaseProbe();

        $engine->setFacade(new ServiceObfuscatorBaseFacadeDouble([
            'DROP_COMMENTS' => false,
        ]));
        $engine->setFacade(new ServiceObfuscatorBaseFacadeDouble([
            'DROP_COMMENTS' => true,
            'DROP_END_ROW' => false,
            'SPACES_TO_ONE' => false,
        ]));

        $this->assertSame([
            'dropComments' => false,
            'dropEndRow' => true,
            'spacesToOne' => true,
        ], $engine->options());
    }
}

final class ServiceObfuscatorBaseProbe extends base
{
    public function obfuscate(string $text): string
    {
        return $text;
    }

    public function options(): array
    {
        return [
            'dropComments' => $this->dropComments,
            'dropEndRow' => $this->dropEndRow,
            'spacesToOne' => $this->spacesToOne,
        ];
    }
}

final class ServiceObfuscatorBaseFacadeDouble extends obfuscator
{
    public function __construct(private array $options)
    {
    }

    public function getConfig(mixed $key = null, mixed $default = null): mixed
    {
        return $key === 'option' ? $this->options : $default;
    }
}
