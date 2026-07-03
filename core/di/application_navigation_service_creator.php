<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_service_creator
{
    private \Closure $projectServiceClassExists;

    public function __construct(?callable $projectServiceClassExists = null)
    {
        $this->projectServiceClassExists = \Closure::fromCallable(
            $projectServiceClassExists ?? static fn(string $className): bool => class_exists($className)
        );
    }

    public function createTabService(
        container_interface $container,
        callable $tabDelegateFactory,
        callable $tabViewParserFactory,
        callable $tabServiceFactory
    ): mixed {
        $className = self::getProjectServiceClassName('tab');
        if (!$this->projectServiceClassExists($className)) {
            throw new \InvalidArgumentException('Service "tab" does not expose a project class.');
        }
        $common = $this->commonDependencies($container);
        $tabDependencies = $this->tabDependencies($container);

        $tab = $tabServiceFactory(
            $className,
            true,
            $tabDependencies->matcher(),
            $tabDependencies->request(),
            $tabDependencies->locale(),
            $tabDependencies->sessionFactory(),
            $tabDependencies->requestInput(),
            $common->bootstrapRuntime(),
            $tabDependencies->roleFactory(),
            $tabDependencies->transferFactory(),
            $tabDependencies->configFactory(),
            $tabDependencies->headerFactory(),
            $tabDependencies->applicationFactory(),
            $tabDependencies->debugFactory(),
            $tabDependencies->jsonFactory(),
            $tabDependencies->dataLoaderFactory(),
            static fn(): mixed => throw new \RuntimeException('Template service has been removed.'),
            $tabDependencies->errorFactory(),
            null,
            $tabDependencies->cookieFactory(),
            $tabDependencies->reflectorFactory(),
            $tabDependencies->entityFactory(),
            null,
            $tabDependencies->pagerFactory(),
            $tabDependencies->obfuscatorFactory(),
            $tabDependencies->imageModifyFactory(),
            $tabDependencies->databaseFactory(),
            $tabDependencies->userFactory(),
            $tabDependencies->dateFactory(),
            $tabDependencies->tabState(),
            $common->bootstrapRuntime(),
            $common->config(),
            $common->cacheFactory(),
            $tabDependencies->phpArrayFileLoader(),
            $tabDelegateFactory,
            $tabViewParserFactory,
            $tabDependencies->blockFactory(),
            $tabDependencies->blockExceptionFactory(),
            $tabDependencies->metaRowFactory(),
            $tabDependencies->tabAliasFileStorage(),
            $tabDependencies->arrayAdducer(),
            $tabDependencies->recursiveMerger(),
            $tabDependencies->arrayValueReader(),
            $tabDependencies->classNameResolver(),
            $tabDependencies->arrayLikeChecker(),
            $tabDependencies->shortClassNameResolver(),
            $tabDependencies->imageMetadataReader(),
            $tabDependencies->errorLogWriter(),
            $tabDependencies->blockFileStorage(),
            $tabDependencies->metaFileStorage(),
            $tabDependencies->projectToolFileStorage(),
            $tabDependencies->rootHtmlFileStorage()
        );
        if (method_exists($tab, 'setUploadSizeLimitProvider')) {
            $tab->setUploadSizeLimitProvider($tabDependencies->uploadSizeLimitProvider());
        }

        return $tab;
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }

    private function projectServiceClassExists(string $className): bool
    {
        return ($this->projectServiceClassExists)($className);
    }

    private function commonDependencies(container_interface $container): application_creator_common_dependencies
    {
        return new application_creator_common_dependencies($container);
    }

    private function tabDependencies(container_interface $container): application_navigation_tab_dependencies
    {
        return new application_navigation_tab_dependencies($container);
    }
}
