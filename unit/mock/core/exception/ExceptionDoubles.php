<?php

declare(strict_types=1);

namespace {
    require_once __DIR__ . '/../../../mock/core/block/GlobalFunctions.php';
    require_once __DIR__ . '/../../../mock/core/block/FrameworkStubs.php';
    require_once __DIR__ . '/../../../mock/core/block/FakeServices.php';
    require_once __DIR__ . '/../../../mock/core/base/TransferStubs.php';
    require_once __DIR__ . '/../../../../core/base/service.php';
    require_once __DIR__ . '/../../../../core/base/model/entity.php';
}

namespace FanTest\core\exception {
    class FakeRequestService
    {
        public string $info = 'GET /unit-test';

        public function getInfoString(): string
        {
            return $this->info;
        }
    }

    class FakeErrorService
    {
        public array $messages = [];
        public array $exceptionMessages = [];
        public array $databaseErrors = [];

        public function logErrorMessage($message, $title = '', $note = '', $fixPosition = false): void
        {
            $this->messages[] = [$message, $title, $note, $fixPosition];
        }

        public function logExceptionMessage(string $message, string $header = '', string $note = ''): void
        {
            $this->exceptionMessages[] = [$message, $header, $note];
        }

        public function logDatabaseError($connectionName, string $operation, $errorMessage, int|float $errorNum, $parsedSql): void
        {
            $this->databaseErrors[] = [$connectionName, $operation, $errorMessage, $errorNum, $parsedSql];
        }
    }

    class TestService extends \fan\core\base\service
    {
        public string $configType = 'service';

        public function __construct(?string $logType = 'service', ?string $dbOper = null)
        {
            $this->setExceptionLogType($logType);
            $this->setExceptionDbOper($dbOper);
        }

        public function isSingleton(): bool
        {
            return true;
        }

        public function getConfigType(): string
        {
            return $this->configType;
        }

        /**
         * @param mixed $value Value that should be applied or transformed.
         */
        public function setConfigValue(\fan\core\service\config\row $row, mixed $key, mixed $value): \fan\core\service\config\row
        {
            return $row->set($key, $value);
        }

        public function resetConfigValue(\fan\core\service\config\row $row, mixed $key): \fan\core\service\config\row
        {
            return $row->reset($key);
        }

        public function mergeConfigData(\fan\core\service\config\row $row, array|\fan\core\service\config\row $data, bool $priority = true): \fan\core\service\config\row
        {
            return $row->mergeData($data, $priority);
        }
    }

    class TestDatabaseService extends \fan\core\base\service
    {
        private ?string $connectionName = null;

        public function __construct(?string $connectionName = 'main', ?string $logType = 'nothing')
        {
            $this->connectionName = $connectionName;
            $this->setExceptionLogType($logType);
        }

        public function isSingleton(): bool
        {
            return false;
        }

        public function getConnectionName(): ?string
        {
            return $this->connectionName;
        }
    }

    class TestEntity extends \fan\core\base\model\entity
    {
        public function __construct()
        {
        }

        /**
         * Implements PHP magic behavior for this current component.
         *
         * @return string String representation used by exception tests.
         */
        public function __toString(): string
        {
            return 'entity snapshot';
        }
    }
}

namespace fan\project\service {
    if (!class_exists(__NAMESPACE__ . '\\error', false)) {
        class error extends \FanTest\core\exception\FakeErrorService
        {
            public static ?object $instance = null;

            public static function instance(): self
            {
                if (empty(self::$instance)) {
                    self::$instance = new self();
                }
                return self::$instance;
            }

            public static function reset(): void
            {
                self::$instance = new self();
                if (class_exists('\FanTest\core\block\FakeServiceRegistry', false)) {
                    \FanTest\core\block\FakeServiceRegistry::set('error', self::$instance);
                }
            }
        }
    }
}
