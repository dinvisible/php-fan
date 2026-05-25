<?php

declare(strict_types=1);

namespace fan\core\di;

use Illuminate\Database\Capsule\Manager as Capsule;

final class eloquent_manager_factory
{
    public function __invoke(array|object $config): Capsule
    {
        $settings = $this->normalizeConfig($config);
        $connections = $settings['CONNECTIONS'] ?? $settings['connections'] ?? [];
        if (!is_array($connections) || $connections === []) {
            throw new \RuntimeException('Eloquent connections are not configured.');
        }

        $manager = new Capsule();
        foreach ($connections as $name => $connection) {
            if (is_object($connection)) {
                $connection = $this->normalizeConfig($connection);
            }
            if (!is_array($connection)) {
                throw new \RuntimeException('Eloquent connection "' . (string)$name . '" must be an array.');
            }

            $manager->addConnection($this->normalizeConnection($connection), (string)$name);
        }

        $defaultConnection = $settings['DEFAULT_CONNECTION'] ?? $settings['default'] ?? null;
        if (is_string($defaultConnection) && $defaultConnection !== '') {
            $manager->getDatabaseManager()->setDefaultConnection($defaultConnection);
        }
        if ($this->enabled($settings['SET_AS_GLOBAL'] ?? $settings['global'] ?? true)) {
            $manager->setAsGlobal();
        }
        if ($this->enabled($settings['BOOT_ELOQUENT'] ?? $settings['boot'] ?? true)) {
            $manager->bootEloquent();
        }

        return $manager;
    }

    private function normalizeConfig(array|object $config): array
    {
        if (is_object($config) && method_exists($config, 'toArray')) {
            return $config->toArray();
        }
        if (is_array($config)) {
            return $config;
        }

        $data = [];
        foreach ($config as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }

    private function normalizeConnection(array $connection): array
    {
        $normalized = [
            'driver' => $connection['driver'] ?? $connection['DRIVER'] ?? 'mysql',
            'host' => $connection['host'] ?? $connection['HOST'] ?? 'localhost',
            'database' => $connection['database'] ?? $connection['DATABASE'] ?? '',
            'username' => $connection['username'] ?? $connection['USER'] ?? $connection['user'] ?? '',
            'password' => $connection['password'] ?? $connection['PASSWORD'] ?? '',
            'charset' => $connection['charset'] ?? $connection['CHARSET'] ?? 'utf8mb4',
            'collation' => $connection['collation'] ?? $connection['COLLATION'] ?? 'utf8mb4_unicode_ci',
            'prefix' => $connection['prefix'] ?? $connection['PREFIX'] ?? '',
        ];

        foreach ($connection as $key => $value) {
            if (is_string($key) && ctype_lower($key)) {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    private function enabled(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? (bool)$value;
    }
}
