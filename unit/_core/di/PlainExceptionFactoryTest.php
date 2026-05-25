<?php

declare(strict_types=1);

use fan\core\di\plain_exception_factory;
use PHPUnit\Framework\TestCase;
use fan\project\exception\plain\fatal;


if (!function_exists('get_class_alt')) {
    function get_class_alt(mixed $value): string
    {
        return is_object($value) ? get_class($value) : (string)$value;
    }
}

final class PlainExceptionFactoryTest extends TestCase
{
    public function testFactoryCreatesProjectPlainFatalException(): void
    {
        $controller = (object)['name' => 'controller'];
        $previous = new RuntimeException('previous');

        $exception = (new plain_exception_factory(
            exceptionRuntimeLogger: new PlainExceptionFactoryRuntimeLoggerDouble(),
            exceptionRequestService: new PlainExceptionFactoryRequestDouble(),
            exceptionErrorService: new PlainExceptionFactoryErrorDouble()
        ))(
            '\fan\project\exception\plain\fatal',
            $controller,
            'Plain controller failed.',
            E_USER_WARNING,
            $previous
        );

        $this->assertInstanceOf(fatal::class, $exception);
        $this->assertSame($controller, $exception->getController());
        $this->assertSame(E_USER_WARNING, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testFactoryPassesClassNameResolverToPlainFatalException(): void
    {
        $controller = (object)['name' => 'controller'];
        $errorService = new PlainExceptionFactoryErrorDouble();

        (new plain_exception_factory(
            exceptionRequestService: new PlainExceptionFactoryRequestDouble(),
            exceptionErrorService: $errorService,
            classNameResolver: static fn(object $object): string => 'resolved-' . get_class($object)
        ))(
            '\fan\project\exception\plain\fatal',
            $controller,
            'Plain controller failed.'
        );

        $this->assertSame(
            ['Plain controller fatal error (resolved-stdClass). Plain controller failed.', 'Log exception', 'request info'],
            $errorService->messages[0]
        );
    }

    public function testFactoryRejectsUnsupportedPlainExceptionClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported plain exception class "\fan\project\exception\plain\local".');

        (new plain_exception_factory())(
            '\fan\project\exception\plain\local',
            (object)[],
            'Plain controller failed.'
        );
    }}

final class PlainExceptionFactoryRuntimeLoggerDouble
{
    public array $errors = [];

    public function logError(string $message): void
    {
        $this->errors[] = $message;
    }
}

final class PlainExceptionFactoryRequestDouble
{
    public function getInfoString(): string
    {
        return 'request info';
    }

    public function getAll(string $source, array $default = []): array
    {
        return $default;
    }
}

final class PlainExceptionFactoryErrorDouble
{
    public array $messages = [];

    public function logExceptionMessage(string $message, string $title, string $note): void
    {
        $this->messages[] = [$message, $title, $note];
    }
}
