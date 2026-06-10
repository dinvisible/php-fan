<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\soap as core_soap_service;

final class soap_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        bool $logEnabled,
        callable $errorFactory,
        object $runtime,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        object $phpRuntimeSettings,
        object $wsdlFileStorage,
        ?callable $arrayValueReader = null,
        ?callable $classNameResolver = null
    ): mixed {
        $arguments = [
            $logEnabled,
            $errorFactory,
            $runtime,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $phpRuntimeSettings,
            $wsdlFileStorage,
            $arrayValueReader ?? static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default),
            $classNameResolver ?? static fn(object $object): string => \get_class_alt($object) ?? get_class($object),
            static fn(string $nameSpace, array $name, ?array $data = null): \SoapHeader => new \SoapHeader($nameSpace, (string)($name[0] ?? ''), $data),
            static fn(string $wsdlFile, ?array $param = null): \SoapClient => $param === null ? new \SoapClient($wsdlFile) : new \SoapClient($wsdlFile, $param),
            static fn(
                mixed $data,
                int $encoding,
                ?string $typeName = null,
                ?string $typeNamespace = null,
                ?string $nodeName = null,
                ?string $nodeNamespace = null
            ): \SoapVar => new \SoapVar($data, $encoding, $typeName, $typeNamespace, $nodeName, $nodeNamespace),
            static fn(): \DOMDocument => new \DOMDocument(),
            static fn(array $options): mixed => \stream_context_create($options)
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_soap_service::class,
            $arguments,
            $arguments
        );
    }

}
