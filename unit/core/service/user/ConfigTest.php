<?php

declare(strict_types=1);

use fan\core\service\user\config;
use FanTest\core\SourceFileContractTestCase;
use FanTest\core\ConfigRowFactory;
use fan\core\service\config\row;


if (!function_exists('get_class_alt')) {
    function get_class_alt(mixed $value): string
    {
        return is_object($value) ? get_class($value) : (string)$value;
    }
}

class ServiceUserConfigTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/user/config.php';

    public function testPasswordHashUsesModernPasswordApi(): void
    {
        $engine = new ServiceUserConfigProbe('alice');
        $engine->setConfig(new row(['ENGINE_KEY' => 'pepper']));

        $hash = $engine->makePasswordHash('secret');

        $this->assertTrue(password_verify('secret', $hash));
        $this->assertFalse(password_verify('wrong', $hash));
    }

    public function testLegacyMd5PasswordIsAcceptedAndRehashed(): void
    {
        $engine = new ServiceUserConfigProbe('alice');
        $engine->setConfig(new row(['ENGINE_KEY' => 'pepper', 'LOG_ERR_AUTH' => false]));
        $engine->setData(['login' => 'alice', 'password' => md5('alicesecretpepper')]);

        $this->assertTrue($engine->checkPassword('secret'));
        $this->assertTrue(password_verify('secret', $engine->getPassword()));
    }

    public function testMergeRolesAcceptsStringArrayAndDataObject(): void
    {
        $engine = new ServiceUserConfigProbe('anonymous');
        $target = ['guest' => null];

        $engine->mergeRoles($target, 'reader');
        $engine->mergeRoles($target, ['editor', 'publisher']);
        $engine->mergeRoles($target, new row(['admin', 'auditor']));
        $engine->mergeRoles($target, null);

        $this->assertSame([
            'guest' => null,
            'reader' => null,
            'editor' => null,
            'publisher' => null,
            'admin' => null,
            'auditor' => null,
        ], $target);
    }

    public function testSaveAndValidateAreDisabledForConfigEngine(): void
    {
        $engine = new ServiceUserConfigProbe('alice');

        $this->assertFalse($engine->saveData());
        $this->assertFalse($engine->validateForSave());
    }

    public function testAccessRuleUsesRequestInputServerValues(): void
    {
        $engine = (new ServiceUserConfigProbe('alice'))
            ->setEngineDependencies(null, new ServiceUserConfigRequestInputDouble([
                    'SERVER_NAME' => 'example.test',
                    'SERVER_ADDR' => '10.0.0.2',
                    'REMOTE_ADDR' => '203.0.113.9',
                ]));
        $engine->setAuthConfig(ConfigRowFactory::row([
            'RULE' => [
                ['name' => 'miss', 're_client_ip' => '/^198\\.51\\.100\\./'],
                ['name' => 'hit', 're_domain' => '/example\\.test/', 're_server_ip' => '/^10\\./', 're_client_ip' => '/^203\\.0\\.113\\./'],
            ],
        ]));

        $this->assertSame('hit', $engine->accessRule()['name']);
    }

    public function testLoadDataUsesInjectedConfigFactory(): void
    {
        $engine = new ServiceUserConfigProbe('anonymous');
        $engine->setConfig(ConfigRowFactory::row([
            'ENGINE_SOURCE' => 'auth',
            'ENGINE_KEY' => 'users',
            'IDENTIFYERS' => ['login'],
        ]));
        $engine->setConfigFactory(function (string $file): object {
            $this->assertSame('auth', $file);

            return ConfigRowFactory::row([
                'users' => [
                    'main_role' => 'guest',
                    'RULE' => [
                        [
                            'is_anonymous' => true,
                            'add_roles' => ['reader'],
                        ],
                    ],
                ],
            ]);
        });

        $this->assertTrue($engine->loadData());
        $this->assertSame('anonymous', $engine->getLogin());
        $this->assertSame(['guest' => null, 'reader' => null], $engine->getRoles(true));
    }

    public function testSourceUsesInjectedConfigFactoryDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('return ($this->configFactory)($file);', $source);
        $this->assertStringContainsString('$this->createUserFatalException(', $source);
        $this->assertStringContainsString('$this->arrayValueReader()', $source);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertStringNotContainsString('call_user_func', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
    }
}

final class ServiceUserConfigProbe extends config
{
    public function __construct(mixed $identifyer)
    {
        parent::__construct(
            $identifyer,
            null,
            null,
            static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default
        );
    }

    public function setAuthConfig(object $config): void
    {
        $property = new ReflectionProperty(config::class, 'authConfig');
        $property->setValue($this, $config);
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function accessRule(): mixed
    {
        return $this->_getAccessRule();
    }

    public function mergeRoles(array &$target, mixed $source): void
    {
        $this->_mergeRoles($target, $source);
    }

    public function saveData(): bool
    {
        return $this->_saveData();
    }

    public function loadData(): bool
    {
        return $this->_loadData();
    }

    public function validateForSave(): bool
    {
        return $this->_validateForSave();
    }
}

final class ServiceUserConfigRequestInputDouble
{
    public function __construct(private array $server)
    {
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }
}
