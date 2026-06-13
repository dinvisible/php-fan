<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_service_registrar
{
    public function __construct(private ?\Closure $dependenciesFactory = null)
    {
    }

    public function register(
        container $container,
        application_service_graph_registration_context $context
    ): container {
        $utilityServiceCreator = $context->utilityServiceCreator;
        $dateServiceFactory = $context->dateServiceFactory;
        $obfuscatorServiceFactory = $context->obfuscatorServiceFactory;
        $imageModifyServiceFactory = $context->imageModifyServiceFactory;
        $soapServiceFactory = $context->soapServiceFactory;
        $dependenciesFactory = $this->dependenciesFactory();

        return $container
            ->factory(
                service_id::DATE,
                static fn(container_interface $container, ?string $date = null, mixed $format = null, mixed $timezone = null, bool $save = true): mixed => $utilityServiceCreator->createDateService(
                    $container,
                    $dependenciesFactory($container)->dateState(),
                    $dateServiceFactory,
                    $date,
                    $format,
                    $timezone,
                    $save
                ),
                false
            )
            ->factory(
                service_id::OBFUSCATOR,
                static fn(container_interface $container, string $type): mixed => $utilityServiceCreator->createObfuscatorService(
                    $container,
                    $dependenciesFactory($container)->obfuscatorState(),
                    $obfuscatorServiceFactory,
                    $type
                ),
                false
            )
            ->factory(
                service_id::IMAGE_MODIFY,
                static fn(container_interface $container, ?string $sourcePath = null, array $createParam = [], bool $saveInstance = true): mixed => $utilityServiceCreator->createImageModifyService(
                    $container,
                    $dependenciesFactory($container)->imageModifyState(),
                    $imageModifyServiceFactory,
                    service_id::IMAGE_MODIFY,
                    $sourcePath,
                    $createParam,
                    $saveInstance
                ),
                false
            )
            ->factory(
                service_id::IMAGE_DRAW,
                static fn(container_interface $container, ?string $sourcePath = null, array $createParam = [], bool $saveInstance = true): mixed => $utilityServiceCreator->createImageModifyService(
                    $container,
                    $dependenciesFactory($container)->imageModifyState(),
                    $imageModifyServiceFactory,
                    service_id::IMAGE_DRAW,
                    $sourcePath,
                    $createParam,
                    $saveInstance
                ),
                false
            )
            ->factory(
                service_id::SOAP,
                static fn(container_interface $container, string $wsdlFile, ?array $param = null, bool $logEnabled = true): mixed => $utilityServiceCreator->createSoapService(
                    $container,
                    $soapServiceFactory,
                    $wsdlFile,
                    $param,
                    $logEnabled
                ),
                false
            );
    }

    private function dependenciesFactory(): \Closure
    {
        return $this->dependenciesFactory
            ?? static fn(container_interface $container): application_utility_service_registrar_dependencies => new application_utility_service_registrar_dependencies($container);
    }
}
