<?php

declare(strict_types=1);

namespace fan\core\bootstrap;
use fan\core\di\context_defaults_factory;
use fan\core\di\container_interface;
use fan\core\di\fatal_exception_factory;


final class application
{
    public const MIN_PHP_VERSION = '8.0.0';

    private ?context_registry_state $contextState;

    private \Closure $defaultContextStateFactory;

    private \Closure $fatalExceptionFactory;

    private \Closure $fatalExceptionFactoryProvider;

    public function __construct(
        ?context_registry_state $contextState = null,
        ?callable $defaultContextStateFactory = null,
        ?callable $fatalExceptionFactory = null,
        ?callable $fatalExceptionFactoryProvider = null
    ) {
        $this->contextState = $contextState;
        $this->defaultContextStateFactory = \Closure::fromCallable(
            $defaultContextStateFactory ?? $this->defaultContextStateFactory()
        );
        $this->fatalExceptionFactoryProvider = \Closure::fromCallable(
            $fatalExceptionFactoryProvider
                ?? static function (): callable {
                    return new fatal_exception_factory();
                }
        );
        $this->fatalExceptionFactory = \Closure::fromCallable(
            $fatalExceptionFactory
                ?? fn(mixed ...$arguments): mixed => ($this->fatalExceptionFactoryProvider)()(...$arguments)
        );
    }

    public function setContext(context $context): void
    {
        $this->contextState = $this->contextStateFromContext($context);
    }

    public function setContextFactory(callable $contextFactory): void
    {
        $this->contextState = $this->contextStateFromFactory($contextFactory);
    }

    public function setContextState(context_registry_state $contextState): void
    {
        $this->contextState = $contextState;
    }

    public function resetContext(): void
    {
        $this->contextState = null;
    }

    public function context(): context
    {
        return $this->contextState()->context();
    }

    public function init(?string $configPath, callable $bootstrapErrorHandler): bool
    {
        $phpRuntimeSettings = $this->phpRuntimeSettings();
        $phpVersion = $phpRuntimeSettings->version();
        if (version_compare($phpVersion, self::MIN_PHP_VERSION) < 0) {
            $phpRuntimeSettings->terminate('PHP-FAN can\'t work with version less than "' . self::MIN_PHP_VERSION . '". Actually your version is "' . $phpVersion . '".');

            return false;
        }
        $state = $this->context()->state();
        if ($state->isInit()) {
            return false;
        }
        $state->markInitialized();
        $state->setCli($state->isCli() || strtolower($phpRuntimeSettings->sapiName()) === 'cli');

        define('CORE_DIR', dirname(__DIR__));
        $bootstrapFileStorage = $this->context()->bootstrapLoaderFileStorage();
        if (!defined('PROJECT_DIR')) {
            define('PROJECT_DIR', $bootstrapFileStorage->realPath(CORE_DIR . '/../_project'));
        }
        if (!defined('BASE_DIR')) {
            $input = $this->context()->requestInput();
            $docRoot = getenv('DOCUMENT_ROOT');
            if (empty($docRoot)) {
                $docRoot = $input->serverValue('DOCUMENT_ROOT');
            }
            if (empty($docRoot)) {
                $docRoot = dirname((string)$input->serverValue('SCRIPT_FILENAME', ''));
            }
            define('BASE_DIR', (string)$docRoot);
        }
        $state->setReplacement([
            '{BASE_DIR}'    => BASE_DIR,
            '{CORE_DIR}'    => CORE_DIR,
            '{PROJECT_DIR}' => PROJECT_DIR,
        ]);

        $this->context()->loadBootstrapConfig($configPath);

        if (!defined('ADMIN_EMAIL')) {
            $config = $state->config();
            define('ADMIN_EMAIL', $config['bootstrap']['admin_email']);
        }

        $this->context()->setupBootstrapErrorHandler($bootstrapErrorHandler);

        $state->setInitializer($this->defineObj('initializer', initializer::class, '{CORE_DIR}/application/initializer.php'));
        $state->setLoader($this->defineObj('loader', loader::class, '{CORE_DIR}/application/loader.php'));
        $container = $this->container();
        $state->initializer()->initAfterLoader(
            $container->get('matcher'),
            $container->get('request_input'),
            $container->get('bootstrap_runtime')
        );
        $state->setRunner($this->defineObj('runner', runner::class, '{CORE_DIR}/application/runner.php'));

        return true;
    }

    public function run(?string $configPath, bool $isEcho, callable $bootstrapErrorHandler): mixed
    {
        $this->init($configPath, $bootstrapErrorHandler);

        return $this->getRunner($bootstrapErrorHandler)->run($isEcho);
    }

    public function getInitializer(callable $bootstrapErrorHandler): initializer
    {
        if (empty($this->context()->state()->initializer())) {
            $this->init(null, $bootstrapErrorHandler);
        }

        return $this->context()->state()->initializer();
    }

    public function getLoader(callable $bootstrapErrorHandler): loader
    {
        if (empty($this->context()->state()->loader())) {
            $this->init(null, $bootstrapErrorHandler);
        }

        return $this->context()->state()->loader();
    }

    public function getRunner(callable $bootstrapErrorHandler): runner
    {
        if (empty($this->context()->state()->runner())) {
            $this->init(null, $bootstrapErrorHandler);
        }

        return $this->context()->state()->runner();
    }

    public function getConfigCache(): array
    {
        $config = $this->context()->state()->config();

        return isset($config['config_cache']) ? $config['config_cache'] : [];
    }

    public function loadClass(string $class, bool $makeAlias, callable $bootstrapErrorHandler): mixed
    {
        return $this->getLoader($bootstrapErrorHandler)->loadClass($class, $makeAlias);
    }

    public function loadFile(string $file, int $handleError, int $way, callable $bootstrapErrorHandler): mixed
    {
        return $this->getLoader($bootstrapErrorHandler)->loadFile($file, $handleError, $way);
    }

    public function parsePath(string $path, callable $bootstrapErrorHandler): string
    {
        return $this->getLoader($bootstrapErrorHandler)->parsePath($path);
    }

    public function getGlobalPath(mixed $key, mixed $altPath = null): ?string
    {
        $config = $this->context()->state()->config();
        $paths = $config['bootstrap']['global_path'];
        $path  = empty($paths[$key]) ? $altPath : $paths[$key];

        return empty($path) ? null : $this->fillPlaceholder($path);
    }

    public function logError(string $message): void
    {
        $this->context()->logError($message);
    }

    public function getPid(): string
    {
        return $this->context()->state()->pid();
    }

    public function isCli(): bool
    {
        return $this->context()->state()->isCli();
    }

    public function bootstrapLoaderFileStorage(): object
    {
        return $this->context()->bootstrapLoaderFileStorage();
    }

    private function phpRuntimeSettings(): object
    {
        return $this->context()->phpRuntimeSettings();
    }

    private function contextState(): context_registry_state
    {
        if ($this->contextState === null) {
            $contextState = ($this->defaultContextStateFactory)();
            if (!$contextState instanceof context_registry_state) {
                throw new \RuntimeException('Bootstrap context state factory must return a context registry state.');
            }
            $this->contextState = $contextState;
        }

        return $this->contextState;
    }

    private function contextStateFromContext(context $context): context_registry_state
    {
        return new context_registry_state($context);
    }

    private function contextStateFromFactory(callable $contextFactory): context_registry_state
    {
        return new context_registry_state(null, $contextFactory);
    }

    private function defaultContextStateFactory(): callable
    {
        return function (): context_registry_state {
            $contextDefaultsProvider = new context_defaults_factory();

            return new context_registry_state(null, new context_factory($contextDefaultsProvider));
        };
    }

    private function fillPlaceholder(string $path): string
    {
        return $this->context()->state()->fillPlaceholder($path);
    }

    private function defineObj(string $key, string $class, string $path): object
    {
        $config = $this->context()->state()->config();
        if (isset($config[$key])) {
            $conf  = $config[$key];
            $class = empty($conf['class']) ? $class : (string)$conf['class'];
            $path  = empty($conf['path'])  ? $path  : (string)$conf['path'];
        }
        $arguments = [isset($conf['ini']) ? $conf['ini'] : null];
        if (is_a($class, initializer::class, true)) {
            $arguments[] = $this->context()->bootstrapRuntime();
            $arguments[] = null;
            $arguments[] = null;
            $arguments[] = $this->context()->errorHandlerRegistrar();
            $arguments[] = $this->context()->phpRuntimeSettings();
        } elseif (is_a($class, loader::class, true)) {
            $arguments[] = $this->context()->zendAutoloaderLoader();
            $application = $this;
            $fatalExceptionFactory = $this->fatalExceptionFactory;
            $arguments[] = static function (string $message) use ($application): void {
                $application->logError($message);
            };
            $arguments[] = $this->context()->bootstrapLoaderFileStorage();
            $arguments[] = static function (string $message) use ($application, $fatalExceptionFactory): \Throwable {
                return $fatalExceptionFactory(
                    $message,
                    requestInput: $application->context()->requestInput(),
                    exceptionRuntimeLogger: new class($application) {
                        public function __construct(private application $application)
                        {
                        }

                        public function logError(string $message): void
                        {
                            $this->application->logError($message);
                        }
                    },
                    exceptionHeaderWriter: $application->container()->get('header_writer')
                );
            };
            $arguments[] = $this->container()->get('array_value_reader');
        } elseif (is_a($class, runner::class, true)) {
            $container = $this->container();
            $arguments[] = $container->get('php_array_file_loader');
            $arguments[] = $container->get('error_demonstrator_factory');
            $arguments[] = $container->get('matcher');
            $arguments[] = $container->get('request');
            $arguments[] = $container->get('error');
            $arguments[] = $container->get('request_input');
            $arguments[] = $container->get('bootstrap_runtime');
            $arguments[] = $container->get('header');
            $arguments[] = [
                'tab' => static fn(): mixed => $container->get('tab'),
                'plain' => static fn(): mixed => $container->get('plain'),
            ];
        }

        return $this->context()->createBootstrapObject($class, $arguments);
    }

    private function container(): container_interface
    {
        return $this->context()->container();
    }
}
