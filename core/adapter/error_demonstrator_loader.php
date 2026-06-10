<?php

declare(strict_types=1);

namespace fan\core\adapter;
use fan\core\error\demonstrator as error_demonstrator;
use fan\project\error\demonstrator;


final class error_demonstrator_loader
{
    public const MAIN_ERROR_DEMONSTRATOR = '{CORE_DIR}/error/demonstrator.php';
    public const PROJECT_ERROR_DEMONSTRATOR = '{PROJECT_DIR}/error/demonstrator.php';

    public function __construct(private object $fileStorage)
    {
    }

    public function create(array $errMsg, string $tplName, object $input): object
    {
        require_once str_replace('{CORE_DIR}', CORE_DIR, self::MAIN_ERROR_DEMONSTRATOR);

        $projectPath = str_replace('{PROJECT_DIR}', PROJECT_DIR, self::PROJECT_ERROR_DEMONSTRATOR);
        if ($this->fileStorage()->isFile($projectPath)) {
            require_once $projectPath;

            return new demonstrator($errMsg, $tplName, $input);
        }

        return new error_demonstrator($errMsg, $tplName, $input);
    }

    private function fileStorage(): object
    {
        return $this->fileStorage;
    }
}
