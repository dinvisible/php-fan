<?php

declare(strict_types=1);

use fan\core\service\locale;
use FanTest\core\SourceFileContractTestCase;
use fan\core\base\service;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;


class ServiceLocaleTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/locale.php';

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseHelper();
        $runtime = new ServiceLocaleRuntimeDouble();
        $configurator = new ServiceLocaleConfiguratorDouble(new ServiceLocaleConfigDouble([]));
        $cacheFactoryCalls = [];

        $locale = new ServiceLocaleConstructorProbe(
            true,
            null,
            null,
            null,
            null,
            null,
            null,
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            },
            self::arrayAdducer(),
            static fn(object $object): string => get_class($object)
        );

        $this->assertSame([ServiceLocaleConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$locale], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceLocaleConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame('en', $locale->getDefaultLanguage());
        $this->assertSame(['en' => 'en'], $locale->getAvailableLanguages());
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testLanguageAndLocaleAccessorsReturnConfiguredState(): void
    {
        $locale = $this->localeService([
            'SHORT_NAME' => ['en' => 'EN', 'fr' => 'FR'],
        ]);

        $this->assertSame(['en' => 'English', 'fr' => 'French'], $locale->getAvailableLanguages());
        $this->assertSame('en', $locale->getDefaultLanguage());
        $this->assertSame(['en' => 'EN', 'fr' => 'FR'], $locale->getLanguageShortNames());

        $this->assertSame('utf-8', $locale->getCharacterSet());
        $this->assertSame($locale, $locale->setCharacterSet('utf-8'));
    }

    public function testCheckUriLngRecognizesConfiguredLanguagePrefix(): void
    {
        $locale = $this->localeService();

        $matches = $locale->checkUriLng('/fr/catalog');
        $this->assertIsArray($matches);
        $this->assertSame('/fr', $matches[1]);
        $this->assertSame('fr', $matches[3]);

        $matches = $locale->checkUriLng('~/en/catalog');
        $this->assertIsArray($matches);
        $this->assertSame('~/en', $matches[1]);
        $this->assertSame('en', $matches[3]);

        $this->assertNull($locale->checkUriLng('/de/catalog'));
    }

    public function testModifyUrnAddsExplicitLanguageWhenUriParsingIsEnabled(): void
    {
        $locale = $this->localeService([
            'ENABLED' => true,
            'REQUEST_HAS_LNG' => true,
        ]);

        $this->assertTrue($locale->isUriParsing());
        $this->assertSame('/fr/catalog', $locale->modifyUrn('/catalog', 'fr'));
        $this->assertSame('/en/catalog', $locale->modifyUrn('/en/catalog', 'fr'));
    }

    public function testModifyUrnLeavesUrnUnchangedWhenLocaleIsDisabled(): void
    {
        $locale = $this->localeService([
            'ENABLED' => false,
            'REQUEST_HAS_LNG' => true,
        ]);

        $this->assertFalse($locale->isUriParsing());
        $this->assertSame('/catalog', $locale->modifyUrn('/catalog', 'fr'));
    }

    public function testLanguageIdUsesInjectedEntityFactory(): void
    {
        $languageRow = new ServiceLocaleLanguageRowDouble(17);
        $entity = new ServiceLocaleEntityDouble($languageRow);
        $locale = $this->localeService();
        $this->setProperty($locale, locale::class, 'isDefined', true);

        $locale->setLocaleDependencies(fn(): ServiceLocaleEntityDouble => $entity);

        $this->assertSame(17, $locale->getLanguageId());
        $this->assertSame('en', $entity->language);
        $this->assertFalse($languageRow->strict);
    }

    public function testSwitcherLinksUsesInjectedTabFactory(): void
    {
        $tab = new ServiceLocaleTabDouble('/catalog?x=1', '&');
        $locale = $this->localeService([
            'SHORT_NAME' => ['en' => 'EN', 'fr' => 'FR'],
            'REQUEST_HAS_LNG' => false,
            'LANGUAGE_KEY' => 'lng',
        ]);
        $this->setProperty($locale, locale::class, 'isDefined', true);

        $locale->setLocaleDependencies(null, fn(): ServiceLocaleTabDouble => $tab);

        $this->assertSame([
            'en' => [
                'key' => 'en',
                'urn' => '/catalog?x=1&lng=en',
                'f_name' => 'English',
                's_name' => 'EN',
                'current' => true,
            ],
            'fr' => [
                'key' => 'fr',
                'urn' => '/catalog?x=1&lng=fr',
                'f_name' => 'French',
                's_name' => 'FR',
                'current' => false,
            ],
        ], $locale->getSwitcherLinks());
    }

    public function testSetCurrentLanguageUsesInjectedSessionAndCookieFactories(): void
    {
        $session = new ServiceLocaleSessionDouble();
        $cookieFactory = new ServiceLocaleCookieFactoryDouble();
        $locale = $this->localeService([
            'ENABLED' => true,
            'USE_SESSION4LNG' => true,
            'LANGUAGE_KEY' => 'lng',
            'COOKIE_TIME' => 3600,
        ]);
        $this->setProperty($locale, locale::class, 'currentLanguage', 'fr');

        $locale->setLocaleDependencies(
            null,
            null,
            fn(string $namespace, string $group): ServiceLocaleSessionDouble => $session,
            null,
            fn(mixed $path = null, mixed $domain = null): ServiceLocaleCookieDouble => $cookieFactory->create($path, $domain)
        );

        $this->assertTrue($locale->_setCurrentLanguage('fr', true));
        $this->assertSame('fr', $session->data['current_language']);
        $this->assertSame('/', $cookieFactory->path);
        $this->assertNull($cookieFactory->domain);
        $this->assertSame(['lng', 'fr', 3600], $cookieFactory->cookie->lastSetByTime);
    }

    public function testSessionAvailabilityUsesInjectedLoadedClassCheck(): void
    {
        $checkedClasses = [];
        $sessionFactoryCalls = 0;
        $locale = $this->localeService();
        $locale->setLocaleDependencies(
            null,
            null,
            static function () use (&$sessionFactoryCalls): object {
                $sessionFactoryCalls++;

                return new ServiceLocaleSessionDouble();
            },
            null,
            null,
            null,
            null,
            static function (string $className) use (&$checkedClasses): bool {
                $checkedClasses[] = $className;

                return false;
            }
        );

        $method = new ReflectionMethod(locale::class, '_getSession');

        $this->assertNull($method->invoke($locale, false));
        $this->assertSame(['\fan\core\service\session'], $checkedClasses);
        $this->assertSame(0, $sessionFactoryCalls);
    }

    public function testLocaleServiceNoLongerFallsBackToServiceLocator(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('getContainerService(', $source);
        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringContainsString('private ?\Closure $localeLoadedClassExists = null;', $source);
        $this->assertStringContainsString('$this->localeLoadedClassExists(\'\fan\core\service\session\')', $source);
        $this->assertStringContainsString('$this->localeLoadedClassExists(\'\fan\core\service\matcher\')', $source);
        $this->assertStringNotContainsString('class_exists(\'\fan\core\service\session\', false)', $source);
        $this->assertStringNotContainsString('class_exists(\'\fan\core\service\matcher\', false)', $source);
    }

    private function ensureBaseHelper(): void
    {
        if (!function_exists('adduceToArray')) {
            eval('
                function adduceToArray(mixed $src): array
                {
                    if (is_array($src)) {
                        return $src;
                    }
                    if (is_object($src) && method_exists($src, "toArray")) {
                        return $src->toArray();
                    }
                    if ($src === null || $src === false || $src === "") {
                        return [];
                    }

                    return [$src];
                }
            ');
        }

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

    private function localeService(array $config = []): locale
    {
        $locale = (new ReflectionClass(locale::class))->newInstanceWithoutConstructor();

        $this->setProperty($locale, service::class, 'config', new ServiceLocaleConfigDouble($config));
        $this->setProperty($locale, locale::class, 'availableLng', ['en' => 'English', 'fr' => 'French']);
        $this->setProperty($locale, locale::class, 'defaultLng', 'en');
        $this->setProperty($locale, locale::class, 'currentLanguage', 'en');
        $this->setProperty($locale, locale::class, 'characterSet', 'utf-8');
        $locale->setLocaleDependencies(null, null, null, null, null, null, self::arrayAdducer());

        return $locale;
    }

    private static function arrayAdducer(): callable
    {
        return static function (mixed $value): array {
            if (is_array($value)) {
                return $value;
            }
            if (is_object($value) && method_exists($value, 'toArray')) {
                return $value->toArray();
            }
            if ($value === null || $value === false || $value === '') {
                return [];
            }

            return [$value];
        };
    }

    private function setProperty(object $object, string $class, string $propertyName, mixed $value): void
    {
        $property = new ReflectionProperty($class, $propertyName);
        $property->setValue($object, $value);
    }
}

final class ServiceLocaleConstructorProbe extends locale
{
    public function __construct(
        bool $allowIni,
        ?callable $entityFactory,
        ?callable $tabFactory,
        ?callable $sessionFactory,
        ?callable $requestFactory,
        ?callable $cookieFactory,
        ?callable $matcherFactory,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory,
        ?callable $arrayAdducer,
        ?callable $classNameResolver
    )
    {
        parent::__construct(
            $allowIni,
            $entityFactory,
            $tabFactory,
            $sessionFactory,
            $requestFactory,
            $cookieFactory,
            $matcherFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $arrayAdducer,
            $classNameResolver
        );
    }
}

final class ServiceLocaleEntityDouble
{
    public ?string $language = null;

    public function __construct(private ServiceLocaleLanguageRowDouble $row)
    {
    }

    public function getConfig(mixed $key, mixed $default = null): bool
    {
        return true;
    }

    public function getLngByName(?string $language): ServiceLocaleLanguageRowDouble
    {
        $this->language = $language;

        return $this->row;
    }
}

final class ServiceLocaleLanguageRowDouble
{
    public ?bool $strict = null;

    public function __construct(private int $id)
    {
    }

    public function getId(bool $strict): int
    {
        $this->strict = $strict;

        return $this->id;
    }
}

final class ServiceLocaleTabDouble
{
    public function __construct(private string $uri, private string $separator)
    {
    }

    public function getCurrentURI(bool $withHost = false, bool $withGet = true, bool $withAnchor = true, bool $encode = true): string
    {
        return $this->uri;
    }

    public function getConfig(mixed $key, mixed $default = null): string
    {
        return $key === 'GET_SEPARATOR' ? $this->separator : (string)$default;
    }
}

final class ServiceLocaleSessionDouble
{
    public array $data = [];

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }
}

final class ServiceLocaleCookieFactoryDouble
{
    public mixed $path = null;
    public mixed $domain = null;
    public ServiceLocaleCookieDouble $cookie;

    public function __construct()
    {
        $this->cookie = new ServiceLocaleCookieDouble();
    }

    public function create(mixed $path = null, mixed $domain = null): ServiceLocaleCookieDouble
    {
        $this->path = $path;
        $this->domain = $domain;

        return $this->cookie;
    }
}

final class ServiceLocaleCookieDouble
{
    public ?array $lastSetByTime = null;

    public function setByTime(string $key, string $value, int $time): void
    {
        $this->lastSetByTime = [$key, $value, $time];
    }
}

final class ServiceLocaleConfigDouble extends ArrayObject
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $this->offsetExists($key) ? $this[$key] : $default;
    }
}

final class ServiceLocaleRuntimeDouble
{
    public ServiceLocaleInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ServiceLocaleInitializerDouble();
    }

    public function getInitializer(): ServiceLocaleInitializerDouble
    {
        return $this->initializer;
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

final class ServiceLocaleInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceLocaleConfiguratorDouble
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
