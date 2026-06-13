<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\service\user;


final class application_user_service_creator
{
    private \Closure $projectServiceClassExists;

    public function __construct(?callable $projectServiceClassExists = null)
    {
        $this->projectServiceClassExists = \Closure::fromCallable(
            $projectServiceClassExists ?? static fn(string $className): bool => class_exists($className)
        );
    }

    public function createUserService(
        container_interface $container,
        object $userState,
        callable $userServiceFactory,
        callable $userEngineFactory,
        mixed $identifyer,
        ?string $reqSpace = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null
    ): mixed {
        $userDependencies = self::userDependencies($container);
        $userSpace = $this->verifyUserSpace($container, $userState, $reqSpace, $userDependencies);
        $serializerOperations = $userDependencies->serializerOperations();
        $instanceKeyEncoder = $serializerOperations->stableKeyEncoder();
        $instanceKey = self::normalizeUserInstanceKey($identifyer, $instanceKeyEncoder);
        if ($userState->getCurrentUsers() === null) {
            self::loadCurrentUsers($container, $userState, $userDependencies);
        }

        $user = $userState->getInstance($userSpace, $instanceKey);
        if ($user === null) {
            $className = self::getProjectServiceClassName('user');
            if (!$this->projectServiceClassExists($className)) {
                throw new \InvalidArgumentException('Service "user" does not expose a project class.');
            }

            $user = $userServiceFactory(
                $className,
                $identifyer,
                $userSpace,
                $userDependencies->configFactory(),
                $userDependencies->sessionFactory(),
                $userDependencies->currentUserFactory(),
                $userDependencies->applicationFactory(),
                $userDependencies->errorFactory(),
                $userDependencies->requestInputFactory(),
                $userDependencies->entityFactory(),
                $userEngineFactory,
                $serviceBootstrapRuntime ?? $userDependencies->bootstrapRuntime(),
                $serviceConfigurator ?? $userDependencies->config(),
                $serviceCacheFactory ?? $userDependencies->cacheFactory(),
                $userState,
                $instanceKeyEncoder,
                $serializerOperations->phpSnapshotEncoder(),
                $serializerOperations->phpSnapshotDecoder(),
                $userDependencies->arrayAdducer()
            );
        }

        return $user;
    }

    public function getCurrentUserService(container_interface $container, object $userState, ?string $reqSpace = null): mixed
    {
        $userSpace = $this->verifyUserSpace($container, $userState, $reqSpace);
        $currentUsers = self::loadCurrentUsers($container, $userState);

        return $currentUsers[$userSpace] ?? null;
    }

    public function getCurrentUserServiceChecked(container_interface $container, object $userState, ?string $reqSpace = null): mixed
    {
        $user = $this->getCurrentUserService($container, $userState, $reqSpace);
        if (empty($user)) {
            return null;
        }

        $field = $user->getConfig('LOGOUT_FIELD');
        if (empty($field)) {
            return $user;
        }

        $order = (string)$user->getConfig('LOGOUT_ORDER', 'GP');
        $logout = self::userDependencies($container)->request()->get((string)$field, $order);
        if (empty($logout)) {
            return $user;
        }

        for ($i = 0; $i < 100 && !empty($user); $i++) {
            $user->logout();
            $user = $this->getCurrentUserService($container, $userState, $reqSpace);
        }
        if ($i > 99) {
            throw $this->createError500Exception($container, 'Too many iteration for logout user.');
        }

        return null;
    }

    public function getCurrentUserSpace(container_interface $container, object $userState): string
    {
        $userDependencies = self::userDependencies($container);
        $config = $userDependencies->config()->get('user');
        $appName = $userDependencies->application()->getAppName();
        $currentUsers = self::loadCurrentUsers($container, $userState, $userDependencies);
        $prioritySpace = $userState->getPrioritySpace() ?? [];

        if (isset($prioritySpace[$appName])) {
            $priority = $prioritySpace[$appName];
            if (isset($currentUsers[$priority])) {
                $userState->setCurrentUserSpace((string)$priority);
                return $priority;
            }
        }

        $firstSpace = null;
        $priority = $priority ?? null;
        foreach ($config->get('space', []) as $key => $value) {
            if (in_array($appName, self::adduceToArray($value->APPLICATIONS), true)) {
                if (isset($currentUsers[$key])) {
                    $userState->setCurrentUserSpace((string)$key);
                    return $key;
                }
                if (empty($firstSpace)) {
                    $firstSpace = $key;
                }
            }
        }

        if (!empty($priority)) {
            $userState->setCurrentUserSpace((string)$priority);
            return $priority;
        }
        if (!empty($firstSpace)) {
            $userState->setCurrentUserSpace((string)$firstSpace);
            return $firstSpace;
        }

        $userSpace = $config->get('DEFAULT_SPACE');
        if (empty($userSpace)) {
            throw $this->createError500Exception($container, 'Default user space is not set.');
        }

        $userState->setCurrentUserSpace((string)$userSpace);
        return $userSpace;
    }

    private function verifyUserSpace(
        container_interface $container,
        object $userState,
        ?string $userSpace,
        ?application_user_service_dependencies $userDependencies = null
    ): string
    {
        if (empty($userSpace)) {
            return $this->getCurrentUserSpace($container, $userState);
        }
        $userSpace = (string)$userSpace;
        $userDependencies ??= self::userDependencies($container);
        $config = $userDependencies->config();
        if (!$config->get('user', ['space', $userSpace])) {
            throw $this->createError500Exception($container, 'Incorrect identifyer of user space - "' . $userSpace . '".');
        }

        return $userSpace;
    }

    private function createError500Exception(
        container_interface $container,
        string $message,
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null
    ): \Throwable {
        $factory = self::userDependencies($container)->error500ExceptionFactory();
        if (!is_callable($factory)) {
            throw new \RuntimeException('Error500 exception factory must be callable.');
        }

        $exception = $factory($message, $code, $previous);
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Error500 exception factory must return a throwable.');
        }

        return $exception;
    }

    private static function loadCurrentUsers(
        container_interface $container,
        object $userState,
        ?application_user_service_dependencies $userDependencies = null
    ): array
    {
        $currentUsers = $userState->getCurrentUsers();
        if ($currentUsers !== null) {
            return $currentUsers;
        }

        $userDependencies ??= self::userDependencies($container);
        $session = self::getUserSession($container, $userState, $userDependencies);
        $currentUsers = $session->get('currents', []);
        $prioritySpace = $session->get('priority', []);
        $serviceBootstrapRuntime = $userDependencies->bootstrapRuntime();
        $serviceConfigurator = $userDependencies->config();
        $serviceCacheFactory = $userDependencies->cacheFactory();
        $serializerOperations = $userDependencies->serializerOperations();
        $instanceKeyEncoder = $serializerOperations->stableKeyEncoder();
        foreach ($currentUsers as $key => $value) {
            self::setUserServiceDependencies(
                $container,
                $value,
                $serviceBootstrapRuntime,
                $serviceConfigurator,
                $serviceCacheFactory,
                $userState,
                $instanceKeyEncoder,
                $serializerOperations->phpSnapshotEncoder(),
                $serializerOperations->phpSnapshotDecoder(),
                $userDependencies
            );
            $instanceKey = self::normalizeUserInstanceKey(self::getObjectPropertyValue($value, 'identifyer'), $instanceKeyEncoder);
            if ($userState->getInstance((string)$key, $instanceKey) === null) {
                $userState->setInstance((string)$key, $instanceKey, $value);
            }
        }
        $userState->setCurrentUsers($currentUsers);
        $userState->setPrioritySpace($prioritySpace);

        return $currentUsers;
    }

    private static function setUserServiceDependencies(
        container_interface $container,
        mixed $user,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?object $userState = null,
        ?callable $instanceKeyEncoder = null,
        ?callable $snapshotEncoder = null,
        ?callable $snapshotDecoder = null,
        ?application_user_service_dependencies $userDependencies = null
    ): void {
        if (!is_object($user) || !method_exists($user, 'setUserDependencies')) {
            return;
        }

        $userDependencies ??= self::userDependencies($container);
        $user->setUserDependencies(
            $userDependencies->configFactory(),
            $userDependencies->sessionFactory(),
            $userDependencies->currentUserFactory(),
            $userDependencies->applicationFactory(),
            $userDependencies->errorFactory(),
            $userDependencies->requestInputFactory(),
            $userDependencies->entityFactory(),
            $serviceBootstrapRuntime ?? $userDependencies->bootstrapRuntime(),
            $serviceConfigurator ?? $userDependencies->config(),
            $serviceCacheFactory ?? $userDependencies->cacheFactory(),
            $userState,
            $instanceKeyEncoder ?? $userDependencies->serializerOperations()->stableKeyEncoder(),
            $snapshotEncoder ?? $userDependencies->serializerOperations()->phpSnapshotEncoder(),
            $snapshotDecoder ?? $userDependencies->serializerOperations()->phpSnapshotDecoder(),
            $userDependencies->arrayAdducer()
        );
    }

    private static function getUserSession(
        container_interface $container,
        object $userState,
        ?application_user_service_dependencies $userDependencies = null
    ): mixed
    {
        $session = $userState->getSession();
        if (empty($session)) {
            $userDependencies ??= self::userDependencies($container);
            $session = $userDependencies->session(user::SES_NAMESPACE, 'system');
            $userState->setSession($session);
        }

        return $session;
    }

    private static function userDependencies(container_interface $container): application_user_service_dependencies
    {
        return new application_user_service_dependencies($container);
    }

    private static function normalizeUserInstanceKey(mixed $identifyer, callable $instanceKeyEncoder): int|string
    {
        return is_int($identifyer) || is_string($identifyer)
            ? $identifyer
            : $instanceKeyEncoder($identifyer);
    }

    private static function getObjectPropertyValue(object $object, string $propertyName): mixed
    {
        $reflection = new \ReflectionObject($object);
        while (!$reflection->hasProperty($propertyName) && ($parent = $reflection->getParentClass())) {
            $reflection = $parent;
        }
        $property = $reflection->getProperty($propertyName);
        if (PHP_VERSION_ID < 80100) {
            $property->setAccessible(true);
        }

        return $property->getValue($object);
    }

    private static function adduceToArray(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }

        return match (gettype($value)) {
            'object' => method_exists($value, 'toArray') ? $value->toArray() : (array)$value,
            'array' => $value,
            'integer', 'double', 'string' => [$value],
            default => [],
        };
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }

    private function projectServiceClassExists(string $className): bool
    {
        return ($this->projectServiceClassExists)($className);
    }
}
