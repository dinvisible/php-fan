<?php

declare(strict_types=1);

use FanTest\_core\SourceFileContractTestCase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class GeneratedPendingServiceTabTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/tab.php';

    public function testParsedMatcherItemsAreStoredAsObjects(): void
    {
        $code = $this->sourceCode();

        $this->assertStringContainsString('protected ?object $currentData = null;', $code);
        $this->assertStringContainsString('protected ?object $lastData = null;', $code);
        $this->assertStringContainsString('$this->currentData = $this->matcher->getCurrentParsedData();', $code);
        $this->assertStringContainsString('$this->lastData    = $this->matcher->getLastParsedData();', $code);
    }

    public function testMainBlockReceivesArrayContainerMeta(): void
    {
        $this->assertStringContainsString(
            '$this->mainBlock = new $class(\'main\', $this, null, [], false);',
            $this->sourceCode()
        );
    }

    public function testDebugExternalFilesUseBooleanMode(): void
    {
        $this->assertStringContainsString('$debug->setExtFiles($rootBlock, false);', $this->sourceCode());
    }

    public function testBooleanFlagsAcceptOnlyNumericValues(): void
    {
        $tab = $this->makeTabService();

        $this->assertFalse($tab->readFlag('0'));
        $this->assertTrue($tab->readFlag('1'));
        $this->assertFalse($tab->readFlag(0));
        $this->assertTrue($tab->readFlag(1));
    }

    public function testBooleanFlagWordsAreRejected(): void
    {
        $tab = $this->makeTabService();

        $this->expectException(\UnexpectedValueException::class);
        $tab->readFlag('false');
    }

    public function testArrayConfigRowsAreConvertedToArrays(): void
    {
        require_once dirname(__DIR__, 3) . '/_core/base/data.php';
        require_once dirname(__DIR__, 3) . '/_core/service/config/row.php';

        $tab = $this->makeTabService();
        $row = new \fan\core\service\config\row(['common' => ['tplVars' => ['foo' => 'bar']]]);

        $this->assertSame(
            ['common' => ['tplVars' => ['foo' => 'bar']]],
            $tab->readArrayConfig($row)
        );
    }

    public function testArrayConfigRejectsScalarValues(): void
    {
        $tab = $this->makeTabService();

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Configuration "TEST_ARRAY" must be an array, string given.');

        $tab->readArrayConfig('legacy');
    }

    private function makeTabService(): object
    {
        require_once dirname(__DIR__, 3) . '/_core/di/container_interface.php';
        require_once dirname(__DIR__, 3) . '/_core/di/container_aware_trait.php';
        require_once dirname(__DIR__, 3) . '/_core/base/service.php';
        require_once dirname(__DIR__, 3) . '/_core/base/service/single.php';

        if (!class_exists('\fan\core\service\tab', false)) {
            require_once dirname(__DIR__, 3) . '/_core/service/tab.php';
        }

        return new class extends \fan\core\service\tab {
            public function __construct()
            {
            }

            public function readFlag(mixed $value): bool
            {
                return $this->readBooleanFlag($value, 'TEST_FLAG');
            }

            public function readArrayConfig(mixed $value): array
            {
                return $this->readArrayConfigValue($value, 'TEST_ARRAY');
            }
        };
    }
}
