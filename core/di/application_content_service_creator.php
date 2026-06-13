<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_content_service_creator
{
    private \Closure $projectServiceClassExists;

    public function __construct(?callable $projectServiceClassExists = null)
    {
        $this->projectServiceClassExists = \Closure::fromCallable(
            $projectServiceClassExists ?? static fn(string $className): bool => class_exists($className)
        );
    }

    public function createTranslationService(
        container_interface $container,
        callable $translationServiceFactory
    ): mixed
    {
        $className = self::getProjectServiceClassName('translation');
        if (!$this->projectServiceClassExists($className)) {
            throw new \InvalidArgumentException('Service "translation" does not expose a project class.');
        }
        $contentDependencies = $this->contentDependencies($container);

        return $translationServiceFactory(
            $className,
            true,
            $contentDependencies->locale(),
            $contentDependencies->bootstrapRuntime(),
            $contentDependencies->tabFactory(),
            [],
            $contentDependencies->error(),
            $contentDependencies->blockContext(),
            $contentDependencies->matcher(),
            $contentDependencies->requestInput(),
            $contentDependencies->bootstrapRuntime(),
            $contentDependencies->config(),
            $contentDependencies->cacheFactory(),
            $contentDependencies->phpArrayFileLoader(),
            $contentDependencies->translationFileStorage()
        );
    }

    private function contentDependencies(container_interface $container): application_content_service_dependencies
    {
        return new application_content_service_dependencies($container);
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }

    private function projectServiceClassExists(string $className): bool
    {
        return (bool)($this->projectServiceClassExists)($className);
    }
}
