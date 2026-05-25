<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\di\bootstrap_runtime_state_defaults_factory;
use fan\core\base\meta\maker_state;
use fan\core\base\model\spec_file\image\row_state;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;
use fan\core\view\router\loader_state;


final class bootstrap_runtime_state_defaults_provider_factory
{
    private \Closure $serviceListenerStateFactory;
    private \Closure $serviceSingleStateFactory;
    private \Closure $viewLoaderStateFactory;
    private \Closure $metaMakerStateFactory;
    private \Closure $specFileImageRowStateFactory;
    private \Closure $viewLoaderStateFactoryFactory;
    private \Closure $viewLoaderJsonKeeperFactory;
    private \Closure $viewLoaderTextKeeperFactory;
    private \Closure $viewLoaderStateFactoryProvider;
    private \Closure $viewLoaderJsonKeeperFactoryProvider;
    private \Closure $viewLoaderTextKeeperFactoryProvider;
    private \Closure $serviceListenerStateProvider;
    private \Closure $serviceSingleStateProvider;
    private \Closure $metaMakerStateProvider;
    private \Closure $specFileImageRowStateProvider;

    public function __construct(
        ?callable $serviceListenerStateFactory = null,
        ?callable $serviceSingleStateFactory = null,
        ?callable $viewLoaderStateFactory = null,
        ?callable $metaMakerStateFactory = null,
        ?callable $specFileImageRowStateFactory = null,
        ?callable $viewLoaderStateFactoryFactory = null,
        ?callable $viewLoaderJsonKeeperFactory = null,
        ?callable $viewLoaderTextKeeperFactory = null,
        ?callable $viewLoaderStateFactoryProvider = null,
        ?callable $viewLoaderJsonKeeperFactoryProvider = null,
        ?callable $viewLoaderTextKeeperFactoryProvider = null,
        ?callable $serviceListenerStateProvider = null,
        ?callable $serviceSingleStateProvider = null,
        ?callable $metaMakerStateProvider = null,
        ?callable $specFileImageRowStateProvider = null
    ) {
        $this->viewLoaderStateFactoryProvider = \Closure::fromCallable(
            $viewLoaderStateFactoryProvider
                ?? static fn(callable $jsonKeeperFactory, callable $textKeeperFactory): callable => new view_loader_state_factory($jsonKeeperFactory, $textKeeperFactory)
        );
        $this->viewLoaderJsonKeeperFactoryProvider = \Closure::fromCallable(
            $viewLoaderJsonKeeperFactoryProvider
                ?? static fn(): callable => new view_loader_json_keeper_factory()
        );
        $this->viewLoaderTextKeeperFactoryProvider = \Closure::fromCallable(
            $viewLoaderTextKeeperFactoryProvider
                ?? static fn(): callable => new view_loader_text_keeper_factory()
        );
        $this->serviceListenerStateProvider = \Closure::fromCallable(
            $serviceListenerStateProvider
                ?? static fn(): object => new service_listener_state()
        );
        $this->serviceSingleStateProvider = \Closure::fromCallable(
            $serviceSingleStateProvider
                ?? static fn(): object => new service_single_state()
        );
        $this->metaMakerStateProvider = \Closure::fromCallable(
            $metaMakerStateProvider
                ?? static fn(): object => new maker_state()
        );
        $this->specFileImageRowStateProvider = \Closure::fromCallable(
            $specFileImageRowStateProvider
                ?? static fn(): object => new row_state()
        );
        $this->viewLoaderStateFactoryFactory = \Closure::fromCallable(
            $viewLoaderStateFactoryFactory
                ?? fn(callable $jsonKeeperFactory, callable $textKeeperFactory): callable => ($this->viewLoaderStateFactoryProvider)($jsonKeeperFactory, $textKeeperFactory)
        );
        $this->viewLoaderJsonKeeperFactory = \Closure::fromCallable(
            $viewLoaderJsonKeeperFactory
                ?? fn(): callable => ($this->viewLoaderJsonKeeperFactoryProvider)()
        );
        $this->viewLoaderTextKeeperFactory = \Closure::fromCallable(
            $viewLoaderTextKeeperFactory
                ?? fn(): callable => ($this->viewLoaderTextKeeperFactoryProvider)()
        );
        $this->serviceListenerStateFactory = \Closure::fromCallable(
            $serviceListenerStateFactory
                ?? fn(): object => ($this->serviceListenerStateProvider)()
        );
        $this->serviceSingleStateFactory = \Closure::fromCallable(
            $serviceSingleStateFactory
                ?? fn(): object => ($this->serviceSingleStateProvider)()
        );
        $this->viewLoaderStateFactory = \Closure::fromCallable(
            $viewLoaderStateFactory
                ?? function (): object {
                    $jsonKeeperFactory = ($this->viewLoaderJsonKeeperFactory)();
                    $textKeeperFactory = ($this->viewLoaderTextKeeperFactory)();
                    $viewLoaderStateFactory = ($this->viewLoaderStateFactoryFactory)(
                        $jsonKeeperFactory,
                        $textKeeperFactory
                    );

                    return $viewLoaderStateFactory();
                }
        );
        $this->metaMakerStateFactory = \Closure::fromCallable(
            $metaMakerStateFactory
                ?? fn(): object => ($this->metaMakerStateProvider)()
        );
        $this->specFileImageRowStateFactory = \Closure::fromCallable(
            $specFileImageRowStateFactory
                ?? fn(): object => ($this->specFileImageRowStateProvider)()
        );
    }

    public function __invoke(): bootstrap_runtime_state_defaults_factory
    {
        $serviceListenerStateFactory = $this->serviceListenerStateFactory;
        $serviceSingleStateFactory = $this->serviceSingleStateFactory;
        $viewLoaderStateFactory = $this->viewLoaderStateFactory;
        $metaMakerStateFactory = $this->metaMakerStateFactory;
        $specFileImageRowStateFactory = $this->specFileImageRowStateFactory;

        return new bootstrap_runtime_state_defaults_factory(
            static fn(): callable => static fn(): array => [
                'serviceListenerState' => $serviceListenerStateFactory(),
                'serviceSingleState' => $serviceSingleStateFactory(),
                'viewLoaderState' => $viewLoaderStateFactory(),
                'metaMakerState' => $metaMakerStateFactory(),
                'specFileImageRowState' => $specFileImageRowStateFactory(),
            ]
        );
    }
}
