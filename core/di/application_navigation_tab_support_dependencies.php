<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_support_dependencies
{
    private application_navigation_tab_support_block_dependencies $block;
    private application_navigation_tab_support_helper_dependencies $helper;
    private application_navigation_tab_support_asset_dependencies $asset;

    public function __construct(container_interface $container)
    {
        $this->block = new application_navigation_tab_support_block_dependencies($container);
        $this->helper = new application_navigation_tab_support_helper_dependencies($container);
        $this->asset = new application_navigation_tab_support_asset_dependencies($container);
    }

    public function tabState(): mixed
    {
        return $this->block->tabState();
    }

    public function phpArrayFileLoader(): mixed
    {
        return $this->helper->phpArrayFileLoader();
    }

    public function blockFactory(): mixed
    {
        return $this->block->blockFactory();
    }

    public function blockExceptionFactory(): mixed
    {
        return $this->block->blockExceptionFactory();
    }

    public function metaRowFactory(): mixed
    {
        return $this->block->metaRowFactory();
    }

    public function tabAliasFileStorage(): mixed
    {
        return $this->asset->tabAliasFileStorage();
    }

    public function arrayAdducer(): mixed
    {
        return $this->helper->arrayAdducer();
    }

    public function recursiveMerger(): mixed
    {
        return $this->helper->recursiveMerger();
    }

    public function arrayValueReader(): mixed
    {
        return $this->helper->arrayValueReader();
    }

    public function classNameResolver(): mixed
    {
        return $this->helper->classNameResolver();
    }

    public function arrayLikeChecker(): mixed
    {
        return $this->helper->arrayLikeChecker();
    }

    public function shortClassNameResolver(): mixed
    {
        return $this->helper->shortClassNameResolver();
    }

    public function imageMetadataReader(): mixed
    {
        return $this->asset->imageMetadataReader();
    }

    public function errorLogWriter(): mixed
    {
        return $this->asset->errorLogWriter();
    }
}
