<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\bootstrap\context_container_factory;
use fan\core\di\context_core_defaults_factory;

final class context_core_defaults_provider_factory
{
    public function __invoke(): context_core_defaults_factory
    {

        return new context_core_defaults_factory(
            static function (): array {
                $contextStateDefaults = new bootstrap_state_defaults_factory();
                $contextApplicationContainerDefaults = (new application_container_defaults_provider_factory())();
                $contextContainerDefaults = new context_container_defaults_factory(
                    static fn(callable $applicationContainerFactory): context_container_factory => new context_container_factory(
                        $applicationContainerFactory
                    ),
                    $contextApplicationContainerDefaults->containerFactory()
                );
                $contextRequestInputDefaults = new bootstrap_request_input_defaults_factory();
                $contextRuntimeDefaults = new bootstrap_runtime_defaults_factory();

                return [
                    'stateFactory' => $contextStateDefaults->stateFactory(),
                    'containerFactory' => $contextContainerDefaults(),
                    'requestInputFactory' => $contextRequestInputDefaults->requestInputFactory(),
                    'bootstrapRuntimeFactory' => $contextRuntimeDefaults(),
                ];
            }
        );
    }
}
