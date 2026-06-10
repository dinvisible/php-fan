<?php

declare(strict_types=1);

namespace fan\core\runtime;

final class error_demonstrator_factory
{
    public function __construct(
        private object $headerWriter,
        private object $errorLogWriter,
        private object $fileStorage,
        private object $demonstratorLoader
    )
    {
    }

    public function __invoke(array $errMsg, string $tplName, object $input, callable $phpArrayFileLoader): object
    {
        $demonstrator = $this->demonstratorLoader()->create($errMsg, $tplName, $input);
        if (method_exists($demonstrator, 'setPhpArrayFileLoader')) {
            $demonstrator->setPhpArrayFileLoader($phpArrayFileLoader);
        }
        if (method_exists($demonstrator, 'setHeaderWriter')) {
            $demonstrator->setHeaderWriter($this->headerWriter());
        }
        if (method_exists($demonstrator, 'setErrorLogWriter')) {
            $demonstrator->setErrorLogWriter($this->errorLogWriter());
        }
        if (method_exists($demonstrator, 'setFileStorage')) {
            $demonstrator->setFileStorage($this->fileStorage());
        }

        return $demonstrator;
    }

    private function headerWriter(): object
    {
        return $this->headerWriter;
    }

    private function errorLogWriter(): object
    {
        return $this->errorLogWriter;
    }

    private function fileStorage(): object
    {
        return $this->fileStorage;
    }

    private function demonstratorLoader(): object
    {
        return $this->demonstratorLoader;
    }
}
