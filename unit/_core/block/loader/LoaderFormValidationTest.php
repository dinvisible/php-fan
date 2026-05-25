<?php

declare(strict_types=1);

use fan\core\block\loader\loader_form_validation;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\block\base;
use fan\project\exception\block\fatal;


class BlockLoaderLoaderFormValidationTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/block/loader/loader_form_validation.php';

    public function testInitRunsFieldCheckerAndWritesOkWhenCheckerReturnsNull(): void
    {
        $block = new BlockLoaderFormValidationProbe([
            'field' => 'email',
            'value' => 'a@example.test',
        ]);

        $block->init();

        $this->assertSame(['field' => 'email', 'value' => 'a@example.test'], $block->view->json);
        $this->assertSame('ok', $block->view->text);
        $this->assertSame([['a@example.test', 'Invalid email']], $block->emailChecks);
    }

    public function testInitWritesCheckerErrorText(): void
    {
        $block = new BlockLoaderFormValidationProbe([
            'field' => 'email',
            'value' => 'bad',
        ]);

        $block->init();

        $this->assertSame('Invalid email', $block->view->text);
    }

    public function testInitUsesInjectedBlockExceptionFactoryWhenFieldIsMissing(): void
    {
        $exceptionCalls = [];
        $block = new BlockLoaderFormValidationProbe([], $this->blockExceptionFactory($exceptionCalls));

        try {
            $block->init();
            $this->fail('Expected fatal block exception for missing field.');
        } catch (fatal $exception) {
            $this->assertStringContainsString('Method name for check field isn\'t set.', $exception->getMessage());
        }

        $this->assertSame('\fan\project\exception\block\fatal', $exceptionCalls[0][0]);
        $this->assertSame($block, $exceptionCalls[0][1]);
        $this->assertSame('Method name for check field isn\'t set.', $exceptionCalls[0][2]);
        $this->assertSame(E_USER_NOTICE, $exceptionCalls[0][3]);
        $this->assertNull($exceptionCalls[0][4]);
    }

    public function testInitUsesInjectedBlockExceptionFactoryWhenCheckerIsMissing(): void
    {
        $exceptionCalls = [];
        $block = new BlockLoaderFormValidationProbe(
            ['field' => 'missing'],
            $this->blockExceptionFactory($exceptionCalls)
        );

        try {
            $block->init();
            $this->fail('Expected fatal block exception for missing checker.');
        } catch (fatal $exception) {
            $this->assertStringContainsString('Method "check_missing" isn\'t found.', $exception->getMessage());
        }

        $this->assertSame('\fan\project\exception\block\fatal', $exceptionCalls[0][0]);
        $this->assertSame($block, $exceptionCalls[0][1]);
        $this->assertSame('Method "check_missing" isn\'t found.', $exceptionCalls[0][2]);
        $this->assertSame(E_USER_NOTICE, $exceptionCalls[0][3]);
        $this->assertNull($exceptionCalls[0][4]);
    }

    public function testSourceUsesInjectedBlockExceptionFactory(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('$this->_makeBlockException(\'Method name for check field isn\\\'t set.\', \'fatal\');', $source);
        $this->assertStringContainsString('$this->_makeBlockException(\'Method "check_\' .  $data[\'field\'] . \'" isn\\\'t found.\', \'fatal\');', $source);
        $this->assertStringNotContainsString('use fan\core\exception\block\fatal as exception_block_fatal;', $source);
        $this->assertStringNotContainsString('new exception_block_fatal(', $source);
    }

    private function blockExceptionFactory(array &$calls): callable
    {
        return static function (
            string $exceptionClass,
            base $block,
            string $message,
            int $code,
            ?\Exception $previous = null
        ) use (&$calls): \Throwable {
            $calls[] = [$exceptionClass, $block, $message, $code, $previous];

            return new $exceptionClass($block, $message, $code, $previous);
        };
    }
}

final class BlockLoaderFormValidationProbe extends loader_form_validation
{
    public BlockLoaderFormValidationViewDouble $view;

    public array $emailChecks = [];

    public function __construct(private array $data, ?callable $blockExceptionFactory = null)
    {
        $this->view = new BlockLoaderFormValidationViewDouble();
        $dependencies = [
            'arrayAdducer' => static fn(mixed $value): array => is_array($value) ? $value : [$value],
            'recursiveMerger' => static fn(mixed ...$values): array => array_replace_recursive(...$values),
        ];
        if ($blockExceptionFactory !== null) {
            $dependencies['blockExceptionFactory'] = $blockExceptionFactory;
        }
        $this->setBlockDependencies($dependencies);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getMeta(string|array|null $key = null, mixed $default = null, bool $convToArray = false): mixed
    {
        return $key === ['err_message', 'email'] ? 'Invalid email' : $default;
    }

    public function check_email(mixed $value, string $message): ?string
    {
        $this->emailChecks[] = [$value, $message];

        return filter_var($value, FILTER_VALIDATE_EMAIL) ? null : $message;
    }
}

final class BlockLoaderFormValidationViewDouble
{
    public array $json = [];

    public string $text = '';
}
