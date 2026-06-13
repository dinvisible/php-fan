<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_meta_view_registrar_dependencies
{
    private application_support_delayed_meta_factory_registrar_dependencies $delayedMetaFactory;
    private application_support_recursive_merger_registrar_dependencies $recursiveMerger;
    private application_support_array_adducer_registrar_dependencies $arrayAdducer;
    private application_support_class_name_resolver_registrar_dependencies $classNameResolver;
    private application_support_view_loader_json_keeper_factory_registrar_dependencies $viewLoaderJsonKeeperFactory;
    private application_support_view_loader_text_keeper_factory_registrar_dependencies $viewLoaderTextKeeperFactory;
    private application_support_view_keeper_factory_registrar_dependencies $viewKeeperFactory;
    private application_support_php_runtime_settings_registrar_dependencies $phpRuntimeSettings;

    public function __construct(container_interface $container)
    {
        $this->delayedMetaFactory = new application_support_delayed_meta_factory_registrar_dependencies($container);
        $this->recursiveMerger = new application_support_recursive_merger_registrar_dependencies($container);
        $this->arrayAdducer = new application_support_array_adducer_registrar_dependencies($container);
        $this->classNameResolver = new application_support_class_name_resolver_registrar_dependencies($container);
        $this->viewLoaderJsonKeeperFactory = new application_support_view_loader_json_keeper_factory_registrar_dependencies($container);
        $this->viewLoaderTextKeeperFactory = new application_support_view_loader_text_keeper_factory_registrar_dependencies($container);
        $this->viewKeeperFactory = new application_support_view_keeper_factory_registrar_dependencies($container);
        $this->phpRuntimeSettings = new application_support_php_runtime_settings_registrar_dependencies($container);
    }

    public function delayedMetaFactory(): callable
    {
        return $this->delayedMetaFactory->delayedMetaFactory();
    }

    public function recursiveMerger(): callable
    {
        return $this->recursiveMerger->recursiveMerger();
    }

    public function arrayAdducer(): callable
    {
        return $this->arrayAdducer->arrayAdducer();
    }

    public function classNameResolver(): callable
    {
        return $this->classNameResolver->classNameResolver();
    }

    public function viewLoaderJsonKeeperFactory(): callable
    {
        return $this->viewLoaderJsonKeeperFactory->viewLoaderJsonKeeperFactory();
    }

    public function viewLoaderTextKeeperFactory(): callable
    {
        return $this->viewLoaderTextKeeperFactory->viewLoaderTextKeeperFactory();
    }

    public function viewKeeperFactory(): callable
    {
        return $this->viewKeeperFactory->viewKeeperFactory();
    }

    public function phpRuntimeSettings(): object
    {
        return $this->phpRuntimeSettings->phpRuntimeSettings();
    }
}
