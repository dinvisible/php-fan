<?php

declare(strict_types=1);

use fan\core\adapter\error_demonstrator_file_storage;
use fan\core\adapter\php_array_file_loader;
use fan\core\error\demonstrator;
use FanTest\_core\SourceFileContractTestCase;

if (!defined('CORE_DIR')) {
    define('CORE_DIR', sys_get_temp_dir() . '/php-fan-core');
}
if (!defined('PROJECT_DIR')) {
    define('PROJECT_DIR', sys_get_temp_dir() . '/php-fan-project');
}

class ErrorDemonstratorTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/error/demonstrator.php';

    public function testTemplateVarsMergeScalarAndArrayValues(): void
    {
        $demo = $this->demo();

        $this->assertSame($demo, $demo->setTplVars('message'));
        $this->assertSame($demo, $demo->setTplVars(['code' => 500]));

        $this->assertSame('message', $demo->getTplVar('var'));
        $this->assertSame(500, $demo->getTplVar('code'));
        $this->assertSame('', $demo->getTplVar('missing'));
    }

    public function testResponseAndContentTypeHeadersNormalizeUnknownValues(): void
    {
        $demo = $this->demo();

        $this->assertSame('', $demo->setResponseHeader(404));
        $this->assertSame('', $demo->setContentType('unknown'));

        $headers = $this->headers($demo);
        $this->assertSame('HTTP/1.1 404 Not Found', $headers['Response']);
        $this->assertSame('Content-Type: text/plain; charset=utf-8', $headers['ContentType']);
    }

    public function testXhtmlContentTypeFallsBackToHtmlWhenHtmlAcceptHasHigherPriority(): void
    {
        $demo = $this->demo(new ErrorDemonstratorInputDouble([
            'HTTP_ACCEPT' => 'application/xhtml+xml;q=0.5,text/html;q=1',
        ]));

        $demo->setContentType('xhtml');

        $this->assertSame('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">', $demo->setDoctype());
    }

    public function testXhtmlContentTypeUsesInjectedInputForAcceptUserAgentAndRequestFlags(): void
    {
        $demo = $this->demo(new ErrorDemonstratorInputDouble([
            'HTTP_ACCEPT' => 'application/xhtml+xml;q=1,text/html;q=0.5',
        ]));
        $demo->setContentType('xhtml');
        $this->assertSame('<?xml version="1.0" encoding="utf-8"?>', $demo->setDoctype());

        $demo = $this->demo(new ErrorDemonstratorInputDouble([
            'HTTP_ACCEPT' => 'application/xhtml+xml;q=1',
            'HTTP_USER_AGENT' => 'Opera',
        ]));
        $demo->setContentType('xhtml');
        $this->assertSame('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">', $demo->setDoctype());

        $demo = $this->demo(new ErrorDemonstratorInputDouble([
            'HTTP_ACCEPT' => 'application/xhtml+xml;q=1',
        ], [
            'notX' => 1,
        ]));
        $demo->setContentType('xhtml');
        $this->assertSame('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">', $demo->setDoctype());
    }

    public function testDefaultInputFallbackDoesNotReadRequestGlobals(): void
    {
        $demo = $this->demo();

        $demo->setContentType('xhtml');

        $this->assertSame('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">', $demo->setDoctype());
    }

    public function testTemplateContentLoadsDataFileAndSetsLengthHeader(): void
    {
        $dir = sys_get_temp_dir() . '/php-fan-demonstrator-' . uniqid('', true);
        mkdir($dir);
        $tplFile = $dir . '/error.html';
        $dataFile = $dir . '/error.php';
        file_put_contents($tplFile, 'Hello {{NAME}}');
        file_put_contents($dataFile, "<?php\nreturn ['name' => 'Ada'];\n");
        $demo = $this->demo();
        $this->setProperty($demo, 'tplFile', $tplFile);
        $this->setProperty($demo, 'dataFile', $dataFile);

        try {
            $this->assertSame('Hello Ada', $demo->getTplContent());
            $this->assertSame('Content-Length: 9', $this->headers($demo)['ContentLength']);
        } finally {
            unlink($tplFile);
            unlink($dataFile);
            rmdir($dir);
        }
    }

    public function testTemplateDataFileRunsInDemonstratorContext(): void
    {
        $dir = sys_get_temp_dir() . '/php-fan-demonstrator-' . uniqid('', true);
        mkdir($dir);
        $tplFile = $dir . '/error.html';
        $dataFile = $dir . '/error.php';
        file_put_contents($tplFile, '{{TEXT}}');
        file_put_contents($dataFile, "<?php\nreturn ['text' => \$this->convArrayToSting(\$this->getTplVar(), '|')];\n");
        $demo = $this->demo();
        $demo->setTplVars(['first' => 'one', 'second' => 'two']);
        $this->setProperty($demo, 'tplFile', $tplFile);
        $this->setProperty($demo, 'dataFile', $dataFile);

        try {
            $this->assertSame('one|two', $demo->getTplContent());
        } finally {
            unlink($tplFile);
            unlink($dataFile);
            rmdir($dir);
        }
    }

    public function testConstructorDefersTemplateResolutionUntilFileStorageIsInjected(): void
    {
        $dir = sys_get_temp_dir() . '/php-fan-demonstrator-' . uniqid('', true);
        mkdir($dir);
        $tplFile = $dir . '/error.html';
        file_put_contents($tplFile, 'deferred template');

        try {
            $demo = new demonstrator([], $tplFile, new ErrorDemonstratorInputDouble());

            $this->assertNull($this->property($demo, 'tplFile'));

            $demo->setFileStorage(new error_demonstrator_file_storage());

            $this->assertSame($tplFile, $this->property($demo, 'tplFile'));
        } finally {
            unlink($tplFile);
            rmdir($dir);
        }
    }

    public function testOutputHeadersUsesInjectedHeaderWriter(): void
    {
        $headerWriter = new ErrorDemonstratorHeaderWriterDouble();
        $demo = $this->demo(headerWriter: $headerWriter);

        $demo->setResponseHeader(500);
        $demo->setContentType('html');
        $demo->outputHeaders();

        $this->assertSame([
            ['HTTP/1.1 500 Internal Server Error', true, 0],
            ['Content-Type: text/html; charset=utf-8', true, 0],
            ['Accept-Ranges: bytes', true, 0],
        ], $headerWriter->headers);
    }

    public function testOutputHeadersLogsIfHeadersBecomeSentDuringLoop(): void
    {
        $headerWriter = new ErrorDemonstratorHeaderWriterDouble([false, true], 'sent.php', 77);
        $errorLogWriter = new ErrorDemonstratorErrorLogWriterDouble();
        $demo = $this->demo(headerWriter: $headerWriter, errorLogWriter: $errorLogWriter);

        $demo->setResponseHeader(500);
        $demo->outputHeaders();

        $this->assertSame([], $headerWriter->headers);
        $this->assertSame([
            'Cannot send error demonstrator header "HTTP/1.1 500 Internal Server Error": headers already sent in "sent.php" on line 77.',
            'Cannot send error demonstrator header "Accept-Ranges: bytes": headers already sent in "sent.php" on line 77.',
        ], $errorLogWriter->messages);
    }

    public function testSourceUsesInjectedPhpArrayLoader(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('setPhpArrayFileLoader(callable $phpArrayFileLoader)', $source);
        $this->assertStringContainsString('setHeaderWriter(object $headerWriter)', $source);
        $this->assertStringContainsString('setErrorLogWriter(object $errorLogWriter)', $source);
        $this->assertStringContainsString('setFileStorage(object $fileStorage)', $source);
        $this->assertStringContainsString('$this->loadPhpArrayFile($this->dataFile, [])', $source);
        $this->assertStringContainsString('($this->phpArrayFileLoader)($path, $default, $this)', $source);
        $this->assertStringContainsString('$this->fileStorage()->exists($v)', $source);
        $this->assertStringContainsString('$this->fileStorage()->exists($this->dataFile)', $source);
        $this->assertStringContainsString('$this->fileStorage()->read($this->tplFile)', $source);
        $this->assertStringContainsString('$headerWriter->send($v);', $source);
        $this->assertStringContainsString('$this->errorLogWriter()->write(', $source);
        $this->assertStringContainsString('Input dependency is not configured for error demonstrator.', $source);
        $this->assertStringNotContainsString('php_array_file::load', $source);
        $this->assertStringNotContainsString('new request_input()', $source);
        $this->assertStringNotContainsString('createDefaultInput', $source);
        $this->assertStringNotContainsString('return new class', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:file_exists|file_get_contents)\s*\(/',
            $source
        );
        $this->assertStringNotContainsString('header(', $source);
        $this->assertStringNotContainsString('headers_sent(', $source);
        $this->assertStringNotContainsString('error_log(', $source);
    }

    public function testConvArrayToStringFlattensNestedScalarValues(): void
    {
        $demo = $this->demo();

        $this->assertSame('a,b,c', $demo->convArrayToSting(['a', ['b', 'c']], ','));
        $this->assertSame('single', $demo->convArrayToSting('single'));
    }

    private function demo(
        ?object $input = null,
        ?callable $phpArrayFileLoader = null,
        ?object $headerWriter = null,
        ?object $errorLogWriter = null
    ): demonstrator
    {
        $demo = (new ReflectionClass(demonstrator::class))->newInstanceWithoutConstructor();
        $this->setProperty($demo, 'input', $input ?? new ErrorDemonstratorInputDouble());
        $demo->setPhpArrayFileLoader($phpArrayFileLoader ?? new php_array_file_loader());
        $demo->setHeaderWriter($headerWriter ?? new ErrorDemonstratorHeaderWriterDouble());
        $demo->setErrorLogWriter($errorLogWriter ?? new ErrorDemonstratorErrorLogWriterDouble());
        $demo->setFileStorage(new error_demonstrator_file_storage());

        return $demo;
    }

    private function headers(demonstrator $demo): array
    {
        $property = new ReflectionProperty(demonstrator::class, 'headers');

        return $property->getValue($demo);
    }

    private function setProperty(demonstrator $demo, string $name, mixed $value): void
    {
        $property = new ReflectionProperty(demonstrator::class, $name);
        $property->setValue($demo, $value);
    }

    private function property(demonstrator $demo, string $name): mixed
    {
        $property = new ReflectionProperty(demonstrator::class, $name);

        return $property->getValue($demo);
    }
}

final class ErrorDemonstratorInputDouble
{
    public function __construct(private array $server = [], private array $request = [])
    {
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function requestValue(string $key, mixed $default = null): mixed
    {
        return $this->request[$key] ?? $default;
    }
}

final class ErrorDemonstratorHeaderWriterDouble
{
    public array $headers = [];

    private int $sentCall = 0;

    /**
     * @param list<bool> $sentSequence
     */
    public function __construct(private array $sentSequence = [false], private string $sentFile = '', private int $sentLine = 0)
    {
    }

    public function sent(?string &$file = null, ?int &$line = null): bool
    {
        $result = $this->sentSequence[$this->sentCall] ?? (bool)end($this->sentSequence);
        ++$this->sentCall;
        if ($result) {
            $file = $this->sentFile;
            $line = $this->sentLine;
        }

        return $result;
    }

    public function send(string $header, bool $replace = true, int $responseCode = 0): void
    {
        $this->headers[] = [$header, $replace, $responseCode];
    }
}

final class ErrorDemonstratorErrorLogWriterDouble
{
    public array $messages = [];

    public function write(string $message, int $messageType = 0, ?string $destination = null): bool
    {
        $this->messages[] = $message;

        return true;
    }
}
