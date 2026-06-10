<?php

declare(strict_types=1);

namespace fan\core\service;

class plain_file_context
{
    public function __construct(
        private ?object $request = null,
        private ?object $application = null,
        private ?object $databaseConnections = null,
        private ?object $translation = null,
        private mixed $cacheFactory = null,
        private ?object $runtime = null,
        private ?object $entity = null,
        private mixed $imageModifyFactory = null,
        private ?object $imageMetadataReader = null,
        private ?object $fileStorage = null,
        private mixed $plainExceptionFactory = null
    )
    {
    }

    public function request(): object
    {
        return $this->requireObject($this->request, 'Request service');
    }

    public function setApplicationName(string $appName): void
    {
        $this->requireObject($this->application, 'Application service')->setAppName($appName);
    }

    public function closeDatabaseConnections(): void
    {
        if ($this->databaseConnections !== null) {
            $this->databaseConnections->close();
        }
    }

    public function message(string $key): string
    {
        return (string)$this->requireObject($this->translation, 'Translation service')->getMessage($key);
    }

    public function cache(string $type): object
    {
        if (!is_callable($this->cacheFactory)) {
            throw new \RuntimeException('Cache service factory is not configured for plain file context.');
        }

        return $this->requireObject(($this->cacheFactory)($type), 'Cache service');
    }

    public function parsePath(string $path): string
    {
        return (string)$this->requireObject($this->runtime, 'Bootstrap runtime service')->parsePath($path);
    }

    public function fileDataRow(): object
    {
        $entity = $this->requireObject($this->entity, 'Entity service');

        return $entity->get((string)$entity->getFileNsSuffix() . 'file_data')->getNewRow();
    }

    public function imageModify(string $sourcePath): object
    {
        if (!is_callable($this->imageModifyFactory)) {
            throw new \RuntimeException('Image modify service factory is not configured for plain file context.');
        }

        return $this->requireObject(($this->imageModifyFactory)($sourcePath), 'Image modify service');
    }

    public function imageMetadataReader(): object
    {
        return $this->requireObject($this->imageMetadataReader, 'Image metadata reader');
    }

    public function fileStorage(): object
    {
        return $this->requireObject($this->fileStorage, 'Plain file storage');
    }

    public function createPlainFatalException(object $controller, string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        if (!is_callable($this->plainExceptionFactory)) {
            throw new \RuntimeException('Plain exception factory is not configured for plain file context.');
        }

        $exception = ($this->plainExceptionFactory)(
            '\fan\project\exception\plain\fatal',
            $controller,
            $message,
            $code,
            $previous
        );
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Plain exception factory must return a throwable object.');
        }

        return $exception;
    }

    private function requireObject(mixed $service, string $name): object
    {
        if (is_object($service)) {
            return $service;
        }

        throw new \RuntimeException($name . ' is not configured for plain file context.');
    }
}
