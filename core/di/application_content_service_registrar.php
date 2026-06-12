<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_content_service_registrar
{
    public function register(container $container, application_service_graph_registration_context $context): container
    {
        $contentServiceCreator = $context->contentServiceCreator;

        return $container
            ->factory(
                service_id::BLOCK_FACTORY,
                static fn(container_interface $container): callable => $context->blockFactory
            )
            ->factory(
                service_id::TRANSLATION,
                static fn(container_interface $container): mixed => $contentServiceCreator->createTranslationService(
                    $container,
                    $context->translationServiceFactory
                )
            );
    }
}
