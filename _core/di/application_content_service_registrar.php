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
                'block_factory',
                static fn(container_interface $container): callable => $context->blockFactory
            )
            ->factory(
                'translation',
                static fn(container_interface $container): mixed => $contentServiceCreator->createTranslationService(
                    $container,
                    $context->translationServiceFactory
                )
            );
    }
}
