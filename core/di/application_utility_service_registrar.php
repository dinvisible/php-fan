<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_service_registrar
{
    public function register(
        container $container,
        application_service_graph_registration_context $context
    ): container {
        $utilityServiceCreator = $context->utilityServiceCreator;
        $dateServiceFactory = $context->dateServiceFactory;
        $obfuscatorServiceFactory = $context->obfuscatorServiceFactory;
        $imageModifyServiceFactory = $context->imageModifyServiceFactory;
        $soapServiceFactory = $context->soapServiceFactory;

        return $container
            ->factory(
                'date',
                static fn(container_interface $container, ?string $date = null, mixed $format = null, mixed $timezone = null, bool $save = true): mixed => $utilityServiceCreator->createDateService(
                    $container,
                    $container->get('date_state'),
                    $dateServiceFactory,
                    $date,
                    $format,
                    $timezone,
                    $save
                ),
                false
            )
            ->factory(
                'obfuscator',
                static fn(container_interface $container, string $type): mixed => $utilityServiceCreator->createObfuscatorService(
                    $container,
                    $container->get('obfuscator_state'),
                    $obfuscatorServiceFactory,
                    $type
                ),
                false
            )
            ->factory(
                'image_modify',
                static fn(container_interface $container, ?string $sourcePath = null, array $createParam = [], bool $saveInstance = true): mixed => $utilityServiceCreator->createImageModifyService(
                    $container,
                    $container->get('image_modify_state'),
                    $imageModifyServiceFactory,
                    'image_modify',
                    $sourcePath,
                    $createParam,
                    $saveInstance
                ),
                false
            )
            ->factory(
                'image_draw',
                static fn(container_interface $container, ?string $sourcePath = null, array $createParam = [], bool $saveInstance = true): mixed => $utilityServiceCreator->createImageModifyService(
                    $container,
                    $container->get('image_modify_state'),
                    $imageModifyServiceFactory,
                    'image_draw',
                    $sourcePath,
                    $createParam,
                    $saveInstance
                ),
                false
            )
            ->factory(
                'soap',
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
}
