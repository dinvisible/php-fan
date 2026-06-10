<?php

declare(strict_types=1);

namespace fan\core\block\admin;

trait upload_size_limit_provider_aware_trait
{
    private mixed $uploadSizeLimitProvider = null;

    public function setUploadSizeLimitProvider(callable $uploadSizeLimitProvider): static
    {
        $this->uploadSizeLimitProvider = $uploadSizeLimitProvider;

        return $this;
    }

    protected function uploadSizeLimit(): string
    {
        return (string)($this->uploadSizeLimitProvider())();
    }

    private function uploadSizeLimitProvider(): callable
    {
        if (!is_callable($this->uploadSizeLimitProvider)) {
            throw new \RuntimeException('Upload size limit provider dependency is not configured for upload block.');
        }

        return $this->uploadSizeLimitProvider;
    }
}
