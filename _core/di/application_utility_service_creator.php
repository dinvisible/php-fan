<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_service_creator
{
    private \Closure $dateExceptionFactory;

    public function __construct(
        callable $dateExceptionFactory
    ) {
        $this->dateExceptionFactory = \Closure::fromCallable($dateExceptionFactory);
    }

    public function createObfuscatorService(
        container_interface $container,
        object $state,
        callable $obfuscatorServiceFactory,
        string $type
    ): mixed {
        $type = strtolower((string)$type);
        $instance = $state->getInstance($type);
        if ($instance === null) {
            $className = self::getProjectServiceClassName('obfuscator');
            if (!class_exists($className)) {
                throw new \InvalidArgumentException('Service "obfuscator" does not expose a project class.');
            }

            $instance = $obfuscatorServiceFactory(
                $className,
                $type,
                $container->get('bootstrap_runtime'),
                $container->get('config'),
                static fn(string $cacheType): mixed => $container->get('cache', $cacheType),
                $container->get('php_array_file_loader'),
                $container->get('obfuscator_file_storage')
            );
            $state->setInstance($type, $instance);
        }

        return $instance;
    }

    public function createImageModifyService(
        container_interface $container,
        object $state,
        callable $imageModifyServiceFactory,
        string $serviceName,
        ?string $sourcePath = null,
        array $createParam = [],
        bool $saveInstance = true
    ): mixed {
        $className = self::getProjectServiceClassName($serviceName);
        $instance = $state->getInstance($className);
        if (!$saveInstance || $instance === null) {
            if (!class_exists($className)) {
                throw new \InvalidArgumentException('Service "' . $serviceName . '" does not expose a project class.');
            }

            $instance = $imageModifyServiceFactory(
                $className,
                $sourcePath,
                $createParam,
                $state,
                $container->get('bootstrap_runtime'),
                $container->get('bootstrap_runtime'),
                $container->get('config'),
                static fn(string $type): mixed => $container->get('cache', $type),
                $container->get('image_metadata_reader'),
                $container->get('image_resource_factory'),
                $container->get('image_canvas_operations'),
                $container->get('image_output_writer'),
                $container->get('image_source_file_storage'),
                $container->get('array_value_reader')
            );
            if (!$saveInstance) {
                return $instance;
            }
            $state->setInstance($className, $instance);
        }

        return $instance;
    }

    public function createSoapService(
        container_interface $container,
        callable $soapServiceFactory,
        string $wsdlFile,
        ?array $param = null,
        bool $logEnabled = true
    ): mixed {
        $className = self::getProjectServiceClassName('soap');
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Service "soap" does not expose a project class.');
        }

        $soap = $soapServiceFactory(
            $className,
            (bool)$logEnabled,
            static fn(): mixed => $container->get('error'),
            $container->get('bootstrap_runtime'),
            $container->get('bootstrap_runtime'),
            $container->get('config'),
            static fn(string $type): mixed => $container->get('cache', $type),
            $container->get('php_runtime_settings'),
            $container->get('soap_wsdl_file_storage'),
            $container->get('array_value_reader'),
            $container->get('class_name_resolver')
        );
        $soap->initializeSoapObject($wsdlFile, $param);

        return $soap;
    }

    public function createDateService(
        container_interface $container,
        object $state,
        callable $dateServiceFactory,
        ?string $date = null,
        mixed $format = null,
        mixed $timezone = null,
        bool $save = true
    ): mixed {
        $config = $state->getGlobalConfig();
        if (empty($config)) {
            $config = $container->get('config')->get('date');
            $state->setGlobalConfig($config);
        }

        $timezoneDefault = (string)$config->get('TIMEZONE', 'Europe/Kiev');
        if (!$state->hasInstances()) {
            date_default_timezone_set($timezoneDefault);
        }
        if ($timezone === null) {
            $timezone = $timezoneDefault;
        }

        $dateObject = null;
        $isTime = null;
        if ($format === null) {
            $date = null;
            foreach ($config->get('DEFAULT_FORMAT', []) as $candidateFormat) {
                [$isTime, $dateObject] = $this->parseDateForService($config, $date, (string)$candidateFormat, (string)$timezone);
                if ($dateObject !== null) {
                    $format = (string)$candidateFormat;
                    break;
                }
            }
        } else {
            [$isTime, $dateObject] = $this->parseDateForService($config, $date, (string)$format, (string)$timezone);
        }

        if ($dateObject === null) {
            throw $this->createDateException('Can\'t get date by "' . $date . '" format "' . $format . '".');
        }

        $key3 = $dateObject->format('YmdHisu');
        $instance = $state->getInstance((bool)$isTime, (string)$timezone, (string)$format, $key3);
        if (!$save || $instance === null) {
            $className = self::getProjectServiceClassName('date');
            if (!class_exists($className)) {
                throw new \InvalidArgumentException('Service "date" does not expose a project class.');
            }

            $creator = $this;

            return $dateServiceFactory(
                $className,
                $dateObject,
                $format,
                $isTime,
                $timezone,
                $save,
                $state,
                static fn(?string $date = null, mixed $format = null, mixed $timezone = null, bool $save = true): mixed =>
                    $creator->createDateService($container, $state, $dateServiceFactory, $date, $format, $timezone, $save),
                $container->get('bootstrap_runtime'),
                $container->get('config'),
                static fn(string $type): mixed => $container->get('cache', $type),
                $container->get('class_name_resolver'),
                $container->get('array_value_reader')
            );
        }

        return $instance;
    }

    private function parseDateForService(object $config, ?string $date, string $format, string $timezone): array
    {
        $confFormat = $config->get(['FORMAT', $format]);
        if ($confFormat === null) {
            throw $this->createDateException('Requested format "' . $format . '" isn\'t found.');
        }

        $timezoneObject = new \DateTimeZone($timezone);
        $dateValue = (string)$date;

        $fullFormat = $confFormat->get('full_pattern');
        $dateObject = \DateTime::createFromFormat((string)$fullFormat, $dateValue, $timezoneObject);
        if (!is_bool($dateObject)) {
            return [true, $dateObject];
        }

        $shortFormat = (string)$confFormat->get('short_pattern') . ' H:i:s';
        $dateObject = \DateTime::createFromFormat($shortFormat, $dateValue . ' 00:00:00', $timezoneObject);

        return is_bool($dateObject) ? [null, null] : [false, $dateObject];
    }

    private function createDateException(string $message): \Throwable
    {
        $exception = ($this->dateExceptionFactory)($message);
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Date exception factory must return Throwable.');
        }

        return $exception;
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }
}
