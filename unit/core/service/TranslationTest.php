<?php

declare(strict_types=1);

use fan\core\service\translation;
use FanTest\core\SourceFileContractTestCase;
use fan\core\base\service;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;


class ServiceTranslationTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/translation.php';

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseHelper();
        $runtime = new ServiceTranslationRuntimeDouble();
        $configurator = new ServiceTranslationConfiguratorDouble(new ServiceTranslationConfigDouble([]));
        $cacheFactoryCalls = [];
        $loadedPhpArrayFiles = [];
        $locale = new ServiceTranslationLocaleDouble(['en' => 'English', 'fr' => 'French']);

        $translation = new ServiceTranslationConstructorProbe(
            true,
            $locale,
            $runtime,
            static fn(): object => new ServiceTranslationTabDouble(),
            [],
            new ServiceTranslationErrorLoggerDouble(),
            new stdClass(),
            new stdClass(),
            new stdClass(),
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            },
            static function (string $path, mixed $default = null) use (&$loadedPhpArrayFiles): mixed {
                $loadedPhpArrayFiles[] = [$path, $default];

                return $default;
            },
            new ServiceTranslationFileStorageDouble()
        );

        $this->assertSame([ServiceTranslationConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$translation], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceTranslationConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame([], $cacheFactoryCalls);
        $this->assertSame([], $loadedPhpArrayFiles);
        $this->assertSame(['en', 'fr'], $translation->editableLanguages());
    }

    public function testCombiMessageAltReplacesPlaceholdersInOrder(): void
    {
        $translation = $this->translationService();

        $this->assertSame(
            'Hello brave world',
            $translation->getCombiMessageAlt('Hello {combi_part}, brave {combi_part}, world')
        );
        $this->assertSame(
            'A B C',
            $translation->getCombiMessageAlt(['A {combi_part} {combi_part}', 'B', 'C'])
        );
    }

    public function testDisabledMessageModeReturnsKeyAndExpandsKnownTags(): void
    {
        $translation = $this->translationService([
            'ENABLED' => false,
        ]);
        $translation->setTags([
            'name' => ['tag' => 'Fan'],
        ]);

        $this->assertSame('Hello Fan', $translation->getMessage('Hello {name}'));
        $this->assertSame('', $translation->getMessage(''));
    }

    public function testCheckUrlLngRecognizesConfiguredLanguagePrefix(): void
    {
        $translation = $this->translationService();

        $matches = $translation->checkUrlLng('/fr/catalog');
        $this->assertIsArray($matches);
        $this->assertSame('/fr', $matches[1]);
        $this->assertSame('fr', $matches[3]);

        $this->assertNull($translation->checkUrlLng('/de/catalog'));
    }

    public function testEditTagArrUpdatesTagLinkAndFunctionFlagWithoutSchedulingSave(): void
    {
        $translation = $this->translationService();
        $translation->setTags([
            'user' => ['tag' => 'old', 'link' => '/old'],
        ]);

        $translation->editTagArr('user', ['tag' => 'Hello {name}', 'link' => ''], false);

        $this->assertSame([
            'user' => [
                'tag' => 'Hello {name}',
                'isFunc' => true,
            ],
        ], $translation->tags());
        $this->assertSame([], $translation->scheduledCalls());
    }

    public function testFormatKeyNormalizesWordsAndHtmlToUppercaseMessageKey(): void
    {
        $translation = $this->translationService();

        $this->assertSame('ORDER_STATUS', $translation->exposeFormatKey('order_status'));
        $this->assertSame('HELLO_FAN', $translation->exposeFormatKey('<b>Hello</b>, fan!'));
    }

    public function testFilePathUsesInjectedRuntimeParser(): void
    {
        $runtime = new ServiceTranslationRuntimeDouble();
        $translation = $this->translationService([
            'MESSAGES_PATH' => '{MESSAGES}/{LNG}.php',
        ], $runtime);

        $this->assertSame('/parsed/messages/en.php', $translation->exposeGetFilePath('MESSAGES_PATH', ['{LNG}' => 'en']));
        $this->assertSame(['messages/en.php'], $runtime->parsedPaths);
    }

    public function testMissingFilePathUsesInjectedServiceExceptionFactory(): void
    {
        $translation = $this->translationService();
        $factoryCalls = [];
        $translation->setServiceDependencies(
            serviceExceptionFactory: static function (
                string $exceptionClass,
                object $service,
                string $message,
                int $code,
                ?Throwable $previous
            ) use (&$factoryCalls): Throwable {
                $factoryCalls[] = [$exceptionClass, $service, $message, $code, $previous];

                return new RuntimeException($message, $code, $previous);
            }
        );

        try {
            $translation->exposeGetFilePath('MISSING_PATH');
            $this->fail('Expected injected service exception factory to create the missing translation path failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Incorrect Key. Key for path "MISSING_PATH" doesn\'t set', $exception->getMessage());
        }

        $this->assertCount(1, $factoryCalls);
        $this->assertSame('\fan\project\exception\service\fatal', $factoryCalls[0][0]);
        $this->assertSame($translation, $factoryCalls[0][1]);
        $this->assertSame('Incorrect Key. Key for path "MISSING_PATH" doesn\'t set', $factoryCalls[0][2]);
        $this->assertSame(E_USER_ERROR, $factoryCalls[0][3]);
        $this->assertNull($factoryCalls[0][4]);
    }

    public function testTagArrayUsesInjectedPhpArrayLoader(): void
    {
        $loadedPhpArrayFiles = [];
        $translation = $this->translationService(
            ['TAGS_PATH' => '{MESSAGES}/tags.php'],
            new ServiceTranslationRuntimeDouble(),
            null,
            null,
            [],
            static function (string $path, mixed $default = null) use (&$loadedPhpArrayFiles): mixed {
                $loadedPhpArrayFiles[] = [$path, $default];

                return [
                    'name' => ['tag' => 'Fan'],
                ];
            }
        );

        $this->assertSame([
            'name' => ['tag' => 'Fan'],
        ], $translation->getTagArr());
        $this->assertSame([
            ['/parsed/messages/tags.php', []],
        ], $loadedPhpArrayFiles);
    }

    public function testInvalidFunctionTagLogsThroughInjectedErrorLogger(): void
    {
        $logger = new ServiceTranslationErrorLoggerDouble();
        $translation = $this->translationService([
            'ENABLED' => false,
        ], null, $logger);
        $translation->setTags([
            'broken' => [
                'tag' => '{MissingClass:missing:argument}',
                'isFunc' => true,
            ],
        ]);

        $this->assertSame('Value {MissingClass:missing:argument}', $translation->getMessage('Value {broken}'));
        $this->assertSame(
            [['Message tag "broken" is not callable.', 'Incorect message tag', '', true, false]],
            $logger->messages
        );
    }

    public function testFunctionTagUsesInjectedTabFactory(): void
    {
        $translation = $this->translationService(
            ['ENABLED' => false],
            null,
            new ServiceTranslationErrorLoggerDouble(),
            static fn(): object => new ServiceTranslationTabDouble()
        );
        $translation->setTags([
            'contact' => [
                'tag' => '{service|tab:getURI:/contact}',
                'isFunc' => true,
            ],
        ]);

        $this->assertSame('Open /uri/contact', $translation->getMessage('Open {contact}'));
    }

    public function testTranslationServiceNoLongerFallsBackToServiceLocator(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('getContainerService(', $source);
        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('serviceFactory', $source);
    }

    private function ensureBaseHelper(): void
    {
        if (function_exists('fan\core\base\get_class_name')) {
            return;
        }

        eval('
            namespace fan\core\base;

            function get_class_name(string|object $object): ?string
            {
                if (is_object($object)) {
                    $object = get_class($object);
                }
                $parts = explode("\\\\", $object);

                return end($parts);
            }
        ');
    }

    private function translationService(
        array $config = [],
        ?ServiceTranslationRuntimeDouble $runtime = null,
        ?ServiceTranslationErrorLoggerDouble $errorLogger = null,
        ?callable $tabFactory = null,
        array $messageTagFactories = [],
        ?callable $phpArrayLoader = null,
        ?object $fileStorage = null
    ): ServiceTranslationProbe
    {
        $translation = new ServiceTranslationProbe();
        $translation->setBaseConfig(new ServiceTranslationConfigDouble($config));
        $translation->setTranslationDependencies(
            new ServiceTranslationLocaleDouble(['en' => 'English', 'fr' => 'French']),
            $runtime,
            $tabFactory,
            $messageTagFactories,
            $errorLogger,
            null,
            null,
            null,
            $phpArrayLoader,
            $fileStorage ?? new ServiceTranslationFileStorageDouble()
        );

        return $translation;
    }
}

final class ServiceTranslationProbe extends translation
{
    public function __construct()
    {
    }

    public function setBaseConfig(object $config): void
    {
        $property = new ReflectionProperty(service::class, 'config');
        $property->setValue($this, $config);
    }

    public function setLocale(object $locale): void
    {
        $property = new ReflectionProperty(translation::class, 'locale');
        $property->setValue($this, $locale);
    }

    public function setTags(array $tags): void
    {
        $property = new ReflectionProperty(translation::class, 'tags');
        $property->setValue($this, $tags);
    }

    public function tags(): array
    {
        $property = new ReflectionProperty(translation::class, 'tags');
        return $property->getValue($this);
    }

    public function scheduledCalls(): array
    {
        $property = new ReflectionProperty(translation::class, 'forCall');
        return $property->getValue($this);
    }

    public function exposeFormatKey(string $key): string
    {
        return $this->_formatKey($key);
    }

    public function exposeGetFilePath(string $key, ?array $repl = null): string
    {
        return $this->_getFilePath($key, $repl);
    }
}

final class ServiceTranslationConstructorProbe extends translation
{
    public function __construct(
        bool $allowIni,
        ?object $locale,
        ?object $runtime,
        ?callable $tabFactory,
        array $messageTagFactories,
        ?object $errorLogger,
        ?object $blockContext,
        ?object $matcher,
        ?object $requestInput,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory,
        ?callable $phpArrayLoader,
        ?object $fileStorage
    )
    {
        parent::__construct(
            $allowIni,
            $locale,
            $runtime,
            $tabFactory,
            $messageTagFactories,
            $errorLogger,
            $blockContext,
            $matcher,
            $requestInput,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $phpArrayLoader,
            $fileStorage
        );
    }

    public function editableLanguages(): array
    {
        return $this->editableLng;
    }
}

final class ServiceTranslationLocaleDouble
{
    public function __construct(private array $languages)
    {
    }

    public function getAvailableLanguages(): array
    {
        return $this->languages;
    }
}

final class ServiceTranslationConfigDouble extends ArrayObject
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this;
        }

        $value = $this->getArrayCopy();
        foreach ((array)$key as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }

        return $value;
    }
}

final class ServiceTranslationRuntimeDouble
{
    public array $parsedPaths = [];
    public ServiceTranslationInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ServiceTranslationInitializerDouble();
    }

    public function getInitializer(): ServiceTranslationInitializerDouble
    {
        return $this->initializer;
    }

    public function parsePath(string $path): string
    {
        $path = str_replace('{MESSAGES}', 'messages', $path);
        $this->parsedPaths[] = $path;

        return '/parsed/' . ltrim($path, '/');
    }

    private ?service_listener_state $baseServiceListenerState = null;

    private ?service_single_state $baseServiceSingleState = null;

    public function serviceListenerState(): service_listener_state
    {
        return $this->baseServiceListenerState ??= new service_listener_state();
    }

    public function serviceSingleState(): service_single_state
    {
        return $this->baseServiceSingleState ??= new service_single_state();
    }
}

final class ServiceTranslationInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceTranslationConfiguratorDouble
{
    public array $getServiceConfigCalls = [];
    public array $resetCalls = [];

    public function __construct(private object $config)
    {
    }

    public function getServiceConfig(object $service): object
    {
        $this->getServiceConfigCalls[] = $service;

        return $this->config;
    }

    public function reset(string $className, string $key): void
    {
        $this->resetCalls[] = [$className, $key];
    }
}

final class ServiceTranslationErrorLoggerDouble
{
    public array $messages = [];

    public function logErrorMessage($message, $title = '', $note = '', $fixPosition = false, $sendEmail = null): void
    {
        $this->messages[] = [$message, $title, $note, $fixPosition, $sendEmail];
    }
}

final class ServiceTranslationFileStorageDouble
{
    public array $writes = [];

    public function isReadable(string $path): bool
    {
        return true;
    }

    public function write(string $path, string $data): int|false
    {
        $this->writes[] = [$path, $data];

        return strlen($data);
    }
}

final class ServiceTranslationTabDouble
{
    public function getURI(string $urn): string
    {
        return '/uri' . $urn;
    }
}
